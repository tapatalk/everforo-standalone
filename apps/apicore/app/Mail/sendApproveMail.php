<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendApproveMail extends Mailable
{
    use Queueable, SerializesModels;

    public $group_title;
    public $group_cover;
    public $msg;
    public $domain;
    public $group_url;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($send_data)
    {
        $this->group_url = $send_data['group_url'];
        $this->msg = $send_data['message'];
        $this->group_title = $send_data['group_title'];
        $this->group_cover = $send_data['group_cover'];
        $this->domain = env('EVERFORO_DOMAIN', 'https://sa.everforo.com');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->msg)->from(env('EMAIL_DOMAIN', 'support@everforo.com'), env('EMAIL_NAME', 'Everforo'))->view('emails.approve_email');
    }
}
