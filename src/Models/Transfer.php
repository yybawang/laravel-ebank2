<?php

namespace yybawang\ebank\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'ebank_transfers';
    protected $guarded = [];
    protected $casts = [
        'business_data' => 'json',
    ];

    public function reason(){
        return $this->belongsTo(Reason::class, 'reason_id');
    }

    public function identity(){
        return $this->belongsTo(Identity::class, 'identity_id');
    }

    public function currency(){
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
