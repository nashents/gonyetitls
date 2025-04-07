<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceProduct extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function invoice_items(){
        return $this->hasMany('App\Models\InvoiceItem');
    }
    public function credit_note_items(){
        return $this->hasMany('App\Models\CreditNoteItem');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
}
