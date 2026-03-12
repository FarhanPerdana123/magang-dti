<?php

namespace OptinCraft\App\Models;

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\App;
use OptinCraft\WpMVC\Database\Eloquent\Model;
use OptinCraft\WpMVC\Database\Eloquent\Relations\HasMany;
use OptinCraft\WpMVC\Database\Resolver;

class Post extends Model {
    public static function get_table_name():string {
        return 'posts';
    }

    public function meta(): HasMany {
        return $this->has_many( PostMeta::class, 'post_id', 'ID' );
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}