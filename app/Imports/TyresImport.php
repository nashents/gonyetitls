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


    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function limit(): int
    {
        return 500; // Import only the first 100 rows
    }

    public function collection(Collection $rows)
    {
       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){     
    
        $this->tyre = Tyre::where('serial_number','LIKE','%'.$row['serial_number'].'%')->first();  
       
        $brandName = trim($row['brand_name']);
        if (filled($brandName)) {
            $brand = Brand::firstOrCreate(
                ['name' => $brandName], 
                ['status' => 1]
            );
            $this->brand = $brand;
        }
        
        $storeName = trim($row['store_name']);
        if (filled($storeName)) {
            $store = Store::firstOrCreate(
                ['name' => $storeName], 
                ['status' => 1]
            );
            $this->store = $store;
        }
        $categoryName = trim($row['category']);
        if (filled($categoryName)) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName], 
                ['status' => 1]
            );
            $this->category = $category;
        }
     
      
        
        $this->horse = Horse::where('registration_number','LIKE','%'.$row['horse_reg_number'].'%')->first();  
        $this->vehicle = Vehicle::where('registration_number','LIKE','%'. $row['vehicle_reg_number'].'%')->first();
        $this->trailer = Trailer::where('registration_number','LIKE','%'. $row['trailer_reg_number'].'%')->first();
        $this->product = Product::where('department','tyre')->where('name','Like', '%'.$row['product_name'].'%')->first();
        $this->currency = Currency::where('name','LIKE','%'.$row['currency'].'%')->first();
        

        if (isset($this->tyre)) {

            if (isset($this->product)) {

                $tyre = Tyre::find($this->tyre->id);

                if ($this->currency) {
                    $tyre->currency_id = $this->currency->id;
                }

                if ($this->store) {
                    $tyre->store_id = $this->store->id;
                }

                $tyre->product_id = $this->product->id;
                $tyre->serial_number = $row['serial_number'];
                $tyre->amount = $row['unit_price'];
                $tyre->subtotal = $row['unit_price'];
                $tyre->subtotal_incl = $row['unit_price'];
                $tyre->total = $row['unit_price'];
                $tyre->type = $row['type'];
                $tyre->width = $row['width'];
                $tyre->aspect_ratio = $row['aspect_ratio'];
                $tyre->diameter = $row['diameter'];
                $tyre->qty = 1;
                // if(filled($row['purchase_date'])){
                //     $dateString = $row['purchase_date'];
                //     $dateTimeObject = new DateTime($dateString);
                //     $tyre->purchase_date = isset($dateTimeObject) ?  Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateTimeObject)->format('Y-m-d')) : Null;
                // }
                $tyre->status = 1;
                $tyre->disposed = 0;
                $tyre->update();

                if (isset($row['horse_reg_number']) || isset($row['vehicle_reg_number']) || isset($row['trailer_reg_number'])) {

                    $assignment = $tyre->tyre_assignment;

                    if (isset($assignment)){  
                            $assignment->tyre_id = $this->tyre->id;
                            if(isset($row['horse_reg_number'])){
                                $assignment->type = "Horse";
                                  if ($this->horse) {
                                    $assignment->horse_id = $this->horse->id;
                                }
                            }elseif(isset($row['vehicle_reg_number'])){
                                $assignment->type = "Vehicle";
                                 if ($this->vehicle) {
                                    $assignment->vehicle_id = $this->vehicle->id;
                                }
                            }elseif(isset($row['trailer_reg_number'])){
                                $assignment->type = "Trailer";
                                 if ($this->trailer) {
                                    $assignment->trailer_id = $this->trailer->id;
                                }
                            }
                            $assignment->starting_odometer = $row['starting_mileage'];
                            $assignment->position = $row['position'];
                            $assignment->axle = $row['axle'];
                            $assignment->status = 1;
                            $assignment->update();

                            if (isset($assignment)) {
                                $tyre_dispatch = $assignment->tyre_dispatch;

                                if (isset($tyre_dispatch)) {
                                    $dispatch = TyreDispatch::find($tyre_dispatch->id);
                                    $dispatch->tyre_assignment_id = $assignment->id;
                                    $dispatch->tyre_id = $this->tyre->id;
                                    $dispatch->tyre_number = $this->tyre->tyre_number;
                                    $dispatch->serial_number = $this->tyre->serial_number;
                                    $dispatch->width = $this->tyre->width;
                                    $dispatch->aspect_ratio = $this->tyre->aspect_ratio;
                                    $dispatch->diameter =  $this->tyre->diameter;
                                      if ($this->horse) {
                                        $dispatch->horse_id = $this->horse->id;
                                    }elseif(isset($this->vehicle)){
                                        $dispatch->vehicle_id = $this->vehicle->id;
                                    }elseif(isset($this->trailer)){
                                        $dispatch->trailer_id = $this->trailer->id;
                                    }
                                    $dispatch->update();
                                }else {
                                    $dispatch = new TyreDispatch;
                                    $dispatch->tyre_assignment_id = $assignment->id;
                                    $dispatch->tyre_id = $this->tyre->id;
                                    $dispatch->tyre_number = $this->tyre->tyre_number;
                                    $dispatch->serial_number = $this->tyre->serial_number;
                                    $dispatch->width = $this->tyre->width;
                                    $dispatch->aspect_ratio = $this->tyre->aspect_ratio;
                                    $dispatch->diameter =  $this->tyre->diameter;
                                      if ($this->horse) {
                                        $dispatch->horse_id = $this->horse->id;
                                    }elseif(isset($this->vehicle)){
                                        $dispatch->vehicle_id = $this->vehicle->id;
                                    }elseif(isset($this->trailer)){
                                        $dispatch->trailer_id = $this->trailer->id;
                                    }
                                    $dispatch->save();
                                }
                        
                            }
        
                            $tyre = Tyre::find($this->tyre->id);
                            $tyre->status = 0;
                            $tyre->disposed = 0;
                            $tyre->update();

                    }else {
                            $assignment = new TyreAssignment;
                            $assignment->user_id = Auth::user()->id;
                            $assignment->tyre_id = $this->tyre->id;

                            if(isset($row['horse_reg_number'])){
                                $assignment->type = "Horse";
                                  if ($this->horse) {
                                    $assignment->horse_id = $this->horse->id;
                                }
                            }elseif(isset($row['vehicle_reg_number'])){
                                $assignment->type = "Vehicle";
                                 if ($this->vehicle) {
                                    $assignment->vehicle_id = $this->vehicle->id;
                                }
                            }elseif(isset($row['trailer_reg_number'])){
                                $assignment->type = "Trailer";
                                 if ($this->trailer) {
                                    $assignment->trailer_id = $this->trailer->id;
                                }
                            }

                            $assignment->starting_odometer = $row['starting_mileage'];
                            $assignment->position = $row['position'];
                            $assignment->axle = $row['axle'];
                            $assignment->status = 1;
                            $assignment->save();

                            if (isset($assignment)) {
                                $dispatch = new TyreDispatch;
                                $dispatch->tyre_assignment_id = $assignment->id;
                                $dispatch->tyre_id = $this->tyre->id;
                                $dispatch->tyre_number = $this->tyre->tyre_number;
                                $dispatch->serial_number = $this->tyre->serial_number;
                                $dispatch->width = $this->tyre->width;
                                $dispatch->aspect_ratio = $this->tyre->aspect_ratio;
                                $dispatch->diameter =  $this->tyre->diameter;

                                  if ($this->horse) {
                                    $dispatch->horse_id = $this->horse->id;
                                }elseif(isset($this->vehicle)){
                                    $dispatch->vehicle_id = $this->vehicle->id;
                                }elseif(isset($this->trailer)){
                                    $dispatch->trailer_id = $this->trailer->id;
                                }
                                $dispatch->save();
                            }
        
                    
                            $tyre = Tyre::find($tyre->id);
                            $tyre->status = 0;
                            $tyre->disposed = 0;
                            $tyre->update();

                    }

                }

            }
            // new product of exsting tyre
            else {
       
                $product = new Product;
                $product->user_id = Auth::user()->id;
                $product->product_number = $this->productNumber();
                if ($this->category) {
                    $product->category_id = $this->category->id;
                }

                if ($this->brand) {
                    $product->brand_id = $this->brand->id;
                }
               
                $product->name = $row['product_name'];
                $product->department = 'tyre';
                $product->status = '1';
                $product->save(); 
                 
                $tyre = Tyre::find($this->tyre->id);

                if ($this->currency) {
                    $tyre->currency_id = $this->currency->id;
                }
                if ($this->store) {
                    $tyre->store_id = $this->store->id;
                }
                
                $tyre->product_id = $product->id;
                $tyre->serial_number = $row['serial_number'];
                $tyre->amount = $row['unit_price'];
                $tyre->subtotal = $row['unit_price'];
                $tyre->subtotal_incl = $row['unit_price'];
                $tyre->total = $row['unit_price'];
                $tyre->type = $row['type'];
                $tyre->width = $row['width'];
                $tyre->aspect_ratio = $row['aspect_ratio'];
                $tyre->diameter = $row['diameter'];
                $tyre->qty = 1;
                // if(filled($row['purchase_date'])){
                //     $dateString = $row['purchase_date'];
                //     $dateTimeObject = new DateTime($dateString);
                //     $tyre->purchase_date = isset($dateTimeObject) ?  Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateTimeObject)->format('Y-m-d')) : Null;
                // }
                $tyre->status = 1;
                $tyre->disposed = 0;
                $tyre->update();

                    if (isset($row['horse_reg_number']) || isset($row['vehicle_reg_number']) || isset($row['trailer_reg_number'])) {

                            $tyre_assignment = $this->tyre->tyre_assignment;
                            
                            if (isset($tyre_assignment)) {
                            
                            $assignment = TyreAssignment::find($tyre_assignment->id);
                            $assignment->tyre_id = $this->tyre->id;

                            if(isset($row['horse_reg_number'])){
                                $assignment->type = "Horse";
                                  if ($this->horse) {
                                    $assignment->horse_id = $this->horse->id;
                                }
                            }elseif(isset($row['vehicle_reg_number'])){
                                $assignment->type = "Vehicle";
                                 if ($this->vehicle) {
                                    $assignment->vehicle_id = $this->vehicle->id;
                                }
                            }elseif(isset($row['trailer_reg_number'])){
                                $assignment->type = "Trailer";
                                 if ($this->trailer) {
                                    $assignment->trailer_id = $this->trailer->id;
                                }
                            }

                            $assignment->starting_odometer = $row['starting_mileage'];
                            $assignment->position = $row['position'];
                            $assignment->axle = $row['axle'];
                            $assignment->status = 1;
                            $assignment->update();

                            if (isset($assignment)) {
                                $tyre_dispatch = $assignment->tyre_dispatch;

                                if (isset($tyre_dispatch)) {
                                    $dispatch = TyreDispatch::find($tyre_dispatch->id);
                                    $dispatch->tyre_assignment_id = $assignment->id;
                                    $dispatch->tyre_id = $this->tyre->id;
                                    $dispatch->tyre_number = $this->tyre->tyre_number;
                                    $dispatch->serial_number = $this->tyre->serial_number;
                                    $dispatch->width = $this->tyre->width;
                                    $dispatch->aspect_ratio = $this->tyre->aspect_ratio;
                                    $dispatch->diameter =  $this->tyre->diameter;
                                      if ($this->horse) {
                                        $dispatch->horse_id = $this->horse->id;
                                    }elseif(isset($this->vehicle)){
                                        $dispatch->vehicle_id = $this->vehicle->id;
                                    }elseif(isset($this->trailer)){
                                        $dispatch->trailer_id = $this->trailer->id;
                                    }
                                    $dispatch->update();
                                }else{
                                    $dispatch =  new TyreDispatch;
                                    $dispatch->tyre_assignment_id = $assignment->id;
                                    $dispatch->tyre_id = $this->tyre->id;
                                    $dispatch->tyre_number = $this->tyre->tyre_number;
                                    $dispatch->serial_number = $this->tyre->serial_number;
                                    $dispatch->width = $this->tyre->width;
                                    $dispatch->aspect_ratio = $this->tyre->aspect_ratio;
                                    $dispatch->diameter =  $this->tyre->diameter;
                                      if ($this->horse) {
                                        $dispatch->horse_id = $this->horse->id;
                                    }elseif(isset($this->vehicle)){
                                        $dispatch->vehicle_id = $this->vehicle->id;
                                    }elseif(isset($this->trailer)){
                                        $dispatch->trailer_id = $this->trailer->id;
                                    }
                                    $dispatch->save();
                                }
                        
                            }
        
                            $tyre = Tyre::find($this->tyre->id);
                            $tyre->status = 0;
                            $tyre->disposed = 0;
                            $tyre->update();

                            }else {
                            
                            $assignment = new TyreAssignment;
                            $assignment->user_id = Auth::user()->id;
                            $assignment->tyre_id = $this->tyre->id;

                            if(isset($row['horse_reg_number'])){
                                $assignment->type = "Horse";
                                  if ($this->horse) {
                                    $assignment->horse_id = $this->horse->id;
                                }
                            }elseif(isset($row['vehicle_reg_number'])){
                                $assignment->type = "Vehicle";
                                 if ($this->vehicle) {
                                    $assignment->vehicle_id = $this->vehicle->id;
                                }
                            }elseif(isset($row['trailer_reg_number'])){
                                $assignment->type = "Trailer";
                                 if ($this->trailer) {
                                    $assignment->trailer_id = $this->trailer->id;
                                }
                            }

                            $assignment->starting_odometer = $row['starting_mileage'];
                            $assignment->position = $row['position'];
                            $assignment->axle = $row['axle'];
                            $assignment->status = 1;
                            $assignment->save();

                            if (isset($assignment)) {
                                $dispatch = new TyreDispatch;
                                $dispatch->tyre_assignment_id = $assignment->id;
                                $dispatch->tyre_id = $this->tyre->id;
                                $dispatch->tyre_number = $this->tyre->tyre_number;
                                $dispatch->serial_number = $this->tyre->serial_number;
                                $dispatch->width = $this->tyre->width;
                                $dispatch->aspect_ratio = $this->tyre->aspect_ratio;
                                $dispatch->diameter =  $this->tyre->diameter;

                                  if ($this->horse) {
                                    $dispatch->horse_id = $this->horse->id;
                                }elseif(isset($this->vehicle)){
                                    $dispatch->vehicle_id = $this->vehicle->id;
                                }elseif(isset($this->trailer)){
                                    $dispatch->trailer_id = $this->trailer->id;
                                }
                                $dispatch->save();
                            }
        
                    
                            $tyre = Tyre::find($tyre->id);
                            $tyre->status = 0;
                            $tyre->disposed = 0;
                            $tyre->update();

                            }

                    }
 
            }

        }
        // new tyre
        else {

        
            if (isset($this->product)) {
               
                $tyre = new Tyre;
                $tyre->user_id = Auth::user()->id;
                $tyre->tyre_number = $this->tyreNumber();

                if ($this->currency) {
                    $tyre->currency_id = $this->currency->id;
                }
                if ($this->store) {
                    $tyre->store_id = $this->store->id;
                }
                
                $tyre->product_id = $this->product->id;
                $tyre->serial_number = $row['serial_number'];
                $tyre->amount = $row['unit_price'];
                $tyre->subtotal = $row['unit_price'];
                $tyre->subtotal_incl = $row['unit_price'];
                $tyre->total = $row['unit_price'];
                $tyre->type = $row['type'];
                $tyre->width = $row['width'];
                $tyre->aspect_ratio = $row['aspect_ratio'];
                $tyre->diameter = $row['diameter'];
                $tyre->qty = 1;
                // if(filled($row['purchase_date'])){
                //     $dateString = $row['purchase_date'];
                //     $dateTimeObject = new DateTime($dateString);
                //     $tyre->purchase_date = isset($dateTimeObject) ?  Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateTimeObject)->format('Y-m-d')) : Null;
                // }
                $tyre->status = 1;
                $tyre->disposed = 0;
                $tyre->save();

                if (isset($row['horse_reg_number']) || isset($row['vehicle_reg_number']) || isset($row['trailer_reg_number'])) {

                    $assignment = new TyreAssignment;
                    $assignment->user_id = Auth::user()->id;
                    $assignment->tyre_id = $tyre->id;

                    if(isset($row['horse_reg_number'])){
                        $assignment->type = "Horse";
                          if ($this->horse) {
                            $assignment->horse_id = $this->horse->id;
                        }
                    }elseif(isset($row['vehicle_reg_number'])){
                        $assignment->type = "Vehicle";
                         if ($this->vehicle) {
                            $assignment->vehicle_id = $this->vehicle->id;
                        }
                    }elseif(isset($row['trailer_reg_number'])){
                        $assignment->type = "Trailer";
                         if ($this->trailer) {
                            $assignment->trailer_id = $this->trailer->id;
                        }
                    }

                    $assignment->starting_odometer = $row['starting_mileage'];
                    $assignment->position = $row['position'];
                    $assignment->axle = $row['axle'];
                    $assignment->status = 1;
                    $assignment->save();

                    if (isset($assignment)) {
                        $tyre = Tyre::find($tyre->id);
                        $dispatch = new TyreDispatch;
                        $dispatch->tyre_assignment_id = $assignment->id;
                        $dispatch->tyre_id = $tyre->id;
                        $dispatch->tyre_number = $tyre->tyre_number;
                        $dispatch->serial_number = $tyre->serial_number;
                        $dispatch->width = $tyre->width;
                        $dispatch->aspect_ratio = $tyre->aspect_ratio;
                        $dispatch->diameter =  $tyre->diameter;

                          if ($this->horse) {
                            $dispatch->horse_id = $this->horse->id;
                        }elseif(isset($this->vehicle)){
                            $dispatch->vehicle_id = $this->vehicle->id;
                        }elseif(isset($this->trailer)){
                            $dispatch->trailer_id = $this->trailer->id;
                        }
                       
                        $dispatch->save();
                    }
  
            
                    $tyre = Tyre::find($tyre->id);
                    $tyre->status = 0;
                    $tyre->disposed = 0;
                    $tyre->update();
                }

            }else {
            
                $product = new Product;
                $product->user_id = Auth::user()->id;
                $product->product_number = $this->productNumber();
                if ($this->category) {
                    $product->category_id = $this->category->id;
                }

                if ($this->brand) {
                    $product->brand_id = $this->brand->id;
                }
               
                $product->name = $row['product_name'];
                $product->department = 'tyre';
                $product->status = '1';
                $product->save(); 
                 
              
                $tyre = new Tyre;
                $tyre->user_id = Auth::user()->id;
                $tyre->tyre_number = $this->tyreNumber();

                 if ($this->currency) {
                    $tyre->currency_id = $this->currency->id;
                }

                if ($this->store) {
                    $tyre->store_id = $this->store->id;
                }
                

                $tyre->product_id = $product->id;
                $tyre->serial_number = $row['serial_number'];
                $tyre->amount = $row['unit_price'];
                $tyre->subtotal = $row['unit_price'];
                $tyre->subtotal_incl = $row['unit_price'];
                $tyre->total = $row['unit_price'];
                $tyre->type = $row['type'];
                $tyre->width = $row['width'];
                $tyre->aspect_ratio = $row['aspect_ratio'];
                $tyre->diameter = $row['diameter'];
                $tyre->qty = 1;
                // if(filled($row['purchase_date'])){
                //     $dateString = $row['purchase_date'];
                //     $dateTimeObject = new DateTime($dateString);
                //     $tyre->purchase_date = isset($dateTimeObject) ?  Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateTimeObject)->format('Y-m-d')) : Null;
                // }
                $tyre->status = 1;
                $tyre->disposed = 0;
                $tyre->save();

                if (isset($row['horse_reg_number']) || isset($row['vehicle_reg_number']) || isset($row['trailer_reg_number'])) {

                    $assignment = new TyreAssignment;
                    $assignment->user_id = Auth::user()->id;
                    $assignment->tyre_id = $tyre->id;

                    if(isset($row['horse_reg_number'])){
                        $assignment->type = "Horse";
                          if ($this->horse) {
                            $assignment->horse_id = $this->horse->id;
                        }
                    }elseif(isset($row['vehicle_reg_number'])){
                        $assignment->type = "Vehicle";
                         if ($this->vehicle) {
                            $assignment->vehicle_id = $this->vehicle->id;
                        }
                    }elseif(isset($row['trailer_reg_number'])){
                        $assignment->type = "Trailer";
                         if ($this->trailer) {
                            $assignment->trailer_id = $this->trailer->id;
                        }
                    }

                    $assignment->starting_odometer = $row['starting_mileage'];
                    $assignment->position = $row['position'];
                    $assignment->axle = $row['axle'];
                    $assignment->status = 1;
                    $assignment->save();
            
                    $tyre = Tyre::find($tyre->id);
                    
                    $dispatch = new TyreDispatch;
                    $dispatch->tyre_assignment_id = $assignment->id;
                    $dispatch->tyre_id = $tyre->id;
                    $dispatch->tyre_number = $tyre->tyre_number;
                    $dispatch->serial_number = $tyre->serial_number;
                    $dispatch->width = $tyre->width;
                    $dispatch->aspect_ratio = $tyre->aspect_ratio;
                    $dispatch->diameter =  $tyre->diameter;

                      if ($this->horse) {
                        $dispatch->horse_id = $this->horse->id;
                    }elseif(isset($this->vehicle)){
                        $dispatch->vehicle_id = $this->vehicle->id;
                    }elseif(isset($this->trailer)){
                        $dispatch->trailer_id = $this->trailer->id;
                    }
                   
                    $dispatch->save();
            
                    $tyre = Tyre::find($tyre->id);
                    $tyre->status = 0;
                    $tyre->disposed = 0;
                    $tyre->update();
                }
 
            }
        }
    
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