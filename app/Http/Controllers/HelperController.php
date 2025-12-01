<?php

namespace App\Http\Controllers;

use App\Helpers\LogSystemHelper;
use App\Helpers\UploadFileHelper;

class HelperController extends Controller
{
        /**
     * @param string $fileName nama filenya
     * @param string $extension extensi file
     * @return resource
     */
    public function getFile($fileName, $extension)
    {
        try {
            if ($fileName . '.' . $extension == UploadFileHelper::NO_THUMBNAIL_FILENAME) {
            return $this->getThumbnail();
            }

            $downloadThumbnail = in_array($extension, ['jpeg', 'bmp', 'png', 'gif', 'jpg', 'webp']);

            return !file_exists(UploadFileHelper::getRealUrl($fileName . '.' . $extension)) ?
                ($downloadThumbnail ? $this->getThumbnail() : null) :
                response()->download(UploadFileHelper::getRealUrl($fileName . '.' . $extension));
        } catch (\Exception $e) {
            LogSystemHelper::errorLog($e);
        }
    }

    /**
     * @return resource
     */
    public function getThumbnail()
    {
        $isVideo = request()->input('is_video', 0);
        $isImage = intval($isVideo) !== 1;
        return response()->download(UploadFileHelper::getNoThumbnailLocalUrl($isImage));
    }
}