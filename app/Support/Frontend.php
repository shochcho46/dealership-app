<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

/** Small formatting helpers for the public website views. */
class Frontend
{
    private const BN_DIGITS = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
        '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];

    public static function isBn(): bool
    {
        return App::getLocale() === 'bn';
    }

    /** Localise digits in any string/number. */
    public static function num($value): string
    {
        $value = (string) $value;
        return self::isBn() ? strtr($value, self::BN_DIGITS) : $value;
    }

    /** ৳ amount without trailing ".00". */
    public static function money(?float $amount): string
    {
        if ($amount === null) {
            return '';
        }
        $formatted = number_format($amount, 2);
        $formatted = preg_replace('/\.00$/', '', $formatted);
        return '৳' . self::num($formatted);
    }

    /** "+880 1711-240255" -> "8801711240255" for wa.me links. */
    public static function waNumber(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($digits, '01')) {
            $digits = '88' . $digits;
        }
        return $digits;
    }

    public static function waLink(?string $phone, string $text = ''): string
    {
        return 'https://wa.me/' . self::waNumber($phone) . ($text !== '' ? '?text=' . rawurlencode($text) : '');
    }

    public static function tel(?string $phone): string
    {
        return 'tel:+' . self::waNumber($phone);
    }
}
