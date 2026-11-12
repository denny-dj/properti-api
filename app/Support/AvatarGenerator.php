<?php

namespace App\Support;

/** Avatar inisial berbentuk SVG (data URI) sebagai cadangan foto profil/logo. Tanpa layanan eksternal. */
final class AvatarGenerator
{
    private const PALETTE = ['#0f172a', '#1e293b', '#334155', '#1d4ed8', '#0f766e', '#4338ca'];

    public static function initials(string $name, int $max = 2): string
    {
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $letters = array_map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)), array_slice($words, 0, $max));

        return implode('', $letters) ?: '?';
    }

    public static function dataUri(string $name): string
    {
        $index = (int) sprintf('%u', crc32(mb_strtolower(trim($name)))) % count(self::PALETTE);
        $initials = htmlspecialchars(self::initials($name), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 256 256">'
            .'<rect width="256" height="256" fill="'.self::PALETTE[$index].'"/>'
            .'<text x="50%" y="50%" dy=".35em" text-anchor="middle" font-family="Arial, sans-serif" font-size="104" font-weight="700" fill="#ffffff">'.$initials.'</text>'
            .'</svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
