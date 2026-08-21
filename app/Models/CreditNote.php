<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditNote extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function journal_entry(){
        return $this->hasOne(JournalEntry::class)->where('status', '!=', 'reversed')->latestOfMany('id');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function sale(){
        return $this->belongsTo('App\Models\Sale');
    }
    public function credit_note_items(){
        return $this->hasMany('App\Models\CreditNoteItem');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }

}
