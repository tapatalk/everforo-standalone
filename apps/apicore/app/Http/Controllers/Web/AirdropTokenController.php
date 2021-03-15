<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Requests\Web\CreateERC20TokenRequest;
use App\Http\Requests\Web\CreateAirdropRequest;
use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Models\Erc20Token;
use App\Models\TokenWallet;
use App\Models\AirdropJob;
use App\Utils\Constants;
use App\Repositories\Erc20TokenRepo;
use App\Repositories\AirdropRepo;
use App\Repositories\TokenTransactionRepo;
use App\Jobs\ERC20TokenJob;
use DB, Queue;


class AirdropTokenController extends Controller
{
    private $_transformer;
    private $_group;
    private $_erc20tokenrepo;
    private $_token;
    private $_airdropRepo;

    public static $_type = [
        'topics'=>1,'receive_likes'=>2,'topic_receive_reply'=>4
    ];
    public static $revert_type = [
        1 =>'topics',2 =>'receive_likes',4=>'topic_receive_reply'
    ];


    /**
     * ERC20TokenController constructor.
     * @param
     */
    public function __construct(Transformer $transformer, Erc20TokenRepo $Erc20TokenRepo, AirdropRepo $AirdropRepo)
    {
        $this->_transformer = $transformer;
        $this->_erc20tokenrepo = $Erc20TokenRepo;
        $this->_group = config('app.group');
        $this->_token = $Erc20TokenRepo->getGroupErc20Token($this->_group->id);
        $this->_airdropRepo = $AirdropRepo;
    }

    /**
     *
     * @param getAirdrop
     * @return \Illuminate\Http\Response
     */
    public function getAirdrop($airdrop_id)
    {
        if(!$airdrop_id){
            return $this->_transformer->fail(40003, 'not exist');
        }

        $airdrop  = AirdropJob::find($airdrop_id);

        $airdrop->award_count =  TokenTransactionRepo::getRealBalance($airdrop->award_count,$this->_token->decimal);

        if(!$airdrop_id){
            return $this->_transformer->fail(40003, 'not exist');
        }

        $airdrop['type'] = self::$revert_type[$airdrop['type']];

        return $this->_transformer->success(['airdrop'=>$airdrop]);
    }

    /**
     *
     * @param createAirdrop
     * @return \Illuminate\Http\Response
     */
    public function createAirdrop(CreateAirdropRequest $request)
    {

        $rule_name = $request->input('rule_name');
        $award_count = $request->input('award_count');
        $require_count = $request->input('require_count');
        $repeat = $request->input('repeat');
        $type = $request->input('type');

        $error = Erc20TokenRepo::check_decimal_part_error($award_count, $this->_token->erc20_token->decimal);

        if($error !== false){
            return $this->_transformer->fail(40016, $error);
        }

        if (!key_exists($type, self::$_type)) {
            return $this->_transformer->fail(40009, 'select one type');
        }

        $condition = [$type => $require_count];

        if( !$this->_token ){
            return $this->_transformer->fail(40020, 'create token first');
        }

        $table_count = TokenTransactionRepo::getTableBalance($award_count,$this->_token->erc20_token->decimal);

//        $airdrop = $this->_airdropRepo->getAirdropByGroupIdJobID($this->_group->id,self::$_type[$type]);
//
//        if($airdrop){
//            return $this->_transformer->fail(40018, 'exist same type rule');
//        }

        $airdrop = AirdropJob::create([
            'group_id' => $this->_group->id,
            'token_id' => $this->_token->token_id,
            'type' => self::$_type[$type],
            'award_count' => $table_count,
            'rule_name' => $rule_name,
            'repeat' => $repeat,
            'condition' => json_encode($condition),
            'exec_status' => 1,
        ]);

        $airdrop['total_count'] = 0;
        $airdrop['award_count'] = $award_count;

        return $this->_transformer->success(['airdrop'=>$airdrop]);
    }



    /**
     * delete old one and create new one
     * @param updateAirdrop
     * @return \Illuminate\Http\Response
     */
    public function editAirdrop(Request $request)
    {
        $rule_name = $request->input('rule_name');
        $award_count = $request->input('award_count');
        $require_count = $request->input('require_count');
        $repeat = $request->input('repeat');
        $type = $request->input('type');
        $airdrop_id = $request->input('airdrop_id');

        $error = Erc20TokenRepo::check_decimal_part_error($award_count, $this->_token->erc20_token->decimal);

        if($error !== false){
            return $this->_transformer->fail(40016, $error);
        }

        $airdrop = AirdropJob::find($airdrop_id);

        if(!$airdrop && isset($airdrop->id) ){
            return $this->_transformer->fail(40012, 'not exist');
        }

        if (!key_exists($type, self::$_type)) {
            return $this->_transformer->fail(40011, 'select one type');
        }

        $condition = [$type => $require_count];

        $table_count = TokenTransactionRepo::getTableBalance($award_count,$this->_token->erc20_token->decimal);

        $new_airdrop = AirdropJob::create([
            'group_id' => $this->_group->id,
            'token_id' => $this->_token->token_id,
            'type' => self::$_type[$type],
            'award_count' => $table_count,
            'rule_name' => $rule_name,
            'repeat' => $repeat,
            'condition' => json_encode($condition),
            'exec_status' => 1,
        ]);

        tap($airdrop)->delete();

        if(isset($new_airdrop->award_count)){
            $new_airdrop->award_count = $award_count;
        }

        return $this->_transformer->success(['airdrop'=>$new_airdrop]);
    }

    /**
     *
     * @param CreateERC20TokenRequest
     * @return \Illuminate\Http\Response
     */
    public function getAirdropList(Request $request)
    {

        $offest = $request->input('page',0);

        $airdrop = $this->_airdropRepo->getAirdropList($this->_group->id, $offest);

        $counts = $this->_airdropRepo->getLastThirtyDaysAirdropHistory($this->_group->id, $this->_token->erc20_token);

        return $this->_transformer->success(array_merge(['airdrop'=>$airdrop], $counts));

    }


    public function pauseAirdropJob(Request $request)
    {
        $airdrop_job_id = $request->input('airdrop_job_id', 0);

        $result = $this->_airdropRepo->switchAirdropJobStatus($airdrop_job_id, 0);

        return $this->_transformer->success(['status'=> 0]);
    }


    public function resumeAirdropJob(Request $request)
    {
        $airdrop_job_id = $request->input('airdrop_job_id', 0);

        $result = $this->_airdropRepo->switchAirdropJobStatus($airdrop_job_id, 1);

        return $this->_transformer->success(['status'=> 1]);
    }

    public function deleteAirdropJob(Request $request)
    {
        $airdrop_job_id = $request->input('airdrop_job_id', 0);

        $result = $this->_airdropRepo->deleteAirdropJob($airdrop_job_id);

        return $this->_transformer->success(['status'=> 1]);
    }

    /**
     *
     * @param createAirdrop
     * @return \Illuminate\Http\Response
     */
    public function createOnetimeAirdrop(Request $request)
    {
        $award_count = $request->input('award_count');
        $require_count = $request->input('require_count',0);
        $type = $request->input('type',0);
        $days = $request->input('days',0);
        $exec_time = date("Y-m-d H:i:s");

        $error = Erc20TokenRepo::check_decimal_part_error($award_count, $this->_token->erc20_token->decimal);

        if($error !== false){
            return $this->_transformer->fail(40016, $error);
        }

        if($type){
            if (!key_exists($type, self::$_type)) {
                return $this->_transformer->fail(40009, 'select one type');
            }
            $condition = [$type => $require_count];
        }

        if( !$this->_token ){
            return $this->_transformer->fail(40010, 'create token first');
        }

        $table_count = TokenTransactionRepo::getTableBalance($award_count,$this->_token->erc20_token->decimal);

        $create_data = [
            'group_id' => $this->_group->id,
            'token_id' => $this->_token->token_id,
            'award_count' => $table_count,
            'exec_status' => 1,
            'repeat' => 0,
            'days' => $days,
            'exec_time' => $exec_time,
            'rule_name' => 'one time airdrop',
        ];

        if($type){
            $create_data['type'] = self::$_type[$type];
            $create_data['condition'] = json_encode($condition);
        } else {
            $create_data['type'] = 0;
        }

        $airdrop = AirdropJob::create($create_data);

//        $airdrop['total_count'] = 0;
//        $airdrop['award_count'] = $award_count;

        Queue::push(
            new ERC20TokenJob([
                'group_id'          =>  $this->_group->id,
                'airdrop_job_id'    =>  $airdrop->id,
                'job_type'          =>  'onetime_airdrop'
            ])
        );

        return $this->_transformer->success(['airdrop'=>$airdrop]);
    }

}