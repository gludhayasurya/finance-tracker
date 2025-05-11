<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Statement;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\DB;


class ImportBankTransactions extends Command
{
    protected $signature = 'app:import-bank-transactions';
    protected $description = 'Import all bank transactions from Excel sheets';

    public function handle()
{
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile(storage_path('app/public/bank.pdf'));
    $text = $pdf->getText();

    $lines = explode("\n", $text);
    $startProcessing = false;
    $transactions = [];
    $currentRow = null;

    foreach ($lines as $line) {
        $line = trim($line);

        // Detect header line to start processing
        if (preg_match('/^DATE\s+MODE\*\*\s+PARTICULARS/i', $line)) {
            $startProcessing = true;
            continue;
        }

        if (!$startProcessing || $line === '') {
            continue;
        }

        // Check if line starts with a valid date
        if (preg_match('/^\d{2}-\d{2}-\d{4}/', $line)) {
            // Save previous row
            if ($currentRow) {
                $transactions[] = $currentRow;
            }

            // Try splitting only known number of columns: date, mode, particulars, deposit, withdrawal, balance
            $parts = preg_split('/\s{2,}/', $line);

            // Reset currentRow
            $currentRow = [
                'date' => (preg_match('/^\d{2}-\d{2}-\d{4}/', $parts[0], $matches)) 
    ? \Carbon\Carbon::createFromFormat('d-m-Y', $matches[0])->format('Y-m-d') 
    : null,

                'mode' => $parts[1] ?? null,
                'particulars' => $parts[2] ?? '',
                'deposit' => isset($parts[3]) && is_numeric(str_replace(',', '', $parts[3])) ? floatval(str_replace(',', '', $parts[3])) : null,
                'withdrawal' => isset($parts[4]) && is_numeric(str_replace(',', '', $parts[4])) ? floatval(str_replace(',', '', $parts[4])) : null,
                'balance' => isset($parts[5]) && is_numeric(str_replace(',', '', $parts[5])) ? floatval(str_replace(',', '', $parts[5])) : null,
            ];
        } else {
            // This is continuation of "Particulars"
            $currentRow['particulars'] .= ' ' . $line;
        }
    }

    // Add last transaction
    if ($currentRow) {
        $transactions[] = $currentRow;
    }

    // Insert into DB
    foreach ($transactions as $txn) {
        DB::table('statement_transactions')->insert($txn);
    }

    $this->info("Imported " . count($transactions) . " transactions successfully.");
}

}
