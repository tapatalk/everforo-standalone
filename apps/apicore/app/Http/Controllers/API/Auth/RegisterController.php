<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\GroupLevelSetting;
use App\Models\InviteMember;
use App\Repositories\FeaturesRepo;
use App\Repositories\GroupAdminRepository;
use App\Repositories\GroupFollowRepository;
use App\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\RegisterByEmailRequest;
use App\Http\Requests\Auth\PasswordResetRequest;

use App\Utils\Constants;
use App\Utils\Transformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\RegisterRepository;
use App\Repositories\UserBehaviorRepository;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Redis;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $_transformer;
    private $_registeRep;
    public function __construct(Transformer $transformer,RegisterRepository $registerRepository)
    {
              $this->_transformer = $transformer;
              $this->_registeRep = $registerRepository;
        $this->_group = config('app.group');
    }

    /**
     * Create a new user instance after a valid registration.
     */
    public function store(RegisterRequest $request)
    {
        $data = $request->all();
        
        if(in_array($data['name'],Constants::reserved_names)){
            return $this->_transformer->fail(40019,'invalid username');
        }

        if (!$this->_registeRep->charactersValid($data['name']))
        {
            return $this->_transformer->fail(40013,'Sorry, You cannot use special characters in your username.');
        }

        $response =  User::create([
            'name' => $data['name'],
            'photo_url' => $data['photo_url'],
            'public_key' => $data['public_key'],
            'activate' => 1,
        ]);



        return $this->_transformer->success($response);
    }

    /**
     * Create silenceRegister.
     */
    public function silenceRegister(Request $request)
    {
        $data = $request->all();
        $data['name'] = 'silence_&' . rand(0,9999999999);

        $response =  User::create([
            'name' => $data['name'],
//            'photo_url' => $data['photo_url'],
            'public_key' => $data['public_key'],
            'activate' => 1,
        ]);

        return $this->_transformer->success($response);
    }

    public function registerAdmin(Request $request, GroupAdminRepository $groupAdminRep, GroupFollowRepository $groupFollowRep)
    {
        $data = $request->all();
        if(in_array($data['name'], Constants::reserved_names)) {
            return $this->_transformer->fail(40019,'invalid username');
        }
        if (!$this->_registeRep->charactersValid($data['name'])) {
            return $this->_transformer->fail(40013,'Sorry, You cannot use special characters in your username.');
        }
        $admins = $groupAdminRep->getGroupAdmin($this->_group->id, 1);
        if ($admins && count($admins) > 0) {
            return $this->_transformer->fail(40014,'Admin already exits!');
        }
        $created_user =  User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'activate' => 1
        ]);

        $userInfo = array(
            'email' => $data['email'],
            'password' => $data['password'],
        );

        $user_id = $created_user->id;
        $groupAdminRep->addGroupOwner($user_id, $this->_group->id);
        $groupFollowRep->followGroup($user_id, $this->_group->id);
        $token = Auth::attempt($userInfo);

        return $this->_transformer->success(['token' => $token]);
    }

    public function storeByEmail(RegisterByEmailRequest $request, GroupFollowRepository $groupFollowRep, FeaturesRepo $featuresRep)
    {
        $data = $request->all();
        $join = 1;
        $token = $data['token'];
        $redis = Redis::connection();
        $token_email = urldecode($redis->get($token));

        if(!$token_email or ($token_email != $data['email'])) {
             return $this->_transformer->fail(40010,'token invalid');
        }

        if(in_array($data['name'], Constants::reserved_names)) {
            return $this->_transformer->fail(40019,'invalid username');
        }

        if (!$this->_registeRep->charactersValid($data['name'])) {
            return $this->_transformer->fail(40013,'Sorry, You cannot use special characters in your username.');
        }

        if ($featuresRep->isFeature($this->_group->id, 5)) {
            $res = GroupLevelSetting::where('group_id', $this->_group->id)->first();
            if ($res) {
                $join = $res->joining;
            }
        }

        if ($join == 3 || $join == 4) {
            $request = InviteMember::where('email', $data['email'])->first();
            if (!$request) {
                return $this->_transformer->fail(40013,'Sorry, You cannot use special characters in your username.');
            }
        }

        $created_user =  User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'photo_url' => $request->input('photo_url',''),
            'password' => bcrypt($data['password']),
            'activate' => 1
        ]);

        $userInfo = array(
            'email' => $data['email'],
            'password' => $data['password'],
        );

        $token = Auth::attempt($userInfo);

        if ($token) {
            $redis->del($token);
        }

        // if($token && env('APP_ENV') !== 'local') {
        //     $this->sendConfirmEmail($data['email']);
        // }

        UserBehaviorRepository::updateLastLogin($created_user->id);
        if ($join == 1) {
            $groupFollowRep->followGroup($created_user->id, $this->_group->id);
        }
        return $this->_transformer->success(['token' => $token]);
    }

    public function sendConfirmEmail($email)
    { 
        try{
            $user = User::select('*')->where('email', $email) ->first();
            $this->_registeRep->sendConfirmEmail($user);
        } catch(Exception $e){
            print_r($e);
        }
    }

    public function emailConfirm(Request $request)
    {
        $user = User::where('id', $request->u)->first();

        if ( ! $user) return redirect("https://sa.everforo.com");

        if ($request->code == md5($user->id.md5($user->id))) {
            tap($user)->update(['activate' => 1]);
          
            return redirect("https://sa.everforo.com");
        }
    }

    public function sendPasswordResetEmail(PasswordResetRequest $request){
        $email = $request->input("email");
        $user = User::select('*')->where('email', $email)->first();
        if($user){
            $this->_registeRep->sendPwsswordResetEmail($user, $this->_group->title);
        } else {
            return $this->_transformer->fail(40010,'user not found'); // todo, use another status code
        }
        return $this->_transformer->success(['success' => 1]);
    }

    public function passwordReset(Request $request){
        $data = $request->all();
        $token = $data['token'];
        $email = $data['email'];
        $redis = Redis::connection();
        $token_email = urldecode($redis->get($token));

        if(!$token_email or ($token_email != $email)){
            return $this->_transformer->fail(40010,'token invalid');
        } else {
            $user = User::where('email', $email)->first();
            tap($user)->update(['password' => bcrypt($data['password'])]);
            $redis->del($token);
        }
        return $this->_transformer->success(['success' => $user->name]);
    }
}
