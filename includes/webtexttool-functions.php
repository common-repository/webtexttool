<?php
/**
 * Global Textmetrics functions.
 *
 */

/**
 * Displays or retrieves the current company name with optional content.
 *
 * @since 3.1.0
 *
 * @param int|WP_Post|null $post (default: null).
 * @return string|null
 */
function tm_the_company_name( $post = null ) {
    $company_name = tm_get_the_company_name( $post );

    if ( 0 === strlen( $company_name ) ) {
        return null;
    }

    $company_name = esc_attr( wp_strip_all_tags( $company_name ) );

    return $company_name;
}

/**
 * Gets the company name.
 *
 * @since 3.1.0
 * @param int $post (default: null).
 * @return string
 */
function tm_get_the_company_name( $post = null ) {
    $post = get_post( $post );
    $option_name = "wtt_social";
    $options = get_option($option_name);
    $post_data = get_post_meta($post->ID, '_tm_page_settings', true);
    $company_name = '';

    if ( ! $post ) {
        return '';
    }

    if (!empty($options['knowledgegraph_name'])) {
        $company_name = $options['knowledgegraph_name'];
    }

    if (!empty($post_data['_tm_company_name'])) {
        $company_name = $post_data['_tm_company_name'];
    }

    return apply_filters( 'tm_the_company_name', $company_name, $post );
}

/**
 * Gets the company website.
 *
 * @since 3.1.0
 * @param int $post (default: null).
 * @return null|string
 */
function tm_get_the_company_website( $post = null ) {
    $post = get_post( $post );
    $website = '';
    if ( ! $post ) {
        return '';
    }

    if ( ! empty( $post->_tm_page_settings ) ) {
        $website = $post->_tm_page_settings['_tm_company_website'];
    }

    if ( $website && ! strstr( $website, 'http:' ) && ! strstr( $website, 'https:' ) ) {
        $website = 'http://' . $website;
    }

    if (!$website) {
        $website = get_site_url();
    }

    return apply_filters( 'tm_the_company_website', $website, $post );
}

/**
 * Displays the company logo.
 *
 * @since 3.1.0
 * @param int|WP_Post $post (default: null).
 */
function tm_the_company_logo( $post = null ) {
    tm_get_the_company_logo( $post );
}

/**
 * Gets the company logo.
 *
 * @since 3.1.0
 * @param int|WP_Post $post (default: null).
 * @return string Image SRC.
 */
function tm_get_the_company_logo( $post = null ) {
    $post = get_post( $post );
    $option_name = "wtt_social";
    $options = get_option($option_name);
    $post_data = get_post_meta($post->ID, '_tm_page_settings', true);
    $company_logo = '';

    if (!empty($options['knowledgegraph_logo'])) {
        $company_logo = $options['knowledgegraph_logo'];
    }

    if (!empty($post_data['_tm_company_logo'])) {
        $company_logo = $post_data['_tm_company_logo'];
    }

    return apply_filters( 'tm_the_company_logo', $company_logo , $post );
}

/**
 * Escape JSON for use on HTML or attribute text nodes.
 *
 * @since 3.1.0
 *
 * @param string $json JSON to escape.
 * @param bool   $html True if escaping for HTML text node, false for attributes. Determines how quotes are handled.
 * @return string Escaped JSON.
 */
function tm_esc_json( $json, $html = false ) {
    return _wp_specialchars(
        $json,
        $html ? ENT_NOQUOTES : ENT_QUOTES, // Escape quotes in attribute nodes only.
        'UTF-8',                           // json_encode() outputs UTF-8 (really just ASCII), not the blog's charset.
        true                               // Double escape entities: `&amp;` -> `&amp;amp;`.
    );
}

/**
 * If wp seo is active, return true
 *
 * @return bool
 */
function is_wp_seo_active()
{
    if (defined('WPSEO_VERSION')) {
        return true;
    }
    return false;
}