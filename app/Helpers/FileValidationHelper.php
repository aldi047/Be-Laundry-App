<?php


namespace App\Helpers;


use Illuminate\Support\Facades\Validator;
use ZipArchive;

class FileValidationHelper
{
    const TIPE_FILE_TIDAK_DIDUKUNG = 'Tipe file tidak didukung dalam sistem ini!';
    const MESSAGE_VALIDASI_BERHASIL = 'Validasi file berhasil';
    const MIN_SIZE_IN_BYTES = 20;

    public static function validateFile($tipe, $file)
    {
        try {
            $fileNameAsli = $file->getClientOriginalName();

            $params = [
                'file' => $file
            ];

            $bunchOfTipe = is_array($tipe) ? $tipe : [$tipe];

            $bunchOfRules = [];
            foreach ($bunchOfTipe as $tipe) {
                switch (strtolower($tipe)) {
                    case 'document':
                        $rules = self::getDocumentRules();
                        break;
                    case 'document;image':
                        $rules = self::getDocumentImageRules();
                        break;
                    case 'image':
                        $rules = self::getImageRules();
                        break;
                    case 'spreadsheet':
                        $rules = self::getSpreadsheetRules();
                        break;
                    case 'word':
                        $rules = self::getWordRules();
                        break;
                    case 'powerpoint':
                        $rules = self::getPowerpointRules();
                        break;
                    case 'video':
                        $rules = self::getVideoRules();
                        break;
                    case 'audio':
                        $rules = self::getAudioRules();
                        break;
                    case 'text':
                        $rules = self::getTextRules();
                        break;
                    case 'zip':
                        $rules = self::getZipRules();
                        break;
                    case 'apk':
                        $rules = self::getApkRules();
                        break;
                    default:
                        return ResponseHelper::errorResponse(400, self::TIPE_FILE_TIDAK_DIDUKUNG);
                }
                $bunchOfRules[] = $rules;
            }

            if (count($bunchOfRules) == 1) {
                $rules = $bunchOfRules[0];
            } else {
                $rules = self::_mergeRules($bunchOfRules);
            }

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                $message = "";
                foreach ($validator->getMessageBag()->getMessages() as $num => $item) {
                    foreach ($item as $key => $value) {
                        $message .= "$num:$value <br>";
                    }
                }

                return ResponseHelper::errorResponse(400, $fileNameAsli . ' => ' . $message);
            }

            return ResponseHelper::successResponse(self::MESSAGE_VALIDASI_BERHASIL, true);
        } catch (\Exception $exception) {
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public static function isDoUploadFile($file)
    {
        $isInvalidUpload = !($file instanceof \Illuminate\Http\UploadedFile) ||
            $file->getSize() < self::MIN_SIZE_IN_BYTES ||
            empty($file->getClientOriginalExtension());

        return !$isInvalidUpload;
    }

    public static function getVideoRules()
    {
        return ['file' => 'mimes:mp4,3gp|max:' . (300 * 1024)];
    }
    public static function getApkRules()
    {
        return [
            'file' => 'required|mimetypes:application/vnd.android.package-archive,application/zip'
        ]; // Maksimal 50MB
    }


    public static function getAudioRules()
    {
        return ['file' => 'mimes:mp3,wav|max:' . (10 * 1024)];
    }

    public static function getDocumentImageRules()
    {
        return ['file' => 'mimes:pdf,jpeg,bmp,png,gif,jpg,webp|max:25000'];
    }
    public static function getDocumentRules()
    {
        return ['file' => 'mimes:pdf|max:25000'];
    }

    public static function getSpreadsheetRules()
    {
        return ['file' => 'mimes:xls,xlsx|max:' . (30 * 1024)];
    }

    public static function getWordRules()
    {
        return ['file' => 'mimes:doc,docx|max:' . (30 * 1024)];
    }

    public static function getPowerpointRules()
    {
        return ['file' => 'mimes:ppt,pptx|max:' . (30 * 1024)];
    }

    public static function getImageRules()
    {
        return ['file' => 'mimes:jpeg,bmp,png,gif,jpg,webp,svg|max:6400'];
    }

    public static function getTextRules()
    {
        return ['file' => 'mimes:txt,csv|max:25000'];
    }

    public static function getZipRules()
    {
        return ['file' => 'mimes:zip|max:' . (30 * 1024)];
    }

    public static function validateZipContentSecure($file, $deleteSourceIfNotSecure = true)
    {
        try {
            $isFromUploadFile = self::isDoUploadFile($file);
            if ($isFromUploadFile) {
                $zip = new ZipArchive;
                $res = $zip->open($file);
                $fileName = $file->getClientOriginalName();
            } else {
                $fileAsliPath = UploadFileHelper::STORAGE_PATH . TDS . $file;
                $zip = new ZipArchive;
                $res = $zip->open($fileAsliPath);
                $fileName = $file;
            }

            if ($res !== TRUE) {
                return ResponseHelper::errorResponse(400, 'File zip gagal divalidasi');
            }

            $folderHasilEkstrak = 'extracted_zip' . TDS . $fileName;
            $zip->extractTo($folderHasilEkstrak);
            $zip->close();

            $fileInsideZip = scandir($folderHasilEkstrak);
            $fileInsideZip = array_values(array_filter($fileInsideZip, fn($item) => !in_array($item, ['.', '..'])));

            $isZipSecure = true;
            foreach ($fileInsideZip as $name) {
                $exploded = UtilityHelper::cleanExplode($name, '.');
                $ekstensi = end($exploded);

                if (is_executable($folderHasilEkstrak . TDS . $name)) {
                    $isZipSecure = false;
                    break;
                } elseif (self::isExtensionNotAllowed($ekstensi)) {
                    $isZipSecure = false;
                    break;
                }
            }

            UtilityHelper::recursivelyDeleteFolder($folderHasilEkstrak);
            if (!$isZipSecure && $deleteSourceIfNotSecure && !$isFromUploadFile) {
                unlink($fileAsliPath);
            }

            if (!$isZipSecure) {
                return ResponseHelper::errorResponse(400, 'File zip tidak boleh mengandung file executable');
            }

            return ResponseHelper::successResponse(self::MESSAGE_VALIDASI_BERHASIL, true);
        } catch (\Exception $exception) {
            return ResponseHelper::serverErrorResponse($exception);
        }
    }

    public static function isExtensionNotAllowed($ekstensi)
    {
        return in_array($ekstensi, ['exe', 'bat', 'cmd', 'vbs', 'js', 'ps1', 'dll', 'hta', 'jar', 'reg', 'scr', 'cpp', 'php', 'aspx', 'sql', 'iso', 'html', 'css', 'swf', 'py', 'rb', 'cgi', 'sh', 'msi', 'ocx', 'sys', 'drv', 'cpl', 'msp', 'ink', 'pif', 'msc', 'mst', 'com']);
    }

    private static function _mergeRules($bunchOfRules)
    {
        $mimes = [];
        $max = 0;
        foreach ($bunchOfRules as $val) {
            $explodedRule = UtilityHelper::cleanExplode($val['file'], '|');
            foreach ($explodedRule as $rule) {
                $explodedSubRule = UtilityHelper::cleanExplode($rule, ':');
                $namaRule = $explodedSubRule[0];
                $valRule = $explodedSubRule[1];
                switch ($namaRule) {
                    case 'mimes':
                        $mimes = array_merge($mimes, UtilityHelper::cleanExplode($valRule, ','));
                        break;
                    case 'max':
                        $max = intval($valRule) > $max ? intval($valRule) : $max;
                        break;
                }
            }
        }

        $mimes = implode(',', array_unique($mimes));
        return ['file' => "mimes:$mimes|max:$max"];
    }
}
