<?php

namespace OptinCraft\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Http\Controllers\Controller;
use OptinCraft\WpMVC\Exceptions\Exception;
use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\WpMVC\RequestValidator\Validator;
use OptinCraft\App\Models\Post;
use WP_REST_Request;

class DisplayConditionController extends Controller {
    public function select( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'field' => 'required|string',
            ]
        );

        $field = $request->get_param( 'field' );

        switch ( $field ) {
            case 'page':
                $pages = Post::query()->select( 'ID as value, post_title as label' )->where( 'post_type', 'page' )->where( 'post_status', 'publish' )->order_by_desc( 'id' )->get();
                return Response::send( $pages );
            case 'post_type':
                $post_types       = get_post_types( [ 'public' => true ], 'objects' );
                $post_types_array = [];
                foreach ( $post_types as $post_type ) {
                    $post_types_array[] = [
                        'value' => $post_type->name,
                        'label' => $post_type->label,
                    ];
                }

                return Response::send( $post_types_array );
            default:
                $items = apply_filters( 'optincraft_display_condition_select_items', [], $field );
                return Response::send( $items );
        }
    }
}