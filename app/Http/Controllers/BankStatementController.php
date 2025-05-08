<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Statement;
use Smalot\PdfParser\Parser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                if (!isset($current['raw'])) {
                    $current['raw'] = $line;
                } else {
                    $current['raw'] .= ' ' . $line;
                }
            }
        }

        if (!empty($current)) {
            $transactions[] = $current;
        }

        Log::channel('query_log')->info("Transactions parsed: " . count($transactions));

        foreach ($transactions as $i => $item) {
            $rawLine = $item['raw'];
            Log::channel('query_log')->info("Processing Transaction [$i]: " . $rawLine);

            // Support both tabs and multiple spaces
            $fields = preg_split('/\t+|\s{2,}/', $rawLine);
            Log::channel('query_log')->info("Split Fields [$i]: " . json_encode($fields));

            $data = [];

            try {
                if (isset($fields[0]) && preg_match('/^\d{2}-\d{2}-\d{4}$/', $fields[0])) {
                    $data['date'] = Carbon::createFromFormat('d-m-Y', $fields[0]);
                }

                $data['mode'] = $fields[1] ?? null;
                $data['particulars'] = $fields[2] ?? null;

                // Conditionally assign only if numeric
                $data['deposit'] = isset($fields[3]) && is_numeric(str_replace(',', '', $fields[3])) ? str_replace(',', '', $fields[3]) : null;
                $data['withdrawal'] = isset($fields[4]) && is_numeric(str_replace(',', '', $fields[4])) ? str_replace(',', '', $fields[4]) : null;
                $data['balance'] = isset($fields[5]) && is_numeric(str_replace(',', '', $fields[5])) ? str_replace(',', '', $fields[5]) : null;

                Statement::create($data);

                Log::channel('query_log')->info("✅ DB Inserted [$i]: " . json_encode($data));
            } catch (\Exception $e) {
                Log::channel('query_log')->error("❌ Error on transaction [$i]: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Bank statement uploaded and parsed successfully.');
    }
}
