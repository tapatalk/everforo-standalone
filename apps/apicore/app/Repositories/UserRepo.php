<?php

namespace App\Repositories;

use App\User;
use App\Models\Post;
use App\Models\Like;
use App\Models\UsersSettings;

class UserRepo
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
    /**
     * @param $user_id
     * @return mixed
     */
    public function getCurrentUser($user_id)
    {
        $user = User::getOne($user_id);

        if ($user) {
            unset($user['password']);
            return $user->toArray();
        }

        return [];
    }

    public function getUserObject($user_id)
    {
        $user = User::getOne($user_id);

        if ($user) {
            unset($user->password);
            return $user;
        }

        return null;
    }

    public function getUserSatistic($user_id)
    {
        return [
            'posts' => Post::where('user_id', $user_id)->count(),
            'likes' => \DB::table('posts')->join('likes', 'posts.id', '=', 'likes.post_id')
                        ->where('posts.user_id', $user_id)->whereNull('likes.deleted_at')->count(),
        ];
    }

    public function getProfiles(array $user_id_list)
    {
        if (empty($user_id_list)) {
            return [];
        }

        $user = User::select(['id as user_id', 'name', 'photo_url'])
                    ->whereIn('id', $user_id_list)
                    ->get();

        foreach($user as $value) {
            if (UserBehaviorRepository::checkUserOnline($value->user_id)) {
                $value->online = true;
            } else {
                $value->online = false;
            }
        }
        

        return $user;
    }

    public static function isSilenceRegister($user)
    {

        if(is_object($user)){
            $username = $user->name;
        } elseif ( is_string($user) ){
            $username = $user;
        } else {
            return false;
        }

        if(strpos($username,'silence_&') === 0 ){
            return true;
        }
        return false;

    }

    
    public function getUserSettings($user_id) {
        return UsersSettings::where('user_id', $user_id)->first();
    }
    

    public function switchDarkMode($user_id, $dark_mode) {

        $usersSetings = UsersSettings::where('user_id', $user_id)->first();

        if ($usersSetings) {
            $usersSetings->dark_mode = $dark_mode;
        } else {
            $usersSetings = new UsersSettings;

            $usersSetings->user_id = $user_id;
            $usersSetings->dark_mode = $dark_mode;
        }

        $usersSetings->save();

        return $usersSetings;
    }

    public function switchLanguage($user_id, $language) {

        $usersSetings = UsersSettings::where('user_id', $user_id)->first();

        if ($usersSetings) {
            $usersSetings->language = $language;
        } else {
            $usersSetings = new UsersSettings;
            $usersSetings->user_id = $user_id;
            $usersSetings->language = $language;
        }

        $usersSetings->save();

        return $usersSetings;
    }


}