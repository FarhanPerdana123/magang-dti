<?php

namespace OptinCraft\App\Helpers;

defined( 'ABSPATH' ) || exit;

class DisplayConditions extends Conditions {
    protected function match_rule( $rule ) {
        switch ( $rule['field'] ) {
            case 'page':
                return $this->match_page( $rule );
            case 'post_type':
                return $this->match_post_type( $rule );
        }

        $instance = $this;

        return apply_filters( 'optincraft_match_display_rule', false, $rule, $instance );
    }

    protected function match_page( $rule ) {
        return $this->match_array( $rule, $this->get_current_page_id() );
    }

    protected function match_post_type( $rule ) {
        return $this->match_array( $rule, get_post_type() );
    }

    private function get_current_page_id() {
        return apply_filters( 'optincraft_current_page_id', get_the_ID() );
    }
}