<?php

namespace OptinCraft\App\Models\Analytics;

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\App;
use OptinCraft\WpMVC\Database\Resolver;
use OptinCraft\WpMVC\Database\Eloquent\Model;

class CountryStat extends Model {
    public static function get_table_name(): string {
        return 'optincraft_country_stats';
    }

    public function resolver(): Resolver {
        return App::$container->get( Resolver::class );
    }
}


