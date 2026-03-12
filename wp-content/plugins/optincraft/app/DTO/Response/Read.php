<?php

namespace OptinCraft\App\DTO\Response;

defined( "ABSPATH" ) || exit;

class Read extends \OptinCraft\WpMVC\DTO\DTO {
    private int $page = 1;

    private int $per_page = 10;

    private int $campaign_id;

    private string $search = '';

    private string $order_by = 'id';

    private string $order_direction = 'asc';

    /**
     * Get the value of page
     *
     * @return int
     */
    public function get_page(): int {
        return $this->page;
    }

    /**
     * Set the value of page
     *
     * @param int $page 
     *
     * @return self
     */
    public function set_page( int $page ): self {
        $this->page = $page;

        return $this;
    }

    /**
     * Get the value of per_page
     *
     * @return int
     */
    public function get_per_page(): int {
        return $this->per_page;
    }

    /**
     * Set the value of per_page
     *
     * @param int $per_page 
     *
     * @return self
     */
    public function set_per_page( int $per_page ): self {
        $this->per_page = $per_page;

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
     * Get the value of search
     *
     * @return string
     */
    public function get_search(): string {
        return $this->search;
    }

    /**
     * Set the value of search
     *
     * @param string $search 
     *
     * @return self
     */
    public function set_search( string $search ): self {
        $this->search = $search;

        return $this;
    }

    /**
     * Get the value of order_by
     *
     * @return string
     */
    public function get_order_by(): string {
        return $this->order_by;
    }

    /**
     * Set the value of order_by
     *
     * @param string $order_by 
     *
     * @return self
     */
    public function set_order_by( string $order_by ): self {
        $this->order_by = $order_by;

        return $this;
    }

    /**
     * Get the value of order_direction
     *
     * @return string
     */
    public function get_order_direction(): string {
        return $this->order_direction;
    }

    /**
     * Set the value of order_direction
     *
     * @param string $order_direction 
     *
     * @return self
     */
    public function set_order_direction( string $order_direction ): self {
        $this->order_direction = $order_direction;

        return $this;
    }
}