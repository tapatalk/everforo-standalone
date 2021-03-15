<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscribeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $post_url;
    public $unsubscribe_url;
    public $user_name;
    public $thread_title;
    public $group_name;
    public $group_title;
    public $type;
    public $to_user;
    public $domain;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($send_data)
    {
        $this->post_url = $send_data['post_url'];
        $this->unsubscribe_url = $send_data['unsubscribe_url'];
        $this->user_name = $send_data['user_name'];
        $this->thread_title = $send_data['thread_title'];
        $this->group_name = $send_data['group_name'];
        $this->group_title = $send_data['group_title'];
        $this->type = $send_data['type'];
        $this->to_user = $send_data['to_user'];
        $this->domain = env('EVERFORO_DOMAIN', 'https://sa.everforo.com');
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->user_name . ' ' . $this->type . ' ' . $this->to_user . ' post in ' . $this->thread_title)->from(env('EMAIL_DOMAIN', 'support@everforo.com'), env('EMAIL_NAME', 'Everforo'))->view('emails.subscribe_email');
    }
}
