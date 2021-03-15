<?php
/**
 *
 * PHP version >= 7.0
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */

namespace App\Console\Commands;


use App\Repositories\IPFS\IPFSGroupRepository;

use Exception;
use Illuminate\Console\Command;



/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class GenerateIPFSForGroup extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "group:ipfs";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "generate ipfs for single post";
    private $_groupRepository;


    public function __construct(IPFSGroupRepository $groupRepository)
    {   
        parent::__construct();
        $this->_groupRepository = $groupRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
     
         $this->_groupRepository->generateGroupIPFS();
    }
}
