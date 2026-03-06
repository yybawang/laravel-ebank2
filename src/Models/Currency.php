<?php

namespace yybawang\ebank\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 币种
 */
class Currency extends Model
{
    protected $table = 'ebank_currencies';
    protected $guarded = [];
}
