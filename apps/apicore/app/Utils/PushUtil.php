<?php

namespace App\Utils;
use Aws\Sns\SnsClient; 


/**
 * Search Transformer
 *
 * @author Hu Yao <yao@tapatalk.com>
 */
class PushUtil{

   
    public static function generatePushByPayload($payload,$type){
        $group = Group::find($this->_payload['group_id']);
        $url = env('EVERFORO_DOMAIN', 'https://sa.everforo.com') . '/g/' . $group['name']."/thread/".$this->_payload['thread_id'];
   
        $data = [
            "type" => "sub", // You can add your custom contents here 
            "url" => $url,
            "group"=>$group,
            "click_action"=>"FLUTTER_NOTIFICATION_CLICK"
        ];
        $pushTokens = PushToken::wherein("user_id",$this->_payload['recipients'])->get();
      

        $notificationTitle =  StringHelper::getNotificationTitle('reply',$this->_payload['username'],$this->_payload['thread_title'],"",0);
        $notificationMessage = $this->_payload['thread_content'];
   
        foreach ($pushTokens as $pushToken) {
            
            $token = $pushToken->token;
            if(! $pushToken->arn){
        
                continue;
            } 
            SNSPush::send($pushToken->arn,$notificationTitle,$notificationMessage,$data);
          
        }
    }


    /**
     * Format success response
     * @param   array   $data
     * @param   string  $description
     * @return  array
     */
    public static function send($arn,$title,$message,$data)
    {
        $endPointArn = array("EndpointArn" => $arn);

        $SnSclient = new SnsClient([
            'region' => 'us-east-1',
            'version' => '2010-03-31',
            'credentials' => ['key' => env('AWS_KEY'), 'secret' => env('AWS_SECRET')],
        ]);
        $endpointAtt = $SnSclient->getEndpointAttributes($endPointArn);

        if ($endpointAtt != 'failed' && $endpointAtt['Attributes']['Enabled'] != 'false') {
            $fcmPayload = json_encode(
                [
                    "notification" =>
                        [
                            "title" => $title,
                            "body" => $message,
                            "sound" => 'default'
                        ],
                    'priority'=> 'high',

                    "data" => $data // data key is used for sending content through notification.
                ]
            );
            $message_data = json_encode(["default" => $message, "GCM" => $fcmPayload]);
    
            $result = $SnSclient->publish([
                'TargetArn' => $arn,
                'Message' => $message_data,
                'MessageStructure' => 'json'
            ]);

        }
    }


  

}