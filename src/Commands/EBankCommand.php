<?php

namespace yybawang\ebank\Commands;

use Illuminate\Console\Command;

class EBankCommand extends Command
{
    public $signature = 'laravel-ebank';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
