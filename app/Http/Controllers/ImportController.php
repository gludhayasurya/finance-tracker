<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Imports\StatementImport;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function importForm($bankId)
    {
        $bank = Bank::findOrFail($bankId);

        return view('transactions.import', compact('bank'));
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->storeAs('imports', time() . '_' . $originalName);

        $import = Import::create([
            'filename' => $originalName,
            'filepath' => $path,
            'status'   => 'pending',
        ]);

        // Import Statements
        Excel::import(new StatementImport($request->bank_id, $import->id), storage_path('app/' . $path));

        $import->update(['status' => 'completed']);

        return back()->with('success', 'Statements imported successfully.');
    }
}
