<?php
/**
 * Structured data template functions
 *
 */

/**
 * Gets the structured data for the posts/pages.
 *
 * @since 3.1.0
 * @see https://developers.google.com/search/docs/data-types/job-postings
 *
 * @param WP_Post|int|null $post
 * @return bool|array False if functionality is disabled; otherwise array of structured data.
 */
function tm_get_job_structured_data($post = null ) {
    $post = get_post( $post );

    if ( ! $post ) {
        return false;
    }

    $data               = [];
    $data['@context']   = 'https://schema.org/';
    $data['@type']      = 'JobPosting';
    $data['datePosted'] =  get_post_time( 'c', false, $post );

    $job_expires = get_post_meta( $post->ID, '_tm_page_settings', true );
    if ( ! empty( $job_expires ) && $job_expires['_tm_job_expires'] ) {
        $data['validThrough'] = date( 'c', strtotime( $job_expires['_tm_job_expires'] ) );
    }

    $data['title']       = wp_strip_all_tags( tm_get_the_title( $post ) );
    $data['description'] = tm_get_the_description( $post );

    $employment_types = tm_get_employment_types($post);
    if ( ! empty( $employment_types ) ) {
        $data['employmentType'] = $employment_types;
    }

    $data['hiringOrganization']          = [];
    $data['hiringOrganization']['@type'] = 'Organization';
    $data['hiringOrganization']['name']  = tm_get_the_company_name( $post );

    $company_website = tm_get_the_company_website( $post );
    if ( $company_website ) {
        $data['hiringOrganization']['sameAs'] = $company_website;
        $data['hiringOrganization']['url']    = $company_website;
    }

    $company_logo = tm_get_the_company_logo( $post );
    if ( $company_logo ) {
        $data['hiringOrganization']['logo'] = $company_logo;
    }

    $data['identifier']          = [];
    $data['identifier']['@type'] = 'PropertyValue';
    $data['identifier']['name']  = tm_get_the_company_name( $post );
    $data['identifier']['value'] = get_the_guid( $post );

    $data['jobLocation']            = [];
    $data['jobLocation']['@type']   = 'Place';
    $data['jobLocation']['address'] = tm_get_location_structured_data( $post );
    if ( empty( $data['jobLocation']['address'] ) ) {
        $data['jobLocation']['address'] = "";
    }

    $data['baseSalary'] = [];
    $data['baseSalary']['@type'] = 'MonetaryAmount';
    $data['baseSalary']['currency'] = tm_get_currency( $post);
    $data['baseSalary']['value']['@type'] = 'QuantitativeValue';
    $data['baseSalary']['value']['value'] = tm_get_salary($post);
    $data['baseSalary']['value']['unitText'] = tm_get_payroll($post);

    /**
     * Filter the structured data.
     *
     * @since 3.1.0
     *
     * @param bool|array $structured_data False if functionality is disabled; otherwise array of structured data.
     * @param WP_Post    $post
     */
    return apply_filters( 'tm_get_job_structured_data', $data, $post );
}

/**
 * Gets the currency value
 *
 * @param int|WP_Post $post (default: null).
 * @return string The currency.
 */
function tm_get_currency($post = null) {
    $post = get_post( $post );
    if ( ! $post ) {
        return null;
    }

    $currency = get_post_meta($post->ID, '_tm_page_settings', true);
    if($currency) {
        $currency = $currency['_tm_monetary_currency'];
    }

    return apply_filters('the_get_currency', $currency, $post);
}

/**
 * Gets the salary value
 *
 * @param int|WP_Post $post (default: null).
 * @return string The salary.
 */
function tm_get_salary($post = null) {
    $post = get_post( $post );
    if ( ! $post ) {
        return null;
    }

    $salary = get_post_meta($post->ID, '_tm_page_settings', true);
    if($salary) {
        $salary = $salary['_tm_monetary_salary'];
    }

    return apply_filters('the_get_salary', $salary, $post);
}

/**
 * Gets the payroll value
 *
 * @param int|WP_Post $post (default: null).
 * @return string The payroll.
 */
function tm_get_payroll($post = null) {
    $post = get_post( $post );
    if ( ! $post ) {
        return null;
    }

    $payroll = get_post_meta($post->ID, '_tm_page_settings', true);
    if($payroll) {
        $payroll = $payroll['_tm_monetary_payroll'];
    }

    return apply_filters('the_get_payroll', $payroll, $post);
}


/**
 * Gets the title for the post/page.
 *
 * @since 3.1.0
 * @param int|WP_Post $post (default: null).
 * @return string|bool|null
 */
function tm_get_the_title( $post = null ) {
    $post = get_post( $post );
    if ( ! $post ) {
        return null;
    }

    $title = wp_strip_all_tags( get_the_title( $post ) );

    /**
     * Filter for the title.
     *
     * @since 3.1.0
     * @param string      $title Title to be filtered.
     * @param int|WP_Post $post
     */
    return apply_filters( 'tm_the_title', $title, $post );
}

/**
 * Validates whether the passed variable is a non-empty string.
 *
 * @param mixed $variable The variable to validate.
 *
 * @return bool Whether or not the passed value is a non-empty string.
 */
function is_non_empty( $variable ) {
    return is_string( $variable ) && '' !== $variable;
}

/**
 * Check if the string contains the given value.
 *
 * @param string $needle   The sub-string to search for.
 * @param string $haystack The string to search.
 *
 * @return bool
 */
function contains( $needle, $haystack ) {
    return is_non_empty( $needle ) ? strpos( $haystack, $needle ) !== false : false;
}

/**
 * Strip all active shortcodes.
 *
 * @param string $content Content to remove shortcodes from.
 * @return string
 */
function tm_strip_shortcodes( $content ) {
    if ( ! contains( '[', $content ) ) {
        return $content;
    }

    return preg_replace( '~\[\/?.*?\]~s', '', $content );
}

/**
 * Get the post excerpt to use as a replacement. It will be auto-generated if it does not exist.
 *
 * @since 3.1.0
 * @param int|WP_Post $post (default: null).
 * @return string|bool|null
 */
function tm_get_the_description( $post = null ) {
    $post = get_post( $post );
    if ( ! $post ) {
        return null;
    }

    $keywords     = get_post_meta($post->ID, '_wtt_post_keyword', true);
    $post_content = do_shortcode( $post->post_content );
    $post_content = preg_replace( '/<!--[\s\S]*?-->/iu', '', $post_content );
    $post_content = wpautop( tm_strip_shortcodes( $post_content ) );
    $post_content = wp_kses( $post_content, [ 'p' => [] ] );

    // Remove empty paragraph tags.
    $post_content = preg_replace( '/<p[^>]*>[\s|&nbsp;]*<\/p>/', '', $post_content );

    // Find the paragraph with the focus keyword.
    if ( ! empty( $keywords ) ) {
        $regex = '/<p>(.*' . str_replace( [ ',', ' ', '/' ], [ '|', '.', '\/' ], $keywords ) . '.*)<\/p>/iu';
        preg_match_all( $regex, $post_content, $matches );
        if ( isset( $matches[1], $matches[1][0] ) ) {
            return $matches[1][0];
        }
    }

    // The First paragraph of the content.
    preg_match_all( '/<p>(.*)<\/p>/iu', $post_content, $matches );
    $description = isset( $matches[1], $matches[1][0] ) ? $matches[1][0] : $post_content;

    /**
     * Filter for the description.
     *
     * @since 3.1.0
     * @param string      $post_content the description to be filtered.
     * @param int|WP_Post $post
     */
    return apply_filters('tm_the_description', $description, $post);
}

/**
 * Get the employment types
 *
 * @since 3.1.0
 *
 * @param WP_Post|int|null $post
 * @return bool|array
 */
function tm_get_employment_types( $post = null ) {
    $employment_types = [];
    $employment_type = get_post_meta($post->ID, "_tm_page_settings", true);

    if ( ! empty( $employment_type ) ) {
        $employment_types[] = $employment_type['_tm_employment_type'];
    }

    /**
     * Filter the employment types.
     *
     * @since 3.1.0
     *
     * @param array            $employment_types Employment types.
     * @param WP_Post|int|null $post
     */
    return apply_filters( 'tm_get_employment_types', array_unique( $employment_types ), $post );
}

/**
 * Gets the job location data.
 *
 * @see https://schema.org/PostalAddress
 *
 * @param WP_Post $post
 * @return array|bool
 */
function tm_get_location_structured_data( $post ) {
    $post = get_post( $post );
    $wtt_social = 'wtt_social';
    $options = get_option($wtt_social);
    $post_data = get_post_meta( $post->ID,'_tm_page_settings', true );
    $value = [];

    if ( ! $post ) {
        return false;
    }

    $mapping                    = [];
    $mapping['streetAddress']   = 'street';
    $mapping['addressLocality'] = 'city';
    $mapping['addressRegion']   = 'state_short';
    $mapping['postalCode']      = 'postcode';
    $mapping['addressCountry']  = 'country_short';

    $address          = [];
    $address['@type'] = 'PostalAddress';
    foreach ( $mapping as $schema_key => $geolocation_key ) {
        if (!empty($options['knowledgegraph_location_'.$schema_key])) {
            $value = $options['knowledgegraph_location_'.$schema_key];
        }

        if(!empty($post_data['_tm_job_location_'.$schema_key])) {
            $value = $post_data['_tm_job_location_'.$schema_key];
        }
        if ( ! empty( $value ) ) {
            $address[ $schema_key ] = $value;
        }
    }

    // No address parts were found.
    if ( 1 === count( $address ) ) {
        $address = false;
    }

    /**
     * Gets the job location structured data.
     *
     * @since 3.1.0
     *
     * @param array|bool $address Array of address data.
     * @param WP_Post    $post
     */
    return apply_filters( 'tm_get_location_structured_data', $address, $post );
}