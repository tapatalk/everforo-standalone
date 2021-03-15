<?php

namespace App\Jobs;
use App\Models\Group;
use App\Models\PushToken;
use Aws\Sns\SnsClient; 
use Aws\Exception\AwsException;

class IPFSJob extends  Job 
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $hash;

    public function __construct($hash)
    {   
        $this->hash = $hash;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {      
      $this->delete();  
        self::pingIPFS();
      

      
    }

    public function pingIPFS()
    {
        $url1 = "https://ipfs.smartsignature.io/ipfs/".$this->hash;
        $this->curl($url1);           
        $url2 = "https://ipfs.infura.io/ipfs/".$this->hash;
         $this->curl($url2); 
         $url3 = "https://ipfs.io/ipfs/".$this->hash;
          $this->curl($url3); 
    }

     private function curl($url, $path = "") {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);


       
        $output = curl_exec($ch);

        curl_close($ch);
        

        return $output;
    
    }
}
