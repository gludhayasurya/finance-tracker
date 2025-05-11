<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\DB;

class ImportBankTransactions extends Command
{
    protected $signature = 'app:import-bank-transactions';
    protected $description = 'Import all bank transactions from PDF';

    public function handle()
    {
        $log = Log::channel('query_log');

        $parser = new Parser();
        $pdfPath = storage_path('app/public/bank.pdf');
        $log->info("Reading PDF from: $pdfPath");

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

            // Step 1: Skip until header line
            if (!$startProcessing && preg_match('/^DATE\s+MODE\*\*\s+PARTICULARS/i', $line)) {
                $startProcessing = true;
                $log->info("Header detected at line $index. Starting to process transactions.");
                continue;
            }

            if (!$startProcessing || $line === '') {
                continue;
            }

            // Step 2: Stop if "TOTAL" is encountered
            if (stripos($line, 'TOTAL') !== false) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction: " . json_encode($currentRow));
                    $currentRow = null;
                }
                $log->info("Reached 'TOTAL' line at index $index. Stopping transaction processing.");
                break;
            }

            // Step 3: Detect page breaks (e.g., "Page X of Y" or repeated header)
            if (preg_match('/Page \d+ of \d+/i', $line) || preg_match('/^DATE\s+MODE\*\*\s+PARTICULARS/i', $line)) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction before page break: " . json_encode($currentRow));
                    $currentRow = null;
                }
                $log->info("Detected page break or repeated header at line $index: \"$line\". Resetting for new page.");
                continue;
            }

            // Step 4: Skip lines that are likely page metadata (e.g., "MR.UDHAYAKUMAR GUNASEELAN")
            if (preg_match('/MR\.UDHAYAKUMAR GUNASEELAN/i', $line)) {
                $log->info("Skipping page metadata at line $index: \"$line\"");
                continue;
            }

            // Step 5: Line starts with date => new transaction
            if (preg_match('/^\d{2}-\d{2}-\d{4}/', $line)) {
                if ($currentRow) {
                    $this->parseNumericValues($currentRow, $log);
                    $transactions[] = $currentRow;
                    $log->info("Saved transaction: " . json_encode($currentRow));
                }

                // Split the line, accounting for both spaces and tabs
                $parts = preg_split('/[\s\t]+/', $line);
                $log->info("[$index] Split parts: " . json_encode($parts));

                $date = null;
                if (preg_match('/^\d{2}-\d{2}-\d{4}/', $parts[0], $matches)) {
                    try {
                        $date = Carbon::createFromFormat('d-m-Y', $matches[0])->format('Y-m-d');
                        $log->info("[$index] Parsed date: $date");
                    } catch (\Exception $e) {
                        $log->error("[$index] Date parsing failed: " . $matches[0] . " - " . $e->getMessage());
                    }
                }

                // Extract mode (e.g., "ICICI ATM")
                $mode = null;
                $particularsStartIndex = 1;
                if (isset($parts[1]) && !preg_match('/\d{1,3}(?:,\d{3})*\.\d{2}/', $parts[1])) {
                    $mode = $parts[1];
                    $particularsStartIndex = 2;
                    // Handle multi-word mode (e.g., "ICICI ATM")
                    if (isset($parts[2]) && !preg_match('/\d{1,3}(?:,\d{3})*\.\d{2}/', $parts[2])) {
                        $mode .= ' ' . $parts[2];
                        $particularsStartIndex = 3;
                    }
                }

                // Extract particulars and amounts
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

                // Initialize the transaction row
                $currentRow = [
                    'date' => $date,
                    'mode' => $mode,
                    'particulars' => $particulars,
                    'deposit' => null,
                    'withdrawal' => null,
                    'balance' => null,
                ];

                // Assign amounts if found in the transaction line
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
                // Step 6: Continuation line (append to particulars)
                if ($currentRow) {
                    $currentRow['particulars'] .= ' ' . $line;
                    $log->info("[$index] Appended to particulars: \"$line\"");
                } else {
                    $log->info("[$index] Skipping line \"$line\" as no current transaction exists.");
                    continue;
                }
            }
        }

        // Step 7: Push last transaction (if not already saved due to TOTAL)
        if ($currentRow) {
            $this->parseNumericValues($currentRow, $log);
            $transactions[] = $currentRow;
            $log->info("Saved final transaction: " . json_encode($currentRow));
        }

        // Step 8: Insert into database
        $log->info("Inserting " . count($transactions) . " transactions...");
        foreach ($transactions as $i => $txn) {
            try {
                DB::table('statement_transactions')->insert($txn);
                $log->info("Inserted row $i: " . json_encode($txn));
            } catch (\Exception $e) {
                $log->error("Failed to insert row $i: " . $e->getMessage());
            }
        }

        $this->info("Import completed. Total: " . count($transactions));
        $log->info("Import process completed successfully.");
    }

    /**
     * Parse numeric values from particulars and assign to deposit, withdrawal, and balance.
     *
     * @param array &$row The current transaction row
     * @param \Illuminate\Support\Facades\Log $log Logger instance
     */
    private function parseNumericValues(&$row, $log)
    {
        // Extract numeric values (e.g., 34,935.00) from particulars
        $pattern = '/\d{1,3}(?:,\d{3})*\.\d{2}\b/';
        preg_match_all($pattern, $row['particulars'], $matches);

        $numbers = $matches[0] ?? [];
        $log->info("Extracted numbers from particulars: " . json_encode($numbers));

        // Assign to deposit, withdrawal, and balance based on position
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

        // Remove numeric values from particulars
        $row['particulars'] = preg_replace($pattern, '', $row['particulars']);
        $row['particulars'] = trim($row['particulars']);
        $log->info("Updated particulars after removing numbers: " . $row['particulars']);
    }
}