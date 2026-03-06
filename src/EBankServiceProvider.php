<?php

namespace yybawang\ebank;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use yybawang\ebank\Commands\EBankCommand;
use yybawang\ebank\Commands\MakeCurrencyCommand;
use yybawang\ebank\Commands\MakeIdentityCommand;
use yybawang\ebank\Commands\MakeReasonCommand;

class EBankServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-ebank')
//            ->hasConfigFile()
            ->hasMigration('create_laravel_ebank_table')
            ->hasCommand(MakeReasonCommand::class)
            ->hasCommand(MakeIdentityCommand::class)
            ->hasCommand(MakeCurrencyCommand::class)
            ->hasCommand(EBankCommand::class);
    }
}
