<?php

use App\Support\Highlight;

$mark = fn (string $text) => '<mark class="rounded bg-yellow-200 px-0.5 text-inherit">'.$text.'</mark>';

it('marks every match, keeping the original letter case', function () use ($mark) {
    expect((string) Highlight::html('Soup, then more soup', ['SOUP']))
        ->toBe($mark('Soup').', then more '.$mark('soup'));
});

it('prefers the longest word when words overlap', function () use ($mark) {
    expect((string) Highlight::html('lunchbox', ['lunch', 'lunchbox']))->toBe($mark('lunchbox'));
});

it('escapes the text and handles special characters in words', function () use ($mark) {
    expect((string) Highlight::html('a+b <i>', ['a+b']))->toBe($mark('a+b').' &lt;i&gt;')
        ->and((string) Highlight::html('Ate 50%', ['50%']))->toBe('Ate '.$mark('50%'));
});

it('returns escaped text when there is nothing to mark', function () {
    expect((string) Highlight::html('<b>', []))->toBe('&lt;b&gt;')
        ->and((string) Highlight::html(null, ['x']))->toBe('');
});

it('handles accented and non-English letters', function () use ($mark) {
    expect((string) Highlight::html('Kumain ng LUGAW at ñame', ['lugaw', 'Ñame']))
        ->toBe('Kumain ng '.$mark('LUGAW').' at '.$mark('ñame'));
});
