<?php

namespace OptinCraft\App\DTO\Answer;

defined( "ABSPATH" ) || exit;

class DTO extends \OptinCraft\WpMVC\DTO\DTO {
    private int $id;

    private int $response_id;

    private int $campaign_id;

    private string $field_name;

    private string $field_type;
    
    private string $value;

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
     * Get the value of response_id
     *
     * @return int
     */
    public function get_response_id(): int {
        return $this->response_id;
    }

    /**
     * Set the value of response_id
     *
     * @param int $response_id 
     *
     * @return self
     */
    public function set_response_id( int $response_id ): self {
        $this->response_id = $response_id;

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
     * Get the value of field_name
     *
     * @return string
     */
    public function get_field_name(): string {
        return $this->field_name;
    }

    /**
     * Set the value of field_name
     *
     * @param string $field_name 
     *
     * @return self
     */
    public function set_field_name( string $field_name ): self {
        $this->field_name = $field_name;

        return $this;
    }

    /**
     * Get the value of field_type
     *
     * @return string
     */
    public function get_field_type(): string {
        return $this->field_type;
    }

    /**
     * Set the value of field_type
     *
     * @param string $field_type 
     *
     * @return self
     */
    public function set_field_type( string $field_type ): self {
        $this->field_type = $field_type;

        return $this;
    }

    /**
     * Get the value of value
     *
     * @return string
     */
    public function get_value(): string {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @param string $value 
     *
     * @return self
     */
    public function set_value( string $value ): self {
        $this->value = $value;

        return $this;
    }
}