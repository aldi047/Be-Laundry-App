<?php


namespace App\Helpers;


use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LogSystemHelper
{
    /** @var \Exception $error */
    public static function errorLog($error)
    {
        $pesan = "Terjadi kesalahan pada server!\n";
        $pesan .= "File : ".$error->getFile()."\n";
        $pesan .= "Baris : ".$error->getLine()."\n";
        $pesan .= "Pesan Kesalahan : ".$error->getMessage()."\n";
        $pesan .= "Waktu dalam GMT+7 : ".Carbon::now();

        Log::error($pesan);
    }

    public static function stringLogError($message, $event = null)
    {
        $pesan = $message."\n";
        $pesan .= 'Event : '.is_null($event) ? 'tidak-terdefinisi' : $event;
        $pesan .= "Waktu dalam GMT+7 : ".Carbon::now()->addHours(7);

        Log::error($pesan);
    }
}
