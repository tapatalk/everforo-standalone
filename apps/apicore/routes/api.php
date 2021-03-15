<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


//$router->options(
//    '/{any:.*}',
//    [
//        'middleware' => ['cors'],
//        function (){
//            return response(['status' => 'success']);
//        }
//    ]
//);

$router->options(
    '{any:.*}',
    [
        'middleware' => ['cors'],
        function () {
            return response(['status' => 'success']);
        }
    ]
);

$router->group(['middleware' => ['setGroup', 'cors', 'laravel.jwt']], function () use ($router) {
    $router->get('me', ['uses' => 'Auth\LoginController@getMe']);
    $router->post('logout', ['uses' => 'Auth\LoginController@logout']);
    $router->patch('settings/profile', ['uses' => 'Settings\ProfileController@update']);
    $router->patch('settings/password', ['uses' => 'Settings\PasswordController@update']);
});

// $router->group(['middleware' => ['cors']], function () use ($router) {
//     $router->post('refresh', ['uses' => 'Auth\LoginController@refreshToken']);
// });


//$router->group(['prefix' => 'api'], function () use ($router) {
//    $router->get('login', ['uses' => 'Auth\LoginController@login']);
//    $router->post('test', ['uses' => 'Auth\LoginController@test']);
//
//});


$router->group(['prefix' => 'password'], function () use ($router) {
    $router->post('email', ['uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail']);
    $router->post('reset', ['uses' => 'Auth\ResetPasswordController@reset']);
});


