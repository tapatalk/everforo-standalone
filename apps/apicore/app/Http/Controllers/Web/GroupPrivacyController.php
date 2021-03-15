<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Repositories\AdminRepository;
use App\Repositories\GroupAdminRepository;
use App\Utils\Transformer;
use Event;
use Illuminate\Http\Request;

class GroupPrivacyController extends Controller
{
    private $_group;
    private $_transformer;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Transformer $transformer)
    {
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
    }

    /**
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupAdminRepository $groupAdminRep
     * @return array|mixed
     */
    public function getGroupPrivacySetting(Request $request,
                                           AdminRepository $adminRep,
                                           GroupAdminRepository $groupAdminRep)
    {
        $user = $request->user();
        // if (empty($user->id) || (!$adminRep->isSuperAdmin($user->id) && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id, 2))) {
        //     return $this->_transformer->noPermission();
        // }
        $url = 'http://apiprivacy/apiprivacy/get_feature_setting?group_id=' . $this->_group->id;
        return geturl($url);
    }

    /**
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupAdminRepository $groupAdminRep
     * @return array|mixed
     */
    public function setGroupPrivacySetting(Request $request,
                                           AdminRepository $adminRep,
                                           GroupAdminRepository $groupAdminRep)
    {
        $data = $request->all();
        $user = $request->user();
       
        if (empty($user->id) || (!$adminRep->isSuperAdmin($user->id) && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id, 2))) {
            return $this->_transformer->noPermission();
        }
        $visibility = isset($data['visibility']) ? $data['visibility'] : 0;
        $joining = isset($data['joining']) ? $data['joining'] : 0;
        $url = 'http://apiprivacy/apiprivacy/set_feature_setting?group_id=' . $this->_group->id
            . '&visibility=' . $visibility . '&joining=' . $joining;
        return geturl($url);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function joinRequest(Request $request)
    {
        $data = $request->all();
        $user = $request->user();
        $join_msg = isset($data['join_msg']) ? $data['join_msg'] : '';
        $user_id = isset($user->id) ? $user->id : 0;
        $url = 'http://apiprivacy/apiprivacy/join_request?group_id=' . $this->_group->id
            . '&join_msg=' . $join_msg . '&user_id=' . $user_id;
        return geturl($url);
    }

    /**
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupAdminRepository $groupAdminRep
     * @return array|mixed
     */
    public function ignoreRequest(Request $request,
                                  AdminRepository $adminRep,
                                  GroupAdminRepository $groupAdminRep)
    {
        $user = $request->user();
        $data = $request->all();
        if (empty($user->id) || (!$adminRep->isSuperAdmin($user->id) && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id, 3))) {
            return $this->_transformer->noPermission();
        }
        $user_id = isset($data['user_id']) ? $data['user_id'] : 0;
        $url = 'http://apiprivacy/apiprivacy/ignore_request?group_id=' . $this->_group->id
            . '&user_id=' . $user_id;
        return geturl($url);
    }

    /**
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupAdminRepository $groupAdminRep
     * @return array|mixed
     */
    public function approveRequest(Request $request,
                                   AdminRepository $adminRep,
                                   GroupAdminRepository $groupAdminRep)
    {
        $data = $request->all();
        $user = $request->user();
        if (empty($user->id) || (!$adminRep->isSuperAdmin($user->id) && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id, 3))) {
            return $this->_transformer->noPermission();
        }
        $user_id = isset($data['user_id']) ? $data['user_id'] : 0;
        $url = 'http://apiprivacy/apiprivacy/approve_request?group_id=' . $this->_group->id
            . '&user_id=' . $user_id;
        return geturl($url);
    }
}
