<?php

namespace App\Imports;

use Carbon\Carbon;
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
    public $trailer_ids;
    public $store;
    public $category;
    public $sub_category;
    public $brand;
    public $currency;

    public function __construct()
    {
        $this->company = Auth::user()->employee->company;
    }

    public function limit(): int
    {
        return 500; // Import only the first 100 rows
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

    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

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
     
            $sub_categoryName = trim($row['sub_category']);
            if (filled($sub_categoryName)) {
                $sub_category = CategoryValue::firstOrCreate(
                    ['name' => $sub_categoryName], 
                    ['status' => 1]
                );
                $this->sub_category = $sub_category;
            }
         
            $brandName = trim($row['brand_name']);
            if (filled($brandName)) {
                $brand = Brand::firstOrCreate(
                    ['name' => $brandName], 
                    ['status' => 1]
                );
                $this->brand = $brand;
            }
            
            $currencyName = trim($row['currency']);
            if (filled($currencyName)) {
                $currency = Currency::firstOrCreate(
                    ['name' => $currencyName], 
                    ['status' => 1]
                );
                $this->currency = $currency;
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
                            if ($this->currency) {
                                $inventory->currency_id = $this->currency->id;
                            }
                            if ($this->store) {
                                $inventory->store_id = $this->store->id;
                            }
                            if($row['purchase_date'] != ""){
                                $inventory->purchase_date = isset($row['purchase_date']) ?  Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['purchase_date'])->format('Y-m-d')) : Null;
                            }
            
                            $inventory->weight = $row['item_contents'] ? $row['item_contents'] : 1;
                            $inventory->balance = $row['balance'] ? $row['balance'] : 1;
                            $inventory->measurement = $row['measurement'];
                            $inventory->status = 1;
                            $inventory->save();
        
                        }else {
                        
                            $product = new Product;
                            $product->user_id = Auth::user()->id;
        
                             if ($this->category) {
                                $product->category_id = $this->category->id;
                            }
                            
                            if ($this->sub_category) {
                                $product->category_value_id = $this->sub_category->id;
                            }
                            
                            if ($this->brand) {
                                $product->brand_id = $this->brand->id;
                            }
                            $product->status = 1;
                            $product->buy = 1;
                            $product->name = $row['product_name'];
                            $product->product_number = $this->productNumber();
                            $product->department = 'inventory';
                            $product->identification_number = $row['part_number'];
                            $product->description = $row['description'];
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
                            if ($this->currency) {
                                $inventory->currency_id = $this->currency->id;
                            }
                        
                            if ($this->store) {
                                $inventory->store_id = $this->store->id;
                            }

                            if($row['purchase_date'] != ""){
                                $inventory->purchase_date = isset($row['purchase_date']) ?  Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['purchase_date'])->format('Y-m-d')) : Null;
                            }
                           
            
                            $inventory->weight = $row['item_contents'] ? $row['item_contents'] : 1;
                            $inventory->balance = $row['balance'] ? $row['balance'] : 1;
                            $inventory->measurement = $row['measurement'];
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
            // '*.measurement' => ['required'],
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
