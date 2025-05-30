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
use App\Models\Bank;


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

        // Store the uploaded file
        // $file = $request->file('statement');
        // $filename = $file->getClientOriginalName();
        // $filepath = $file->store('public'); // Stores in storage/app/public
        // $pdfPath = storage_path('app/' . $filepath);
        // $log->info("Using PDF file at: $pdfPath");

         $pdfPath = storage_path('app/public/i_bank.pdf');
        $filename = $request->file('statement')->getClientOriginalName();
        $filepath = 'public/bank.pdf'; // Relative path in storage

        // Create Import record
        $import = Import::create([
            'bank_id' => $bank_id,
            'filename' => $filename,
            'filepath' => $filepath,
            'status' => 'pending',
        ]);
        $log->info("Created import record: ID={$import->id}");

        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();
        $lines = explode("\n", $text);
        $log->info("PDF parsed. Total lines: " . count($lines));

        // Detect bank type
        $bankType = $this->detectBank($text, $bank_id, $log);
        $log->info("Detected bank type: $bankType");

        $startProcessing = false;
        $transactions = [];
        $currentRow = null;
        $totalWithdrawal = 0;
        $totalDeposit = 0;
        $finalBalance = 0;

        foreach ($lines as $index => $line) {
            $line = trim($line);
            $log->info("[$index] Raw Line: \"$line\"");

            // Detect header to start processing
            if (!$startProcessing) {
                if ($bankType === 'icici' && preg_match('/^DATE\s+MODE\*\*\s+PARTICULARS/i', $line)) {
                    $startProcessing = true;
                    $log->info("ICICI header detected at line $index. Starting to process transactions.");
                    continue;
                } elseif ($bankType === 'indian' && preg_match('/^Date\s+Transaction Details\s+Debits\s+Credits\s+Balance/i', $line)) {
                    $startProcessing = true;
                    $log->info("Indian Bank header detected at line $index. Starting to process transactions.");
                    continue;
                }
                continue;
            }

            if ($line === '') {
                continue;
            }

            // Stop processing at footer or summary
            if ($bankType === 'indian' && (stripos($line, 'Ending Balance') !== false || stripos($line, 'ACCOUNT SUMMARY') !== false || stripos($line, 'For period:') !== false || stripos($line, 'Total INR') !== false)) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log, $bankType);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction: " . json_encode($currentRow));
                    $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                    $totalDeposit += $currentRow['deposit'] ?? 0;
                    if ($currentRow['balance']) {
                        $finalBalance = $currentRow['balance'];
                    }
                    $currentRow = null;
                }
                break;
            } elseif ($bankType === 'icici' && stripos($line, 'TOTAL') !== false) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log, $bankType);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction: " . json_encode($currentRow));
                    $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                    $totalDeposit += $currentRow['deposit'] ?? 0;
                    if ($currentRow['balance']) {
                        $finalBalance = $currentRow['balance'];
                    }
                    $currentRow = null;
                }
                break;
            }

            // Skip page breaks
            if (preg_match('/Page \d+ of \d+/i', $line)) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log, $bankType);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction before page break: " . json_encode($currentRow));
                    $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                    $totalDeposit += $currentRow['deposit'] ?? 0;
                    if ($currentRow['balance']) {
                        $finalBalance = $currentRow['balance'];
                    }
                    $currentRow = null;
                }
                continue;
            }

            // Skip account holder details for ICICI
            if ($bankType === 'icici' && preg_match('/MR\.UDHAYAKUMAR GUNASEELAN/i', $line)) {
                continue;
            }

            // Start a new transaction if line begins with a date
            $datePattern = $bankType === 'icici' ? '/^\d{2}-\d{2}-\d{4}/' : '/^\d{2}\s+[A-Za-z]{3}\s+\d{4}/';
            if (preg_match($datePattern, $line)) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log, $bankType);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction: " . json_encode($currentRow));
                    $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                    $totalDeposit += $currentRow['deposit'] ?? 0;
                    if ($currentRow['balance']) {
                        $finalBalance = $currentRow['balance'];
                    }
                }

                $parts = preg_split('/\s+/', $line, $bankType === 'icici' ? 4 : 5);
                $log->info("[$index] Split parts: " . json_encode($parts));

                $date = null;
                if ($bankType === 'icici' && preg_match('/^\d{2}-\d{2}-\d{4}/', $parts[0], $matches)) {
                    try {
                        $date = Carbon::createFromFormat('d-m-Y', $matches[0])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $log->error("[$index] Date parsing failed: " . $matches[0]);
                    }
                } elseif ($bankType === 'indian' && count($parts) >= 3 && preg_match('/^\d{2}\s+[A-Za-z]{3}\s+\d{4}/', $parts[0] . ' ' . $parts[1] . ' ' . $parts[2])) {
                    try {
                        $date = Carbon::createFromFormat('d M Y', $parts[0] . ' ' . $parts[1] . ' ' . $parts[2])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $log->error("[$index] Date parsing failed: " . $parts[0] . ' ' . $parts[1] . ' ' . $parts[2]);
                    }
                }

                if ($bankType === 'icici') {
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
                } else {
                    $mode = null;
                    $particulars = isset($parts[3]) ? $parts[3] : '';
                    $amounts = array_slice($parts, 3);
                }

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

                if ($bankType === 'icici' && !empty($amounts)) {
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
                    // For Indian Bank, check if the line contains amounts (e.g., "INR 500.00 - INR 19,979.62")
                    if ($bankType === 'indian' && preg_match('/INR\s+\d{1,3}(?:,\d{3})*\.\d{2}\s*-\s*INR\s+\d{1,3}(?:,\d{3})*\.\d{2}/', $line)) {
                        $currentRow['particulars'] .= ' ' . $line;
                        $this->parseNumericValues($currentRow, $log, $bankType);
                        $transactions[] = $currentRow;
                        $log->info("Saved transaction with amounts: " . json_encode($currentRow));
                        $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                        $totalDeposit += $currentRow['deposit'] ?? 0;
                        if ($currentRow['balance']) {
                            $finalBalance = $currentRow['balance'];
                        }
                        $currentRow = null;
                    } else {
                        $currentRow['particulars'] .= ' ' . $line;
                    }
                } else {
                    continue;
                }
            }
        }

        // Save the final transaction if it hasn't been saved
        if ($currentRow) {
            $this->parseNumericValues($currentRow, $log, $bankType);
            $transactions[] = $currentRow;
            $log->info("Saved final transaction: " . json_encode($currentRow));
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
                $log->info("Inserted transaction $i: " . json_encode($txn));
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
            $log->info("Updated import ID={$import->id}: total_withdrawal=$totalWithdrawal, total_deposit=$totalDeposit, total_balance=$finalBalance");
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

    private function parseNumericValues(&$row, $log, $bankType)
    {
        $pattern = $bankType === 'indian' ? '/INR\s+\d{1,3}(?:,\d{3})*\.\d{2}\b/' : '/\d{1,3}(?:,\d{3})*\.\d{2}\b/';
        preg_match_all($pattern, $row['particulars'], $matches);

        $numbers = $matches[0] ?? [];
        $numbers = array_map(function ($num) use ($bankType) {
            return floatval(str_replace(['INR', ','], '', trim($num)));
        }, $numbers);

        // Remove numeric values from particulars
        $row['particulars'] = preg_replace($pattern, '', $row['particulars']);
        $row['particulars'] = trim($row['particulars']);

        if ($bankType === 'indian') {
            // Indian Bank: Handle Debits - Balance format
            if (preg_match('/INR\s+\d{1,3}(?:,\d{3})*\.\d{2}\s*-\s*INR\s+\d{1,3}(?:,\d{3})*\.\d{2}/', $row['particulars'], $match)) {
                $amounts = preg_match_all($pattern, $match[0], $amountMatches);
                $numbers = array_map(function ($num) {
                    return floatval(str_replace(['INR', ','], '', trim($num)));
                }, $amountMatches[0]);
                $row['particulars'] = str_replace($match[0], '', $row['particulars']);
                $row['particulars'] = trim($row['particulars']);
            }

            if (count($numbers) >= 1) {
                if (strpos($row['particulars'], 'WITHDRAWAL') !== false || strpos($row['particulars'], 'UPI') !== false || strpos($row['particulars'], 'IMPS') !== false) {
                    $row['withdrawal'] = $numbers[0];
                } else {
                    $row['deposit'] = $numbers[0];
                }
            }
            if (count($numbers) >= 2) {
                $row['balance'] = $numbers[1];
            }
        } else {
            // ICICI Bank: Existing logic
            if (count($numbers) >= 1) {
                if (strpos($row['particulars'], 'UPI') !== false || strpos($row['particulars'], 'IMPS') !== false || strpos($row['particulars'], 'ATM') !== false) {
                    $row['withdrawal'] = $numbers[0];
                } else {
                    $row['deposit'] = $numbers[0];
                }
            }
            if (count($numbers) >= 2) {
                $row['balance'] = $numbers[1];
            }
            if (count($numbers) >= 3) {
                $row['deposit'] = $numbers[0];
                $row['withdrawal'] = $numbers[1];
                $row['balance'] = $numbers[2];
            }
        }
    }

    private function detectBank($text, $bank_id, $log)
    {
        // Check for bank-specific headers
        if (stripos($text, 'DATE MODE** PARTICULARS') !== false) {
            return 'icici';
        } elseif (stripos($text, 'Date Transaction Details Debits Credits Balance') !== false) {
            return 'indian';
        }

        // Fallback: Check for bank names
        if (stripos($text, 'ICICI') !== false) {
            $log->info("Detected ICICI Bank via keyword.");
            return 'icici';
        } elseif (stripos($text, 'Indian Bank') !== false) {
            $log->info("Detected Indian Bank via keyword.");
            return 'indian';
        }

        // Fallback to bank_id (assuming bank_id 1=ICICI, 2=Indian Bank, adjust as needed)
        $log->warning("Could not detect bank from headers or keywords. Using bank_id=$bank_id as fallback.");
        return $bank_id == 1 ? 'icici' : 'indian';
    }


    public function index(Request $request, $bank_id)
    {
        $statements = Statement::where('bank_id', $bank_id)
            ->orderBy('date', 'desc')
            ->get();

$bank = Bank::findOrFail($bank_id);

        return view('statements.index', compact('statements', 'bank_id', 'bank'));
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

public function budgets(Request $request)
    {
        // Fetch budgets and related data
        $budgets = DB::table('budgets')
            ->get();

$categories = DB::table('categories')
            ->get();

        return view('budgets.index', compact('budgets', 'categories'));
    }

}
