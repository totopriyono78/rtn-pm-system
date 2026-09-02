<?php

namespace App\Support;

/**
 * Konversi angka menjadi rangkaian kata Bahasa Indonesia (terbilang),
 * dipakai pada dokumen cetak resmi (mis. Surat Penawaran Harga) untuk
 * menuliskan nilai total dalam bentuk "... Rupiah".
 */
class NumberToWords
{
    /** @var array<int, string> */
    private const ANGKA = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas',
    ];

    public static function terbilang(int $number): string
    {
        if ($number === 0) {
            return 'nol';
        }

        if ($number < 0) {
            return 'minus '.self::terbilang(abs($number));
        }

        return trim(preg_replace('/\s+/', ' ', self::convert($number)));
    }

    /**
     * Format nilai rupiah menjadi kalimat "... Rupiah" dengan huruf awal kapital.
     */
    public static function rupiah(float|int $amount): string
    {
        return ucwords(self::terbilang((int) round($amount))).' Rupiah';
    }

    private static function convert(int $x): string
    {
        if ($x < 12) {
            return ' '.self::ANGKA[$x];
        }

        if ($x < 20) {
            return self::convert($x - 10).' belas';
        }

        if ($x < 100) {
            return self::convert(intdiv($x, 10)).' puluh'.self::convert($x % 10);
        }

        if ($x < 200) {
            return ' seratus'.self::convert($x - 100);
        }

        if ($x < 1000) {
            return self::convert(intdiv($x, 100)).' ratus'.self::convert($x % 100);
        }

        if ($x < 2000) {
            return ' seribu'.self::convert($x - 1000);
        }

        if ($x < 1_000_000) {
            return self::convert(intdiv($x, 1000)).' ribu'.self::convert($x % 1000);
        }

        if ($x < 1_000_000_000) {
            return self::convert(intdiv($x, 1_000_000)).' juta'.self::convert($x % 1_000_000);
        }

        if ($x < 1_000_000_000_000) {
            return self::convert(intdiv($x, 1_000_000_000)).' milyar'.self::convert($x % 1_000_000_000);
        }

        return self::convert(intdiv($x, 1_000_000_000_000)).' triliun'.self::convert($x % 1_000_000_000_000);
    }
}
