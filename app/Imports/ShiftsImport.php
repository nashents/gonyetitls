<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Shift;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ShiftType;
use App\Models\Transporter;
use App\Models\DeliveryNote;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

class ShiftsImport implements ToCollection, SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use Importable, SkipsErrors;

    public $for;

    public function __construct($for)
    {
        $this->for = $for;
    }

    private function parseExcelDate($value)
    {
        if (!isset($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                );
            } catch (\Exception $e) {
                return null;
            }
        }

        if (is_string($value)) {
            try {
                $parsed = Carbon::createFromFormat('Y-m-d', $value);
                return $parsed && $parsed->format('Y-m-d') === $value ? $parsed : null;
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    private function parseExcelTime($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                // Excel stores time as fraction of a day (e.g. 0.5 = 12:00 PM)
                $time = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $time->format('H:i:s');
            }

            // If it is already a string like '1:10:00 AM'
            $parsed = Carbon::createFromFormat('g:i:s A', $value);
            return $parsed->format('H:i:s');

        } catch (\Exception $e) {
            return null; // fallback
        }
    }

    public function limit(): int
    {
        return 2500;
    }

    public function collection(Collection $rows)
    {
        $userId = Auth::id();

        foreach ($rows as $row) {
            if ($row->filter()->isNotEmpty()) {

                $customer = Customer::where('name', $row->get('customer'))->first();
                $horse = Horse::where('fleet_number', $row->get('horse'))->first();
                $cargo = Cargo::where('name', $row->get('cargo'))->first();

                $employee = Employee::where('surname', $row->get('driver'))->first();
                
                $driver = $employee?->driver;

                $date = $this->parseExcelDate($row->get('date'))?->format('Y-m-d');

                Shift::create([
                    'user_id' => $userId,
                    'type' => match ($row->get('shift')) {
                        'Morning' => 'Day',
                        'Night' => 'Night',
                        default => null
                    },
                    'date' => $date,
                    'shift_start_time' => $this->parseExcelTime($row->get('shift_start')),
                    'shift_end_time' => $this->parseExcelTime($row->get('shift_close')),
                    'horse_id' => $horse?->id,
                    'driver_id' => $driver?->id,
                    'customer_id' => $customer?->id,
                    'cargo_id' => $cargo?->id,
                    'actual_mileage' => $row->get('actual_mileage'),
                    'calculated_mileage' => $row->get('cal_mileage'),
                    'open_mileage' => $row->get('open_mileage'),
                    'close_mileage' => $row->get('close_mileage'),
                    'fuel_consumption_mileage' => $row->get('consumption'),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            // Add validation rules if needed
        ];
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function chunkSize(): int
    {
        return 50;
    }
}
