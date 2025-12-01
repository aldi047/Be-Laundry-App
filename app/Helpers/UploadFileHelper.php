<?php

namespace App\Helpers;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;

class UploadFileHelper
{
    const PUBLIC_PATH = 'public';
    const GEOJSON_PATH = 'geojson';
    const STORAGE_PATH = 'uploads';
    const CERTIFICATE_PATH = 'sertifikat';
    const CONTOH_DOKUMEN_PATH = 'contoh_dokumen';
    const STORAGE_IMG_PATH = 'img';
    const NO_THUMBNAIL_FILENAME = 'no-thumbnail.jpg';
    const NO_THUMBNAIL_VIDEO_FILENAME = 'no-thumbnail-vid.jpg';
    const PDF_TO_IMAGE_PATH = "pdf_to_image";
    public static function encodeImageToBase64($imagePath)
    {
        // Check if the file exists
        if (!file_exists($imagePath)) {
            return null;
        }

        // Get the file contents
        $imageData = base64_encode(file_get_contents($imagePath));

        // Get the file's mime type
        $imageType = mime_content_type($imagePath);

        // Return the base64 string with the mime type
        return 'data:' . $imageType . ';base64,' . $imageData;
    }

    public static function getFile($filename)
    {
        try {
            $path = self::getLocalUrl($filename);
            if (!file_exists($path)) {
                // return file bukan response
                return false;
            }

            $file = file_get_contents($path);
            // dd($file);
            $type = mime_content_type($path);
            $size = filesize($path);
            $fileName = basename($path);
            $fileNameAsli = $filename;

            $returnData = [
                'file_name' => $fileName,
                'file_name_asli' => $fileNameAsli,
                // 'file_size' => $isConvertToMB ? ($fileSize / 1024) / 1024 : $fileSize,
                // 'file_ekstensi' => $fileEkstensi,
            ];
            return $returnData;
        } catch (\Exception $exception) {
            LogSystemHelper::errorLog($exception);
            return false;
        }
    }
    public static function uploadFile($filename, $file)
    {
        try {
            self::initStorageDirectory();
            // $cleanFilename = preg_replace("/[^a-zA-Z0-9.]/", "_", $filename);
            // $file->move(self::STORAGE_PATH, $cleanFilename);
            $file->move(self::STORAGE_PATH, $filename);
            return true;
        } catch (\Exception $exception) {
            LogSystemHelper::errorLog($exception);
            return false;
        }
    }

    public static function removeFile($filename)
    {
        try {
            unlink(self::getLocalUrl($filename));
            return true;
        } catch (\Exception $exception) {
            LogSystemHelper::errorLog($exception);
            return false;
        }
    }

    // use endpoin method
    public static function getUrlApiEndpoint($filename, $isImagePredefined = 0)
    {
        $isUseIndexPhp = intval(env('IS_USE_INDEX_PHP', 0));
        $arr = explode('.', $filename);
        if (count($arr) == 2) {
            $url = self::urlValidated(($isImagePredefined ? 'get-image' : 'get-file') . "/" . $arr[0] . "/" . $arr[1]);
            return $isUseIndexPhp == 1 ? $url : str_replace("/index.php", "", $url);
        }

        return self::getNoThumbnailUrlApiEndpoint();
    }

    public static function getNoThumbnailUrlApiEndpoint($isImage = 1)
    {
        $isUseIndexPhp = intval(env('IS_USE_INDEX_PHP', 0));
        $url = self::urlValidated('get-thumbnail') . (!$isImage ? '?is_video=1' : '');
        return $isUseIndexPhp == 1 ? $url : str_replace("/index.php", "", $url);
    }

    // use old version
    public static function getUrl($filename, $isImagePredefined = 0)
    {
        $url = self::urlValidated($isImagePredefined ? self::getImgLocalUrl($filename) : self::getLocalUrl($filename));
        return str_replace("/index.php", "", $url);
    }

    public static function getNoThumbnailUrl($isImage = 1)
    {
        $thumbnail = $isImage ? self::NO_THUMBNAIL_FILENAME : self::NO_THUMBNAIL_VIDEO_FILENAME;
        $url = self::urlValidated(self::getImgLocalUrl($thumbnail));
        return str_replace("/index.php", "", $url);
    }

    // real url
    public static function getRealUrl($filename, $isRoot = 0)
    {
        return self::publicPath() . TDS . self::getLocalUrl($filename, $isRoot);
    }

    public static function getRealImgUrl($filename)
    {
        return self::publicPath() . TDS . self::getImgLocalUrl($filename);
    }

    public static function getLocalUrl($filename, $isRoot = 0)
    {
        return $isRoot ? $filename : (self::STORAGE_PATH . TDS . $filename);
    }

    public static function getImgLocalUrl($filename)
    {
        return self::STORAGE_IMG_PATH . TDS . $filename;
    }

    private static function initStorageDirectory()
    {
        if (!file_exists(self::STORAGE_PATH)) {
            mkdir(self::STORAGE_PATH, 0777, true);
        }
    }

    private static function publicPath()
    {
        return base_path() . TDS . self::PUBLIC_PATH;
    }

    private static function urlValidated($urlPath)
    {
        $isUnderProxy = intval(env('IS_UNDER_PROXY', 0));
        $isHasStaticUrl = !empty(env('APP_URL')) && env('APP_URL') != 'http://localhost';
        if ($isHasStaticUrl) {
            $url = env('APP_URL') . '/' . $urlPath;
        } elseif ($isUnderProxy) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? '');
            $url = (UtilityHelper::isHttps() ? 'https:' : 'http:') . '/' . '/';
            $url .= $host;
            $url .= !UtilityHelper::isValidIpv4($host) ? env('PATH_REVERSE', '') . '/' : '';
            $url .= $_SERVER['SCRIPT_NAME'] . '/' . $urlPath;
        } else {
            $url = url($urlPath);
        }

        return UtilityHelper::removeDoubleSlashInUrl($url);
    }

    public static function getImgUrl($filename)
    {
        $url = self::urlValidated(self::getImgLocalUrl($filename));
        return str_replace("/index.php", "", $url);
    }

    public static function getImgUrlApiEndpoint($filename)
    {
        $isUseIndexPhp = intval(env('IS_USE_INDEX_PHP', 0));
        $arr = explode('.', $filename);
        if (count($arr) == 2) {
            $url = self::urlValidated('get-image' . "/" . $arr[0] . "/" . $arr[1]);
            return $isUseIndexPhp == 1 ? $url : str_replace("/index.php", "", $url);
        }

        return self::getNoThumbnailUrlApiEndpoint();
    }

    public static function initPublicDirectoryIfNotExists($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    public static function getNoThumbnailLocalUrl($isImage = 1)
    {
        $thumbnail = $isImage ? self::NO_THUMBNAIL_FILENAME : self::NO_THUMBNAIL_VIDEO_FILENAME;
        return self::STORAGE_IMG_PATH . TDS . $thumbnail;
    }
}
