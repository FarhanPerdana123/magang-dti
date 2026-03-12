<?php

namespace OptinCraft\App\Models\Analytics;

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\App;
use OptinCraft\WpMVC\Database\Resolver;
use OptinCraft\WpMVC\Database\Eloquent\Model;

class PageView extends Model {
    public static function get_table_name(): string {
        return 'optincraft_page_views';
    }

    public function resolver(): Resolver {
        return App::$container->get( Resolver::class );
    }
}
