<?php

namespace OptinCraft\App\Models;


defined( "ABSPATH" ) || exit;

use OptinCraft\WpMVC\App;
use OptinCraft\WpMVC\Database\Resolver;
use OptinCraft\WpMVC\Database\Eloquent\Model;
use OptinCraft\WpMVC\Database\Eloquent\Relations\HasMany;

class Response extends Model {
    public static function get_table_name():string {
        return 'optincraft_responses';
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }

    public function answers(): HasMany {
        return $this->has_many( Answer::class, 'response_id', 'id' );
    }
}