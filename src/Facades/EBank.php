<?php

namespace yybawang\ebank\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string balance(int $user_id, string $currency_code = 'cash', string $identity_code = 'user')
 * @method static string freezing(int $user_id, string $currency_code = 'cash', string $identity_code = 'user')
 * @method static int freeze(int $user_id, float $amount, string $currency_code = 'cash', string $identity_code = 'user')
 * @method static void unFreeze(int $freeze_id)
 * @method static int transfer(int $user_id, float $amount, string $reason_code, string $description = '', ?array $business_data = [])
 * @method static void faceToFace(int $from_user_id, int $to_user_id, float $amount, string $from_reason_code, string $to_reason_code, string $description = '', ?array $business_data = [])
 *
 * @see \yybawang\ebank\LaravelEBank
 */
class EBank extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \yybawang\ebank\LaravelEBank::class;
    }
}
