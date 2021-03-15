<?php

// namespace App\Providers;
// use Illuminate\Support\Facades\Broadcast;
// use Illuminate\Support\ServiceProvider;

// class BroadcastServiceProvider extends ServiceProvider
// {
//     /**
//      * Bootstrap any application services.
//      *
//      * @return void
//      */
//     public function boot()
//     {
//         // lumen 5.6 has an issue, Broadcast::routes() can't work
//         // LumenApi/vendor/illuminate/broadcasting/BroadcastManager.php line 59
//         // https://github.com/laravel/lumen-framework/issues/746
//         // 
//         // registers the broadcast routes /broadcasting/auth 
//         // that Echo uses for authentication and authorization.
//         // Broadcast::routes();

//         /*
//          * Authenticate the user's personal channel...
//          */
//         Broadcast::channel('web-user.*', function ($user, $userId) {
//             return (int) $user->id === (int) $userId;
//         });
//     }
// }