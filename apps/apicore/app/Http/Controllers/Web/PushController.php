<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Http\Requests\Web\RegisterTokenRequest;
use App\Models\PushToken;
use App\Jobs\PushJob;
use Aws\Sns\SnsClient; 
use Aws\Exception\AwsException;
use Log, Queue;


class PushController extends Controller
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


    public function registerToken(RegisterTokenRequest $request)
    {
     
        $user = $request->user();

        
        $data = $request->all();
        $response = array();


        PushToken::where('token', $request->token)->where('user_id','!=',$user->id)->delete();
        $token = PushToken::updateOrCreate(["user_id" =>$user->id],
            array('user_id' => $user->id, 'app_id' => $request->app_id,'token'=>$request->token));
        if($token->exists){
         //  tap($token)->update(['token' => $request->token]);
        }


        $SnSclient = new SnsClient([
            'region' => 'us-east-1',
            'version' => '2010-03-31',
            'credentials' => ['key' => env('AWS_KEY'), 'secret' => env('AWS_SECRET')],
        ]);
   
        $result = $SnSclient->createPlatformEndpoint([
            'CustomUserData' => '$user->id',
            'PlatformApplicationArn' => 'arn:aws:sns:us-east-1:046052160661:app/GCM/everforo', // REQUIRED
            'Token' => $request->token, // REQUIRED
        ]);
        $arn = $result->get('EndpointArn');
        $token = tap($token)->update(['arn' => $arn]);
        $response['token'] = $token;
        return $this->_transformer->success($response);
    }





}