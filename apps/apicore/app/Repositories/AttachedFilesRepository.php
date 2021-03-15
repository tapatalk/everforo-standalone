<?php

namespace App\Repositories;

use App\Models\AttachedFiles;
use App\Models\AttachedFilesSetting;

class AttachedFilesRepository {

    public function getGroupSetting($group_id) {
        $setting = AttachedFilesSetting::where('group_id', $group_id)->first();

        if ($setting) {
            return $setting->toArray();
        } else {
            // default available to everyone
            return ['allow_everyone' => 1, 'allow_post' => 1];
        }
    }

    public function saveGroupSetting($data) 
    {

        $setting = AttachedFilesSetting::where('group_id', $data['group_id'])->first();

        if ($setting) {

            $setting->allow_everyone = $data['allow_everyone'];
            $setting->allow_post = $data['allow_post'];

        } else {

            $setting = new AttachedFilesSetting;

            $setting->group_id = $data['group_id'];
            $setting->allow_everyone = $data['allow_everyone'];
            $setting->allow_post = $data['allow_post'];
        }

        $setting->save();

        return $setting;
    }

    public function updateAttachedFiles($attached_files_id, $group_id, $thread_id, $post_id)
    {
        if ($attached_files_id) {
            AttachedFiles::whereIn('id', $attached_files_id)
                            ->update(['group_id' => $group_id, 'thread_id' => $thread_id, 'post_id' => $post_id]);
        }

        return AttachedFiles::where('post_id', $post_id)->get();
    }

    public function deleteAttachedFiles($deleted_attached_files)
    {
        $deletedRows = AttachedFiles::whereIn('id', $deleted_attached_files)->delete();
    }
}