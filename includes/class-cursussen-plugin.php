<?php
declare(strict_types=1);

namespace SodriveAcademie;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CursussenPlugin {
    public function run(): void {
        $this->load_dependencies();
        $this->load_textdomain();
        $this->initialize_components();
    }

    private function load_dependencies(): void {
        require_once CURSUSSEN_PLUGIN_DIR . 'includes/class-cpt-cursussen.php';
        require_once CURSUSSEN_PLUGIN_DIR . 'includes/class-meta-boxes.php';
        require_once CURSUSSEN_PLUGIN_DIR . 'includes/class-rest-api.php';
        require_once CURSUSSEN_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once CURSUSSEN_PLUGIN_DIR . 'includes/class-assets-manager.php';
    }

    private function initialize_components(): void {
        new CPT_Cursussen();
        new Meta_Boxes();
        new REST_API();
        new Assets_Manager();
        new Shortcodes();

        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_menu', [ $this, 'register_settings_page' ] );
    }

    public function register_settings(): void {
        register_setting(
            'cursussen_plugin_settings',
            'cursussen_plugin_delete_data_on_uninstall',
            [
                'type'              => 'boolean',
                'sanitize_callback' => static function ( $value ): bool {
                    return (bool) $value;
                },
                'default'           => false,
            ]
        );
    }

    public function register_settings_page(): void {
        add_submenu_page(
            'edit.php?post_type=' . CPT_Cursussen::POST_TYPE,
            esc_html__( 'Cursussen instellingen', 'cursussen-plugin' ),
            esc_html__( 'Instellingen', 'cursussen-plugin' ),
            'manage_options',
            'cursussen-plugin-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Je hebt geen toestemming om deze pagina te bekijken.', 'cursussen-plugin' ) );
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Cursussen instellingen', 'cursussen-plugin' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'cursussen_plugin_settings' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php echo esc_html__( 'Data verwijderen bij uninstall', 'cursussen-plugin' ); ?></th>
                        <td>
                            <label for="cursussen_plugin_delete_data_on_uninstall">
                                
                                <input type="hidden" name="cursussen_plugin_delete_data_on_uninstall" value="0"><input type="checkbox" id="cursussen_plugin_delete_data_on_uninstall" name="cursussen_plugin_delete_data_on_uninstall" value="1" <?php checked( (bool) get_option( 'cursussen_plugin_delete_data_on_uninstall', false ) ); ?>>
                                <?php echo esc_html__( 'Verwijder alle cursusberichten en plugininstellingen wanneer de plugin wordt verwijderd.', 'cursussen-plugin' ); ?>
                            </label>
                            <p class="description"><?php echo esc_html__( 'Laat dit uit als je cursusdata wilt bewaren wanneer de plugin tijdelijk wordt verwijderd.', 'cursussen-plugin' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    private function load_textdomain(): void {
        load_plugin_textdomain(
            'cursussen-plugin',
            false,
            dirname( plugin_basename( CURSUSSEN_PLUGIN_FILE ) ) . '/languages'
        );
    }
}
