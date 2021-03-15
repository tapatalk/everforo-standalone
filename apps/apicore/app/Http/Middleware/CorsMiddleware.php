<?php

namespace App\Http\Middleware;

use App\Repositories\UserBehaviorRepository;
use Closure;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $origin = $request->header('origin');
        // for now, production site share the same API server of stage
        if ($origin === 'https://sa.everforo.com'){
            $allow_origin = 'https://sa.everforo.com';
        } elseif ($origin === 'https://stage.everforo.com'){
            $allow_origin = 'https://stage.everforo.com';
        } elseif (env('APP_ENV') === 'local' || env('APP_ENV') === 'dev') {
            $allow_origin = $origin;
        } else {
            $allow_origin = env('EVERFORO_DOMAIN');
        }

        $headers = [
            'Access-Control-Allow-Origin' => "*",
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS',
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization, X-Requested-With',
            'P3P' => 'CP="CAO PSA OUR"',
            // 'Access-Control-Max-Age'           => '86400',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        //user active
        $user = $request->user();
        if (isset($user->id)) {
            UserBehaviorRepository::updateActive($user->id);
        }

        return $response;
    }
}
