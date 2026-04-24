<?php
declare(strict_types=1);

namespace SodriveAcademie;

class Shortcodes {
    public function __construct() {
        // Registreer de shortcode
        add_shortcode('toon_cursussen', [$this, 'toon_cursussen_shortcode']);
    }

    /**
     * Render de lijst van cursussen via de shortcode [toon_cursussen].
     *
     * @param array $atts Shortcode attributen.
     * @return string HTML-output van de lijst.
     */
    public function toon_cursussen_shortcode(array $atts): string {
        // Standaard attributen
        $atts = shortcode_atts([
            'categorie' => '',
            'aantal'    => -1,
        ], $atts, 'toon_cursussen');

        // Query-argumenten
        $args = [
            'post_type'      => 'cursussen',
            'posts_per_page' => (int)$atts['aantal'],
            'orderby'        => 'meta_value',
            'meta_key'       => 'startdatum',
            'order'          => 'ASC',
        ];

        // Voeg categorie filter toe indien ingesteld
        if (!empty($atts['categorie'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'cursus_categorie',
                    'field'    => 'slug',
                    'terms'    => $atts['categorie'],
                ],
            ];
        }

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            ob_start(); // Start output buffering
            ?>
            <div class="cursus-lijst">
                <!-- Tabel voor Desktop -->
                <div class="desktop-view">
                    <table class="table-cursussen">
                        <thead>
                            <tr>
                                <th><?php _e('Startdatum', 'cursussen-plugin'); ?></th>
                                <th><?php _e('Opleiding', 'cursussen-plugin'); ?></th>
                                <th><?php _e('Tijd', 'cursussen-plugin'); ?></th>
                                <th><?php _e('Bijeenkomsten', 'cursussen-plugin'); ?></th>
                                <th><?php _e('Beschikbare plekken', 'cursussen-plugin'); ?></th>
                                <th><?php _e('Actie', 'cursussen-plugin'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($query->have_posts()): $query->the_post(); ?>
                                <tr>
                                    <td>
                                        <?php
                                        $startdatum = get_post_meta(get_the_ID(), 'startdatum', true);
                                        if ($startdatum) {
                                            $timezone = wp_timezone();
                                            $datetime = new \DateTime($startdatum, $timezone);
                                            $datetime->modify('+1 day');
                                            echo esc_html(date_i18n('d-m-Y', $datetime->getTimestamp()));
                                        } else {
                                            echo __('Niet ingesteld', 'cursussen-plugin');
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $opleidingstype = get_post_meta(get_the_ID(), 'opleidingstype', true);
                                        echo $opleidingstype ? esc_html(ucfirst($opleidingstype)) : __('Niet opgegeven', 'cursussen-plugin');
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $starttijd = get_post_meta(get_the_ID(), 'starttijd', true);
                                        $eindtijd = get_post_meta(get_the_ID(), 'eindtijd', true);
                                        echo ($starttijd && $eindtijd) ? esc_html($starttijd . ' - ' . $eindtijd) : __('Niet opgegeven', 'cursussen-plugin');
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $bijeenkomsten = get_post_meta(get_the_ID(), 'bijeenkomsten', true);
                                        echo $bijeenkomsten ? esc_html($bijeenkomsten) : __('Niet opgegeven', 'cursussen-plugin');
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $beschikbare_plekken = get_post_meta(get_the_ID(), 'beschikbare_plekken', true);
                                        error_log("Beschikbare plekken (rauwe waarde): " . print_r($beschikbare_plekken, true));
                                        echo intval($beschikbare_plekken) > 0 ? intval($beschikbare_plekken) : __('Geen plekken', 'cursussen-plugin');
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $inschrijven = get_post_meta(get_the_ID(), 'inschrijven', true);
                                        if ($inschrijven === 'Vol') {
                                            echo '<span class="custom-red disabled" data-inschrijven="Geen plekken">' . __('Vol', 'cursussen-plugin') . '</span>';
                                        } else {
                                            echo '<a href="https://test.sodriveacademie.nl/inschrijven/" class="custom-inschrijven-btn hidden-mobile" data-inschrijven="Inschrijven">' . __('Inschrijven', 'cursussen-plugin') . '</a>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Toggle voor Mobiel/Tablet -->
                <div class="mobile-view cursussen-toggle-container">
                    <?php while ($query->have_posts()): $query->the_post(); ?>
                        <?php
                        $startdatum = get_post_meta(get_the_ID(), 'startdatum', true);
                        $opleidingstype = get_post_meta(get_the_ID(), 'opleidingstype', true);
                        $starttijd = get_post_meta(get_the_ID(), 'starttijd', true);
                        $eindtijd = get_post_meta(get_the_ID(), 'eindtijd', true);
                        $bijeenkomsten = get_post_meta(get_the_ID(), 'bijeenkomsten', true);
                        $beschikbare_plekken = get_post_meta(get_the_ID(), 'beschikbare_plekken', true);
                        $inschrijven = get_post_meta(get_the_ID(), 'inschrijven', true);
                        ?>
                        <div class="cursus-toggle-item active">
                            <button class="cursus-toggle-button active">
                                <span class="toggle-startdatum">
                                    <?php
                                    if ($startdatum) {
                                        $datetime = new \DateTime($startdatum, wp_timezone());
                                        $datetime->modify('+1 day');
                                        echo esc_html(date_i18n('d-m-Y', $datetime->getTimestamp()));
                                    } else {
                                        echo __('Niet ingesteld', 'cursussen-plugin');
                                    }
                                    ?>
                                </span>
                                <span class="toggle-icon">-</span>
                            </button>
                            <div class="cursus-toggle-content">
                                <p>
                                    <strong><?php _e('Startdatum:', 'cursussen-plugin'); ?></strong>
                                    <?php
                                    if ($startdatum) {
                                        $datetime = new \DateTime($startdatum, wp_timezone());
                                        $datetime->modify('+1 day');
                                        echo esc_html(date_i18n('d-m-Y', $datetime->getTimestamp()));
                                    } else {
                                        echo __('Niet ingesteld', 'cursussen-plugin');
                                    }
                                    ?>
                                </p>
                                <p>
                                    <strong><?php _e('Opleidingstype:', 'cursussen-plugin'); ?></strong>
                                    <?php echo $opleidingstype ? esc_html(ucfirst($opleidingstype)) : __('Niet opgegeven', 'cursussen-plugin'); ?>
                                </p>
                                <p>
                                    <strong><?php _e('Tijd:', 'cursussen-plugin'); ?></strong>
                                    <?php echo ($starttijd && $eindtijd) ? esc_html($starttijd . ' - ' . $eindtijd) : __('Niet opgegeven', 'cursussen-plugin'); ?>
                                </p>
                                <p>
                                    <strong><?php _e('Bijeenkomsten:', 'cursussen-plugin'); ?></strong>
                                    <?php echo $bijeenkomsten ? esc_html($bijeenkomsten) : __('Niet opgegeven', 'cursussen-plugin'); ?>
                                </p>
                                <p>
                                    <strong><?php _e('Beschikbare plekken:', 'cursussen-plugin'); ?></strong>
                                    <?php
                                    error_log("Beschikbare plekken (mobiel): " . print_r($beschikbare_plekken, true));
                                    if ($inschrijven === 'Vol') {
                                        echo '<span class="custom-red">' . __('Geen plekken', 'cursussen-plugin') . '</span>';
                                    } else {
                                        echo $beschikbare_plekken ? esc_html($beschikbare_plekken) : __('Geen plekken', 'cursussen-plugin');
                                    }
                                    ?>
                                </p>
                                <p>
                                    <?php
                                    if ($inschrijven === 'Vol') {
                                        echo '<span class="custom-green disabled" data-inschrijven="Geen plekken">' . __('Vol', 'cursussen-plugin') . '</span>';
                                    } else {
                                        echo '<a href="https://test.sodriveacademie.nl/inschrijven/" class="custom-inschrijven-btn hidden-mobile" data-inschrijven="Inschrijven">' . __('Inschrijven', 'cursussen-plugin') . '</a>';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php
            wp_reset_postdata();
            return ob_get_clean();
        }
        return '';
    }
}