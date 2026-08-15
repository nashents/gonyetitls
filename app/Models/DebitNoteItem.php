<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DebitNoteItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function debit_note(){
        return $this->belongsTo('App\Models\DebitNote');
    }
    public function bill_expense(){
        return $this->belongsTo('App\Models\BillExpense');
    }
}
