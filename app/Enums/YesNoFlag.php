<?php

declare(strict_types=1);

namespace App\Enums;

enum YesNoFlag: string
{
    case YES = 'Y';
    case NO = 'N';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
