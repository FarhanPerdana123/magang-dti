<?php

namespace OptinCraft\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use WP_REST_Request;
use OptinCraft\WpMVC\RequestValidator\Validator;
use OptinCraft\WpMVC\Exceptions\Exception;
use OptinCraft\WpMVC\Routing\Response;

/**
 * TemplateController handles media attachment operations for templates
 * 
 * This controller manages the insertion and caching of media attachments
 * from external URLs, storing them as WordPress attachments for reuse.
 */
class TemplateController {
    /**
     * WordPress option key for storing cached attachment mappings
     * Maps external URLs to their corresponding WordPress attachment IDs
     */
    const ATTACHMENT_OPTION_KEY = 'optincraft_demo_media';

    /**
     * Insert a media attachment from an external URL
     * 
     * Downloads media from the provided URL and creates a WordPress attachment.
     * Implements caching to avoid re-downloading the same media multiple times.
     * 
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request REST API request object
     * @return array Response containing attachment ID and URL
     * @throws Exception If validation fails, download fails, or attachment creation fails
     */
    public function insert_attachment( Validator $validator, WP_REST_Request $request ) {
        // Validate required parameters
        $validator->validate(
            [
                'url' => 'required|string',
            ]
        );

        $url         = $request->get_param( 'url' );
        $attachments = $this->get_attachments();

        // Check if this URL has already been processed and cached
        if ( ! empty( $attachments[$url] ) ) {
            $id         = $attachments[$url];
            $attachment = wp_get_attachment_url( $id );

            // Verify the cached attachment still exists and is accessible
            if ( is_string( $attachment ) ) {
                return Response::send(
                    [
                        'id'  => $id,
                        'url' => $attachment
                    ]
                );
            }
        }

        // Download the media file from the external URL
        $response = wp_remote_get(
            $url, [
                [
                    'timeout' => 30
                ]
            ]
        );

        // Handle download errors
        if ( is_wp_error( $response ) ) {
            $error_code    = $response->get_error_code();
            $response_code = 500;

            // Map specific error codes to appropriate HTTP status codes
            if ( is_string( $error_code ) ) {
                if ( 'http_request_failed' === $error_code ) {
                    $response_code = 495; // Network error
                }
            } else {
                $response_code = $error_code;
            }
            throw new Exception( esc_html( $response->get_error_message() ), esc_attr( $response_code ) );
        }

        // Check if the HTTP response was successful
        $response_code = intval( wp_remote_retrieve_response_code( $response ) );

        if ( 200 !== $response_code ) {
            throw new Exception( esc_html( wp_remote_retrieve_response_message( $response ) ), esc_attr( $response_code ) );
        }

        // Extract filename from URL and upload the file to WordPress uploads directory
        $file_name = wp_basename( $url );
        $upload    = wp_upload_bits( $file_name, null, wp_remote_retrieve_body( $response ) );

        // Check for upload errors
        if ( ! empty( $upload['error'] ) ) {
            throw new Exception( esc_html( $upload['error'] ), 500 );
        }

        // Prepare attachment data for WordPress
        $attachment = [
            'post_title'     => $file_name,
            'post_type'      => 'attachment',
            'post_mime_type' => $upload['type'],
            'guid'           => $upload['url']
        ];

        // Create the WordPress attachment post
        $id = wp_insert_attachment( $attachment, $upload['file'] );

        if ( is_wp_error( $id ) ) {
            //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            throw new Exception( $id->get_error_message(), $id->get_error_code() );
        }

        // Include required WordPress functions for metadata generation
        if ( ! function_exists( 'wp_read_video_metadata' ) ) {
            include_once ABSPATH . 'wp-admin/includes/media.php';
        }

        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            include_once ABSPATH . 'wp-admin/includes/image.php';
        }

        // Generate and update attachment metadata (thumbnails, dimensions, etc.)
        wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

        // Cache the URL-to-ID mapping for future use
        $attachments[$url] = $id;
        $this->update_attachments( $attachments );

        // Return the created attachment information
        return Response::send(
            [
                'id'  => $id,
                'url' => $upload['url']
            ]
        );
    }

    /**
     * Retrieve cached attachment mappings from WordPress options
     * 
     * @return array Associative array mapping URLs to attachment IDs
     */
    private function get_attachments():array {
        return get_option( self::ATTACHMENT_OPTION_KEY, [] );
    }

    /**
     * Update cached attachment mappings in WordPress options
     * 
     * @param array $attachments Associative array mapping URLs to attachment IDs
     * @return bool True if option was updated successfully, false otherwise
     */
    private function update_attachments( array $attachments ) {
        return update_option( self::ATTACHMENT_OPTION_KEY, $attachments );
    }
}