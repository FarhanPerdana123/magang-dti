<?php

namespace OptinCraft\App\Http\Controllers;

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\WpMVC\RequestValidator\Validator;
use OptinCraft\App\Repositories\EventRepository;
use OptinCraft\App\Repositories\StatsRepository;
use OptinCraft\App\Repositories\PageViewRepository;
use OptinCraft\App\DTO\Analytics\PageViewDTO;
use WP_REST_Request;

/**
 * Controller for handling event ingestion from external sources.
 * Processes raw event data and triggers analytics aggregation.
 */
class EventsController extends Controller {
    private EventRepository $repository;

    private StatsRepository $stats;

    private PageViewRepository $page_view_repository;

    public function __construct( EventRepository $repository, StatsRepository $stats, PageViewRepository $page_view_repository ) {
        $this->repository           = $repository;
        $this->stats                = $stats;
        $this->page_view_repository = $page_view_repository;
    }

    /**
     * Ingest a new event from external sources.
     * Validates the event data, stores it in the database, and triggers analytics aggregation.
     *
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request WordPress REST request object
     * @return array JSON response with success message and event ID
     */
    public function ingest( Validator $validator, WP_REST_Request $request ): array {
        $event_type = $request->get_param( 'event_type' );
        $country    = optincraft_get_user_country_code();
        
        // Handle page views separately (global, not campaign-specific)
        if ( $event_type === 'view' ) {
            return $this->ingest_page_view( $validator, $request, $country );
        }
        
        // Handle campaign-specific events (impression, conversion, revenue)
        return $this->ingest_campaign_event( $validator, $request, $country );
    }

    /**
     * Ingest a page view event (global, not campaign-specific).
     */
    private function ingest_page_view( Validator $validator, WP_REST_Request $request, $country ): array {
        // Validate page view data
        $validator->validate(
            [
                'page_url'   => 'required|string',
                'referrer'   => 'string',
                'device'     => 'string|accepted:desktop,mobile,tablet',
                'browser'    => 'string',
                'visitor_id' => 'string',
                'session_id' => 'string',
            ]
        );

        // Prepare page view payload
        // $payload = [
        //     'page_url'     => $request->get_param( 'page_url' ),
        //     'referrer'     => $request->get_param( 'referrer' ),
        //     'device'       => $request->get_param( 'device' ),
        //     'browser'      => $request->get_param( 'browser' ),
        //     'country_code' => $country,
        //     'visitor_id'   => $request->get_param( 'visitor_id' ),
        //     'session_id'   => $request->get_param( 'session_id' ),
        // ];

        // Create PageViewDTO and store
        $page_view_dto = ( new PageViewDTO )
            ->set_page_url( $request->get_param( 'page_url' ) )
            ->set_referrer( $request->get_param( 'referrer' ) )
            ->set_device( $request->get_param( 'device' ) )
            ->set_browser( $request->get_param( 'browser' ) )
            ->set_country_code( $country )
            ->set_visitor_id( $request->get_param( 'visitor_id' ) )
            ->set_session_id( $request->get_param( 'session_id' ) );

        $this->page_view_repository->track_page_view( $page_view_dto );
        
        // Trigger page view statistics aggregation
        // $this->stats->apply_page_view( $payload );

        return Response::send( [ 'message' => esc_html__( 'Page view recorded', 'optincraft' ) ], 201 );
    }

    /**
     * Ingest a campaign-specific event (impression, conversion, revenue).
     */
    private function ingest_campaign_event( Validator $validator, WP_REST_Request $request, $country ): array {
        $validator->validate(
            [
                'campaign_id' => 'required|numeric',
                'event_type'  => 'required|string|accepted:impression,conversion,revenue',
                'page_url'    => 'string',
                'referrer'    => 'string',
                'device'      => 'string|accepted:desktop,mobile,tablet',
                'browser'     => 'string',
                'revenue'     => 'numeric',
                'visitor_id'  => 'string',
                'session_id'  => 'string',
            ]
        );

        // Prepare event payload with proper data types
        $payload = [
            'campaign_id'  => (int) $request->get_param( 'campaign_id' ),
            'event_type'   => $request->get_param( 'event_type' ),
            'page_url'     => $request->get_param( 'page_url' ),
            'referrer'     => $request->get_param( 'referrer' ),
            'device'       => $request->get_param( 'device' ),
            'browser'      => $request->get_param( 'browser' ),
            'country_code' => $country,
            'revenue'      => $request->get_param( 'revenue' ) ?: 0,
            'visitor_id'   => $request->get_param( 'visitor_id' ),
            'session_id'   => $request->get_param( 'session_id' ),
        ];

        // Create EventDTO for structured data transfer
        $event_dto = ( new \OptinCraft\App\DTO\Analytics\EventDTO() )
            ->set_campaign_id( (int) $payload['campaign_id'] )
            ->set_event_type( $payload['event_type'] )
            ->set_visitor_id( $payload['visitor_id'] ?? null )
            ->set_session_id( $payload['session_id'] ?? null )
            ->set_device( $payload['device'] ?? null )
            ->set_country_code( $payload['country_code'] ?? null )
            ->set_referrer( $payload['referrer'] ?? null )
            ->set_page_url( $payload['page_url'] ?? null )
            ->set_browser( $payload['browser'] ?? null )
            ->set_revenue( $payload['revenue'] ?? null );

        // Store event and trigger analytics aggregation
        $this->repository->ingest_event( $event_dto );
        $this->stats->apply_event( $payload );

        return Response::send( [ 'message' => esc_html__( 'Event recorded', 'optincraft' ) ], 201 );
    }
}


