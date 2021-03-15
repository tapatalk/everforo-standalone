<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Utils\Transformer;


use App\Repositories\UserRepo;

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;

class KeyVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    private $_transformer;
    private $_userRepo;
    public function __construct(Transformer $transformer,UserRepo $userRepo)
    {
        

        $this->_transformer = $transformer;
        $this->_userRepo = $userRepo;
    }


    public function handle(Request $request, Closure $next)
    {
       $user_id = $request->uid;
       $user = $this->_userRepo->getUserObject($user_id);


// \Log::info('KeyVerify middleware', [$user_id]);
        $bitcoinECDSA = new BitcoinECDSA();
        $public_key = $user->public_key;

        $address = $bitcoinECDSA->getAddress($public_key);
     

        $signature = $request->signature;
// \Log::info('KeyVerify middleware', [$signature]);
        if ($bitcoinECDSA->checkSignatureForMessage($address, $signature, $user_id)) {

// \Log::info('KeyVerify middleware', [1]);
            $request->merge(['user' => $user ]);

            //add this
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
         
          return $next($request);
            
            #       print "pass";
        } else {
// \Log::info('KeyVerify middleware', [2]);
               return response('Unauthorized.', 401);
        }

       
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function parseGroup($request)
    {
        return 1;
    }
}
