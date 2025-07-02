<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Bin;
use App\Models\Rack;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Inventory;
use App\Models\CategoryValue;
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

class InventoriesImport implements ToCollection, SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use Importable, SkipsErrors;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public $company;
  
    public function __construct()
    {
        $this->company = Auth::user()->employee->company;
    }

    public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }

    public function inventoryNumber(){

        if (isset($this->company)) {
               $str = $this->company->name;
               $words = explode(' ', $str);
               if (isset($words[1][0])) {
                   $initials = $words[0][0].$words[1][0];
               }else {
                   $initials = $words[0][0];
               }
           }
   
               $inventory = Inventory::orderBy('id', 'desc')->first();
   
           if (!$inventory) {
               $inventory_number =  $initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
           }else {
               $number = $inventory->id + 1;
               $inventory_number =  $initials .'I'. str_pad($number, 5, "0", STR_PAD_LEFT);
           }
   
           return  $inventory_number;
   
   
       }
   
       
       public function productNumber(){
   
    if (isset($this->company)) {
               $str = $this->company->name;
               $words = explode(' ', $str);
               if (isset($words[1][0])) {
                   $initials = $words[0][0].$words[1][0];
               }else {
                   $initials = $words[0][0];
               }
           }
   
           $product = Product::where('department','inventory')->orderBy('id','desc')->first();
   
           if (!$product) {
               $product_number =  $initials .'IP'. str_pad(1, 5, "0", STR_PAD_LEFT);
           }else {
               $number = $product->id + 1;
               $product_number =  $initials .'IP'. str_pad($number, 5, "0", STR_PAD_LEFT);
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

    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

            $store = Null;
            $category = Null;
            $sub_category = Null;
            $brand = Null;
            $currency = Null;
            $rack = Null;
            $bin = Null;

            $storeName = trim($row['store_name']);
            if (filled($storeName)) {
                $store = Store::firstOrCreate(
                    ['name' => $storeName], 
                    ['status' => 1]
                );
              
            }
            $categoryName = trim($row['category']);
            if (filled($categoryName)) {
                $category = Category::firstOrCreate(
                    ['name' => $categoryName], 
                    ['status' => 1]
                );
              
            }
     
            $sub_categoryName = trim($row['sub_category']);
            if (filled($sub_categoryName)) {
                $sub_category = CategoryValue::firstOrCreate(
                    ['name' => $sub_categoryName], 
                    ['status' => 1]
                );
            }
         
            $brandName = trim($row['brand_name']);
            if (filled($brandName)) {
                $brand = Brand::firstOrCreate(
                    ['name' => $brandName], 
                    ['status' => 1]
                );
            }

            $rackNumber = trim($row['rack_number']);
            if (filled($rackNumber)) {
                $rack = Rack::firstOrCreate(
                    ['rack_number' => $rackNumber], 
                    ['status' => 1]
                );
            }
         
            $binNumber = trim($row['bin_number']);
            if (filled($binNumber)) {
                $bin = Bin::firstOrCreate(
                    ['bin_number' => $binNumber], 
                    ['status' => 1]
                );
            }
            
            $currencyName = trim($row['currency']);
            if (filled($currencyName)) {
                $currency = Currency::firstOrCreate(
                    ['name' => $currencyName], 
                    ['status' => 1]
                );
            }
          
            $product = Product::where('name',$row['product_name'])->first();
           

            if (isset($row['quantity']) && is_numeric($row['quantity']) && $row['quantity'] > 0 ) {
                for ($i= 0; $i < $row['quantity']; $i++) { 
                        if (isset($product)) {
                            $inventory = new Inventory;
                            $inventory->user_id = Auth::user()->id;
                            $inventory->inventory_number = $this->inventoryNumber();
                            $inventory->product_id = $product->id;
                            $inventory->subtotal = $row['unit_price'];
                            $inventory->qty = 1;
                            $inventory->amount = $row['unit_price'];
                            $inventory->subtotal_incl = $row['unit_price'];
                            $inventory->total = $row['unit_price'];

                            if ($currency) {
                                $inventory->currency_id = $currency->id;
                            }
                            if ($rack) {
                                $inventory->rack_id = $rack->id;
                            }
                            if ($bin) {
                                $inventory->bin_id = $bin->id;
                            }
                            if ($store) {
                                $inventory->store_id = $store->id;
                            }
                            $inventory->purchase_date = $this->parseExcelDate($row['purchase_date']);
                            $inventory->weight = $row['item_contents'] ? $row['item_contents'] : 1;
                            $inventory->balance = $row['balance'] ? $row['balance'] : 1;
                            $inventory->status = 1;
                            $inventory->save();
        
                        }else {
                        
                            $product = new Product;
                            $product->user_id = Auth::user()->id;
                            $product->unit_of_measure = $row['unit_of_measure'];
                             if ($category) {
                                $product->category_id = $category->id;
                            }
                            
                            if ($sub_category) {
                                $product->category_value_id = $sub_category->id;
                            }
                            
                            if ($brand) {
                                $product->brand_id = $brand->id;
                            }
                            $product->status = 1;
                            $product->buy = 1;
                            $product->name = $row['product_name'];
                            $product->product_number = $this->productNumber();
                            $product->department = 'inventory';
                            $product->identification_number = $row['part_number'];
                            $product->status = '1';
                            $product->save(); 
                            
                            $inventory = new Inventory;
                            $inventory->user_id = Auth::user()->id;
                            $inventory->inventory_number = $this->inventoryNumber();
                            $inventory->product_id = $product->id;
                            $inventory->amount = $row['unit_price'];
                            $inventory->subtotal = $row['unit_price'];
                            $inventory->qty = 1;
                            $inventory->subtotal_incl = $row['unit_price'];
                            $inventory->total = $row['unit_price'];
                            if ($currency) {
                                $inventory->currency_id = $currency->id;
                            }
                              if ($rack) {
                                $inventory->rack_id = $rack->id;
                            }
                            if ($bin) {
                                $inventory->bin_id = $bin->id;
                            }
                            if ($store) {
                                $inventory->store_id = $store->id;
                            }
                            $inventory->purchase_date = $this->parseExcelDate($row['purchase_date']);
                            $inventory->weight = $row['item_contents'] ? $row['item_contents'] : 1;
                            $inventory->balance = $row['balance'] ? $row['balance'] : 1;
                            $inventory->status = 1 ;
                            $inventory->save();
            
                        }
                }
               
             }
           
            
    }
       }
    }

    public function rules(): array{
        return[
            // '*.qty' => ['required'],
            // '*.weight' => ['required'],
            // '*.balance' => ['required'],
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
