<?php

namespace OptinCraft\App\Helpers;

defined( 'ABSPATH' ) || exit;

abstract class Conditions {
    protected array $args;

    public function __construct( array $args ) {
        $this->args = $args;
    }

    public static function is_matched( array $conditions, array $args = [] ): bool {
        if ( empty( $conditions ) ) {
            return false;
        }

        $instance = new static( $args );

        $combinator = $conditions['combinator'];
        $rules      = $conditions['rules'];

        if ( 'and' === $combinator ) {
            foreach ( $rules as $rule ) {
                if ( ! $instance->match_rule( $rule ) ) {
                    return false;
                }
            }
            return true;
        }

        foreach ( $rules as $rule ) {
            if ( $instance->match_rule( $rule ) ) {
                return true;
            }
        }
        return false;
    }

    abstract protected function match_rule( $rule );

    public function match_string( $rule, $value ) {
        switch ( $rule['operator'] ) {
            case '=':
                return $value === $rule['value'];
            case '!=':
                return $value !== $rule['value'];
            case '>':
                return strlen( $value ) > $rule['value'];
            case '<':
                return strlen( $value ) < $rule['value'];
            case '>=':
                return strlen( $value ) >= $rule['value'];
            case '<=':
                return strlen( $value ) <= $rule['value'];
            case 'contains':
                return strpos( $value, $rule['value'] ) !== false;
            case 'doesNotContain':
                return strpos( $value, $rule['value'] ) === false;
            case 'beginsWith':
                return substr( $value, 0, strlen( $rule['value'] ) ) === $rule['value'];
            case 'doesNotBeginWith':
                return substr( $value, 0, strlen( $rule['value'] ) ) !== $rule['value'];
            case 'endsWith':
                return substr( $value, - strlen( $rule['value'] ) ) === $rule['value'];
            case 'doesNotEndWith':
                return substr( $value, - strlen( $rule['value'] ) ) !== $rule['value'];
            case 'null':
                return empty( $value );
            case 'notNull':
                return ! empty( $value );
        }

        return false;
    }

    public function match_array( $rule, $value ) {
        if ( empty( $rule['value'] ) ) {
            return false;
        }

        if ( 'in' === $rule['operator'] ) {
            return in_array( $value, $rule['value'] );
        }

        if ( 'notIn' === $rule['operator'] ) {
            return ! in_array( $value, $rule['value'] );
        }

        return false;
    }
}