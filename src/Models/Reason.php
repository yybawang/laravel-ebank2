<?php

namespace yybawang\ebank\Models;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $table = 'ebank_reasons';
    protected $guarded = [];

    public function identity(){
        return $this->belongsTo(Identity::class, 'identity_id');
    }

    public function currency(){
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
