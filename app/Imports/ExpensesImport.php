<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Currency;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ExpensesImport implements ToCollection,
    SkipsEmptyRows,
    WithHeadingRow,
    SkipsOnError,
    WithValidation,
    WithChunkReading,
    WithBatchInserts,
    WithLimit
{
    use Importable, SkipsErrors;


    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if ($row->filter()->isEmpty()) continue;
            
            $name        = $row->get('name');
            $currency       = Currency::find($row->get('currency'));
            $amount = $row->get('amount');
            $account = Account::where('name', 'Trip Expense')->first();
            


            $expense = Expense::firstOrNew(['name' => $name]);

            $expense->user_id     = Auth::user()->id;
            $expense->currency_id     = $currency ?->id;
            $expense->account_id     = $account ?->id;
            $expense->amount     = $amount;
            $expense->type     = "Direct";
            $expense->status     = 1;
            $expense->save();
        }
    }

    public function rules(): array
    {
         return[
            '*.name' => ['required'],
        ];
    }

    public function chunkSize(): int
    {
        return 50;
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function limit(): int
    {
        return 2500;
    }
}
