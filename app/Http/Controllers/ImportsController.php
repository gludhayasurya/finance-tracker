<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\Statement;
use Illuminate\Http\Request;

class ImportsController extends Controller
{
    public function index()
    {
        $imports = Import::with('bank')->latest()->get();
        // dd($imports);
        return view('imports.index', compact('imports'));
    }

    public function viewStatements($bank, $import)
    {
        $statements = Statement::where('bank_id', $bank)
            ->where('imported_id', $import)
            ->get();

        return view('statements.index', compact('statements'));
    }

}
