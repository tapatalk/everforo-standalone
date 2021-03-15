<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->options(
    '{any:.*}',
    [
        'middleware' => ['cors'],
        function () {
            return response(['status' => 'success']);
        }
    ]
);

$router->group(['prefix' => 'apiprivacy', 'middleware' => ['cors']], function () use ($router) {
    $router->get('group_list', ['uses' => 'GroupPrivacyController@getAllPrivacyGroupByUser']);
    $router->get('check_group_feature', ['uses' => 'GroupPrivacyController@checkPrivacyGroupStatus']);
    $router->get('get_feature_setting', ['uses' => 'GroupPrivacyController@getGroupPrivacySetting']);
    $router->get('set_feature_setting', ['uses' => 'GroupPrivacyController@setGroupPrivacySetting']);
    $router->get('join_request', ['uses' => 'GroupPrivacyController@joinRequest']);
    $router->get('ignore_request', ['uses' => 'GroupPrivacyController@ignoreRequest']);
    $router->get('approve_request', ['uses' => 'GroupPrivacyController@approveRequest']);
});

$router->get('{path:.*}', function () {
    return response(['status' => 'undefined route']);
});
