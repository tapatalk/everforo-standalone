<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Repositories\UploadRepository;
use App\Http\Requests\Auth\UpdateProfileRequest;


class FileController extends Controller
{

    private $userRepo;
    private $_transformer;

    /**
     * UserController constructor.
     * @param UserRepo $userRepo
     */
    public function __construct(Transformer $transformer)
    {
  
        $this->_transformer = $transformer;
    }


    public function uploadAttach(Request $request, UploadRepository $imageRepo)
    {
        $file = $request->file('image');

        $response = $imageRepo->save($request, $file,'attach');
        
        return $this->_transformer->success($response);
    }

    /**
     * 
     */
    public function uploadFiles(Request $request, UploadRepository $uploadRepo)
    {
        $file = $request->file('attached_file');

        $response = $uploadRepo->saveAttachedFile($file);
        
        return $this->_transformer->success($response);
    }

    public function uploadGroupPic(Request $request,UploadRepository $imageRepo)
    {
        $file = $request->file('image');
        
        $response = $imageRepo->save($request, $file,'group_pic');
       
        return $this->_transformer->success($response);
    }

   
}