<?php
declare(strict_types=1);

namespace SodriveAcademie;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPT_Cursussen {
    public const POST_TYPE = 'cursussen';
    public const TAXONOMY  = 'cursus_categorie';

    public function __construct() {
        add_action( 'init', [ $this, 'register_custom_post_type' ] );
        add_action( 'init', [ $this, 'register_custom_taxonomies' ] );
        add_filter( 'manage_cursussen_posts_columns', [ $this, 'add_custom_columns' ] );
        add_action( 'manage_cursussen_posts_custom_column', [ $this, 'populate_custom_columns' ], 10, 2 );
    }

    public function register_custom_post_type(): void {
        $labels = [
            'name'                  => esc_html__( 'Cursussen', 'cursussen-plugin' ),
            'singular_name'         => esc_html__( 'Cursus', 'cursussen-plugin' ),
            'menu_name'             => esc_html__( 'Cursussen', 'cursussen-plugin' ),
            'name_admin_bar'        => esc_html__( 'Cursus', 'cursussen-plugin' ),
            'add_new'               => esc_html__( 'Nieuwe cursus', 'cursussen-plugin' ),
            'add_new_item'          => esc_html__( 'Nieuwe cursus toevoegen', 'cursussen-plugin' ),
            'edit_item'             => esc_html__( 'Cursus bewerken', 'cursussen-plugin' ),
            'new_item'              => esc_html__( 'Nieuwe cursus', 'cursussen-plugin' ),
            'view_item'             => esc_html__( 'Cursus bekijken', 'cursussen-plugin' ),
            'view_items'            => esc_html__( 'Cursussen bekijken', 'cursussen-plugin' ),
            'search_items'          => esc_html__( 'Cursussen zoeken', 'cursussen-plugin' ),
            'not_found'             => esc_html__( 'Geen cursussen gevonden', 'cursussen-plugin' ),
            'not_found_in_trash'    => esc_html__( 'Geen cursussen gevonden in de prullenbak', 'cursussen-plugin' ),
            'all_items'             => esc_html__( 'Alle cursussen', 'cursussen-plugin' ),
            'archives'              => esc_html__( 'Cursusarchief', 'cursussen-plugin' ),
            'attributes'            => esc_html__( 'Cursus eigenschappen', 'cursussen-plugin' ),
            'insert_into_item'      => esc_html__( 'Invoegen in cursus', 'cursussen-plugin' ),
            'uploaded_to_this_item' => esc_html__( 'Geüpload naar deze cursus', 'cursussen-plugin' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'has_archive'        => true,
            'rewrite'            => [ 'slug' => 'cursussen', 'with_front' => false ],
            'supports'           => [ 'title', 'editor', 'excerpt', 'thumbnail' ],
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-welcome-learn-more',
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    public function register_custom_taxonomies(): void {
        $labels = [
            'name'              => esc_html__( 'Cursus categorieën', 'cursussen-plugin' ),
            'singular_name'     => esc_html__( 'Cursus categorie', 'cursussen-plugin' ),
            'search_items'      => esc_html__( 'Categorieën zoeken', 'cursussen-plugin' ),
            'all_items'         => esc_html__( 'Alle categorieën', 'cursussen-plugin' ),
            'parent_item'       => esc_html__( 'Hoofdcategorie', 'cursussen-plugin' ),
            'parent_item_colon' => esc_html__( 'Hoofdcategorie:', 'cursussen-plugin' ),
            'edit_item'         => esc_html__( 'Categorie bewerken', 'cursussen-plugin' ),
            'update_item'       => esc_html__( 'Categorie bijwerken', 'cursussen-plugin' ),
            'add_new_item'      => esc_html__( 'Nieuwe categorie toevoegen', 'cursussen-plugin' ),
            'new_item_name'     => esc_html__( 'Nieuwe categorienaam', 'cursussen-plugin' ),
            'menu_name'         => esc_html__( 'Categorieën', 'cursussen-plugin' ),
        ];

        $args = [
            'labels'            => $labels,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => [ 'slug' => 'cursus-categorie', 'with_front' => false ],
        ];

        register_taxonomy( self::TAXONOMY, [ self::POST_TYPE ], $args );
    }

    public function add_custom_columns( array $columns ): array {
        return [
            'cb'         => $columns['cb'] ?? '',
            'title'      => esc_html__( 'Cursus', 'cursussen-plugin' ),
            'startdatum' => esc_html__( 'Startdatum', 'cursussen-plugin' ),
            'categorie'  => esc_html__( 'Categorie', 'cursussen-plugin' ),
            'date'       => $columns['date'] ?? '',
        ];
    }

    public function populate_custom_columns( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'startdatum':
                $startdatum = (string) get_post_meta( $post_id, 'startdatum', true );
                echo esc_html( $startdatum ?: esc_html__( 'Niet ingesteld', 'cursussen-plugin' ) );
                break;
            case 'categorie':
                $terms = get_the_terms( $post_id, self::TAXONOMY );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
                } else {
                    echo esc_html__( 'Geen categorie', 'cursussen-plugin' );
                }
                break;
        }
    }
}
