<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Repositories\AdminRepository;
use App\Repositories\GroupAdminRepository;
use App\Repositories\GroupFollowRepository;
use App\Utils\Transformer;
use App\Utils\Constants;

use Illuminate\Http\Request;
use App\Http\Requests\Settings\UpdateProfileRequest;

use App\Repositories\BanUsersRepository;
use App\Repositories\GroupRepository;
use App\Repositories\UserRepo;
use App\Repositories\UploadRepository;
use App\Repositories\RegisterRepository;
use App\Repositories\UserBehaviorRepository;

class UserController extends Controller
{

    private $userRepo;
    private $_transformer;
    private $_registeRep;

    /**
     * UserController constructor.
     * @param UserRepo $userRepo
     */
    public function __construct(UserRepo $userRepo, Transformer $transformer, RegisterRepository $registerRepository)
    {
        $this->userRepo = $userRepo;
        $this->_transformer = $transformer;
        $this->_registeRep = $registerRepository;
    }

    /**
     * A custom broadcast auth,  
     * TODO this is a temporary solution, do a proper auth later
     *
     * @param string $username
     * @return \Illuminate\Http\Response
     */
    public function broadcastAuth(Request $request)
    {
        $channel = $request->input('channel_name');

        preg_match("/private\-web\-user\.(\d+)/", $channel, $matches);

        if (empty($matches) || empty($matches[1])){
            return response()->json(['error' => 'broadcast auth failed'], 403); 
        }

        $user_id = $matches[1];
        $data = [];

        if ($user_id) {
            $data = $this->userRepo->getCurrentUser($user_id);
        }

        if (empty($data)){
            return response()->json(['error' => 'broadcast auth failed, user not exists'], 403); 
        }

        return $this->_transformer->success();
    }
    
    public function uploadAvatar(Request $request, UploadRepository $imageRepo)
    {
        $file = $request->file('image');

        if($file->getMimeType() == 'image/gif' && $file->getSize() > UploadRepository::GIF_FILESIZE_LIMIT ) {
            return $this->_transformer->fail(403, 'GIF avatar max size is ' . UploadRepository::GIF_FILESIZE_LIMIT/(1024*1024) . 'MB' );
        }
        
        $response = $imageRepo->save($request, $file, 'avatar');
    
        return $this->_transformer->success($response);
    }

    public function updateInfo(UpdateProfileRequest $request)
    {
        $data = $request->all();
        $user = $request->user();

//        $username = $request->only('name')['name'];

        if(empty($data['name'])){
            $data['name'] = $user->name;
        }

        if(in_array($data['name'],Constants::reserved_names)) {
            return $this->_transformer->fail(40019,'invalid username');
        }

        if (!$this->_registeRep->charactersValid($data['name'])) {
            return $this->_transformer->fail(40013,'Sorry, You cannot use special characters in your username.');
        }

        $response = tap($user)->update($request->only('name', 'photo_url'));

        return $this->_transformer->success($response);
    }

    public function getProfile(GroupRepository $groupRep,
                                BanUsersRepository $banUsersRep,
                                GroupFollowRepository $groupFollowRep,
                                GroupAdminRepository $groupAdminRep,
                                AdminRepository $adminRep,
                                $profile_id,
                                $group_name = ''
    )
    {
        if (!$profile_id)
        {
            return $this->_transformer->fail(403, 'Profile not exists');
        }

        $user = $this->userRepo->getUserObject($profile_id);

        if (!$user) {
            return $this->_transformer->fail(403, 'Profile not exists');
        }

        // if has group name check user ban status
        if ($group_name) {
            $group = $groupRep->getGroupByName($group_name);
            if ($group) {
                $user->is_ban = $banUsersRep->checkUserBan($profile_id, $group->id) ? 1 : 0;
                $user->is_follow = $groupFollowRep->isGroupFollowed($profile_id, $group->id) ? 1 : 0;
                $user->is_admin = $groupAdminRep->checkGroupAdmin($group->id, $profile_id) || $adminRep->isSuperAdmin($user->id) ? 1 : 0;
            }
        }

        $statstic = $this->userRepo->getUserSatistic($user->id);

        if (UserBehaviorRepository::checkUserOnline($user->id)) {
            $user->online = true;
        } else {
            $user->online = false;
        }
        $last_seen = UserBehaviorRepository::getUserLastSeen($user->id);
        $user->last_seen = $last_seen ? $last_seen : date('Y-m-d H:i:s', strtotime($user->updated_at));

        $user->likes = $statstic['likes'];
        $user->posts = $statstic['posts'];

        return $this->_transformer->success([
            'user' => $user,
        ]);
    }

    public function getProfileList(Request $request)
    {
        $profiles = $this->userRepo->getProfiles($request->input('user_id_list', []));

        return $this->_transformer->success([
            'profiles' => $profiles,
        ]);
    }

    public function switchDarkMode(Request $request) {

        $dark_mode = $request->input('dark_mode');
        $user = $request->user();

        return $this->_transformer->success([
            'users_settings' => $this->userRepo->switchDarkMode($user->id, $dark_mode),
        ]);
    }

    public function switchLanguage(Request $request) {

        $language = $request->input('language');
        $user = $request->user();
        return $this->_transformer->success([
            'success' => $this->userRepo->switchLanguage($user->id, $language) ? 1 : 0,
        ]);
    }

}