<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\Transformer;
use App\Repositories\AdminRepository;
use App\Repositories\GroupAdminRepository;
use App\Repositories\GroupRepository;
use App\Repositories\GroupPinRepository;

class GroupAdminController extends Controller
{
    private $_group;
    private $_transformer;
    public function __construct(Transformer $transformer)
    {
   
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
    }

    /**
     * add group admin
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupRepository $groupRep
     * @param GroupPinRepository $groupPinRep
     * @param $thread_id
     *
     * @return null
     */
    public function addGroupAdmin(
                        Request $request,
                        AdminRepository $adminRep,
                        GroupAdminRepository $groupAdminRep
                    )
    {
        $user = $request->user();
        $data = $request->all();
        if (!$adminRep->isSuperAdmin($user->id) && !$groupAdminRep->isAdmin($user->id, $this->_group->id, 1)) {
            return $this->_transformer->noPermission();
        }
        
        if (!$groupAdminRep->addGroupAdmin($data['admin_id'], $this->_group->id)) {
            return $this->_transformer->fail(40001, "add admin failed");
        }
        
        return $this->_transformer->success(['success'=>true]);
    }

    /**
     * admin pin thread
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupRepository $groupRep
     * @param GroupPinRepository $groupPinRep
     * @param $thread_id
     *
     * @return null
     */
    public function deleteGroupAdmin(
                        Request $request,
                        AdminRepository $adminRep,
                        GroupAdminRepository $groupAdminRep
                    )
    {
        $user = $request->user();
        $data = $request->all();
        if (!$adminRep->isSuperAdmin($user->id) && !$groupAdminRep->isAdmin($user->id, $this->_group->id, 1)) {
            return $this->_transformer->noPermission();
        }

        $groupAdminRep->deleteGroupAdmin($data['admin_id'], $this->_group->id);
        return $this->_transformer->success(['success'=>true]);
    }
    
    /**
     * add group moderator
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupRepository $groupRep
     * @param GroupPinRepository $groupPinRep
     * @param $thread_id
     *
     * @return null
     */
    public function addGroupModerator(
                        Request $request,
                        AdminRepository $adminRep,
                        GroupAdminRepository $groupAdminRep
                    )
    {
        $user = $request->user();
        $data = $request->all();
        if (!$adminRep->isSuperAdmin($user->id)
                && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id, 2)
            ) {
            return $this->_transformer->noPermission();
        }

        if (!$groupAdminRep->addGroupModerator($data['admin_id'], $this->_group->id)) {
            return $this->_transformer->fail(40001, "add moderator failed");
        }

        return $this->_transformer->success(['success'=>true]);
    }

    /**
     * add group moderator
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupRepository $groupRep
     * @param GroupPinRepository $groupPinRep
     * @param $thread_id
     *
     * @return null
     */
    public function deleteGroupModerator(
                        Request $request,
                        AdminRepository $adminRep,
                        GroupAdminRepository $groupAdminRep
                    )
    {
        $user = $request->user();
        $data = $request->all();
        if (!$adminRep->isSuperAdmin($user->id)
                && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id, 2)) {
            return $this->_transformer->noPermission();
        }

        $groupAdminRep->deleteGroupModerator($data['admin_id'], $this->_group->id);
        return $this->_transformer->success(['success'=>true]);
    }

    /**
     * change group owner
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupAdminRepository $groupAdminRep
     * @return void
     */
    public function changeGroupOwner(
                        Request $request,
                        AdminRepository $adminRep,
                        GroupAdminRepository $groupAdminRep
                    )
    {
        $user = $request->user();
        $data = $request->all();
        if (!$adminRep->isSuperAdmin($user->id) && !$groupAdminRep->isAdmin($user->id, $this->_group->id, 1)) {
            return $this->_transformer->noPermission();
        }

        if ($groupAdminRep->isAdmin($data['admin_id'], $this->_group->id)) {
            if ($groupAdminRep->changeGroupAdmin($user->id, $data['admin_id'], $this->_group->id)) {
                return $this->_transformer->success(['success'=>true]);
            }
        }

        return $this->_transformer->fail(40001, "change admin failed");
    }

    /**
     * get group member
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupAdminRepository $groupAdminRep
     * @return void
     */
    public function getGroupMember(
                        Request $request,
                        AdminRepository $adminRep,
                        GroupAdminRepository $groupAdminRep
                    )
    {
        $admin = false;
        $adminData = array();
        $unAdminData = array();
        $moderatorData = array();
        $unModeratorData = array();
        $res = $groupAdminRep->getGroupMembers($this->_group->id);
        foreach ($res as $value) {
            if ($value['level'] == 2 || $value['level'] == 1 || $value['level'] == 3) {
                if ($value['level'] == 1) {
                    $admin = $value;
                } else if ($value['level'] == 2) {
                    $adminData[] = $value;
                } else {
                    $moderatorData[] = $value;
                }
            } else {
                $unAdminData[] = $value;
                $unModeratorData[] = $value;
            }
        }
        return $this->_transformer->success([
                    'admin' => $admin,
                    'adminData' => $adminData,
                    'unAdminData' => $unAdminData,
                    'moderatorData' => $moderatorData,
                    'unModeratorData' => $unModeratorData,
                ]);
    }

    /**
     * select group member
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupAdminRepository $groupAdminRep
     * @return void
     */
    public function selectGroupMember(
        Request $request,
        GroupAdminRepository $groupAdminRep
    )
    {
        $data = $request->all();
        
        if (is_null($data['name'])) {
            return $this->_transformer->success([
                'list' => [],
            ]);
        }
        $res = $groupAdminRep->selectGroupMember($this->_group->id, $data['name']);
        return $this->_transformer->success([
            'list' => $res
        ]);
    }

    /**
     * select group member
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupAdminRepository $groupAdminRep
     * @return void
     */
    public function selectGroupAdmin(
        Request $request,
        GroupAdminRepository $groupAdminRep
    )
    {
        $data = $request->all();
        
        if (is_null($data['name'])) {
            return $this->_transformer->success([
                'list' => [],
            ]);
        }
        $res = $groupAdminRep->selectGroupAdmin($this->_group->id, $data['name']);
        return $this->_transformer->success([
            'list' => $res
        ]);
    }

}