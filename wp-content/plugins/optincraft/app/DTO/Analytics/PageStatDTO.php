<?php

namespace OptinCraft\App\DTO\Analytics;

defined( 'ABSPATH' ) || exit;

class PageStatDTO extends \OptinCraft\WpMVC\DTO\DTO {
    private int $campaign_id;

    private string $stat_date;

    private string $page_url;

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

    public function get_page_url(): string {
        return $this->page_url;
    }

    public function set_page_url( string $page_url ): self {
        $this->page_url = $page_url;
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
