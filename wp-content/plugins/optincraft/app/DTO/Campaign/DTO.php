<?php

namespace OptinCraft\App\DTO\Campaign;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Helpers\DateTime;

class DTO extends \OptinCraft\WpMVC\DTO\DTO {
    private int $id;

    private string $title;

    private string $description;

    private array $content;

    private string $type;

    private string $open_event;

    private array $display_conditions;

    private array $device_visibility;

    private string $geolocation_action;

    private ?array $geolocation_countries = null;

    private ?DateTime $start_date = null;

    private ?DateTime $end_date = null;

    private string $budget;

    private bool $status;

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
     * Get the value of title
     *
     * @return string
     */
    public function get_title(): string {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param string $title 
     *
     * @return self
     */
    public function set_title( string $title ): self {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of description
     *
     * @return string
     */
    public function get_description(): string {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param string $description 
     *
     * @return self
     */
    public function set_description( string $description ): self {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of content
     *
     * @return array
     */
    public function get_content(): array {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @param array $content 
     *
     * @return self
     */
    public function set_content( array $content ): self {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of type
     *
     * @return string
     */
    public function get_type(): string {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @param string $type 
     *
     * @return self
     */
    public function set_type( string $type ): self {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of open_event
     *
     * @return string
     */
    public function get_open_event(): string {
        return $this->open_event;
    }

    /**
     * Set the value of open_event
     *
     * @param string $open_event 
     *
     * @return self
     */
    public function set_open_event( string $open_event ): self {
        $this->open_event = $open_event;

        return $this;
    }

    /**
     * Get the value of display_conditions
     *
     * @return array
     */
    public function get_display_conditions(): array {
        return $this->display_conditions;
    }

    /**
     * Set the value of display_conditions
     *
     * @param array $display_conditions 
     *
     * @return self
     */
    public function set_display_conditions( array $display_conditions ): self {
        $this->display_conditions = $display_conditions;

        return $this;
    }

    /**
     * Get the value of device_visibility
     *
     * @return array
     */
    public function get_device_visibility(): array {
        return $this->device_visibility;
    }

    /**
     * Set the value of device_visibility
     *
     * @param array $device_visibility 
     *
     * @return self
     */
    public function set_device_visibility( array $device_visibility ): self {
        $this->device_visibility = $device_visibility;

        return $this;
    }

    /**
     * Get the value of geolocation_action
     *
     * @return string
     */
    public function get_geolocation_action(): string {
        return $this->geolocation_action;
    }

    /**
     * Set the value of geolocation_action
     *
     * @param string $geolocation_action 
     *
     * @return self
     */
    public function set_geolocation_action( string $geolocation_action ): self {
        $this->geolocation_action = $geolocation_action;

        return $this;
    }

    /**
     * Get the value of geolocation_countries
     *
     * @return ?array
     */
    public function get_geolocation_countries(): ?array {
        return $this->geolocation_countries;
    }

    /**
     * Set the value of geolocation_countries
     *
     * @param ?array $geolocation_countries 
     *
     * @return self
     */
    public function set_geolocation_countries( ?array $geolocation_countries ): self {
        $this->geolocation_countries = $geolocation_countries;

        return $this;
    }

    /**
     * Get the value of start_date
     *
     * @return ?DateTime
     */
    public function get_start_date(): ?DateTime {
        return $this->start_date;
    }

    /**
     * Set the value of start_date
     *
     * @param ?DateTime $start_date 
     *
     * @return self
     */
    public function set_start_date( ?DateTime $start_date ): self {
        $this->start_date = $start_date;

        return $this;
    }

    /**
     * Get the value of end_date
     *
     * @return ?DateTime
     */
    public function get_end_date(): ?DateTime {
        return $this->end_date;
    }

    /**
     * Set the value of end_date
     *
     * @param ?DateTime $end_date 
     *
     * @return self
     */
    public function set_end_date( ?DateTime $end_date ): self {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * Get the value of budget
     *
     * @return string
     */
    public function get_budget(): string {
        return $this->budget;
    }

    /**
     * Set the value of budget
     *
     * @param string $budget 
     *
     * @return self
     */
    public function set_budget( string $budget ): self {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get the value of status
     *
     * @return bool
     */
    public function is_status(): bool {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @param bool $status 
     *
     * @return self
     */
    public function set_status( bool $status ): self {
        $this->status = $status;

        return $this;
    }
}