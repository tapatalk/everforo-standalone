<?php
namespace App\Utils;



/**
 * Search Transformer
 *
 * @author Hu Yao <yao@tapatalk.com>
 */
class StringHelper{


    /**
     * generate notification title based on type
     * @param   string  $type
     * @param   string  $username
     * @param   string  $title
     * @param   string|int  $extra_info
     * @param   int     $need_font
     * @return  string
     */
    public static function getNotificationTitle($type, $username, $title, $extra_info = "", $need_font = 1)
    {
        $title = strip_tags($title);

        if($type == 'like'){
            $format = '<strong>%1$s</strong> liked your post in <strong>%2$s</strong>';

            $msg =sprintf($format,$username,$title);
        } else if($type == 'flag'){
            $format = '<strong>%1$s</strong> flagged the post in <strong>%2$s</strong> as <strong>%3$s<strong>';
            
            if ($extra_info && !is_array($extra_info) ) {
                $extra_info = trans('flag_reason.flag_post_reason' . $extra_info);
            } else {
                // maybe some default reason?
                $extra_info = '';
            }

            $msg =sprintf($format,$username,$title,$extra_info);
        }else if($type == 'airdrop'){

            if(!empty($extra_info) && is_array($extra_info)) {
                $format = 'You received <strong>%1$s</strong> %2$s from <strong>%3$s</strong> because of your participation. Keep up the good work!';

                $msg =sprintf($format, $extra_info['count'], $extra_info['token_name'], $extra_info['group_name']);

            } else {
                return '';
            }
        } else if($type == 'onetime_airdrop'){

            if(!empty($extra_info) && is_array($extra_info)) {
                $format = '%1$s sent you <strong>%2$s</strong> %3$s';
                $msg =sprintf($format,$extra_info['admin_name'], $extra_info['count'], $extra_info['token_name']);
            } else {
                return '';
            }
        } else {
            $format = '<strong>%1$s</strong> posted a new comment in <strong>%2$s</strong>';
            $msg =sprintf($format,$username,$title);
        }

        if($need_font == 0){
            $msg = strip_tags($msg);
        }
        return $msg;
    }


    public static function slugify($text,$id)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // $text = iconv('utf-8', 'utf-8//IGNORE', $text);

        // transliterate
        $text = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
        return $id;
        }

        $text = $text."-".$id;

        return $text;
    }
           

}