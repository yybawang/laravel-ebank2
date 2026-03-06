<?php

namespace yybawang\ebank\Concerns;

use Illuminate\Support\Facades\DB;
use yybawang\ebank\Enums\FreezeStatusEnum;
use yybawang\ebank\Models\Currency;
use yybawang\ebank\Models\Freeze;
use yybawang\ebank\Models\Identity;
use yybawang\ebank\Models\Wallet;

trait WalletConcerns
{
    /**
     * 返回会员可用余额（不包含冻结中）
     * @param int $user_id
     * @param string $currency_code 货币代码
     * @param string $identity_code 身份代码
     * @return string
     */
    public function balance(int $user_id, string $currency_code = 'cash', string $identity_code = 'user'): string {
        $wallet = $this->getWallet($user_id, $this->getCurrencyId($currency_code), $this->getIdentityId($identity_code));
        return $wallet->balance;
    }

    /**
     * 返回冻结中余额
     * @param int $user_id
     * @param string $currency_code 货币代码
     * @param string $identity_code 身份代码
     * @return string
     */
    public function freezing(int $user_id, string $currency_code = 'cash', string $identity_code = 'user'): string {
        $wallet = $this->getWallet($user_id,  $this->getCurrencyId($currency_code), $this->getIdentityId($identity_code));
        return $wallet->freezing;
    }

    /**
     * 冻结钱包余额
     * @param int $user_id
     * @param float $amount
     * @param string $currency_code
     * @param string $identity_code
     * @return int
     */
    public function freeze(int $user_id, float $amount, string $currency_code = 'cash', string $identity_code = 'user'): int {
        if($amount <= 0){
            return 0;
        }
        $wallet = $this->getWallet($user_id,  $this->getCurrencyId($currency_code), $this->getIdentityId($identity_code));
        abort_if($wallet->balance < $amount, 422, '余额不足以冻结');
        $freeze = DB::transaction(function() use ($wallet, $amount){
            $freeze = Freeze::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'status' => FreezeStatusEnum::FREEZING,
            ]);
            $wallet->update([
                'balance' => DB::raw("balance - $amount"),
                'freezing' => DB::raw("freezing + $amount"),
            ]);
            return $freeze;
        });

        return $freeze->id;
    }

    /**
     * 根据冻结ID 解冻余额
     * @param int $freeze_id
     * @return void
     */
    public function unfreeze(int $freeze_id): void {
        if($freeze_id <= 0){
            return;
        }
        $freeze = Freeze::where(['id' => $freeze_id])->first();
        abort_if(!$freeze, 422, '未找到冻结记录 '.$freeze_id);
        abort_if($freeze->status != FreezeStatusEnum::FREEZING, 422, '冻结记录 '.$freeze_id.' 已被处理过，不再支持继续解冻');
        $wallet = Wallet::find($freeze->wallet_id);
        abort_if(!$wallet, 422, '未找到钱包数据 '. $freeze->wallet_id);
        DB::transaction(function() use ($freeze, $wallet){
            $wallet->update([
                'freezing' => DB::raw("freezing - $freeze->amount"),
                'balance' => DB::raw("balance + $freeze->amount"),
            ]);
            $freeze->update(['status' => FreezeStatusEnum::UNFREEZE]);
        });
    }

    /**
     * 币种代码转币种ID
     * @param string $currency_code
     * @return mixed
     */
    protected function getCurrencyId(string $currency_code){
        $currency_id = Currency::where('code', $currency_code)->value('id');
        abort_if(!$currency_id, 422, '未找到币种代码 '.$currency_code);
        return $currency_id;
    }

    /**
     * 身份代码转身份ID
     * @param string $identity_code
     * @return mixed
     */
    protected function getIdentityId(string $identity_code){
        $identity_id = Identity::where('code', $identity_code)->value('id');
        abort_if(!$identity_id, 422, '未找到身份代码 '.$identity_code);
        return $identity_id;
    }

    /**
     * 钱包模型
     * @param int $user_id
     * @param int $currency_id
     * @param int $identity_id
     * @return Wallet
     */
    protected function getWallet(int $user_id, int $currency_id, int $identity_id){
        return Wallet::firstOrCreate([
            'user_id' => $user_id,
            'currency_id' => $currency_id,
            'identity_id' => $identity_id,
        ], [
            'balance' => '0.0000',
            'freezing' => '0.0000',
        ]);
    }
}
