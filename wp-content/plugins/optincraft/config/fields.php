<?php

use OptinCraft\App\Fields\Email;
use OptinCraft\App\Fields\Name;

defined( 'ABSPATH' ) || exit;

return apply_filters(
    'optincraft_fields', [
        Email::get_key() => [
            'class' => Email::class,
        ],
        Name::get_key()  => [
            'class' => Name::class,
        ],
    ]
);