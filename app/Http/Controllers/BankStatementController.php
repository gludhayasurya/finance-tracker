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

        // Determine bank-specific settings
        $isAbcBank = $bank_id == 2; // bank_id 1 = XYZ Bank, 2 = ABC Bank
        $headerPattern = $isAbcBank
            ? '/^Date\s+Transaction Details\s+Debits\s+Credits\s+Balance/i'
            : '/^DATE\s+MODE\*\*\s+PARTICULARS/i';
        $dateFormat = $isAbcBank ? 'd M Y' : 'd-m-Y';
        $datePattern = $isAbcBank ? '/^\d{2}\s+[A-Za-z]{3}\s+\d{4}/' : '/^\d{2}-\d{2}-\d{4}/';
        $amountPrefix = $isAbcBank ? 'INR ' : null;
        $bankName = $isAbcBank ? 'ABC Bank' : 'XYZ Bank';

        // Store the uploaded file
        // $file = $request->file('statement');
        // $filename = $file->getClientOriginalName();
        // $filepath = $file->store('public');
        // $pdfPath = storage_path('app/' . $filepath);

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

        // Parse PDF
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $pages = $pdf->getPages();
        $transactions = [];
        $totalWithdrawal = 0;
        $totalDeposit = 0;
        $finalBalance = 0;
        $startProcessing = false;
        $currentRow = null;

        foreach ($pages as $pageIndex => $page) {
            $text = $page->getText();
            $lines = explode("\n", $text);

            foreach ($lines as $index => $line) {
                $line = trim($line);

                // Detect the bank-specific header
                if (!$startProcessing && preg_match($headerPattern, $line)) {
                    $startProcessing = true;
                    $log->info("Header detected on page $pageIndex, line $index: $line");
                    continue;
                }

                if (!$startProcessing || $line === '') {
                    continue;
                }

                // Stop processing for specific conditions
                if ($isAbcBank && preg_match('/^ACCOUNT SUMMARY/i', $line)) {
                    if ($currentRow) {
                        $this->parseNumericValues($currentRow, $log, $amountPrefix, $isAbcBank);
                        $transactions[] = $currentRow;
                        $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                        $totalDeposit += $currentRow['deposit'] ?? 0;
                        if ($currentRow['balance']) {
                            $finalBalance = $currentRow['balance'];
                        }
                        $currentRow = null;
                    }
                    break;
                } elseif (!$isAbcBank && stripos($line, 'TOTAL') !== false) {
                    if ($currentRow) {
                        $this->parseNumericValues($currentRow, $log, $amountPrefix, $isAbcBank);
                        $transactions[] = $currentRow;
                        $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                        $totalDeposit += $currentRow['deposit'] ?? 0;
                        if ($currentRow['balance']) {
                            $finalBalance = $currentRow['balance'];
                        }
                        $currentRow = null;
                    }
                    break;
                }

                // Skip page breaks or repeated headers
                if (preg_match('/Page \d+ of \d+/i', $line) || preg_match($headerPattern, $line)) {
                    if ($currentRow) {
                        $this->parseNumericValues($currentRow, $log, $amountPrefix, $isAbcBank);
                        $transactions[] = $currentRow;
                        $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                        $totalDeposit += $currentRow['deposit'] ?? 0;
                        if ($currentRow['balance']) {
                            $finalBalance = $currentRow['balance'];
                        }
                        $currentRow = null;
                    }
                    continue;
                }

                // Skip account holder name for XYZ Bank
                if (!$isAbcBank && preg_match('/MR\.UDHAYAKUMAR GUNASEELAN/i', $line)) {
                    continue;
                }

                // Handle date lines
                if (preg_match($datePattern, $line)) {
                    if ($currentRow) {
                        $this->parseNumericValues($currentRow, $log, $amountPrefix, $isAbcBank);
                        $transactions[] = $currentRow;
                        $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
                        $totalDeposit += $currentRow['deposit'] ?? 0;
                        if ($currentRow['balance']) {
                            $finalBalance = $currentRow['balance'];
                        }
                    }

                    $parts = preg_split('/\s+/', $line, $isAbcBank ? 4 : -1);
                    $date = null;
                    if (preg_match($datePattern, $line, $matches)) {
                        try {
                            $date = Carbon::createFromFormat($dateFormat, $matches[0])->format('Y-m-d');
                        } catch (\Exception $e) {
                            $log->error("Page $pageIndex, Line $index: Date parsing failed: " . $matches[0]);
                        }
                    }

                    if ($isAbcBank) {
                        $particulars = isset($parts[3]) ? $parts[3] : '';
                        $mode = null; // ABC Bank has no mode column
                    } else {
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

                        if (!empty($amounts)) {
                            if (count($amounts) >= 1) {
                                if (strpos($particulars, 'UPI') !== false || strpos($particulars, 'IMPS') !== false || strpos($particulars, 'ATM') !== false) {
                                    $currentRow['withdrawal'] = floatval(str_replace(',', '', $amounts[0]));
                                } else {
                                    $currentRow['deposit'] = floatval(str_replace(',', '', $amounts[0]));
                                }
                            }
                            if (count($amounts) >= 2) {
                                $currentRow['balance'] = floatval(str_replace(',', '', $amounts[1]));
                            }
                        }
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
                } else {
                    if ($currentRow) {
                        $currentRow['particulars'] .= ' ' . $line;
                    }
                }
            }
        }

        // Save the last transaction
        if ($currentRow) {
            $this->parseNumericValues($currentRow, $log, $amountPrefix, $isAbcBank);
            $transactions[] = $currentRow;
            $totalWithdrawal += $currentRow['withdrawal'] ?? 0;
            $totalDeposit += $currentRow['deposit'] ?? 0;
            if ($currentRow['balance']) {
                $finalBalance = $currentRow['balance'];
            }
        }

        // Insert transactions
        foreach ($transactions as $i => $txn) {
            try {
                Statement::create($txn);
                $log->info("Inserted transaction $i: " . json_encode($txn));
            } catch (\Exception $e) {
                $log->error("Failed to insert transaction $i: " . $e->getMessage());
            }
        }

        // Update Import record
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
                'message' => "$bankName transactions imported successfully.",
                'count' => count($transactions),
            ]);
    }

    private function parseNumericValues(&$row, $log, $amountPrefix, $isAbcBank)
    {
        $pattern = $amountPrefix
            ? '/' . preg_quote($amountPrefix, '/') . '\s*\d{1,3}(?:,\d{3})*\.\d{2}\b/'
            : '/\d{1,3}(?:,\d{3})*\.\d{2}\b/';
        preg_match_all($pattern, $row['particulars'], $matches);

        $numbers = array_map(function ($value) use ($amountPrefix) {
            return floatval(str_replace([$amountPrefix ?? '', ','], '', $value));
        }, $matches[0] ?? []);

        if (count($numbers) >= 1) {
            if ($isAbcBank) {
                if (strpos($row['particulars'], 'TRANSFER FROM') !== false || strpos($row['particulars'], 'PhonePe Reversal') !== false) {
                    $row['deposit'] = $numbers[0];
                } else {
                    $row['withdrawal'] = $numbers[0];
                }
            } else {
                if (strpos($row['particulars'], 'UPI') !== false || strpos($row['particulars'], 'IMPS') !== false || strpos($row['particulars'], 'ATM') !== false) {
                    $row['withdrawal'] = $numbers[0];
                } else {
                    $row['deposit'] = $numbers[0];
                }
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
