<?php

namespace App\Http\Controllers\Web;

use App\Repositories\AdminRepository;
use App\Repositories\GroupAdminRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Utils\Transformer;

use App\Repositories\AttachedFilesRepository;

class AttachedFilesController extends Controller {

    private $_transformer;
    private $_group;

    public function __construct(Transformer $transformer)
    {
        $this->_transformer = $transformer;
        $this->_group = config('app.group');
    }

    public function getGroupSetting(AttachedFilesRepository $attachedFilesRepo) 
    {
        $setting = $attachedFilesRepo->getGroupSetting($this->_group->id);

        return $this->_transformer->success($setting);
    }

    public function updateGroupSetting(Request $request,
                                       AttachedFilesRepository $attachedFilesRepo,
                                       AdminRepository $adminRep,
                                       GroupAdminRepository $groupAdminRep)
    {
        $data = $request->only(['allow_everyone', 'allow_post']);
        $user = $request->user();

        if (!$adminRep->isSuperAdmin($user->id)
            && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id)
        ) {
            return $this->_transformer->noPermission();
        }

        $data['group_id'] = $this->_group->id;

        $data = $attachedFilesRepo->saveGroupSetting($data);

        return $this->_transformer->success($data);
    }

}