<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\Transformer;
use App\Repositories\AdminRepository;
use App\Repositories\GroupAdminRepository;
use App\Repositories\GroupRepository;
use App\Repositories\GroupPinRepository;
use App\Utils\Constants;

class GroupPinController extends Controller
{
    private $_group;
    private $_transformer;
    public function __construct(Transformer $transformer)
    {
   
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
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
    public function pin(
                        Request $request,
                        AdminRepository $adminRep,
                        GroupRepository $groupRep,
                        GroupPinRepository $groupPinRep,
                        GroupAdminRepository $groupAdminRep,
                        $thread_id
                    )
    {
        $user = $request->user();
        $pin_type = $adminRep->isSuperAdmin($user->id);
        if (!$pin_type
                && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id)
            ) {
            return $this->_transformer->noPermission();
        }
        $result = $groupPinRep->adminPinThread($thread_id, $this->_group->id, $user->id, $pin_type);
        if ($result) {
            return $this->_transformer->success(['success'=>true]);
        }
        return $this->_transformer->fail(40001, "pin thread failed");
    }

    /**
     * admin unpin thread
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupRepository $groupRep
     * @param GroupPinRepository $groupPinRep
     * @param int $thread_id thread_id
     *
     * @return null
     */
    public function unpin(
                        Request $request,
                        AdminRepository $adminRep,
                        GroupRepository $groupRep,
                        GroupPinRepository $groupPinRep,
                        GroupAdminRepository $groupAdminRep,
                        $thread_id
                    )
    {
        $user = $request->user();

        if (!$adminRep->isSuperAdmin($user->id)
            && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id)
        ) {
            return $this->_transformer->noPermission();
        }

        $deletedRows = $groupPinRep->adminUnpinThread($this->_group->id, $thread_id);

        if($deletedRows){
            return $this->_transformer->success(['success'=>true]);
        }
        
        return $this->_transformer->fail(40001, "unpin thread failed");
    }

    /**
     * admin unpin thread
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupRepository $groupRep
     * @param GroupPinRepository $groupPinRep
     * @param int $thread_id thread_id
     *
     * @return null
     */
    public function pinStatus(
        Request $request,
        AdminRepository $adminRep,
        GroupRepository $groupRep,
        GroupPinRepository $groupPinRep,
        GroupAdminRepository $groupAdminRep,
        $thread_id
        )
    {
        $user = $request->user();
        if (!$adminRep->isSuperAdmin($user->id)
            && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id)
        ) {
            return $this->_transformer->noPermission();
        }

        $status = $groupPinRep->checkGroupPin($this->_group->id, Constants::GROUP_ADMIN_PIN);

        if($status){
            return $this->_transformer->success(['status' => 0]);
        } else {
            return $this->_transformer->success(['status' => 1]);
        }
    }
}