<?php

namespace OptinCraft\App\DTO\Analytics;

defined( 'ABSPATH' ) || exit;

class EventDTO extends \OptinCraft\WpMVC\DTO\DTO {
    private int $campaign_id;

    private string $event_type;

    private ?string $visitor_id = null;

    private ?string $session_id = null;

    private ?string $device = null;

    private ?string $country_code = null;

    private ?string $referrer = null;

    private ?string $page_url = null;

    private ?string $browser = null;

    private ?float $revenue = null;

    public function get_campaign_id(): int {
        return $this->campaign_id;
    }

    public function set_campaign_id( int $campaign_id ): self {
        $this->campaign_id = $campaign_id;
        return $this;
    }

    public function get_event_type(): string {
        return $this->event_type;
    }

    public function set_event_type( string $event_type ): self {
        $this->event_type = $event_type;
        return $this;
    }

    public function get_visitor_id(): ?string {
        return $this->visitor_id;
    }

    public function set_visitor_id( ?string $visitor_id ): self {
        $this->visitor_id = $visitor_id;
        return $this;
    }

    public function get_session_id(): ?string {
        return $this->session_id;
    }

    public function set_session_id( ?string $session_id ): self {
        $this->session_id = $session_id;
        return $this;
    }

    public function get_device(): ?string {
        return $this->device;
    }

    public function set_device( ?string $device ): self {
        $this->device = $device;
        return $this;
    }

    public function get_country_code(): ?string {
        return $this->country_code;
    }

    public function set_country_code( ?string $country_code ): self {
        $this->country_code = $country_code;
        return $this;
    }

    public function get_referrer(): ?string {
        return $this->referrer;
    }

    public function set_referrer( ?string $referrer ): self {
        $this->referrer = $referrer;
        return $this;
    }

    public function get_page_url(): ?string {
        return $this->page_url;
    }

    public function set_page_url( ?string $page_url ): self {
        $this->page_url = $page_url;
        return $this;
    }

    public function get_browser(): ?string {
        return $this->browser;
    }

    public function set_browser( ?string $browser ): self {
        $this->browser = $browser;
        return $this;
    }

    public function get_revenue(): ?float {
        return $this->revenue;
    }

    public function set_revenue( ?float $revenue ): self {
        $this->revenue = $revenue;
        return $this;
    }
}
