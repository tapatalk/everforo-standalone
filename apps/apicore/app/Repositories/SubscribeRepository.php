<?php

namespace App\Repositories;

use App\Mail\SubscribeMail;
use App\Models\Post;
use App\Models\ThreadSubscribe;
use Illuminate\Support\Facades\Mail;
use App\Utils\StringHelper;
use App\User;

class SubscribeRepository {


    /**
     * subscribe a thread
     * 
     * @param int $user_id
     * @param int $thread_id
     * 
     * @return object|int
     */
    public function subscribeThread($user_id, $thread_id, $group_id)
    {
        $subscribe = ThreadSubscribe::withTrashed()
                    ->where( 'thread_id', $thread_id)
                    ->where('user_id', $user_id)->first();

        if ($subscribe) {
            // if not deleted, do nothing
            if ($subscribe->trashed()) {
                $subscribe->restore();
            }
        } else {
            $subscribe = ThreadSubscribe::create([
                'user_id' => $user_id,
                'thread_id' => $thread_id,
                'group_id' => $group_id,
            ]);
        }
        return $subscribe;
    }

    /**
     * check if thread subscribed
     * 
     * @param int $user_id
     * @param int $thread_id
     * @param bool $type
     * 
     * @return bool|object
     */
    public function isThreadSubscribed($user_id, $thread_id, $type = false)
    {
        if ($type) {
           return ThreadSubscribe::withTrashed()
                ->where( 'thread_id', $thread_id)
                ->where('user_id', $user_id)->first(); 
        } else {
            return ThreadSubscribe::where( 'thread_id', $thread_id)
                ->where('user_id', $user_id)->first();
        }
        
    }

    /**
     * unsubscribe a thread
     * 
     * @param int $user_id
     * @param int $thread_id
     * 
     * @return int|bool
     */
    public function unsubscribeThread($user_id, $thread_id)
    {
        return ThreadSubscribe::where('user_id', $user_id)
                ->where('thread_id', $thread_id)->delete();
    }

    /**
     * send subscribe email
     * 
     * @param array $data
     * 
     * @return null
     */
    public function sendMail($data)
    {
        $page = 1;//default page is 1
        $send_data['post_url'] = env('EVERFORO_DOMAIN', 'https://sa.everforo.com') . '/g/' . $data['group_name']
        . '/thread/' . StringHelper::slugify($data['thread_title'], $data['thread_id']) . '/' . $data['sort']
        . '/' . $page . '/' .  $data['post_id'];

        $send_data['unsubscribe_url'] = env('EVERFORO_DOMAIN', 'https://sa.everforo.com') . '/g/' . $data['group_name']
        . '/thread/' . StringHelper::slugify($data['thread_title'], $data['thread_id']) . '/' . $data['sort']
        . '/0/1/unsubscribe/' . $data['user_id'];

        $send_data['user_name'] = $data['user_name'];
        $send_data['thread_title'] = $data['thread_title'];
        $send_data['group_name'] = $data['group_name'];
        $send_data['group_title'] = $data['group_title'];
        if ($data['to_user_id'] == $data['user_id']) {
            $send_data['to_user'] = 'your';
        } else {
            $send_data['to_user'] = $data['to_user_name'];
        }
        $send_data['type'] = $data['type'];
        $subscribeMail = new SubscribeMail($send_data);

        if (env('APP_ENV') === 'local') {
            \Log::info($send_data);
            return;
        }

        try{
            Mail::to($data['email'])->send($subscribeMail);
        } catch(Exception $e){
            print_r($e);
        }
    }

    /**
     * send subscribe mail
     * 
     * @param string $group_name
     * @param object $thread
     * @param int $post_id
     * @param int $parent_id
     * @param int $user_id
     * @param object $groupFollowRep
     * @param int $group_id
     * 
     * @return null
     * 
     */
    public function sendSubscribeMail($group_name, $group_title, $thread, $post_id, $parent_id, $user_id, $groupFollowRep, $group_id, $type = 'replied to')
    {
        $send_user_list = array();
        $data['sort'] = 'Relevant';
        if ($parent_id == -1) {
            $res = ThreadSubscribe::select('user_id')
                ->where('thread_id', $thread->id)
                ->get()->toArray();
            if ($res) {
                $send_user_list = array_column($res, 'user_id');
            }  
            $data['to_user_id'] = $thread->user_id;
            $user = User::getOne($data['to_user_id'])->toArray();
            $data['to_user_name'] = $user['name'];
        } else {
            $post = Post::find($parent_id)->toArray();
            if ($post) {
                $data['to_user_id'] = $post['user_id'];
                if ($this->isThreadSubscribed($post['user_id'], $thread->id)) {
                    $send_user_list[] = $post['user_id'];
                }
            } else {
                return;
            }
        }

        if ($send_user_list) {
            foreach ($send_user_list as $send_user_id) {
                if ($send_user_id && $user_id != $send_user_id) {
                    $send_user = User::getOne($send_user_id)->toArray();
                    $user = User::getOne($user_id)->toArray();
                    if ($user && $send_user && $send_user['email'] && 
                    $groupFollowRep->isGroupFollowed($send_user_id, $group_id)) {
                        //if user unSubscribe、has no email,don't send email
                        $data['email'] = $send_user['email'];
                        $data['user_name'] = $user['name'];
                        $data['post_id'] = $post_id;
                        $data['thread_title'] = $thread->title;
                        $data['thread_id'] = $thread->id;
                        $data['group_name'] = $group_name;
                        $data['group_title'] = $group_title;
                        $data['user_id'] = $send_user_id;
                        $data['type'] = $type;
                        $this->sendMail($data);
                    }
                }
            }
        }
    }

}