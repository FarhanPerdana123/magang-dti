<?php

namespace OptinCraft\App\Fields;

defined( 'ABSPATH' ) || exit;

class Name extends Field {
    public static function get_key(): string {
        return 'name';
    }

    protected function get_validation_rules( array $field ): array {
        return ['string', 'max:250'];
    }
}
