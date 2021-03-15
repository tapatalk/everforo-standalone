<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Repositories\BanUsersRepository;
use App\Repositories\UserRepo;
use App\Repositories\GroupFollowRepository;
use App\Http\Requests\Web\SubscribeThreadRequest;
use App\Models\Thread;
use App\Repositories\SubscribeRepository;
use Illuminate\Http\Request;

class SubscribeThreadController extends Controller
{
    private $_transformer;
    private $_group;


    public function __construct(Transformer $transformer)
    {
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
    }

    /**
     * subscribe a thread
     * 
     * @param Request $request
     * @param GroupFollowRepository $groupFollowRep
     * @param SubscribeRepository $subscribeRep
     * @param BanUsersRepository $banUsersRep
     * 
     * @return \App\Utils\json|array
     */
    public function subscribeThread(
        Request $request,
        GroupFollowRepository $groupFollowRep,
        SubscribeRepository $subscribeRep,
        BanUsersRepository $banUsersRep,
        $thread_id
    )
    {
        $user = $request->user();
        if(UserRepo::isSilenceRegister($user)){
            return $this->_transformer->fail(400, "please register");
        }
        $thread = Thread::find($thread_id);
        if (!$thread) {
            return $this->_transformer->fail(400, "thread not found");
        }
        //if user ban , can not Subscribe thread
        if ($banUsersRep->checkUserBan($user->id, $thread->group_id)) {
            return $this->_transformer->fail(401, "You have been banned.");
        }

        //check user follow group
        if (!$groupFollowRep->isCanFollow($user->id, $this->_group->id)) {
            return $this->_transformer->fail(40003);
        }

        $groupFollowRep->followGroup($user->id, $thread->group_id);
        $subscribeRep->subscribeThread($user->id, $thread_id, $thread->group_id);
        return $this->_transformer->success(['success' => 1]);
    }

    /**
     * unsubscribe a thread
     * 
     * @param Request $request
     * @param SubscribeRepository $subscribeRep
     * @param BanUsersRepository $banUsersRep
     * 
     * @return \App\Utils\json|array
     */
    public function unsubscribeThread(
        Request $request,
        SubscribeRepository $subscribeRep,
        BanUsersRepository $banUsersRep,
        $thread_id,
        $user_id
    )
    {
        if (!$user_id) {
            $user = $request->user();
            if(UserRepo::isSilenceRegister($user)){
                return $this->_transformer->fail(400, "please register");
            } else {
                $user_id = $user->id;
            }
        }
        
        $thread = Thread::find($thread_id);

        if (!$thread) {
            return $this->_transformer->fail(400, "thread not found");
        }
        if ($banUsersRep->checkUserBan($user_id, $this->_group->id)) {
            return $this->_transformer->fail(401, 'You have been banned.');
        }

        $subscribeRep->unsubscribeThread($user_id, $thread_id);
        return $this->_transformer->success(['success' => 1]);
    }

}