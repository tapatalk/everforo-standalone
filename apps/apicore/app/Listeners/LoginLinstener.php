<?php

//Listener 
namespace App\Listeners;
 
use App\Events\ParseFilesEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ParseFilesNoti;
use App\User;
 
class LoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
 
    /**
     * Handle the event.
     *
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(LoginEvent $event)
    {
        //
        //Log::info('Event is happing: ' . $event->message);
        //Notification::send(User::find($event->user_id), new ParseFilesNoti($event->message));
    }
}