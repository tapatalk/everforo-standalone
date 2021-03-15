<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public $_url;
    public $group_title;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($url, $group_title)
    {
        $this->_url = $url;
        $this->group_title = $group_title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Password Reset Request at " . $this->group_title)->from(env('EMAIL_DOMAIN', 'support@everforo.com'), env('EMAIL_NAME', 'Everforo'))->view('emails.password_reset');
    }
}
