<?php

namespace App\Http\Controllers\Web;

use App\Repositories\CategoryRepository;
use App\Repositories\GroupAdminRepository;
use App\Repositories\GroupRepository;
use Event;
use Illuminate\Http\Request;

use App\Utils\Transformer;
use App\Utils\StringHelper;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\NewPostRequest;
use App\Http\Requests\Web\EditPostRequest;
use App\Http\Requests\Web\DeletePostRequest;
use App\Http\Requests\Web\NewThreadRequest;

use App\Repositories\BanUsersRepository;
use App\Repositories\CommentsRepository;
use App\Repositories\NotificationsRepository;
use App\Repositories\GroupFollowRepository;
use App\Repositories\AttachedFilesRepository;

use App\Repositories\AirdropRepo;
use App\Repositories\UserRepo;
use App\Events\ThreadActivitiesEvent;
use App\Events\PostActivitiesEvent;
use App\Models\Post;
use App\Models\Thread;
use App\Models\Attachment;
use App\Repositories\IPFS\IPFSRepository;
use App\Repositories\PostRepository;
use App\Repositories\PushRepository;
use App\Repositories\IPFS\IPFSThreadRepository;
use App\Repositories\IPFS\IPFSGroupRepository;
use App\Repositories\IPFS\IPFSPostRepository;

use App\Jobs\IPFSJob;
use App\Repositories\AdminRepository;
use App\Repositories\FeaturesRepo;
use App\Repositories\MemberListRepository;
use App\Repositories\SubscribeRepository;
use App\Utils\Constants;
use App\Utils\Curl;

class PostController extends Controller
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





    /**
     * @param NewPostRequest $request
     * @param CommentsRepository $commentRep
     * @param NotificationsRepository $notificationsRep
     * @return array
     */
    public function submitPost(NewPostRequest $request, 
                               CommentsRepository $commentRep,
                               NotificationsRepository $notificationsRep,
                               GroupFollowRepository $groupFollowRep,
                               PostRepository $postRepository,
                               PushRepository $pushRep,
                               IPFSThreadRepository $ipfsThreadRepository,
                               AirdropRepo $airdropRepo,
                               BanUsersRepository $banUsersRep,
                               SubscribeRepository $subscribeRep,
                               FeaturesRepo $featuresRep,
                               MemberListRepository $memberListRep
    )
    {
        $data = $request->all();
        $user = $request->user();

        if(UserRepo::isSilenceRegister($user)){
            return $this->_transformer->fail(400, "please register");
        }

        if ($user->last_post_time) {
            $timestamp = strtotime($user->last_post_time);

            if ((time() - $timestamp) < config('app.flood_interval')) {
                return $this->_transformer->fail('503', 'You can not make another post so soon after your last.');
            }
        }

        //check user follow group
        if (!$groupFollowRep->isCanFollow($user->id, $this->_group->id)) {
            return $this->_transformer->fail(40003);
        }

        $thread = Thread::find($data['thread_id']);

        //if user ban  can not comment
        if ($banUsersRep->checkUserBan($user->id, $thread->group_id)) {
            return $this->_transformer->fail(401, 'You have been banned.');
        }

        $parent_id = $request->input('reply_id', -1); //-1 is the first level under the thread.
        $data['parent_id'] = $parent_id;
  
        $post = $postRepository->createPost($this->_group->id, $thread, $user, $data);
   
        tap($thread)->update(['posts_count' => ($thread->posts_count + 1),'nsfw'=>-1]);
 
        $post->user = $user;
        $post->likes = [];//keep format
        $post->flags = [];
        $post->children = [];


        $ipfsThreadRepository->buildIPFSPayload($thread->id);
        $commentRep->addCommentToCache($this->_group->id, $data['thread_id'], $post);

        // try follow group if user not followed yet
        $groupFollowRep->followGroup($user->id, $this->_group->id);

        $notificationsRep->subscribe($user->id, $this->_group->id, $thread->id);
        
        if ($featuresRep->isFeature($this->_group->id, Constants::SUBSCRIBE_FEATURE)) {
            //check owner subscribe status,send subscribe mail
            $subscribeRep->sendSubscribeMail($this->_group->name, $this->_group->title, $thread,$post->id, $parent_id, $user->id, $groupFollowRep, $this->_group->id, 'replied to');

            // if user unsubscribe thread,auto subscribe thread
            if (!$subscribeRep->isThreadSubscribed($user->id, $data['thread_id'], true)) {
                $post->is_subscribe = 1;
                $subscribeRep->subscribeThread($user->id, $data['thread_id'], $this->_group->id);
            }
        }

        self::sendSubscribeNotification($thread,$post,$user,$this->_group,$notificationsRep,$pushRep);
        $memberListRep->addMemberActiveRecord($this->_group->id, $user->id);

        //airdrop replay
        if( $user->id != $thread->user_id ) {
            $airdropRepo->airdrop_hook('topic_receive_reply', $this->_group->id, $thread->user_id);
        }

        return $this->_transformer->success(['post' => $post]);
    }


    private static function sendSubscribeNotification($thread, $post, $user, $group,
                                                NotificationsRepository $notificationsRep,
                                                PushRepository $pushRep)
    {
        $subscribers = $notificationsRep->getThreadSubscribers(
            $group->id, $thread->id, $user->id);

        if($subscribers) {
            // here we add notifications when there is no unread notification for corresonding thread, 
            // but we send the event to all subscribers, to upadte the thread in real time 
            $notifications = $notificationsRep->addNewReplyNotifications(
                $group->id, $thread->id, $subscribers, $thread, $post, $user->id);

            $payload = [
                'type' => 'reply',
                'group_name' => $group->name,
                'thread_id' => $thread->id,
                'post_id' => $post->id,
                'user_id' => $user->id,
                'user' => [
                    'photo_url' => $user->photo_url,
                    'name' => $user->name,
                ],
                'post_content' => $post->content,
                'post_parent_id' => $post->parent_id,
                'created_at' => date("Y-m-d H:i:s"),
                'ipfs' => $post->ipfs,
                'attached_files' => $post->attached_files,
                'msg' => \App\Utils\StringHelper::getNotificationTitle('post', $user->name, $thread->title),
            ];

            Event::fire(new ThreadActivitiesEvent($subscribers, $payload));
            $pushRep->sendPushJobs($group->id, $subscribers, $thread, $post, $user,'reply');
        }
    }

    public function testThreadIPFS(Request $request,IPFSThreadRepository $rep){
        $rep->buildIPFSPayload($request->id);
    }


        public function testGroupIPFS(Request $request,IPFSGroupRepository $rep){
        $rep->buildIPFSPayload($request->id);
    }


    /**
     * edit a post, with or without thread title and thread category
     * @param EditPostRequest $request
     * @return \App\Utils\json|array
     */
    public function edit(EditPostRequest $request,
                        PostRepository $postRepository, 
                        BanUsersRepository $banUsersRep,
                        AttachedFilesRepository $attachedFilesRepo,
                        GroupAdminRepository $groupAdminRep,
                        AdminRepository $adminRep)
    {
        $user = $request->user();
        $data = $request->all();

        $post = Post::find($data['post_id']);
        $content = $request->input('content');

        if ($post and $post->user_id != $user->id and !$adminRep->isSuperAdmin($user->id) and !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id)) {
            return $this->_transformer->noPermission();
        }

        $response = array();
        $thread = Thread::find($post->thread_id);
        //if user ban  can not comment
        if ($banUsersRep->checkUserBan($user->id, $thread->group_id)) {
            return $this->_transformer->fail(401, 'You have been banned.');
        }
        if (isset($data['thread_id'])) {

            if ($thread->user_id != $user->id and !$adminRep->isSuperAdmin($user->id) and !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id)) {
                return $this->_transformer->noPermission();
            } else {
                $thread_updated = tap($thread)->update([
                    'title' => $request->input('title'),
                    'category_index_id' => $request->input('category_index_id'),
                ]);
            }
            $response['thread'] = $thread_updated;
        }

        if (!empty($data['deleted_attached_files'])) {
            $attachedFilesRepo->deleteAttachedFiles(explode(',', $data['deleted_attached_files']));
        }

       // $post_updated = tap($post)->update(['content' => $request->input('content')]);
        $post_updated = $postRepository->editPost($data['post_id'], $content, $thread, $user);

        $attached_files = isset($data['file_attachments']) ? explode(',', $data['file_attachments']) : [];

        $post_updated->attached_files = $attachedFilesRepo->updateAttachedFiles($attached_files, $post_updated->group_id, $post_updated->thread_id, $post_updated->id);

        $response['post'] = $post_updated;

        return $this->_transformer->success($response);
    }

    /**
     * soft delete a post
     * @param DeletePostRequest $request
     * @return \App\Utils\json|array
     */
    public function delete(DeletePostRequest $request,
                            PostRepository $postRep,
                            CommentsRepository $commentsRep,
                            BanUsersRepository $banUsersRep,
                            GroupAdminRepository $groupAdminRep,
                            FeaturesRepo $featuresRepo,
                            AdminRepository $adminRep
                            
                            )
    {
        $user = $request->user();
        $data = $request->all();
        $response = array();
        if ($banUsersRep->checkUserBan($user->id, $this->_group->id)) {
            return $this->_transformer->fail(401, 'You have been banned.');
        }
        $post = $postRep->hasDeletePermission($user, $data['post_id']);

        if (!$post) {
            return $this->_transformer->noPermission();
        }

        if ($adminRep->isSuperAdmin($user->id)) {
            //deleted 2 is super admin delete,do not show anytime
            $delete_type = 2;
        } else if ($featuresRepo->isFeature($this->_group->id, 4) && $groupAdminRep->isAdmin($user->id, $this->_group->id, 3)) {
            $delete_type = 3;
        } else {
            $delete_type = 1;
        }
        $commentsRep->delPost($data['post_id'], $delete_type);
        $response['post'] = $postRep->deletePost($post, $user, $delete_type);

        return $this->_transformer->success($response);
    }

    public function submitTopic(NewThreadRequest $request,
                                NotificationsRepository $notificationsRep,
                                GroupFollowRepository $groupFollowRep,
                                PostRepository $postRepository,
                                IPFSThreadRepository $ipfsThreadRepository,
                                AirdropRepo $airdropRepo,
                                BanUsersRepository $banUsersRep,
                                SubscribeRepository $subscribeRep,
                                FeaturesRepo $featuresRep,
                                MemberListRepository $memberListRep,
                                CategoryRepository $categoryRep
    )
    {
        $user = $request->user();
        $data = $request->all();

        if(UserRepo::isSilenceRegister($user)){
            return $this->_transformer->fail(400, "please register");
        }

        if ($user->last_post_time) {
            $timestamp = strtotime($user->last_post_time);

            if ((time() - $timestamp) < config('app.flood_interval')) {
                return $this->_transformer->fail('503', 'You can not make another post so soon after your last.');
            }
        }

        //if user ban  can not reply thread
        if ($banUsersRep->checkUserBan($user->id, $this->_group->id)) {
            return $this->_transformer->fail(401, "You have been banned.");
        }

        //check user follow group
        if (!$groupFollowRep->isCanFollow($user->id, $this->_group->id)) {
            return $this->_transformer->fail(40003);
        }

        $data['title'] = mb_substr($data['title'], 0, 190, 'utf-8');

        $group_id = $this->_group->id;

        $thread_data = [
            'title' => $data['title'],
            'user_id' => $user->id,
            'group_id' => $group_id,
        ];

        if (!empty($data['category_index_id'])){
            $thread_data['category_index_id'] = $data['category_index_id'];
            $categoryRep->updateCategoryLastActive($this->_group->id, $data['category_index_id']);
            $categoryRep->recordUserCategoryActive($user->id, $this->_group->id, $data['category_index_id']);
        }

        $thread = Thread::create($thread_data);
        $data['parent_id'] = -1;
        $post =  $postRepository->createPost($this->_group->id,$thread,$user,$data);

        $thread = tap($thread)->update(['first_post_id' => $post->id]);
        $ipfsThreadRepository->buildIPFSPayload($thread->id);
        $notificationsRep->subscribe($user->id, $group_id, $thread->id);

        $groupFollowRep->followGroup($user->id, $this->_group->id);

        // if user unsubscribe thread,auto subscribe thread
        if ($featuresRep->isFeature($this->_group->id, Constants::SUBSCRIBE_FEATURE) && !$subscribeRep->isThreadSubscribed($user->id, $thread->id, true)) {
            $subscribeRep->subscribeThread($user->id, $thread->id, $group_id);
        }

        $memberListRep->addMemberActiveRecord($this->_group->id, $user->id);
        $thread->slug = StringHelper::slugify($thread->title,$thread->id);

        $airdropRepo->airdrop_hook('topics', $group_id, $user->id);

        return $this->_transformer->success(['thread' => $thread, 'post' => $post, 'attachments' => $post->attachments]);
    }



    private function url_slug($str){

      $search = array('Ș', 'Ț', 'ş', 'ţ', 'Ş', 'Ţ', 'ș', 'ț', 'î', 'â', 'ă', 'Î', 'Â', 'Ă', 'ë', 'Ë');
      $replace = array('s', 't', 's', 't', 's', 't', 's', 't', 'i', 'a', 'a', 'i', 'a', 'a', 'e', 'E');
      $str = str_ireplace($search, $replace, strtolower(trim($str)));
      $str = preg_replace('/[^\w\d\-\ ]/', '', $str);
      $str = str_replace(' ', '-', $str);
      return preg_replace('/\-{2,}/', '-', $str);

    }

    public function userPosts(Request $request, 
                            PostRepository $postRepository, 
                            $profile_id, $filter, $page) {

        $per_page = 10;

        switch (strtolower($filter)) {
            case 'posts':
                $user_posts = $postRepository->getPostsByUserId($profile_id, $page, $per_page);
                break;
            case 'likes':
                $user_posts = $postRepository->getPostsByUserLike($profile_id, $page, $per_page);
                break;
            default:
                $user_posts = $postRepository->getPostsByUserActivity($profile_id, $page, $per_page);       
        }

        return $this->_transformer->success(['user_posts' => $user_posts]);
    }

    // public function linkPreview(Request $request, Curl $curl) {
    //     $url = $request->input('url', '');
    //     $data = [];
    //     if ($url) {
    //         $result = $curl->post('http://apiflask/apiflask/link_preview', ['url' => $url]);
    //         $data = json_decode($result);
    //     }

    //     return $this->_transformer->success(['preview' => $data]);
    // }

    /**
     * soft undelete a post
     * @param DeletePostRequest $request
     * @return \App\Utils\json|array
     */
    public function undelete(DeletePostRequest $request,
                           PostRepository $postRep,
                           CommentsRepository $commentsRep,
                           GroupAdminRepository $groupAdminRep,
                           FeaturesRepo $featuresRepo,
                           AdminRepository $adminRep

    )
    {
        $user = $request->user();
        $data = $request->all();
        $response = array();

        if (!$adminRep->isSuperAdmin($user->id) && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id, 2)
            ) {
            return $this->_transformer->noPermission();
        }

        $response['post'] = $postRep->undeletePost($data['post_id']);
        $commentsRep->delPost($data['post_id'], 0);

        return $this->_transformer->success($response);
    }

}