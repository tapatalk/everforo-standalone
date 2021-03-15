<?php
/*
	This code is licensed under the MIT license.
	See the LICENSE file for more information.
*/

namespace App\Repositories\IPFS;

class IPFS {
	private $gatewayIP;
	private $gatewayPort;
	private $gatewayApiPort;

	function __construct($ip = "localhost", $port = "8080", $apiPort = "5001") {
		$this->gatewayIP      = $ip;
		$this->gatewayPort    = $port;
		$this->gatewayApiPort = $apiPort;
	}

	public function cat ($hash) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayPort;
		return $this->curl("http://$ip:$port/ipfs/$hash"); 

	}

	public function add ($content) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$req = $this->curl("http://$ip:$port/api/v0/add?stream-channels=true", $content);
		$req = json_decode($req, TRUE);

		return $req['Hash'];
	}

	public function addFile ($path) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$req = $this->curl1("http://$ip:$port/api/v0/add?stream-channels=true", $path);
		$req = json_decode($req, TRUE);

		return $req['Hash'];
	}

	public function ls ($hash) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$response = $this->curl("http://$ip:$port/api/v0/ls/$hash");

		$data = json_decode($response, TRUE);

		return $data['Objects'][0]['Links'];
	}

	public function size ($hash) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$response = $this->curl("http://$ip:$port/api/v0/object/stat/$hash");
		$data = json_decode($response, TRUE);

		return $data['CumulativeSize'];
	}

	public function pinAdd ($hash) {
		
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$response = $this->curl("http://$ip:$port/api/v0/pin/add/$hash");
		$data = json_decode($response, TRUE);

		return $data;
	}


	public function publish ($hash) {
		print "+++++++++";
		$cmd = 'ipfs name publish '.$hash.'  --allow-offline';
		print $cmd;
		$response = shell_exec($cmd);
		print "111";
		print $response; exit;
	}




    public function version () {
        
        $ip = $this->gatewayIP;
        $port = $this->gatewayApiPort;
        $response = $this->curl("http://$ip:$port/api/v0/version");
        $data = json_decode($response, TRUE);
        return $data["Version"];
    }

	private function curl ($url, $data = "") {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		 
		if ($data != "") {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data; boundary=a831rwxi1a3gzaorw1w2z49dlsor')); 
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "--a831rwxi1a3gzaorw1w2z49dlsor\r\nContent-Type: application/octet-stream\r\nContent-Disposition: file; \r\n\r\n" . $data);
		}

		$output = curl_exec($ch);
		if ($output == FALSE) {
			//todo: when ipfs doesn't answer
		}		 
		curl_close($ch);

		return $output;
	}


	private function curlPost ($url, $data = "") {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		 
	
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$output = curl_exec($ch);
		if ($output == FALSE) {
			//todo: when ipfs doesn't answer
		}		 
		curl_close($ch);

		return $output;
	}

	private function curl1 ($url, $path = "") {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$fp = fopen($path, 'r');

		if ($path != "") {
			curl_setopt($ch, CURLOPT_POST, 1);
			 $cFile = curl_file_create($path,'application/octet-stream', basename($path));
			$data = array(
			    'file' => $cFile
			);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);


		}
		$output = curl_exec($ch);

		if ($output == FALSE) {
			//todo: when ipfs doesn't answer
		}		 
		curl_close($ch);
 		

		return $output;
	}
}


