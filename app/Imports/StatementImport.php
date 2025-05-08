<?php

namespace App\Imports;

use App\Models\Statement;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class StatementImport implements ToModel, WithHeadingRow
{
    protected $bank_id;
    protected $import_id;

    public function __construct($bank_id, $import_id)
    {
        $this->bank_id = $bank_id;
        $this->import_id = $import_id;
    }


    public function model(array $row)
    {
        return new Statement([
            'date'        => Carbon::parse($row['date'])->format('Y-m-d'),
            'mode'        => $row['mode'] ?? null,
            'particulars' => $row['particulars'] ?? null,
            'deposit'     => $row['deposit'] ?? null,
            'withdrawal'  => $row['withdrawal'] ?? null,
            'balance'     => $row['balance'] ?? null,
            'bank_id'     => $this->bank_id,
            'imported_id' => $this->import_id,
        ]);
    }
}
