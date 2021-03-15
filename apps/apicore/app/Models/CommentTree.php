<?php

namespace App\Models;

class  CommentTree  {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    public static function buildTree($thread_id)
    {
        $posts = Post::with('user')->where('thread_id',$thread_id)->get();
        $post_index = array();
        foreach($posts as $post){
            $post_index[$post->id] = $post;
        }
        $index_tree = self::parseToTree($post_index);
        $comments_tree = self::getCommentsByTree($index_tree,$post_index);
        return $comments_tree;
    }

    public static function getLeafByCommentId($comment_id){
          $post = Post::with('user')->where('id',$comment_id)->frist();
          $post->children = array();
          return $post;
    }


    private static function parseToTree($posts)
    {
        $flat = array();
        $tree = array();

        foreach ($posts as $post_id => $post) {
            if (!isset($flat[$post->parent_id])) {
                $flat[$post_id] = array();
            }
            if ($post->parent_id != -1) {
                $flat[$post->parent_id][$post_id] =& $flat[$post_id];
            } else {
                $tree[$post_id] =& $flat[$post_id];
            }
        }

        return $tree;
    }


    private static function getCommentsByTree($tree,$posts)
    {
        $comments_tree = array();
        if(sizeof($tree) > 0){
            foreach($tree as $parent_id => $children) {

                $child_comments = self::getCommentsByTree($children,$posts);
                $posts[$parent_id]->children = $child_comments;
                 $comments_tree[] = $posts[$parent_id];
            }
        }
        return  $comments_tree ;
    }

    public function likes()
    {
        $data=  $this->hasMany('App\Models\Like',"post_id","id");
        if($data){
            return $data;
        } else {
            return Array();
        }
    }


    public function thread(){
        return $this->belongsTo('App\Models\Thread','thread_id','id');
    }


 
}
