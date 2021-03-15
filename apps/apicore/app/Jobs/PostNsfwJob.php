<?php

namespace App\Jobs;
use App\Models\Group;
use App\Models\Post;
use Aws\Sns\SnsClient; 
use Aws\Exception\AwsException;
use App\Models\Thread;
use Exception;

class PostNsfwJob extends  Job 
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $_post_id;

    public function __construct($post_id)
    {   
        $this->_post_id = $post_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {      
      $this->delete();  
      self::scanPost($this->_post_id);
    }

    public function scanPost()
    {
        $post = Post::select('id','content','thread_id','nsfw')->where('id', $this->_post_id)->first();
        $thread = Thread::find($post->thread_id);
	   $content_elements = json_decode($post->content);
	   $is_nsfw = 0;
       $max_score = 0;
        foreach($content_elements as $element){
            $insert = $element->insert;
            if(isset($insert->image)){
                $path = self::downloadImage($insert->image);
		             if($path){
			             $score= self::nsfwCheck("./img/".$path);
                         if($score > $max_score){
                            $max_score = $score;
                         }
                 }
	           }
      
        }

        if($max_score > 0.7){
            $is_nsfw = 1;
            tap($post)->update(['nsfw' => 1,'nsfw_score' => $max_score]);
             
            tap($thread)->update(['nsfw' => 1]);
            return;
        } else {
            if($post->nsfw == -1){
                tap($post)->update(['nsfw' => 0]);
            }
            if($thread->nsfw == -1){
                tap($thread)->update(['nsfw' => 0]);
            }
        }
      
      
    }
    

    public function nsfwCheck($path){
        chdir("/home/ubuntu/open_nsfw");
	$cmd = "docker run --volume=$(pwd):/workspace caffe:cpu python ./classify_nsfw.py --model_def nsfw_model/deploy.prototxt --pretrained_model nsfw_model/resnet_50_1by2_nsfw.caffemodel ".$path; 

	$output =  shell_exec($cmd);
	$lines = explode("\n",$output);
	$score = 0;

		 preg_match("(0\.\d+)",$output,$matches);
		 if($matches[0] > 0){
			$score = $matches[0];
		 }
         return $score;
    }

    public function downloadImage($url){
        $localPath = env('OPEN_NSFW_PATH')."img/".md5($url);
        $fp = fopen($localPath,'w+');
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_exec($ch);
         
        if(curl_errno($ch)){
            throw new Exception(curl_error($ch));
        }
         
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);
         
        fclose($fp); 
        if($statusCode == 200){
            return md5($url);
        } else{
            return "";
        }
    }

}
