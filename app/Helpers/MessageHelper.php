<?php

namespace App\Helpers;

class MessageHelper
{
    public static function errorRequired($namaField)
    {
        return "Silakan mengisi input $namaField terlebih dahulu!";
    }

    public static function errorNotFound($keyword)
    {
        return "Data $keyword tidak ditemukan!";
    }

    public static function errorNotMatch($keyword1, $keyword2 = '')
    {
        if (strlen($keyword2) == 0) {
            return "Data $keyword1 tidak sesuai!";
        } else {
            return "Data $keyword1 tidak sesuai dengan $keyword2";
        }
    }

    public static function errorNotActive($keyword)
    {
        return ucfirst($keyword) . " saat ini sedang tidak tersedia / tidak aktif";
    }

    public static function errorNotAllowed($keyword)
    {
        return ucfirst($keyword) . " tidak diizinkan untuk mengakses!";
    }

    public static function errorUpload($keyword = '')
    {
        return "File gagal diupload!" . (strlen($keyword) > 0 ? ' Karena $keyword' : '');
    }

    public static function errorDuplicate($keyword)
    {
        return "Data $keyword sudah ada!";
    }

    public static function errorInvalid($keyword)
    {
        return "Data $keyword tidak valid!";
    }

    public static function errorHasChild($keyword)
    {
        return "Data $keyword memiliki child!";
    }

    public static function errorExpired($keyword)
    {
        return ucfirst($keyword) . " sudah tidak berlaku!";
    }

    public static function errorRelation($keyword)
    {
        return "Data memiliki relasi dengan $keyword!";
    }

    public static function errorComparison($keyword, $status, $keyword2)
    {
        return "Data $keyword $status dari $keyword2!";
    }

    public static function successCreated($keyword)
    {
        return "Data $keyword berhasil dibuat!";
    }

    public static function successLogin($keyword)
    {
        return "Login $keyword berhasil!";
    }

    public static function successFound($keyword)
    {
        return "Data $keyword berhasil ditemukan!";
    }

    public static function successStored($keyword)
    {
        return "Data $keyword berhasil disimpan!";
    }

    public static function successRemoved($keyword)
    {
        return "Data $keyword berhasil dihapus!";
    }

    public static function successValidated($keyword)
    {
        return "Data $keyword berhasil divalidasi!";
    }

    public static function unauthorized()
    {
        return "Silakan melakukan authentifikasi terlebih dahulu!";
    }
}
