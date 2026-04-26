<?php
declare(strict_types=1);

namespace SodriveAcademie;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use DateTime;
use WP_Query;

class Shortcodes {
    public function __construct() {
        add_shortcode( 'toon_cursussen', [ $this, 'toon_cursussen_shortcode' ] );
    }

    public function toon_cursussen_shortcode( $atts ): string {
        $atts = is_array( $atts ) ? $atts : [];
        $atts = shortcode_atts(
            [
                'categorie'       => '',
                'aantal'          => 50,
                'inschrijven_url' => home_url( '/inschrijven/' ),
                'layout'          => 'responsive',
            ],
            $atts,
            'toon_cursussen'
        );

        $layout = $this->normalize_layout( (string) $atts['layout'] );
        $limit  = (int) $atts['aantal'];

        if ( 0 === $limit ) {
            $limit = 50;
        }
        if ( -1 !== $limit ) {
            $limit = max( 1, min( 100, $limit ) );
        }

        $args = [
            'post_type'              => CPT_Cursussen::POST_TYPE,
            'post_status'            => 'publish',
            'posts_per_page'         => $limit,
            'orderby'                => 'meta_value',
            'meta_key'               => 'startdatum',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
        ];

        $categorie = sanitize_title( (string) $atts['categorie'] );
        if ( '' !== $categorie ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => CPT_Cursussen::TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => $categorie,
                ],
            ];
        }

        $query = new WP_Query( $args );
        if ( ! $query->have_posts() ) {
            return '';
        }

        $courses = $this->build_course_rows( $query );
        wp_reset_postdata();

        if ( empty( $courses ) ) {
            return '';
        }

        Assets_Manager::enqueue_shortcode_assets();

        $signup_url = esc_url( $atts['inschrijven_url'] );
        if ( '' === $signup_url ) {
            $signup_url = esc_url( home_url( '/inschrijven/' ) );
        }

        $wrapper_classes = 'sda-cursussen sda-cursussen--' . $layout;

        ob_start();
        ?>
        <div class="<?php echo esc_attr( $wrapper_classes ); ?>" data-sda-cursussen>
            <?php if ( 'mobile' !== $layout ) : ?>
                <?php $this->render_desktop_table( $courses, $signup_url ); ?>
            <?php endif; ?>
            <?php if ( 'desktop' !== $layout ) : ?>
                <?php $this->render_mobile_accordion( $courses, $signup_url ); ?>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private function normalize_layout( string $layout ): string {
        $layout = sanitize_key( $layout );
        return in_array( $layout, [ 'responsive', 'desktop', 'mobile' ], true ) ? $layout : 'responsive';
    }

    private function build_course_rows( WP_Query $query ): array {
        $courses = [];

        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();

            $status  = (string) get_post_meta( $post_id, 'inschrijven', true );
            $places  = absint( get_post_meta( $post_id, 'beschikbare_plekken', true ) );
            $is_full = 'Vol' === $status;

            $courses[] = [
                'id'             => $post_id,
                'title'          => get_the_title( $post_id ),
                'startdatum'     => $this->format_date( (string) get_post_meta( $post_id, 'startdatum', true ) ),
                'opleidingstype' => $this->format_choice( (string) get_post_meta( $post_id, 'opleidingstype', true ) ),
                'starttijd'      => $this->sanitize_time_for_display( (string) get_post_meta( $post_id, 'starttijd', true ) ),
                'eindtijd'       => $this->sanitize_time_for_display( (string) get_post_meta( $post_id, 'eindtijd', true ) ),
                'bijeenkomsten'  => sanitize_text_field( (string) get_post_meta( $post_id, 'bijeenkomsten', true ) ),
                'places'         => $places,
                'is_full'        => $is_full,
            ];
        }

        return $courses;
    }

    private function render_desktop_table( array $courses, string $signup_url ): void {
        ?>
        <div class="sda-cursussen__desktop">
            <table class="sda-cursussen__table">
                <caption class="screen-reader-text"><?php echo esc_html__( 'Overzicht van beschikbare cursussen', 'cursussen-plugin' ); ?></caption>
                <thead>
                    <tr>
                        <th scope="col"><?php echo esc_html__( 'Startdatum', 'cursussen-plugin' ); ?></th>
                        <th scope="col"><?php echo esc_html__( 'Opleiding', 'cursussen-plugin' ); ?></th>
                        <th scope="col"><?php echo esc_html__( 'Tijd', 'cursussen-plugin' ); ?></th>
                        <th scope="col"><?php echo esc_html__( 'Bijeenkomsten', 'cursussen-plugin' ); ?></th>
                        <th scope="col"><?php echo esc_html__( 'Beschikbare plekken', 'cursussen-plugin' ); ?></th>
                        <th scope="col"><?php echo esc_html__( 'Actie', 'cursussen-plugin' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $courses as $course ) : ?>
                        <tr>
                            <td><?php echo esc_html( $course['startdatum'] ); ?></td>
                            <td><?php echo esc_html( $course['opleidingstype'] ); ?></td>
                            <td><?php echo esc_html( $this->format_time_range( $course['starttijd'], $course['eindtijd'] ) ); ?></td>
                            <td><?php echo esc_html( $course['bijeenkomsten'] ?: esc_html__( 'Niet opgegeven', 'cursussen-plugin' ) ); ?></td>
                            <td><?php $this->render_places( $course ); ?></td>
                            <td><?php $this->render_action( $course, $signup_url ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function render_mobile_accordion( array $courses, string $signup_url ): void {
        ?>
        <div class="sda-cursussen__mobile" data-sda-cursussen-accordion>
            <?php foreach ( $courses as $index => $course ) :
                $panel_id  = 'sda-cursus-panel-' . absint( $course['id'] ) . '-' . absint( $index );
                $button_id = 'sda-cursus-button-' . absint( $course['id'] ) . '-' . absint( $index );
                ?>
                <div class="sda-cursussen__item is-open">
                    <button class="sda-cursussen__toggle" type="button" id="<?php echo esc_attr( $button_id ); ?>" aria-expanded="true" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
                        <span><?php echo esc_html( $course['startdatum'] ); ?></span>
                        <span class="sda-cursussen__toggle-icon" aria-hidden="true">−</span>
                    </button>
                    <div class="sda-cursussen__panel" id="<?php echo esc_attr( $panel_id ); ?>" role="region" aria-labelledby="<?php echo esc_attr( $button_id ); ?>">
                        <p><strong><?php echo esc_html__( 'Startdatum:', 'cursussen-plugin' ); ?></strong> <?php echo esc_html( $course['startdatum'] ); ?></p>
                        <p><strong><?php echo esc_html__( 'Opleidingstype:', 'cursussen-plugin' ); ?></strong> <?php echo esc_html( $course['opleidingstype'] ); ?></p>
                        <p><strong><?php echo esc_html__( 'Tijd:', 'cursussen-plugin' ); ?></strong> <?php echo esc_html( $this->format_time_range( $course['starttijd'], $course['eindtijd'] ) ); ?></p>
                        <p><strong><?php echo esc_html__( 'Bijeenkomsten:', 'cursussen-plugin' ); ?></strong> <?php echo esc_html( $course['bijeenkomsten'] ?: esc_html__( 'Niet opgegeven', 'cursussen-plugin' ) ); ?></p>
                        <p><strong><?php echo esc_html__( 'Beschikbare plekken:', 'cursussen-plugin' ); ?></strong> <?php $this->render_places( $course ); ?></p>
                        <p><?php $this->render_action( $course, $signup_url ); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function render_places( array $course ): void {
        if ( $course['is_full'] ) {
            echo '<span class="sda-cursussen__status sda-cursussen__status--full">' . esc_html__( 'Geen plekken', 'cursussen-plugin' ) . '</span>';
            return;
        }

        if ( 0 === (int) $course['places'] ) {
            echo '<span class="sda-cursussen__status sda-cursussen__status--unknown">' . esc_html__( 'Niet opgegeven', 'cursussen-plugin' ) . '</span>';
            return;
        }

        echo esc_html( (string) $course['places'] );
    }

    private function render_action( array $course, string $signup_url ): void {
        if ( $course['is_full'] ) {
            echo '<span class="sda-cursussen__status sda-cursussen__status--full">' . esc_html__( 'Vol', 'cursussen-plugin' ) . '</span>';
            return;
        }

        $url = add_query_arg( 'cursus_id', absint( $course['id'] ), $signup_url );
        printf(
            '<a href="%1$s" class="sda-cursussen__button">%2$s<span class="screen-reader-text"> %3$s</span></a>',
            esc_url( $url ),
            esc_html__( 'Inschrijven', 'cursussen-plugin' ),
            esc_html( $course['title'] )
        );
    }

    private function format_date( string $date ): string {
        if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches ) || ! checkdate( (int) $matches[2], (int) $matches[3], (int) $matches[1] ) ) {
            return esc_html__( 'Niet ingesteld', 'cursussen-plugin' );
        }

        try {
            $datetime = new DateTime( $date, wp_timezone() );
            return date_i18n( 'd-m-Y', $datetime->getTimestamp() );
        } catch ( \Exception $e ) {
            return esc_html__( 'Niet ingesteld', 'cursussen-plugin' );
        }
    }

    private function sanitize_time_for_display( string $time ): string {
        return preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time ) ? $time : '';
    }

    private function format_choice( string $value ): string {
        if ( 'online' === $value ) {
            return esc_html__( 'Online', 'cursussen-plugin' );
        }

        if ( 'klassikaal' === $value ) {
            return esc_html__( 'Klassikaal', 'cursussen-plugin' );
        }

        return esc_html__( 'Niet opgegeven', 'cursussen-plugin' );
    }

    private function format_time_range( string $start, string $end ): string {
        if ( '' !== $start && '' !== $end ) {
            return $start . ' - ' . $end;
        }

        return esc_html__( 'Niet opgegeven', 'cursussen-plugin' );
    }
}
