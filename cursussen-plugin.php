<?php
/**
 * Plugin Name: Cursussen
 * Description: Een plugin voor het beheren en weergeven van cursusinformatie.
 * Version: 1.3
 * Author: Sodriveacademie
 * Text Domain: cursussen-plugin
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Autoload classes
require_once plugin_dir_path(__FILE__) . 'includes/class-cursussen-plugin.php';

// Start de plugin
function run_cursussen_plugin() {
    $plugin = new \SodriveAcademie\CursussenPlugin();
    $plugin->run();
}
run_cursussen_plugin();