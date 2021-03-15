<?php

namespace App\Http\Controllers\Web;
use App\Models\Group;
use App\Http\Controllers\Controller;
use App\Models\Thread;
use App\Repositories\CommentsRepository;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Utils\Transformer;
use App\Repositories\CategoryRepository;
use App\Repositories\ThreadRepository;
use App\Repositories\ThreadListRepository;
use App\Http\Requests\Web\DeleteCategoryRequest;
use App\Http\Requests\Web\AddCategoryRequest;

class CategoryController extends Controller
{

    private $_group = "";
    private $_transformer;
    /**
     * ProfileController constructor.
     *
     */
    public function __construct(Transformer $transformer)
    {
     //   $this->middleware('auth', ['except' => 'show']);
    //    $this->middleware('auth:optional', ['only' => 'show']);
       $this->_transformer = $transformer;
       $this->_group = config('app.group');

    }

    /**
     * @param Request $request
     * @param CategoryRepository $categoryRepo
     * @param ThreadListRepository $threadListRepo
     * @param CommentsRepository $commentsRep
     * @param string $sort
     * @param int $category_id
     * @param int $page
     * @return array
     */
    public function getThreads(Request $request, 
                                CategoryRepository $categoryRepo,
                                ThreadListRepository $threadListRepo,
                                CommentsRepository $commentsRep,
                                $sort, $page, $category_id)
    {
        $user = $request->user();

        $url = 'http://apiprivacy/apiprivacy/check_group_feature?group_id=' . $this->_group->id;
        if ($user) {
            $url = $url . '&user_id=' . $user->id;
        }
        if (!geturl($url)) {
            return $this->_transformer->fail(40003);
        }

        if ($category_id > 0){

            $category_exists = $categoryRepo->getOne($this->_group->id, $category_id);
            // category might be deleted
            if(!$category_exists) {
                return $this->_transformer->success(['threads' => []]);
            } else if ($user && $user->id) {
                //Record the user's activity time in the category
                $categoryRepo->recordUserCategoryActive($user->id, $this->_group->id, $category_id);
            }
        }

        $threads = $threadListRepo->getThreadList($this->_group->id, $sort, $page, $category_id, $user, $commentsRep);

        return $this->_transformer->success(['threads' => $threads]);
    }

    /**
     * @param CategoryRepository $categoryRepo
     * @return array
     */
    public function getCategory(Request $request,
                                CategoryRepository $categoryRepo,
                                ThreadRepository $threadRepo,
                                ThreadListRepository $threadListRepo)
    {
        $group_id = $this->_group->id;
        $user = $request->user();
        $url = 'http://apiprivacy/apiprivacy/check_group_feature?group_id=' . $this->_group->id;
        if ($user) {
            $url = $url . '&user_id=' . $user->id;
        }
         if (!geturl($url)) {
             return $this->_transformer->success(['category' => []]);
         }
        $categories = $categoryRepo->getCategoryTree($group_id)->toArray();

        if ($categories && $threadRepo->isUncategorized($group_id)) {

            $uncategorized = [];
            $uncategorized['id']=-1;
            $uncategorized['group_id']=$group_id;
            $uncategorized['name']='Uncategorized';
            $uncategorized['category_id']=-1;
            $uncategorized['parent_id']=null;
            $uncategorized['order']=null;
            $uncategorized['created_at']='1970-01-01 00:00:00';
            $uncategorized['updated_at']='1970-01-01 00:00:00';
            $uncategorized['deleted_at']=null;

            array_unshift($categories, $uncategorized);
        }

        if ($user) {
            $category_ids = $categoryRepo->getCategoryNewTopic($group_id, $user->id);
            if ($category_ids) {
                foreach ($categories as &$category) {
                    if (in_array($category['category_id'], $category_ids)) {
                        $category['new_topics'] = 1;
                    }
                }
            }

        }

        return $this->_transformer->success(['category' => $categories]);
    }

    public function orderCategory(Request $request,
                                CategoryRepository $categoryRepo)
    {
        $group_id = $this->_group->id;
        $category_order = $request->input('category_order');
        
        $category = $categoryRepo->orderCategories($group_id, $category_order);

        return $this->_transformer->success(['category' => $category]);
    }

}