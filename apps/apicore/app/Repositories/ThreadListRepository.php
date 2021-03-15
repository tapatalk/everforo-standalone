<?php

namespace App\Repositories;

use DB;

use App\Models\GroupBanUsers;
use App\Models\Thread;
use App\Models\ThreadTrack;
use App\Models\Post;

use App\Utils\Constants;
use App\Utils\StringHelper;

use App\Repositories\CommentsRepository;
use App\Repositories\GroupRepository;

class ThreadListRepository
{


    public static function getThreadList($group_id, $sort, $page, $category_id, $user, $commentsRep)
    {
        $page_length = 10;
        $user_id = $user ? $user->id : 0;
        $offset = ($page - 1) * $page_length;
        $sort = strtolower($sort);

        $thread_fields = ['threads.id', 'threads.title', 'threads.created_at', 'threads.posts_count', 'threads.likes_count', 'threads.category_index_id',
                            'threads.user_id', 'threads.first_post_id', 'threads.updated_at','group_ban_users.id as is_ban', 'group_pin.id as is_pin'];
        
        $query = Thread::with(['user', 'first_post', 'first_post.deletedBy',
                        'category' => function ($query) use ($group_id) {
                            $query->where('group_id', $group_id);
                        }])
                         ->select($thread_fields)
                        ->where('threads.group_id', $group_id)
                        ->join('group_ban_users', function($join) use ($group_id){
                            $join->on('group_ban_users.user_id','=','threads.user_id')
                                ->where('group_ban_users.group_id','=',$group_id)
                                ->where('group_ban_users.deleted_at',null)
                            ;
                        }, null,null,'left')
                        ->join('group_pin', function($join) {
                            $join->on('group_pin.thread_id','=','threads.id')
                                ->where('group_pin.deleted_at',null)
                            ;
                        }, null,null,'left')
                        ->orderBy('group_pin.updated_at','desc')
        ;

        if ($category_id > -2) {
            if ($category_id == -1) {
                $query = $query->where(function ($query) {
                    return $query->where('threads.category_index_id', '-1')->orWhereNull('threads.category_index_id');
                });
            } else {
                $query = $query->where('threads.category_index_id', $category_id);
            }
        }


        //First determine whether you need to filter
        switch ($sort) {
            case 'all':
                //deleted 2 is super admin delete,do not show anytime
                $query->whereHas('first_post', function ($query) use ($user_id)  {
                    $query->where('deleted', '<>', 2);
                });
                break;
            case 'participated':
                $query->whereHas('posts', function ($query) use ($user_id)  {
                    if ($user_id) {
                        $query->leftJoin('likes','likes.post_id','=','posts.id')->where(function ($query) use ($user_id) {
                            $query->where('posts.user_id', $user_id)->orWhere(function ($query) use ($user_id) {
                                $query->where('likes.user_id', $user_id)->where('likes.deleted_at', null);
                            });
                        });
                    }
                });
                //deleted 2 is super admin delete,do not show anytime
                $query->whereHas('first_post', function ($query){
                    $query->where('deleted', '<>', 2);
                });
                break;
            case 'subscribed':
                if ($user_id) {
                    $query->whereHas('subscribe', function ($query) use ($user_id)  {
                        $query->where('user_id', $user_id)->whereNull('deleted_at');
                    });
                }
                //deleted 2 is super admin delete,do not show anytime
                $query->whereHas('first_post', function ($query){
                    $query->where('deleted', '<>', 2);
                });
                break;
            default:
                $query->whereHas('first_post', function ($query) {
                    $query->where('deleted', 0);
                });
                if ($user_id) {
                    $query->whereNotIn('threads.user_id', function ($query) use ($user_id) {
                        $query->select("block_users.block_user_id")->from("block_users")->where("block_users.user_id","$user_id");
                    });
                }
                $query->whereNotIn('threads.user_id', function ($query) use ($group_id) {
                    $query->select("group_ban_users.user_id")->from("group_ban_users")
                        ->where("group_ban_users.group_id","$group_id")->whereNull("group_ban_users.deleted_at");
                });
        }

        $query = $query->orderBy('threads.updated_at', 'desc');
        $threads = $query->offset($offset)->limit($page_length)->get();
        $thread_ids = array();
        $thread_updateds = array();

        foreach ($threads as $key => $thread) {

            // try{

            //     $first_post = Post::where('id', $thread->first_post_id)->firstOrFail();

            // }catch(\Exception $e) {
            //     unset($threads[$key]);
            //     \Log::error('missing first post', [$thread]);
            //     continue;
            // }

            // $thread->first_post = $first_post;
            // if (!$thread->latest_reply) {
            //     unset($thread->latest_reply);
            // }
            $thread_ids[] = $thread->id;
            $thread->slug = StringHelper::slugify($thread->title,$thread->id);
            $thread_updateds[$thread->id] = $thread->updated_at;
            if (UserBehaviorRepository::checkUserOnline($thread->user_id)) {
                $thread->online = true;
            } else {
                $thread->online = false;
            }
            $thread->posts_count = $commentsRep->getThreadCount($group_id, $thread->id, $user);
            if ($thread->first_post) {
                // not using $thread->first_post->is_ban = $thread->is_ban;
                // to avoid "Indirect modification of overloaded property App\Models\Thread::$first_post has no effect"
                $first_post = $thread->first_post;
                $first_post->is_ban = $thread->is_ban;
                $thread->first_post = $first_post;
            }
        }
        if ($user_id) {

            $unread_tracks = self::getThreadTrack($group_id, $thread_ids, $thread_updateds, $user_id);

            foreach ($threads as $thread) {
                if (in_array($thread->id, $unread_tracks)) {
                    $thread->unread = 1;
                }
            }
        }

        // front end would expect the thread list to be a continuous array, so we remove the keys, cause there could be deleted keys
        return array_values($threads->toArray());
    }


    public static function getThreadTrack($group_id,$thread_ids,$thread_updateds,$user_id){

 
        $tracks = ThreadTrack::where('group_id',$group_id)->where('user_id',$user_id)->whereIn('thread_id', $thread_ids)
                    ->get();
                   
              
        $result = array();
        foreach($tracks as $track){
         
            if($track->updated_at < $thread_updateds[$track->thread_id]){
                 $result[] = $track->thread_id;
            }
        }
        return $result;
    }

    /**
     * feed the latest thread
     */
    public function threadFeed($page, $blocked_users = [], $in_groups = [], $user_id = '') 
    {
        $page_length = 20;
        $offset = ($page - 1) * $page_length;

        $query = Thread::with(['user', 'group', 'first_post'])
                        ->select(['threads.*','group_pin.id as is_pin'])
                        ->whereHas('group',function ($query) {
                                return $query->where('no_recommend', '!=', 1)->where('super_no_recommend','!=',1);
                            })
                        ->whereHas('first_post', function ($query) use ($blocked_users)  {

                            if ($blocked_users) {
                                $query->where('nsfw', 0)->where('no_recommend',0)->where('deleted', 0)->whereNotIn('user_id', $blocked_users);
                            } else {
                                $query->where('nsfw', 0)->where('no_recommend',0)->where('deleted', 0);
                            }
                        })
                        ->join('group_pin', function($join){
                            $join->on('group_pin.thread_id','=','threads.id')
                                ->where('group_pin.pin_type','=',Constants::SUPER_ADMIN_PIN)
                                ->whereNull('group_pin.deleted_at');
                        }, null,null,'left')
                        ->orderBy('group_pin.updated_at', 'DESC');

        if ($in_groups) {
            $query = $query->whereIn('threads.group_id', $in_groups);
        }
        $url = 'http://apiprivacy/apiprivacy/group_list';
        if ($user_id) {
            $url = $url . '?user_id=' . $user_id;
        }
        $no_group_list = geturl($url);

        if ($no_group_list && is_array($no_group_list)) {
            $query = $query->whereNotIn('threads.group_id', $no_group_list);
        }
        $groupRepo = new GroupRepository();
        // exlude groups with less or equal 3 posts
        $group_posts_count = array_filter($groupRepo->getGroupPostCount(), function($v) {
            return $v <= 3;
        });

        $exclude_group_id = array_keys($group_posts_count);

        if ($exclude_group_id) {
            $query = $query->whereNotIn('threads.group_id', $exclude_group_id);
        }

        //user can not see if user be baned
        if ($user_id) {
            $query->whereNotIn('threads.group_id', function ($query) use ($user_id) {
                $query->select("group_ban_users.group_id")->from("group_ban_users")->where('user_id', $user_id)->where('deleted_at', null);
            });
        }

        $query->whereNotIn('threads.user_id', function ($query) use ($user_id) {
            $query->select("group_ban_users.user_id")->from("group_ban_users")->where('deleted_at', null);
        });

        $thread_list = $query->orderBy('threads.updated_at', 'DESC')
                            ->offset($offset)->limit($page_length)->get();

        foreach ($thread_list as $key => $thread) {

            $thread->slug = StringHelper::slugify($thread->title,$thread->id);
            if (UserBehaviorRepository::checkUserOnline($thread->user_id)) {
                $thread->online = true;
            } else {
                $thread->online = false;
            }
        }

        return $thread_list;
    }

}
