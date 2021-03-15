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



/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class GenerateIPFSForPost extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "post:ipfs {post_id}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "generate ipfs for single post";
    private $_postRepository;


    public function __construct(PostRepository $postRepository)
    {   
        parent::__construct();
        $this->_postRepository = $postRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $postId = $this->argument('post_id');
         $this->_postRepository->generatePostIPFS($postId);
    }
}
