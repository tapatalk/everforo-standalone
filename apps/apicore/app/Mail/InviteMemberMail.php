<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public $join_url;
    public $user_name;
    public $group_title;
    public $invite_msg;
    public $group_cover;
    public $user_avatar;
    public $domain;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($send_data)
    {
        $this->join_url = $send_data['join_url'];
        $this->user_name = $send_data['user_name'];
        $this->user_avatar = $send_data['user_avatar'];
        $this->group_title = $send_data['group_title'];
        $this->group_cover = $send_data['group_cover'];
        $this->invite_msg = $send_data['message'];
        $this->domain = env('EVERFORO_DOMAIN', 'https://sa.everforo.com');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->user_name . ' invites you to join ' . $this->group_title)->from(env('EMAIL_DOMAIN', 'support@everforo.com'), env('EMAIL_NAME', 'Everforo'))->view('emails.invite_member_email');
    }
}
