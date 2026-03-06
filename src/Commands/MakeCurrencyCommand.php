<?php

namespace yybawang\ebank\Commands;

use Illuminate\Console\Command;
use yybawang\ebank\Models\Currency;

class MakeCurrencyCommand extends Command
{
    public $signature = 'make:currency';

    public $description = 'Create a new currency by laravel-ebank';

    public function handle(): int {
        $name = $this->ask('输入币种名称');
        if(!$name){
            $this->error("请填写币种名称");
            return self::FAILURE;
        }
        $code = $this->ask('输入币种代码（英文字符）');
        if(!$code){
            $this->error("请填写币种代码");
            return self::FAILURE;
        }
        $exists_name = Currency::where('name', $name)->exists();
        if($exists_name){
            $this->error("币种名「{$name}」已在数据表存在，请保持唯一");
            return self::FAILURE;
        }
        $exists_code = Currency::where('code', $code)->exists();
        if($exists_code){
            $this->error("币种代码「{$code}」已在数据表存在，请保持唯一");
            return self::FAILURE;
        }

        $identity = Currency::create([
            'name' => $name,
            'code' => $code,
        ]);

        $this->comment('已完成币种创建，币种代码 '.$identity->code);
        return self::SUCCESS;
    }
}
