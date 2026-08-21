<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DebitNote extends Model implements Auditable
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
    public function bill(){
        return $this->belongsTo('App\Models\Bill');
    }
    public function debit_note_items(){
        return $this->hasMany('App\Models\DebitNoteItem');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }

}
