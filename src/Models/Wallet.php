<?php

namespace yybawang\ebank\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 钱包
 */
class Wallet extends Model
{
    protected $table = 'ebank_wallets';
    protected $guarded = [];

    public function identity(){
        return $this->belongsTo(Identity::class);
    }

    public function currency(){
        return $this->belongsTo(Currency::class);
    }
}
