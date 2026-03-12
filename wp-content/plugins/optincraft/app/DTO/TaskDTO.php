<?php

namespace OptinCraft\App\DTO;

defined( "ABSPATH" ) || exit;

use OptinCraft\WpMVC\DTO\DTO;

class TaskDTO extends DTO {
    private int $id;

    private int $campaign_id;

    private string $type;

    private array $data;

    private int $status;

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
     * Get the value of data
     *
     * @return array
     */
    public function get_data(): array {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @param array $data 
     *
     * @return self
     */
    public function set_data( array $data ): self {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the value of status
     *
     * @return int
     */
    public function get_status(): int {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @param int $status 
     *
     * @return self
     */
    public function set_status( int $status ): self {
        $this->status = $status;

        return $this;
    }
}