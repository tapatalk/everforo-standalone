<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Thread;
use App\Models\UserCategoryActive;

/**
 * Push repo
 * 
 * @author Chen Chao <chenchao@tapatalk.com>
 */
class CategoryRepository
{
    
//    public static function buildTree( $elements, $parentId = -1) {
//        $branch = array();
//
//        foreach ($elements as $element) {
//            if ($element['parent_id'] == $parentId) {
//                $children = self::buildTree($elements, $element['id']);
//                if ($children) {
//                    $element['children'] = $children;
//                }
//                $branch[] = $element;
//            }
//        }
//
//          return $branch;
//    }

    /**
     * 
     */
    public static function getOne($group_id, $category_id) {
        return Category::where('group_id', $group_id)
                        ->where('category_id', $category_id)
                        ->first();
    } 

    /**
     * fetch category list,
     * it used to be nested data structure, but later we changed it to simple one dim array
     * @param $group_id
     * @return mixed
     */
    public static function getCategoryTree($group_id)
    {
        return Category::select(['*'])->where('group_id', $group_id)
                ->orderBy('order', 'asc')->get();
    }

    /**
     * the `category_id` act like a index in group level
     * @param $group_id
     * @param $category_name
     * @return mixed
     */
    public static function addCategory($group_id, $category_name)
    {
        $max_category_id = Category::withTrashed()
                            ->select('category_id')
                            ->where('group_id', $group_id)
                            ->max('category_id');

        return Category::create([
            'name' => $category_name,
            'group_id' => $group_id,
            'category_id' => $max_category_id+1,
//            'parent_id' => -1,
        ]);
    }

    /**
     * @param $group_id
     * @param $category_id
     * @param $category_name
     * @return mixed
     */
    public static function updateCategory($group_id, $category_id, $category_name)
    {
        return Category::where('group_id', $group_id)
            ->where('category_id', $category_id)
            ->update(['name' => $category_name]);
    }

    public static function deleteCategory($group_id, $category_id)
    {
        $deletedRows = Category::where('category_id', $category_id)->where('group_id', $group_id)->delete();//soft delete

        Thread::where('group_id', $group_id)->where('category_index_id', $category_id)->update(['category_index_id' => '-1']);

        if ($deletedRows) {
            return $deletedRows;
        } else {
            return False;
        }
    }

    public static function orderCategories($group_id, $category_order)
    {
        $category_order = json_decode($category_order);

        try {
            \DB::beginTransaction();
        
            foreach ($category_order as $order => $category_id) {
                
                Category::where('group_id', $group_id)
                        ->where('id', $category_id)
                        ->update(['order' => (int)$order + 1]);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
        }

    }

    /**
     * @param $user_id
     * @param $category_id
     * @return mixed
     */
    public function recordUserCategoryActive($user_id, $group_id, $category_id)
    {
        $userCategoryActive = UserCategoryActive::where( 'category_id', $category_id)
            ->where('user_id', $user_id)
            ->where('group_id', $group_id)
            ->first();
        if ($userCategoryActive) {
            $userCategoryActive->updated_at = date('Y-m-d H:i:s');
            $userCategoryActive->save();
        } else {
            $userCategoryActive = UserCategoryActive::create([
                'user_id' => $user_id,
                'group_id' => $group_id,
                'category_id' => $category_id,
            ]);
        }
        return $userCategoryActive;
    }

    /**
     * @param $group_id
     * @param $category_id
     */
    public function updateCategoryLastActive($group_id, $category_id)
    {
        $time = date('Y-m-d H:i:s');
        Category::where('group_id', $group_id)
            ->where('category_id', $category_id)
            ->update(['last_active_time' => $time]);
    }

    public function getCategoryNewTopic($group_id, $user_id)
    {
        $category_data = [];
        $category = Category::where('group_id', $group_id)->whereNotNull('last_active_time')->get()->toArray();
        $user_active = UserCategoryActive::where('group_id', $group_id)->where('user_id', $user_id)->get()->toArray();
        foreach ($category as $value) {
            if ($user_active) {
                foreach ($user_active as $val) {
                    if ($value['category_id'] == $val['category_id'] && $value['last_active_time'] <= $val['updated_at']) {
                        $category_data[] = $value['category_id'];
                    }
                }
            }
        }
        $category_ids = [];
        if ($category_data) {
            foreach ($category as $value) {
                if (!in_array($value['category_id'], $category_data)) {
                    $category_ids[] = $value['category_id'];
                }
            }
        } else {
            $category_ids = array_column($category, 'category_id');
        }
        return $category_ids;
    }

}