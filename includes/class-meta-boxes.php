<?php
declare(strict_types=1);

namespace SodriveAcademie;

use WP_Post;

class Meta_Boxes {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_custom_fields_metabox']);
        add_action('save_post', [$this, 'save_custom_fields']);
    }

    /**
     * Voeg een custom meta box toe aan de 'cursussen' post type.
     */
    public function add_custom_fields_metabox(): void {
        add_meta_box(
            'cursussen_custom_fields',
            __('Cursus Details', 'cursussen-plugin'),
            [$this, 'custom_fields_callback'],
            'cursussen',
            'normal',
            'high'
        );
    }

    /**
     * HTML output voor de custom fields in de meta box.
     *
     * @param WP_Post $post Het huidige post-object.
     */
    public function custom_fields_callback(WP_Post $post): void {
        // Haal opgeslagen metadata op of gebruik standaardwaarden
        $custom_fields = get_post_custom($post->ID);
        $fields = [
            'startdatum'         => '',
            'opleidingstype'     => '',
            'starttijd'          => '',
            'eindtijd'           => '',
            'bijeenkomsten'      => '',
            'inschrijven'        => 'Inschrijven',
            'beschikbare_plekken'=> 0,
        ];

        foreach ($fields as $field => $default) {
            $fields[$field] = isset($custom_fields[$field])
                ? sanitize_text_field($custom_fields[$field][0])
                : $default;
        }

        // Voeg een nonce toe voor beveiliging
        wp_nonce_field('cursussen_custom_fields', 'cursussen_custom_fields_nonce');
        ?>
        <div class="custom-cursussen-details">
            <table class="form-table">
                <?php foreach ($fields as $field => $value): ?>
                    <tr id="<?php echo esc_attr($field); ?>_row" <?php echo ($field === 'beschikbare_plekken' && $fields['inschrijven'] === 'Vol') ? 'style="display:none;"' : ''; ?>>
                        <th>
                            <label for="<?php echo esc_attr($field); ?>">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $field))); ?>:
                            </label>
                        </th>
                        <td>
                            <?php if ($field === 'opleidingstype'): ?>
                                <select name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>">
                                    <option value="klassikaal" <?php selected($value, 'klassikaal'); ?>>
                                        <?php _e('Klassikaal', 'cursussen-plugin'); ?>
                                    </option>
                                    <option value="online" <?php selected($value, 'online'); ?>>
                                        <?php _e('Online', 'cursussen-plugin'); ?>
                                    </option>
                                </select>
                            <?php elseif ($field === 'inschrijven'): ?>
                                <select name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>" onchange="toggleBeschikbarePlekken(this.value)">
                                    <option value="Inschrijven" <?php selected($value, 'Inschrijven'); ?>>
                                        <?php _e('Inschrijven', 'cursussen-plugin'); ?>
                                    </option>
                                    <option value="Vol" <?php selected($value, 'Vol'); ?>>
                                        <?php _e('Vol', 'cursussen-plugin'); ?>
                                    </option>
                                </select>
                            <?php elseif ($field === 'beschikbare_plekken'): ?>
                                <input type="number" name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($value); ?>" min="0">
                            <?php elseif ($field === 'startdatum'): ?>
                                <input type="date" name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($value); ?>">
                            <?php else: ?>
                                <input type="text" name="<?php echo esc_attr($field); ?>" id="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($value); ?>">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <script type="text/javascript">
            const toggleBeschikbarePlekken = (value) => {
                const beschikbaarPlekkenRow = document.getElementById('beschikbare_plekken_row');
                beschikbaarPlekkenRow.style.display = (value === 'Vol') ? 'none' : 'table-row';
            };
            document.addEventListener('DOMContentLoaded', () => {
                toggleBeschikbarePlekken(document.getElementById('inschrijven').value);
            });
        </script>
        <?php
    }

    /**
     * Validatie en opslag van custom fields.
     *
     * @param int $post_id Het ID van de huidige post.
     */
    public function save_custom_fields(int $post_id): void {
        // Controleer op autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        // Controleer of de gebruiker toestemming heeft om de post te bewerken
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        // Controleer de nonce
        if (!isset($_POST['cursussen_custom_fields_nonce']) || !wp_verify_nonce($_POST['cursussen_custom_fields_nonce'], 'cursussen_custom_fields')) {
            return;
        }
        // Definieer de velden die opgeslagen moeten worden
        $fields = ['startdatum', 'opleidingstype', 'starttijd', 'eindtijd', 'bijeenkomsten', 'inschrijven', 'beschikbare_plekken'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $sanitized_value = in_array($field, ['startdatum', 'starttijd', 'eindtijd'], true)
                    ? sanitize_text_field($_POST[$field])
                    : ($field === 'beschikbare_plekken'
                        ? intval($_POST[$field])
                        : sanitize_text_field($_POST[$field])
                    );
                update_post_meta($post_id, $field, $sanitized_value);
            }
        }
        // Als 'inschrijven' op "Vol" staat, zet dan 'beschikbare_plekken' op 0
        if (isset($_POST['inschrijven']) && $_POST['inschrijven'] === 'Vol') {
            update_post_meta($post_id, 'beschikbare_plekken', 0);
        }
    }
}

// Initialiseer de Meta_Boxes klasse
new Meta_Boxes();