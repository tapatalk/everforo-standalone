<?php

namespace App\Repositories;

use App\Http\Controllers\Web\AirdropTokenController;

use Illuminate\Support\Facades\Redis;

use App\Models\AirdropJob;
use App\Models\AirdropExecLog;
use App\Jobs\ERC20TokenJob;
use DB, Queue, Log;

class AirdropRepo
{

    public function getAirdropList($group_id ,$offset = 0){
        if(empty($group_id)) {
            return false;
        }

        $page_length = 20;

        $query = AirdropJob::with('erc20token')
            ->where('group_id',$group_id)
            ->whereNull('deleted_at')
            ->whereNull('exec_time')        //without one time airdrop
            ->orderBy('id', 'desc');

        $airdrop = $query->offset($offset)->limit($page_length)->get()->toArray();

        if(!empty($airdrop)){
            foreach($airdrop as $key => $item){
                $airdrop[$key]['award_count'] =  TokenTransactionRepo::getRealBalance($item['award_count'], $item['erc20token']['decimal']);
                $airdrop[$key]['total_count'] =  TokenTransactionRepo::getRealBalance($this->getAirdropTotalCountByAirdropId($item['id']), $item['erc20token']['decimal']);
            };
        }

        return $airdrop;

    }


    public function getAirdropTotalCountByAirdropId($airdrop_id, $limit_days = 30){
        $limit_days = date("Y-m-d H:i:s", strtotime(' -'. $limit_days . 'day'));
        return AirdropExecLog::where('job_id',$airdrop_id)
            ->where('created_at','>', $limit_days)
            ->sum('count');
    }

    //$job accept 'topics','receive_likes','topic_receive_reply'
    public function airdrop_hook($job, $group_id, $user_id){

        $jobs = $this->getExecAirdropByGroupIdJobType($group_id, $job);

        $job_ary = [];
        foreach($jobs as $job){
            if($job && isset($job->id)){
                $job_ary[] = $job->id;
            }
        }

        \Log::error($job_ary);
        if(!empty($job_ary)){
            Queue::push(
                new ERC20TokenJob([
                    'group_id'          =>  $group_id,
                    'airdrop_job_id'    =>  json_encode($job_ary),
                    'user_id'           =>  $user_id,
                    'job_type'          =>  'airdrop'
                ])
            );
        }

    }

    public function getExecAirdropByGroupIdJobType($group_id, $job){
        if (!key_exists($job, AirdropTokenController::$_type)) {
            return false;
        }

        $type = AirdropTokenController::$_type[$job];

        return AirdropJob::where('group_id',$group_id)
            ->where('type',$type)
            ->where('exec_status',1)
            ->whereNull('deleted_at')
            ->whereNull('exec_time')
            ->get()->all();

    }

    public function getAirdropByGroupIdJobID($group_id, $type){

        return AirdropJob::where('group_id',$group_id)
            ->where('type',$type)
            ->whereNull('deleted_at')
            ->whereNull('exec_time')
            ->first();

    }

    
    public function switchAirdropJobStatus($airdrop_job_id, $status) {
        return AirdropJob::where('id', $airdrop_job_id)
                    ->update(['exec_status'=> $status]);
    }


    public function deleteAirdropJob($airdrop_job_id) {
        return AirdropJob::where('id', $airdrop_job_id)->delete();
    }

    public function fetchAllAirdrop($group_id){
        return AirdropJob::where('group_id',$group_id)
            ->whereNull('deleted_at')
            ->whereNull('exec_time')
            ->get()
            ->toArray();
    }

    public function getLastThirtyDaysAirdropSum($group_id){
        $count = AirdropExecLog::where('group_id', $group_id)
            ->where('created_at','>=',date('Y-m-d H:i:s', strtotime('-30 day')))
            ->sum('count');
        return $count;
    }

    public function getLastThirtyDaysAirdropHistory($group_id, $token){

        $redis = Redis::connection();
        $key = self::get_last_thirty_days_airdrop_history_key($group_id);

        $result = $redis->get($key);
        $result = false;
        if($result == false ){
            $all_airdrop = $this->fetchAllAirdrop($group_id);
            $airdropcount = 0;
            if(!empty($all_airdrop)){

                $airdropcount = count($all_airdrop);
            }
            $total_award = $this->getLastThirtyDaysAirdropSum($group_id);
            $total_award = TokenTransactionRepo::getRealBalance($total_award,$token->decimal);
            $result = json_encode([
                'count' => $airdropcount,
                'award' => $total_award,
            ]);
            $redis->setEx($key,1800, $result);
        }

        return json_decode($result, true);
    }

    public static function get_last_thirty_days_airdrop_history_key($group_id){
        return 'airdrop_h_30_'.$group_id;
    }

}