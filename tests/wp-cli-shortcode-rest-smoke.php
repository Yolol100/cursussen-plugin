<?php
/**
 * WordPress course shortcode/REST behavior regression.
 * Runs against synthetic content in a disposable CI installation only.
 */
$post_id = wp_insert_post([
    'post_type' => \SodriveAcademie\CPT_Cursussen::POST_TYPE,
    'post_status' => 'publish',
    'post_title' => 'CI Test Course',
], true);
if (is_wp_error($post_id) || !$post_id) {
    throw new RuntimeException('Could not create isolated course.');
}

try {
    update_post_meta($post_id, 'startdatum', '2027-02-10');
    update_post_meta($post_id, 'opleidingstype', 'klassikaal');
    update_post_meta($post_id, 'inschrijven', 'Inschrijven');
    update_post_meta($post_id, 'beschikbare_plekken', 4);
    $html = do_shortcode('[toon_cursussen][toon_cursussen layout="mobile"]');
    if (substr_count($html, 'CI Test Course') < 2) {
        throw new RuntimeException('Course is missing from multi-shortcode display.');
    }

    preg_match_all('/\bid="(sda-cursus-(?:panel|button)-[^"]+)"/', $html, $matches);
    if (count($matches[1]) !== 4 || count(array_unique($matches[1])) !== 4) {
        throw new RuntimeException('Multiple shortcodes share accordion button/panel IDs.');
    }
    foreach ($matches[1] as $id) {
        $attribute = strpos($id, 'sda-cursus-button-') === 0 ? 'aria-labelledby' : 'aria-controls';
        if (strpos($html, $attribute . '="' . $id . '"') === false) {
            throw new RuntimeException('Accordion ARIA reference missing for ' . $id);
        }
    }

    $route = rest_do_request(new WP_REST_Request('GET', '/cursussen/v1/all'));
    if ($route->get_status() !== 200) {
        throw new RuntimeException('Public course REST listing is unavailable.');
    }
    $found = false;
    foreach ((array) $route->get_data() as $row) {
        if ((int) ($row['id'] ?? 0) === (int) $post_id) {
            $found = true;
        }
    }
    if (!$found) {
        throw new RuntimeException('Published course missing from public REST listing.');
    }
    echo "Cursussen multi-instance accordion and REST: OK\n";
} finally {
    wp_delete_post($post_id, true);
}
