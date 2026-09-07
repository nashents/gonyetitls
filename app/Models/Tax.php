<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tax extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function quotation_items(){
        return $this->hasMany('App\Models\QuotationItem');
    }
    public function quotation_products(){
        return $this->hasMany('App\Models\QuotationProduct');
    }
    public function invoice_items(){
        return $this->hasMany('App\Models\InvoiceItem');
    }
    public function sale_items(){
        return $this->hasMany('App\Models\SaleItem');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }

    /** Sage Intacct link (entity_type tax_group) for the sync badge/status. */
    public function sageMapping(){
        return $this->hasOne(\App\Models\IntegrationMapping::class, 'local_id')
            ->where('entity_type', 'tax_group');
    }

    protected $fillable =[
        'name',
        'abbreviation',
        'description',
        'account_id',
        'user_id',
        'rate',
        'hs_code',
    ];
}
