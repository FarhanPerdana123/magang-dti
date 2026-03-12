<?php

namespace OptinCraft\App\Fields;

defined( 'ABSPATH' ) || exit;

class Email extends Field {
    public static function get_key(): string {
        return 'email';
    }

    protected function get_validation_rules( array $field ): array {
        return ['string', 'email', 'max:250'];
    }
}
