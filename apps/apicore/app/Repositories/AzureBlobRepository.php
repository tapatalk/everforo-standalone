<?php
namespace App\Repositories;

use MicrosoftAzure\Storage\Common\ServicesBuilder;
use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
use MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use Exception;
/**
 * Azuer Blob repo
 * 
 * @author Chen Chao <chenchao@tapatalk.com>
 */
class AzureBlobRepository {


    /**
     * Create a container
     * 
     * @param  object $blobRestProxy [description]
     * @param  string $container     [description]
     * 
     * @return void        
     */
    private function createContainer($blobRestProxy, $container)
    {
        $createContainerOptions = new CreateContainerOptions(); 
        $createContainerOptions->setPublicAccess(PublicAccessType::BLOBS_ONLY);  // or : "PublicAccessType::CONTAINER_AND_BLOBS"

        try 
        {
            // Create container.
            $blobRestProxy->createContainer($container, $createContainerOptions);
        }
        catch(Exception $e)
        {
            // Handle exception based on error codes and messages.
            // Error codes and messages are here: 
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            
            // $code = $e->getCode();
            // $error_message = $e->getMessage();
            // echo $code.": ".$error_message."<br />";
        }        
    }

    /**
     * Upload images to azure.blob
     *
     * Refer to : https://azure.microsoft.com/en-us/documentation/articles/storage-php-how-to-use-blobs/
     *
     * @param string  $file_path      File to be upload
     * @param string  $new_file_name  New file
     * @param string  $container      Blob's container name
     * @param string  $new_mime       Image mime type ('image/jpeg' or 'image/gif') 
     * 
     * @return string image url
     */
    public function upload($file_path, $new_file_name, $container, $new_mime = 'image/jpeg')
    {
        if (env('APP_ENV') === 'local'){
            return "https://www.petmd.com/sites/default/files/petmd-kitten-facts.jpg";
        }

        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService(env('AZURE_BLOB_CONNECTION'));  // Create blob REST proxy.

        $content = fopen($file_path, 'r');
        try{

            $this->createContainer($blobRestProxy, $container);

        } catch(Exception $e){

        }
        // If not set, open the image url will download the image instead of opening it:
        $createBlobOptions = new CreateBlobOptions();
        $createBlobOptions->setContentType($new_mime);

        // Upload blob
        $blobRestProxy->createBlockBlob($container, $new_file_name, $content, $createBlobOptions);

        return env('AZURE_IMAGE_DOMAIN','')."/{$container}/{$new_file_name}";
    }

    /**
     * Download image, save image to $new_filepath
     *
     * @param   string  $source_container
     * @param   string  $source_blob
     * @param   string  $new_filepath
     * @return  void
     */
    public function download($source_container, $source_blob, $new_filepath)
    {
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService(config('api.azure.blob'));  // Create blob REST proxy.

        $blob = $blobRestProxy->getBlob($source_container, $source_blob);

        file_put_contents($new_filepath, $blob->getContentStream(), LOCK_EX);  // the LOCK_EX flag to prevent anyone else writing to the file at the same 
    }

    /**
     * Delete a blob file
     *
     * @param   string  $container
     * @param   string  $blob
     * @return  void
     */
    public function delete($container, $blob)
    {
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService(config('api.azure.blob'));  // Create blob REST proxy.

        $blobRestProxy->deleteBlob($container, $blob);
    }

    /**
     * Move a blob to a new location. ORIGIN WILL BE DELETED!
     *
     * @param   string  $original_container
     * @param   string  $original_blob
     * @param   string  $new_container
     * @param   string  $new_blob
     * @return  string  Url
     */
    public function move($original_container, $original_blob, $new_container, $new_blob)
    {
        $file_path = '/tmp/'.$original_blob;

        $this->download($original_container, $original_blob, $file_path);  
        
        $new_image_url = $this->upload($file_path, $new_blob, $new_container);

        $this->delete($original_container, $original_blob);

        return $new_image_url;
    }

    /**
     * Sof delete an image: move the image to new container, replace original image with a default image
     *
     * @param   string  $original_container
     * @param   string  $original_blob
     * @param   string  $new_container
     * @param   string  $new_blob
     * @param   string  $default_image
     * @return  string  Deleted image's url in a new container
     */
    public function softDeleteImage($original_container, $original_blob, $new_container, $new_blob, $default_image = '/path/to/deleted.png')
    {
        $default_image = base_path('resources/assets/deleted.png');

        $deleted_image_url = $this->move($original_container, $original_blob, $new_container, $new_blob);

        $this->upload($default_image, $original_blob, $original_container);

        return $deleted_image_url;
    }
}