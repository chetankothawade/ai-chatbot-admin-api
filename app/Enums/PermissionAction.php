<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionAction: string
{
    case VIEW = 'view';
    case CREATE = 'create';
    case EDIT = 'edit';
    case DELETE = 'delete';
    case STATUS = 'status';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function defaults(): array
    {
        return [
            self::VIEW->value,
            self::CREATE->value,
            self::EDIT->value,
            self::DELETE->value,
            self::STATUS->value,
        ];
    }
}
