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
$router->get('health', function () {
    return 'OK';
});

$router->group(['prefix' => 'oauth'], function () use ($router) {
    $router->get('{driver}', ['as' => 'oauth', 'uses' => 'Auth\OAuthController@redirectToProvider']);
    $router->get('{driver}/callback', ['as' => 'oauth.callback', 'uses' => 'Auth\OAuthController@handleProviderCallback']);
});

$router->post('custom/broadcast/auth', ['uses' => 'Web\UserController@broadcastAuth']); // echo server auth endpoint

$router->options(
    '{any:.*}',
    [
        'middleware' => ['cors'],
        function () {
            return response(['status' => 'success']);
        }
    ]
);

$router->get('withdraw/list', ['uses' => 'Web\WithdrawController@getWithdrawRequestList']);
$router->post('withdraw/transafer', ['uses' => 'Web\WithdrawController@withdrawTransafer']);


// create/update group, update profile on web
$router->group(['prefix' => 'api', 'middleware' => ['cors','laravel.jwt']], function () use ($router) {
    $router->post('user/update_info', ['uses' => 'Web\UserController@updateInfo']);
    $router->post('upload_group_pic', ['uses' => 'Web\FileController@uploadGroupPic']); 
    $router->post('group/create', ['uses' => 'Web\GroupController@createGroup']);
        //  $router->post('feed/filter', ['uses' => 'Web\GroupController@removeThreadInFeed']);
            //  $router->post('feed/filter_group', ['uses' => 'Web\GroupController@removeGroupInFeed']);
    $router->post('group/update', ['uses' => 'Web\GroupController@updateGroupInfo']);
    $router->post('group/delete', ['uses' => 'Web\GroupController@deleteGroup']);
    $router->post('notifications/list', ['uses' => 'Web\NotificationController@getNotifications']);
    $router->post('notifications/read', ['uses' => 'Web\NotificationController@readNotifications']);
    $router->post('block/user', ['uses' => 'Web\BlockUsersController@blockUser']);
    $router->post('unblock/user', ['uses' => 'Web\BlockUsersController@unblockUser']);

    $router->get('erc20token/getmyassets', ['uses' => 'Web\ERC20TokenController@getMyAssets']);

    $router->post('paypal/gettoken', ['uses' => 'Web\PaypalController@getTokenByOrder']); // pass order id
    $router->post('paypal/confirm', ['uses' => 'Web\PaypalController@payPalChargeExecute']);

    $router->post('withdraw/create', ['uses' => 'Web\WithdrawController@createWithdrawRequest']);
    $router->post('withdraw/getdetail', ['uses' => 'Web\WithdrawController@userGetWithdrawInfo']);

    $router->post('order/cancel', ['uses' => 'Web\OrderController@cancelOrder']);

    $router->post('dark_mode', ['uses' => 'Web\UserController@switchDarkMode']);

    $router->post('switch_language', ['uses' => 'Web\UserController@switchLanguage']);
});
// create/update group, update profile on app
$router->group(['prefix' => 'api/app', 'middleware' => ['cors','keyVerify']], function () use ($router) {
    $router->post('user/update_info', ['uses' => 'Web\UserController@updateInfo']);
    $router->post('upload_group_pic', ['uses' => 'Web\FileController@uploadGroupPic']);
    $router->post('group/create', ['uses' => 'Web\GroupController@createGroup']);
    $router->post('group/update', ['uses' => 'Web\GroupController@updateGroupInfo']);
    // todo delete in the future
    $router->get('groups/{page}/{my_group}', ['uses' => 'Web\GroupController@feed']);
});

$router->group(['prefix' => 'api/app','middleware' => ['cors', 'keyVerify']], function () use ($router) {
    $router->post('me', ['uses' => 'API\Auth\LoginController@getMe']);
    $router->post('logout', ['uses' => 'Auth\LoginController@logout']);
    $router->patch('settings/profile', ['uses' => 'Settings\ProfileController@update']);
    $router->patch('settings/password', ['uses' => 'Settings\PasswordController@update']);
    $router->post('register_token', ['uses' => 'Web\PushController@registerToken']);

    $router->post('email_restore', ['uses' => 'Web\PushController@registerToken']);

    $router->post('get_jwt_token', ['uses' => 'API\Auth\LoginController@getToken']);  

    $router->post('notifications/list', ['uses' => 'Web\NotificationController@getNotifications']);
    $router->get('notifications/list/{page}{/per_page}', ['uses' => 'Web\NotificationController@getNotifications']);
    $router->post('notifications/read', ['uses' => 'Web\NotificationController@readNotifications']);

});

$router->group(['prefix' => 'api', 'middleware' => ['setGroup','cors']], function () use ($router) {
    $router->get('qr_code', ['uses' => 'API\Auth\LoginController@QRCode']);
    $router->post('test_push', ['uses' => 'Web\PushController@testPush']);
    $router->post('test_queue', ['uses' => 'Web\PushController@testQueue']);
    $router->post('testIPFS', ['uses' => 'Web\PostController@testIPFS']);
    $router->post('app_login', ['uses' => 'API\Auth\LoginController@appLogin']);
    
    $router->post('qr_code_scan', ['uses' => 'API\Auth\LoginController@QRCodeScan']);
    $router->get('qr_code_scan_local', ['uses' => 'API\Auth\LoginController@QRCodeScanLocal']);
    $router->post('login', ['uses' => 'API\Auth\LoginController@login']);
    $router->post('check_email', ['uses' => 'API\Auth\LoginController@checkEmail']);
    $router->post('register_admin', ['uses' => 'API\Auth\RegisterController@registerAdmin']);
    // new users upload avatar
    $router->post('avatar/upload', ['uses' => 'Web\UserController@uploadAvatar']);
    // after user got an avatar url, do register
    $router->post('register', ['uses' => 'API\Auth\RegisterController@store']);
    $router->post('register_silence', ['uses' => 'API\Auth\RegisterController@silenceRegister']);
    $router->post('register_by_email', ['uses' => 'API\Auth\RegisterController@storeByEmail']);

    $router->post('send_register_email', ['uses' => 'API\Auth\RegisterController@sendRegisterEmail']);
    $router->post('password/reset_email', ['uses' => 'API\Auth\RegisterController@sendPasswordResetEmail']);
    $router->post('password/reset', ['uses' => 'API\Auth\RegisterController@passwordReset']);

    $router->post('email/confirm', ['uses' => 'API\Auth\RegisterController@emailConfirm']);
    $router->get('email/confirm', ['uses' => 'API\Auth\RegisterController@emailConfirm']);

    $router->get('profile/{profile_id}[/{group_name}]', ['uses' => 'Web\UserController@getProfile']);
    $router->post('profile/list', ['uses' => 'Web\UserController@getProfileList']);
    $router->get('profile_posts/{profile_id}/{filter}/{page}', ['uses' => 'Web\PostController@userPosts']);

    $router->get('groups/{page}/{my_group}', ['uses' => 'Web\GroupController@feed']);
    $router->get('send_approve_email', ['uses' => 'Web\GroupMemberController@sendApproveEmail']);

//    create_token
    $router->get('get_price/{product_name}', ['uses' => 'Web\ProductController@getPrice']);

    // move to line 45 todo
    $router->post('paypal/webhook', ['uses' => 'Web\PaypalController@webhook']);

    // $router->post('link_preview', ['uses' => 'Web\PostController@linkPreview']);
});


$router->group(['prefix' => 'test', 'middleware' => ['cors']], function () use ($router) {
    $router->get('test_nsfw', ['uses' => 'Web\TestController@testPostNsfw']);
    $router->post('test_thread_ipfs', ['uses' => 'Web\PostController@testThreadIPFS']);
    $router->post('test_group_ipfs', ['uses' => 'Web\PostController@testGroupIPFS']);

});

$router->group([/*'prefix' => 'groups/{group_name}',*/ 'middleware' => ['setGroup', 'cors','laravel.jwt']], function ($router) {
    $router->post('api/submit_thread', ['uses' => 'Web\PostController@submitTopic']);
    $router->post('api/thread/feed', ['uses' => 'Web\GroupController@removeThreadInFeed']);

    $router->post('api/submit_post', ['uses' => 'Web\PostController@submitPost']);
    $router->post('api/edit_post', ['uses' => 'Web\PostController@edit']);
    $router->post('api/delete_post', ['uses' => 'Web\PostController@delete']);
    $router->post('api/un_delete_post', ['uses' => 'Web\PostController@undelete']);
    $router->post('api/like_post', ['uses' => 'Web\LikeController@like']);
    $router->post('api/unlike_post', ['uses' => 'Web\LikeController@unlike']);

    $router->post('api/attach/upload', ['uses' => 'Web\FileController@uploadAttach']);
    $router->post('api/attached_file/upload', ['uses' => 'Web\FileController@uploadFiles']);

    $router->post('api/follow', ['uses' => 'Web\GroupController@follow']);
    $router->post('api/unfollow', ['uses' => 'Web\GroupController@unfollow']);

    $router->post('api/flag/post', ['uses' => 'Web\ReportController@reportPost']);

    $router->post('api/erc20token/enable', ['uses' => 'Web\ERC20TokenController@enableERC20Token']);
    $router->post('api/erc20token/createimport', ['uses' => 'Web\ERC20TokenController@createERC20TokenImport']);
    $router->post('api/erc20token/create', ['uses' => 'Web\ERC20TokenController@createERC20Token']);
    $router->post('api/erc20token/delete', ['uses' => 'Web\ERC20TokenController@deleteImportedERC20Token']);

    $router->post('api/airdrop/pause', ['uses' => 'Web\AirdropTokenController@pauseAirdropJob']);
    $router->post('api/airdrop/resume', ['uses' => 'Web\AirdropTokenController@resumeAirdropJob']);
    $router->post('api/airdrop/delete', ['uses' => 'Web\AirdropTokenController@deleteAirdropJob']);

    $router->post('api/airdrop/create', ['uses' => 'Web\AirdropTokenController@createAirdrop']);
    $router->post('api/onetime_airdrop/create', ['uses' => 'Web\AirdropTokenController@createOnetimeAirdrop']);
    $router->post('api/airdrop/edit', ['uses' => 'Web\AirdropTokenController@editAirdrop']);
    $router->get('api/airdrop/getlist', ['uses' => 'Web\AirdropTokenController@getAirdropList']);
    $router->get('api/airdrop/get/{airdrop_id}', ['uses' => 'Web\AirdropTokenController@getAirdrop']);
    $router->get('api/airdrop/gettotal', ['uses' => 'Web\AirdropTokenController@getLastThirtyDaysAirdropHistory']);

    // $router->post('api/erc20token/buy', ['uses' => 'Web\PaypalController@getTokenFromBuyCreateToken']);
    // $router->post('api/erc20token/buyconfirm', ['uses' => 'Web\PaypalController@payPalChargeExecute']);
    $router->get('api/features/getlist', ['uses' => 'Web\FeatureController@getList']);
    $router->post('api/features/enable', ['uses' => 'Web\FeatureController@enableFeature']);
    $router->post('api/features/disable', ['uses' => 'Web\FeatureController@disableFeature']);

    // admin ban/unban user
    $router->post('api/group/banUser/{user_id}', ['uses' => 'Web\BanUsersController@banUser']);
    $router->post('api/group/unBanUser/{user_id}', ['uses' => 'Web\BanUsersController@unBanUser']);

    //admin pin topic
    $router->post('api/group/pin/{thread_id}', ['uses' => 'Web\GroupPinController@pin']);
    $router->post('api/group/unpin/{thread_id}', ['uses' => 'Web\GroupPinController@unpin']);
    $router->post('api/group/pin_status/{thread_id}', ['uses' => 'Web\GroupPinController@pinStatus']);

    $router->post('api/attached_files/setting', ['uses' => 'Web\AttachedFilesController@updateGroupSetting']);

    //invite member
    $router->post('api/group/inviteMember', ['uses' => 'Web\GroupMemberController@inviteMember']);

    //admin setting
    $router->post('api/group/admin_setting/add_admin', ['uses' => 'Web\GroupAdminController@addGroupAdmin']);
    $router->post('api/group/admin_setting/del_admin', ['uses' => 'Web\GroupAdminController@deleteGroupAdmin']);
    $router->post('api/group/admin_setting/add_moderator', ['uses' => 'Web\GroupAdminController@addGroupModerator']);
    $router->post('api/group/admin_setting/del_moderator', ['uses' => 'Web\GroupAdminController@deleteGroupModerator']);
    $router->post('api/group/admin_setting/change_owner', ['uses' => 'Web\GroupAdminController@changeGroupOwner']);
    $router->get('api/group/admin_setting/group_member', ['uses' => 'Web\GroupAdminController@getGroupMember']);
    $router->get('api/group/admin_setting/select_member', ['uses' => 'Web\GroupAdminController@selectGroupMember']);
    $router->get('api/group/admin_setting/select_admin', ['uses' => 'Web\GroupAdminController@selectGroupAdmin']);

    //get invite status
    $router->get('api/group/get_invite_status', ['uses' => 'Web\GroupMemberController@getInviteStatus']);

    // order categories
    $router->post('/api/category/order', ['uses' => 'Web\CategoryController@orderCategory']);

    //group privacy
    $router->get('api/group/get_feature_setting', ['uses' => 'Web\GroupPrivacyController@getGroupPrivacySetting']);
    $router->post('api/group/set_feature_setting', ['uses' => 'Web\GroupPrivacyController@setGroupPrivacySetting']);
    $router->post('api/group/join_request', ['uses' => 'Web\GroupPrivacyController@joinRequest']);
    $router->post('api/group/ignore_request', ['uses' => 'Web\GroupPrivacyController@ignoreRequest']);
    $router->post('api/group/approve_request', ['uses' => 'Web\GroupPrivacyController@approveRequest']);
});

// authenticated routes for app
$router->group(['prefix' => /*'groups/{group_name}/*/'api/app', 'middleware' => ['setGroup', 'cors', 'keyVerify']], function ($router) {
    $router->post('follow', ['uses' => 'Web\GroupController@follow']);
    $router->post('unfollow', ['uses' => 'Web\GroupController@unfollow']);

    $router->post('attach/upload', ['uses' => 'Web\FileController@uploadAttach']);

    $router->post('submit_thread', ['uses' => 'Web\PostController@submitTopic']);
});


$router->group([/*'prefix' => 'groups/{group_name}',*/ 'middleware' => ['setGroup', 'cors']], function ($router) {
    $router->get('api/user/{user_id}', ['uses' => 'Web\UserController@getLoginUser']);// get a user info

    $router->get('get_topics', ['uses' => 'Web\CategoryController@getTopics']);
    $router->get('login', ['uses' => 'Auth\
    @login']);
    $router->get('api/thread/{thread_id}/{sort}/{page}[/{post_id}]', ['uses' => 'Web\ThreadController@getThread']);
    $router->get('api/load_more/{sort}/{post_id}', ['uses' => 'Web\ThreadController@loadMore']);

    $router->get('api/get_category/', ['uses' => 'Web\CategoryController@getCategory']);
    $router->get('api/category/{sort}/{page}[/{category_id}]', ['uses' => 'Web\CategoryController@getThreads']);

    $router->get('api/group/info', ['uses' => 'Web\GroupController@getGroupInfo']);
    $router->get('api/group/statistic', ['uses' => 'Web\GroupController@groupStatistic']);
    $router->get('api/group/memberList/{filter}/{page}', ['uses' => 'Web\GroupMemberController@getGroupMemberList']);
    $router->get('api/group/banNum', ['uses' => 'Web\GroupMemberController@getGroupBanNum']);
    // $router->get('api/group/checkUser/{user_id}', ['uses' => 'Web\GroupController@checkUserBan']);
    // $router->post('api/group/banUser/{user_id}', ['uses' => 'Web\GroupController@banUser']);
    // $router->post('api/group/unBanUser/{user_id}', ['uses' => 'Web\GroupController@unBanUser']);

    //user subscribe/unsubscribe thread 
    $router->post('api/group/subscribeThread/{thread_id}', ['uses' => 'Web\SubscribeThreadController@subscribeThread']);
    $router->post('api/group/unsubscribeThread/{thread_id}[/{user_id}]', ['uses' => 'Web\SubscribeThreadController@unsubscribeThread']);


    $router->get('api/like/list/{post_id}', ['uses' => 'Web\LikeController@likeList']);
    $router->get('api/flag/post/list/{post_id}', ['uses' => 'Web\ReportController@reportPostUserList']);

    $router->get('api/erc20token/getimportlist/{page}', ['uses' => 'Web\ERC20TokenController@getERC20TokenImportList']);

    //todo remove to line 128
     $router->get('api/erc20token/test', ['uses' => 'Web\ERC20TokenController@testERC20Token']);
    // $router->post('api/erc20token/createtest', ['uses' => 'Web\ERC20TokenController@createERC20Token']);

    // $router->post('api/erc20token/enableimporttest', ['uses' => 'Web\ERC20TokenController@createERC20TokenImport']);
    // $router->post('api/airdrop/create', ['uses' => 'Web\AirdropTokenController@createAirdrop']);
    // $router->post('api/onetime_airdrop/create', ['uses' => 'Web\AirdropTokenController@createOnetimeAirdrop']);
    // $router->post('api/airdrop/edit', ['uses' => 'Web\AirdropTokenController@editAirdrop']);
    // $router->get('api/airdrop/getlist', ['uses' => 'Web\AirdropTokenController@getAirdropList']);
    // $router->get('api/airdrop/get/{airdrop_id}', ['uses' => 'Web\AirdropTokenController@getAirdrop']);
    //todo end

    $router->get('api/attached_files/setting', ['uses' => 'Web\AttachedFilesController@getGroupSetting']);

});

$router->get('{path:.*}', function () {
    return response(['status' => 'undefined route']);
});