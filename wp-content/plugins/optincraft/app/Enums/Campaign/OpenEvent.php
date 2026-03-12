<?php

namespace OptinCraft\App\Enums\Campaign;

defined( "ABSPATH" ) || exit;

class OpenEvent {
    public const ON_LOAD        = 'on_load';
    public const ON_SCROLL      = 'on_scroll';
    public const ON_EXIT_INTENT = 'on_exit_intent';
    public const ON_CLICK       = 'on_click';

    public static function get_all() {
        return [
            self::ON_LOAD,
            self::ON_SCROLL,
            self::ON_EXIT_INTENT,
            self::ON_CLICK,
        ];
    }
}
