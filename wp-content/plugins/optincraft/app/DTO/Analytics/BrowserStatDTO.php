<?php

namespace OptinCraft\App\DTO\Analytics;

defined( 'ABSPATH' ) || exit;

class BrowserStatDTO extends \OptinCraft\WpMVC\DTO\DTO {
    private int $campaign_id;

    private string $stat_date;

    private string $browser;

    private int $views = 0;

    private int $conversions = 0;

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

    public function get_browser(): string {
        return $this->browser;
    }

    public function set_browser( string $browser ): self {
        $this->browser = $browser;
        return $this;
    }

    public function get_views(): int {
        return $this->views;
    }

    public function set_views( int $views ): self {
        $this->views = $views;
        return $this;
    }

    public function get_conversions(): int {
        return $this->conversions;
    }

    public function set_conversions( int $conversions ): self {
        $this->conversions = $conversions;
        return $this;
    }
}
