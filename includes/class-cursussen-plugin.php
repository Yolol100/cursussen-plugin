<?php
declare(strict_types=1);

namespace SodriveAcademie;

class CursussenPlugin
{
    public function run(): void
    {
        $this->load_dependencies();
        $this->initialize_components();
        $this->load_textdomain();
    }

    private function load_dependencies(): void
    {
        require_once plugin_dir_path(__FILE__) . 'class-cpt-cursussen.php';
        require_once plugin_dir_path(__FILE__) . 'class-meta-boxes.php';
        require_once plugin_dir_path(__FILE__) . 'class-rest-api.php';
        require_once plugin_dir_path(__FILE__) . 'class-shortcodes.php';
        require_once plugin_dir_path(__FILE__) . 'class-assets-manager.php';
    }

    private function initialize_components(): void
    {
        new CPT_Cursussen();
        new Meta_Boxes();
        new REST_API();
        new Shortcodes();
        new Assets_Manager();
    }

    private function load_textdomain(): void
    {
        load_plugin_textdomain(
            'cursussen-plugin',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
}