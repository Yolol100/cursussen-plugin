<?php
declare(strict_types=1);

namespace SodriveAcademie;

class CPT_Cursussen {
    public function __construct() {
        // Registreer acties en hooks
        add_action('init', [$this, 'register_custom_post_type']);
        add_action('init', [$this, 'register_custom_taxonomies']);
        add_filter('manage_cursussen_posts_columns', [$this, 'add_custom_columns']);
        add_action('manage_cursussen_posts_custom_column', [$this, 'populate_custom_columns'], 10, 2);
    }

    /**
     * Registreert het custom post type "Cursussen".
     */
    public function register_custom_post_type(): void {
        $labels = [
            'name'               => __('Cursussen', 'cursussen-plugin'),
            'singular_name'      => __('Cursus', 'cursussen-plugin'),
            'menu_name'          => __('Cursussen', 'cursussen-plugin'),
            'add_new'            => __('Nieuwe Cursus Toevoegen', 'cursussen-plugin'),
            'add_new_item'       => __('Nieuwe Cursus Toevoegen', 'cursussen-plugin'),
            'edit_item'          => __('Cursus Bewerken', 'cursussen-plugin'),
            'new_item'           => __('Nieuwe Cursus', 'cursussen-plugin'),
            'view_item'          => __('Cursus Bekijken', 'cursussen-plugin'),
            'search_items'       => __('Cursussen Zoeken', 'cursussen-plugin'),
            'not_found'          => __('Geen Cursussen gevonden', 'cursussen-plugin'),
            'not_found_in_trash' => __('Geen Cursussen gevonden in de prullenbak', 'cursussen-plugin'),
        ];

        $args = [
            'labels'       => $labels,
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => ['slug' => 'cursussen'],
            'supports'     => ['title', 'editor', 'excerpt', 'thumbnail'],
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-welcome-learn-more',
        ];

        register_post_type('cursussen', $args);
    }

    /**
     * Registreert een aangepaste taxonomie voor "Cursussen".
     */
    public function register_custom_taxonomies(): void {
        $labels = [
            'name'              => __('Cursus Categorieën', 'cursussen-plugin'),
            'singular_name'     => __('Cursus Categorie', 'cursussen-plugin'),
            'search_items'      => __('Zoek Categorieën', 'cursussen-plugin'),
            'all_items'         => __('Alle Categorieën', 'cursussen-plugin'),
            'edit_item'         => __('Bewerk Categorie', 'cursussen-plugin'),
            'update_item'       => __('Update Categorie', 'cursussen-plugin'),
            'add_new_item'      => __('Voeg Nieuwe Categorie Toe', 'cursussen-plugin'),
            'new_item_name'     => __('Nieuwe Categorie Naam', 'cursussen-plugin'),
            'menu_name'         => __('Categorieën', 'cursussen-plugin'),
        ];

        $args = [
            'labels'            => $labels,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'cursus-categorie'],
        ];

        register_taxonomy('cursus_categorie', ['cursussen'], $args);
    }

    /**
     * Voegt aangepaste kolommen toe aan de lijstweergave van Cursussen in de admin.
     *
     * @param array $columns De bestaande kolommen.
     * @return array De aangepaste kolommen.
     */
    public function add_custom_columns(array $columns): array {
        return [
            'cb'         => $columns['cb'] ?? '',
            'title'      => __('Cursus', 'cursussen-plugin'),
            'startdatum' => __('Startdatum', 'cursussen-plugin'),
            'categorie'  => __('Categorie', 'cursussen-plugin'),
            'date'       => $columns['date'] ?? '',
        ];
    }

    /**
     * Populeert de aangepaste kolommen met data.
     *
     * @param string $column  De kolomnaam.
     * @param int    $post_id De ID van de huidige post.
     */
    public function populate_custom_columns(string $column, int $post_id): void {
        switch ($column) {
            case 'startdatum':
                $startdatum = get_post_meta($post_id, 'startdatum', true);
                echo esc_html($startdatum ?: __('Niet ingesteld', 'cursussen-plugin'));
                break;
            case 'categorie':
                $terms = get_the_terms($post_id, 'cursus_categorie');
                if (!empty($terms) && !is_wp_error($terms)) {
                    $term_names = wp_list_pluck($terms, 'name');
                    echo esc_html(implode(', ', $term_names));
                } else {
                    _e('Geen categorie', 'cursussen-plugin');
                }
                break;
        }
    }
}