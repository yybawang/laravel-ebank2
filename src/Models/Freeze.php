<?php

namespace yybawang\ebank\Models;

use Illuminate\Database\Eloquent\Model;
use yybawang\ebank\Enums\FreezeStatusEnum;

class Freeze extends Model
{
    protected $table = 'ebank_freezes';
    protected $guarded = [];
    protected $casts = [
        'status' => FreezeStatusEnum::class,
    ];

    public function wallet(){
        return $this->belongsTo(Wallet::class);
    }
}
