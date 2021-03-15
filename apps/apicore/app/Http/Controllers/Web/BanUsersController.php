<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Utils\Transformer;

use App\Repositories\AdminRepository;
use App\Repositories\BanUsersRepository;
use App\Repositories\GroupAdminRepository;
use App\Repositories\GroupRepository;
use App\Repositories\GroupFollowRepository;
use App\Repositories\NotificationsRepository;

class BanUsersController extends Controller
{
    private $_group;
    private $_transformer;
    public function __construct(Transformer $transformer)
    {
   
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
    }

    /**
     * admin ban user
     *
     * @param Request $request
     * @param BanUsersRepository $banUsersRep
     * @param NotificationsRepository $notificationsRep
     * @param GroupFollowRepository $groupFollowRep
     * @param $user_id
     *
     * @return null
     */
    public function banUser(Request $request,
                            AdminRepository $adminRep,
                            BanUsersRepository $banUsersRep,
                            GroupRepository $groupRep,
                            GroupFollowRepository $groupFollowRep,
                            NotificationsRepository $notificationsRep,
                            GroupAdminRepository $groupAdminRep,
                            $user_id)
    {
        $user = $request->user();

        if (!$adminRep->isSuperAdmin($user->id)
            && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id)
            ) {
            return $this->_transformer->noPermission();
        }

        if (!$groupFollowRep->isGroupFollowed($user_id, $this->_group->id)) {
            return $this->_transformer->fail(40002);
        }

        if ($groupAdminRep->checkGroupAdmin($this->_group->id, $user_id)) {
            return $this->_transformer->fail(40003);
        }

        $result = $banUsersRep->adminBanUser($user_id, $this->_group->id);

        if ($result) {
            // ban is left group
            $groupFollowRep->unfollowGroup($user_id, $this->_group->id);
            $notificationsRep->readNotificationsByGroup($user->id, $this->_group->id);
            return $this->_transformer->success(['ban_status'=>true]);
        }

        return $this->_transformer->fail(40001, "ban user failed");
    }

    /**
     * admin ban user
     *
     * @param Request $request
     * @param GroupRepository $GroupRep
     * @param GroupFollowRepository $groupFollowRep
     * @param int $user_id
     *
     * @return null
     */
    public function unBanUser(Request $request,
                                AdminRepository $adminRep,
                                BanUsersRepository $banUsersRep,
                                GroupRepository $groupRep,
                                GroupFollowRepository $groupFollowRep,
                                GroupAdminRepository $groupAdminRep,
                                $user_id)
    {
        $user = $request->user();

        if (!$adminRep->isSuperAdmin($user->id)
            && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id)
            ) {
            return $this->_transformer->noPermission();
        }

        $deletedRows = $banUsersRep->adminUnBanUser($user_id, $this->_group->id);

        if($deletedRows){
            $groupFollowRep->followGroup($user_id, $this->_group->id);
            return $this->_transformer->success(['unban_status'=>true]);
        }
        
        return $this->_transformer->fail(40001, "unban user failed");
    }
}