<?php

namespace App\Http\Controllers\Web;

use App\Repositories\GroupRepository;
use Event;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LikePostRequest;

use App\Utils\Transformer;

use App\Repositories\BanUsersRepository;
use App\Repositories\CommentsRepository;
use App\Repositories\LikeRepository;
use App\Repositories\NotificationsRepository;

use App\Repositories\AirdropRepo;
use App\Repositories\UserRepo;
use App\Repositories\PushRepository;
use App\Repositories\GroupFollowRepository;
use App\Events\PostActivitiesEvent;

use App\Models\Post;
use App\Models\Thread;
use App\Models\Attachment;
use App\Models\Like;
use App\Repositories\FeaturesRepo;
use App\Repositories\MemberListRepository;
use App\Repositories\SubscribeRepository;
use App\Repositories\UserBehaviorRepository;
use App\Utils\Constants;

class LikeController extends Controller
{
        /**
     * ProfileController constructor.
     *
     */
    private $_transformer;
    private $_group;
    private $likeRepo;


    public function __construct(Transformer $transformer, LikeRepository $likeRepo)
    {
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
        $this->likeRepo = $likeRepo;
    }

    /**
     * like a post
     * @param LikePostRequest $request
     * @return \App\Utils\json|array
     */
    public function like(LikePostRequest $request,
                         CommentsRepository $commentRep,
                         NotificationsRepository $notificationsRep,
                         GroupFollowRepository $groupFollowRep,
                         PushRepository $pushRep,
                         AirdropRepo $airdropRepo,
                         BanUsersRepository $banUsersRep,
                         SubscribeRepository $subscribeRep,
                         FeaturesRepo $featuresRep,
                         MemberListRepository $memberListRep
    )
    {
        $user = $request->user();
        $data = $request->all();
        $post = Post::find($data['post_id']);

        //if user ban  can not like post
        if ($banUsersRep->checkUserBan($user->id, $post->group_id)) {
            return $this->_transformer->fail(401, "You have been banned.");
        }

        if(UserRepo::isSilenceRegister($user)){
            return $this->_transformer->fail(400, "please register");
        }

        if (!$post) {
            return $this->_transformer->fail(400, "post not found");
        }

        //check user follow group
        if (!$groupFollowRep->isCanFollow($user->id, $this->_group->id)) {
            return $this->_transformer->fail(40003);
        }


        list($like, $send_notification) = $this->likeRepo->likePost($user->id, $post->id, $post->group_id, $post->user_id);

        $thread = Thread::find($post->thread_id);
        $thread = tap($thread)->update(['likes_count' => ($thread->likes_count + 1)]);

        //update member likes count
        $groupFollowRep->updateGroupMemberInfo($post->user_id, $post->group_id, 1);

        // update post tree cache for sorting
        // $commentRep->addLikeToCache($post->group_id, $post->thread_id, $post);
        // auto gollow group
        $groupFollowRep->followGroup($user->id, $post->group_id);
        if ($send_notification && $user->id != $post->user_id 
            && $groupFollowRep->isGroupFollowed($post->user_id, $post->group_id)){
            $notifications = $notificationsRep->addLikeNotifications($post, $user->id);

            Event::fire(new PostActivitiesEvent($thread, $post, $user));
            $subscribers = array();
            $subscribers[] = $post->user_id;
            $type = 'like';
            $pushRep->sendPushJobs($post->group_id, $subscribers, $thread, $post, $user,$type);
        }

        // if user unsubscribe thread,auto subscribe thread
        if ($featuresRep->isFeature($this->_group->id, Constants::SUBSCRIBE_FEATURE)) {
            //check owner subscribe status,send subscribe mail
            $subscribeRep->sendSubscribeMail($this->_group->name, $this->_group->title, $thread,$post->id, $post->id, $user->id, $groupFollowRep, $this->_group->id, 'liked');
            //Used to judge subscribe status
            if (!$subscribeRep->isThreadSubscribed($user->id, $post->thread_id, true)) {
                $like->is_subscribe = 1;
            $subscribeRep->subscribeThread($user->id, $post->thread_id, $post->group_id);
            }
        }

        $memberListRep->addMemberActiveRecord($this->_group->id, $user->id);

        //airdrop like
        if($send_notification && $user->id != $post->user_id ){
            $airdropRepo->airdrop_hook('receive_likes', $this->_group->id, $post->user_id);
        }

        return $this->_transformer->success(['like' => $like]);
    }

    /**
     * 
     */
    public function unlike(LikePostRequest $request,
                    CommentsRepository $commentRep,
                    GroupFollowRepository $groupFollowRep,
                    BanUsersRepository $banUsersRep
    )
    {
        $user = $request->user();
        $post_id = $request->input('post_id');
        $post = Post::find($post_id);

        if (!$post) {
            $this->_transformer->fail(400, "post not found");
        }
        if ($banUsersRep->checkUserBan($user->id, $this->_group->id)) {
            return $this->_transformer->fail(401, 'You have been banned.');
        }

        $this->likeRepo->unLikePost($user->id, $post->id);

        $thread = Thread::find($post->thread_id);
        $thread = tap($thread)->update(['likes_count' => ($thread->likes_count - 1)]);

        //update member likes count
        $groupFollowRep->updateGroupMemberInfo($post->user_id, $post->group_id, - 1);

        // update post tree cache for sorting
        // $commentRep->removeLikeFromCache($post->group_id, $post->thread_id, $post);

        return $this->_transformer->success(['like' => ['post_id' => $post->id, 'user_id' => $user->id]]);
    }

    public function likeList($post_id)
    {
        if (!$post_id) {
            return $this->_transformer->fail(403, 'Post not exists');
        }

        $like_list = $this->likeRepo->userList($post_id, $this->_group->id);

        foreach ($like_list as $key => $value) {
            if (UserBehaviorRepository::checkUserOnline($value['user_id'])) {
                $like_list[$key]['online'] = true;
            } else {
                $like_list[$key]['online'] = false;
            }
        }

        return $this->_transformer->success(['like_list' => $like_list]);
    }
}