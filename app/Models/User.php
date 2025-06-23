<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'active',
        'is_admin',
        'category',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function is_admin(){
        if($this->is_admin){
            return true;
        }else{
            return false;
        }

    }
    public function bins(){
        return $this->hasMany('App\Models\Bin');
    }
     public function exchange_rates(){
        return $this->hasMany('App\Models\ExchangeRate');
    }
    public function racks(){
        return $this->hasMany('App\Models\Rack');
    }
      public function rehandlings(){
        return $this->hasMany('App\Models\Rehandling');
    }
      public function shifts(){
        return $this->hasMany('App\Models\Shift');
    }
      public function works(){
        return $this->hasMany('App\Models\Work');
    }
      public function location(){
        return $this->hasMany('App\Models\Location');
    }
    public function tax_brackets(){
        return $this->hasMany('App\Models\TaxBracket');
    }
    public function transfers(){
        return $this->hasMany('App\Models\Transfer');
    }
    public function breakages(){
        return $this->hasMany('App\Models\Breakage');
    }
    public function emptyruns(){
        return $this->hasMany('App\Models\EmptyRun');
    }
    public function disposes(){
        return $this->hasMany('App\Models\Dispose');
    }
    public function rate_cards(){
        return $this->hasMany('App\Models\RateCard');
    }
    public function retreads(){
        return $this->hasMany('App\Models\Retread');
    }

    public function notifications(){
        return $this->hasMany('App\Models\Notifications');
    }
    public function sales(){
        return $this->hasMany('App\Models\Sale');
    }
    
    public function loss_groups(){
        return $this->hasMany('App\Models\LossGroup');
    }
    public function consignees(){
        return $this->hasMany('App\Models\Consignee');
    }
    public function purchases(){
        return $this->hasMany('App\Models\Purchase');
    }
    public function loss_categories(){
        return $this->hasMany('App\Models\LossCategory');
    }
    public function losses(){
        return $this->hasMany('App\Models\Loss');
    }
    public function requisitions(){
        return $this->hasMany('App\Models\Requisition');
    }
    public function workshop_services(){
        return $this->hasMany('App\Models\WorkshopService');
    }
    public function taxes(){
        return $this->hasMany('App\Models\Tax');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function logs(){
        return $this->hasMany('App\Models\Log');
    }
    public function goods_receiveds(){
        return $this->hasMany('App\Models\GoodsReceived');
    }
    public function quotations(){
        return $this->hasMany('App\Models\Quotation');
    }
    public function employee(){
        return $this->hasOne('App\Models\Employee');
    }
    public function driver(){
        return $this->hasOne('App\Models\Driver');
    }
    public function accounts(){
        return $this->hasMany('App\Models\Account');
    }
    public function transporter(){
        return $this->hasOne('App\Models\Transporter');
    }
    public function trip_locations(){
        return $this->hasMany('App\Models\TripLocation');
    }
    public function loan_types(){
        return $this->hasMany('App\Models\LoanType');
    }
    public function loans(){
        return $this->hasMany('App\Models\Loan');
    }
    public function payrolls(){
        return $this->hasMany('App\Models\Payroll');
    }
    public function salary_items(){
        return $this->hasMany('App\Models\SalaryItem');
    }
    public function salary(){
        return $this->hasMany('App\Models\Salary');
    }
    public function loading_points(){
        return $this->hasMany('App\Models\LoadingPoint');
    }
    public function offloading_points(){
        return $this->hasMany('App\Models\OffloadingPoint');
    }
    public function deductions(){
        return $this->hasMany('App\Models\Deduction');
    }
    public function cargos(){
        return $this->hasMany('App\Models\Cargo');
    }
    public function income_streams(){
        return $this->hasMany('App\Models\IncomeStream');
    }
    public function vendor(){
        return $this->hasOne('App\Models\Vendor');
    }
    public function broker(){
        return $this->hasOne('App\Models\Broker');
    }
    public function invoices(){
        return $this->hasMany('App\Models\Invoice');
    }
    public function agent(){
        return $this->hasOne('App\Models\Agent');
    }
    public function trip_positions(){
        return $this->hasMany('App\Models\TripPosition');
    }
    public function recoveries(){
        return $this->hasMany('App\Models\Recovery');
    }
    public function customer(){
        return $this->hasOne('App\Models\Customer');
    }
    public function invoice_trips(){
        return $this->hasMany('App\Models\InvoiceTrip');
    }
    public function containers(){
        return $this->hasMany('App\Models\Container');
    }
    public function credit_notes(){
        return $this->hasMany('App\Models\CreditNote');
    }
    public function tyres(){
        return $this->hasMany('App\Models\Tyre');
    }
    public function top_ups(){
        return $this->hasMany('App\Models\TopUp');
    }
    public function tyre_assignment(){
        return $this->hasMany('App\Models\TyreAssignment');
    }
    public function vehicles(){
        return $this->hasMany('App\Models\Vehicle');
    }
    public function trailers(){
        return $this->hasMany('App\Models\Trailer');
    }
    public function bookings(){
        return $this->hasMany('App\Models\Booking');
    }
    public function admin(){
        return $this->hasOne('App\Models\Admin');
    }
    public function company(){
        return $this->hasOne('App\Models\Company');
    }
    public function fitnesses(){
        return $this->hasMany('App\Models\Fitness');
    }
    public function notices(){
        return $this->hasMany('App\Models\Notice');
    }
    public function inspections(){
        return $this->hasMany('App\Models\Inspection');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function horses(){
        return $this->hasMany('App\Models\Horse');
    }
    public function permissions(){
        return $this->hasMany('App\Models\Permission');
    }
    public function fuels(){
        return $this->hasMany('App\Models\Fuel');
    }
    public function leaves(){
        return $this->hasMany('App\Models\Leave');
    }
    public function branches(){
        return $this->hasMany('App\Models\Branch');
    }
    public function departments(){
        return $this->hasMany('App\Models\Department');
    }
    public function mails(){
        return $this->hasMany('App\Models\Mail');
    }
    public function roles(){
        return $this->belongsToMany('App\Models\Role');
    }
}
