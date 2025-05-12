<?php

namespace App\Helpers;

class TerbilangHelper
{
    public static function terbilang($angka)
    {
        $angka = abs($angka);
        $baca = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $terbilang = "";

        if ($angka < 12) {
            $terbilang = $baca[$angka];
        } else if ($angka < 20) {
            $terbilang = $baca[$angka - 10] . " Belas";
        } else if ($angka < 100) {
            $terbilang = $baca[floor($angka / 10)] . " Puluh " . self::terbilang($angka % 10);
        } else if ($angka < 200) {
            $terbilang = "Seratus " . self::terbilang($angka - 100);
        } else if ($angka < 1000) {
            $terbilang = $baca[floor($angka / 100)] . " Ratus " . self::terbilang($angka % 100);
        } else if ($angka < 2000) {
            $terbilang = "Seribu " . self::terbilang($angka - 1000);
        } else if ($angka < 1000000) {
            $terbilang = self::terbilang(floor($angka / 1000)) . " Ribu " . self::terbilang($angka % 1000);
        } else if ($angka < 1000000000) {
            $terbilang = self::terbilang(floor($angka / 1000000)) . " Juta " . self::terbilang($angka % 1000000);
        } else if ($angka < 1000000000000) {
            $terbilang = self::terbilang(floor($angka / 1000000000)) . " Miliar " . self::terbilang($angka % 1000000000);
        }

        return trim(preg_replace('/\s+/', ' ', $terbilang)); // Menghapus spasi berlebih
    }
}
