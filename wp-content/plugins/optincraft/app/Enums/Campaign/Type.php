<?php

namespace OptinCraft\App\Enums\Campaign;

defined( 'ABSPATH' ) || exit;

class Type
{
    public const POPUP        = 'popup';
    public const FLOATING_BAR = 'floating_bar';
    public const SLIDE_IN     = 'slide_in';
    public const FULL_SCREEN  = 'full_screen';

    public static function get_all() {
        return [
            self::POPUP,
            self::FLOATING_BAR,
            self::SLIDE_IN,
            self::FULL_SCREEN,
        ];
    }
}

