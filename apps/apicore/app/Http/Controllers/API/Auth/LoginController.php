<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;

use App\Models\GroupLevelSetting;
use App\Models\InviteMember;
use App\Repositories\BanUsersRepository;
use App\Repositories\FeaturesRepo;
use Event;
use Log;

use App\Events\LoginEvent;
use App\Utils\Transformer;
use Illuminate\Broadcasting\PrivateChannel;

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\JWTAuth;

use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\AppLoginRequest;
use App\Http\Requests\Auth\QRLoginRequest;

use App\Repositories\AdminRepository;
use App\Repositories\RegisterRepository;
use App\Repositories\BlockUsersRepository;
use App\Repositories\UserRepo;
use App\Repositories\GroupFollowRepository;
use App\Repositories\UserBehaviorRepository;
use App\Repositories\MemberListRepository;
use App\User;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $_transformer;

    private $userRepo;

    private $jwtGuard;
    private $jwtAuth;
    private $_regRepository;
    // private $jwt;
    // private $authHeader;

    public function __construct(
        UserRepo $userRepo,
        JWTGuard $jwtGuard,
        Transformer $transformer,
        JWTAuth $jwtAuth,
        RegisterRepository $registerRepository
        // JWT $jwt,
        // AuthHeaders $authHeader
    )
    {
        $this->userRepo = $userRepo;
        $this->jwtGuard = $jwtGuard;
        $this->_transformer = $transformer;
        $this->jwtAuth = $jwtAuth;
        $this->_regRepository = $registerRepository;
        $this->_group = config('app.group');
        // $this->jwt = $jwt;
        // $this->authHeader = $authHeader;
    }

    public function login(LoginRequest $request)
    {
        if ($token = Auth::attempt($request->only(['email', 'password']))) {
            $email = $request->input('email');
            UserBehaviorRepository::updateLastLoginByEmail($email);
            return $this->_transformer->success(['token' => $token]);
        }
        return $this->_transformer->fail(40001,"Invalid email or password");
    }

    public function applogin(AppLoginRequest $request)
    {
        if ($token = Auth::attempt($request->only(['email', 'password']))) {

            $email = $request->input('email');
            $user = User::where('email', $email)
                        ->first();
            $public_key = $request->input('public_key');
             tap($user)->update(['public_key' =>$public_key]);

            UserBehaviorRepository::updateLastLogin($user->id);

            return $this->_transformer->success(['user' => $user]);
        }
          
        return $this->_transformer->fail(40001,"Invalid email or password");
    }




    /**
     * generate a qr code token, which also used as the laravel echo channel name
     * for local develop, we can set a env variable LOCAL_SOCKET_CHANNEL
     * @param Request $request
     * @return array
     */
    public function QRCode(Request $request)
    {
        $channel_token = 'everforo://' . md5(rand() . time());

        if (env('APP_ENV') === 'local' && env('LOCAL_SOCKET_CHANNEL')){
            $channel_token = env('LOCAL_SOCKET_CHANNEL');
        }

        return $this->_transformer->success(['token' => $channel_token]);
    }

    /**
     * app scanning the qr code, we do authentication here
     * fire broadcast event when auth passed
     * @param Request $request
     * @return array
     */
    public function QRCodeScan(Request $request)
    {
        $user_id = $request->uid;
        $user = $this->userRepo->getUserObject($user_id);

        $bitcoinECDSA = new BitcoinECDSA();
        $public_key = $user->public_key;
        #    $public_key = "0323c0383d5c53694f2a019075a875585d8c678abb2f79ab316c76656222b83bf0";
        $address = $bitcoinECDSA->getAddress($public_key);
        $signature = $request->signature;
        #   $signature = "IMd5hhCnj4PBYK4WYiqVQ9yW2QfoztlPfxNlWz9VAS5cblZeAPJFiVD4G1L936t4tIXHXfw3BVYpEVxSj+Gdgc0=";
        $token = $request->token;
        $jwt_token = $this->jwtGuard->setTTL(86400)->login($user);

        Log::info('channel ' . $token . ' jwt ' . $jwt_token);

        if ($bitcoinECDSA->checkSignatureForMessage($address, $signature, $token)) {
            Event::fire(new LoginEvent($token, $jwt_token));

            Log::info('checkSignatureForMessage true');

            return $this->_transformer->success(['result' => 1]);
            #	    print "pass";
        }

        Log::info('checkSignatureForMessage false. ' . $public_key . ' address.' . $address . ' signature' . $signature);

        return $this->_transformer->success(['result' => 0]);
        #	    print "faile"
    }


    public function getToken(Request $request){
        $user = $request->user();
        $token =$this->jwtGuard->setTTL(86400)->login($user);
        return $this->_transformer->success(['token' => $token]);
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        return response()->json([
            'success' => true
        ]);
    }


    public function checkEmail(Request $request, FeaturesRepo $featuresRep)
    {
        $email = urldecode($request->input('email'));
        $go_back = $request->input('go_back');

        if($email){
            $user = User::where('email', $email )->first();

            if($user) {
                return $this->_transformer->success(['username' => $user->name]);
            } else {
                $join = 1;
                if ($featuresRep->isFeature($this->_group->id, 5)) {
                    $res = GroupLevelSetting::where('group_id', $this->_group->id)->first();
                    if ($res) {
                        $join = $res->joining;
                    }
                }
                if ($join == 3 || $join == 4) {
                    $request = InviteMember::where('email', $email)->first();
                    if (!$request) {
                        return $this->_transformer->fail(40013,'Sorry, You cannot use special characters in your username.');
                    }
                }

                $this->_regRepository->sendRegisterEmail($email, $go_back, $this->_group->title);
                return $this->_transformer->success(['username' => '']);
            }
       }
    }


    /**
     * get the current user info
     * @return array
     */
    public function getMe(Request $request, 
                        AdminRepository $adminRep,
                        BlockUsersRepository $blockUsersRep,
                        BanUsersRepository $banUsersRep,
                        GroupFollowRepository $groupFollowRep,
                        MemberListRepository $memberListRep
    ){

        $user = $request->user();

        // when doesn't exists, return a code in response
       
        $follow_groups = $groupFollowRep->userFowllowedGroups($user->id);
     
        $groups = array();
        foreach($follow_groups as $follow_group){

            $group = $follow_group->group;
            if($group){
                if($user->id == $group->owner){
                    //user is owner of the group
                    $group->is_admin = 1;
                }
                unset($group->owner);
                $group->url= env('EVERFORO_DOMAIN')."/g/".$group->name."/all";
                $groups[] = $group;
            }
        }
        // fetch user blocked users
        $blocked_users = $blockUsersRep->getUserBlockedUsers($user->id);

        $user->groups = $groups;
        $user->ban_group = $banUsersRep->getBanGroupByUserId($user->id);
        $user->blocked_users = $blocked_users;
        $user->joinStatus = $memberListRep->checkUserJoinRequest($this->_group->id, $user->id);
        $user->api_version = env('API_VERSION', 0);
        $user->super_admin = $adminRep->isSuperAdmin($user->id);
        $user->settings = $this->userRepo->getUserSettings($user->id);
        //$user->group_invite = $memberListRep::getGroupInviteCount($user->id);
        return  $this->_transformer->success([
            'user' => $user,
        ]);
    }


    /**
     * only for local testing
     * mimic the app scan QRcode behaviour, and login a certain user
     * @param Request $request
     * @return array
     */
    public function QRCodeScanLocal(Request $request)
    {
        $user_id = $request->input('user_id');

        if (!env('LOCAL_SOCKET_CHANNEL')) {
            return $this->_transformer->fail(666,
                'Don\'t you think I don\'t know what you are doing?');
        }

        $user = $this->userRepo->getUserObject($user_id);
        $channel = env('LOCAL_SOCKET_CHANNEL');

        $jwt_token = $this->jwtGuard->setTTL(86400)->login($user);

        Event::fire(new LoginEvent($channel, $jwt_token));

        $response = [
            'socket_channel' => $channel,
            'jwt_token' => $jwt_token,
            'expire_minutes' => 86400,
        ];
        return $this->_transformer->success($response);
    }
}
