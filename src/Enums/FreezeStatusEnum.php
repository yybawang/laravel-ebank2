<?php

namespace yybawang\ebank\Enums;

enum FreezeStatusEnum: string
{
    case FREEZING = 'FREEZING';
    case UNFREEZE = 'UNFREEZE';

    public function name(){
        return match($this){
            self::FREEZING => '冻结中',
            self::UNFREEZE => '已解冻',
        };
    }
}
