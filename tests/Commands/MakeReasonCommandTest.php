<?php

use Symfony\Component\Console\Command\Command;
use function Pest\Laravel\artisan;

it('can assign a chat to the new reason', function(){
    artisan('make:reason')
        ->expectsQuestion('选择入账身份类型', '用户')
        ->assertExitCode(Command::SUCCESS);
});
