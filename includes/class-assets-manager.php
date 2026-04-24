<?php
declare(strict_types=1);

namespace SodriveAcademie;

class Assets_Manager {
    public function __construct() {
        // Registreer hooks voor frontend- en admin-assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Laadt de CSS- en JavaScript-bestanden voor de frontend.
     */
    public function enqueue_frontend_assets(): void {
        // Laad de frontend CSS
        wp_enqueue_style(
            'cursussen-plugin-frontend-styles',
            plugin_dir_url(__FILE__) . '../assets/css/custom-plugin-styles.css',
            [],
            '1.0.0'
        );

        // Laad de frontend JavaScript
        wp_enqueue_script(
            'cursussen-plugin-frontend-scripts',
            plugin_dir_url(__FILE__) . '../assets/js/custom-plugin-scripts.js',
            ['jquery'],
            '1.0.0',
            true
        );

        // Voeg een inline script toe voor configuratie
        wp_add_inline_script(
            'cursussen-plugin-frontend-scripts',
            "var cursussenSettings = {
                ajaxUrl: '" . esc_url(admin_url('admin-ajax.php')) . "',
                nonce: '" . esc_js(wp_create_nonce('cursussen_nonce')) . "'
            };"
        );
    }

    /**
     * Laadt de CSS- en JavaScript-bestanden voor de WordPress-adminomgeving.
     *
     * @param string $hook De huidige adminpagina.
     */
    public function enqueue_admin_assets(string $hook): void {
        // Controleer of de juiste adminpagina wordt geladen
        if ($hook === 'post.php' || $hook === 'post-new.php') {
            global $post;

            // Alleen assets laden voor het aangepaste posttype 'cursussen'
            if (isset($post->post_type) && $post->post_type === 'cursussen') {
                // Laad de admin CSS
                wp_enqueue_style(
                    'cursussen-plugin-admin-styles',
                    plugin_dir_url(__FILE__) . '../assets/css/admin-plugin-styles.css',
                    [],
                    '1.0.0'
                );

                // Laad de admin JavaScript
                wp_enqueue_script(
                    'cursussen-plugin-admin-scripts',
                    plugin_dir_url(__FILE__) . '../assets/js/admin-plugin-scripts.js',
                    ['jquery'],
                    '1.0.0',
                    true
                );

                // Voeg een inline script toe voor adminconfiguratie
                wp_add_inline_script(
                    'cursussen-plugin-admin-scripts',
                    "jQuery(document).ready(function($) {
                        console.log('Cursussen admin script geladen.');
                    });"
                );
            }
        }
    }
}