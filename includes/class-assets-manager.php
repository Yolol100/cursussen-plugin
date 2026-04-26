<?php
declare(strict_types=1);

namespace SodriveAcademie;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Assets_Manager {
    private static $instance = null;

    public function __construct() {
        if ( self::$instance instanceof self ) {
            return;
        }

        self::$instance = $this;
        add_action( 'wp_enqueue_scripts', [ $this, 'register_frontend_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    public static function enqueue_shortcode_assets(): void {
        $manager = self::$instance instanceof self ? self::$instance : new self();
        $manager->enqueue_frontend_assets();
    }

    public function register_frontend_assets(): void {
        wp_register_style(
            'cursussen-plugin-frontend-styles',
            CURSUSSEN_PLUGIN_URL . 'assets/css/custom-plugin-styles.css',
            [],
            $this->asset_version( 'assets/css/custom-plugin-styles.css' )
        );

        wp_register_script(
            'cursussen-plugin-frontend-scripts',
            CURSUSSEN_PLUGIN_URL . 'assets/js/custom-plugin-scripts.js',
            [ 'jquery' ],
            $this->asset_version( 'assets/js/custom-plugin-scripts.js' ),
            true
        );
    }

    public function enqueue_frontend_assets(): void {
        if ( ! wp_style_is( 'cursussen-plugin-frontend-styles', 'registered' ) ) {
            $this->register_frontend_assets();
        }

        wp_enqueue_style( 'cursussen-plugin-frontend-styles' );
        wp_enqueue_script( 'cursussen-plugin-frontend-scripts' );
    }

    public function enqueue_admin_assets( string $hook ): void {
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            return;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || CPT_Cursussen::POST_TYPE !== $screen->post_type ) {
            return;
        }

        wp_enqueue_style(
            'cursussen-plugin-admin-styles',
            CURSUSSEN_PLUGIN_URL . 'assets/css/admin-plugin-styles.css',
            [],
            $this->asset_version( 'assets/css/admin-plugin-styles.css' )
        );

        wp_enqueue_script(
            'cursussen-plugin-admin-scripts',
            CURSUSSEN_PLUGIN_URL . 'assets/js/admin-plugin-scripts.js',
            [],
            $this->asset_version( 'assets/js/admin-plugin-scripts.js' ),
            true
        );
    }

    private function asset_version( string $relative_path ): string {
        $path = CURSUSSEN_PLUGIN_DIR . ltrim( $relative_path, '/' );
        return file_exists( $path ) ? (string) filemtime( $path ) : CURSUSSEN_PLUGIN_VERSION;
    }
}
