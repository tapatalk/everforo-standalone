<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Erc20Token;
use App\Utils\Transformer;

class SetGroup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    private $_transformer;

    public function __construct(Transformer $transformer)
    {
        

        $this->_transformer = $transformer;

    }


    public function handle(Request $request, Closure $next)
    {
        $group_info = Group::first();

        //erc20token 没有groupid 要通过group_erc20token 表查找到 group拥有哪些erc20_token 中的token 然后erc20_token 中取logo等信息

        if(!$group_info){
           return  $this->_transformer->fail(40001,'group not exist');

        } else {
            config(['app.group' => $group_info ]);
        }

        return $next($request);
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
