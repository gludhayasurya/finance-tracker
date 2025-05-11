<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Statement;
use Smalot\PdfParser\Parser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BankStatementController extends Controller
{
    public function uploadForm()
    {
        return view('banks.upload');
    }

    public function parseAndStore(Request $request)
    {
        $request->validate([
            'statement' => 'required|mimes:pdf|max:5120',
        ]);

        DB::enableQueryLog(); // ✅ Enable SQL logging

        $pdfPath = $request->file('statement')->getPathname();
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();

        $lines = explode("\n", $text);
        $transactions = [];
        $current = [];
        $startParsing = false;

        Log::channel('query_log')->info("PDF Upload successful. Line count: " . count($lines));

        foreach ($lines as $index => $line) {
            $line = trim($line);

            if (!$startParsing) {
                if (stripos($line, 'Statement of Transactions') !== false) {
                    $startParsing = true;
                    Log::channel('query_log')->info("Found 'Statement of Transactions' at line $index.");
                }
                continue;
            }

            if ($line === '') {
                continue;
            }

            if (preg_match('/^\d{2}-\d{2}-\d{4}/', $line)) {
                if (!empty($current)) {
                    $transactions[] = $current;
                }
                $current = ['raw' => $line];
            } else {
                $current['raw'] = isset($current['raw']) ? $current['raw'] . ' ' . $line : $line;
            }
        }

        if (!empty($current)) {
            $transactions[] = $current;
        }

        Log::channel('query_log')->info("Transactions parsed: " . count($transactions));

        foreach ($transactions as $i => $item) {
            $rawLine = $item['raw'];
            Log::channel('query_log')->info("Processing Transaction [$i]: $rawLine");
        
            try {
                // Initialize variables
                $date = null;
                $mode = null;
                $particulars = null;
                $deposit = null;
                $withdrawal = null;
                $balance = null;
        
                // Step 1: Extract the date (dd-mm-yyyy) from the start of the line
                if (preg_match('/^(\d{2}-\d{2}-\d{4})/', $rawLine, $dateMatch)) {
                    $date = Carbon::createFromFormat('d-m-Y', $dateMatch[1]);
                    // Remove the date from the raw line
                    $rawLine = trim(substr($rawLine, strlen($dateMatch[1])));
                }
        
                // Step 2: Extract the amounts (deposit, withdrawal, balance) from the end
                // Look for the last two or three numeric values (e.g., 92.00 5,661.09)
                $amounts = [];
                if (preg_match_all('/\d{1,3}(?:,\d{3})*\.\d{2}/', $rawLine, $amountMatches)) {
                    $amounts = $amountMatches[0];
                    // Remove the amounts from the raw line
                    foreach ($amounts as $amount) {
                        $rawLine = trim(str_replace($amount, '', $rawLine));
                    }
                }
        
                // Assign amounts (from right to left: balance, withdrawal, deposit)
                if (count($amounts) > 0) {
                    $balance = $this->parseAmount(array_pop($amounts));
                }
                if (count($amounts) > 0) {
                    $withdrawal = $this->parseAmount(array_pop($amounts));
                }
                if (count($amounts) > 0) {
                    $deposit = $this->parseAmount(array_pop($amounts));
                }
        
                // Step 3: Split the remaining part into mode and particulars
                $remainingFields = preg_split('/\s{2,}/', $rawLine);
                if (count($remainingFields) >= 2) {
                    $mode = $remainingFields[0];
                    $particulars = implode(' ', array_slice($remainingFields, 1));
                } else {
                    $particulars = $rawLine;
                }
        
                // Create data for insertion into the database
                $data = [
                    'date'        => $date,
                    'mode'        => $mode,
                    'particulars' => $particulars,
                    'deposit'     => $deposit,
                    'withdrawal'  => $withdrawal,
                    'balance'     => $balance,
                ];
        
                Statement::create($data);
                Log::channel('query_log')->info("✅ DB Inserted [$i]: " . json_encode($data));
            } catch (\Exception $e) {
                Log::channel('query_log')->error("❌ Error on transaction [$i]: " . $e->getMessage());
            }
        }

        // ✅ Log SQL Queries
        $queryLog = DB::getQueryLog();
        Log::channel('query_log')->info("🔍 SQL Queries: " . json_encode($queryLog));

        return redirect()->back()->with('success', 'Bank statement uploaded and parsed successfully.');
    }

    private function parseAmount($value)
    {
        if (is_null($value)) return null;
        $value = str_replace(',', '', trim($value));
        return is_numeric($value) ? (float) $value : null;
    }


    public function index()
    {
        $statements = Statement::latest()->get();
        return view('statements.index', compact('statements'));
    }
}
