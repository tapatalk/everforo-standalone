<?php

namespace App\Repositories\IPFS;

use App\Models\Thread;
use App\Models\Post;
use App\Models\Group;
use App\Repositories\PostRepository;
use App\Repositories\CategoryRepository;
class IPFSGroupRepository {
	private $_ipfsRepository;


	public function __construct(IPFSRepository $ipfsRepository,CategoryRepository $categoryRepo)
    {
   
        $this->_ipfsRepository = $ipfsRepository;
        $this->_categoryRepository = $categoryRepo;
    }

    public function buildIPFSPayload($group_id){
       $threads = Thread::with('category')->select('ipfs','category_id','group_id','category_index_id')->where('group_id', $group_id)->where("ipfs","!=",'')->get()->toArray();
    	$categories = $this->_categoryRepository->getCategoryTree($group_id)->toArray();
         $group = Group::find($group_id);

    	$category_array = array();
         foreach($categories as $category){
         	$item = array();
         	$item['name'] = $category['name'];
         	$category_array[] = $item;
         }
         $thread_array = array();
        foreach ($threads as $thread) {
      
        	$thread_array[] = $thread['ipfs'];
        }


        $payload =array();
        $payload['name'] =$group->name;
        $payload['description'] = $group->description;
        $payload['logo'] = $group->logo;
        $payload['cover'] = $group->cover;
       
        $payload['categories'] = $category_array;
   		$payload['threads'] = $thread_array;

   		$type = 'group';
   		print_r($payload);
       
     	$ipfs_group = $this->_ipfsRepository->submitToIPFS($payload,$type);
     	print $ipfs_group;
        $ipns_group =$this->_ipfsRepository->publish($ipfs_group);
        print $ipns_group;exit;
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