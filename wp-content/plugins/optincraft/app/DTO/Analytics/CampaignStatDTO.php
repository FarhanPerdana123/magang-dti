<?php

namespace OptinCraft\App\DTO\Analytics;

defined( 'ABSPATH' ) || exit;

class CampaignStatDTO extends \OptinCraft\WpMVC\DTO\DTO {
    private int $campaign_id;

    private string $stat_date;

    private int $impressions = 0;

    private int $conversions = 0;

    private float $revenue = 0.0;

    public function get_campaign_id(): int {
        return $this->campaign_id;
    }

    public function set_campaign_id( int $campaign_id ): self {
        $this->campaign_id = $campaign_id;
        return $this;
    }

    public function get_stat_date(): string {
        return $this->stat_date;
    }

    public function set_stat_date( string $stat_date ): self {
        $this->stat_date = $stat_date;
        return $this;
    }

    public function get_impressions(): int {
        return $this->impressions;
    }

    public function set_impressions( int $impressions ): self {
        $this->impressions = $impressions;
        return $this;
    }

    public function get_conversions(): int {
        return $this->conversions;
    }

    public function set_conversions( int $conversions ): self {
        $this->conversions = $conversions;
        return $this;
    }

    public function get_revenue(): float {
        return $this->revenue;
    }

    public function set_revenue( float $revenue ): self {
        $this->revenue = $revenue;
        return $this;
    }
}
