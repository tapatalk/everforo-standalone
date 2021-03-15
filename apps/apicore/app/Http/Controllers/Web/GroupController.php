<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\MemberListRepository;
use App\Utils\Transformer;
use App\Utils\Constants;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

use App\Http\Requests\Web\NewGroupRequest;
use App\Http\Requests\Web\UpdateGroupRequest;

use App\Repositories\AttachedFilesRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ThreadListRepository;
use App\Repositories\AdminRepository;
use App\Repositories\GroupRepository;
use App\Repositories\NotificationsRepository;
use App\Repositories\GroupFollowRepository;
use App\Repositories\BlockUsersRepository;
use App\Repositories\BanUsersRepository;
use App\Repositories\TokenTransactionRepo;
// use App\Repositories\OrdersRepo;

use App\Models\Group;
use App\Models\Category;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Admin;
// use App\Models\GroupBanUsers;
use App\Models\GroupFollow;
use App\Repositories\FeaturesRepo;
use App\Repositories\GroupAdminRepository;
use DB;

class GroupController extends Controller
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
     * Get the profile of the user given by their username
     *
     * @param string $username
     * @return \Illuminate\Http\Response
     */
    public function getGroupInfo(
                        Request $request,
                        FeaturesRepo $FeaturesRepo,
                        MemberListRepository $memberListRep,
                        GroupAdminRepository $groupAdminRep
                    )
    {
        $response = array();
        $response['id'] = $this->_group->id;
        $response['name'] = $this->_group->name;
        $response['title'] = $this->_group->title;
        $response['created_at'] = $this->_group->created_at->toDateTimeString();
        $response['owner'] = $this->_group->owner;
        $response['cover'] = $this->_group->cover;
        $response['logo'] = $this->_group->logo;
        $response['updated_at'] = $this->_group->updated_at->toDateTimeString();
        $response['description'] = $this->_group->description;
        $response['no_recommend'] = $this->_group->no_recommend;
        $response['super_no_recommend'] = $this->_group->super_no_recommend;
        $response['joining'] = 1;
        $response['visibility'] = 1;
         $url = 'http://apiprivacy/apiprivacy/get_feature_setting?group_id=' . $this->_group->id;
         $levelSetting = geturl($url);
         if ($levelSetting) {
             if ($levelSetting && !empty($levelSetting['data'])) {
                 if ($levelSetting['data']['joining']) {
                     $response['joining'] = $levelSetting['data']['joining'];
                 }
                 if ($levelSetting['data']['visibility']) {
                     $response['visibility'] = $levelSetting['data']['visibility'];
                 }
             }
         }
        $erc20_token = [
            'status' => 0,
            'is_import' => 0,
            'address'=> '',
            'name' => '',
            'symbol' => '',
            'decimal' => 0,
            'logo' => '',
            'balance' => 0,
        ];
        if(!empty($this->_group->group_erc20token)){
            $erc20_token['status']      = $this->_group->group_erc20token->status;

            // if ($erc20_token['status'] == 1) {
            //     // get order info

            //     if(isset($this->_group->group_erc20token->token_id)) {
            //         $order = OrdersRepo::getUnfinishedGroupCreateTokenOrder($this->_group->group_erc20token->token_id);

            //         if ($order){
            //             $erc20_token['order_id'] = $order->order_id;
            //         }
            //     }

            // }

            $erc20_token['is_import']   = $this->_group->group_erc20token->is_import;
            $erc20_token['address']     = $this->_group->group_erc20token->public_key;
            if(!empty($this->_group->group_erc20token->erc20_token)){
                $erc20_token['name']      = $this->_group->group_erc20token->erc20_token->name;
                $erc20_token['symbol']      = $this->_group->group_erc20token->erc20_token->symbol;
                $erc20_token['decimal']      = $this->_group->group_erc20token->erc20_token->decimal;
                $erc20_token['logo']      = $this->_group->group_erc20token->erc20_token->logo;
                $erc20_token['contract_url'] =  Constants::get_etherscan_link() . '/address/' . $this->_group->group_erc20token->erc20_token->contract_address;
                if(!empty($this->_group->token_wallet)){
//                        \Log::error('group:' . $this->_group->id . ' balance:' .$this->_group->token_wallet->balance);
//                        \Log::error($erc20_token['decimal']);
                    $erc20_token['balance']      = TokenTransactionRepo::getRealBalance($this->_group->token_wallet->balance, $erc20_token['decimal'])  ;
                }
            }
        }
        $response['erc20_token'] = $erc20_token;

        $response['feature'] = $FeaturesRepo->getList($this->_group->id);

        foreach ($response['feature'] as $feature) {
            if ($feature->feature_name == 'attached_files' && $feature->status == 1) {
                $attachedFilesRepo = new AttachedFilesRepository();

                $response['attached_files'] = $attachedFilesRepo->getGroupSetting($this->_group->id);
            }
        }
        $response['group_admin'] = $groupAdminRep->getGroupAdmin($this->_group->id);
        $response['online_members'] = $memberListRep->getOnlineNum($this->_group->id);

        return $this->_transformer->success([ 'group' => $response]);
    }

    /**
     * crate a new group, only a group name is needed
     * @param NewGroupRequest $request
     * @return array
     */
    public function createGroup(
                                    NewGroupRequest $request,
                                    GroupFollowRepository $groupFollowRep,
                                    FeaturesRepo $featuresRep,
                                    GroupAdminRepository $groupAdminRep,
                                    MemberListRepository $memberListRep
                                )
    {
        $user = $request->user();
        $data = $request->all();

        $group_data = [
            'name' => preg_replace('/[^a-z0-9]/', '', $data['name']),
            'title' => $data['title'],
            'owner' => $user->id,
        ];

        if (!empty($data['cover'])) {
            $group_data['cover'] = $data['cover'];
        }

        if (!empty($data['logo'])) {
            $group_data['logo'] = $data['logo'];
        }

        if (!empty($data['description'])) {
            $group_data['description'] = $data['description'];
        }

        if (!empty($data['no_recommend']) && $data['no_recommend']  ) {
            $group_data['no_recommend'] = 1;
        }

        if(in_array($group_data['name'], Constants::reserved_names)){
            return $this->_transformer->fail(40019, 'invalid group name');
        }

        $group = Group::where('name', $group_data['name'])->first();
        if($group){
            return $this->_transformer->fail('40002', 'This URL is not available. Please choose another one.');
        }

        $title_taken = Group::where('title', $group_data['title'])->first();
        if($title_taken){
            return $this->_transformer->fail('40003', 'Group name is taken.');
        }

        try {

            DB::beginTransaction();

            $group = Group::create($group_data);
            $response_category = array();

            if (isset($data['categories'])) {
                $categories = json_decode($data['categories'], true);

                foreach ($categories as $key => $category) {
                    # code...
                    $created_category = Category::create([
                        'name' => trim($category['name']),
                        'group_id' => $group->id,
                        'category_id' => $key + 1,
//                    'parent_id' => '-1',
                    ]);
                    $response_category[] = $created_category;
                }
            }

            $groupFollowRep->followGroup($user->id, $group->id);

            //auto open subscribe and share extension
            $featuresRep->switchFeature(Constants::SUBSCRIBE_FEATURE, $group->id, Constants::FEATURE_STATUS);
            $featuresRep->switchFeature(Constants::SHARE_FEATURE, $group->id, Constants::FEATURE_STATUS);
            $featuresRep->switchFeature(Constants::ATTACHMENTS_FEATURE, $group->id, Constants::FEATURE_STATUS);
            $featuresRep->switchFeature(Constants::MODERATOR_FEATURE, $group->id, Constants::FEATURE_STATUS);
            $featuresRep->switchFeature(Constants::GROUP_PRIVACY_FEATURE, $group->id, Constants::FEATURE_STATUS);
            $groupAdminRep->addGroupOwner($user->id, $group->id);
            $memberListRep->addAdminLevelSetting($group->id);
            DB::commit();

            return $this->_transformer->success(['group' => $group, 'categories' => $response_category]);
        } catch (\Exception $e) {
            DB::rollBack();
//            return $this->_transformer->fail(512, $e->getMessage());
            return $this->_transformer->fail(512, 'Server error please try again later');
        }
    }

    /**
     * update group info, and group categories
     * @param UpdateGroupRequest $request
     * @param CategoryRepository $categoryRepo
     * @return \App\Utils\json|array
     */
    public function updateGroupInfo(UpdateGroupRequest $request, CategoryRepository  $categoryRepo, GroupAdminRepository $groupAdminRep)
    {
        $user = $request->user();

//        $admin_list = Admin::select('user_id')->get()->toArray();
//        $admin_list = array_pluck($admin_list, 'user_id');
        $admin_list = [];
        $data = $request->all();
        $group = Group::find($data['group_id']);
        if (
            $group->owner != $user->id 
            and !in_array($user->id, $admin_list)
            && !$groupAdminRep->checkGroupAdmin($data['group_id'], $user->id, 2)
            ) {
            return $this->_transformer->noPermission();
        }

        // $new_name = preg_replace('/[^\w\d]/', '', $request->name);

        if($group->title != $request->title){

            if(in_array($request->title, Constants::reserved_names)){
                return $this->_transformer->fail(40019, 'Invalid group name.');
            }

            $new_name_occupy = Group::where("title", $request->title)->first();
            if(!empty($new_name_occupy)){
                return $this->_transformer->fail(512, 'Group name is taken.');
            }
        }

        try {

            DB::beginTransaction();

            $group_updated = tap($group)->update($request->only('title', 'description', 'cover', 'logo','no_recommend'));

            if (isset($data['categories'])) {
                $old_category_tree = $categoryRepo->getCategoryTree($group->id);
                $old_categories = [];

                foreach ($old_category_tree as $old_category){
                    $old_categories[$old_category->category_id] = $old_category->name;
                }

                $id_delete = array_keys($old_categories);

                $new_categories = json_decode($data['categories'], true);

                if(count($new_categories) > 20 ){
                    if(count($old_categories) < count($new_categories) ){
                        throw new Exception('no more than 20 Categories');
                    }
                }

                $name_ary = [];
                foreach ($new_categories as $category){
                    if(in_array($category['name'],$name_ary)){
                        throw new \Exception('The same categories name is not allowed');
                    }
                    $name_ary[] = $category['name'];
                }

                foreach ($new_categories as $category) {

                    // update a category
                    if (!empty($category['id'])) {
                        $delete_index = array_search($category['id'], $id_delete);
                        // this statement shall always be true, keep it here to be prudent
                        if ($delete_index !== false) {
                            // this category not to be deleted
                            unset($id_delete[$delete_index]);

                            if ($category['name'] !== $old_categories[$category['id']]) {
                                $categoryRepo->updateCategory($group->id, $category['id'], $category['name']);
                            }
                        }
                    } else {
                        $categoryRepo->addCategory($group->id, trim($category['name']));
                    }
                }

                // delete some category
                if (count($id_delete)){
                    foreach ($id_delete as $id){
                        $categoryRepo->deleteCategory($group->id, $id);
                    }
                }
            }


            if(isset($request->super_no_recommend)){

//                $admin_list = Admin::select('user_id')->get()->toArray();
//                $admin_list = array_pluck($admin_list, 'user_id');
                $admin_list = [];
                if(!in_array($user->id, $admin_list)){
                    return $this->_transformer->noPermission();
                }

                $group_updated = tap($group_updated)->update(['super_no_recommend' => $request->super_no_recommend ? 1 : 0]);
            }

            DB::commit();

            return $this->_transformer->success(['group' => $group_updated, 'categories' => $categoryRepo->getCategoryTree($group->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->_transformer->fail(512, $e->getMessage());
        }
    }

    public function follow(Request $request,
                    BanUsersRepository $banUsersRep,
                    GroupFollowRepository $groupFollowRep){

        $user = $request->user();
        //If the user is banned，can not join group
        if($banUsersRep->checkUserBan($user->id, $this->_group->id)) {
            return $this->_transformer->fail(401, 'You have been banned.');
        }
         
        $follow  = $groupFollowRep->followGroup($user->id, $this->_group->id);
        if($follow){
            return $this->_transformer->success([ 'success'=>1]);
        }
    }

    public function unfollow(Request $request, 
                             NotificationsRepository $notificationsRep,
                             GroupAdminRepository $groupAdminRep,
                             GroupFollowRepository $groupFollowRep){
        $user = $request->user();
    
        $deletedRows = $groupFollowRep->unfollowGroup($user->id, $this->_group->id);
                
        if($deletedRows){
            $notificationsRep->readNotificationsByGroup($user->id, $this->_group->id);
            $groupAdminRep->deleteGroupModerator($user->id, $this->_group->id);
            return $this->_transformer->success([ 'success'=>1]);
        } else {
            return $this->_transformer->noPermission();
        }
    }

    /**
     * get group statistics info
     */
    public function groupStatistic(Request $request,GroupFollowRepository $groupFollowRep) {
        $group_id = $this->_group->id;
        $user = $request->user();
        $user_id = $user && $user->id ? $user->id : 0;
        $members = $groupFollowRep->totalFollowers($this->_group->id);
        $query = Thread::where('threads.group_id', $group_id)
            ->join('group_ban_users', function($join) use ($group_id){
                $join->on('group_ban_users.user_id','=','threads.user_id')
                    ->where('group_ban_users.group_id','=',$group_id)
                    ->where('group_ban_users.deleted_at',null);
            }, null,null,'left')
            ->join('group_pin', function($join) {
                $join->on('group_pin.thread_id','=','threads.id')
                    ->where('group_pin.deleted_at',null);
            }, null,null,'left')
            ->whereHas('first_post', function ($query) {
                $query->where('deleted', 0);
            })
            ->whereNotIn('threads.user_id', function ($query) use ($group_id) {
                $query->select("group_ban_users.user_id")->from("group_ban_users")
                    ->where("group_ban_users.group_id","$group_id")->whereNull("group_ban_users.deleted_at");
            });

        if ($user_id) {
            $query->whereNotIn('threads.user_id', function ($query) use ($user_id) {
                $query->select("block_users.block_user_id")->from("block_users")->where("block_users.user_id","$user_id");
            });
        }
        $threads = $query->count();
        return $this->_transformer->success(['group_statistic' => ['members' => $members, 'threads' => $threads]]);
    }

    /**
     * get feed
     */
    public function feed(Request $request, 
                        ThreadListRepository $threadListRep, 
                        GroupFollowRepository $groupFollowRep,
                        BlockUsersRepository $blockUserRep,
                        $page, $my_group) {
        $user = $request->user();
        $blocked_users = [];

        if ($user && $user->id) {
            $blocked_users = $blockUserRep->getUserBlockedUsers($user->id);
        }

        if ($my_group) {

            $followed_groups = $groupFollowRep->userFowllowedGroups($user->id, true);

            if(empty($followed_groups)) {
                $followed_groups = [0];
            }

            $threadList = $threadListRep->threadFeed($page, $blocked_users, $followed_groups,
                ($user && $user->id) ? $user->id : '');
        } else {
            $threadList = $threadListRep->threadFeed($page, $blocked_users, [],
                ($user && $user->id) ? $user->id : '');
        }

        return $this->_transformer->success(['feed' => $threadList]);
    }

    public function removeThreadInFeed(Request $request){
	    $user = $request->user();
//	    $admin_list = Admin::select('user_id')->get()->toArray();
//	    $admin_list = array_pluck($admin_list, 'user_id');
        $admin_list = [];
        if(!in_array($user->id, $admin_list)){
                return $this->_transformer->noPermission();
        }
        $thread = Thread::find($request->thread_id);
        $thread = tap($thread)->update(['no_recommend' => $thread->no_recommend ? 0 : 1]);
        return $this->_transformer->success(['success' => 1]);
    }

    // public function removeGroupInFeed(Request $request){
    //     $user = $request->user();
    //     $admin_list = Admin::select('user_id')->get()->toArray();
    //     $admin_list = array_pluck($admin_list, 'user_id');
    //     if(!in_array($user->id, $admin_list)){
    //             return $this->_transformer->noPermission();
    //     }
    //     $group = Group::find($request->group_id);
    //     $group = tap($group)->update(['no_recommend' => 1]);
    //     return $this->_transformer->success(['success' => 1]);
    // }


    /**
     * delete group
     * @param $request
     * @return \App\Utils\json|array
     */
    public function deleteGroup(Request $request,
                                AdminRepository $AdminRepository,
                                GroupRepository $GroupRepository,
                                GroupAdminRepository $groupAdminRep
    )
    {
        $user = $request->user();

        $data = $request->all();
        $group = Group::find($data['group_id']);

        if(empty($group)){
            return $this->_transformer->fail(512,'Don\'t worry, please try again');
        }

        if(!$groupAdminRep->isAdmin($user->id, $data['group_id'], 1) && !$AdminRepository->isSuperAdmin($user->id) ){
            return $this->_transformer->noPermission();
        }

        try {
            $group_updated = $GroupRepository->deleteGroup($group);
            return $this->_transformer->success(['group' => $group_updated]);
        } catch (\Exception $e) {

            return $this->_transformer->fail(512, $e->getMessage());
        }
    }

}


