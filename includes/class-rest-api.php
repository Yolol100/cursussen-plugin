<?php
declare(strict_types=1);

namespace SodriveAcademie;

use WP_Query;
use WP_REST_Request;
use WP_REST_Response;

class REST_API {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    /**
     * Registreert de custom REST API routes voor cursussen.
     */
    public function register_rest_routes(): void {
        register_rest_route('cursussen/v1', '/all', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_all_cursussen'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('cursussen/v1', '/filter', [
            'methods'             => 'GET',
            'callback'            => [$this, 'filter_cursussen'],
            'permission_callback' => '__return_true',
            'args'                => [
                'categorie' => [
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'aantal' => [
                    'required'          => false,
                    'default'           => -1,
                    'sanitize_callback' => 'intval',
                ],
            ],
        ]);
    }

    /**
     * Haalt alle cursussen op zonder filters.
     *
     * @return WP_REST_Response Lijst van cursussen.
     */
    public function get_all_cursussen(): WP_REST_Response {
        $args = [
            'post_type'      => 'cursussen',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value',
            'meta_key'       => 'startdatum',
            'order'          => 'ASC',
        ];

        $query = new WP_Query($args);
        $result = [];

        while ($query->have_posts()) {
            $query->the_post();
            $result[] = [
                'id'         => get_the_ID(),
                'title'      => get_the_title(),
                'startdatum' => get_post_meta(get_the_ID(), 'startdatum', true),
                'categorie'  => $this->get_categorie_names((int) get_the_ID()),
                'link'       => get_permalink(),
                'thumbnail'  => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
            ];
        }

        wp_reset_postdata();

        return rest_ensure_response($result);
    }

    /**
     * Filtert cursussen op basis van categorie of aantal.
     *
     * @param WP_REST_Request $request Het REST API verzoek.
     * @return WP_REST_Response Gefilterde lijst van cursussen.
     */
    public function filter_cursussen(WP_REST_Request $request): WP_REST_Response {
        $categorie = $request->get_param('categorie');
        $aantal = $request->get_param('aantal');

        $args = [
            'post_type'      => 'cursussen',
            'posts_per_page' => $aantal,
            'orderby'        => 'meta_value',
            'meta_key'       => 'startdatum',
            'order'          => 'ASC',
        ];

        if (!empty($categorie)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'cursus_categorie',
                    'field'    => 'slug',
                    'terms'    => $categorie,
                ],
            ];
        }

        $query = new WP_Query($args);
        $result = [];

        while ($query->have_posts()) {
            $query->the_post();
            $result[] = [
                'id'         => get_the_ID(),
                'title'      => get_the_title(),
                'startdatum' => get_post_meta(get_the_ID(), 'startdatum', true),
                'categorie'  => $this->get_categorie_names((int) get_the_ID()),
                'link'       => get_permalink(),
                'thumbnail'  => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
            ];
        }

        wp_reset_postdata();

        return rest_ensure_response($result);
    }

    /**
     * Haalt de namen van categorieën op voor een cursus.
     *
     * @param int $post_id Het ID van de cursus.
     * @return string[] Lijst van categorienamen.
     */
    private function get_categorie_names(int $post_id): array {
        $terms = get_the_terms($post_id, 'cursus_categorie');
        if (!empty($terms) && !is_wp_error($terms)) {
            return wp_list_pluck($terms, 'name');
        }
        return [];
    }
}