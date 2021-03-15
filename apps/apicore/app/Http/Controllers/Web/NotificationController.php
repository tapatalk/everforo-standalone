<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Models\Notificaiton;
use App\Repositories\NotificationsRepository;


class NotificationController extends Controller
{

    private $_transformer;

    /**
     * UserController constructor.
     * @param UserRepo $userRepo
     */
    public function __construct( Transformer $transformer)
    {
        $this->_transformer = $transformer;
    }


    public function getNotifications(Request $request, NotificationsRepository $notificationsRep)
    {     
        $user = $request->user();
        $page = $request->input('page', 1);
        $perpage = $request->input('perpage', 20);

        $notifications = $notificationsRep->getNotifications($user->id, ($page-1)*$perpage, $perpage);

        return $this->_transformer->success(['notifications' => $notifications]);
    }

    /**
     * mark notification read by notification ids
     */
    public function readNotifications(Request $request, NotificationsRepository $notificationsRep)
    {     
        $user = $request->user();
        $notification_ids = $request->input('notification_ids', []);
 
        $response = $notificationsRep->readNotifications($user->id, $notification_ids);

        return $this->_transformer->success(['mark_read' => $response]);
    }


    public function testQueue()
    {
        $group_id = 1;
        $thread_id = 44;
        $payload = array();
        $payload['group_id']=1;
        $payload['thread_id'] = 44;
        Queue::push(new PushJob($payload));
    }



}