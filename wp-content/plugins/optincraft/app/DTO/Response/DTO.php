<?php

namespace OptinCraft\App\DTO\Response;

defined( "ABSPATH" ) || exit;

class DTO extends \OptinCraft\WpMVC\DTO\DTO {
    private int $id;

    private int $campaign_id;

    private ?string $ip;

    private ?string $device;

    private ?string $browser;

    private ?string $browser_version;

    private ?int $user_id;

    private array $user_info;

    /**
     * Get the value of id
     *
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param int $id 
     *
     * @return self
     */
    public function set_id( int $id ): self {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of campaign_id
     *
     * @return int
     */
    public function get_campaign_id(): int {
        return $this->campaign_id;
    }

    /**
     * Set the value of campaign_id
     *
     * @param int $campaign_id 
     *
     * @return self
     */
    public function set_campaign_id( int $campaign_id ): self {
        $this->campaign_id = $campaign_id;

        return $this;
    }

    /**
     * Get the value of ip
     *
     * @return ?string
     */
    public function get_ip(): ?string {
        return $this->ip;
    }

    /**
     * Set the value of ip
     *
     * @param ?string $ip 
     *
     * @return self
     */
    public function set_ip( ?string $ip ): self {
        $this->ip = $ip;

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
     * Get the value of browser_version
     *
     * @return ?string
     */
    public function get_browser_version(): ?string {
        return $this->browser_version;
    }

    /**
     * Set the value of browser_version
     *
     * @param ?string $browser_version 
     *
     * @return self
     */
    public function set_browser_version( ?string $browser_version ): self {
        $this->browser_version = $browser_version;

        return $this;
    }

    /**
     * Get the value of user_id
     *
     * @return ?int
     */
    public function get_user_id(): ?int {
        return $this->user_id;
    }

    /**
     * Set the value of user_id
     *
     * @param ?int $user_id 
     *
     * @return self
     */
    public function set_user_id( ?int $user_id ): self {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get the value of user_info
     *
     * @return array
     */
    public function get_user_info(): array {
        return $this->user_info;
    }

    /**
     * Set the value of user_info
     *
     * @param array $user_info 
     *
     * @return self
     */
    public function set_user_info( array $user_info ): self {
        $this->user_info = $user_info;

        return $this;
    }
}