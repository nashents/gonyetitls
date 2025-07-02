<?php

namespace App\Http\Livewire\Products;

use App\Models\Tyre;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Value;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Inventory;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Exports\ProductsExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\StockValuationExport;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $department;
    protected $queryString = ['search'];
    private $products;
    public $base_currency;

    public function mount($category){
        $this->department = $category;
        $this->base_currency = Auth::user()->employee->company->currency;
        $this->resetPage();
    }


    
    public function exportProductsCSV(Excel $excel){
        return $excel->download(new ProductsExport, 'products_' .time().'.csv', Excel::CSV);
    }
    public function exportProductsPDF(Excel $excel){
        return $excel->download(new ProductsExport, 'products_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportProductsExcel(Excel $excel){
        return $excel->download(new ProductsExport, 'products_' .time().'.xlsx');
    }
    public function exportStockValuationExcel(Excel $excel){
        return $excel->download(new StockValuationExport($this->department), 'stock_valuation_' .time().'.xlsx');
    }


    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function calculateTotalValue($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return null;
        }

        $currency_id = Auth::user()->employee->company->currency_id;
        $total_value = 0;

        $relations = [
            'tyre' => 'tyres',
            'inventory' => 'inventories',
            'asset' => 'assets'
        ];

        if (!isset($relations[$this->department])) {
            return null; // invalid department
        }

        $relation = $relations[$this->department];

        $items = $product->$relation;

        $value = $items->where('status', 1)
                ->where('currency_id', $currency_id)
                ->filter(function ($item) {
                    return !is_null($item->total) || !is_null($item->subtotal_incl);
                })
                ->map(function ($item) {
                    return $item->total ?? $item->subtotal_incl ?? 0;
                })
                ->sum();

        $value_exchange = $items->where('status', 1)
                                ->where('currency_id', '!=', $currency_id)
                                ->whereNotNull('exchange_amount')
                                ->sum('exchange_amount');

        if (is_numeric($value) && is_numeric($value_exchange)) {
            $total_value = $value + $value_exchange;
        }

        return $total_value;
    }

    public function calculateAvgLeadTime($id){


        $averageLeadTime = 0;

        if ($this->department == "inventory") {
            $averageLeadTime = Inventory::select(DB::raw('AVG(DATEDIFF(inventories.created_at, purchases.created_at)) as avg_lead_time'))
            ->join('purchases', 'inventories.purchase_id', '=', 'purchases.id')
            ->where('inventories.product_id', $id) // Filter by specific product_id
            ->value('avg_lead_time');
        }elseif($this->department == "tyre") {
            $averageLeadTime = Tyre::select(DB::raw('AVG(DATEDIFF(tyres.created_at, purchases.created_at)) as avg_lead_time'))
            ->join('purchases', 'tyres.purchase_id', '=', 'purchases.id')
            ->where('tyres.product_id', $id) // Filter by specific product_id
            ->value('avg_lead_time');
        }elseif ($this->department == "assset") {
            $averageLeadTime = Asset::select(DB::raw('AVG(DATEDIFF(assets.created_at, purchases.created_at)) as avg_lead_time'))
            ->join('purchases', 'assets.purchase_id', '=', 'purchases.id')
            ->where('assets.product_id', $id) // Filter by specific product_id
            ->value('avg_lead_time');
        }
       
       return $averageLeadTime;

    }

    public function render()
    {
        if (isset($this->search)) {
            return view('livewire.products.index',[
                'products' => Product::query()->with('brand','category','category_value')->where('department', $this->department)->where('status',1)
                ->where('product_number','like', '%'.$this->search.'%')
                ->orWhere('name','like', '%'.$this->search.'%')
                ->orWhereHas('category', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('category_value', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('name','asc')->paginate(10)
            ]);
        }else{
            return view('livewire.products.index',[
                'products' => Product::query()->with('brand','category','category_value')->where('department', $this->department)->where('status',1)->orderBy('name','asc')->paginate(10)
            ]);
        }
    }
}
