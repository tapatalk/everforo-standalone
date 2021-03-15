<?php

namespace App\Repositories\IPFS;
use App\Repositories\IPFS\IPFS;

class IPFSRepository {


    public function submitToIPFS($data,$type='post'){
      $ipfs_data = [
           
            'source' => 'everforo.com',
            'type'   =>  $type,
            'protocol_version' => '0.9',
            'data' => $data,
            'timestamp' => time()
      ];
      
      $data_json  = json_encode($ipfs_data);

      $ipfs = new IPFS("ipfs", "8080", "5001"); 


      $hash = $ipfs->add($data_json);

      return $hash;
    }

    public function submitFileToIPFS($path){

      $ipfs = new IPFS("ipfs", "8080", "5001"); 

      $hash = $ipfs->addFile($path);

      return $hash;
    }

    public function publish($path){

      $ipfs = new IPFS("ipfs", "8080", "5001"); 
      print "----------------".$path;
      $hash = $ipfs->publish($path);

      return $hash;
    }

}