<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Image;  // Intervention
use App\Models\AvatarImage;
use App\Models\Attachment;
use App\Models\AttachedFiles;
use App\Repositories\AzureBlobRepository;
use App\Repositories\IPFS\IPFSRepository;

/**
 * Post image repository
 * 
 * @author Chen Chao <chenchao@tapatalk.com>
 */
class UploadRepository  {



    const IMAGE_FILESIZE_LIMIT = 3145728; // 3 Mb

    const GIF_FILESIZE_LIMIT   = 5242880; // 5 Mb
    
    const RESIZE_WIDTH_LIMIT = 800;

    const RESIZE_HEIGHT_LIMIT = 800;

    const RESIZE_FILESIZE_LIMIT = 307200;  // 300 Kb

    const THUMB_WIDTH_LIMIT = 240;

    const THUMB_HEIGHT_LIMIT = 240;

    const DEFAULT_QUALITY = 90;
    /**
     * Azure blob repo
     *
     * @var  App\Repositories\Azure\AzureBlobRepository
     */
    public $azure;

    public $_ipfsRepository;

    public function __construct(AzureBlobRepository $azureBlobRepository, IPFSRepository $ipfsRepository)
    {
        $this->azure = $azureBlobRepository;
        $this->_ipfsRepository = $ipfsRepository;
    }


    /**
     * Save to DB
     *
     * @param   Request $request
     * @param   string  $image_url
     * @param   file    $file
     * @param   User    $user
     * @return  int     Database id
     */    
    private function saveAvatarToDB(Request $request, $image_url, $file, $thumb_url)
    {
       // $user = User::find($request->au_id);

        $record = new AvatarImage;
        $record->url            = $image_url;
        $record->save();
        return $record->id;
    }


    private function saveAttachToDB(Request $request, $image_url, $file, $thumb_url,$ipfs)
    {
       // $user = User::find($request->au_id);

        $record = new attachment;
        $record->url            = $image_url;
        $record->ipfs = $ipfs;
        $record->save();
        return $record->id;
    }


        public function genrerateThumbnailNameFromFilename($original_filename)
    {
        list($original_prefix, $original_postfix) = explode('.', $original_filename);

        return $original_prefix . '_thumb.' . $original_postfix;
    }

    public function createThumbnail($original_filepath, $thumb_filepath)
    {
        $intervention_obj = $this->resize($original_filepath, $thumb_filepath, self::THUMB_WIDTH_LIMIT, self::THUMB_HEIGHT_LIMIT);

        $this->thumb_width  = $intervention_obj->width();
        $this->thumb_height = $intervention_obj->height();

        return $intervention_obj;
    }



        /**
     * Generate container name
     * 
     * @return string container name
     */
    private function generateContainerName()
    {
        return date('Ymd');
    }
   

    /**
     * Upload market image to azure
     *
     * @param   Request  $request
     * @param   File     $file
     * @return  array
     */
    private function upload(Request $request, $file)
    {
        $real_path = $file->getRealPath();
        // read Orientation property when image captured by ios camera
        // $exif = exif_read_data($real_path);
        // $mimeType = $exif['MimeType'];
        // $orientation = $exif['Orientation'];

        // if ($mimeType === 'image/jpeg' || $mimeType === 'image/png') {
        //     if ($orientation) {
        //         $image = $mimeType === 'image/jpeg' ? imagecreatefromjpeg($real_path) : imagecreatefrompng($real_path);
        //         $degree = 0;
        //         switch ($orientation) {
        //             case 3:
        //                 $degree = 180;
        //                 break;
        //             case 6:
        //                 $degree = -90;
        //                 break;
        //             case 8:
        //                 $degree = 90;
        //                 break;
        //         }
        //         if ($degree !== 0) {
        //             if ($mimeType === 'image/jpeg') {
        //                 imagejpeg(imagerotate($image, $degree, 0), $real_path);
        //             } else {
        //                 imagepng(imagerotate($image, $degree, 0), $real_path);
        //             }
        //         }
        //     }
        // }

        $new_filename = $this->genrerateFilename($file->getClientOriginalName());

        $container = $this->generateContainerName();

        if($file->getMimeType() == 'image/gif'){
            $image_url = $this->azure->upload($real_path, $new_filename, $container, 'image/gif');
        } else {
            $image_url = $this->azure->upload($real_path, $new_filename, $container, 'image/jpeg');
        }

        $ipfs = $this->_ipfsRepository->submitFileToIPFS($real_path);
    
        return ['image_url' => $image_url, 'filename' => $new_filename, 'container' => $container,'ipfs' => $ipfs];
    }    

    /**
     * Upload thumbnail
     *
     * @param   string  $filename
     * @param   object  $file
     * @param   string  $container
     * @return  string  $thumb_url
     */
    private function uploadThumb($filename, $file, $container)
    {
        $thumbnail_filename = $this->genrerateThumbnailNameFromFilename($filename); 
        
        $thumbnail_filepath = '/tmp/'.$thumbnail_filename;

        $this->createThumbnail($file->getRealPath(), $thumbnail_filepath);

        $thumb_url = $this->azure->upload($thumbnail_filepath, $thumbnail_filename, $container, $file->getMimeType());  

        return $thumb_url;      
    }

    /**
     * Save forum image (upload, log to DB, log to DynamoDB, log to DB-ByUrl)
     *
     * @param   Request  $request
     * @param   File     $file
     * @param   bool     $do_thumbnail  Optional, default false.
     * @return  array
     */
    public function save(Request $request, $file,$type = 'attach')
    {
        $image_results   = $this->upload($request, $file);
        $image_url       = $image_results['image_url'];  // This url will be used in forums, the url won't be able to change, but the image sometimes needs switching between watermarked image and unwatermarked image, using urls above
        $image_filename  = $image_results['filename'];
        $image_container = $image_results['container'];
        $ipfs = $image_results['ipfs'];
        
        $thumb_url = $image_url;

        try{
            if($file->getMimeType() != 'image/gif'){
                $thumb_url = $this->uploadThumb($image_filename, $file, $image_container);
            }
        }catch (\Exception $e) {
            \Log::error('create thum failed. '. $e->getMessage());
        }

        if($type == 'attach'){
            $db_id = $this->saveattachToDB($request, $image_url, $file, $thumb_url,$ipfs);
        } else if($type == 'avatar') {
            $db_id = $this->saveAvatarToDB($request, $image_url, $file, $thumb_url);
        } else if($type == 'group_pic'){
            //no need save to db   
            $db_id = -1;
        }

        return ['url' => $image_url, 'thumb_url' => $thumb_url, 'id' => (string) $db_id];
     
    }


      /**
     * Generate new image file name
     * 
     * @param  string  $original_filename 
     * @return string  new filename
     */
    public function genrerateFilename($original_filename)
    {
        $path_parts = pathinfo($original_filename);

        // Some app will send original file name like "tmpphoto-172957537jpg", which PHP can't pharse the extension correctlly:
        if (isset($path_parts['extension']) && strtolower($path_parts['extension']) != 'heic')
        {
            $original_postfix = strtolower($path_parts['extension']);
        }
        else
        {
            //TTLog::warning('[User Image Upload No Extension] original_filename:'.$original_filename.' - url:'.Request::url(), 'User');

            $original_postfix = 'jpg';
        }

        if (isset($this->image_width) && isset($this->image_height)) {
            $width_height = '_'.$this->image_width.'x'.$this->image_height;
        } else {
            $width_height = '';
        }

        return self::generateRandomString(16, false) . $width_height . '.' . $original_postfix;
    }


    /**
     * Resize a image and save the new one. Old one is still there, new one is saved in $new_filepath
     * 
     * @param  string  $original_filepath 
     * @param  string  $new_filepath     
     * @return object  Intervention\Image\Image
     */
    public function resize($original_filepath, $new_filepath, $resize_width_limit = self::RESIZE_WIDTH_LIMIT, $resize_height_limit = self::RESIZE_HEIGHT_LIMIT, $quality = self::DEFAULT_QUALITY)
    {
        $img = Image::make($original_filepath);

        $intervention_obj = $img->resize($resize_width_limit, $resize_height_limit, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($new_filepath, $quality);

        return $intervention_obj;
    }


    /**
     * Copied from tapatalk.com's generate_api_key()
     *
     * @return string  defalut upper-cased 32 bit random string, otherwise lower-cased
     *
     * @author Chenchao
     */
    public static function generateRandomString($length = 16, $upper_cased = true)
    {
        $bytes = openssl_random_pseudo_bytes($length, $csstrong);

        return $upper_cased ? strtoupper(bin2hex($bytes)) : bin2hex($bytes);
    }


    public function saveAttachedFile($file)
    {
        $file_uploaded = $this->uploadAttachedFile($file);
        
        $db_id = $this->saveAttachedFilesToDB($file_uploaded['file_url'], $file_uploaded['filename'], 
                                                $file->getSize(), $file_uploaded['mime_type'], $file_uploaded['ipfs']);

        return ['url' => $file_uploaded['file_url'], 'attached_file_id' => $db_id];
    }


    /**
     * Upload market image to azure
     *
     * @param   File     $file
     * @return  array
    */
    private function uploadAttachedFile($file)
    {
        $real_path = $file->getRealPath();

        $new_filename = $file->getClientOriginalName();

        $container = $this->generateContainerName();

        $mimeType = $file->getClientMimeType();

        $file_url = $this->azure->upload($real_path, $new_filename, $container, $mimeType);

        $ipfs = $this->_ipfsRepository->submitFileToIPFS($real_path);
    
        return [
            'file_url' => $file_url,
            'filename' => $new_filename,
            'container' => $container,
            'ipfs' => $ipfs,
            'mime_type' => $mimeType,
        ];
    }


    private function saveAttachedFilesToDB($file_url, $file_name, $size, $mime_type, $ipfs)
    {
        $record = new AttachedFiles;
        $record->url = $file_url;
        $record->name = $file_name;
        $record->size = $size;
        $record->mime_type = $mime_type;
        $record->ipfs = $ipfs;
        $record->save();

        return $record->id;
    }

}