<?php

namespace App\Enums;

enum TagType: string
{
    case MOBILE = 'mobile';
    case DESKTOP = 'desktop';
    case WEB = 'web';
    case ADMIN = 'admin';
    case USER = 'user';
    case PUBLIC = 'public';
    case PRIVATE = 'private';

    /**
     * Get all values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all cases as an array of TagType objects
     */
    public static function all(): array
    {
        return self::cases();
    }
}
