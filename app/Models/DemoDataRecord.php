<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A row created while loading a demo batch (table + id). */
class DemoDataRecord extends Model
{
    public $timestamps = false;

    protected $fillable = ['demo_data_batch_id', 'table_name', 'record_id', 'model_type', 'created_at'];

    public function batch()
    {
        return $this->belongsTo(DemoDataBatch::class, 'demo_data_batch_id');
    }
}
