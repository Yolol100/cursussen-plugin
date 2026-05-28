<?php
/**
 * Plugin Name: Cursussen
 * Description: Beheer en toon cursusinformatie met een custom post type, shortcode en publieke REST API.
 * Version: 1.4.2
 * Author: Sodriveacademie
 * Text Domain: cursussen-plugin
 * Domain Path: /languages
 * Requires at least: 6.3
 * Tested up to: 6.7
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CURSUSSEN_PLUGIN_VERSION', '1.4.2' );
define( 'CURSUSSEN_PLUGIN_FILE', __FILE__ );
define( 'CURSUSSEN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CURSUSSEN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CURSUSSEN_PLUGIN_DIR . 'includes/class-cpt-cursussen.php';
require_once CURSUSSEN_PLUGIN_DIR . 'includes/class-cursussen-plugin.php';

function cursussen_plugin_activate(): void {
    $cpt = new \SodriveAcademie\CPT_Cursussen();
    $cpt->register_custom_post_type();
    $cpt->register_custom_taxonomies();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'cursussen_plugin_activate' );

function cursussen_plugin_deactivate(): void {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'cursussen_plugin_deactivate' );

function run_cursussen_plugin(): void {
    $plugin = new \SodriveAcademie\CursussenPlugin();
    $plugin->run();
}
run_cursussen_plugin();
