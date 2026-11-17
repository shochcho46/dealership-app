<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Phonetic English -> Bangla script transliteration
 * (e.g. "Rahim Store" -> "রহিম স্টোর") using Google Input Tools.
 */
class BanglaTransliterator
{
    private const ENDPOINT = 'https://inputtools.google.com/request';

    /**
     * Common shop/business words that Google tends to get wrong.
     * Keys are lowercase English; extend as needed.
     */
    private const DICTIONARY = [
        'store' => 'স্টোর', 'stores' => 'স্টোর', 'shop' => 'শপ', 'mart' => 'মার্ট',
        'enterprise' => 'এন্টারপ্রাইজ', 'enterprises' => 'এন্টারপ্রাইজ',
        'traders' => 'ট্রেডার্স', 'trader' => 'ট্রেডার্স', 'trading' => 'ট্রেডিং',
        'telecom' => 'টেলিকম', 'electronics' => 'ইলেকট্রনিক্স', 'electric' => 'ইলেকট্রিক',
        'mobile' => 'মোবাইল', 'center' => 'সেন্টার', 'centre' => 'সেন্টার',
        'corner' => 'কর্নার', 'point' => 'পয়েন্ট', 'house' => 'হাউজ', 'gallery' => 'গ্যালারি',
        'zone' => 'জোন', 'world' => 'ওয়ার্ল্ড', 'plaza' => 'প্লাজা', 'market' => 'মার্কেট',
        'general' => 'জেনারেল', 'variety' => 'ভ্যারাইটি', 'fashion' => 'ফ্যাশন',
        'bhandar' => 'ভান্ডার', 'bitan' => 'বিতান', 'bitaan' => 'বিতান', 'ghar' => 'ঘর',
        'medical' => 'মেডিকেল', 'pharmacy' => 'ফার্মেসি', 'hall' => 'হল', 'agency' => 'এজেন্সি',
        'gate' => 'গেট', 'and' => 'অ্যান্ড', '&' => 'অ্যান্ড',
        // places / local words
        'more' => 'মোড়', 'purbogate' => 'পূর্বগেট', 'railgate' => 'রেলগেট', 'para' => 'পাড়া', 'engineerpara' => 'ইঞ্জিনিয়ারপাড়া', 'becon' => 'বেকন', 'zara' => 'জারা',
        'chattar' => 'চত্বর', 'ramc' => 'রমেক', 'dcr' => 'ডিসির', 'mosque' => 'মসজিদ',
        'underground' => 'আন্ডারগ্রাউন্ড', 'beside' => 'পাশে',
        // business words
        'bank' => 'ব্যাংক', 'cafe' => 'ক্যাফে', 'tea' => 'টি', 'stall' => 'স্টল', 'mill' => 'মিল',
        'grand' => 'গ্র্যান্ড', 'chinese' => 'চাইনিজ', 'restaurant' => 'রেস্টুরেন্ট',
        'laundry' => 'লন্ড্রি', 'laudry' => 'লন্ড্রি', 'bakery' => 'বেকারি', 'bakey' => 'বেকারি',
        'varaitise' => 'ভ্যারাইটিজ', 'stor' => 'স্টোর', 'sop' => 'শপ', 'scientific' => 'সায়েন্টিফিক',
        'medicate' => 'মেডিকেল', 'sweet' => 'সুইট', 'easy' => 'ইজি',
        // names / common words Google gets wrong
        'doa' => 'দোয়া', 'ridoy' => 'হৃদয়', 'fuska' => 'ফুচকা', 'said' => 'সাঈদ',
        'bekery' => 'বেকারি', 'bekhari' => 'বেকারি', 'bake' => 'বেক', 'kafe' => 'ক্যাফে',
        'selun' => 'সেলুন', 'saloon' => 'সেলুন', 'confectionary' => 'কনফেকশনারি',
        'confectionery' => 'কনফেকশনারি', 'canteen' => 'ক্যান্টিন', 'cantin' => 'ক্যান্টিন',
        'burger' => 'বার্গার', 'sweets' => 'সুইটস', 'birani' => 'বিরিয়ানি',
        'varitise' => 'ভ্যারাইটিজ', 'veritise' => 'ভ্যারাইটিজ', 'varaitig' => 'ভ্যারাইটিজ',
        'mor' => 'মোড়', 'parker' => 'পার্কের', 'chawkbazar' => 'চকবাজার',
        'duya' => 'দোয়া', 'doya' => 'দোয়া', 'haider' => 'হায়দার', 'raian' => 'রায়ান',
        'tusher' => 'তুষার', 'kara' => 'কারা',
    ];

    /** Titles: matched case-sensitively so all-caps "MR"/"MD" are spelled as abbreviations. */
    private const TITLES = [
        'Mr' => 'মি.', 'MR.' => 'মি.', 'Md' => 'মো.', 'Dr' => 'ডা.', 'Govt' => 'সরকারি',
    ];

    /** English letter names, used to spell out abbreviations (DSR -> ডিএসআর). */
    private const LETTERS = [
        'A' => 'এ', 'B' => 'বি', 'C' => 'সি', 'D' => 'ডি', 'E' => 'ই', 'F' => 'এফ', 'G' => 'জি',
        'H' => 'এইচ', 'I' => 'আই', 'J' => 'জে', 'K' => 'কে', 'L' => 'এল', 'M' => 'এম', 'N' => 'এন',
        'O' => 'ও', 'P' => 'পি', 'Q' => 'কিউ', 'R' => 'আর', 'S' => 'এস', 'T' => 'টি', 'U' => 'ইউ',
        'V' => 'ভি', 'W' => 'ডাব্লিউ', 'X' => 'এক্স', 'Y' => 'ওয়াই', 'Z' => 'জেড',
    ];

    /** Compound place-name suffixes: "Purbogate" -> Google("Purbo") + গেট. */
    private const SUFFIXES = ['gate' => 'গেট'];

    public static function toBangla(?string $text): ?string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }

        // Split into words + whitespace; dictionary words are replaced directly,
        // runs of other words are transliterated together (better context).
        // "3 No Gate" -> "3no Gate" so the "<n>no" rule applies.
        $text = preg_replace('/\b(\d+)\s+no\b\.?/i', '$1no', $text);

        // Hyphens are separators too (e.g. "Hasan Mart-Babupara").
        $tokens = preg_split('/(\s+|-)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';
        $pending = '';
        $ok = true;

        $flush = function () use (&$out, &$pending, &$ok) {
            if (trim($pending, " \t\n\r-") === '') {
                $out .= $pending;
                $pending = '';
                return;
            }
            // Keep leading/trailing whitespace/hyphens of the run as-is.
            preg_match('/^([\s-]*)(.*?)([\s-]*)$/su', $pending, $m);
            $bn = self::convertRun($m[2]);
            if ($bn === null) {
                $ok = false;
            }
            $out .= $m[1] . ($bn ?? $m[2]) . $m[3];
            $pending = '';
        };

        foreach ($tokens as $token) {
            $fixed = self::fixedToken($token);
            if ($fixed !== null) {
                $flush();
                $out .= $fixed;
            } else {
                $pending .= $token;
            }
        }
        $flush();

        $out = self::toBanglaDigits($out);

        // Fully failed (nothing converted) -> null so callers can fall back.
        return ($ok || preg_match('/\p{Bengali}/u', $out)) ? $out : null;
    }

    /**
     * Rule-based replacement for a single token, or null to let Google handle it.
     */
    private static function fixedToken(string $token): ?string
    {
        $clean = trim($token, '.,');
        if ($clean === '') {
            return null;
        }

        // 1. Title ("Mr.", "Md.", "Dr.", "Govt.") - case-sensitive.
        if (isset(self::TITLES[$clean])) {
            return self::TITLES[$clean];
        }

        // 2. Dictionary word (includes "&").
        $key = strtolower($clean);
        if (isset(self::DICTIONARY[$key])) {
            return self::DICTIONARY[$key];
        }

        if (! preg_match('/[A-Za-z]/', $clean)) {
            return null;
        }

        // 3. "1no." -> "1নং" (digits converted later).
        if (preg_match('/^(\d+)no$/i', $clean, $m)) {
            return $m[1] . 'নং';
        }

        // 3. Abbreviation: 1-4 capital letters (DSR, SR, CO, TTC, R, K ...).
        if (preg_match('/^[A-Z]{1,4}$/', $clean)) {
            return strtr($clean, self::LETTERS);
        }

        // 4. Compound place name ending with a known suffix.
        foreach (self::SUFFIXES as $suffix => $bnSuffix) {
            if (preg_match('/^([a-z]{3,})' . $suffix . '$/i', $clean, $m)) {
                $stem = self::DICTIONARY[strtolower($m[1])] ?? self::request($m[1]);
                if ($stem !== null) {
                    return $stem . $bnSuffix;
                }
            }
        }

        return null;
    }

    /**
     * Transliterate a run of words: whole phrase first, then word by word.
     */
    private static function convertRun(string $run): ?string
    {
        if (! preg_match('/[A-Za-z]/', $run)) {
            return $run;
        }

        $result = self::request($run);
        if ($result !== null) {
            return $result;
        }

        $parts = preg_split('/(\s+)/u', $run, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';
        $converted = false;
        foreach ($parts as $part) {
            if (preg_match('/[A-Za-z]/', $part)) {
                $bn = self::request($part);
                $converted = $converted || $bn !== null;
                $out .= $bn ?? $part;
            } else {
                $out .= $part;
            }
        }

        return $converted ? $out : null;
    }

    private static function toBanglaDigits(string $text): string
    {
        return strtr($text, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
            '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }

    private static function request(string $text): ?string
    {
        try {
            $response = Http::timeout(5)->get(self::ENDPOINT, [
                'text' => $text,
                'itc'  => 'bn-t-i0-und',
                'num'  => 1,
            ]);

            if (! $response->ok()) {
                return null;
            }

            $json = $response->json();
            if (($json[0] ?? null) !== 'SUCCESS') {
                return null;
            }

            $best = $json[1][0][1][0] ?? null;

            return is_string($best) && $best !== ''
                ? html_entity_decode($best, ENT_QUOTES | ENT_HTML5, 'UTF-8')
                : null;
        } catch (\Throwable $e) {
            Log::warning('Bangla transliteration failed: ' . $e->getMessage());
            return null;
        }
    }
}
