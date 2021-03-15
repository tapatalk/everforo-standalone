<?php
/**
 *
 * PHP version >= 7.0
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */

namespace App\Console\Commands;


use App\Repositories\PostRepository;

use Exception;
use Illuminate\Console\Command;
use App\Jobs\ERC20TokenJob;



/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class ERC20tokenImportLog extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "token:verify";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "cron import token to wallet";

    public function __construct()
    {   
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $obj = new ERC20TokenJob([]);
        $obj::verifyERC20TokenImportToken();
    }

}
