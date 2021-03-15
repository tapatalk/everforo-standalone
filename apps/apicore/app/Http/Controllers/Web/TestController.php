<?php

namespace App\Http\Controllers\Web;

use Event;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\NewPostRequest;
use App\Http\Requests\Web\EditPostRequest;
use App\Http\Requests\Web\DeletePostRequest;
use App\Http\Requests\Web\NewThreadRequest;
use App\Repositories\CommentsRepository;
use App\Repositories\NotificationsRepository;
use App\Events\ThreadActivitiesEvent;
use App\Events\PostActivitiesEvent;
use App\Models\Post;

use App\Models\Thread;
use App\Utils\Transformer;
use App\Models\Attachment;
use App\Repositories\IPFS\IPFSRepository;
use App\Repositories\PostRepository;
use App\Repositories\PushRepository;
use App\Repositories\IPFS\IPFSThreadRepository;
use App\Jobs\PostNsfwJob;
use Illuminate\Http\Request;
use Log, Queue;
class TestController extends Controller
{
	  /**
     * ProfileController constructor.
     *
     */
    private $_transformer;
    private $_group;


    public function __construct(Transformer $transformer)
    {
      $this->_transformer = $transformer;
      $this->_group = config('app.group');
     //   $this->middleware('auth', ['except' => 'show']);
    //    $this->middleware('auth:optional', ['only' => 'show']);
    }


    public function testPostNsfw(Request $reqeust){
         $payload = array();
         	$post_id = 1010;
        
            Queue::push(new PostNsfwJob($post_id));
    }

}