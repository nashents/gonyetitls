<?php

namespace App\Models;

use App\Models\EmployeeLeave;
use App\Models\JournalEntryLine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Employee extends Model implements Auditable
{
    use HasFactory,SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function grade(){
        return $this->belongsTo('App\Models\Grade');
    }
    public function journal_entry_lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
    public function employee_leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }
    public function goods_receiveds(){
        return $this->hasMany('App\Models\GoodsReceived');
    }
    public function work_dones(){
        return $this->hasMany('App\Models\WorkDone');
    }
    public function waste_collection_items(){
        return $this->hasMany('App\Models\WasteCollectionItem');
    }
   
    public function employee_positions(){
        return $this->hasMany('App\Models\EmployeePosition');
    }
    public function purchases(){
        return $this->hasMany('App\Models\Purchase');
    }
    public function vehicle_assignments(){
        return $this->hasMany('App\Models\VehicleAssignment');
    }
    public function teams(){
        return $this->belongsToMany('App\Models\Team');
    }
    public function gate_passes(){
        return $this->hasMany('App\Models\GatePass');
    }
      public function dispatches(){
        return $this->hasMany('App\Models\Dispatch');
    }
    public function dependants(){
        return $this->hasMany('App\Models\Dependant');
    }
    public function vehicle_assignment(){
        return $this->hasMany('App\Models\VehicleAssignment');
    }
    public function bank_account(){
        return $this->hasOne('App\Models\BankAccount');
    }
    public function movements(){
        return $this->hasMany('App\Models\Movement');
    }
    public function payroll_salaries(){
        return $this->hasMany('App\Models\PayrollSalary');
    }
     public function waste_disposals(){
        return $this->hasMany('App\Models\WasteDisposal');
    }
    public function notifications(){
        return $this->hasMany('App\Models\Notifications');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function compliances(){
        return $this->hasMany('App\Models\Compliance');
    }
    public function checklists(){
        return $this->hasMany('App\Models\Checklist');
    }
    public function trainings(){
        return $this->hasMany('App\Models\Training');
    }
    public function emails(){
        return $this->hasMany('App\Models\Email');
    }
    public function fitnesses(){
        return $this->hasMany('App\Models\Fitness');
    }
    public function bookings(){
        return $this->belongsToMany('App\Models\Booking');
    }
    public function inspections(){
        return $this->belongsToMany('App\Models\Inspection');
    }
    public function tickets(){
        return $this->belongsToMany('App\Models\Ticket');
    }
    public function logs(){
        return $this->hasMany('App\Models\Log');
    }
    public function loans(){
        return $this->hasMany('App\Models\Loan');
    }
    public function salaryAdvances(){
        return $this->hasMany('App\Models\SalaryAdvance');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function leaves(){
        return $this->hasMany('App\Models\Leave');
    }
    public function salaries(){
        return $this->hasMany('App\Models\Salary');
    }
   
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function asset_assignments(){
        return $this->belongsToMany('App\Models\AssetAssignment');
    }
    public function inventory_assignments(){
        return $this->hasMany('App\Models\InventoryAssignment');
    }
   
 
    public function cash_flows(){
        return $this->hasMany('App\Models\CashFlow');
    }
    public function asset_assignment(){
        return $this->hasMany('App\Models\AssetAssignment');
    }
    public function allocations(){
        return $this->hasMany('App\Models\Allocation');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function department_head(){
        return $this->hasOne('App\Models\DepartmentHead');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function ranks(){
        return $this->belongsToMany('App\Models\Rank');
    }
    public function departments(){
        return $this->belongsToMany('App\Models\Department');
    }
    public function branch(){
        return $this->belongsTo('App\Models\Branch');
    }
    public function driver(){
        return $this->hasOne('App\Models\Driver');
    }
  
    public function fuel_requests(){
        return $this->hasMany('App\Models\FuelRequest');
    }
    public function fuels(){
        return $this->hasMany('App\Models\Fuel');
    }
   

    protected $fillable = [ 
        'company_id',
        'creator_id',
        'user_id',
        'employee_number',
        'branch_id',
        'name',
        'middle_name',
        'surname',
        'dob',
        'gender',
        'post',
        'email',
        'pin',
        'idnumber',
        'phonenumber',
        'country',
        'city',
        'province',
        'suburb',
        'street_address',
        'start_date',   
        'duration',
        'expiration',
        'leave_days',
        'next_of_kin',
        'relationship',
        'contact',
        'status',
    ];
}
