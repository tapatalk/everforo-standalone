<?php

namespace App\Jobs;
use App\Utils\Constants;
use App\Events\AirdropActivitiesEvent;
use App\Models\AirdropJob;
use App\Models\AirdropExecLog;
use App\Models\Group;
use App\Models\Thread;
use App\Models\PushToken;
use App\Models\Erc20Token;
use App\Models\GroupFollow;
use App\Models\AirdropReachCount;
use App\Models\ERC20TokenImportLog;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use App\Repositories\TokenWalletRepo;
use App\Repositories\TokenTransactionRepo;
use App\Repositories\Erc20TokenRepo;
use App\Utils\StringHelper;
use App\Repositories\NotificationsRepository;
use APP\WriteLog;
use Log, Event;

class ERC20TokenJob extends  Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $_payload;
    protected $_job;
    protected $_erc20tokenrepo;
    protected $_tokenWalletRepo;
    protected $_tokenTransactionRepo;


    public function __construct($payload )
    {
        $this->_payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Erc20TokenRepo $Erc20TokenRepo, TokenWalletRepo $TokenWalletRepo, TokenTransactionRepo $TokenTransactionRepo)
    {
        $this->delete();

        if(empty($this->_payload['group_id'])){
            return false;
        }

        $group = Group::where('id', $this->_payload['group_id'])->first();
        $this->_payload['group'] = $group;

        $this->_erc20tokenrepo = $Erc20TokenRepo;
        $this->_tokenWalletRepo = $TokenWalletRepo;
        $this->_tokenTransactionRepo = $TokenTransactionRepo;

        if(isset($this->_payload['job_type']) && $this->_payload['job_type'] == 'airdrop' ){
            foreach(json_decode($this->_payload['airdrop_job_id']) as $job_id ){
                self::airdrop($job_id);
            }
            return ;
        }

        if(isset($this->_payload['job_type']) && $this->_payload['job_type'] == 'onetime_airdrop' ){
            $admin = \DB::table('users')->where('id',$this->_payload['group']->owner)->first();
            $this->_payload['admin'] = $admin;
            self::onetime_airdrop();
        }


    }

    public function ERC20Transaction( $count, $channel = 0 ){

        $_erc20tokenrepo = $this->_erc20tokenrepo;
        $_tokenWalletRepo = $this->_tokenWalletRepo;
        $_tokenTransactionRepo =  $this->_tokenTransactionRepo;

        $group_id = $this->_payload['group_id'];
        $user_id = $this->_payload['user_id'];

        //including group wallet

        $token =  $_erc20tokenrepo->getGroupErc20Token($group_id);

        if(!$token){
            return false;
        }

        $this->_payload['token'] = $token;

        $group_wallet = $_tokenWalletRepo->getWallet(0,$group_id, $token->token_id);
        if(!$group_wallet || !isset($group_wallet->id)){
            Log::error('ERC20TokenJob:ERC20Transaction: missing group wallet gid=' . $group_id );
            return false;
        }

        $user_wallet = $_tokenWalletRepo->getWallet($user_id,0, $token->token_id);
        if(!$user_wallet){
            $_tokenWalletRepo->createWallet($user_id,0,$token->token_id);
            $user_wallet = $_tokenWalletRepo->getWallet($user_id,0, $token->token_id);
        }
        if(!$user_wallet || !isset($user_wallet->id)){
            Log::error('ERC20TokenJob:ERC20Transaction: missing user wallet gid=' . $user_id );
            return false;
        }

        $transaction = $_tokenTransactionRepo->transactionToken( $group_wallet->id, $user_wallet->id, $token->token_id, $count, $channel);

        if(isset($transaction['error'])){
            Log::error('ERC20TokenJob:ERC20Transaction: transactionToken error' . $transaction['error'] );
            return false;
        }

        if(!empty($transaction) && isset($transaction['transaction']) ){
            $transaction['from_wallet_id'] = $token->wallet_id;
            $transaction['to_wallet_id'] = $user_wallet->id;
        }

        return $transaction;
    }


    public function airdrop($job_id){

        $job = AirdropJob::find($job_id);

        if(empty($job) || $job->exec_status != 1){
            return ;
        }

        $this->_job = $job;
        $this->_payload['award_count'] = $this->_job->award_count;

        if(isset($job->condition)){
            $condition = json_decode($job->condition);
            foreach($condition as $jobs=>$value){
//                    if(method_exists($this, $jobs)){
//                        $this->$jobs($value);
//                    }
                $this->check_count($value);
                return;
            }
        }
    }

    private function airdrop_reach_count_add($require_count){

        $reach_count = AirdropReachCount::where('user_id', $this->_payload['user_id'])
            ->where('group_id', $this->_payload['group_id'])
            ->where('job_id', $this->_job->id)
            ->first();

        if(!$reach_count || empty($reach_count->id) ){
            AirdropReachCount::create([
                'job_id'    => $this->_job->id,
                'group_id'  => $this->_payload['group_id'],
                'user_id'   => $this->_payload['user_id'],
                'reach_count'   => 1,
            ]);
            $reach_count = AirdropReachCount::where('user_id', $this->_payload['user_id'])
                ->where('group_id', $this->_payload['group_id'])
                ->where('job_id', $this->_job->id)
                ->first();
        } else {
            $reach_count = AirdropReachCount::find($reach_count->id);
            $reach_count->increment('reach_count',1);
        }

        if(($this->_job->repeat == 0)
            && !empty($reach_count)
            && isset($reach_count->reach_count)
            && ($require_count<$reach_count->reach_count)
        ){
            return false;
        }

        return $reach_count;

    }

    private function check_count($require_count, $push_type = 'airdrop'){

        $reach_count = $this->airdrop_reach_count_add($require_count);

        if($reach_count == false || empty($reach_count->reach_count)){
            return false;
        }

        $this->check_count_to_transaction($require_count, $reach_count->reach_count, $push_type);
    }

    //create topics count job
//    private function topics($require_count, $push_type = 'airdrop'){

//        $count = Thread::where('user_id', $this->_payload['user_id'])
//            ->where('group_id', $this->_payload['group_id'])
//            ->count();
//        $reach_count = $this->airdrop_reach_count_add($require_count);
//
//        if($reach_count === false){
//            return ;
//        }
//
//        $this->check_count_to_transaction($require_count, $reach_count->reach_count, $push_type);
//    }

    //topic recive reply count job
//    private function topic_receive_reply($require_count, $push_type = 'airdrop'){

//        $count = Thread::where('user_id', $this->_payload['user_id'])
//            ->where('group_id', $this->_payload['group_id'])
//            ->sum('posts_count');

//        $reach_count = $this->airdrop_reach_count_add($require_count);
//
//        if($reach_count == false || empty($reach_count->reach_count)){
//            return false;
//        }
//
//        $this->check_count_to_transaction($require_count, $reach_count->reach_count, $push_type );
//
//    }

    //topic recive reply count job
//    private function receive_likes($require_count, $push_type = 'airdrop'){

//        $count = \DB::table('posts')->join('likes', 'posts.id', '=', 'likes.post_id')
//            ->where('posts.group_id', $this->_payload['group_id'])
//            ->where('posts.user_id', $this->_payload['user_id'])->whereNull('likes.deleted_at')->count();

//        $reach_count = $this->airdrop_reach_count_add($require_count);
//
//        $this->check_count_to_transaction($require_count, $reach_count->reach_count, $push_type );
//
//    }

    private function check_count_to_transaction($require_count, $count, $push_type ){

        if($count >= $require_count && $count){

            if($this->_job->repeat == 0){
                if($require_count > $count ){
                    return ;
                }
            }

            if($this->_job->repeat != 0){
                if(( (int)$count % (int)$require_count) >0){
                    return ;
                }
            }

            $transaction = $this->ERC20Transaction($this->_job->award_count, Constants::CHANNEL_ID_AIRDROP);

            if($transaction || isset($transaction['transaction'])){
                //add log
//                \Log::error('airdrop transaction success');
                AirdropExecLog::create([
                    'job_id'            => $this->_job->id,
                    'group_id'          => $this->_job->group_id,
                    'transaction_id'    => $transaction['transaction']['transaction_id'],
                    'count'             =>$this->_job->award_count,
                    'user_id'           =>$this->_payload['user_id']
                ]);

                $this->sendNotifications($push_type);
            }

        }
    }


    public function sendNotifications($push_type = 'airdrop'){

        if($push_type == 'airdrop' ){
            $this->_payload['push_type'] = 'airdrop';

        } else if($push_type == 'onetime_airdrop'){
            $this->_payload['push_type'] = 'onetime_airdrop';
            $this->_payload['admin_name'] = $this->_payload['admin']->name;

        }
        $this->_payload['airdrop_name'] = $this->_job->rule_name;
        $this->_payload['count'] = TokenTransactionRepo::getRealBalance( $this->_job->award_count,$this->_payload['token']->erc20_token->decimal);
        $this->_payload['token_name'] = $this->_payload['token']->erc20_token->name;
        $this->_payload['logo'] = $this->_payload['token']->erc20_token->logo ?  $this->_payload['token']->erc20_token->logo : '';
        $this->_payload['recipients'] = [$this->_payload['user_id']];
        $this->_payload['group_name'] = $this->_payload['group']->name;
        $this->_payload['msg'] =  StringHelper::getNotificationTitle($this->_payload['push_type'],'','',$this->_payload,1);

        NotificationsRepository::addAirdropNotifications($this->_payload['group_id'],$this->_payload['user_id'],0, $this->_payload['msg']);

        Event::fire(new AirdropActivitiesEvent($this->_payload));

    }

    public static function verifyERC20TokenImportToken()
    {
        // find the import list
        $tokenlist = Erc20Token::where('allow_import', 1)->get()->all();
        $_tokenWalletRepo = new TokenWalletRepo();
        $_tokenTransactionRepo = new TokenTransactionRepo();

        foreach ($tokenlist as $token) {

            $verifyToken =
                \DB::table('erc20token_import_log','group_erc20token.group_id')
                    ->select('erc20token_import_log.id','erc20token_import_log.value','group_erc20token.group_id','group_erc20token.token_id')
                    ->join('group_erc20token','erc20token_import_log.to','=','group_erc20token.public_key')
                    ->where('erc20token_import_log.status',2)
                    ->where('erc20token_import_log.token_id',$token->id)
                    ->limit('100')
                    ->get();

            foreach ($verifyToken as $vtoken) {
                $wallet = $_tokenWalletRepo->getWallet(0, $vtoken->group_id, $token->id);
                if (!$wallet) {
                    $wallet = $_tokenWalletRepo->createWallet(0, $vtoken->group_id, $token->id);
                }
                if(empty($wallet->id)){
                    continue;
                }
                $import = $_tokenTransactionRepo->importTransaction($wallet->id, $token->id, $vtoken->value, 2, $token->decimal);
                if(!empty($import['increase'])){
                    ERC20TokenImportLog::where('id',$vtoken->id)->update(['status'=>4]);
                } else {
                    ERC20TokenImportLog::where('id',$vtoken->id)->update(['status'=>5]);
                }
            }
        }


    }

    public function sendPush()
    {

        $group = Group::find($this->_payload['group_id']);
        $url = env('EVERFORO_DOMAIN', 'https://sa.everforo.com') . '/g/' . $group['name']."/";

        $type = $this->_payload['push_type'];

        $data = [
            "type" => $type, // You can add your custom contents here
            "url" => $url,
            "group"=>$group,
            "click_action"=>"FLUTTER_NOTIFICATION_CLICK"
        ];
        $pushTokens = PushToken::wherein("user_id",$this->_payload['recipients'])->get();

        $extra_info['count'] = $this->_payload['count'] ;

        $extra_info['token_name'] = $this->_payload['token_name'] ;
        $extra_info['airdrop_name'] = $this->_job->rule_name ;

//        $extra_info['reason'] = $this->_payload['reason'] ;

//        if ($type == 'flag') {
//            $report = Report::where('post_id', $this->_payload['post_id'])
//                ->where('user_id', $this->_payload['user_id'])
//                ->first();
//
//            $extra_info = $report->reason;
//        }

        $notificationMessage =  StringHelper::getNotificationTitle($type,'','',$extra_info,0);

        if(!$notificationMessage){
            return false;
        }

        if(empty($pushTokens)){
            return false;
        }

        foreach ($pushTokens as $pushToken) {

//            $token = $pushToken->token;

            if(! $pushToken->arn){

                continue;
            }
            $endPointArn = array("EndpointArn" => $pushToken->arn);

            $SnSclient = new SnsClient([
                'region' => 'us-east-1',
                'version' => '2010-03-31',
                'credentials' => ['key' => env('AWS_KEY'), 'secret' => env('AWS_SECRET')],
            ]);
            $endpointAtt = $SnSclient->getEndpointAttributes($endPointArn);

            if ($endpointAtt != 'failed' && $endpointAtt['Attributes']['Enabled'] != 'false') {
                //  if ($pushToken->app_id == 2) {

                $fcmPayload = json_encode(
                    [
                        "notification" =>
                            [
                                "title" => "",
                                "body" => $notificationMessage,
                                "sound" => 'default'
                            ],
                        'priority'=> 'high',

                        "data" => $data // data key is used for sending content through notification.
                    ]
                );

                $message = json_encode(["default" => $notificationMessage, "GCM" => $fcmPayload]);

                $result = $SnSclient->publish([
                    'TargetArn' => $pushToken->arn,
                    'Message' => $message,
                    'MessageStructure' => 'json'
                ]);
                //  }
            }

        }
    }

    //one time airdrop start
    public function onetime_airdrop(){
        if(isset($this->_payload['airdrop_job_id'])){
            $job = AirdropJob::find($this->_payload['airdrop_job_id']);

            if(empty($job) || $job->exec_status != 1 ){
                return ;
            }

            tap($job)->update(['exec_status'=>2]);

            $this->_job = $job;
            $this->_payload['award_count'] = $this->_job->award_count;

            if(isset( $this->_job->condition) && $this->_job->condition ){
                $condition = @json_decode( $this->_job->condition);
                if(!empty($condition)){
                    $user_array = [];
                    foreach($condition as $jobs=>$value){
                        $jobs = 'find_users_from_' . $jobs;
                        if(method_exists($this, $jobs)){
                            $user_array[] = $this->$jobs($value);
                        }
                    }
                    $users_allow = array_shift($user_array);
                    if(count($user_array)>0){
                        foreach($user_array as $users){
                            $users_allow = array_intersect($users_allow,$users);
                        }
                    }

                    $users_allow = array_unique($users_allow);
                    foreach($users_allow as $users){
                        $this->_payload['user_id'] = $users;
                        $this->onetime_transaction();
                    }
                }
            } else {
                $this->onetime_find_all_users();
            }

            tap($job)->update(['exec_status'=>3]);

        } else {
            return;
        }
    }

    //create topics count job
    private function find_users_from_topics($require_count){
        $user_ary = [];
        $offset = 0;

        $exec_time = $this->_job->exec_time;
        $days = (int)$this->_job->days ? (int)$this->_job->days : 30;

        $start = date("Y-m-d H:i:s", strtotime('-'.$days.' day',strtotime($exec_time)));

        $count = 1000;

        while($count == 1000){
            $data = \DB::table('threads')
                ->select(\DB::raw('count(id) as rows'),'user_id')
                ->groupBy('user_id')
                ->where('group_id', $this->_payload['group_id'])
                ->whereBetween('created_at',[$start,$exec_time])
                ->offset($offset)
                ->limit($count)
                ->get()
                ->toArray();
            $offset = $offset + $count;

            foreach($data as $info){
                if($info->rows >= $require_count){
                    $user_ary[] = $info->user_id;
                }
            }

            $count = count($data);

        }
        return $user_ary;
    }

    //create topic_receive_reply count job
    private function find_users_from_topic_receive_reply($require_count){
        $user_ary = [];
        $offset = 0;

        $exec_time = $this->_job->exec_time;
        $days = (int)$this->_job->days ? (int)$this->_job->days : 30;

        $start = date("Y-m-d H:i:s", strtotime('-'.$days.' day',strtotime($exec_time)));

        $count = 1000;

        while($count == 1000){
            $data = \DB::table('threads')
                ->select(\DB::raw('sum(posts_count) as rows'),'user_id')
                ->groupBy('user_id')
                ->where('group_id', $this->_payload['group_id'])
                ->whereBetween('created_at',[$start,$exec_time])
                ->offset($offset)
                ->limit($count)
                ->get()
                ->toArray();

            $offset = $offset + $count;

            foreach($data as $info){
                if($info->rows >= $require_count){
                    $user_ary[] = $info->user_id;
                }
            }

            $count = count($data);

        }
        return $user_ary;
    }

    //create receive_likes count job
    private function find_users_from_receive_likes($require_count){
        $user_ary = [];
        $offset = 0;

        $exec_time = $this->_job->exec_time;
        $days = (int)$this->_job->days ? (int)$this->_job->days : 30;

        $start = date("Y-m-d H:i:s", strtotime('-'.$days.' day',strtotime($exec_time)));

        $count = 1000;

        while($count == 1000){
            $data = \DB::table('likes')
                ->select(\DB::raw('count(id) as rows'),'reciver_id')
                ->groupBy('reciver_id')
                ->where('group_id', $this->_payload['group_id'])
                ->whereNull('deleted_at')
                ->whereBetween('updated_at',[$start,$exec_time])
                ->offset($offset)
                ->limit($count)
                ->get()
                ->toArray();
            $offset = $offset + $count;

            foreach($data as $info){
                if($info->rows >= $require_count){
                    $user_ary[] = $info->reciver_id;
                }
            }

            $count = count($data);

        }
        return $user_ary;
    }

    //create topics count job
    private function onetime_find_all_users(){
        $count = 1000;

        $query =  GroupFollow::where('group_id',$this->_payload['group_id'])
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc');

        $offset = 0;

        while(true){
            $airdropList = $query->offset($offset)->limit($count)->get()->all();

            if(!empty($airdropList)){
                foreach($airdropList as $airdrop){
                    $this->_payload['user_id'] = $airdrop->user_id;
                    $this->onetime_transaction();
                }
                if(count($airdropList) != $count ){
                    break;
                }
            } else {
                break;
            }
            $offset++;
        }

    }

    //one time transaction
    private function onetime_transaction( ){

        $transaction = $this->ERC20Transaction($this->_job->award_count, 1);

        if($transaction || isset($transaction['transaction'])){
            //add log
            AirdropExecLog::create([
                'job_id'            => $this->_job->id,
                'group_id'            => $this->_job->group_id,
                'transaction_id'    => $transaction['transaction']['transaction_id'],
                'count'             =>$this->_job->award_count,
                'user_id'           =>$this->_payload['user_id']
            ]);

            $this->sendNotifications('onetime_airdrop');

        }
    }

    //one time airdrop end

}
