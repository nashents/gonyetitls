<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Shift;
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
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ShiftsImport implements ToCollection, SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts,
WithCalculatedFormulas
{
    use Importable, SkipsErrors;

    public $for;
    protected $company;
    protected $initialShiftId;

    public function __construct($for)
    {
        $this->company = Auth::user()->employee->company;
        $this->initialShiftId = Shift::max('id') ?? 0;
        $this->for = $for;
    }

    private function generateNumber($prefix, $id)
    {
        $initials = collect(explode(' ', $this->company->name))->map(fn($word) => $word[0])->implode('');
        return $initials . $prefix . str_pad($id + 1, 5, '0', STR_PAD_LEFT);
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

                $transporter = Null;
                $customer = Customer::where('name', $row->get('customer'))->first();
                $horse = Horse::where('fleet_number', $row->get('horse'))->first();
                if ($horse) {
                    $transporter = $horse->transporter;
                }
                $cargo = Cargo::where('name', $row->get('cargo'))->first();
                
                

                 // Loading Point IDs
                $loading_point_ids = [];
                $loading_points = explode(',', trim($row->get('loading_points') ?? ''));
                foreach ($loading_points as $lp) {
                    $loading_point = LoadingPoint::firstOrCreate(['name' => trim($lp)], ['status' => 1]);
                    // $loading_point = LoadingPoint::where('name', 'LIKE', '%' . trim($lp) . '%')->first();
                    if ($loading_point) {
                        $loading_point_ids[] = $loading_point->id;
                    }
                }
                 // Offloading Point IDs
                $offloading_point_ids = [];
                $offloading_points = explode(',', trim($row->get('offloading_points') ?? ''));
                foreach ($offloading_points as $op) {
                    $offloading_point = OffloadingPoint::firstOrCreate(['name' => trim($op)], ['status' => 1]);
                    // $offloading_point = OffloadingPoint::where('name', 'LIKE', '%' . trim($op) . '%')->first();
                    if ($offloading_point) {
                        $offloading_point_ids[] = $offloading_point->id;
                    }
                }

                $driver_name = trim($row->get('driver')); 
                $driver = null;
              

                if ($driver_name) {
                    // Use regex to split and clean up whitespace
                    $name_parts = preg_split('/\s+/', $driver_name, -1, PREG_SPLIT_NO_EMPTY);
                  
                    if (count($name_parts) >= 2) {
                        $name = $name_parts[0];
                        $surname = $name_parts[1] ?? $name_parts[2] ?? null;
                        if ($surname) {
                            $employee = Employee::where('name', 'LIKE', "%$name%")
                                ->where('surname', 'LIKE', "%$surname%")
                                ->first();
                            $driver = $employee?->driver;
                        }
                    }

                    if (count($name_parts) === 1) {
                      
                        $surname = $name_parts[0];
                        if ($surname) {
                              
                            $employee = Employee::where('surname', 'LIKE', "%$surname%")->first();
                         
                            $driver = $employee?->driver;

                            
                        }
                    }
                }

             
                 
                $date = $this->parseExcelDate($row->get('date'))?->format('Y-m-d');

                
                if ($this->for == "Trips") {

                    $shift = Shift::firstOrCreate(
                        [
                            'type' => $row->get('shift'),
                            'date' => $date,
                            'driver_id' => $driver?->id,
                        ],
                        [
                            'user_id' => $userId,
                            'shift_number' => $this->generateNumber('S', ++$this->initialShiftId),
                            'shift_start_time' => $this->parseExcelTime($row->get('shift_start')),
                            'shift_end_time' => $this->parseExcelTime($row->get('shift_close')),
                            'depart_workshop_time' => $this->parseExcelTime($row->get('depart_workshop')),
                            'horse_id' => $horse?->id,
                            'customer_id' => $customer?->id,
                            'transporter_id' => $transporter?->id,
                            'cargo_id' => $cargo?->id,
                            'actual_mileage' => $row->get('actual_mileage'),
                            'calculated_mileage' => $row->get('cal_mileage'),
                            'open_mileage' => $row->get('open_mileage'),
                            'close_mileage' => $row->get('close_mileage'),
                            'fuel_consumption_mileage' => $row->get('consumption'),
                            'equipment' => "Horse",
                            'total_loads' => $row->get('total_loads'),
                            'total_fuel' => $row->get('fuel'),
                            'authorization' => "approved",
                            'authorized_by_id' => Auth::id(),
                            'authorization_date' => date('Y-m-d'),
                            'status' => False,
                            'for' => $this->for,
                        ]
                    );

                }
                

                $shift->loading_points()->sync($loading_point_ids);
                $shift->offloading_points()->sync($offloading_point_ids);
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
        return 150;
    }

    public function chunkSize(): int
    {
        return 150;
    }
}
