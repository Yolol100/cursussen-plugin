<?php
declare(strict_types=1);

namespace SodriveAcademie;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WP_Query;
use WP_REST_Request;
use WP_REST_Response;

class REST_API {
    private const MAX_PER_PAGE = 100;

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
    }

    public function register_rest_routes(): void {
        $args = [
            'page' => [
                'required'          => false,
                'default'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => static function ( $value ): bool {
                    return absint( $value ) >= 1;
                },
            ],
            'per_page' => [
                'required'          => false,
                'default'           => 20,
                'sanitize_callback' => 'absint',
                'validate_callback' => static function ( $value ): bool {
                    $value = absint( $value );
                    return $value >= 1 && $value <= self::MAX_PER_PAGE;
                },
            ],
            'categorie' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_title',
                'validate_callback' => static function ( $value ): bool {
                    return '' === $value || is_string( $value );
                },
            ],
        ];

        register_rest_route( 'cursussen/v1', '/all', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_all_cursussen' ],
            'permission_callback' => '__return_true',
            'args'                => $args,
        ] );

        register_rest_route( 'cursussen/v1', '/filter', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'filter_cursussen' ],
            'permission_callback' => '__return_true',
            'args'                => $args,
        ] );
    }

    public function get_all_cursussen( WP_REST_Request $request ): WP_REST_Response {
        return $this->query_cursussen( $request );
    }

    public function filter_cursussen( WP_REST_Request $request ): WP_REST_Response {
        return $this->query_cursussen( $request );
    }

    private function query_cursussen( WP_REST_Request $request ): WP_REST_Response {
        $page      = max( 1, absint( $request->get_param( 'page' ) ?: 1 ) );
        $per_page  = absint( $request->get_param( 'per_page' ) ?: 20 );
        $per_page  = max( 1, min( self::MAX_PER_PAGE, $per_page ) );
        $categorie = sanitize_title( (string) ( $request->get_param( 'categorie' ) ?: '' ) );

        $args = [
            'post_type'              => CPT_Cursussen::POST_TYPE,
            'post_status'            => 'publish',
            'posts_per_page'         => $per_page,
            'paged'                  => $page,
            'orderby'                => 'meta_value',
            'meta_key'               => 'startdatum',
            'order'                  => 'ASC',
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
        ];

        if ( '' !== $categorie ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => CPT_Cursussen::TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => $categorie,
                ],
            ];
        }

        $query  = new WP_Query( $args );
        $result = [];

        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = absint( get_the_ID() );

            $result[] = [
                'id'                  => $post_id,
                'title'               => sanitize_text_field( wp_strip_all_tags( get_the_title( $post_id ) ) ),
                'startdatum'          => sanitize_text_field( (string) get_post_meta( $post_id, 'startdatum', true ) ),
                'opleidingstype'      => sanitize_text_field( (string) get_post_meta( $post_id, 'opleidingstype', true ) ),
                'starttijd'           => sanitize_text_field( (string) get_post_meta( $post_id, 'starttijd', true ) ),
                'eindtijd'            => sanitize_text_field( (string) get_post_meta( $post_id, 'eindtijd', true ) ),
                'bijeenkomsten'       => sanitize_text_field( (string) get_post_meta( $post_id, 'bijeenkomsten', true ) ),
                'inschrijven'         => sanitize_text_field( (string) get_post_meta( $post_id, 'inschrijven', true ) ),
                'beschikbare_plekken' => absint( get_post_meta( $post_id, 'beschikbare_plekken', true ) ),
                'categorie'           => $this->get_categorie_names( $post_id ),
                'link'                => esc_url_raw( (string) get_permalink( $post_id ) ),
                'thumbnail'           => esc_url_raw( (string) ( get_the_post_thumbnail_url( $post_id, 'medium' ) ?: '' ) ),
            ];
        }

        wp_reset_postdata();

        $response = rest_ensure_response( $result );
        $response->header( 'X-WP-Total', (string) absint( $query->found_posts ) );
        $response->header( 'X-WP-TotalPages', (string) absint( $query->max_num_pages ) );

        return $response;
    }

    private function get_categorie_names( int $post_id ): array {
        $terms = get_the_terms( $post_id, CPT_Cursussen::TAXONOMY );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            return array_map( 'sanitize_text_field', wp_list_pluck( $terms, 'name' ) );
        }
        return [];
    }
}
