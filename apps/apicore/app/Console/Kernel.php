<?php

namespace App\Console;

use App\Jobs\VerifyImportJob;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Mlntn\Console\Commands\Serve::class,
         Commands\GenerateIPFSForPost::class,
         Commands\ERC20tokenImportLog::class
    ];


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

    }


    public function run_erc20token_import_log(){

    }


}
