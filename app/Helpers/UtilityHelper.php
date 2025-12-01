<?php

namespace App\Helpers;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use NumberFormatter;

class UtilityHelper
{
    /**
     * @param string $file nama file
     * @param boolean $responseThumbnail jika ingin respon no thumbnail jika tidak ditemukan
     * @param boolean $isImageThumbnail jika ingin respon no thumbnail image, else video
     * @return string|null
     * @example "api\app\Resources\Video\DaftarVideoResource.php" UtilityHelper::fileUrlAttribute($response->file, 1)
     */
    public static function fileUrlAttribute($file, $responseThumbnail = 0, $isImageThumbnail = 1, $isImagePredefined = 0)
    {
        if (empty($file)) {
            return $responseThumbnail ? ($isImageThumbnail ? UploadFileHelper::getNoThumbnailUrlApiEndpoint() : UploadFileHelper::getNoThumbnailUrlApiEndpoint(false)) : null;
        }

        $url = UploadFileHelper::getUrlApiEndpoint($file, $isImagePredefined);
        $urlLocal = $isImagePredefined ? UploadFileHelper::getImgLocalUrl($file) : UploadFileHelper::getLocalUrl($file);
        if (file_exists($urlLocal)) {
            return $url;
        }

        return $responseThumbnail ? ($isImageThumbnail ? UploadFileHelper::getNoThumbnailUrlApiEndpoint() : UploadFileHelper::getNoThumbnailUrlApiEndpoint(false)) : null;
    }

    /**
     * @param string $file nama file
     * @param boolean $responseThumbnail jika ingin respon no thumbnail jika tidak ditemukan
     * @param boolean $isImageThumbnail jika ingin respon no thumbnail image, else video
     * @return string|null
     * @example "api\app\Resources\Video\DaftarVideoResource.php" UtilityHelper::fileLinkAttribute($response->file, 1)
     */
    public static function fileLinkAttribute($file, $responseThumbnail = 0, $isImageThumbnail = 1, $isImagePredefined = 0)
    {
        if (empty($file)) {
            return $responseThumbnail ? ($isImageThumbnail ? UploadFileHelper::getNoThumbnailUrl() : UploadFileHelper::getNoThumbnailUrl(false)) : null;
        }

        $url = UploadFileHelper::getUrl($file, $isImagePredefined);
        $urlLocal = $isImagePredefined ? UploadFileHelper::getImgLocalUrl($file) : UploadFileHelper::getLocalUrl($file);
        if (file_exists($urlLocal)) {
            return $url;
        }

        return $responseThumbnail ? ($isImageThumbnail ? UploadFileHelper::getNoThumbnailUrl() : UploadFileHelper::getNoThumbnailUrl(false)) : null;
    }

    /**
     * @param object|null $fileObj object file yang diupload
     * @param array $typeFile array type file yang diupload untuk validasi, contoh ['image', 'document, 'video']
     * @param boolean $isResponLengkap mereturn file_name_asli & file_size juga
     * @param boolean $isConvertToMB convert file_size ke MB
     * @param boolean $isSaveAsOriginalName save file yang diupload menggunakan nama asli
     * @param boolean $stopNonExecutable stop executable file
     * @return array
     * @example "api\app\Repositories\Main\BahanAjarFilePendukungRepository.php" UtilityHelper::validateAndUploadFile($file, ['spreadsheet','word','powerpoint','zip','document'], isSaveAsOriginalName:true)
     */
    public static function validateAndUploadFile($fileObj = null, $typeFile = [], $isResponLengkap = false, $isConvertToMB = true, $isSaveAsOriginalName = false, $stopNonExecutable = true)
    {
        $fileName = null;
        $fileNameAsli = null;
        $fileSize = null;
        $fileEkstensiAsli = null;
        if (!empty($fileObj)) {
            $isUploadFile = FileValidationHelper::isDoUploadFile($fileObj);
            if ($isUploadFile) {
                $fileNameAsli = $fileObj->getClientOriginalName();
                $fileEkstensiAsli = $fileObj->getClientOriginalExtension();
                $fileSize = floatval($fileObj->getSize());
                $fileMime = $fileObj->getMimeType();
                if ($stopNonExecutable && FileValidationHelper::isExtensionNotAllowed($fileEkstensiAsli)) {
                    return ResponseHelper::errorResponse(400, "File '$fileNameAsli' tidak diizinkan");
                }

                if (strpos($fileMime, 'json') !== false && $fileEkstensiAsli != 'json') {
                    return ResponseHelper::errorResponse(400, "File '$fileNameAsli' rusak, silakan ganti dengan file lain");
                }

                $validasiFileObj = FileValidationHelper::validateFile($typeFile, $fileObj);
                if ($validasiFileObj['code'] != 200) {
                    return ResponseHelper::errorResponse(400, $validasiFileObj['message']);
                }

                $extractEkstensiFromMime = self::cleanExplode($fileMime, '/')[1] ?? null;
                if (xstrlen($extractEkstensiFromMime) > 5) {
                    $extractEkstensiFromMime = null;
                }

                $fileEkstensi = ($extractEkstensiFromMime ?? $fileEkstensiAsli);
                if (in_array('apk', $typeFile)) {
                    $fileEkstensi = ($fileEkstensiAsli ?? $extractEkstensiFromMime);
                }

                if ($fileEkstensi == 'zip') {
                    $validasiZip = FileValidationHelper::validateZipContentSecure($fileObj);
                    if ($validasiZip['code'] != 200) {
                        return ResponseHelper::errorResponse(400, $validasiZip['message']);
                    }
                }

                $fileName = $isSaveAsOriginalName ? time() . "_" . $fileNameAsli : time() . "_" . uniqid() . "." . $fileEkstensi;
                $uploadResult = UploadFileHelper::uploadFile($fileName, $fileObj);
                if (!$uploadResult) {
                    return ResponseHelper::errorResponse(400, MessageHelper::errorUpload());
                }
            }
        }

        $returnData = $isResponLengkap ? [
            'file_name' => $fileName,
            'file_name_asli' => $fileNameAsli,
            'file_size' => $isConvertToMB ? ($fileSize / 1024) / 1024 : $fileSize,
            'file_ekstensi' => $fileEkstensiAsli
        ] : $fileName;

        return ResponseHelper::successResponse(MessageHelper::successFound('file'), $returnData);
    }

    /**
     * @param string $file filepath
     * @param float $widthBaru lebar
     * @param float $heightBaru tinggi
     * @param boolean $crop true jika crop tengah image
     * @return resource
     */
    public static function resizeImage($file, $widthBaru, $heightBaru, $crop = false)
    {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $widthBaru / $heightBaru)));
            } else {
                $height = ceil($height - ($height * abs($r - $widthBaru / $heightBaru)));
            }
            $newwidth = $widthBaru;
            $newheight = $heightBaru;
        } else {
            if ($widthBaru / $heightBaru > $r) {
                $newwidth = $heightBaru * $r;
                $newheight = $heightBaru;
            } else {
                $newheight = $widthBaru / $r;
                $newwidth = $widthBaru;
            }
        }
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return $dst;
    }

    /**
     * @param string $dir dir path
     */
    public static function recursivelyDeleteFolder($dir)
    {
        if (is_dir($dir)) {
            $bunchOfObjek = scandir($dir);
            foreach ($bunchOfObjek as $objek) {
                if ($objek != "." && $objek != "..") {
                    if (is_dir($dir . TDS . $objek) && !is_link($dir . "/" . $objek)) {
                        self::recursivelyDeleteFolder($dir . TDS . $objek);
                    } else {
                        unlink($dir . TDS . $objek);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * @param string|array $data variable yang mau dicek
     * @return mixed|null
     */
    public static function ifNullEmptyReturnNullElseData($data)
    {
        $kondisiBenar = !is_null($data) && !empty($data);
        return $kondisiBenar ? $data : null;
    }

    /**
     * @param string $stringData data yang mau di explode
     * @param string|array $separator separator array
     * @return array
     */
    public static function cleanExplode($stringData, $separator = ';')
    {
        if (is_array($separator)) {
            $arraySep = $separator;
            foreach ($arraySep as $sep) {
                if (strpos($stringData, $sep) > -1) {
                    $separator = $sep;
                    break;
                }
            }

            if (is_array($separator)) {
                $separator = $separator[0];
            }
        }

        $tempArray = explode($separator, $stringData);
        $trimmedArray = array_map('trim', $tempArray);
        $cleanedArray = array_filter($trimmedArray, fn($item) => !is_null($item) && $item !== '');
        return array_values($cleanedArray);
    }

    /**
     * @param array $arrayData array data yang mau divalidasi key nya
     * @param array $requiredKolom key yang mau di cek harus tersedia di array
     * @return boolean
     */
    public static function validateArrayKeyRequired($arrayData, $requiredKolom)
    {
        if (count($arrayData) == 0) {
            return false;
        }

        $isValid = true;
        foreach ($arrayData as $elemen) {
            $keys = array_keys($elemen);
            foreach ($requiredKolom as $data) {
                if (!in_array($data, $keys)) {
                    $isValid = false;
                    break;
                }
            }

            if (!$isValid) {
                break;
            }
        }

        return $isValid;
    }

    /**
     * @param array $params base params nya
     * @param string $value isi array params
     * @param int $xstrlenVal nilai xstrlen val
     * @return boolean
     */
    public static function issetAndxstrlen($params, $value, $xstrlenVal = 0)
    {
        return isset($params[$value]) && xstrlen($params[$value]) > $xstrlenVal;
    }

    /**
     * @param string $url url lengkap, bisa mengandung http ataupun tidak
     * @return string
     */
    public static function removeDoubleSlashInUrl($url)
    {
        $url = str_replace('//', '/', $url);
        $url = str_replace(['http:/', 'https:/'], ['http://', 'https://'], $url);
        return $url;
    }

    /**
     * @param string $url url lengkap, bisa mengandung http ataupun tidak
     * @return string
     */
    public static function removePathReverseInUrl($url)
    {
        if (xstrlen(env('PATH_REVERSE', '')) > 0) {
            $url = str_replace(env('PATH_REVERSE'), '', $url);
        }

        return self::removeDoubleSlashInUrl($url);
    }

    /**
     * @param string $string string yang akan di test
     * @return boolean
     */
    public static function isValidIpv4($string)
    {
        return preg_match("/^(?:(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])(\\.(?!$)|$)){4}$/", $string);
    }

    /**
     * @return boolean
     */
    public static function isHttps()
    {
        if (xstrlen(env('IS_HTTPS_CONTENT', '')) > 0) {
            return intval(env('IS_HTTPS_CONTENT'));
        }

        $kondisiBenar = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ||
            (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443 || $_SERVER['SERVER_PORT'] == 8443)) ||
            strpos(url(), 'https://') !== false;

        return $kondisiBenar;
    }

    /**
     * @param object $query query builder objek
     * @param array $kolomFilter array kolom pada db, misal ['jumlah_min', 'jumlah_max']
     * @param array $rangeFilter array range angka, misal [100, 200]
     * @return void
     */
    public static function filterRangeAngkaWith2Column(&$query, $kolomFilter = [], $rangeFilter = [])
    {
        if (count($kolomFilter) != 2 || count($rangeFilter) != 2) {
            return;
        }

        $kolomMin = $kolomFilter[0];
        $kolomMax = $kolomFilter[1];

        $rangeMin = $rangeFilter[0];
        $rangeMax = $rangeFilter[1];

        if (xstrlen($rangeMin) > 0 && xstrlen($rangeMax) > 0) {
            $rangeMin = intval($rangeMin);
            $rangeMax = intval($rangeMax);

            $query->where(function ($query) use ($rangeMin, $rangeMax, $kolomMin, $kolomMax) {
                $query->orWhere($kolomMin, '>=', $rangeMin);
                $query->orWhere($kolomMax, '<=', $rangeMax);
            });

            $query->where(function ($query) use ($rangeMin, $rangeMax, $kolomMin, $kolomMax) {
                $query->where(function ($query) use ($rangeMin, $rangeMax, $kolomMin) {
                    $query->where($kolomMin, '>=', $rangeMin);
                    $query->where($kolomMin, '<=', $rangeMax);
                });
                $query->orWhere(function ($query) use ($rangeMin, $rangeMax, $kolomMax) {
                    $query->where($kolomMax, '>=', $rangeMin);
                    $query->where($kolomMax, '<=', $rangeMax);
                });
            });
        } elseif ((xstrlen($rangeMin) > 0 && xstrlen($rangeMax) == 0) || (xstrlen($rangeMin) == 0 && xstrlen($rangeMax) > 0)) {
            if (xstrlen($rangeMin) > 0) {
                $query->where($kolomMin, '>=', intval($rangeMin));
            }

            if (xstrlen($rangeMax) > 0) {
                $query->where($kolomMax, '<=', intval($rangeMax));
            }
        }

        return;
    }

    /**
     * @param object $query query builder objek
     * @param array $kolomFilter array kolom pada db, misal ['tanggal_min', 'tanggal_max']
     * @param array $rangeFilter array range tanggal Y-m-d, misal ['2020-01-02', '2020-01-10']
     * @return void
     */
    public static function filterRangeTanggalWith2Column(&$query, $kolomFilter = [], $rangeFilter = [])
    {
        if (count($kolomFilter) != 2 || count($rangeFilter) != 2) {
            return;
        }

        $kolomMin = $kolomFilter[0];
        $kolomMax = $kolomFilter[1];

        $rangeMin = \Carbon\Carbon::parse($rangeFilter[0])->toDateString();
        $rangeMax = \Carbon\Carbon::parse($rangeFilter[1])->toDateString();

        if (xstrlen($rangeMin) > 0 && xstrlen($rangeMax) > 0) {
            $period = \Carbon\CarbonPeriod::create($rangeMin, $rangeMax);
            $periodeBetween = [];
            foreach ($period as $date) {
                $periodeBetween[] = $date->format('Y-m-d');
            }

            $query->where(function ($query) use ($rangeMin, $rangeMax, $periodeBetween, $kolomMax, $kolomMin) {
                $query->whereIn($kolomMax, $periodeBetween);
                $query->orWhereIn($kolomMin, $periodeBetween);
                $query->orWhere(function ($sql) use ($rangeMin, $rangeMax, $kolomMax, $kolomMin) {
                    $sql->where($kolomMin, '<', $rangeMin);
                    $sql->where($kolomMax, '>', $rangeMax);
                });
            });
        } elseif ((xstrlen($rangeMin) > 0 && xstrlen($rangeMax) == 0) || (xstrlen($rangeMin) == 0 && xstrlen($rangeMax) > 0)) {
            if (xstrlen($rangeMin) > 0) {
                $query->where($kolomMin, '>=', $rangeMin);
            }

            if (xstrlen($rangeMax) > 0) {
                $query->where($kolomMax, '<=', $rangeMax);
            }
        }

        return;
    }
    public static function formattedRupiah($ammount)
    {
        $formatter = new NumberFormatter('id_ID', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($ammount ?? 0, 'IDR');
    }

    public static function rangeToIncrement($start, $end)
    {
        $increment = $end - $start;
        if ($increment == 0) {
            return strval(0);
        }

        return strval($increment > 0 ? '+' . $increment : $increment);
    }
}
