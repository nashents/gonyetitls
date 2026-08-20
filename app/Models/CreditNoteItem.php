<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditNoteItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'credit_note_id',
        'invoice_product_id',
        'tax_id',
        'item',
        'description',
        'qty',
        'amount',
        'tax_amount',
        'subtotal',
        'subtotal_inclusive',
    ];

    public function credit_note(){
        return $this->belongsTo('App\Models\CreditNote');
    }
    public function invoice_product(){
        return $this->belongsTo('App\Models\InvoiceProduct');
    }
    public function tax(){
        return $this->belongsTo('App\Models\Tax');
    }
}
