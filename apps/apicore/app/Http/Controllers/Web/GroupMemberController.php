<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GroupLevelSetting;
use App\Repositories\MemberListRepository;
use App\Utils\Transformer;
use Illuminate\Http\Request;
use App\Repositories\AdminRepository;
use App\Repositories\BanUsersRepository;
use App\Repositories\FeaturesRepo;
use App\Repositories\GroupAdminRepository;
use App\Repositories\GroupFollowRepository;
use App\Repositories\GroupRepository;

class GroupMemberController extends Controller
{

    /**
     * ProfileController constructor.
     *
     */
    private $_group;
    private $_transformer;
    public function __construct(Transformer $transformer)
    {
   
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
    }

    /**
     * invite member
     *
     * @param Request $request
     * @param MemberListRepository $memberListRep
     * @param string $groupName
     */
    public function inviteMember(
        Request $request,
        MemberListRepository $memberListRep)
    {
        $user = $request->user();
        $data = $request->all();
        $group_id = $this->_group->id;
        $group_title = $this->_group->title;
        $group_name = $this->_group->name;


        switch ($memberListRep::checkInvite($group_id, $user->id, $data['email'])) {
                case 1:
                    return $this->_transformer->fail('40001');
                    break;
                case 2:
                    return $this->_transformer->fail('40002');
                    break;
                case 3:
                    return $this->_transformer->fail('40003');
                    break;
                case 4:
                    return $this->_transformer->fail('40004');
                    break;
                default;
        }

        $memberListRep::addInviteRecord($group_id, $user->id, $data['email'], $data['message']);
        $memberListRep::sendInviteEmail($group_name, $group_title, $this->_group->cover, $user->name, $user->photo_url, $data['email'], $data['message']);
        return $this->_transformer->success(['success' => 1]);
    }

    /**
     * invite member
     *
     * @param Request $request
     * @param MemberListRepository $memberListRep
     * @param string $groupName
     */
    public function sendApproveEmail(
            Request $request,
            MemberListRepository $memberListRep
        )
    {
        $data = $request->all();
        $email = $data['email'];
        $group_name = $data['group_name'];
        $group_title = $data['group_title'];
        $group_cover = $data['group_cover'];
        if ($email && $group_name) {
            $memberListRep::sendApproveEmail($group_name, $group_title, $group_cover, $email);
        } else {
            return 0;
        }
        return 1;
    }

    /**
     * get group ban num
     * @param BanUsersRepository $banUsersRep
     * @param MemberListRepository $memberListRep
     * @return array
     */
    function getGroupBanNum(
            BanUsersRepository $banUsersRep,
            MemberListRepository $memberListRep
        )
    {
        $ban_count = $banUsersRep->getGroupBanUserNum($this->_group->id);
        $online_count = $memberListRep->getOnlineNum($this->_group->id);
        $active_count = $memberListRep->getActiveNum($this->_group->id);
        $pending_count = $memberListRep->getPendingNum($this->_group->id);
        return $this->_transformer->success([
                'ban_count' => $ban_count,
                'online_count' => $online_count,
                'active_count' => $active_count,
                'pending_count' => $pending_count,
            ]);
    }

    /**
     * Get the member list of the user given by groupName
     *
     * @param Request $request
     * @param AdminRepository $adminRep
     * @param GroupRepository $groupRep
     * @param int $page
     * @param string $filter
     * @param string $groupName
     */
    public function getGroupMemberList(
        Request $request,
        MemberListRepository $memberListRep,
        AdminRepository $adminRep,
        GroupRepository $groupRep,
        GroupAdminRepository $groupAdminRep,
        $page, $filter)
    {
        $user = $request->user();
        $group_id = $this->_group->id;
        $page_length = 20;
        $filter = strtolower(urldecode($filter));
        $offset = ($page - 1) * $page_length;
        $res = array(
            'list' => array(),
            'count' => 0,
            'notOnline' => 0,
        );
        if ($filter === 'all' || $filter === 'relevant' || $filter === 'active' || $filter === 'online') {
            $res = $memberListRep->getMemberList($group_id, $offset, $page_length, $filter);
            if ($res['list']) {
                foreach ($res['list'] as $key => $value) {
                    $res['list'][$key]['is_ban'] = '';
                }
            }
        }

        if ($filter === 'pending') {
            $res = $memberListRep->getPendingList($group_id, $offset, $page_length, $filter);
        }

        if (isset($user->id) && !empty($user->id)
                && ($adminRep->isSuperAdmin($user->id)
                    || $groupAdminRep->checkGroupAdmin($this->_group->id, $user->id)
                ) && count($res['list']) < $page_length
                && ($filter === 'all' || $filter === 'banned')) {
            if ($offset > $res['count']) {
                $offset = $offset - $res['count'];
            } else {
                $offset = 0;
                $page_length = $page_length - count($res['list']);
            }
            $list = $memberListRep->getBanMemberList($group_id, $offset, $page_length);
            if ($list['list']) {
                foreach ($list['list'] as $k => $v) {
                    $list['list'][$k]['is_admin'] = '';
                    $list['list'][$k]['is_ban'] = 1;
                    $list['list'][$k]['online'] = false;
                }
                $res['list'] = array_merge($res['list'], $list['list']);
            }
            if ($filter === 'banned') {
                $res['count'] = $list['count'];
            }
        }
        return $this->_transformer->success(['list' => $res['list'], 'count' => $res['count'], 'notOnline' => $res['notOnline']]);
    }

    /**
     * get invite status
     *
     * @return void
     */
    public function getInviteStatus(
        Request $request,
        FeaturesRepo $featuresRepo,
        GroupFollowRepository $groupFollowRep,
        AdminRepository $adminRep,
        GroupAdminRepository $groupAdminRep
    )
    {
        $user = $request->user();
        $group_id = $this->_group->id;
        $user_id = $user->id;
        if ($featuresRepo->isFeature($group_id, 5)) {
            $groupLevel = GroupLevelSetting::where('group_id', $group_id)->first();
            if ($groupLevel->joining == 3) {
                if (!$groupFollowRep->isGroupFollowed($user_id, $group_id)) {
                    return $this->_transformer->success(['success' => 0]);
                }
            } else if ($groupLevel->joining == 4) {
                if (!$adminRep->isSuperAdmin($user_id)
                        && !$groupFollowRep->isGroupFollowed($user_id, $group_id)
                        && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user_id, 2)
                    ) {
                    return $this->_transformer->success(['success' => 0]);
                }
            }
        }
        return $this->_transformer->success(['success' => 1]);
    }

}


