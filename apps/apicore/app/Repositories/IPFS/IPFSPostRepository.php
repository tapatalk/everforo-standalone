<?php

namespace App\Repositories\IPFS;

use App\Models\Thread;
use App\Models\Post;
use App\Models\Attachment;

class IPFSPostRepository {
    public function buildPostIPFSBody($post){
        $ipfs_post_data = array();
        $ipfs_post_data['user'] =  $post->user->name;

        $attachments = Attachment::select('id', 'ipfs', 'url')
                    ->where('post_id', $post->id)
                    ->get();
        $ipfs_attachments = array();
        $attachments_array = array();


        foreach($attachments as $attachment){
            $attachment_ipfs = array();
            $attachment_ipfs['ipfs'] = env('IPFS_DOMAIN')."/".$attachment->ipfs;
            $ipfs_attachments[] = $attachment_ipfs;
            $attachments_array[$attachment->id] = $attachment;
        }

        $content = json_decode($post->content);
        $content_new = array();

        foreach($content as $element){
            if(isset($element->insert->image) && isset($element->attributes) && isset($element->attributes->id)
                && isset($attachments_array[$element->attributes->id])){
                $attachment = $attachments_array[$element->attributes->id];
                $element->insert->image = env('IPFS_DOMAIN')."/ipfs/".$attachment->ipfs;
                unset($element->attributes->id); 
                unset($element->attributes->thumb_url); 
            }   
           // remove id.
            $content_new[] = $element;
        }
        
        if(!empty($attachment_ipfs)){
             $ipfs_post_data['attachments'] = $ipfs_attachments;
        }

        $ipfs_post_data['content'] = $content_new;
        return $ipfs_post_data;
    }



}