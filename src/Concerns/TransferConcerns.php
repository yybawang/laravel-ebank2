<?php

namespace yybawang\ebank\Concerns;

use Illuminate\Support\Facades\DB;
use yybawang\ebank\Models\Reason;
use yybawang\ebank\Models\Transfer;
use yybawang\ebank\Models\Wallet;

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

        // 先在事务外确保钱包存在（避免 firstOrCreate 在事务内产生 gap lock 导致死锁）
        $wallet = $this->getWallet($user_id, $reason->currency_id, $reason->identity_id);

        $transfer_id = DB::transaction(function() use ($wallet, $user_id, $amount, $reason, $description, $business_data){
            // 使用 SELECT ... FOR UPDATE 在数据库层面串行化对同一钱包行的并发访问
            Wallet::lockForUpdate()->where('id', $wallet->id)->update(['balance' => DB::raw('balance + '.$amount)]);

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
        });

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
    public function faceToFace(int $from_user_id, int $to_user_id, float $amount, string $from_reason_code, string $to_reason_code, string $description = '', ?array $business_data = null): void {
        if($amount == 0){
            return;
        }
        $from_reason = $this->getReason($from_reason_code);
        $to_reason = $this->getReason($to_reason_code);

        // 先在事务外确保钱包存在（避免 firstOrCreate 在事务内产生 gap lock 导致死锁）
        $from_wallet = $this->getWallet($from_user_id, $from_reason->currency_id, $from_reason->identity_id);
        $to_wallet = $this->getWallet($to_user_id, $to_reason->currency_id, $to_reason->identity_id);

        DB::transaction(function() use ($from_wallet, $to_wallet, $from_user_id, $to_user_id, $amount, $from_reason, $to_reason, $description, $business_data){
            Wallet::lockForUpdate()->where('id', $from_wallet->id)->update(['balance' => DB::raw('balance + '.$amount)]);
            Wallet::lockForUpdate()->where('id', $to_wallet->id)->update(['balance' => DB::raw('balance - '.$amount)]);

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
        });
    }

    protected function getReason(string $reason_code){
        $reason = Reason::where('code', $reason_code)->first();
        abort_if(!$reason, 422, '未找到转账 reason '. $reason_code);
        return $reason;
    }
}
