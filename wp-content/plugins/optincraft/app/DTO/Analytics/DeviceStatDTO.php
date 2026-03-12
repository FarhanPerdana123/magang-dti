<?php

namespace OptinCraft\App\DTO\Analytics;

defined( 'ABSPATH' ) || exit;

class DeviceStatDTO extends \OptinCraft\WpMVC\DTO\DTO {
    private int $campaign_id;

    private string $stat_date;

    private string $device;

    private int $views = 0;

    private int $conversions = 0;

    private int $unique_visitors = 0;

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

    public function get_device(): string {
        return $this->device;
    }

    public function set_device( string $device ): self {
        $this->device = $device;
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

    public function get_unique_visitors(): int {
        return $this->unique_visitors;
    }

    public function set_unique_visitors( int $unique_visitors ): self {
        $this->unique_visitors = $unique_visitors;
        return $this;
    }
}
