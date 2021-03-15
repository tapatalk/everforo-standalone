<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\GroupAdminRepository;
use App\Utils\Transformer;

use Event;
use App\Events\ReportPostEvent;

use App\Http\Requests\Web\ReportRequest;

use App\Repositories\NotificationsRepository;
use App\Repositories\ReportRepository;
use App\Repositories\PostRepository;

use App\Repositories\PushRepository;
use App\Models\Thread;
use App\Repositories\GroupFollowRepository;
use App\Repositories\UserBehaviorRepository;

class ReportController extends Controller
{
        /**
     * ProfileController constructor.
     *
     */
    private $_transformer;

    private $_group = "";

    private $reportRep;


    public function __construct(Transformer $transformer, ReportRepository $reportRep)
    {
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
        $this->reportRep = $reportRep;
    }

    /**
     * report a user
     * @param ReportRequest $request
     * @return \App\Utils\json|array
     */
    public function reportPost(ReportRequest $request,
                            PostRepository $postRep,
                            GroupFollowRepository $groupFollowRep,
                            GroupAdminRepository $groupAdminRep,
                            NotificationsRepository $notificationsRep,PushRepository $pushRep)
    {
        $user = $request->user();
        $post_id = $request->input('post_id');
        $reason = $request->input('reason');

        //check user follow group
        if (!$groupFollowRep->isCanFollow($user->id, $this->_group->id)) {
            return $this->_transformer->fail(40003);
        }

        list($report, $send_notification) = $this->reportRep->addReport($post_id, $this->_group->id, $user->id, $reason);

        $post = $postRep->getOne($post_id);
        $adminList = $groupAdminRep->getAllGroupAdmin($this->_group->id);
        foreach ($adminList as $value) {
            if ($send_notification && $value->user_id != $user->id) {
                $notifications = $notificationsRep->addReportNotifications($post, $value->user_id, $user->id);

                $thread = Thread::find($post->thread_id);

                Event::fire(new ReportPostEvent($value->user_id, $thread, $post, $user, $report));
                $subscribers = array();
                $subscribers[] = $value->user_id;

                $type = 'flag';
                $pushRep->sendPushJobs($post->group_id, $subscribers, $thread, $post, $user,$type);
            }
        }


        return $this->_transformer->success(['flag' => $report]);
    }

    /**
     * @param ReportRequest $request
     * @return \App\Utils\json|array
     */
    public function reportPostUserList($post_id)
    {
        if (!$post_id) {
            return $this->_transformer->fail(403, 'Post not exists');
        }

        $flag_list = $this->reportRep->userList($this->_group->id, $post_id);
        foreach ($flag_list as $key => $value) {
            if (UserBehaviorRepository::checkUserOnline($value['user_id'])) {
                $flag_list[$key]['online'] = true;
            } else {
                $flag_list[$key]['online'] = false;
            }
        }
        
        return $this->_transformer->success(['flag_list' => $flag_list]);
    }

}