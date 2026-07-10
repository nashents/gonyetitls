<?php

namespace App\Http\Livewire\Dispatches;

use App\Imports\DispatchesImport;
use App\Mail\PendingNotificationEmails;
use App\Models\Account;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Movement;
use App\Models\Notification;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tax;
use App\Models\Ticket;
use App\Models\Tyre;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Validators\ValidationException ;

class Index extends Component
{

    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    public $from;
    public $to;
    protected $queryString = ['searchProduct','searchTicket','search'];
    public $dispatch_filter = "created_at";
    public $vendors;
    public $vendor_id;
    public $currencies;
    public $currency_id;
    public $searchProduct;
    public $searchTicket;

    private $dispatches;
    public $department;
 
    public $all_departments;
    public $asset_department_id;
    public $horse_id;
    public $dispatch_for = "inventory";
    public $trailer_id;
    public $vehicle_id;
    public $dispatch_id;
    public $branches;
    public $branch_id;
    public $tickets;
    public $ticket;
    public $selectedTicket;
    public $inventories;
    public $attach = false;
   
    public $tyres;
    public $assets;
    public $dispatch;
    public $selectedTyre = [];
    public $selectedInventory = [];
    public $selectedAsset = [];
    public $products;
    public $amount = [];
    public $qty = [];
    public $weight = [];
    public $selectedExpenseProduct = [];
    public $selectedProduct = [];
    public $requestedItem = [];
    public $ticket_requests;
    public $employees;
    public $company;
    public $all_tickets = False;
    public $exchange_amount;
    public $exchange_rate;
    public $max;
    public $max_weight;
    public $description;
    public $selectedEmployee;
    public $requested_by_id;
    public $all_products;
    public $date;
    public $stores;
    public $selectedStore;
    public $expand = False;
    

    public $payment_methods;
    public $payment_method_id;

    public $item_name;
    public $item_description;
    public $item_price;
    public $tax_id;
    public $tax;
    public $accounts;
    public $selectedAccount;
    public $tax_accounts;
    public $selectedTax = [];
    public $tax_rate = [];
    public $expense_account_id;
    public $sell = False;
    public $buy = True;
    public $importFile;

    public $inputs = [];
    public int $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        $this->inputs[] = $i;
    }

    public function remove($i)
    {
        $index = $this->inputs[$i]; // grab the $value (e.g. 2) before unsetting

        unset($this->inputs[$i]);

        // clean up all related state for that product slot
        unset($this->selectedProduct[$index]);
        unset($this->qty[$index]);
        unset($this->requestedItem[$index]);
        unset($this->fitment[$index]);
        unset($this->fitment_rows[$index]);
    }
  

    public $expenses_inputs = [];
    public $e = 1;
    public $f = 1;

    public function addExpense($e)
    {
        $e = $e + 1;
        $this->e = $e;
        $this->expenses_inputs[] = $e;
    }

        // Properties
    public array $fitment = [];       // [productIndex => bool]
    public array $fitment_rows = [];  // [productIndex => [rowIndex => [mileage, qty, position]]]

    // Add a fitment row for a specific product
    public function addFitmentRow(int $productIndex): void
    {
        $this->fitment_rows[$productIndex][] = [
            'mileage'  => '',
            'qty'      => '',
            'position' => '',
        ];
    }

    // Remove a specific fitment row
    public function removeFitmentRow(int $productIndex, int $rowIndex): void
    {
        unset($this->fitment_rows[$productIndex][$rowIndex]);
        $this->fitment_rows[$productIndex] = array_values($this->fitment_rows[$productIndex]);
    }

    // When fitment checkbox is toggled on, seed one empty row
    public function updatedFitment($value, string $productIndex): void
    {
        if ($value && empty($this->fitment_rows[(int)$productIndex])) {
            $this->fitment_rows[(int)$productIndex][] = [
                'mileage'  => '',
                'qty'      => '',
                'position' => '',
            ];
        }

        if (!$value) {
            unset($this->fitment_rows[(int)$productIndex]);
        }
    }

       private function resetExpenseInputFields(){
        $this->selectedExpenseProduct = [];
        $this->inputs = [];
        $this->expenses_inputs = [];
        $this->selectedTax = '';
        $this->qty = [];
        $this->currency_id = '';
        $this->item_name = '';
        $this->vendor_id = '';
        $this->item_price = '';
        $this->tax_id = '';
        $this->sell = True;
        $this->buy = False;
    }

    

    public function removeExpense($e)
    {
        unset($this->expenses_inputs[$e]);
    }

    public function updatedSearchProduct($value)
    {
        $relation = $this->department === 'tyre' 
            ? 'tyres'
            : ($this->department === 'asset'
                ? 'assets'
                : 'inventories');

        $relationFields = [
            'tyre' => 'tyres:id,product_id,tyre_number,serial_number,balance,weight,total,currency_id,status',
            'asset' => 'assets:id,product_id,asset_number,serial_number,balance,weight,total,currency_id,status',
            'inventory' => 'inventories:id,product_id,inventory_number,serial_number,balance,weight,total,currency_id,status',
        ];

        // Base query
        $query = Product::with('brand:id,name', $relationFields[$this->department])
            ->where('department', $this->department)
            ->whereHas($relation, function ($q) {
                $q->where('status', 1);
                $q->where('balance', '>', 0);
            });

        // If search term exists
        if (filled($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%")
                ->orWhere('product_number', 'like', "%{$value}%")
                ->orWhere('identification_number', 'like', "%{$value}%")
                ->orWhereHas('brand', function ($b) use ($value) {
                    $b->where('name', 'like', "%{$value}%");
                });
            });
        }

        $this->products = $query->orderBy('name', 'asc')->get();
    }

   

    public function updatedSearchTicket($value)
    {
        $search = trim($value);

        $baseQuery = Ticket::query()->with(['booking','service_type','horse','vehicle','trailer']);
            // ->whereYear('in_date', date('Y'));
            // ->where('status', 1);

        if (filled($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                ->orWhere('in_date', 'like', "%{$search}%")
                ->orWhereHas('booking', function ($qb) use ($search) {
                    $qb->where('booking_number', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($q2) use ($search) {
                            $q2->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%");
                        })
                        ->orWhereHas('employees', function ($q3) use ($search) {
                            $q3->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%");
                        });
                })
                ->orWhereHas('service_type', function ($qs) use ($search) {
                    $qs->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('horse', function ($qh) use ($search) {
                    $qh->where('registration_number', 'like', "%{$search}%")
                        ->orWhere('fleet_number', 'like', "%{$search}%");
                })
                ->orWhereHas('vehicle', function ($qv) use ($search) {
                    $qv->where('registration_number', 'like', "%{$search}%")
                        ->orWhere('fleet_number', 'like', "%{$search}%");
                })
                ->orWhereHas('trailer', function ($qt) use ($search) {
                    $qt->where('registration_number', 'like', "%{$search}%")
                        ->orWhere('fleet_number', 'like', "%{$search}%");
                });
            });
        }

        $this->tickets = $baseQuery
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function loadProducts($store_id = null)
    {

        $map = [
            'asset'     => 'assets',
            'inventory' => 'inventories',
            'tyre'      => 'tyres',
        ];

        $relation = $map[$this->department] ?? null;

        if (! $relation) {
            $this->products = collect();
            return;
        }
       
        $this->products = Product::with(['brand', $relation])
        ->where('status', 1)
        ->where('department', $this->department)
        ->when($this->dispatch_for === 'inventory', function ($query) use ($relation, $store_id) {
            $query->whereHas($relation, function ($q) use ($store_id) {
                $q->where('status', 1)
                ->where('balance', '>', 0)
                ->when($store_id, function ($qq) use ($store_id) {
                    $qq->where('store_id', $store_id);
                });
            });
        })
        ->orderBy('name', 'asc')
        ->get();
       
    }

    public function mount($department){
        $this->inventories = collect();
        $this->tyres = collect();
        $this->assets = collect();
        $this->department = $department;
        $this->company = Auth::user()->employee->company;
        $this->currencies = collect();
        $this->payment_methods = PaymentMethod::orderBy('name','asc')->get();
        $this->tax_accounts = collect();
        $this->accounts = collect();
        $this->vendors = collect();
        $this->employees = Employee::where('status',1)->where('archive',0)->orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->all_departments = Department::orderBy('name','asc')->get();
       
        $this->stores = Store::orderBy('name','asc')->get();
       
        $this->reset(['searchProduct', 'searchTicket']);
        $this->loadProducts();
      
       
       
    }

    
    public function importDispatches()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new DispatchesImport();

            ExcelFacade::import($import, $this->importFile->getRealPath());

            $failures = $import->failures();
            $errors   = $import->errors();

            if ($failures->isNotEmpty()) {
                foreach ($failures as $failure) {
                    $this->addError(
                        'importFile',
                        "Row {$failure->row()}: " . implode(', ', $failure->errors())
                    );
                }
                return;
            }

            if ($errors->isNotEmpty()) {
                foreach ($errors as $error) {
                    $this->addError('importFile', $error->getMessage());
                }
                return;
            }

            $this->importFile = null;
            $this->dispatchBrowserEvent('hide-importModal');
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => 'Dispatches imported successfully!',
            ]);

        } catch (ValidationException $e) {
            foreach ($e->failures() as $failure) {
                $this->addError(
                    'importFile',
                    "Row {$failure->row()}: " . implode(', ', $failure->errors())
                );
            }
        } catch (\Exception $e) {
            $this->addError('importFile', 'Import failed: ' . $e->getMessage());
        }
    }


    // ─────────────────────────────────────────────────────────────────────────────
    // 2. Download blank template (add this method too)
    // ─────────────────────────────────────────────────────────────────────────────

    public function downloadDispatchTemplate()
    {
        $headers = [
            'date', 'dispatch_for', 'department',
            'employee_name', 'horse', 'trailer', 'vehicle',
            'store_name', 'branch_name', 'department_name',
            'currency', 'vendor_name', 'description', 'items',
        ];

        $example_inventory = [
            '2025-06-01', 'inventory', 'inventory',
            'John Doe', 'AAA 1234', '', '',
            'Main Store', '', '',
            'USD', '', 'Workshop stock out',
            'Engine Oil*5, Air Filter*2',
        ];

        $example_expenses = [
            '2025-06-01', 'expenses', 'inventory',
            'John Doe', 'AAA 1234', '', '',
            '', '', '',
            'ZWG', 'FuelCo Pvt Ltd', 'Fuel expense',
            'Diesel*50:2.80:VAT 15%, Labour*1:150.00',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        foreach ($headers as $col => $heading) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $heading);
            $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
            $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
        }

        // Example rows
        foreach ($example_inventory as $col => $val) {
            $sheet->setCellValueByColumnAndRow($col + 1, 2, $val);
        }
        foreach ($example_expenses as $col => $val) {
            $sheet->setCellValueByColumnAndRow($col + 1, 3, $val);
        }

        // dispatch_for dropdown
        $validation = $sheet->getCell('B2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setFormula1('"inventory,expenses"');
        $validation->setShowDropDown(false);
        $validation->setSqref('B2:B1000');

        // department dropdown
        $validation2 = $sheet->getCell('C2')->getDataValidation();
        $validation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation2->setFormula1('"inventory,tyre,asset"');
        $validation2->setShowDropDown(false);
        $validation2->setSqref('C2:C1000');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'dispatches_import_template.xlsx';
        $path = storage_path('app/' . $filename);
        $writer->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    public function updatedDispatchFor($value){
        if(!is_null($value)){
            if($value == "expenses"){
                $this->currencies = Currency::orderBy('name','asc')->get();
                $this->vendors = Vendor::orderBy('name','asc')->get();
                 $this->tax_accounts = Tax::whereHas('account', function ($query) {
                        return $query->where('name','Value Added Tax');
                    })->orderBy('name','asc')->get();
                $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
                    return $query->where('name','Expenses');
                })->orderBy('name','asc')->get();
            }
            
        }
    }

    public function updatedSelectedEmployee($id){
        if (!is_null($id)) {
            $employee = Employee::find($id);
            if ($employee) {
                $this->asset_department_id = $employee->departments->first()?->id;
                $this->branch_id = $employee->branch_id;
            }
          
        }
    }
    public function updatedSelectedTicket($id){
        if (!is_null($id)) {
            $this->ticket = Ticket::find($id);
            $this->ticket_requests = $this->ticket?->ticket_requests;
            $this->horse_id = $this->ticket?->horse_id;
            $this->vehicle_id = $this->ticket?->vehicle_id;
            $this->trailer_id = $this->ticket?->trailer_id;
           
        }
    }

    public function updatedAttach($value){
        if($value == true){
            $this->tickets = Ticket::query()->with(['booking','service_type','horse','vehicle','trailer'])->whereYear('in_date',date('Y'))->where('status',1)->orderBy('in_date','desc')->get();
        }else {
            $this->tickets = collect();
            $this->ticket = Null;
            $this->ticket_requests = [];
            $this->horse_id = Null;
            $this->vehicle_id = Null;
            $this->trailer_id = Null;
        }
    }

    public function updatedExpand($value){
        if (is_null($value)) {
            return ;
        }

        if($value == False){
            $this->inventories = collect();
            $this->tyres = collect();
            $this->assets = collect();
            $this->selectedTyre = [];
            $this->selectedInventory = [];
            $this->selectedAsset = [];
        }
    }

   

    public function updatedSelectedInventory($id, $key){
        if (!is_null($id)) {
            $inventory = Inventory::find($id);
            if ($inventory && $this->expand == True) {
                $this->qty[$key] = $inventory->balance;
                $this->max[$key] = $inventory->balance;
            }
        }
         
    }
    public function updatedSelectedAsset($id, $key){
        if (!is_null($id)) {
            $asset = Asset::find($id);
            if ($asset && $this->expand == True) {
                $this->qty[$key] = $asset->balance;
                $this->max[$key] = $asset->balance;
            }
        }
    }
    public function updatedSelectedTyre($id, $key){
        if (!is_null($id)) {
            $tyre = Tyre::find($id);
            if ($tyre && $this->expand == True) {
                $this->weight[$key] = $tyre->balance;
                $this->max[$key] = $tyre->balance;
            }
        }
    }
  
    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            if ($this->expand == False) {
                  $this->qty[$key] = 1;
            }
            $this->inventories = Inventory::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            $this->tyres = Tyre::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            $this->assets = Asset::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            
            $product = Product::find($id);

            if ($product && $this->department == "inventory") {
                $this->max[$key] = $product->inventories->where('status',1)->where('balance','>',0)->sum('balance');
            }elseif ($product && $this->department == "tyre") {
                $this->max[$key] = $product->tyres->where('status',1)->sum('balance');
            }elseif ($product && $this->department == "asset") {
                $this->max[$key] = $product->assets->where('status',1)->where('balance','>',0)->sum('balance');
            }

        
            
        }
    }

        public function updatedSelectedExpenseProduct($id , $key){
            
        if (!is_null($id) && !is_null($key)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount[$key] = $product->price;
                }
                if ($product->description) {
                    $this->description[$key] = $product->description;
                }
                if ($product->expense_account_id) {
                    $this->selectedAccount[$key] = $product->expense_account_id;
                }
                $this->qty[$key] = 1;
                if ($product->tax_id) {
                    $this->selectedTax[$key] = $product->tax_id;
                    $tax = Tax::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate[$key] = $tax->rate;
                    }
                    
                }  
            }
           
        }
    }

     public function dispatchNumber(){
        $initials  = "";
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

        $dispatch = Dispatch::latest()->orderBy('id','desc')->first();

        if (!$dispatch) {
            $dispatch_number =  $initials .'D'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $dispatch->id + 1;
            $dispatch_number =  $initials .'D'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $dispatch_number;


    }

    
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
    ];

      public function updatedSelectedTax($id, $key){
        if(!is_null($id) && !is_null($key)){
            $tax = Tax::find($id);
            if (isset($tax)) {
                $this->tax_rate[$key] = $tax->rate;
            }
           
        }
    }
    
    private function resetInputFields(){
        $this->date = Null;
        $this->vendor_id = Null;
        $this->requested_by_id = Null;
        $this->selectedEmployee = Null;
        $this->branch_id = Null;
        $this->selectedStore = Null;
        $this->asset_department_id = Null;
        $this->selectedExpenseProduct = [];
        $this->selectedProduct = [];
        $this->qty = [];
        $this->amount = [];
        $this->selectedTax = [];
        $this->tax_rate = [];
        $this->selectedInventory = [];
        $this->selectedTicket = Null;
        $this->searchTicket = [] ;
        $this->searchProduct = [] ;
        $this->dispatch_for = "inventory" ;
    }

     public function calculateExchangeAmount($total = null){
        $exchange_amount = 0;
        if (($total && is_numeric($total)) && ($this->exchange_rate && is_numeric($this->exchange_rate)) ) {
            $exchange_amount = $this->exchange_rate * $total;
        }
        return $exchange_amount;
    }

    public function inventoryFIFO($dispatch)
    {
        $dispatch_total = 0;

        // 1) Resolve collection based on department
        if ($this->department === 'tyre') {
            $collection = $this->selectedTyre ?? [];
        } elseif ($this->department === 'asset') {
            $collection = $this->selectedAsset ?? [];
        } elseif ($this->department === 'inventory') {
            $collection = $this->selectedInventory ?? [];
        } else {
            return 0;
        }

        if (empty($collection)) {
            return 0;
        }

        foreach ($collection as $key => $collectionId) {

            $requestedQty = (float)($this->qty[$key] ?? 0);
            if ($requestedQty <= 0) {
                continue;
            }

            // 2) Load correct model row
            if ($this->department === 'tyre') {
                $model = Tyre::find($collectionId);
            } elseif ($this->department === 'asset') {
                $model = Asset::find($collectionId);
            } elseif ($this->department === 'inventory') {
                $model = Inventory::find($collectionId);
            }

            if (!$model) {
                continue;
            }

            // 3) How much is available on this exact row
            $rowQtyAvailable = (float)($model->balance);
            if ($rowQtyAvailable <= 0) {
                continue;
            }

            // 4) Row cost in company currency
            $rowCostCompany = $model->currency_id != $this->company->currency_id
                ? (float)$model->exchange_amount
                : (float)$model->total;

            $unitCost      = $rowCostCompany / $rowQtyAvailable;
            $qtyFromRow    = min($requestedQty, $rowQtyAvailable);
            $amountFromRow = $qtyFromRow * $unitCost;

            // 5) Create dispatch item
            $dispatch_item              = new DispatchItem;
            $dispatch_item->dispatch_id = $dispatch->id;
            $dispatch_item->product_id  = $model->product?->id;

            if (isset($this->requestedItem[$key])) {
                $dispatch_item->ticket_request_id = $this->requestedItem[$key];
            }

            $dispatch_item->qty         = $qtyFromRow;
            $dispatch_item->unit_cost   = $unitCost;
            $dispatch_item->amount      = $amountFromRow;
            $dispatch_item->currency_id = $this->company->currency_id;

            if ($this->department === 'tyre') {
                $dispatch_item->tyre_id = $model->id;
            } elseif ($this->department === 'asset') {
                $dispatch_item->asset_id = $model->id;
            } else {
                $dispatch_item->inventory_id = $model->id;
            }

            $dispatch_item->save();

            $dispatch_total += $amountFromRow;

            if (!empty($this->fitment[$key])) {
                $ticket = Ticket::find($dispatch->ticket_id);

                foreach ($this->fitment_rows[$key] ?? [] as $row) {
                    $movement = new Movement;
                    $movement->user_id     = Auth::user()->id;
                    $movement->dispatch_id = $dispatch->id;
                    $movement->dispatch_item_id = $dispatch->id;
                    $movement->ticket_id   = $ticket?->id;
                    $movement->horse_id    = $ticket?->horse_id;
                    $movement->vehicle_id  = $ticket?->vehicle_id;
                    $movement->trailer_id  = $ticket?->trailer_id;
                    $movement->product_id  = $model->product?->id;

                    if ($this->department === 'tyre') {
                        $movement->tyre_id  = $collectionId;
                    } elseif ($this->department === 'asset') {
                        $movement->asset_id  = $collectionId;
                    } elseif ($this->department === 'inventory') {
                        $movement->inventory_id  = $collectionId;
                    } 
                    
                    $movement->date        = now();
                    $movement->mileage_moved = $row['mileage'];
                    $movement->qty         = $row['qty'];
                    $movement->position    = $row['position'];
                    $movement->save();
                }
            }
        }

        return $dispatch_total;
    }


    public function inventoryAVCO($dispatch)
    {
        $dispatch_total = 0;

        // 1) Resolve collection based on department
        if ($this->department === 'tyre') {
            $collection = $this->selectedTyre ?? [];
        } elseif ($this->department === 'asset') {
            $collection = $this->selectedAsset ?? [];
        } elseif ($this->department === 'inventory') {
            $collection = $this->selectedInventory ?? [];
        } else {
            return 0;
        }

        if (empty($collection)) {
            return 0;
        }

        // Cache avg unit cost per product to avoid N+1 queries
        $avcoCache = [];

        foreach ($collection as $key => $collectionId) {

            $requestedQty = (float)($this->qty[$key] ?? 0);
            if ($requestedQty <= 0) {
                continue;
            }

            // 2) Load correct model row
            if ($this->department === 'tyre') {
                $model = Tyre::find($collectionId);
            } elseif ($this->department === 'asset') {
                $model = Asset::find($collectionId);
            } elseif ($this->department === 'inventory') {
                $model = Inventory::find($collectionId);
            }

            if (!$model) {
                continue;
            }

            $rowQtyAvailable = (float)$model->balance;
            if ($rowQtyAvailable <= 0) {
                continue;
            }

            $productId = $model->product?->id;

            // 3) Compute weighted average cost, cached per product
            if (!isset($avcoCache[$productId])) {

                if ($this->department === 'tyre') {
                    $allRows = Tyre::where('product_id', $productId)->where('balance', '>', 0)->get();
                } elseif ($this->department === 'asset') {
                    $allRows = Asset::where('product_id', $productId)->where('balance', '>', 0)->get();
                } else {
                    $allRows = Inventory::where('product_id', $productId)->where('balance', '>', 0)->get();
                }

                $totalQty  = 0;
                $totalCost = 0;

                foreach ($allRows as $row) {
                    $rowQty  = (float)$row->balance;
                    $rowCost = $row->currency_id != $this->company->currency_id
                        ? (float)$row->exchange_amount
                        : (float)$row->total;

                    $totalQty  += $rowQty;
                    $totalCost += $rowCost;
                }

                $avcoCache[$productId] = $totalQty > 0
                    ? $totalCost / $totalQty
                    : 0;
            }

            $avgUnitCost = $avcoCache[$productId];

            if ($avgUnitCost <= 0) {
                continue;
            }

            // 4) Calculate dispatch amounts using average cost
            $qtyFromRow    = min($requestedQty, $rowQtyAvailable);
            $amountFromRow = $qtyFromRow * $avgUnitCost;

            // 5) Create dispatch item
            $dispatch_item              = new DispatchItem;
            $dispatch_item->dispatch_id = $dispatch->id;
            $dispatch_item->product_id  = $productId;

            if (isset($this->requestedItem[$key])) {
                $dispatch_item->ticket_request_id = $this->requestedItem[$key];
            }

            $dispatch_item->qty         = $qtyFromRow;
            $dispatch_item->unit_cost   = $avgUnitCost;
            $dispatch_item->amount      = $amountFromRow;
            $dispatch_item->currency_id = $this->company->currency_id;

            if ($this->department === 'tyre') {
                $dispatch_item->tyre_id = $model->id;
            } elseif ($this->department === 'asset') {
                $dispatch_item->asset_id = $model->id;
            } else {
                $dispatch_item->inventory_id = $model->id;
            }

            $dispatch_item->save();

            $dispatch_total += $amountFromRow;
        }

        return $dispatch_total;
    }


    public function productFIFO($dispatch)
    {
        $dispatch_total = 0.0;

        if (empty($this->selectedProduct)) {
            return 0.0;
        }

        foreach ($this->selectedProduct as $key => $productId) {

            $requestedQty = (float)($this->qty[$key] ?? 0);

            if ($requestedQty <= 0) {
                continue;
            }

            $product = Product::find($productId);
            if (!$product) {
                continue;
            }

            // 1) Get source rows in FIFO order
            $items = collect();

            switch ($this->department) {
                case 'inventory':
                    $items = Inventory::where('product_id', $product->id)
                        ->where('status', 1)
                        ->where('balance', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;

                case 'asset':
                    $items = Asset::where('product_id', $product->id)
                        ->where('status', 1)
                        ->where('balance', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;

                case 'tyre':
                    $items = Tyre::where('product_id', $product->id)
                        ->where('status', 1)
                        ->where('balance', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;
            }

            if ($items->isEmpty()) {
                continue;
            }

            $remainingQty       = $requestedQty;
            $totalQtyDispatched = 0.0;
            $totalLineAmount    = 0.0;

            foreach ($items as $item) {

                if ($remainingQty <= 0) {
                    break;
                }

                // 2) Determine available qty on this row
                $rowQtyAvailable = (float)($item->balance ?: $item->qty);

                if ($rowQtyAvailable <= 0) {
                    continue;
                }

                // 3) Row cost in company currency
                $rowCostCompany = $item->currency_id != $this->company->currency_id
                    ? (float)$item->exchange_amount
                    : (float)$item->total;

                $unitCost = $rowCostCompany / $rowQtyAvailable;

                // 4) Take from this row (FIFO)
                $qtyFromRow    = min($remainingQty, $rowQtyAvailable);
                $amountFromRow = $qtyFromRow * $unitCost;

                // 5) Create dispatch item per source row
                $dispatch_item              = new DispatchItem;
                $dispatch_item->dispatch_id = $dispatch->id;
                $dispatch_item->product_id  = $product->id;

                if (isset($this->requestedItem[$key])) {
                    $dispatch_item->ticket_request_id = $this->requestedItem[$key];
                }

                $dispatch_item->qty         = $qtyFromRow;
                $dispatch_item->unit_cost   = $unitCost;
                $dispatch_item->amount      = $amountFromRow;
                $dispatch_item->currency_id = $this->company->currency_id;

                if ($this->department === 'inventory') {
                    $dispatch_item->inventory_id = $item->id;
                } elseif ($this->department === 'asset') {
                    $dispatch_item->asset_id = $item->id;
                } elseif ($this->department === 'tyre') {
                    $dispatch_item->tyre_id = $item->id;
                }

                $dispatch_item->save();

                $totalQtyDispatched += $qtyFromRow;
                $totalLineAmount    += $amountFromRow;
                $remainingQty       -= $qtyFromRow;
            }

            if ($totalQtyDispatched <= 0) {
                continue;
            }

            $dispatch_total += $totalLineAmount;

            if (!empty($this->fitment[$key])) {
                $ticket = Ticket::find($dispatch->ticket_id);

                foreach ($this->fitment_rows[$key] ?? [] as $row) {
                    $movement = new Movement;
                    $movement->user_id     = Auth::user()->id;
                    $movement->dispatch_id = $dispatch->id;
                    $movement->dispatch_item_id = $dispatch->id;
                    $movement->ticket_id   = $ticket?->id;
                    $movement->horse_id    = $ticket?->horse_id;
                    $movement->vehicle_id  = $ticket?->vehicle_id;
                    $movement->trailer_id  = $ticket?->trailer_id;
                    $movement->product_id  = $productId;
                    $movement->date        = now();
                    $movement->mileage_moved = $row['mileage'];
                    $movement->qty         = $row['qty'];
                    $movement->position    = $row['position'];
                    $movement->save();
                }
            }

           
        }

        return $dispatch_total;
    }


    public function productAVCO($dispatch)
    {
        $dispatch_total = 0.0;

        if (empty($this->selectedProduct)) {
            return 0.0;
        }

        foreach ($this->selectedProduct as $key => $productId) {

            $requestedQty = (float)($this->qty[$key] ?? 0); // fixed: was (int), now (float)
            if ($requestedQty <= 0) {
                continue;
            }

            $product = Product::find($productId);
            if (!$product) {
                continue;
            }

            // 1) Get source rows (oldest first for physical consumption order)
            $items = collect();

            switch ($this->department) {
                case 'inventory':
                    $items = Inventory::where('product_id', $product->id)
                        ->where('status', 1)
                        ->where('balance', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;

                case 'asset':
                    $items = Asset::where('product_id', $product->id)
                        ->where('status', 1)
                        ->where('balance', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;

                case 'tyre':
                    $items = Tyre::where('product_id', $product->id)
                        ->where('status', 1)
                        ->where('balance', '>', 0) // fixed: was missing
                        ->orderBy('created_at', 'asc')
                        ->get();
                    break;
            }

            if ($items->isEmpty()) {
                continue;
            }

            // 2) Compute weighted average unit cost across all available rows
            $totalQtyAvailable    = 0.0;
            $totalCostCompanyCurr = 0.0;

            foreach ($items as $item) {
                $itemQty = (float)($item->balance ?: $item->qty); // fixed: was hardcoded 1.0 for asset/tyre

                if ($itemQty <= 0) {
                    continue;
                }

                $rowCostCompany = $item->currency_id != $this->company->currency_id
                    ? (float)$item->exchange_amount
                    : (float)$item->total;

                $totalQtyAvailable    += $itemQty;
                $totalCostCompanyCurr += $rowCostCompany;
            }

            if ($totalQtyAvailable <= 0) {
                continue;
            }

            $averageUnitCost = $totalCostCompanyCurr / $totalQtyAvailable;

            // 3) Consume rows in order (FIFO physically), priced at AVCO rate
            $remainingQty       = min($requestedQty, $totalQtyAvailable);
            $totalQtyDispatched = 0.0;
            $totalLineAmount    = 0.0;

            foreach ($items as $item) {

                if ($remainingQty <= 0) {
                    break;
                }

                $rowQtyAvailable = (float)($item->balance ?: $item->qty);
                if ($rowQtyAvailable <= 0) {
                    continue;
                }

                // Take from this row, price at average cost
                $qtyFromRow    = min($remainingQty, $rowQtyAvailable);
                $amountFromRow = $qtyFromRow * $averageUnitCost;

                // 4) Create dispatch item per source row (fixed: was one item for all rows)
                $dispatch_item              = new DispatchItem;
                $dispatch_item->dispatch_id = $dispatch->id;
                $dispatch_item->product_id  = $product->id;

                if (isset($this->requestedItem[$key])) { // fixed: was missing
                    $dispatch_item->ticket_request_id = $this->requestedItem[$key];
                }

                $dispatch_item->qty         = $qtyFromRow;
                $dispatch_item->unit_cost   = $averageUnitCost;
                $dispatch_item->amount      = $amountFromRow;
                $dispatch_item->currency_id = $this->company->currency_id;

                // fixed: source row links were missing entirely
                if ($this->department === 'inventory') {
                    $dispatch_item->inventory_id = $item->id;
                } elseif ($this->department === 'asset') {
                    $dispatch_item->asset_id = $item->id;
                } elseif ($this->department === 'tyre') {
                    $dispatch_item->tyre_id = $item->id;
                }

                $dispatch_item->save();

                $totalQtyDispatched += $qtyFromRow;
                $totalLineAmount    += $amountFromRow;
                $remainingQty       -= $qtyFromRow;
            }

            if ($totalQtyDispatched <= 0) {
                continue;
            }

            $dispatch_total += $totalLineAmount;

            if (!empty($this->fitment[$key])) {
                $ticket = Ticket::find($dispatch->ticket_id);

                foreach ($this->fitment_rows[$key] ?? [] as $row) {
                    $movement = new Movement;
                    $movement->user_id     = Auth::user()->id;
                    $movement->dispatch_id = $dispatch->id;
                    $movement->ticket_id   = $ticket?->id;
                    $movement->horse_id    = $ticket?->horse_id;
                    $movement->vehicle_id  = $ticket?->vehicle_id;
                    $movement->trailer_id  = $ticket?->trailer_id;
                    $movement->product_id  = $productId;
                    $movement->date        = now();
                    $movement->mileage_moved = $row['mileage'];
                    $movement->qty         = $row['qty'];
                    $movement->position    = $row['position'];
                    $movement->save();
                }
            }
        }

        return $dispatch_total;
    }

    public function store(){

        DB::transaction(function () {

        $this->validate([
            'selectedEmployee' => [
                'nullable',
                Rule::exists('employees', 'id'),
            ],
        ]);
        
        $dispatch = new Dispatch;
        $dispatch->user_id = Auth::user()->id;
        $dispatch->dispatch_number = $this->dispatchNumber();
        $dispatch->horse_id = $this->horse_id ?: null;
        $dispatch->trailer_id = $this->trailer_id ?: null;
        $dispatch->vehicle_id = $this->vehicle_id ?: null;
        $dispatch->ticket_id = $this->selectedTicket ?: null;
        $dispatch->store_id = $this->selectedStore ?: null;
        $dispatch->employee_id = $this->selectedEmployee ?: null;
        $dispatch->requested_by_id = $this->requested_by_id ?: null;
        $dispatch->department = $this->department;
        $dispatch->dispatch_for = $this->dispatch_for;
        $dispatch->department_id = $this->asset_department_id ?: null;
        $dispatch->branch_id = $this->branch_id ?: null;
        $dispatch->vendor_id = $this->vendor_id ?: null;
        $dispatch->currency_id = $this->currency_id ?: $this->company->currency_id;
        $dispatch->description = $this->description;
        $dispatch->date = $this->date;
        $dispatch->expand = $this->expand;
        $dispatch->save();

        if ($this->dispatch_for == "inventory") {

            if ($this->expand == True) {
                if($this->company->valuation_method == "AVCO"){
                    $dispatch_total = $this->inventoryAVCO($dispatch);
                }elseif($this->company->valuation_method == "FIFO"){
                    $dispatch_total =  $this->inventoryFIFO($dispatch);
                }
            }elseif ($this->expand == False) {
                if($this->company->valuation_method == "AVCO"){
                    $dispatch_total = $this->productAVCO($dispatch);
                }elseif($this->company->valuation_method == "FIFO"){
                    $dispatch_total = $this->productFIFO($dispatch);
                }
            }

        }elseif($this->dispatch_for == "expenses"){

            if (isset($this->selectedExpenseProduct)) {
              
                foreach($this->selectedExpenseProduct as $key => $id){

                    $dispatch_total = 0;
                    $item_subtotal = 0;
                    $item_subtotal_incl = 0;

                    $currency_id = $this->currency_id ?: Null;
                    $payment_method_id = $this->payment_method_id[$key] ?? Null;
                    $product_id = $this->selectedExpenseProduct[$key] ?? Null;
                    $qty = $this->qty[$key] ?? Null;
                    $amount = $this->amount[$key] ?? Null;
                    $tax_rate = $this->tax_rate[$key] ?? Null;
                    $tax_id = $this->selectedTax[$key] ?? Null;

                  

                $dispatch_item = new  DispatchItem;
                $dispatch_item->dispatch_id =  $dispatch->id;
                $dispatch_item->currency_id =  $currency_id;
                $dispatch_item->payment_method_id =  $payment_method_id;
                $dispatch_item->product_id =  $product_id;
                $dispatch_item->qty =  $qty;
                $dispatch_item->unit_cost =  $amount;
                $dispatch_item->tax_rate = $tax_rate;
                $dispatch_item->tax_id = $tax_id;
    
                 if (is_numeric($amount) && is_numeric($qty ) ) {
                    $item_subtotal = $amount*$qty;
                    $dispatch_item->subtotal = $item_subtotal;
                }
    
                if (is_numeric($tax_rate)) {
    
                    $item_tax_amount = ($item_subtotal * ($tax_rate / 100 ));
                    $dispatch_item->tax_amount =  $item_tax_amount;
                    $tax_amount = $tax_amount + $item_tax_amount;
                    $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                    $dispatch_item->subtotal_incl =  $item_subtotal_incl;
                    $dispatch_item->amount =  $item_subtotal_incl;

                }else{
                    $item_subtotal_incl = $item_subtotal;
                    $dispatch_item->subtotal_incl = $item_subtotal_incl;
                    $dispatch_item->amount =  $item_subtotal_incl;
                }


               
                $exchange_amount = $this->calculateExchangeAmount($item_subtotal_incl);
                $dispatch_item->exchange_rate = $this->exchange_rate;
                $dispatch_item->exchange_amount =  $exchange_amount;
                $dispatch_item->save();

                $dispatch_total += $currency_id != $this->company->currency_id ? $exchange_amount : $item_subtotal_incl;

                }

            }

        }
       
        
        
        $dispatch->total = $dispatch_total;
        $dispatch->save();

        $notifications = Notification::where('when','before')->where('category','Dispatch Authorization')->where('status',1)->get();
                
        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                $email = $notification->email ?? $notification->employee->email ?? null;
                if($email){
                    Mail::to($email)->send(new PendingNotificationEmails($this->company, $notification, $dispatch));
                }
                }
            }
        }

        $this->dispatchBrowserEvent('hide-dispatchModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Items Dispatched Successfully!!"
        ]);

    });
    
    }

    public function updatedSelectedStore($id){
        if(!is_null($id)){
            $store = Store::find($id);
            $this->loadProducts($store->id);
        }
    }

    public function showDelete($id){
        if(!is_null($id)){

             $this->dispatch = Dispatch::find($id);
             $this->dispatchBrowserEvent('show-dispatchDeleteModal');

        }
    }

    public function refresh($category){

        if($category == "products"){

            $store_id = $this->selectedStore;

                    $map = [
                'asset'     => 'assets',
                'inventory' => 'inventories',
                'tyre'      => 'tyres',
            ];

            $relation = $map[$this->department] ?? null;

            if (! $relation) {
                $this->products = collect();
                return;
            }
        
            $this->products = Product::with(['brand', $relation])
            ->where('status', 1)
            ->where('department', $this->department)
            ->when($this->dispatch_for === 'inventory', function ($query) use ($relation, $store_id) {
                $query->whereHas($relation, function ($q) use ($store_id) {
                    $q->where('status', 1)
                    ->where('balance', '>', 0)
                    ->when($store_id, function ($qq) use ($store_id) {
                        $qq->where('store_id', $store_id);
                    });
                });
            })
            ->orderBy('name', 'asc')
            ->get();
            
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Products Refreshed Successfully!!."
            ]);

        }
        elseif($category == ""){
           
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bank Accounts Refreshed Successfully!!."
            ]);
        }
    }

    public function destroy(){

        $bill = $this->dispatch->bill;
        $bill_expenses = $bill?->bill_expenses;
        if($bill_expenses){
            foreach($bill_expenses as $bill_expense){
                $bill_expense->delete();
            }
        }
        $bill?->delete();
        $dispatch_items = $this->dispatch->dispatch_items;
        if($dispatch_items){

            foreach($dispatch_items as $dispatch_item){

                // $tyre = $dispatch_item->tyre;
                // $inventory = $dispatch_item->inventory;
                // $asset = $dispatch_item->asset;

                // if (isset($tyre)) {
                //     $tyre_assignments = $tyre?->tyre_assignment;

                //     if(isset($tyre_assignments)){
                //         foreach($tyre_assignments as $tyre_assignment){
                //             $tyre_assignment->delete();
                //         }
                //     }

                //     $tyre->status = 1;
                //     $tyre->balance += $dispatch_item->qty;
                //     $tyre->save();
                // }
                // elseif(isset($inventory)){
                //     $inventory->status = 1;
                //     $inventory->balance += $dispatch_item->qty;
                //     $inventory->save();
                // }
                // elseif(isset($asset)){
                //     $asset->status = 1;
                //     $asset->balance += $dispatch_item->qty;
                //     $asset->save();
                // }
                $dispatch_item->delete();
            }
        }

        $this->dispatch->delete();
        $this->dispatchBrowserEvent('hide-dispatchDeleteModal');

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Dipatch Record Deleted Successfully!!"
        ]);

    }

 

    public function edit($id){
        $dispatch = Dispatch::find($id);
        $this->horse_id = $dispatch->horse_id;
        $this->horse_id = $dispatch->trailer_id;
        $this->horse_id = $dispatch->vehicle_id;
        $this->expand = $dispatch->expand;
        $this->selectedTicket = $dispatch->ticket_id;
        if($dispatch->ticket_id){
             $this->attach = True;
        }
        $this->selectedEmployee = $dispatch->ticket_id;
        $this->selectedStore = $dispatch->store_id;
        $this->requested_by_id = $dispatch->requested_by_id;
        $this->department = $dispatch->department;
        $this->asset_department_id = $dispatch->department_id;
        $this->branch_id = $dispatch->branch_id;
        $this->currency_id = $dispatch->currency_id;
        $this->description = $dispatch->description;
        $this->date = $dispatch->date;
        $this->dispatch_id = $dispatch->id;
        $dispatch_items = $dispatch->dispatch_items;

        if($dispatch_items){
            foreach($dispatch_items as $dispatch_item){
                $this->requestedItem[] = $dispatch_item->ticket_request_id; 
                $this->selectedInventory[] = $dispatch_item->inventory_id; 
                $this->selectedProduct[] = $dispatch_item->inventory_id; 
                $this->selectedTyre[] = $dispatch_item->tyre_id; 
                $this->selectedAsset[] = $dispatch_item->tyre_id; 
                $this->weight[] = $dispatch_item->weight; 
                $this->qty[] = $dispatch_item->qty; 
            }
        }
        
        if (!empty($this->selectedInventory) || !empty($this->selectedAsset) || !empty($this->selectedTyre)) {
            $this->expand = true;
        }
         $this->dispatchBrowserEvent('show-dispatchEditModal');

    }


    public function update(){

        DB::transaction(function () {
        
        $dispatch = Dispatch::find($this->dispatch_id);
        $dispatch->horse_id = $this->horse_id ?: null;
        $dispatch->trailer_id = $this->trailer_id ?: null;
        $dispatch->vehicle_id = $this->vehicle_id ?: null;
        $dispatch->ticket_id = $this->selectedTicket ?: null;
        $dispatch->store_id = $this->selectedStore ?: null;
        $dispatch->employee_id = $this->selectedEmployee ?: null;
        $dispatch->requested_by_id = $this->requested_by_id ?: null;
        $dispatch->department = $this->department;
        $dispatch->department_id = $this->asset_department_id ?: null;
        $dispatch->branch_id = $this->branch_id ?: null;
        $dispatch->currency_id = $this->company->currency_id ?: null;
        $dispatch->description = $this->description;
        $dispatch->date = $this->date;
        $dispatch->expand = $this->expand;
        $dispatch->update();

        $this->dispatchBrowserEvent('hide-dispatchEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Dispatch Record Updated Successfully!!"
        ]);

    });
    
    }
    public function render()
    {

            $base = Dispatch::query()->with(['ticket','horse','vehicle','trailer','employee','department','branch'])
                        ->where('department',$this->department);

            $base->when(filled($this->from) && filled($this->to), function ($q) {
                    $q->whereDate($this->dispatch_filter, '>=', $this->from)
                    ->whereDate($this->dispatch_filter, '<=', $this->to);
                }, function ($q) {
                    $q->whereMonth($this->dispatch_filter, Carbon::now()->month)
                    ->whereYear($this->dispatch_filter, Carbon::now()->year);
                });

               // Search filter (grouped to keep AND/OR logic correct)
            $base->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';

                $q->where(function ($qq) use ($term) {
                    $qq->where('dispatch_number', 'like', $term)
                    ->orWhere('authorization', 'like', $term)
                    ->orWhere('date', 'like', $term)
                    ->orWhereHas('ticket', function ($sub) use ($term) {
                        $sub->where('ticket_number', 'like', $term);
                    })
                    ->orWhereHas('horse', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('store', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('trailer', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->where('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.product', function ($sub) use ($term) {
                        $sub->where('product_number', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('identification_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.inventory', function ($sub) use ($term) {
                        $sub->where('inventory_number', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.tyre', function ($sub) use ($term) {
                        $sub->where('tyre_number', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.asset', function ($sub) use ($term) {
                        $sub->where('asset_number', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                    });
                });
            });

            $dispatches = $base
                ->orderByDesc($this->dispatch_filter)
                ->paginate(10);



        return view('livewire.dispatches.index',[
            'dispatches' => $dispatches
        ]);
    }
}
