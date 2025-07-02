<?php

namespace App\Imports;

use DateTime;
use Carbon\Carbon;
use App\Models\Tyre;
use App\Models\Brand;
use App\Models\Horse;
use App\Models\Store;
use App\Models\Product;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Movement;
use App\Models\TyreDispatch;
use App\Models\TyreAssignment;
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

class TyresImport implements  ToCollection, SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use Importable, SkipsErrors;

    public $horse;
    public $trailer;
    public $vehicle;
    public $tyre;
    public $product;
    public $category;
    public $store;
    public $brand;
    public $currency;



    public function tyreNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $tyre = Tyre::orderBy('id', 'desc')->first();

        if (!$tyre) {
            $tyre_number =  $initials .'T'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $tyre->id + 1;
            $tyre_number =  $initials .'T'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $tyre_number;


    }

    
    public function productNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $product = Product::orderBy('id','desc')->first();

        if (!$product) {
            $product_number =  $initials .'P'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $product->id + 1;
            $product_number =  $initials .'P'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $product_number;


    }

      private function parseExcelDate($value)
        {
            if (!isset($value)) {
                return null;
            }

            // If it's a numeric Excel date serial
            if (is_numeric($value)) {
                try {
                    return Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    );
                } catch (\Exception $e) {
                    return null;
                }
            }

            // If it's a string in strict YYYY-MM-DD format
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


    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }

    
    public function assignTyre(Collection $row, $horse, $trailer, $vehicle, $tyre)
    {
        if (
            !$row->get('horse_reg_number') &&
            !$row->get('vehicle_reg_number') &&
            !$row->get('trailer_reg_number')
        ) {
            return;
        }

        $tyreAssignment = $tyre->tyre_assignment;
        $assignment = $tyreAssignment
            ? TyreAssignment::find($tyreAssignment->id)
            : new TyreAssignment;

        if (!$tyreAssignment) {
            $assignment->user_id = Auth::id();
        }

        $assignment->tyre_id = $tyre->id;

        // Determine type and assign relevant ID
        if ($row->get('horse_reg_number') && $horse) {
            $assignment->type = 'Horse';
            $assignment->horse_id = $horse->id;
        } elseif ($row->get('vehicle_reg_number') && $vehicle) {
            $assignment->type = 'Vehicle';
            $assignment->vehicle_id = $vehicle->id;
        } elseif ($row->get('trailer_reg_number') && $trailer) {
            $assignment->type = 'Trailer';
            $assignment->trailer_id = $trailer->id;
        }

        $assignment->date_fitted = $this->parseExcelDate($row->get('date_fitted'));
        $assignment->starting_odometer = $row->get('fitting_mileage');
        $assignment->current_mileage = $row->get('current_mileage');
        $assignment->position = $row->get('position');
        $assignment->axle = $row->get('axle');
        $assignment->status = 1;

        $assignment->save();

        // Record movement
        $movement = Movement::firstOrNew(['tyre_assignment_id' => $assignment->id]);
        $movement->user_id = $assignment->user_id;
        $movement->tyre_id = $assignment->tyre_id;

        if ($row->get('horse_reg_number') && $horse) {
            $movement->location = 'Horse';
            $movement->horse_id = $horse->id;
        } elseif ($row->get('vehicle_reg_number') && $vehicle) {
            $movement->location = 'Vehicle';
            $movement->vehicle_id = $vehicle->id;
        } elseif ($row->get('trailer_reg_number') && $trailer) {
            $movement->location = 'Trailer';
            $movement->trailer_id = $trailer->id;
        }

        $movement->current_mileage = $row->get('current_mileage');
        $movement->mileage_moved = $row->get('fitting_mileage');
        $movement->date = $this->parseExcelDate($row->get('date_fitted'));
        $movement->save();

        // Handle Dispatch
        $tyreDispatch = $assignment->tyre_dispatch;
        $dispatch = $tyreDispatch
            ? TyreDispatch::find($tyreDispatch->id)
            : new TyreDispatch;

        $dispatch->tyre_assignment_id = $assignment->id;
        $dispatch->tyre_id = $tyre->id;
        $dispatch->tyre_number = $tyre->tyre_number;
        $dispatch->serial_number = $tyre->serial_number;
        $dispatch->width = $tyre->width;
        $dispatch->aspect_ratio = $tyre->aspect_ratio;
        $dispatch->diameter = $tyre->diameter;

        if ($horse) {
            $dispatch->horse_id = $horse->id;
        } elseif ($vehicle) {
            $dispatch->vehicle_id = $vehicle->id;
        } elseif ($trailer) {
            $dispatch->trailer_id = $trailer->id;
        }

        $dispatch->save();

        // Final tyre status update
        $tyre->status = 0;
        $tyre->disposed = 0;
        $tyre->save();
    }


    public function collection(Collection $rows)
    {
       foreach($rows as $row){

                if($row->filter()->isNotEmpty()){     
                        
                    $store = $currency = $product = $category = $horse = $vehicle = $trailer = null;

                    // Lookup related assets
                    $horseReg = trim($row->get('horse_reg_number'));
                    $vehicleReg = trim($row->get('vehicle_reg_number'));
                    $trailerReg = trim($row->get('trailer_reg_number'));

                    $horse   = Horse::where('registration_number', 'LIKE', "%{$horseReg}%")->first();
                    $vehicle = Vehicle::where('registration_number', 'LIKE', "%{$vehicleReg}%")->first();
                    $trailer = Trailer::where('registration_number', 'LIKE', "%{$trailerReg}%")->first();

                    // Handle Product
                    $productName = trim($row->get('product_name'));
                    $product = Product::where('department', 'tyre')->where('name', $productName)->first();

                    if (!$product) {
                        $product = new Product([
                            'department' => 'tyre',
                            'name' => $productName,
                        ]);

                        $product->user_id = Auth::id();
                        $product->product_number = $this->productNumber();

                        $categoryName = trim($row->get('category'));
                        if (filled($categoryName)) {
                            $category = Category::firstOrCreate(['name' => $categoryName], ['status' => 1]);
                            $product->category_id = $category->id;
                        }

                        $brandName = trim($row->get('brand_name'));
                        if (filled($brandName)) {
                            $brand = Brand::firstOrCreate(['name' => $brandName], ['status' => 1]);
                            $product->brand_id = $brand->id;
                        }

                        $product->status = 1;
                        $product->save();
                    }

                    // Handle Currency
                    $currencyName = trim($row->get('currency'));
                    $currency = Currency::where('name', 'LIKE', "%{$currencyName}%")->first();

                    // Handle Store
                    $storeName = trim($row->get('store_name'));
                    if (filled($storeName)) {
                        $store = Store::firstOrCreate(['name' => $storeName], ['status' => 1]);
                    }

                    // Handle Tyre
                    $serial_number = trim($row->get('serial_number'));
                    if (empty($serial_number)) {
                        \Log::warning('Skipped tyre row due to empty serial number:', $row->toArray());
                        continue;
                    }

                    $unitPrice = (float) $row->get('unit_price', 0);

                    $tyre = Tyre::firstOrNew(['serial_number' => $serial_number]);
                    $tyre->fill([
                        'currency_id'              => $currency?->id,
                        'store_id'                 => $store?->id,
                        'product_id'               => $product?->id,
                        'serial_number'            => $serial_number,
                        'amount'                   => $unitPrice,
                        'subtotal'                 => $unitPrice,
                        'subtotal_incl'            => $unitPrice,
                        'total'                    => $unitPrice,
                        'type'                     => $row->get('type'),
                        'width'                    => $row->get('width'),
                        'aspect_ratio'             => $row->get('aspect_ratio'),
                        'diameter'                 => $row->get('diameter'),
                        'qty'                      => 1,
                        'purchase_date'            => $this->parseExcelDate($row->get('purchase_date')),
                        'status'                   => 1,
                        'disposed'                 => 0,
                    ]);
                    $tyre->save();

                    // Assign the tyre
                    $this->assignTyre($row, $horse, $trailer, $vehicle, $tyre);

                }
        }
    
    }

    public function rules(): array{
        return[
             '*.serial_number' => ['required'],
             '*.aspect_ratio' => ['required'],
             '*.diameter' => ['required'],
        ];
    }




    public function batchSize(): int
    {
        return 10;
    }

    public function chunkSize(): int
    {
        return 10;
    }
}