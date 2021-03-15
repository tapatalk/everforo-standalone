<?php

namespace App\Utils;

use Request;

/**
 * Search Transformer
 *
 * @author Hu Yao <yao@tapatalk.com>
 */
class Transformer{

   
    /**
     * Format success response
     * @param   array   $data
     * @param   string  $description
     * @return  array
     */
    public function success($data = [], $description = '')
    {
        if (empty($data)) $data = new \stdClass;  // For IOS, needs to return {} instead of null
        

        array_walk_recursive($data,function(&$item){if($item == null){$item=strval($item);}});

        return [
            'status'      => (bool) true,
            'code'        => (string) 20000,
            'description' => (string) $description,
            'server'      => 'rest',
            'data'        => $data
        ];
    }


    public function noPermission($data = []){
        return self::fail(403,'You have no permission to perform this action.');
    }

    /**
     * Format failure response
     * @param   int(5)  $error_code
     * @param   string  $description  Default: 'Invalid Request'
     * @param   array   $data
     * @return  array
     */
    public function fail($error_code, $description = 'Invalid Request', $data = [])
    {
        if (empty($data)) $data = new \stdClass;  // For IOS, needs to return {} instead of null

        return [
            'status'      => (bool) false,
            'code'        => (string) $error_code,
            'description' => (string) $description,
            'server'      => 'master',
            'data'        => $data
        ];
    }

}