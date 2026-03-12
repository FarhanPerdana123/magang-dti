<?php

namespace OptinCraft\App\DTO\Analytics;

defined( 'ABSPATH' ) || exit;

class PageViewDTO extends \OptinCraft\WpMVC\DTO\DTO {
    public string $page_url;

    public ?string $referrer;

    public ?string $device;

    public ?string $browser;

    public ?string $country_code;

    public ?string $visitor_id;

    public ?string $session_id;

    /**
     * Get the value of page_url
     *
     * @return string
     */
    public function get_page_url(): string {
        return $this->page_url;
    }

    /**
     * Set the value of page_url
     *
     * @param string $page_url 
     *
     * @return self
     */
    public function set_page_url( string $page_url ): self {
        $this->page_url = $page_url;

        return $this;
    }

    /**
     * Get the value of referrer
     *
     * @return ?string
     */
    public function get_referrer(): ?string {
        return $this->referrer;
    }

    /**
     * Set the value of referrer
     *
     * @param ?string $referrer 
     *
     * @return self
     */
    public function set_referrer( ?string $referrer ): self {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * Get the value of device
     *
     * @return ?string
     */
    public function get_device(): ?string {
        return $this->device;
    }

    /**
     * Set the value of device
     *
     * @param ?string $device 
     *
     * @return self
     */
    public function set_device( ?string $device ): self {
        $this->device = $device;

        return $this;
    }

    /**
     * Get the value of browser
     *
     * @return ?string
     */
    public function get_browser(): ?string {
        return $this->browser;
    }

    /**
     * Set the value of browser
     *
     * @param ?string $browser 
     *
     * @return self
     */
    public function set_browser( ?string $browser ): self {
        $this->browser = $browser;

        return $this;
    }

    /**
     * Get the value of country_code
     *
     * @return ?string
     */
    public function get_country_code(): ?string {
        return $this->country_code;
    }

    /**
     * Set the value of country_code
     *
     * @param ?string $country_code 
     *
     * @return self
     */
    public function set_country_code( ?string $country_code ): self {
        $this->country_code = $country_code;

        return $this;
    }

    /**
     * Get the value of visitor_id
     *
     * @return ?string
     */
    public function get_visitor_id(): ?string {
        return $this->visitor_id;
    }

    /**
     * Set the value of visitor_id
     *
     * @param ?string $visitor_id 
     *
     * @return self
     */
    public function set_visitor_id( ?string $visitor_id ): self {
        $this->visitor_id = $visitor_id;

        return $this;
    }

    /**
     * Get the value of session_id
     *
     * @return ?string
     */
    public function get_session_id(): ?string {
        return $this->session_id;
    }

    /**
     * Set the value of session_id
     *
     * @param ?string $session_id 
     *
     * @return self
     */
    public function set_session_id( ?string $session_id ): self {
        $this->session_id = $session_id;

        return $this;
    }
}
