<?php

namespace App\Jobs;
use App\Models\Group;
use App\Models\PushToken;
use App\Models\GroupBanUsers;
use Aws\Sns\SnsClient; 
use Aws\Exception\AwsException;
use Exception;
use App\Utils\StringHelper;
use App\Models\Report;
class ReplyPushJob extends  Job 
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $_payload;

    public function __construct($payload)
    {
        $this->_payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $this->delete();
        self::sendPush();

      
    }

    public function sendPush()
    {
        $group = Group::find($this->_payload['group_id']);
        $url = env('EVERFORO_DOMAIN', 'https://sa.everforo.com') . '/g/' . $group['name']."/thread/".$this->_payload['thread_id'];

        $type = $this->_payload['push_type'];
   
        $data = [
            "type" => $type, // You can add your custom contents here 
            "url" => $url,
            "group"=>$group,
            "click_action"=>"FLUTTER_NOTIFICATION_CLICK"
        ];
        $pushTokens = PushToken::wherein("user_id",$this->_payload['recipients'])->get();


        $extra_info = "";

        if ($type == 'flag') {
            $report = Report::where('post_id', $this->_payload['post_id'])
                    ->where('user_id', $this->_payload['user_id'])
                    ->first();

            $extra_info = $report->reason;
        }


        
        $notificationMessage =  StringHelper::getNotificationTitle($type,$this->_payload['username'],$this->_payload['thread_title'],$extra_info,0);
        if($type == 'reply'){
             $notificationMessage = $notificationMessage.": ".$this->_payload['thread_content'];
         }
        foreach ($pushTokens as $pushToken) {
            $token = $pushToken->token;
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
}
