<?php

namespace App\Repositories\IPFS;

use App\Models\Thread;
use App\Models\Post;
use App\Repositories\IPFS\IPFSPostRepository;

class IPFSThreadRepository {
	private $_ipfsRepository;
	private $_commentsRepository;
	private $_ipfsPostRepository;

	public function __construct(IPFSRepository $ipfsRepository,IPFSPostRepository $ipfsPostRepository)
    {
   
        $this->_ipfsRepository = $ipfsRepository;
        $this->_ipfsPostRepository = $ipfsPostRepository;
    }

    public function buildIPFSPayload($thread_id){
       $thread = Thread::with('user','category')->where('id', $thread_id)->first();
        $posts = Post::with('user')->select('id', 'thread_id', 'parent_id', 'created_at','ipfs','user_id')
                    ->where('thread_id', $thread_id)
                    ->orderBy('id', 'desc')
                    ->get()->all();
         
        $first_post = Post::with('user')->select('id','ipfs','created_at','user_id','content')->where('id', $thread->first_post_id)->first();
      
        $first_post_data = $this->_ipfsPostRepository->buildPostIPFSBody($first_post);
        $tree = $this->buildTree($posts);

        $payload =$first_post_data;
        $payload['title'] = $thread->title;
        if($thread->category && $thread->category->name){
        	$payload['category'] = $thread->category->name;
    	}
        unset($tree[$first_post->ipfs]);
        $payload['replies'] = $tree;
   		$payload['timestamp'] =  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $first_post->created_at)->timestamp;
   		$type = 'thread';

       
     	$ipfs_thread = $this->_ipfsRepository->submitToIPFS($payload,$type);
     	if($ipfs_thread){
     		tap($thread)->update(['ipfs'=>$ipfs_thread]);
     	}
    }

  
	private function buildTree( &$posts, $parentId = -1) {
	    $branch = array();

	    foreach ($posts as $post) {
	        if ($post->parent_id == $parentId) {
	            $children = $this->buildTree($posts, $post->id);
	             $data = array();
	            if ($children) {
	                $data['replies'] = $children;
	            }
	         


	            $timestamp = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $post->created_at)->timestamp;
	            if($post->ipfs){
		            $data['ipfs'] = $post->ipfs;
		           	$data['url'] = env('IPFS_DOMAIN')."/ipfs/".$post->ipfs;
		            $branch[$post->ipfs] = $data;
		            unset($posts[$post->id]);
	        	}
	        }
	    }
	    return $branch;
	}


}