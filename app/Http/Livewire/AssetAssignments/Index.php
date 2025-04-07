<?php

namespace App\Http\Livewire\AssetAssignments;

use App\Models\Asset;
use App\Models\Branch;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AssetDetail;
use App\Models\AssetDispatch;
use App\Models\CategoryValue;
use App\Models\AssetAssignment;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $products;
    public $assets;
    public $asset_qty;
    public $categories;
    public $category_values;
    public $selectedCategoryValue;
    public $selectedCategory;
    public $product_id;
    public $selectedAsset;
    public $asset_details;
    public $asset_detail_id;
    public $departments;
    public $department_id;
    public $branches;
    public $branch_id;
    public $employees;
    public $employee_id;
    public $specifications;
    public $weight;
    public $asset;
    public $start_date;
    public $end_date;
    public $qty = 1;
    public $status;
    public $asset_assignments;
    public $asset_assignment_id;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount(){
        $this->assets = Asset::with('product')->where('status',1)->get()->sortBy('product.name');
        $this->categories = Category::orderBy('name','asc')->get();
        $this->category_values = CategoryValue::orderBy('name','asc')->get();
        $this->departments = Department::orderBy('name','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->get();
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->asset_assignments = AssetAssignment::latest()->get();
        } else {
            $this->asset_assignments = AssetAssignment::where('user_id',Auth::user()->id)->latest()->get();
        }

    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedAsset' => 'required',
        // 'department_id' => 'required',
        // 'branch_id' => 'required',
        // 'qty' => 'required',
        // 'employee_id' => 'required',
        'start_date' => 'required',
        // 'end_date' => 'required',
        // 'specifications' => 'required',
    ];

    private function resetInputFields(){
        $this->selectedAsset = '';
        $this->selectedCategory = '';
        $this->selectedCategoryValue = '';
        $this->branch_id = '';
        $this->department_id = '';
        $this->weight = '';
        $this->asset = '';
        $this->employee_id = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->specifications = '';
    }

    public function updatedSelectedAsset($id){
        if (!is_null($id)) {
           $this->asset = Asset::find($id);
        }
    }
   
    public function unAssign(){
        $assignment = AssetAssignment::find($this->asset_assignment_id);
        $assignment->end_date = $this->end_date;
        $assignment->status = 0;
        $assignment->update();

        $asset = Asset::find($this->asset->id);
        $asset->weight =  $asset->weight + $assignment->weight;
        $asset->balance = $asset->balance + $assignment->weight;
        $asset->status = 1;
        $asset->update();

        $this->dispatchBrowserEvent('hide-asset_unassignmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Asset UnAssigned Successfully!!"
        ]);
    }

    public function unAssignment($id){
        $this->asset_assignment_id = $id;
        $this->asset_assignment = AssetAssignment::find($id);
        $this->asset = Asset::find($this->asset_assignment->asset_id);
        $this->dispatchBrowserEvent('show-asset_unassignmentModal');
    }

    public function store(){


            $assignment = new AssetAssignment;
            $assignment->user_id = Auth::user()->id;
            $assignment->asset_id = $this->selectedAsset;
            $assignment->branch_id = $this->branch_id;
            $assignment->department_id = $this->department_id;
            $assignment->employee_id = $this->employee_id;
            $assignment->start_date = $this->start_date;
            $assignment->specifications = $this->specifications;
            $assignment->weight =  $this->weight;
            $assignment->measurement =  $this->asset->measurement;
            $assignment->currency_id = $this->asset->currency_id;
            if ((isset($this->weight) && is_numeric($this->weight && $this->weight > 0)) && (isset($this->asset->weight) && is_numeric($this->asset->weight) && $this->asset->weight > 0) && (isset($this->asset->subtotal_incl) && is_numeric($this->asset->subtotal_incl) && $this->asset->subtotal_incl > 0 )) {
                $amount = ($this->weight / $this->asset->weight) * $this->asset->subtotal_incl;
                $assignment->amount = $amount;
            }
            $assignment->exchange_amount = $this->asset->exchange_amount;
            $assignment->exchange_rate = $this->asset->exchange_rate;
            $assignment->status = 1;
            $assignment->save();

            $serial_number = Asset::find($this->selectedAsset)->serial_number;

            $dispatch = new AssetDispatch;
            $dispatch->user_id = Auth::user()->id;
            $dispatch->asset_assignment_id = $assignment->id;
            $dispatch->asset_id = $this->selectedAsset;
            $dispatch->employee_id = $this->employee_id;
            $dispatch->branch_id = $this->branch_id;
            $dispatch->department_id = $this->department_id;
            $dispatch->issue_date = $this->start_date;
            if ($serial_number) {
                $dispatch->serial_number = $serial_number;
            }
          
            $dispatch->save();

            $asset = Asset::find($this->selectedAsset);
            $this->asset->status = 0;
            $this->asset->update();


        $this->dispatchBrowserEvent('hide-asset_assignmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Asset Assigned Successfully!!"
        ]);


      
    }

    public function edit($id){
        
        $assignment = AssetAssignment::find($id);
        $this->asset_assignment_id = $assignment->id;
        $this->selectedAsset = $assignment->asset_id;
        $this->selectedCategory =   $assignment->asset->category_id;
        $this->selectedCategoryValue =   $assignment->asset->category_value_id;
        $this->branch_id = $assignment->branch_id;
        $this->asset_dispatch_id = $assignment->asset_dispatch->id;
        $this->department_id = $assignment->department_id;
        $this->weight = $assignment->weight;
        $this->employee_id = $assignment->employee_id;
        $this->start_date = $assignment->start_date;
        $this->specifications = $assignment->specifications;
        $this->status = $assignment->status;
        $this->user_id = $assignment->user_id;
        $this->assets = Asset::with('product')->get()->sortBy('product.name');
        $this->dispatchBrowserEvent('show-asset_assignmentEditModal');
    }

    public function update(){
        try{

        $assignment =  AssetAssignment::find($this->asset_assignment_id);
         $assignment->user_id = Auth::user()->id;
        $assignment->asset_id = $this->selectedAsset;
        $assignment->branch_id = $this->branch_id;
        $assignment->department_id = $this->department_id;
        $assignment->employee_id = $this->employee_id;
        $assignment->start_date = $this->start_date;
        $assignment->specifications = $this->specifications;
        $assignment->update();

        $serial_number = Asset::find($this->selectedAsset)->serial_number;

        $dispatch = AssetDispatch::find($this->asset_dispatch_id);
        $dispatch->user_id = Auth::user()->id;
        $dispatch->asset_assignment_id = $assignment->id;
        $dispatch->asset_id = $this->selectedAsset;
        $dispatch->employee_id = $this->employee_id;
        $dispatch->branch_id = $this->branch_id;
        $dispatch->department_id = $this->department_id;
        $dispatch->issue_date = $this->start_date;
        if ($serial_number) {
            $dispatch->serial_number = $serial_number;
        }
        $dispatch->update();

        $asset = Asset::find($this->selectedAsset);
        $asset->status = 0;
        $asset->update();

        $this->dispatchBrowserEvent('hide-asset_assignmentEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Asset Assignment Updated Successfully!!"
        ]);


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating cargo!!"
        ]);
    }
    }
    public function render()
    {
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->asset_assignments = AssetAssignment::latest()->get();
        } else {
            $this->asset_assignments = AssetAssignment::where('user_id',Auth::user()->id)->latest()->get();
        }
      
        return view('livewire.asset-assignments.index',[
            'asset_assignments' => $this->asset_assignments,
            'assets' => $this->assets,
        ]);
    }
}
