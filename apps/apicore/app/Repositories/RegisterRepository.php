<?php

namespace App\Repositories;


use App\Mail\RegisterLink;
use App\Mail\PasswordReset;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use App\Constants\Constants;

/**
 * Push repo
 * 
 * @author Chen Chao <chenchao@tapatalk.com>
 */
class RegisterRepository
{

    public static function sendRegisterEmail($email, $go_back, $group_title)
    {   
        $user = User::where('email', $email )->first();
        if($user) {
            // it's not possible if previous process is working
        } else {
            $code = rand(1,1000);
            $token = md5(md5($email).$code);
            $redis = Redis::connection();
            $redis->setEx($token,1800, urlencode($email));
            $url = env('EVERFORO_DOMAIN', 'https://sa.everforo.com')
            . "/email/register?token=".$token."&email=" . urlencode($email)
            . "&go_back=" . $go_back;
            $registerEmail = new RegisterLink($url, $group_title);

            if (env('APP_ENV') === 'local') {
                \Log::info('confirm-email-link '.$url);
                return;
            }

            try{
                Mail::to($email)->send($registerEmail);
            } catch(Exception $e){
                print_r($e);
            }
        }
    }

    public static function sendPwsswordResetEmail($user, $title)
    {
        $code = rand(1,1000);
        $token = md5(md5($user->email).$code);
        $redis = Redis::connection();
        $redis->setEx($token, 1800, urlencode($user->email));
        $url = env('EVERFORO_DOMAIN', 'https://sa.everforo.com')
        . "/password/reset?&token=".$token."&email=" . urlencode($user->email);

        if (env('APP_ENV') === 'local') {
            \Log::info('reset-passowrd-link '.$url);
            return;
        }

        $passwordReset = new PasswordReset($url, $title);
        try{
            Mail::to($user->email)->send($passwordReset);
        } catch(Exception $e){
            print_r($e);
        }
    }

    // public static function sendConfirmEmail($user)
    // {
    //     // todo, send confirm email??
    // }

    /**
     * Check if a username's characters are valid
     *
     * @param  string $username
     *
     * @return boolean
     */
    public function charactersValid($username)
    {
        // All Printable Characters in the ASCII Table—Except the Space Character (http://www.rexegg.com/regex-interesting-character-classes.html)
//        if(preg_match('/^[\w \-\.]{'.Constants::USERNAME_MIN_LENGTH.','.Constants::USERNAME_MAX_LENGTH.'}$/', $username))
        if(preg_match('/^[\p{L}\p{Nd}\.]+$/', $username))
            return true;
        return false;
    }

    /**
     * Check if a username's length is valid
     *
     * @param  string $username
     *
     * @return boolean
     */
    public function lengthValid($username)
    {
        if(strlen($username) < Constants::USERNAME_MIN_LENGTH || strlen($username) > Constants::USERNAME_MAX_LENGTH) return false;

        return true;
    }


    /**
     * Check if a username already exists
     *
     * @param  string $username
     *
     * @return boolean
     */
    public function exists($username)
    {
        $user = User::where('username', $username)->first();

        if($user) return true;

        return false;
    }

}