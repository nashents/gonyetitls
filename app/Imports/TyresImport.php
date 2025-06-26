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
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TyresImport implements  ToCollection, WithLimit,
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
        return 500; // Import only the first 100 rows
    }

    public function assignTyre(Collection $row, $horse, $trailer, $vehicle, $tyre)
        {
            if (!isset($row['horse_reg_number']) && !isset($row['vehicle_reg_number']) && !isset($row['trailer_reg_number'])) {
                return;
            }

            $tyre = $tyre;
            $tyreAssignment = $tyre->tyre_assignment;

            $assignment = isset($tyreAssignment)
                ? TyreAssignment::find($tyreAssignment->id)
                : new TyreAssignment;

            if (!isset($tyreAssignment)) {
                $assignment->user_id = Auth::user()->id;
            }

            $assignment->tyre_id = $tyre->id;

            if (isset($row['horse_reg_number']) && $horse) {
                $assignment->type = 'Horse';
                $assignment->horse_id = $horse->id;
            } elseif (isset($row['vehicle_reg_number']) && $vehicle) {
                $assignment->type = 'Vehicle';
                $assignment->vehicle_id = $vehicle->id;
            } elseif (isset($row['trailer_reg_number']) && $trailer) {
                $assignment->type = 'Trailer';
                $assignment->trailer_id = $trailer->id;
            }

            $assignment->date_fitted = $this->parseExcelDate($row['date_fitted']);
            $assignment->starting_odometer = $row['fitting_mileage'];
            $assignment->current_mileage = $row['current_mileage'];
            $assignment->position = $row['position'];
            $assignment->axle = $row['axle'];
            $assignment->status = 1;

            isset($tyreAssignment) ? $assignment->update() : $assignment->save();

            $movement = Movement::firstOrNew(['tyre_assignment_id' => $assignment->id]);

            $movement->user_id = $assignment->user_id;
            $movement->tyre_id = $assignment->tyre_id;
            
            if (isset($row['horse_reg_number']) && $horse) {
                $movement->location = 'Horse';
                $movement->horse_id = $horse->id;
            } elseif (isset($row['vehicle_reg_number']) && $vehicle) {
                $movement->location = 'Vehicle';
                $movement->vehicle_id = $vehicle->id;
            } elseif (isset($row['trailer_reg_number']) && $trailer) {
                $movement->location = 'Trailer';
                $movement->trailer_id = $trailer->id;
            }
            
            $movement->current_mileage = $row['current_mileage'];
            $movement->mileage_moved = $row['fitting_mileage'];
            $movement->date = $this->parseExcelDate($row['date_fitted']);
            $movement->save();


            if ($assignment) {
                $tyreDispatch = $assignment->tyre_dispatch;

                $dispatch = isset($tyreDispatch)
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

                isset($tyreDispatch) ? $dispatch->update() : $dispatch->save();
            }

            $tyre = Tyre::find($tyre->id);
            $tyre->status = 0;
            $tyre->disposed = 0;
            $tyre->update();

           
        }


    public function collection(Collection $rows)
    {
       foreach($rows as $row){

                if($row->filter()->isNotEmpty()){     
                        
                    $store = null;
                    $currency = null;
                    $product = null;
                    $category = null;
                    $horse = null;
                    $vehicle = null;
                    $trailer = null;
                    // Lookup related assets
                    $horse   = Horse::where('registration_number', 'LIKE', '%' . $row['horse_reg_number'] . '%')->first();
                    $vehicle = Vehicle::where('registration_number', 'LIKE', '%' . $row['vehicle_reg_number'] . '%')->first();
                    $trailer = Trailer::where('registration_number', 'LIKE', '%' . $row['trailer_reg_number'] . '%')->first();
                    
                    // Handle Product (with fallback if not found)
                    $productName = trim($row['product_name']);
                    $product = Product::where('department', 'tyre')
                        ->where('name',$productName)
                        ->first() ?? new Product(['department' => 'tyre', 'name' => $productName]);
                    
                    if (!$product->exists) {
                        $product->user_id = Auth::id();
                        $product->product_number = $this->productNumber();

                        if (filled($row['category'])) {
                            $categoryName = trim($row['category']);
                            if (filled($categoryName)) {
                                $category = Category::firstOrCreate(['name' => $categoryName], ['status' => 1]);
                                $product->category_id = $category->id;
                            }
                        }
                      
                        if (filled($row['brand_name'])) {
                            $brandName = trim($row['brand_name']);
                            if (filled($brandName)) {
                                $brand = Brand::firstOrCreate(['name' => $brandName], ['status' => 1]);
                                $product->brand_id =   $brand->id;
                            }
                        }
                       
                        $product->status = 1;
                        $product->save();
                    }
                    
                    // Handle Currency
                    $currency = Currency::where('name', 'LIKE', '%' . $row['currency'] . '%')->first();
                    
                    if (filled($row['store_name'])) {
                        // Handle Store
                   $storeName = trim($row['store_name']);
                   if (filled($storeName)) {
                       $store = Store::firstOrCreate(['name' => $storeName], ['status' => 1]);
                   }
                   }

                    // Handle Tyre
                    $serial_number = trim($row['serial_number']);
                    $tyre = Tyre::firstOrNew(['serial_number' => $serial_number]);
                    $tyre->fill([
                        'currency_id'              => $currency ? $currency->id : null,
                        'store_id'                 => $store ? $store->id : null,
                        'product_id'               => $product ? $product->id : null,
                        'serial_number'            => $serial_number,
                        'amount'                   => $row['unit_price'],
                        'subtotal'                 => $row['unit_price'],
                        'subtotal_incl'            => $row['unit_price'],
                        'total'                    => $row['unit_price'],
                        'type'                     => $row['type'],
                        'width'                    => $row['width'],
                        'aspect_ratio'             => $row['aspect_ratio'],
                        'diameter'                 => $row['diameter'],
                        'qty'                      => 1,
                        'purchase_date'            => $this->parseExcelDate($row['purchase_date']),
                        'status'                   => 1,
                        'disposed'                 => 0,
                    ]);
                    
                    $tyre->save();
                    
                    // Assign the tyre to something (horse/trailer/etc.)
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