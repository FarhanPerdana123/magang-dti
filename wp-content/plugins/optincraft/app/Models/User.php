<?php

namespace OptinCraft\App\Models;

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\App;
use OptinCraft\WpMVC\Database\Eloquent\Model;
use OptinCraft\WpMVC\Database\Resolver;

class User extends Model {
    public static function get_table_name():string {
        return 'users';
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}