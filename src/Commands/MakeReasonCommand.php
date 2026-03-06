<?php

namespace yybawang\ebank\Commands;

use Illuminate\Console\Command;
use yybawang\ebank\Models\Currency;
use yybawang\ebank\Models\Identity;
use yybawang\ebank\Models\Reason;

class MakeReasonCommand extends Command
{
    public $signature = 'make:reason';

    public $description = 'Create a new transfer reason by laravel-ebank';

    public function handle(): int
    {
        $identity_names = Identity::pluck('name', 'id');
        $currency_names = Currency::pluck('name', 'id');
        $name = $this->ask('输入转账场景名称');
        if(!$name){
            $this->error("请填写场景名称");
            return self::FAILURE;
        }
        $identity = $this->choice('选择出/入帐身份类型', $identity_names->values()->toArray());
        $currency = $this->choice('选择出/入帐钱包', $currency_names->values()->toArray());
        $tips = $this->ask('补充业务场景说明（可选）');

        $identity_id = $identity_names->flip()->only([$identity])->first();
        $currency_id = $currency_names->flip()->only([$currency])->first();
        $code = (Reason::max('id')+1).str_pad($identity_id, 2, '0', STR_PAD_LEFT).str_pad($currency_id, 2, '0', STR_PAD_LEFT);
        $exists = Reason::where(['code' => $code])->exists();
        if($exists){
            $this->error("Reason 代码「{$code}」已在数据表存在，请重试");
            return self::FAILURE;
        }

        $reason = Reason::create([
            'name' => $name,
            'code' => $code,
            'identity_id' => $identity_id,
            'currency_id' => $currency_id,
            'tips' => $tips ?? '',
        ]);

        $this->comment('已完成 Reason 创建，Reason 代码 '.$reason->code);

        return self::SUCCESS;
    }
}
