<?php
/**
 * Uninstall cleanup for Cursussen.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$delete_data = (bool) get_option( 'cursussen_plugin_delete_data_on_uninstall', false );

if ( ! $delete_data ) {
    return;
}

while ( true ) {
    $post_ids = get_posts(
        [
            'post_type'              => 'cursussen',
            'post_status'            => 'any',
            'posts_per_page'         => 100,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]
    );

    if ( empty( $post_ids ) ) {
        break;
    }

    foreach ( $post_ids as $post_id ) {
        wp_delete_post( (int) $post_id, true );
    }
}

$terms = get_terms(
    [
        'taxonomy'   => 'cursus_categorie',
        'hide_empty' => false,
        'fields'     => 'ids',
    ]
);

if ( ! is_wp_error( $terms ) ) {
    foreach ( $terms as $term_id ) {
        wp_delete_term( (int) $term_id, 'cursus_categorie' );
    }
}

delete_option( 'cursussen_plugin_delete_data_on_uninstall' );
flush_rewrite_rules();
