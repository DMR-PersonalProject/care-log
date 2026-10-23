<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class Highlight
{
    /**
     * Escape the text and wrap every case-insensitive match of the words in <mark>.
     *
     * @param  list<string>  $words
     */
    public static function html(?string $text, array $words): HtmlString
    {
        $text ??= '';
        $words = array_filter($words, fn (string $word) => $word !== '');

        if ($text === '' || $words === []) {
            return new HtmlString(e($text));
        }

        // Longest words first, so "lunchbox" is marked whole rather than just "lunch".
        usort($words, fn (string $a, string $b) => mb_strlen($b) <=> mb_strlen($a));
        $pattern = '/('.implode('|', array_map(fn (string $word) => preg_quote($word, '/'), $words)).')/iu';

        // With a capture group, the odd-numbered pieces are the matches.
        $pieces = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$text];

        $html = '';
        foreach ($pieces as $i => $piece) {
            $html .= $i % 2 === 1
                ? '<mark class="rounded bg-yellow-200 px-0.5 text-inherit">'.e($piece).'</mark>'
                : e($piece);
        }

        return new HtmlString($html);
    }
}
