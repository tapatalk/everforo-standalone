<?php

namespace App\Http\Controllers\Web;

use App\Models\Group;
use App\Http\Controllers\Controller;
use App\Models\Thread;

use DB;
use App\Models\Post;
use App\Models\ThreadTrack;
use Illuminate\Http\Request;

use App\Repositories\BanUsersRepository;
use App\Repositories\CommentsRepository;
use App\Repositories\GroupRepository;
use App\Repositories\PostRepository;

use App\Models\Forum;
use App\Repositories\GroupPinRepository;
use App\Repositories\SubscribeRepository;
use App\Repositories\UserBehaviorRepository;
use App\Utils\Constants;
use App\Utils\Transformer;
use App\Utils\StringHelper;

class ThreadController extends Controller
{
    private $_transformer;
    private $_group;
    /**
     * ProfileController constructor.
     *
     */
    public function __construct(Transformer $transformer)
    {
        //   $this->middleware('auth', ['except' => 'show']);
        //    $this->middleware('auth:optional', ['only' => 'show']);
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
    }

    /**
     * @param Request $request
     * @param $thread_id
     * @param $sort
     * @param $page
     * @param int $post_number
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getThread(Request $request,
                            BanUsersRepository $banUsersRep,
                            SubscribeRepository $subscribeRep,
                            CommentsRepository $commentsRep,
                            GroupPinRepository $groupPinRep,
                            $thread_id, $sort, $page, $post_id = 0)
    {
        $group_id = $this->_group->id;
        $user = $request->user();
        $page_number = 10;
        $sort = strtolower($sort);
        $is_loadmore = true;
        // when pass `post_id` in parameters, we need to calculate the corrent `page` which the `post_id` lay in 
        // if it doesn't coincide with the `page` passed in parameter, we return it in the response, 
        // and let the frontend correct the url on browser 
        $return_page = false;
        $url = 'http://apiprivacy/apiprivacy/check_group_feature?group_id=' . $this->_group->id;
        if ($user) {
            $url = $url . '&user_id=' . $user->id;
        }
        if (!geturl($url)) {
            return $this->_transformer->fail(40003);
        }
        // find page by post_id
        if ($post_id) {
            $calculated_page = $commentsRep->getPageByPostId($group_id, $thread_id, $post_id, $sort, $page_number, $user);

            if ($calculated_page != $page) {
                $is_loadmore = false;
                $return_page = true;
                $page = $calculated_page;
            }
        }

        // makes no sense!!!!
        // if user ban  can not browse this thread
        // $res = $banUsersRep->checkUserBan($user->id, $group_id);
        // if ($res) {
        //     return $this->_transformer->fail('400', 'You can not browse this thread');
        // }

        $thread = Thread::with(['category' => function ($query) use ($group_id) {
                            $query->where('group_id', $group_id)->whereNull('deleted_at');
                        }, 'user', 'first_post', 'first_post.likes', 'first_post.flags', 'first_post.deletedBy', 'first_post.attachedFiles'])
                        ->where('id', $thread_id)
                        ->where('group_id', $group_id)
                        ->whereHas('first_post', function ($query) {
                            $query->where('deleted', '<>', 2);
                        })
                        ->first();

        if (!$thread) {
            return $this->_transformer->fail(404, 'thread not found');
        }

        // add is ban use check user ban
        $thread->first_post->is_ban = $banUsersRep->checkUserBan($thread->user_id, $thread->group_id) ? 1 : 0;
        $thread->first_post->online = UserBehaviorRepository::checkUserOnline($thread->user_id) ? true : false;
        //$comment_tree = CommentTree::buildTree($thread_id);
        $comment_tree = $commentsRep->getComments($group_id, $thread_id, $sort, $page, $page_number, $is_loadmore, $user);
        
        $thread->slug = StringHelper::slugify($thread->title,$thread->id);
        $thread->posts = $comment_tree['tree'];
        if ($sort == 'relevant') {
            $thread->posts_count = $comment_tree['count']; 
         }
        //add like user ban status
        if ($thread->first_post->likes) {
            foreach($thread->first_post->likes as $key=>$like) {
                if ($banUsersRep->checkUserBan($like->user_id, $thread->group_id)) {
                    $thread->first_post->likes[$key]->is_ban = 1;
                } else {
                    $thread->first_post->likes[$key]->is_ban = 0;
                }
            }
        }

        //add user subscribe status
        $thread->is_subscribe = 0;
        if ($user && $user->id && $subscribeRep->isThreadSubscribed($user->id, $thread_id)) {
            $thread->first_post->is_subscribe = 1;
        }

        //add thread pin status
        $thread->is_pin = 0;
        if ($groupPinRep->checkThreadPin($group_id, $thread_id, Constants::GROUP_ADMIN_PIN) || $groupPinRep->checkThreadPin($group_id, $thread_id, Constants::SUPER_ADMIN_PIN)) {
            $thread->is_pin = 1;
            $thread->pin_user = $groupPinRep->getPinUser($thread_id);
        }

        if($user && $user->id){
            $track = ThreadTrack::firstOrNew(array('group_id' => $group_id, 'thread_id' => $thread->id, 'user_id' => $user->id));
            if($track->exists){
                $track->touch();
            }
        }

        $response = ['thread' => $thread];

        if ($return_page) {
            $response['page'] = $calculated_page;
        }

        return $this->_transformer->success($response);
    }

    /**
     * load more feature
     * load a sub post tree
     */
    public function loadMore(Request $request, CommentsRepository $commentsRep, PostRepository $postRep, 
                            $sort, $post_id)
    {
        $post = $postRep->getOne($post_id);
        $user = $request->user();
        if (!$post) {
            return $this->_transformer->fail(404, 'post not found');
        }

        $posts = $commentsRep->getSubComments($post->group_id, $post->thread_id, $post->id, $sort, $user);

        return $this->_transformer->success(['posts' => $posts]);
    }

}