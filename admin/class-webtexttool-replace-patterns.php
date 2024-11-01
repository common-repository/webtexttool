<?php

/**
 * Class WTT_Replace_Patterns
 *
 * Implements the functions to replace dynamic variables.
 */
class WTT_Replace_Patterns
{
    protected $defaults = array(
        'ID' => '',
        'post_author' => '',
        'post_date' => '',
        'post_content' => '',
        'post_title' => '',
        'post_excerpt' => '',
        'post_modified' => '',
        'term_id' => '',
        'name' => '',
        'taxonomy' => '',
        'term404' => '',
    );

    protected $queried_object;

    public function __construct()
    {
    }

    /**
     * Match the variables and return the string
     * @param  string $var    The variable to replace
     * @param  array  $object The object
     * @return string
     */
    public function replace($var, $object)
    {
        $var = wp_strip_all_tags($var);
        $object = (array)$object;
        $replacements = array();

        $this->queried_object = (object)wp_parse_args($object, $this->defaults);

        // Preg match all double curly braces (with spaces)
        if (preg_match_all("~\{\{\s*(.*?)\s*\}\}~", $var, $matches)) {
            $replacements = $this->setup_replacement_parts($matches);
        }

        //actually replace the string with replacement parts
        if ($replacements !== array() && is_array($replacements)) {
            $var = str_replace(array_keys($replacements), array_values($replacements), $var);
        }

        return $var;
    }

    /**
     * Setup the replacement parts for the variables
     *
     * @param   array   $matches    List of matched variables using the doubly curly braces regex
     * @return  array   The replaced variables
     */
    private function setup_replacement_parts($matches)
    {
        $replacements = array();
        foreach ($matches[1] as $k => $var) {

            if (method_exists($this, 'generate_' . $var)) {
                $method_name = 'generate_' . $var;
                $replacement = $this->$method_name();
            }

            if (isset($replacement)) {
                $var = '{{' . $var . '}}';
                $replacements[$var] = $replacement;
            }

            unset($replacement, $method_name);
        }

        return $replacements;
    }

    private function generate_sitetitle()
    {
        return get_bloginfo('name');
    }

    private function generate_tagline()
    {
        return wp_strip_all_tags(get_bloginfo('description'));
    }

    private function generate_sep()
    {
        // get default value (sc-dash)
        $sep_value = 'sc-dash';
        // get wtt_settings value and all global separators value
        $wtt_settings = get_option('wtt_settings');
        $sep_options = json_decode(WTT_SEPARATORS, true);
        $sep = isset($wtt_settings['separator']) ? $wtt_settings['separator'] : '';
        if (isset($sep_options[$sep])) {
            $sep_value = $sep_options[$wtt_settings['separator']];
        }

        return $sep_value;
    }

    private function generate_post_title()
    {
        $title = '';

        if (is_string($this->queried_object->post_title) && $this->queried_object->post_title !== '') {
            $title = stripslashes($this->queried_object->post_title);
        }

        return $title;
    }

    private function generate_page()
    {
        $wtt_paged = '';

        if (get_query_var('paged') >= '1') {
            $wtt_paged = get_query_var('paged');
        }

        return $wtt_paged;
    }

    public function truncateDesc($text, $min = 100, $max = 110) {
        if ($text <> '' && strlen($text) > $max) {
            if (function_exists('strip_tags')) {
                $text = strip_tags($text);
            }
            $text = str_replace(']]>', ']]&gt;', $text);
            $text = @preg_replace('/\[(.+?)\]/is', '', $text);
            $text = strip_tags($text);

            if ($max < strlen($text)) {
                while ($text[$max] != ' ' && $max > $min) {
                    $max--;
                }
            }
            $text = substr($text, 0, $max);
            return trim(stripcslashes($text));
        }

        return $text;
    }

    private function generate_post_excerpt()
    {
        $var = '';

        if (!empty($this->queried_object->ID) && !post_password_required($this->queried_object->ID)) {
            if($this->queried_object->post_excerpt !== '') {
                $var = $this->truncateDesc($this->queried_object->post_excerpt, 0, 158);
            }
            elseif ($this->queried_object->post_content !== '') {
                $var = $this->truncateDesc($this->queried_object->post_content, 0, 158);
            }
        }

        return $var;
    }

    private function generate_post_date()
    {
        $date = '';

        if ( $this->queried_object->post_date !== '' ) {
            $date = mysql2date( get_option( 'date_format' ), $this->queried_object->post_date, true );
        }
        else {
            if ( get_query_var( 'day' ) && get_query_var( 'day' ) !== '' ) {
                $date = get_the_date();
            }
            else {
                if ( single_month_title( ' ', false ) && single_month_title( ' ', false ) !== '' ) {
                    $date = single_month_title( ' ', false );
                }
                elseif ( get_query_var( 'year' ) !== '' ) {
                    $date = get_query_var( 'year' );
                }
            }
        }

        return $date;
    }

    private function generate_post_author()
    {
        $author = '';

        $user_id = !empty( $this->queried_object->post_author ) ? $this->queried_object->post_author : get_query_var( 'author' );
        $display_name = get_the_author_meta('display_name',$user_id);

        if ($display_name !== '') {
            $author = $display_name;
        }

        return $author;
    }

    private function generate_post_category()
    {
        $category = '';

        if ( is_category() || is_tag() || is_tax() ) {
            $term   = $GLOBALS['wp_query']->get_queried_object();
            $category = $term->name;
        } elseif ( ! empty($this->queried_object->ID) && ! empty( 'category' ) ) {
            $terms = get_the_terms( $this->queried_object->ID, 'category' );
            if ( is_array( $terms ) && $terms !== [] ) {
                foreach ( $terms as $term ) {
                    $category .= $term->name . ', ';
                }
                $category = rtrim( trim( $category ), ',' );
            }
        }

        unset( $post_category_array, $tags );
        return $category;
    }

    private function generate_post_tag()
    {
        $post_tag = '';
        if (is_single() && has_tag()) {
            $post_tag_array = get_the_terms(get_the_id(), 'post_tag');
            foreach($post_tag_array as $tags) {
                $post_tag .= $tags->name . ', ';
            }
            $post_tag = rtrim( trim( $post_tag ), ',' );
        }
        unset($post_tag_array, $tags);

        return $post_tag;
    }

    private function generate_category_title()
    {
        return single_cat_title('', false);
    }

    private function generate_category_description()
    {
        return wp_filter_nohtml_kses(category_description());
    }

    private function generate_tag_title()
    {
        return single_tag_title('', false);
    }

    private function generate_tag_description()
    {
        return wp_filter_nohtml_kses(tag_description());
    }

    private function generate_term_title()
    {
        $term_title = '';

        if (!empty($this->queried_object->taxonomy) && !empty($this->queried_object->name)) {
            $term_title = $this->queried_object->name;
        }

        return $term_title;
    }

    private function generate_term_description()
    {
        $term_desc = '';

        if (isset($this->queried_object->term_id) && !empty($this->queried_object->taxonomy)) {
            $term_desc = term_description($this->queried_object->term_id);
            if ($term_desc !== '') {
                $term_desc = wp_filter_nohtml_kses($term_desc);
            }
        }
//        return wp_filter_nohtml_kses(term_description());
        return $term_desc;
    }

    private function generate_searchphrase()
    {
        $search_query = '';
        if (get_search_query() != '') {
            $search_query = '"' . get_search_query() . '"';
        } else {
            $search_query = esc_attr('" "');
        }

        return $search_query;
    }

    private function generate_pt_plural()
    {
        return post_type_archive_title('', false);
    }

    private function generate_archive_date()
    {
        return get_the_archive_title();
    }

    private function generate_wc_single_cat()
    {
        $wc_single_cat = '';
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            if (is_product()) {
                $woo_single_cats = get_the_terms($this->queried_object->ID, 'product_cat');

                if ($woo_single_cats && !is_wp_error($woo_single_cats)) {

                    $woo_single_cat = array();

                    foreach ($woo_single_cats as $term) {
                        $woo_single_cat[] = $term->name;
                    }

                    $wc_single_cat = wp_filter_nohtml_kses(join(", ", $woo_single_cat));
                }
            }
        }

        return $wc_single_cat;
    }

    private function generate_wc_single_tag()
    {
        $wc_single_tag = '';
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            if (is_product()) {
                $woo_single_tags = get_the_terms($this->queried_object->ID, 'product_tag');

                if ($woo_single_tags && !is_wp_error($woo_single_tags)) {

                    $woo_single_tag = array();

                    foreach ($woo_single_tags as $term) {
                        $woo_single_tag[] = $term->name;
                    }

                    $wc_single_tag = wp_filter_nohtml_kses(join(", ", $woo_single_tag));
                }
            }
        }

        return $wc_single_tag;
    }

    private function generate_wc_single_short_desc()
    {
        $excerpt = '';

        if (!is_404() && $this->queried_object != '') {
            if (has_excerpt($this->queried_object->ID)) {
                $excerpt = get_the_excerpt();
            }
        }

        return $excerpt;
    }
}