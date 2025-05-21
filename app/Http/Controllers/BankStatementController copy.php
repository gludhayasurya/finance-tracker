<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\Statement;
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

        // Assume the file is already in storage/app/public/bank.pdf
        $pdfPath = storage_path('app/public/bank.pdf');
        $filename = $request->file('statement')->getClientOriginalName();
        $filepath = 'public/bank.pdf'; // Relative path in storage
        //$log->info("Using existing PDF file at: $pdfPath");

        // // Store the uploaded file
        // $file = $request->file('statement');
        // $filename = $file->getClientOriginalName();
        // $filepath = $file->store('public'); // Stores in storage/app/public
        // $pdfPath = storage_path('app/' . $filepath);
        // //$log->info("Using PDF file at: $pdfPath");

        // Create Import record
        $import = Import::create([
            'bank_id' => $bank_id,
            'filename' => $filename,
            'filepath' => $filepath,
            'status' => 'pending',
        ]);
        //$log->info("Created import record: ID={$import->id}");

        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();
        $lines = explode("\n", $text);
        //$log->info("PDF parsed. Total lines: " . count($lines));

        $startProcessing = false;
        $transactions = [];
        $currentRow = null;
        $totalWithdrawal = 0;
        $totalDeposit = 0;
        $finalBalance = 0;

        foreach ($lines as $index => $line) {
            $line = trim($line);
            //$log->info("[$index] Raw Line: \"$line\"");

            if (!$startProcessing && preg_match('/^DATE\s+MODE\*\*\s+PARTICULARS/i', $line)) {
                $startProcessing = true;
                //$log->info("Header detected at line $index. Starting to process transactions.");
                continue;
            }

            if (!$startProcessing || $line === '') {
                continue;
            }

            if (stripos($line, 'TOTAL') !== false) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log);
                    $transactions[] = $currentRow;
                    //$log->info("Saved transaction: " . json_encode($currentRow));
                    $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                    $totalDeposit += $currentRow['deposit'] ?? 0;
                    if ($currentRow['balance']) {
                        $finalBalance = $currentRow['balance'];
                    }
                    $currentRow = null;
                }
                break;
            }

            if (preg_match('/Page \d+ of \d+/i', $line) || preg_match('/^DATE\s+MODE\*\*\s+PARTICULARS/i', $line)) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log);
                    $transactions[] = $currentRow;
                    //$log->info("Saved transaction before page break: " . json_encode($currentRow));
                    $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                    $totalDeposit += $currentRow['deposit'] ?? 0;
                    if ($currentRow['balance']) {
                        $finalBalance = $currentRow['balance'];
                    }
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
                    //$log->info("Saved transaction: " . json_encode($currentRow));
                    $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                    $totalDeposit += $currentRow['deposit'] ?? 0;
                    if ($currentRow['balance']) {
                        $finalBalance = $currentRow['balance'];
                    }
                }

                $parts = preg_split('/[\s\t]+/', $line);
                //$log->info("[$index] Split parts: " . json_encode($parts));

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
                    'imported_id' => $import->id,
                    'bank_id' => $bank_id,
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
            //$log->info("Saved final transaction: " . json_encode($currentRow));
            $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
            $totalDeposit += $currentRow['deposit'] ?? 0;
            if ($currentRow['balance']) {
                $finalBalance = $currentRow['balance'];
            }
        }

        // Insert transactions using Eloquent
        foreach ($transactions as $i => $txn) {
            try {
                Statement::create($txn);
                //$log->info("Inserted transaction $i: " . json_encode($txn));
            } catch (\Exception $e) {
                $log->error("Failed to insert transaction $i: " . $e->getMessage());
            }
        }

        // Update Import record with totals
        try {
            $import->update([
                'total_withdrawal' => $totalWithdrawal,
                'total_deposit' => $totalDeposit,
                'total_balance' => $finalBalance,
                'status' => 'completed',
            ]);



            //$log->info("Updated import ID={$import->id}: total_withdrawal=$totalWithdrawal, total_deposit=$totalDeposit, total_balance=$finalBalance");
        } catch (\Exception $e) {
            $log->error("Failed to update import ID={$import->id}: " . $e->getMessage());
        }

        return redirect()->route('statements.index', ['bank_id' => $bank_id])
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
        $statements = Statement::where('bank_id', $bank_id)
            ->orderBy('date', 'desc')
            ->get();


        return view('statements.index', compact('statements', 'bank_id'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        // If "extracted_particular" is present, map it to "particulars"
        if (isset($data['extracted_particular'])) {
            $data['particulars'] = $data['extracted_particular'];
            unset($data['extracted_particular']);
        }

        $txn = Statement::findOrFail($id);
        // dd($txn);
        $txn->update($data);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Transaction updated successfully.'
        ]);
    }

}
