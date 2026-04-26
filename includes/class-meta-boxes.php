<?php
declare(strict_types=1);

namespace SodriveAcademie;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WP_Post;

class Meta_Boxes {
    private const NONCE_ACTION = 'cursussen_custom_fields';
    private const NONCE_NAME   = 'cursussen_custom_fields_nonce';

    private const FIELDS = [
        'startdatum'          => 'date',
        'opleidingstype'      => 'opleidingstype',
        'starttijd'           => 'time',
        'eindtijd'            => 'time',
        'bijeenkomsten'       => 'text',
        'inschrijven'         => 'inschrijven',
        'beschikbare_plekken' => 'int',
    ];

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_custom_fields_metabox' ] );
        add_action( 'save_post_' . CPT_Cursussen::POST_TYPE, [ $this, 'save_custom_fields' ], 10, 2 );
    }

    public function add_custom_fields_metabox(): void {
        add_meta_box(
            'cursussen_custom_fields',
            esc_html__( 'Cursus details', 'cursussen-plugin' ),
            [ $this, 'custom_fields_callback' ],
            CPT_Cursussen::POST_TYPE,
            'normal',
            'high'
        );
    }

    public function custom_fields_callback( WP_Post $post ): void {
        $values = $this->get_field_values( $post->ID );
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
        ?>
        <div class="sda-cursussen-admin-details">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="startdatum"><?php echo esc_html__( 'Startdatum', 'cursussen-plugin' ); ?></label></th>
                        <td><input type="date" class="regular-text" name="startdatum" id="startdatum" value="<?php echo esc_attr( $values['startdatum'] ); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="opleidingstype"><?php echo esc_html__( 'Opleidingstype', 'cursussen-plugin' ); ?></label></th>
                        <td>
                            <select name="opleidingstype" id="opleidingstype">
                                <option value="klassikaal" <?php selected( $values['opleidingstype'], 'klassikaal' ); ?>><?php echo esc_html__( 'Klassikaal', 'cursussen-plugin' ); ?></option>
                                <option value="online" <?php selected( $values['opleidingstype'], 'online' ); ?>><?php echo esc_html__( 'Online', 'cursussen-plugin' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="starttijd"><?php echo esc_html__( 'Starttijd', 'cursussen-plugin' ); ?></label></th>
                        <td><input type="time" class="regular-text" name="starttijd" id="starttijd" value="<?php echo esc_attr( $values['starttijd'] ); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eindtijd"><?php echo esc_html__( 'Eindtijd', 'cursussen-plugin' ); ?></label></th>
                        <td><input type="time" class="regular-text" name="eindtijd" id="eindtijd" value="<?php echo esc_attr( $values['eindtijd'] ); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bijeenkomsten"><?php echo esc_html__( 'Bijeenkomsten', 'cursussen-plugin' ); ?></label></th>
                        <td><input type="text" class="regular-text" name="bijeenkomsten" id="bijeenkomsten" value="<?php echo esc_attr( $values['bijeenkomsten'] ); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="inschrijven"><?php echo esc_html__( 'Inschrijfstatus', 'cursussen-plugin' ); ?></label></th>
                        <td>
                            <select name="inschrijven" id="inschrijven" data-cursussen-toggle-target="beschikbare_plekken_row">
                                <option value="Inschrijven" <?php selected( $values['inschrijven'], 'Inschrijven' ); ?>><?php echo esc_html__( 'Inschrijven', 'cursussen-plugin' ); ?></option>
                                <option value="Vol" <?php selected( $values['inschrijven'], 'Vol' ); ?>><?php echo esc_html__( 'Vol', 'cursussen-plugin' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr id="beschikbare_plekken_row" <?php echo 'Vol' === $values['inschrijven'] ? 'style="display:none;"' : ''; ?>>
                        <th scope="row"><label for="beschikbare_plekken"><?php echo esc_html__( 'Beschikbare plekken', 'cursussen-plugin' ); ?></label></th>
                        <td><input type="number" min="0" step="1" class="small-text" name="beschikbare_plekken" id="beschikbare_plekken" value="<?php echo esc_attr( (string) $values['beschikbare_plekken'] ); ?>"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function save_custom_fields( int $post_id, WP_Post $post ): void {
        if ( CPT_Cursussen::POST_TYPE !== $post->post_type ) {
            return;
        }

        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $nonce = isset( $_POST[ self::NONCE_NAME ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ) : '';
        if ( ! $nonce || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
            return;
        }

        foreach ( self::FIELDS as $field => $type ) {
            $raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : null;
            $value = $this->sanitize_field( $type, $raw );
            update_post_meta( $post_id, $field, $value );
        }

        if ( 'Vol' === (string) get_post_meta( $post_id, 'inschrijven', true ) ) {
            update_post_meta( $post_id, 'beschikbare_plekken', 0 );
        }
    }

    private function get_field_values( int $post_id ): array {
        $defaults = [
            'startdatum'          => '',
            'opleidingstype'      => 'klassikaal',
            'starttijd'           => '',
            'eindtijd'            => '',
            'bijeenkomsten'       => '',
            'inschrijven'         => 'Inschrijven',
            'beschikbare_plekken' => 0,
        ];

        foreach ( array_keys( $defaults ) as $field ) {
            $stored = get_post_meta( $post_id, $field, true );
            if ( '' !== $stored && null !== $stored ) {
                $defaults[ $field ] = is_scalar( $stored ) ? $stored : '';
            }
        }

        $defaults['opleidingstype'] = $this->sanitize_field( 'opleidingstype', $defaults['opleidingstype'] );
        $defaults['inschrijven'] = $this->sanitize_field( 'inschrijven', $defaults['inschrijven'] );
        $defaults['beschikbare_plekken'] = absint( $defaults['beschikbare_plekken'] );
        $defaults['startdatum'] = $this->sanitize_field( 'date', $defaults['startdatum'] );
        $defaults['starttijd'] = $this->sanitize_field( 'time', $defaults['starttijd'] );
        $defaults['eindtijd'] = $this->sanitize_field( 'time', $defaults['eindtijd'] );
        $defaults['bijeenkomsten'] = sanitize_text_field( (string) $defaults['bijeenkomsten'] );

        return $defaults;
    }

    private function sanitize_field( string $type, $raw ) {
        $value = is_scalar( $raw ) ? (string) $raw : '';

        switch ( $type ) {
            case 'opleidingstype':
                return in_array( $value, [ 'klassikaal', 'online' ], true ) ? $value : 'klassikaal';
            case 'inschrijven':
                return in_array( $value, [ 'Inschrijven', 'Vol' ], true ) ? $value : 'Inschrijven';
            case 'int':
                return absint( $value );
            case 'date':
                if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches ) ) {
                    return '';
                }
                return checkdate( (int) $matches[2], (int) $matches[3], (int) $matches[1] ) ? $value : '';
            case 'time':
                return preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '';
            case 'text':
            default:
                return sanitize_text_field( $value );
        }
    }
}
