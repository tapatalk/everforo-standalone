<?php

namespace App\Repositories;

use App\Models\Report;

/**
 * 
 */
class ReportRepository
{
    /**
     * add a new report to a post
     */
    public function addReport($post_id, $group_id, $user_id, $reason) {

        $send_notification = false;

        $report = Report::where('user_id', $user_id)
                        ->where('post_id', $post_id)
                        ->first();

        if (!$report) {
            $send_notification = true;
            $report =  Report::create([
                'post_id' => $post_id,
                'group_id' => $group_id,
                'user_id' => $user_id,
                'reason' => $reason,
            ]);
        }

        return [$this->getReportWithPoster($report->id), $send_notification];
    }

    /**
     * fetch report data with poster info
     */
    public function getReportWithPoster($report_id)
    {
        $report = \DB::table('report')
                        ->join('posts', 'report.post_id', '=', 'posts.id')
                        ->join('users', 'posts.user_id', '=', 'users.id')
                        ->where('report.id', $report_id)
                        ->select('report.*', 'users.id as poster_id', 'users.name as poster_name', 'users.photo_url as poster_photo_url')
                        ->first();

        if ($report) {
            $report->poster = new \stdClass();
            $report->poster->user_id = $report->poster_id;
            $report->poster->name = $report->poster_name;
            $report->poster->photo_url = $report->poster_photo_url;
            unset($report->poster_id);
            unset($report->poster_name);
            unset($report->poster_photo_url);
        }

        return $report;
    }

    /**
     * get the user list of a post which reported by these users
     */
    public function userList($group_id, $post_id)
    {
        return Report::with('user')
                        ->select(array('report.*','group_ban_users.id as is_ban'))
                        ->where('report.group_id', $group_id)
                        ->where('report.post_id', $post_id)
                        ->join('group_ban_users', function($join) use ($group_id){
                            $join->on('group_ban_users.user_id','=','report.user_id')
                                ->where('group_ban_users.group_id','=',$group_id)
                                ->whereNull('group_ban_users.deleted_at');
                        }, null,null,'left')
                        ->get();
    }

}