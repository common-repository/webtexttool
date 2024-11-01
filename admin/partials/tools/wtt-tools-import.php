<?php

global $_wtt_seo_plugins;

$_wtt_seo_plugins = array(
    'Yoast SEO' => array(
        'SEO Title' => '_yoast_wpseo_title',
        'Meta Description' => '_yoast_wpseo_metadesc',
        'Meta Keywords' => '_yoast_wpseo_focuskw',
        'Canonical URI' => '_yoast_wpseo_canonical',
        'Social Media Title' => '_yoast_wpseo_opengraph-title',
        'Social Media Description' => '_yoast_wpseo_opengraph-description',
        'Social Media Image' => '_yoast_wpseo_opengraph-image',
    ),
    'All in One SEO Pack' => array(
        'SEO Title' => '_aioseop_title',
        'Meta Description' => '_aioseop_description',
        'Meta Keywords' => '_aioseop_keywords',
        'Canonical URI' => '_aioseop_custom_link',
    ),
    'SEOPress' => array(
        'SEO Title' => '_seopress_titles_title',
        'Meta Description' => '_seopress_titles_desc',
        'Meta Keywords' => '_seopress_analysis_target_kw',
        'Canonical URI' => '_seopress_robots_canonical',
        'Social Media Title' => '_seopress_social_fb_title',
        'Social Media Description' => '_seopress_social_fb_desc',
        'Social Media Image' => '_seopress_social_fb_img',
    ),
    'The SEO Framework' => array(
        'SEO Title' => '_genesis_title',
        'Meta Description' => '_genesis_description',
        'Meta Keywords' => '_genesis_keywords',
        'Canonical URI' => '_genesis_canonical_uri',
        'Social Media Title' => '_open_graph_title',
        'Social Media Description' => '_open_graph_description',
        'Social Media Image' => '_social_image_url',
    ),
    'Rank Math SEO' => array(
        'SEO Title' => 'rank_math_title',
        'Meta Description' => 'rank_math_description',
        'Meta Keywords' => 'rank_math_focus_keyword',
        'Canonical URI' => 'rank_math_canonical_url',
        'Social Media Title' => 'rank_math_facebook_title',
        'Social Media Description' => 'rank_math_facebook_description',
        'Social Media Image' => 'rank_math_facebook_image',
    ),
    'Webtexttool' => array(
        'SEO Title' => '_wtt_post_title',
        'Meta Description' => '_wtt_post_description',
        'Meta Keywords' => '_wtt_post_keyword',
        'Canonical URI' => '_wtt_canonical-link',
        'Social Media Title' => '_wtt_opengraph-title',
        'Social Media Description' => '_wtt_opengraph-description',
        'Social Media Image' => '_wtt_opengraph-image',
    )
);

/**
 * Performs the import action, renders the results and errors.
 *
 */
function wtt_import_action()
{
    if (empty($_REQUEST['_wpnonce']))
        return;

    if (empty($_REQUEST['platform_old'])) {
        printf('<div class="notice notice-error wtt-plugin-notice"><p>%s</p></div>', _('Sorry, you can\'t do that. Please choose a platform and then click Analyze or Convert.'));
        return;
    }

    if ($_REQUEST['platform_old'] === 'Webtexttool') {
        printf('<div class="notice notice-error wtt-plugin-notice"><p>%s</p></div>', __('Sorry, you can\'t do that. Please choose a different platform and then click Analyze or Convert.'));
        return;
    }

    check_admin_referer('webtexttool');

    if (!empty($_REQUEST['analyze'])) {

        printf('<h3>%s</h3>', _('Analysis Results'));

        $response = wtt_seo_data_analyze($_REQUEST['platform_old'], 'Webtexttool');
        if (is_wp_error($response)) {
            printf('<div class="notice notice-error wtt-plugin-notice"><p>%s</p></div>', _('Sorry, something went wrong. Please try again'));
            return;
        }

        printf('<p><strong>Analyzing records for %s:</strong>', esc_html($_POST['platform_old']));
        printf('<p><strong>%d</strong> Compatible records were found.</p>', $response->update);

        if ($response->ignore > 0) {
            printf('<p><strong>%d</strong> Textmetrics records will be ignored.</p>', $response->ignore);
        }

        printf('<p><b>%s</b></p>', 'Compatible elements:');

        echo '<ol>';
        foreach ((array)$response->elements as $element) {
            printf('<li>%s</li>', $element);
        }
        echo '</ol>';

        return;
    }

    printf('<h3>%s</h3>', 'Conversion Results');

    $result = wtt_seo_data_convert(stripslashes($_REQUEST['platform_old']), 'Webtexttool', isset($_POST['delete_old_data']));

    if (is_wp_error($result)) {
        printf('<div class="notice notice-error wtt-plugin-notice"><p>%s</p></div>', _('Sorry, something went wrong. Please try again'));
        return;
    }

    if ($result) {
        $message = stripslashes($_REQUEST['platform_old']) . ' data successfully imported.';

        if (isset($_POST['delete_old_data'])) {
            $message .= ' ' . 'The old data was successfully deleted.';
        }

        printf('<div class="notice notice-success wtt-plugin-notice"><p>%s</p></div>', $message);

        printf('<p><b>%d</b> Compatible records were updated</p>', isset($result->updated) ? $result->updated : 0);
        printf('<p><b>%d</b> Textmetrics records were ignored</p>', isset($result->ignored) ? $result->ignored : 0);
    }

    return;
}

/**
 * Analyzes two plugins to find the compatible elements.
 *
 * @param string $old_platform The old seo plugin.
 * @param string $new_platform The new seo plugin.
 *
 * @return stdClass Object for error detection, and the number of affected rows.
 */
function wtt_seo_data_analyze($old_platform = '', $new_platform = 'Webtexttool')
{
    global $wpdb, $_wtt_seo_plugins;

    $output = new stdClass;

    if (empty($_wtt_seo_plugins[$old_platform])) {
        $output->WP_Error = 1;
        return $output;
    }

    $output->update = 0;
    $output->ignore = 0;
    $output->elements = '';

    // Calculates number of tables that will be updated after conversion
    foreach ((array)$_wtt_seo_plugins[$old_platform] as $label => $meta_key) {
        if (empty($_wtt_seo_plugins[$new_platform][$label]))
            continue;

        $elements[] = $label;

        $update = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", $meta_key));
        $update = count((array)$update);
        $output->update = $output->update + (int)$update;
    }

    // Calculates number of tables that will be ignored after conversion
    foreach ((array)$_wtt_seo_plugins[$new_platform] as $new_label => $new_meta_key) {
        $ignore = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", $new_meta_key));
        $ignore = count((array)$ignore);
        $output->ignore = $output->ignore + (int)$ignore;
    }

    $output->elements = $elements;

    do_action('wtt_seo_data_analyze', $output, $old_platform, $new_platform);

    return $output;
}

/**
 * Cycles through all compatible SEO entries of two platforms and calls wtt_meta_key_convert conversion for each key.
 *
 * @param string $old_platform The old seo plugin.
 * @param string $new_platform The new seo plugin.
 *
 * @param bool $delete_old Whether to delete the old entries.
 * @return stdClass Object for error detection, and the number of affected rows.
 */
function wtt_seo_data_convert($old_platform = '', $new_platform = 'Webtexttool', $delete_old = false)
{
    global $_wtt_seo_plugins;

    $output = new stdClass;

    if (empty($_wtt_seo_plugins[$old_platform])) {
        $output->WP_Error = 1;
        return $output;
    }

    $output->updated = 0;
    $output->ignored = 0;

    foreach ((array)$_wtt_seo_plugins[$old_platform] as $label => $meta_key) {

        // skip iterations where no $new analog exists
        if (empty($_wtt_seo_plugins[$new_platform][$label]))
            continue;

        // set $old and $new meta_key values
        $old = $_wtt_seo_plugins[$old_platform][$label];
        $new = $_wtt_seo_plugins[$new_platform][$label];

        // convert
        $result = wtt_meta_key_convert($old, $new, $delete_old);

        // error check
        if (is_wp_error($result))
            continue;

        $output->updated = $output->updated + (int)$result->updated;
        $output->ignored = $output->ignored + (int)$result->ignored;

    }

    do_action('wtt_seo_data_convert', $output, $old_platform, $new_platform, $delete_old);

    return $output;

}

/**
 * Calculates amount of entries that will be excluded in the conversion.
 * Preparares a wpdb query and gets the results based on the query.
 *
 * @param string $old Old meta_key entries.
 * @param string $new New meta_key entries.
 *
 * @param bool $delete_old Whether to delete the old entries.
 * @return stdClass Object for error detection, and the number of affected rows.
 */
function wtt_meta_key_convert($old = '', $new = '', $delete_old = false)
{
    global $wpdb, $_wtt_seo_plugins;

    $output = new stdClass;

    if (!$old || !$new) {
        $output->WP_Error = 1;
        return $output;
    }

    $exclude = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", $new));

    $query = $wpdb->get_results($wpdb->prepare(
        "SELECT `a`.*
				FROM {$wpdb->postmeta} AS a
				WHERE `a`.`meta_key` = %s
					AND NOT	EXISTS (
						SELECT DISTINCT `post_id` , count( `meta_id` ) AS count
						FROM {$wpdb->postmeta} AS b
						WHERE `a`.`post_id` = `b`.`post_id` AND `meta_key` LIKE %s GROUP BY `post_id`
					);", $old, $new));

    $output->updated = count($query);
    $output->ignored = count($exclude);

    if (is_array($query) && $query !== array()) {
        foreach ($query as $old_data) {

            //ignore (meta_value) fields with patterns (i.e. %%title%%)
            if (preg_match_all("~\%\%\s*(.*?)\s*\%\%~", $old_data->meta_value, $matches)) {
                continue;
            }

            if ($old_data->meta_key === $_wtt_seo_plugins['All in One SEO Pack']['Meta Keywords'] || $old_data->meta_key === $_wtt_seo_plugins['Rank Math SEO']['Meta Keywords']) {
                $newValue = explode(',', $old_data->meta_value);
                $old_data->meta_value = reset($newValue);
            }

            update_post_meta($old_data->post_id, $new, $old_data->meta_value);
        }
    }

    if ($delete_old === true) {
        delete_post_meta_by_key($old);
    }

    do_action('wtt_meta_key_convert', $output, $old, $new, $delete_old);

    return $output;
}
