<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/** Allowable loss % agreed with one customer for one cargo (e.g. ZUVA / Diesel 0.3%). */
class CustomerCargoLossAllowance extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['customer_id', 'cargo_id', 'allowable_loss_percentage', 'user_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}
