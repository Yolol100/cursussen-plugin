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

require_once __DIR__ . '/includes/class-cpt-cursussen.php';

$cpt = new \SodriveAcademie\CPT_Cursussen();
$cpt->register_custom_post_type();
$cpt->register_custom_taxonomies();

while ( true ) {
    $post_ids = get_posts(
        [
            'post_type'              => \SodriveAcademie\CPT_Cursussen::POST_TYPE,
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
        'taxonomy'   => \SodriveAcademie\CPT_Cursussen::TAXONOMY,
        'hide_empty' => false,
        'fields'     => 'ids',
    ]
);

if ( ! is_wp_error( $terms ) ) {
    foreach ( $terms as $term_id ) {
        wp_delete_term( (int) $term_id, \SodriveAcademie\CPT_Cursussen::TAXONOMY );
    }
}

delete_option( 'cursussen_plugin_delete_data_on_uninstall' );
flush_rewrite_rules();
