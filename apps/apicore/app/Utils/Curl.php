<?php

namespace App\Utils;
use Aws\Sns\SnsClient; 


/**
 * Curl
 *
 */
class Curl{

    public function __construct()
    {
        # code...
    }

    public function post($url, $data) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // execute!
        $response = curl_exec($ch);

        // close the connection, release resources used
        curl_close($ch);

        return $response;
    }
}
