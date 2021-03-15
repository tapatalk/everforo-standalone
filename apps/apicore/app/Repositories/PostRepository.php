<?php

namespace App\Repositories;
use App\Models\GroupFollow;
use App\Models\Post;
use App\Models\Thread;
use App\Models\Group;
use App\Models\Like;
use App\Models\Admin;
use App\User;

use App\Models\Attachment;
use App\Models\AttachedFiles;

use App\Jobs\IPFSJob;
use Log, Queue;
use App\Jobs\PostNsfwJob;

use App\Repositories\AttachedFilesRepository;
use App\Repositories\IPFS\IPFSPostRepository;
use App\Repositories\IPFS\IPFSRepository;

class PostRepository {

    private $_ipfsRepository;
    private $_ipfsPostRepository;
    public function __construct(IPFSRepository $ipfsRepository,
        IPFSPostRepository $ipfsPostRepository)
    {
   
        $this->_ipfsRepository = $ipfsRepository;
        $this->_ipfsPostRepository = $ipfsPostRepository;
    }

    public function createPost($group_id,$thread,$user,$post_data)
    {
        $post = Post::create([
            'group_id' => $group_id,
            'user_id' => $user->id,
            'parent_id' => $post_data['parent_id'],
            'content' => $post_data['content'],
            'thread_id' => $thread->id,
        ]);

        tap($user)->update(['last_post_time' => $post->created_at]);

        $attachments = array();
        $ipfs_attachments = array();

        if (isset($post_data['image_attachments'])) {
            $ids = explode(',', $post_data['image_attachments']);
            foreach ($ids as $id) {
                $attachment = Attachment::find($id);
                if ($attachment) {
                    $attachment->update([
                        'group_id' => $group_id,
                        'thread_id' => $thread->id,
                        'post_id' => $post->id
                    ]);


                    // $attachments[] = $attachment;
                    // $attachment_ipfs = array();
                    // $attachment_ipfs['url'] = $attachment->url;
                    // $attachment_ipfs['ipfs'] = $attachment->ipfs;
                    // $ipfs_attachments[] = $attachment_ipfs;
                }
            }
            // $response['attachments'] = $attachments;
        }

        if (isset($post_data['file_attachments'])) {

            $attachedFilesRepo = new AttachedFilesRepository();

            $post->attached_files = $attachedFilesRepo->updateAttachedFiles(explode(',', $post_data['file_attachments']), $post->group_id, $post->thread_id, $post->id);
        }

        self::generatePostIPFS($post->id);
    
        if (env('APP_ENV') !== 'local'){
            Queue::push(new PostNsfwJob($post->id));
        }

        return $post;
    }


    public function editPost($post_id,$content,$user,$thread){

        $post = $this->generatePostIPFS($post_id, $content);
        $update_data = ['nsfw'=>-1];
        tap($post)->update($update_data);
        if (env('APP_ENV') !== 'local'){
            Queue::push(new PostNsfwJob($post_id));
        }

        return $post;
    }

    public function generatePostIPFS($post_id,$edited_content = ""){
        $post =  Post::with('user')->find($post_id);

        $thread = Thread::find($post->thread_id);
        if($edited_content){
            $post = tap($post)->update(['content' => $edited_content]);   
        }
   
     
        $ipfs_post_data =   $this->_ipfsPostRepository->buildPostIPFSBody($post);
        $ipfs_post_data['timestamp'] = time();
        $ipfs_post_data['thread_title'] =  $thread->title;

        if (env('APP_ENV') !== 'local'){
            $ipfs = $this->_ipfsRepository->submitToIPFS($ipfs_post_data);
        
            Queue::push(new IPFSJob($ipfs));

            $update_data = ['ipfs' => $ipfs];   

            $post_updated = tap($post)->update($update_data);
        } else {
            $post_updated = tap($post)->update(['ipfs' => 'pseudo-ipfs-hash']);
        }
    
        
        return $post;
    }



    public function getOne($post_id) 
    {
        return Post::where('id', $post_id)->first();
    }

    public function hasDeletePermission(\App\User $user, $post_id)
    {
        try {
            $post = Post::with('thread','group')->where('id',$post_id)->first();
            $groupAdminRep = new GroupAdminRepository();
            if($post->deleted == 0 && ($post->user_id == $user->id
            || $groupAdminRep->checkGroupAdmin($post->group_id, $user->id))){
                return $post;
            } else {
//                $super_admin = Admin::where('user_id',  $user->id)->first();
//                if ($super_admin) {
//                    return $post;
//                }
                return false;
            }

        } catch(\Exception $e) {
            return false;
        }
    }

    public function deletePost(Post $post, User $user, $delete_type) {

        $post->timestamps = false;

        tap($post)->update(['deleted' => $delete_type, 'deleted_by' => $user->id]);

        $likes_count = Like::where('post_id', $post->id)->count();

        $thread = Thread::where('id', $post->thread_id)->first();

        $thread->timestamps = false;

        $update_data = ['likes_count', (int)$thread->likes_count - 1 * $likes_count];

        if($post->thread->first_post_id != $post->id){
            $update_data['posts_count'] = (int)$thread->posts_count-1;
        }

        tap($thread)->update($update_data);
        //update group user likes count
        GroupFollow::where('group_id', $post->group_id)
            ->where('user_id', $post->user_id)->increment('likes_count', -1 * $likes_count);
        return $post;
    }

    private function userPosts($user_id) {
        return \DB::table('posts')
                ->leftJoin('users', 'posts.user_id', '=', 'users.id')
                ->leftJoin('posts as parent_post', 'posts.parent_id', '=', 'parent_post.id')
                ->leftJoin('groups', 'posts.group_id', '=', 'groups.id')
                ->leftJoin('threads', 'posts.thread_id', '=', 'threads.id')
                ->leftJoin('posts as first_post', 'threads.first_post_id', '=', 'first_post.id')
                ->leftJoin('users as first_poster', 'first_post.user_id', '=', 'first_poster.id')
                ->leftJoin('users as parent_poster', 'parent_post.user_id', '=', 'parent_poster.id')
                ->where('posts.deleted', 0)
                ->where('posts.user_id', $user_id)
                ->selectRaw('posts.*, users.name as username, users.photo_url as user_avatar, groups.name as group_name, \'\' as like_username,
                threads.title as thread_title, first_post.user_id as thread_poster_id, parent_poster.name as parent_poster_name, first_poster.name as thread_poster_name')
                ->orderBy('posts.id', 'desc');
    }

    private function userLikedPosts($user_id) {
        return \DB::table('posts')->join('likes', 'posts.id', '=', 'likes.post_id')
                ->leftJoin('users', 'posts.user_id', '=', 'users.id')
                ->leftJoin('posts as parent_post', 'posts.parent_id', '=', 'parent_post.id')
                ->leftJoin('groups', 'posts.group_id', '=', 'groups.id')
                ->leftJoin('threads', 'posts.thread_id', '=', 'threads.id')
                ->leftJoin('posts as first_post', 'threads.first_post_id', '=', 'first_post.id')
                ->leftJoin('users as first_poster', 'first_post.user_id', '=', 'first_poster.id')
                ->leftJoin('users as parent_poster', 'parent_post.user_id', '=', 'parent_poster.id')
                ->leftJoin('users as like_user', 'likes.user_id', '=', 'like_user.id')
                ->where('posts.deleted', 0)
                ->where(function ($query) use ($user_id) {
                    $query->where('likes.user_id', $user_id)
                            ->whereNull('likes.deleted_at');
                })
                ->selectRaw('posts.*, users.name as username, like_user.photo_url as user_avatar, groups.name as group_name, like_user.name as like_username,
                threads.title as thread_title, first_post.user_id as thread_poster_id, parent_poster.name as parent_poster_name, first_poster.name as thread_poster_name')
                ->orderBy('posts.id', 'desc');
    }

    public function getPostsByUserActivity($user_id, $page, $per_page) {

        $offset = ($page - 1) * $per_page;

        $first = $this->userPosts($user_id);

        $posts = $this->userLikedPosts($user_id)
                    ->union($first)
                    ->offset($offset)->limit($per_page)->get();

        return $posts;
    }

    public function getPostsByUserId($user_id, $page, $per_page) {

        $offset = ($page - 1) * $per_page;

        $posts = $this->userPosts($user_id)
                    ->offset($offset)->limit($per_page)->get();

        return $posts;
    }

    public function getPostsByUserLike($user_id, $page, $per_page) {

        $offset = ($page - 1) * $per_page;

        $posts = $this->userLikedPosts($user_id)
                ->offset($offset)->limit($per_page)->get();

        return $posts;
    }

    /**
     * undelete post
     * @param $post_id
     * @return Post
     */
    public function undeletePost($post_id) {
        $post = Post::find($post_id);
        tap($post)->update(['deleted' => 0, 'deleted_by' => 0]);

        $likes_count = Like::where('post_id', $post->id)->count();

        $thread = Thread::where('id', $post->thread_id)->first();
        $update_data = ['likes_count', (int)$thread->likes_count + 1 * $likes_count];

        if($post->thread->first_post_id != $post->id){
            $update_data['posts_count'] = (int)$thread->posts_count + 1;
        }

        tap($thread)->update($update_data);
        //update group user likes count
        GroupFollow::where('group_id', $post->group_id)
            ->where('user_id', $post->user_id)->increment('likes_count', 1 * $likes_count);
        return $post;
    }


}