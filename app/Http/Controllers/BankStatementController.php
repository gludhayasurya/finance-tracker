<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Carbon\Carbon;

class BankStatementController extends Controller
{
    public function uploadForm(Request $request, $bank_id)
    {
        return view('banks.upload', compact('bank_id'));
    }

    public function parseAndStore(Request $request, $bank_id)
    {
        $request->validate([
            'statement' => 'required|mimes:pdf|max:5120',
        ]);

        $log = Log::channel('query_log');

        // Store uploaded file
        $path = $request->file('statement')->storeAs('public', 'bank.pdf');
        $pdfPath = storage_path('app/' . $path);
        $log->info("PDF uploaded and stored at: $pdfPath");

        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();
        $lines = explode("\n", $text);
        $log->info("PDF parsed. Total lines: " . count($lines));

        $startProcessing = false;
        $transactions = [];
        $currentRow = null;

        foreach ($lines as $index => $line) {
            $line = trim($line);
            $log->info("[$index] Raw Line: \"$line\"");

            if (!$startProcessing && preg_match('/^DATE\s+MODE\*\*\s+PARTICULARS/i', $line)) {
                $startProcessing = true;
                $log->info("Header detected at line $index. Starting to process transactions.");
                continue;
            }

            if (!$startProcessing || $line === '') {
                continue;
            }

            if (stripos($line, 'TOTAL') !== false) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction: " . json_encode($currentRow));
                    $currentRow = null;
                }
                break;
            }

            if (preg_match('/Page \d+ of \d+/i', $line) || preg_match('/^DATE\s+MODE\*\*\s+PARTICULARS/i', $line)) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction before page break: " . json_encode($currentRow));
                    $currentRow = null;
                }
                continue;
            }

            if (preg_match('/MR\.UDHAYAKUMAR GUNASEELAN/i', $line)) {
                continue;
            }

            if (preg_match('/^\d{2}-\d{2}-\d{4}/', $line)) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction: " . json_encode($currentRow));
                }

                $parts = preg_split('/[\s\t]+/', $line);
                $log->info("[$index] Split parts: " . json_encode($parts));

                $date = null;
                if (preg_match('/^\d{2}-\d{2}-\d{4}/', $parts[0], $matches)) {
                    try {
                        $date = Carbon::createFromFormat('d-m-Y', $matches[0])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $log->error("[$index] Date parsing failed: " . $matches[0]);
                    }
                }

                $mode = null;
                $particularsStartIndex = 1;
                if (isset($parts[1]) && !preg_match('/\d{1,3}(?:,\d{3})*\.\d{2}/', $parts[1])) {
                    $mode = $parts[1];
                    $particularsStartIndex = 2;
                    if (isset($parts[2]) && !preg_match('/\d{1,3}(?:,\d{3})*\.\d{2}/', $parts[2])) {
                        $mode .= ' ' . $parts[2];
                        $particularsStartIndex = 3;
                    }
                }

                $particulars = '';
                $amounts = [];
                for ($i = $particularsStartIndex; $i < count($parts); $i++) {
                    if (preg_match('/\d{1,3}(?:,\d{3})*\.\d{2}/', $parts[$i])) {
                        $amounts[] = $parts[$i];
                    } else {
                        $particulars .= ' ' . $parts[$i];
                    }
                }
                $particulars = trim($particulars);

                $currentRow = [
                    'date' => $date,
                    'mode' => $mode,
                    'particulars' => $particulars,
                    'deposit' => null,
                    'withdrawal' => null,
                    'balance' => null,
                ];

                if (!empty($amounts)) {
                    if (count($amounts) >= 1) {
                        if (strpos($currentRow['particulars'], 'UPI') !== false || strpos($currentRow['particulars'], 'IMPS') !== false || strpos($currentRow['particulars'], 'ATM') !== false) {
                            $currentRow['withdrawal'] = floatval(str_replace(',', '', $amounts[0]));
                        } else {
                            $currentRow['deposit'] = floatval(str_replace(',', '', $amounts[0]));
                        }
                    }
                    if (count($amounts) >= 2) {
                        $currentRow['balance'] = floatval(str_replace(',', '', $amounts[1]));
                    }
                }
            } else {
                if ($currentRow) {
                    $currentRow['particulars'] .= ' ' . $line;
                } else {
                    continue;
                }
            }
        }

        if ($currentRow) {
            $this->parseNumericValues($currentRow, $log);
            $transactions[] = $currentRow;
            $log->info("Saved final transaction: " . json_encode($currentRow));
        }

        foreach ($transactions as $i => $txn) {
            $txn['bank_id'] = $bank_id;
            try {
                DB::table('statement_transactions')->insert($txn);
                $log->info("Inserted row $i: " . json_encode($txn));
            } catch (\Exception $e) {
                $log->error("Failed to insert row $i: " . $e->getMessage());
            }
        }

        return redirect()->route('statements.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Bank transactions imported successfully.',
                'count' => count($transactions),
            ]);
    }

    private function parseNumericValues(&$row, $log)
    {
        $pattern = '/\d{1,3}(?:,\d{3})*\.\d{2}\b/';
        preg_match_all($pattern, $row['particulars'], $matches);

        $numbers = $matches[0] ?? [];

        if (count($numbers) >= 1) {
            if (strpos($row['particulars'], 'UPI') !== false || strpos($row['particulars'], 'IMPS') !== false || strpos($row['particulars'], 'ATM') !== false) {
                $row['withdrawal'] = floatval(str_replace(',', '', $numbers[0]));
            } else {
                $row['deposit'] = floatval(str_replace(',', '', $numbers[0]));
            }
        }
        if (count($numbers) >= 2) {
            $row['balance'] = floatval(str_replace(',', '', $numbers[1]));
        }
        if (count($numbers) >= 3) {
            $row['deposit'] = floatval(str_replace(',', '', $numbers[0]));
            $row['withdrawal'] = floatval(str_replace(',', '', $numbers[1]));
            $row['balance'] = floatval(str_replace(',', '', $numbers[2]));
        }

        $row['particulars'] = preg_replace($pattern, '', $row['particulars']);
        $row['particulars'] = trim($row['particulars']);
    }


    public function index(Request $request, $bank_id)
    {
        $statements = DB::table('statement_transactions')
            ->where('bank_id', $bank_id)
            ->orderBy('date', 'desc')
            ->get();

        return view('bank.statements.index', compact('statements', 'bank_id'));
    }
}
