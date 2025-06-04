<?php

namespace App\Themes;

use Hasnayeen\Themes\Contracts\Theme;

class Default implements Theme
{
    public static function getName(): string
    {
        return 'Default';
    }

    public static function getColors(): array
    {
        return [
            'blue' => 'Plava',
            'red' => 'Crvena',
            'green' => 'Zelena',
        ];
    }
}
