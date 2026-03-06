<?php

namespace yybawang\ebank\Commands;

use Illuminate\Console\Command;
use yybawang\ebank\Models\Identity;

class MakeIdentityCommand extends Command
{
    public $signature = 'make:identity';

    public $description = 'Create a new identity by laravel-ebank';

    public function handle(): int {
        $name = $this->ask('输入身份名称');
        if(!$name){
            $this->error("请填写身份名称");
            return self::FAILURE;
        }
        $code = $this->ask('输入身份代码（英文字符）');
        if(!$code){
            $this->error("请填写身份代码");
            return self::FAILURE;
        }
        $exists_name = Identity::where('name', $name)->exists();
        if($exists_name){
            $this->error("身份名「{$name}」已在数据表存在，请保持唯一");
            return self::FAILURE;
        }
        $exists_code = Identity::where('code', $code)->exists();
        if($exists_code){
            $this->error("身份代码「{$code}」已在数据表存在，请保持唯一");
            return self::FAILURE;
        }

        $identity = Identity::create([
            'name' => $name,
            'code' => $code,
        ]);

        $this->comment('已完成身份创建，身份代码 '.$identity->code);
        return self::SUCCESS;
    }
}
