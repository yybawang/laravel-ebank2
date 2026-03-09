<?php

namespace yybawang\ebank\Concerns;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use yybawang\ebank\Models\Reason;
use yybawang\ebank\Models\Transfer;

trait TransferConcerns
{
    use WalletConcerns;

    /**
     * 交易转账
     * @param int $user_id
     * @param float $amount
     * @param string $reason_code
     * @param string $description
     * @param array|null $business_data
     * @return int
     */
    public function transfer(int $user_id, float $amount, string $reason_code, string $description = '', ?array $business_data = null): int {
        if($amount == 0){
            return 0;
        }
        $reason = $this->getReason($reason_code);
        $transfer_id = Cache::lock('ebank@transfer:'.$user_id.':'.$reason->currency_id.':'.$reason->identity_id)->block(10, fn() => DB::transaction(function() use ($user_id, $amount, $reason, $description, $business_data){
            $wallet = $this->getWallet($user_id, $reason->currency_id, $reason->identity_id);
            // 线程互抢时，如果有两笔入款两笔扣款，可能先扣款两笔导致异常退出而余额对不上，所以这里需允许为负数
//            abort_if($amount < 0 && $wallet->balance < -$amount, 422, '钱包 '.$wallet->id.' 可用余额不足');
            $wallet->increment('balance', $amount); // increment 也是兼容负数的
            $transfer = Transfer::create([
                'user_id' => $user_id,
                'reason_id' => $reason->id,
                'currency_id' => $reason->currency_id,
                'identity_id' => $reason->identity_id,
                'amount' => $amount,
                'balance' => $wallet->balance,
                'description' => $description,
                'business_data' => $business_data,
            ]);

            return $transfer->id;
        }));

        return $transfer_id;
    }

    /**
     * 面对面交易
     * @param int $from_user_id
     * @param int $to_user_id
     * @param float $amount
     * @param string $from_reason_code
     * @param string $to_reason_code
     * @param string $description
     * @param array|null $business_data
     * @return void
     */
    public function faceToFace(int $from_user_id, int $to_user_id, float $amount, string $from_reason_code, string $to_reason_code, string $description = '', ?array $business_data = []): void {
        if($amount == 0){
            return;
        }
        $from_reason = $this->getReason($from_reason_code);
        $to_reason = $this->getReason($to_reason_code);
        Cache::lock('ebank@faceToFace:'.'ebank@transfer:'.$from_user_id.':'.$to_user_id.':'.$from_reason->currency_id.':'.$from_reason->identity_id.':'.$to_reason->currency_id.':'.$to_reason->identity_id)->block(10, fn() => DB::transaction(function() use ($from_user_id, $to_user_id, $amount, $from_reason, $to_reason, $description, $business_data){
            $from_wallet = $this->getWallet($from_user_id, $from_reason->currency_id, $from_reason->identity_id);
            $to_wallet = $this->getWallet($to_user_id, $to_reason->currency_id, $to_reason->identity_id);
//            abort_if($amount > 0 && $from_wallet->balance < $amount, 422, '钱包 '.$from_wallet->id.' 可用余额不足');
//            abort_if($amount < 0 && $to_wallet->balance < -$amount, 422, '钱包 '.$to_wallet->id.' 可用余额不足');
            $from_wallet->decrement('balance', $amount);
            $to_wallet->increment('balance', $amount);

            Transfer::create([
                'user_id' => $from_user_id,
                'reason_id' => $from_reason->id,
                'currency_id' => $from_reason->currency_id,
                'identity_id' => $from_reason->identity_id,
                'amount' => -$amount,
                'balance' => $from_wallet->balance,
                'description' => $description,
                'business_data' => $business_data,
            ]);

            Transfer::create([
                'user_id' => $to_user_id,
                'reason_id' => $to_reason->id,
                'currency_id' => $to_reason->currency_id,
                'identity_id' => $to_reason->identity_id,
                'amount' => $amount,
                'balance' => $to_wallet->balance,
                'description' => $description,
                'business_data' => $business_data,
            ]);
        }));
    }

    protected function getReason(string $reason_code){
        $reason = Reason::where('code', $reason_code)->first();
        abort_if(!$reason, 422, '未找到转账 reason '. $reason_code);
        return $reason;
    }
}
