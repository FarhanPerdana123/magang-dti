<?php

namespace OptinCraft\App\Models\Campaign;

defined( "ABSPATH" ) || exit;

use OptinCraft\WpMVC\App;
use OptinCraft\WpMVC\Database\Resolver;
use OptinCraft\WpMVC\Database\Eloquent\Model;
use OptinCraft\App\Models\Analytics\CampaignStat;
use OptinCraft\WpMVC\Database\Eloquent\Relations\HasMany;

class Campaign extends Model {
    public static function get_table_name():string {
        return 'optincraft_campaigns';
    }

    public function campaign_stats(): HasMany {
        return $this->has_many( CampaignStat::class, 'campaign_id', 'id' );
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}