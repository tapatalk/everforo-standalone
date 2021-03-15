<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades();
// If you would like to use the exists or unique validation rules, 
// you should uncomment the $app->withEloquent() method call in your bootstrap/app.php file.
$app->withEloquent();

$app->configure('app');
$app->configure('auth');
$app->configure('database');
$app->configure('services');
$app->configure('mail');
$app->configure('queue');

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton('mailer', function ($app) {
    return $app->loadComponent('mail', 'Illuminate\Mail\MailServiceProvider', 'mailer');
});

$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);



$app->withFacades(
    class_alias('Intervention\Image\Facades\Image', 'Image')
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
   App\Http\Middleware\SetLocale::class
]);


$app->routeMiddleware([
//    'auth' => App\Http\Middleware\Authenticate::class,
    'setGroup' => App\Http\Middleware\SetGroup::class,
    'cors' => App\Http\Middleware\CorsMiddleware::class,
    'keyVerify' => App\Http\Middleware\KeyVerify::class,
]);


/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\BroadcastServiceProvider::class);
//$app->register(App\Providers\JWTUserProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Unisharp\JWT\JWTServiceProvider::class);
$app->register(Vluzrmos\Tinker\TinkerServiceProvider::class);
$app->register(App\Helpers\Form\FormRequestServiceProvider::class);
$app->register(Laravel\Socialite\SocialiteServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Illuminate\Auth\Passwords\PasswordResetServiceProvider::class);
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Appzcoder\LumenRoutesList\RoutesCommandServiceProvider::class);

$app->register(Intervention\Image\ImageServiceProviderLumen::class);

$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
// bind custom user provider with interface, for Tymon\JWTAuth\JWTGuard
// which used in app/Http/Controllers/API/Auth/LoginController.php
$app->bind('Illuminate\Contracts\Auth\UserProvider', App\Providers\JWTUserProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/
$app->router->group([
    'prefix' => 'api',
    'namespace' => 'App\Http\Controllers\API',
], function ($router) {
    require __DIR__.'/../routes/api.php';
});

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
