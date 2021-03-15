<?php

namespace App\Repositories;

use App\Models\CommentTree;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Support\Facades\Redis;
use App\Utils\Constants;
use App\WriteLog;
/* 
 */
class CommentsRepository
{
    public static $comments = 0;
    public static $tree = array();

    /**
     * @param $group_id
     * @param $thread_id
     * @return string
     */
    private static function getTreeCacheKey($group_id, $thread_id)
    {
        return "group-tree:" . $group_id . "-" . $thread_id;
    }

    /**
     * select all posts by thread id
     * @param $group_id
     * @param $thread_id
     * @return array
     */
    private static function buildTreeCache($group_id, $thread_id)
    {
        $thread = Thread::where('id', $thread_id)->first();
        //Detected deleted are used to determine whether to hide
        $posts = Post::select('id', 'thread_id', 'parent_id', 'created_at', 'deleted', 'user_id')
                    ->where('thread_id', $thread_id)
                    ->where('id', "!=", $thread->first_post_id)
                    ->orderBy('id', 'asc')
                    ->get();

        $tree = array();

        foreach($posts as $post){
            if(!isset($tree[$post->parent_id])){
                $tree[$post->parent_id] = array();
            }
            //Record user id to filter relevant
            $tree[$post->parent_id][$post->id] = array(
                                                'id' => $post->id,
                                                'del' => $post->deleted,
                                                'user' => $post->user_id,
                                            );
        }
        $redis = Redis::connection();
        $key = self::getTreeCacheKey($group_id, $thread_id);
        $redis->set($key, json_encode($tree));
        
        return $tree;
    }

    /**
     * get tree cache
     */
    private static function getTreeCache($group_id, $thread_id)
    {
        $redis = Redis::connection();
        $key = self::getTreeCacheKey($group_id, $thread_id);
        $data = $redis->get($key);
        return json_decode($data, true);
    }

    /**
     * get first fetch top level posts (parent_id=-1), do pagination
     * then fetch all of the child posts
     */
    private static function getTreeCids($cid, $tree, $page = 1, $per_page = 10, $is_loadmore = true)
    {
        $result = array();
        $cid_tree = array();
     
        if(isset($tree[$cid])){
            // pagination only apply on the top level posts
            if($cid == -1){
                $child_cids = array_slice($tree[-1], (($page-1) * $per_page), $per_page);
            } else {
                sort($tree[$cid]);
                $child_cids = $tree[$cid];
            }

            $result = array_merge($result, $child_cids);

            foreach($child_cids as $key => $child_cid){

                if ($is_loadmore && $cid != -1 && $key >= Constants::LOADMORE_THRESHOLD) {
                    // for non-top level posts, we loda maximum 5 of them and add a load more
                    // todo, maybe recursivly count all child posts
                    $cid_tree += ['load_more' => count($child_cids) - Constants::LOADMORE_THRESHOLD];
                    break;

                } else {

                    list($child_cid_tree,$cids) = self::getTreeCids($child_cid, $tree, $page, $per_page, $is_loadmore);
                    // ksort($child_cid_tree, SORT_NATURAL);
                    $cid_tree[$child_cid] = $child_cid_tree;
                    $result = array_merge($result, $cids);
                }
            }
        }
        return array($cid_tree,$result);  //return tree structure & $cids when recursive function end
    }

    /**
     * @param $group_id
     * @param $tree_piece
     * @param $cids
     * @param $sort
     * @return array
     */
    private static function getCommentTreeData($group_id, $tree_piece, $cids, $sort = '')
    {
        $comment_fields = ['posts.id', 'posts.thread_id', 'posts.created_at', 'posts.content', 'posts.ipfs', 'posts.parent_id',
                            'posts.user_id', 'posts.nsfw', 'posts.deleted', 'posts.deleted_by', 'posts.group_id','group_ban_users.id as is_ban'];
        
        $posts = Post::with('user', 'likes', 'deletedBy', 'flags', 'attachedFiles')
            ->select($comment_fields)
            ->where('posts.group_id', $group_id)
            ->wherein('posts.id', $cids)
            ->join('group_ban_users', function($join) use ($group_id){
                $join->on('group_ban_users.user_id','=','posts.user_id')
                    ->where('group_ban_users.group_id','=',$group_id)
                    ->whereNull('group_ban_users.deleted_at');
            }, null,null,'left')
            ->orderBy('posts.id', 'asc')->get();
        $posts_index = array();
        foreach ($posts as $post) {
            if (UserBehaviorRepository::checkUserOnline($post->user_id)) {
                $post->online = true;
            } else {
                $post->online = false;
            }
            $posts_index[$post->id] = $post;
        }
        // print_r($posts_index);
        //add sort comment_total is to block hidden
        return self::getCommentsByTree($tree_piece, $posts_index);
    }

    /**
     * get child posts
     */
    private static function getCommentsByTree($tree_piece, $posts_index)
    {
        $comments_tree = array();
        if(sizeof($tree_piece) > 0){
            foreach($tree_piece as $parent_id => $children) {
                if (is_array($children)) {
                    $child_comments = self::getCommentsByTree($children, $posts_index);
                    if (!empty($posts_index[$parent_id])) {
                        $posts_index[$parent_id]->children = $child_comments;
                        $comments_tree[] = $posts_index[$parent_id];
                    }
                } else {
                        // when there is a load more, children is not an array
                        $comments_tree[] = $children;
                }
            }
        }
        return  $comments_tree;
    }

    /**
     * @param $group_id
     * @param $thread_id
     * @param $sort
     * @param $page
     * @param int $per_page
     * @param bool $is_loadmore
     * @return array
     */
    public static function getComments($group_id, $thread_id, $sort, $page, $per_page = 10, $is_loadmore = true, $user)
    {
        $sort = strtolower($sort);
        $tree = self::getTreeCache($group_id, $thread_id);
        if (!$tree or sizeof($tree) == 0) {
            $tree = self::buildTreeCache($group_id, $thread_id);
        }

        if ($tree) {
            //handle the cache
            $tree = self::handleCache($group_id, $tree, $sort, $user);
            self::$tree = $tree;
            if ($sort === 'relevant') {
                self::getCommentCount(-1);
            }
        }

        // override top leverl post by the sorted list
        list($tree_piece, $page_cids) = self::getTreeCids(-1, $tree, $page, $per_page, $is_loadmore);
        $comments_data = self::getCommentTreeData($group_id, $tree_piece, $page_cids, $sort);

        if (sizeof($comments_data) == 0) {
            self::cleanTreeCache($group_id, $thread_id);
        }

        return array('tree' => $comments_data,'count' => self::$comments);
    }

    public function getThreadCount($group_id, $thread_id, $user)
    {
        $sort = 'relevant';
        self::$comments = 0;
        $tree = self::getTreeCache($group_id, $thread_id);
        if (!$tree or sizeof($tree) == 0) {
            $tree = self::buildTreeCache($group_id, $thread_id);
        }

        if ($tree) {
            //handle the cache
            $tree = self::handleCache($group_id, $tree, $sort, $user);
            self::$tree = $tree;
            self::getCommentCount(-1);
        }
        return self::$comments;
    }

    /**
     * Handle the cache
     * @param int $group_id
     * @param array $tree
     * @param string $sort
     * @param object $user
     * 
     * @return array
     */
    private static function handleCache($group_id, $tree, $sort, $user) {
        $newTree = array();
        if ($sort === 'relevant') {
            $user_list = BanUsersRepository::getGroupBanUser($group_id);
            if ($user_list && !$user_list[0]) {
                unset($user_list[0]);
            }
            if ($user && $user->id) {
                $BlockUsersRep = new BlockUsersRepository();
                $block_user = $BlockUsersRep->getUserBlockedUsers($user->id);
                if ($block_user) {
                    $user_list = array_merge($user_list, $block_user);
                }
            }
            $user_list = array_flip($user_list);
            foreach($tree as $key => $value) {
                foreach($value as $v) {
                    if (!$v['del'] && (!$user_list || !array_key_exists($v['user'], $user_list))) {
                        $newTree[$key][] = $v['id'];
                    }
                }
            }
        } else {
            foreach($tree as $key => $value) {
                foreach($value as $v) {
                    if ($v['del'] != 2) {
                        $newTree[$key][] = $v['id'];
                    }
                }
            }
        }

        return $newTree;
    }

    /**
     * get relevant comment count
     *
     * @param int $parent_id
     * @return void
     */
    public static function getCommentCount($parent_id)
    {
        if (isset(self::$tree[$parent_id]) && self::$tree[$parent_id]) {
            self::$comments += count(self::$tree[$parent_id]);
            foreach(self::$tree[$parent_id] as $value) {
                self::getCommentCount($value);
            }
        }
    }

    /**
     * clear post tree cache by thread_id
     */
    public static function cleanTreeCache($group_id, $thread_id)
    {
        $redis = Redis::connection();
        $key = self::getTreeCacheKey($group_id, $thread_id);
        $redis->del($key);
    }

    /**
     * @param $group_id
     * @param $thread_id
     * @param $comment
     */
    public static function addCommentToCache($group_id, $thread_id, $comment)
    {
        $tree = self::getTreeCache($group_id, $thread_id);
        // array_unshift correspond to ->orderBy('id', 'desc'), 
        // if the order by is asc, we can juse append to the end of the array
        if (isset($tree[$comment->parent_id]) && is_array($tree[$comment->parent_id])){
            $tree[$comment->parent_id][$comment->id] = array(
                'id' => $comment->id,
                'del' => null,
                'user' => $comment->user_id,
                );
        } else {
            $tree[$comment->parent_id][$comment->id] = array(
                'id' => $comment->id,
                'del' => null,
                'user' => $comment->user_id,
                );
        }
        
        $key = self::getTreeCacheKey($group_id, $thread_id);
        $redis = Redis::connection();
        $redis->set($key, json_encode($tree));
    }

    /**
     * get part of the comments tree, load more API
     * @param $group_id
     * @param $thread_id
     * @param $parent_post_id
     * @param $sort
     */
    public static function getSubComments($group_id, $thread_id, $parent_post_id, $sort, $user)
    {
        $tree = self::getTreeCache($group_id, $thread_id);

        if (!$tree or sizeof($tree) == 0) {
            $tree = self::buildTreeCache($group_id, $thread_id);
        }
        if ($tree) {
            $tree = self::handleCache($group_id, $tree, $sort, $user);
        }
        // load more pass parent post_id in parameters, the first LOADMORE_THRESHOLD child posts already loaded, 
        // thus, it must have more than LOADMORE_THRESHOLD posts  
        if (empty($tree[$parent_post_id]) || count($tree[$parent_post_id]) <= Constants::LOADMORE_THRESHOLD) {
            return [];
        }
        sort($tree[$parent_post_id]);
        // the first LOADMORE_THRESHOLD post are already loaded
        $tree[$parent_post_id] = array_slice($tree[$parent_post_id], Constants::LOADMORE_THRESHOLD);

        // override top leverl post by the sorted list
        list($tree_piece, $page_cids) = self::getSubTreeCids($parent_post_id, $tree, $parent_post_id);

        $comments_data = self::getCommentTreeData($group_id, $tree_piece, $page_cids, $sort);

        return $comments_data;
    }

    /**
     * get the cid tree of a part of a whole thread tree
     * compare to the original version `getTreeCids`
     * `getSubTreeCids` doesn't invole in pagination and load more, 
     * just find all its child post recursively
     * @param $cid
     * @param $tree
     */
    private static function getSubTreeCids($cid, $tree, $root_post_id)
    {
        $result = array();
        $cid_tree = array();
     
        if(isset($tree[$cid])){
            // pagination only apply on the top level posts
            $child_cids = $tree[$cid];

            $result = array_merge($result, $child_cids);

            foreach($child_cids as $key => $child_cid){

                if ($cid != $root_post_id && $key >= Constants::LOADMORE_THRESHOLD) {
                    // for non-top level posts, we loda maximum 5 of them and add a load more
                    // todo, maybe recursivly count all child posts
                    $cid_tree += ['load_more' => count($child_cids) - Constants::LOADMORE_THRESHOLD];
                    break;

                } else {

                    list($child_cid_tree,$cids) = self::getSubTreeCids($child_cid, $tree, $root_post_id);

                    $cid_tree[$child_cid] = $child_cid_tree;
                    $result = array_merge($result, $cids);
                }
            }
        }
      
        return array($cid_tree, $result);  //return tree structure & $cids when recursive function end
    }

    /**
     * get the page number of a post, in a post tree
     */
    public static function getPageByPostId($group_id, $thread_id, $post_id, $sort, $page_number, $user)
    {
        $tree = self::getTreeCache($group_id, $thread_id);

        if (!$tree || sizeof($tree) == 0) {
            $tree = self::buildTreeCache($group_id, $thread_id);
        }

        if ($tree) {
            $tree = self::handleCache($group_id, $tree, $sort, $user);
        }

        if (empty($tree[-1])) {
            return 0;
        }

        // find top level post id
        $post_index = array_search(self::findTopLevelPostId($tree, $post_id), $tree[-1]);
        // +1 becasue index start from 0
        return $post_index === FALSE ? 0 : ceil(($post_index+1)/ $page_number);
    }

    /**
     * delete post update cache
     */
    public function  delPost($post_id, $delete_type) {
        $post = Post::find($post_id);
        $thread = Thread::where('id', $post->thread_id)->first();
        if ($thread && $thread->first_post_id != $post_id) {
            $tree = self::getTreeCache($post->group_id, $post->thread_id);
            if (!$tree || sizeof($tree) == 0) {
                return true;
            }
            $tree[$post->parent_id][$post_id]['del'] = $delete_type;
            $key = self::getTreeCacheKey($post->group_id, $post->thread_id);
            $redis = Redis::connection();
            $redis->set($key, json_encode($tree));
        }
       
    }

    /**
     * find the top level post id recursively
     */
    private static function findTopLevelPostId($tree, $post_id) 
    {
        // this is termination, when the post id is a top level one
        if (in_array($post_id, $tree[-1])) {
            return $post_id;
        }

        // if post id is not at top level, pass it's parent to next recursion
        foreach ($tree as $key => $value) {
            if ($key > 0 && is_array($value) && in_array($post_id, $value)) {
                return self::findTopLevelPostId($tree, $key);
            }
        }

        return 0;
    }

}