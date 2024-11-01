<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Webtexttool_Social
{

    /**
     * The ID of this plugin.
     *
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private $title = null;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Set the start string
     *
     * @return string
     */
    public function set_start()
    {
        return "\n<!-- Textmetrics SEO Plugin " . WTT_VERSION . " - https://www.textmetrics.com/ -->\n";
    }

    /**
     * Set the end string
     *
     * @return string
     */
    public function set_end()
    {
        return "\n<!-- /Textmetrics SEO Plugin -->\n";
    }

    /**
     * Insert the social meta tags in the header
     */
    public function set_header_meta()
    {
        $data = "";
        $wtt_social = get_option("wtt_social");
        $data .= $this->set_start();
        global $post;

        if (isset($wtt_social['canonical_url']) && $wtt_social['canonical_url'] == "on") {
            remove_action('wp_head', 'rel_canonical');
            $data .= $this->get_canonical_url();
        }

        if (isset($wtt_social['show_meta_desc']) && $wtt_social['show_meta_desc'] == "on" && $this->get_other_plugin_description()) {
            $data .= $this->get_meta_description();
        }

        if (isset($wtt_social['socialmetabox']) && $wtt_social['socialmetabox'] == "on") {
            if (isset($wtt_social['opengraph']) && $wtt_social['opengraph'] == "on") {
                $data .= $this->get_opengraph_meta();
            }

            if (isset($wtt_social['twitter']) && $wtt_social['twitter'] == "on") {
                $data .= $this->get_twittercard_meta();
            }

            isset($post) ? $value = get_post_meta($post->ID, '_tm_page_settings', true) : [];
            if(!empty($value['_tm_schema_type']) && $value['_tm_schema_type'] == "job_posting") {
                $data .= $this->output_structured_data();
            }
        }

        $data .= $this->set_end();
        echo $data;
    }

    /**
     * Default get_post_meta function
     *
     * @param $meta
     * @param int $post_id
     * @return bool|string
     */
    private function getPostMeta($meta, $post_id = 0)
    {
        global $post;
        if(!isset($post))
            return false;

        $post_meta = htmlspecialchars(get_post_meta($post_id ? $post_id : $post->ID, $meta, true), ENT_QUOTES);

        if (!empty($post_meta)) {
            return $post_meta;
        }

        return "";
    }

    /**
     * Add structured data to the header of posts/pages.
     */
    public function output_structured_data() {
        $structured_data = tm_get_job_structured_data();
        if ( ! empty( $structured_data ) ) {
            return sprintf('<script type="application/ld+json">%s</script>', tm_esc_json( wp_json_encode( $structured_data ), true ) . "\n");
        }
    }

    /**
     * Set the SEO title by add_filter (called in Webtexttool::define_social_hooks)
     *
     * @param $title
     * @param string $separator
     * @param string $sep_location
     * @return null|string|Webtexttool_Social
     */
    public function wtt_set_seo_title($title, $separator = '', $sep_location = '')
    {
        if (is_null($this->title)) {
            $this->title = $this->wtt_generate_seo_title($title, $sep_location);
        }

        return $this->title;
    }

    /**
     * Get wtt_settings option variable
     *
     * @param string $index The option variable to get the settings from
     * @param object|array $object The object variable
     *
     * @return string
     */
    private function get_wtt_title_from_options($index, $object = array())
    {
        $wtt_settings = get_option("wtt_settings");

        if (!isset($wtt_settings[$index]) || $wtt_settings[$index] === '') {
            if (is_singular()) {
                return $this->replacePatterns('{{post_title}} {{sep}} {{sitetitle}}', $object);
            }

            return '';
        }

        return $this->replacePatterns($wtt_settings[$index], $object);
    }

    /**
     * Generates the SEO title based on input
     *
     * @param $title
     * @param $sep_location
     * @return string|Webtexttool_Social
     */
    private function wtt_generate_seo_title($title, $sep_location)
    {
        if (is_feed()) {
            return $title;
        }

        $separator = $this->replacePatterns('{{sep}}', array());
        $separator = ' ' . trim($separator) . ' ';

        if (trim($sep_location) === '') {
            $sep_location = (is_rtl()) ? 'left' : 'right';
        }

        $original_title = $title;

        $modified_title = true;

        $title_part = '';

        if (is_front_page() && get_option('show_on_front') === 'page' && is_page(get_option('page_on_front'))) { //STATIC HOMEPAGE (frontend)
            $title = $this->get_wtt_static_page_title();
        } elseif (is_home() && get_option('show_on_front') === 'posts') {              //LATEST POSTS (default)
            $title = $this->get_wtt_title_from_options('title-home-wtt');
        } elseif ($this->is_single_page()) {                                            //STATIC POST PAGE (frontend)
            $post = get_post($this->get_single_page_id());
            $title = $this->get_wtt_static_page_title($post);

            if (!is_string($title) || $title === '') {
                $title_part = $original_title;
            }
        } elseif (is_search()) {                                                        // Search page
            $title = $this->get_wtt_title_from_options('title-search-wtt');

            if (!is_string($title) || $title === '') {
                $title_part = $original_title;
            }
        } elseif (is_tax() || is_category() || is_tag()) {                            // Taxonomy title
            $title = $this->get_wtt_taxonomy_title();

            if (!is_string($title) || $title === '') {
                if (is_category()) {
                    $title_part = single_cat_title('', false);
                } elseif (is_tag()) {
                    $title_part = single_tag_title('', false);
                } else {
                    $title_part = single_term_title('', false);
                    if ($title_part === '') {
                        $term = get_queried_object() === null ? get_queried_object() : '';
                        $title_part = $term->name;
                    }
                }
            }
        } elseif (is_author()) {
            $title = $this->get_wtt_author_title();

            if (!is_string($title) || $title === '') {
                $title_part = get_the_author_meta('display_name', get_query_var('author'));
            }
        } elseif ($this->is_woocommerce_page()) { // WooCommerce Shop page
            $post = get_post(function_exists('wc_get_page_id') ? wc_get_page_id('shop') : (-1));
            $title = $this->get_wtt_title($post);

            if (!is_string($title) || $title === '') {
                $title = $this->get_wtt_post_type_archive_title($separator, $sep_location);
            }
        } elseif (is_post_type_archive()) {
            $title = $this->get_wtt_post_type_archive_title($separator, $sep_location);
        } elseif (is_archive()) { // if any type of Archive page is being displayed
            $title = $this->get_wtt_title_from_options('title-archive-wtt');

            if (empty($title)) {
                $title_part = __('Archives', 'webtexttool');
            }
        } elseif (is_404()) {
            $title = $this->get_wtt_title_from_options('title-404-wtt');

            if (empty($title)) {
                $title_part = __('Page not found', 'webtexttool');
            }
        } else {
            $title_part = $title;
        }

        if (!empty($title_part) || (empty($title) && $modified_title)) {
            $title = $this->get_default_page_title($separator, $sep_location, $title_part);
        }

        return esc_html(wp_strip_all_tags(stripslashes($title), true));
    }

    private function is_single_page()
    {
        return $this->get_single_page_id() > 0;
    }

    private function get_single_page_id()
    {
        if (is_singular()) {
            return get_the_ID();
        }

        if (is_home() && get_option('show_on_front') === 'page') {
            return get_option('page_for_posts');
        }

        return 0;
    }

    private function get_wtt_author_title()
    {
        $author_id = get_query_var('author');
        $title = trim(get_the_author_meta('wtt_title', $author_id));

        if ($title !== '') {
            return $this->replacePatterns($title, array());
        }

        return $this->get_wtt_title_from_options('title-author-wtt');
    }

    private function get_wtt_taxonomy_title()
    {
        $queried_object = $GLOBALS['wp_query']->get_queried_object();

        $title = $this->get_wtt_tax_term_meta($queried_object, $queried_object->taxonomy, '_wtt_term_title');

        if (is_string($title) && $title !== '') {
            return $this->replacePatterns($title, $queried_object);
        }

        return $this->get_wtt_title_from_options('title-tax-' . $queried_object->taxonomy, $queried_object);
    }

    private function get_wtt_meta_without_term($meta) {
        $term = $GLOBALS['wp_query']->get_queried_object();

        return self::get_wtt_tax_term_meta( $term, $term->taxonomy, $meta );
    }

    private function get_wtt_tax_term_meta($term, $tax, $meta = null)
    {
        if (is_int($term)) {
            $term = get_term_by('id', $term, $tax);
        } elseif (is_string($term)) {
            $term = get_term_by('slug', $term, $tax);
        }

        if (isset($term->term_id) && is_object($term)) {
            $term_id = $term->term_id;
        } else {
            return false;
        }

        $wtt_tax_meta = $this->get_tax_meta($term_id, $meta);

        return $wtt_tax_meta;
    }

    private function get_tax_meta($term_id, $meta)
    {
        return get_term_meta($term_id, $meta, true);
    }

    private function get_wtt_post_type_archive_title($separator, $sep_location)
    {
        $post_type = get_query_var('post_type');

        $title = $this->get_wtt_title_from_options('title-ptarchive-' . $post_type);

        if (!is_string($title) || $title === '') {
            $post_type_obj = get_post_type_object($post_type);
            $title_part = '';

            if (isset($post_type_obj->labels->menu_name)) {
                $title_part = $post_type_obj->labels->menu_name;
            } elseif (isset($post_type_obj->name)) {
                $title_part = $post_type_obj->name;
            }

            $title = $this->get_default_page_title($separator, $sep_location, $title_part);
        }

        return $title;
    }

    private function get_default_page_title($sep, $sep_location, $title = '')
    {
        if ($sep_location === 'right') {
            $regex = '`\s*' . preg_quote(trim($sep), '`') . '\s*`u';
        } else {
            $regex = '`^\s*' . preg_quote(trim($sep), '`') . '\s*`u';
        }
        $title = preg_replace($regex, '', $title);

        if (!is_string($title) || (is_string($title) && $title === '')) {
            $title = wp_strip_all_tags(get_bloginfo('name'), true);
            $title = $this->add_page_number_to_title($sep, $sep_location, $title);
            $title = $this->add_title_part_to_title($sep, $sep_location, $title, wp_strip_all_tags(get_bloginfo('description'), true));

            return $title;
        }

        $title = $this->add_page_number_to_title($sep, $sep_location, $title);
        $title = $this->add_title_part_to_title($sep, $sep_location, $title, wp_strip_all_tags(get_bloginfo('name'), true));

        return $title;
    }

    private function add_page_number_to_title($sep, $sep_location, $title)
    {
        global $wp_query;

        if (!empty($wp_query->query_vars['paged']) && $wp_query->query_vars['paged'] > 1) {
            return $this->add_title_part_to_title($sep, $sep_location, $title, $wp_query->query_vars['paged'] . '/' . $wp_query->max_num_pages);
        }

        return $title;
    }

    private function add_title_part_to_title($sep, $sep_location, $title, $title_part)
    {
        if ($sep_location === 'right') {
            return $title . $sep . $title_part;
        }

        return $title_part . $sep . $title;
    }

    /**
     * Generate the title for static home page type
     *
     * @param null $queried_object
     * @return mixed
     */
    private function get_wtt_static_page_title($queried_object = null)
    {
        if ($queried_object === null) {
            $queried_object = $GLOBALS['wp_query']->get_queried_object();
        }

        $title = $this->get_wtt_title($queried_object);

        if ($title !== '') {
            return $title;
        };

        $post_type = (isset ($queried_object->post_type) ? $queried_object->post_type : $queried_object->query_var);

        return $this->get_wtt_title_from_options('title-' . $post_type, $queried_object);
    }

    /**
     * Test current if the current page is a shop page
     *
     * @return bool
     *
     */
    public function is_woocommerce_page() {
        if ( function_exists( 'is_shop' ) && function_exists( 'wc_get_page_id' ) ) {
            return is_shop() && ! is_search();
        }

        return false;
    }

    /**
     * Get the title from the custom wtt post meta
     *
     * @param null $queried_object
     * @return bool|string
     */
    private function get_wtt_title($queried_object = null)
    {
        if ($queried_object === null) {
            $queried_object = $GLOBALS['wp_query']->get_queried_object();
        }

        if (!is_object($queried_object)) {
            return $this->get_wtt_title_from_options('title-404-wtt');
        }

        $title = $this->getPostMeta('_wtt_post_title', $queried_object->ID);

        if ($title !== '') {
            return $this->replacePatterns($title, $queried_object);
        }

        return $title;
    }

    /**
     * Replace the dynamic patterns in a string
     *
     * @param string $string String to compare and replace
     * @param array $object Queried object or post type
     * @return string           The replaced string
     */
    private function replacePatterns($string, $object)
    {
        $replacement = new WTT_Replace_Patterns();

        return $replacement->replace($string, $object);
    }

    /**
     * Get the Open Graph Meta
     */
    private function get_opengraph_meta()
    {
        global $post;

        $meta = "";
        $wtt_social = get_option("wtt_social");

        $openGraphTitle = $this->generate_og_title();
        $openGraphImage = $this->get_custom_image();
        $ogDescription = $this->get_og_description();

        $meta .= sprintf('<meta property="og:url" content="%s" />', $this->get_og_url()) . "\n";

        $meta .= (($openGraphImage <> '') ? sprintf('<meta property="og:image" content="%s" />', $openGraphImage) . "\n" : '');
        $meta .= (($openGraphTitle <> '') ? sprintf('<meta property="og:title" content="%s" />', $openGraphTitle) . "\n" : '');
        $meta .= (($ogDescription <> '') ? sprintf('<meta property="og:description" content="%s" />', $ogDescription) . "\n" : '');

        $meta .= ((get_bloginfo('name') <> '') ? sprintf('<meta property="og:site_name" content="%s" />', get_bloginfo('name')) . "\n" : '');

        $meta .= sprintf('<meta property="og:locale" content="%s" />', isset($wtt_social['wtt_og_locale']) ? $wtt_social['wtt_og_locale'] : get_locale()) . "\n";

        if (is_author()) {
            $author = get_queried_object();

            $meta .= sprintf('<meta property="og:type" content="%s" />', 'profile') . "\n";
            if(get_the_author_meta('first_name', $author->ID) || get_the_author_meta('last_name', $author->ID) !== '') {
                $meta .= sprintf('<meta property="profile:first_name" content="%s" />', get_the_author_meta('first_name', $author->ID)) . "\n";
                $meta .= sprintf('<meta property="profile:last_name" content="%s" />', get_the_author_meta('last_name', $author->ID)) . "\n";
            }
        } else if (function_exists('is_product') && is_product()) {
            $meta .= sprintf('<meta property="og:type" content="%s" />', 'product') . "\n";

            $cat = get_the_terms($post->ID, 'product_cat');
            if (!empty($cat) && count($cat) > 0) {
                $meta .= sprintf('<meta property="product:category" content="%s" />', $cat[0]->name) . "\n";
            }
        } else if (is_front_page() || is_home()) {
            $meta .= sprintf('<meta property="og:type" content="%s" />', 'website') . "\n";
        } else if (is_singular()) {

            $meta .= sprintf('<meta property="og:type" content="%s" />', 'article') . "\n";
            $meta .= sprintf('<meta property="article:published_time" content="%s" />', get_the_time('c', $post->ID)) . "\n";
            $facebook = get_the_author_meta( 'facebook', $GLOBALS['post']->post_author );

            $meta .= (($facebook <> '') ? sprintf('<meta property="article:author" content="%s" />', $facebook) . "\n" : '');
            $meta .= (($wtt_social['facebook-site'] <> '') ? sprintf('<meta property="article:publisher" content="https://facebook.com/%s" />', $wtt_social['facebook-site']) . "\n" : '');

            $category = get_the_category($post->ID);
            if (!empty($category) && $category[0]->cat_name <> 'Uncategorized') {
                $meta .= sprintf('<meta property="article:section" content="%s" />', $category[0]->cat_name) . "\n";
            }

            $tags = get_the_tags();
            if ( ( is_array( $tags ) && !is_wp_error($tags) && $tags !== array())) {
                foreach ( $tags as $tag ) {
                    $meta .= sprintf('<meta property="article:tag" content="%s" />', $tag->name) . "\n";
                }
            }
        } else {
            $meta .= sprintf('<meta property="og:type" content="%s" />', 'object') . "\n";
        }

        return $meta;
    }

    /**
     * Get the Twitter Card Meta
     */
    private function get_twittercard_meta()
    {
        $meta = "";
        $wtt_social = get_option("wtt_social");

        $openGraphTitle = $this->generate_og_title();
        $openGraphImage = $this->get_custom_image();
        $ogDescription = $this->get_og_description();

        if (isset($wtt_social['twitter_card_type']) && $wtt_social['twitter_card_type'] <> '') {
            $meta .= sprintf('<meta name="twitter:card" content="%s" />', $wtt_social['twitter_card_type']) . "\n";
        }

        if ( is_singular() ) {
            $twitter = get_the_author_meta('twitter', $GLOBALS['post']->post_author);
            if (get_post() && is_object(get_post())) {
                $meta .= (($twitter <> '') ? sprintf('<meta name="twitter:creator" content="%s" />', $this->get_twitter_account($twitter)) . "\n" : '');
            }
        }

        $meta .= (($wtt_social['twitter-site'] <> '') ? sprintf('<meta name="twitter:site" content="%s" />', $this->get_twitter_account($wtt_social['twitter-site'])) . "\n" : '');
        $meta .= (($openGraphImage <> '') ? sprintf('<meta name="twitter:image" content="%s" />', $openGraphImage) . "\n" : '');
        $meta .= (($openGraphTitle <> '') ? sprintf('<meta name="twitter:title" content="%s" />', $openGraphTitle) . "\n" : '');
        $meta .= (($ogDescription <> '') ? sprintf('<meta name="twitter:description" content="%s" />', $ogDescription) . "\n" : '');

        $meta .= ((get_bloginfo('name') <> '') ? sprintf('<meta name="twitter:domain" content="%s" />', get_bloginfo('name')) . "\n" : '');

        return $meta;
    }

    /**
     * Get the twitter account from the database
     *
     * @param string $twitterAccount
     * @return bool|string
     */
    private function get_twitter_account($twitterAccount)
    {
        if ($twitterAccount <> '') {
            if (strpos($twitterAccount, 'twitter.com') !== false) {
                preg_match('/twitter.com\/([@1-9a-z_-]+)/i', $twitterAccount, $matchResult);
                if (isset($matchResult[1]) && !empty($matchResult[1])) {
                    return '@' . str_replace('@', '', $matchResult[1]);
                }
            } else {
                preg_match('/([@1-9a-z_-]+)/i', $twitterAccount, $matchResult);
                if (isset($matchResult[1]) && !empty($matchResult[1])) {
                    return '@' . str_replace('@', '', $matchResult[1]);
                }
            }
        } else {
            return '';
        }
        return false;
    }

    /**
     * Checks if other plugins have description enabled
     *
     * @return bool
     */
    private function get_other_plugin_description()
    {
        if (is_wp_seo_active()) {
            $desc = $this->getPostMeta('_yoast_wpseo_metadesc');

            if ($desc !== '') {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    protected $meta_description = null;
    protected $og_meta_desc = null;

    /**
     * Creates the meta description element
     * @param bool $show
     * @return null|string
     */
    private function get_meta_description( $show = true )
    {
        if (is_null($this->meta_description)) {
            $this->generate_meta_description();
        }

        if($show !== false) {
            if (is_string($this->meta_description) && $this->meta_description !== '') {
                return sprintf('<meta name="description" content="%s" />', esc_attr(wp_strip_all_tags(stripslashes($this->meta_description)))) . "\n";
            }
        }
        return $this->meta_description;
    }

    private function get_og_description()
    {
        if (is_null($this->og_meta_desc)) {
            $this->generate_og_description();
        }

        if (is_string($this->og_meta_desc) && $this->og_meta_desc !== '') {
            return esc_attr(wp_strip_all_tags(stripslashes($this->og_meta_desc)));
        }

        return $this->og_meta_desc;
    }

    /**
     * Return the default meta description from the post if open graph description field is empty
     * If both description fields are empty, post excerpt or post content overwrites the description tag
     *
     * @return string
     */
    private function generate_meta_description()
    {
        global $post;
        $wtt_settings = get_option('wtt_settings');
        $description = '';
        $main_desc = false;
        $desc_template = '';
        $post_type = '';

        if (is_object($post) && (isset($post->post_type) && $post->post_type !== '')) {
            $post_type = $post->post_type;
        }

        if ($this->is_woocommerce_page()) {
            $shop_page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : ( -1 );
            $post = get_post($shop_page_id);
            $post_type = get_query_var('post_type');

            $option_key = 'metadesc-ptarchive-'.$post_type;
            if ( ( $description === '' && $post_type !== '' ) && isset( $wtt_settings[ $option_key ] ) ) {
                $desc_template = $wtt_settings[ 'metadesc-ptarchive-' . $post_type ];
            }
            $description = $this->getPostMeta( '_wtt_post_description', $post->ID );
        }
        elseif ($this->is_single_page()) {
            $post      = get_post( $this->get_single_page_id() );
            $post_type = $post->post_type;

            $option_key = 'metadesc-' . $post_type;
            if ( ( $description === '' && $post_type !== '' ) && isset( $wtt_settings[ $option_key ] ) ) {
                $desc_template = $wtt_settings[ $option_key ];
            }
            $main_desc = $this->getPostMeta( '_wtt_post_description', $post->ID );
        }
        else {
            if (is_search()) {
                $description = '';
            } elseif (get_option('show_on_front') === 'posts' && is_home()) { //Home post page
                $desc_template = $wtt_settings['metadesc-home-wtt'];
                if (empty($desc_template)) {
                    $desc_template = get_bloginfo('description');
                }
            } elseif (get_option('show_on_front') === 'page' && is_page(get_option('page_on_front')) && is_front_page()) { //Home static page
                $description = $this->getPostMeta('_wtt_post_description');
                if (($description === '' && $post_type !== '') && isset($wtt_settings['metadesc-' . $post_type])) {
                    $desc_template = $wtt_settings['metadesc-' . $post_type];
                }
            } elseif (is_category() || is_tag() || is_tax()) {
                $term = $GLOBALS['wp_query']->get_queried_object();
                $description = $this->get_wtt_tax_term_meta($term, $term->taxonomy, '_wtt_term_description');

                if (is_object($term) && isset($term->taxonomy) && isset($wtt_settings['metadesc-tax-' . $term->taxonomy]) && $wtt_settings['metadesc-tax-' . $term->taxonomy] !== '') {
                    $desc_template = $wtt_settings['metadesc-tax-' . $term->taxonomy];
                }
            } elseif (is_author()) {
                if (isset($wtt_settings['metadesc-author-wtt']) && $wtt_settings['metadesc-author-wtt'] !== '') {
                    $desc_template = $wtt_settings['metadesc-author-wtt'];
                }
            } elseif (is_post_type_archive()) {
                if (isset($wtt_settings['metadesc-ptarchive-' . $post_type])) {
                    $desc_template = $wtt_settings['metadesc-ptarchive-' . $post_type];
                }
            } elseif (is_archive()) {
                $desc_template = isset($wtt_settings['metadesc-archive-wtt']) ? $wtt_settings['metadesc-archive-wtt'] : '';
            }
        }

        if ($main_desc !== '' && is_string($main_desc)) {
            $description = $main_desc;
        } elseif ($desc_template !== '' && ($description === '')) {
            $description = $desc_template;
        }

        $description = $this->replacePatterns($description, $post);

        $this->meta_description = $description;
    }

    private function generate_og_description()
    {
        global $post;
        $og_desc = '';

        if (is_front_page()) {
            $og_desc = $this->meta_description;
        }

        if ($this->is_single_page()) {
            $postId = $this->get_single_page_id();
            $post = get_post( $postId );
            $og_desc = self::getPostMeta('_wtt_opengraph-description');

            $og_desc = $this->replacePatterns( $og_desc, $post );

            if ($og_desc === '') {
                $og_desc = $this->get_meta_description( false );
            }

            if (!is_string($og_desc) || (is_string($og_desc) && $og_desc === '')) {
                $og_desc = str_replace( '[&hellip;]', '&hellip;', wp_strip_all_tags( get_the_excerpt() ) );
            }
        }

        if (is_tax() || is_category() || is_tag()) {
            $og_desc = '';
            if ($og_desc === '') {
                $og_desc = $this->get_meta_description(false);
            }
            if ($og_desc === '') {
                $og_desc = wp_strip_all_tags(term_description());
            }
            if ( $og_desc === '' ) {
                $og_desc = $this->get_wtt_meta_without_term('_wtt_term_description');
            }
            $og_desc = $this->replacePatterns($og_desc, $post);
        }

        if ( is_string( $og_desc ) && $og_desc !== '' ) {
            $this->og_meta_desc = trim(strip_shortcodes($og_desc));
        }
    }

    protected $canonical_url;
    protected $og_url;

    /**
     * Return the default canonical url from the post if canonical field is empty
     *
     * @return string
     */
    private function get_canonical_url()
    {
        if (is_null($this->canonical_url)) {
            $this->generate_canonical_url();
        }

        if (is_string($this->canonical_url) && $this->canonical_url !== '') {
            return sprintf('<link rel="canonical" href="%s" />', esc_url($this->canonical_url)) . "\n";
        }

        return $this->canonical_url;
    }

    private function get_og_url()
    {
        if (is_null($this->og_url)) {
            $this->generate_canonical_url();
        }

        if (is_string($this->og_url) && $this->og_url !== '') {
            return esc_url($this->og_url);
        }

        return $this->og_url;
    }

    private function generate_canonical_url()
    {
        global $post;
        $url = "";
        $wtt_canonical_url = '';

        if(is_singular()) {
            $queried_object = get_queried_object();
            $url = get_permalink( $queried_object->ID );

            $wtt_canonical_url = self::getPostMeta('_wtt_canonical-link');

            if ( $post->ID === get_queried_object_id() ) {
                $page = get_query_var( 'page', 0 );
                if ( $page >= 2 ) {
                    if ( '' == get_option( 'permalink_structure' ) ) {
                        $url = add_query_arg( 'page', $page, $url );
                    } else {
                        $url = trailingslashit( $url ) . user_trailingslashit( $page, 'single_paged' );
                    }
                }

                $cpage = get_query_var( 'cpage', 0 );
                if ( $cpage ) {
                    $url = get_comments_pagenum_link( $cpage );
                }
            }
        } else {
            if (is_search()) {
                $search_query = get_search_query();
                if (!empty($search_query) && !preg_match('|^page/\d+$|', $search_query)) {
                    $url = get_search_link();
                }
            } elseif(is_front_page()) {
                $url = self::get_home_url();
            } elseif (is_home() && get_option( 'show_on_front' ) === 'page') {

                $posts_page_id = get_option( 'page_for_posts' );
                $url = $this->getPostMeta( '_wtt_canonical-link', $posts_page_id );

                if ( empty( $url ) ) {
                    $url = get_permalink( $posts_page_id );
                }
            } elseif(is_category() || is_tag() || is_tax()) {
                $term = $GLOBALS['wp_query']->get_queried_object();

                if(!empty($term)) {
                    $term_link = get_term_link($term, $term->taxonomy);

                    if ( !is_wp_error( $term_link ) ) {
                        $url = $term_link;
                    }
                }
            } elseif ( is_post_type_archive() ) {
                $post_type = get_query_var('post_type');
                $url = get_post_type_archive_link( $post_type );
            }
            elseif ( is_author() ) {
                $url = get_author_posts_url( get_query_var( 'author' ), get_query_var( 'author_name' ) );
            }
            elseif ( is_archive() ) {
                if ( is_date() ) {
                    if ( is_day() ) {
                        $url = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
                    }
                    elseif ( is_month() ) {
                        $url = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
                    }
                    elseif ( is_year() ) {
                        $url = get_year_link( get_query_var( 'year' ) );
                    }
                }
            }
        }

        if (is_string( $wtt_canonical_url ) && $wtt_canonical_url !== '' ) {
            $url = $wtt_canonical_url;
        }

        $this->canonical_url = $url;
        $this->og_url = $url;
    }

    public static function get_home_url($url_path = '') {

        $url = home_url();

        if ( !empty( $url_path ) ) {
            return $url;
        }

        $url_path = wp_parse_url( $url, PHP_URL_PATH );

        if ( '/' === $url_path ) {
            return $url;
        }

        if ( is_null( $url_path ) ) {
            return trailingslashit( $url );
        }

        if ( is_string( $url_path ) ) {
            return user_trailingslashit( $url );
        }

        return $url;
    }

    /**
     * Return the default title from the post if og title is empty
     *
     * @return string
     */
    private function generate_og_title()
    {
        if($this->is_single_page()) {
            $post_id = $this->get_single_page_id();
            $post = get_post($post_id);
            $title = $this->getPostMeta('_wtt_opengraph-title', $post_id);

            if($title === '' || $title === false) {
                $title = $this->wtt_generate_seo_title('', "");
            } else {
                $title = $this->replacePatterns($title, $post);
            }
        }
        elseif(is_front_page() ) {
            $title = $this->wtt_generate_seo_title('', "");
        }
        elseif(is_tag() || is_tax() || is_category()) {
            $title = $this->get_wtt_meta_without_term('_wtt_term_title');

            if($title === '') {
                $title = $this->wtt_generate_seo_title('', "");
            } else {
                $title = $this->replacePatterns($title, $GLOBALS['wp_query']->get_queried_object());
            }
        }
        else {
            $title = $this->wtt_generate_seo_title('', "");
        }

        return trim($title);
    }

    /**
     * Get image for meta data
     *
     * @param null $post_id
     * @return false|mixed|string
     */
    private function get_custom_image($post_id = null)
    {
        $imageIsDone = false;
        $openGraphImage = '';
        $wtt_social = get_option("wtt_social");

        if ($post_id) {
            $current_post = false;
            //Specific post
            $post = get_post($post_id);
        } else {
            $current_post = true;
            //Current post
            global $post;
        }
        if ($post) {
            /**
             * If page is attachment
             */
            if (!$current_post && is_attachment()) {
                if ($temp = wp_get_attachment_image_src(null, 'full')) {
                    $openGraphImage = trim($temp[0]);
                    if (trim($openGraphImage) != '') {
                        $imageIsDone = true;
                    }
                }
            }

            /**
             * If specific image from post
             */
            if (!$imageIsDone) {
                if (!empty($wtt_social['og_image_use_specific']) && $wtt_social['og_image_use_specific'] == "on") {
                    if ($openGraphImage = self::getPostMeta('_wtt_opengraph-image')) {
                        if ($openGraphImage != '') {
                            $imageIsDone = true;
                        }
                    }
                }
            }

            /**
             * If featured image from post is active
             */
            if (!$imageIsDone) {
                if (function_exists('get_post_thumbnail_id')) {
                    if (!empty($wtt_social['og_image_use_featured']) && $wtt_social['og_image_use_featured'] == "on") {
                        if ($id_attachment = get_post_thumbnail_id($post->ID)) {
                            $openGraphImage = wp_get_attachment_url($id_attachment);
                            $imageIsDone = true;
                        }
                    }
                }
            }

            /**
             * If use default image specified in the settings screen
             */
            if (!$imageIsDone) {
                if (!empty($wtt_social['og_image_use_default']) && $wtt_social['og_image_use_default'] == "on") {
                    $openGraphImage = $wtt_social['opengraph_image'];
                } else {
                    $openGraphImage = '';
                }
            }

            return $openGraphImage;
        } else {
            //No post
            return false;
        }
    }

    /**
     * Hooks into the save post function and saves wtt social meta tags
     *
     * @param $post_id
     * @return mixed
     */
    function wtt_save_social_meta($post_id)
    {
        // Check if our nonce is set.
        if (!isset($_POST['wttcontent']))
            return $post_id;

        $nonce = $_POST['wttcontent'];

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, 'wttcallback'))
            return $post_id;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        // Check the user's permissions.
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return $post_id;
        } else {
            if (!current_user_can('edit_post', $post_id))
                return $post_id;
        }

        // Sanitize user input.
        $openGraphTitle = stripslashes($_POST['wtt_opengraph-title']);
        $openGraphDescription = stripslashes(str_replace(array("\r\n", "\r", "\n"), '', $_POST['wtt_opengraph-description']));
        $openGraphImage = sanitize_text_field($_POST['wtt_opengraph-image']);
        $canonicalLink = sanitize_text_field($_POST['wtt_canonical-link']);

        // Update the meta field in the database if the field is not empty.
        if (isset($_POST['wtt_opengraph-title']) && ($_POST['wtt_opengraph-title']) <> "") {
            update_post_meta($post_id, '_wtt_opengraph-title', $openGraphTitle);
        } else {
            delete_post_meta($post_id, '_wtt_opengraph-title', $openGraphTitle);
        }

        if (isset($_POST['wtt_opengraph-description']) && ($_POST['wtt_opengraph-description']) <> "") {
            update_post_meta($post_id, '_wtt_opengraph-description', $openGraphDescription);
        } else {
            delete_post_meta($post_id, '_wtt_opengraph-description', $openGraphDescription);
        }

        if (isset($_POST['wtt_opengraph-image']) && ($_POST['wtt_opengraph-image']) <> "") {
            update_post_meta($post_id, '_wtt_opengraph-image', $openGraphImage);
        } else {
            delete_post_meta($post_id, '_wtt_opengraph-image', $openGraphImage);
        }

        if (isset($_POST['wtt_canonical-link']) && ($_POST['wtt_canonical-link']) <> "") {
            update_post_meta($post_id, '_wtt_canonical-link', $canonicalLink);
        } else {
            delete_post_meta($post_id, '_wtt_canonical-link', $canonicalLink);
        }

        return $post_id;
    }

    /**
     * If wp seo is active, return true
     * Returns configuration for custom fields on posts.
     *
     * @return array See `tm_structured_data_fields` filter for more documentation.
     */
    public static function get_structured_data_fields() {
        $option_name = "wtt_social";
        $options = get_option($option_name);

        $default_field = [
            'label'              => null,
            'placeholder'        => null,
            'description'        => null,
            'priority'           => 10,
            'value'              => null,
            'default'            => null,
            'classes'            => [],
            'type'               => 'text',
            'data_type'          => 'string',
        ];

        $fields = [
            '_tm_monetary_currency' => [
                'label'             => __( 'Salary Currency', WTT_PLUGIN_NAME ),
                'description'       => __('ISO 4217 Currency code. Example: EUR', WTT_PLUGIN_NAME ),
                'priority'          => 2,
                'type'              => 'text',
                'data_type'         => 'string',
                'classes'           => ['tm-validate-field', 'tm-job-monetary-currency'],
                'attributes' => [
                    'data-rule-regex'       => 'true',
                    'data-validate-pattern' => '^[A-Z]{3}$',
                    'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: EUR', WTT_PLUGIN_NAME ),
                ],
            ],
            '_tm_monetary_salary' => [
                'label'             => __( 'Salary (Recommended)', WTT_PLUGIN_NAME ),
                'description'       => __('Insert amount, e.g. "50.00", or a salary range, e.g. "40.00-50.00".', WTT_PLUGIN_NAME),
                'priority'          => 3,
                'type'              => 'text',
                'data_type'         => 'number',
                'classes'           => ['tm-validate-field', 'tm-job-monetary-salary'],
                'attributes' => [
                    'data-rule-regex'       => 'true',
                    'data-validate-pattern' => '[\d -]+',
                    'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: 50000', WTT_PLUGIN_NAME ),
                ],
            ],
            '_tm_monetary_payroll' => [
                'label'             => __( 'Payment per', WTT_PLUGIN_NAME ),
                'priority'          => 4,
                'type'              => 'select',
                'classes'           => ['tm-job-monetary-payroll'],
                'options'           => [
                    ""         => __("None", WTT_PLUGIN_NAME),
                    "HOUR" => __("Hour", WTT_PLUGIN_NAME),
                    "DAY" => __("Day", WTT_PLUGIN_NAME),
                    "WEEK" => __("Week", WTT_PLUGIN_NAME),
                    "MONTH" => __("Month", WTT_PLUGIN_NAME),
                    "YEAR" => __("Year", WTT_PLUGIN_NAME),
                ],
                'data_type'         => 'string',
            ],
            '_tm_job_expires'     => [
                'label'              => __( 'Expiry posted', WTT_PLUGIN_NAME ),
                'description'       => __('If a job posting never expires, or you do not know when the job will expire, leave this blank.', WTT_PLUGIN_NAME),
                'priority'           => 5,
                'data_type'          => 'string',
                'classes'            => [ 'structured-data-datepicker', 'tm-job-expires'],
                'sanitize_callback'  => [ __CLASS__, 'sanitize_meta_field_date' ],
            ],
            '_tm_employment_type'     => [
                'label'              => __( 'Employment Type (Recommended)', WTT_PLUGIN_NAME ),
                'priority'           => 6,
                'type'               => 'select',
                'classes'            => [ 'tm-job-employment-type' ],
                'options'            => [
                    ""         => __("None", WTT_PLUGIN_NAME),
                    "FULL_TIME" => __("Full Time", WTT_PLUGIN_NAME),
                    "PART_TIME" => __('Part Time', WTT_PLUGIN_NAME),
                    "CONTRACTOR" => __('Contractor', WTT_PLUGIN_NAME),
                    "TEMPORARY" => __('Temporary', WTT_PLUGIN_NAME),
                    "INTERN" => __('Intern', WTT_PLUGIN_NAME),
                    "VOLUNTEER" => __('Volunteer', WTT_PLUGIN_NAME),
                    "PER_DIEM" => __('Per diem', WTT_PLUGIN_NAME),
                    "OTHER" => __('Other', WTT_PLUGIN_NAME)
                ],
                'data_type'          => 'string',
            ],
            '_tm_company_name'    => [
                'label'         => __( 'Company Name', WTT_PLUGIN_NAME ),
                'description'   =>  __('The name of the company. Leave empty to use your own company information.', WTT_PLUGIN_NAME),
                'placeholder'   => !empty($options['knowledgegraph_name']) ? $options['knowledgegraph_name'] : '',
                'priority'      => 7,
                'classes'           => ['tm-job-company-name'],
                'data_type'     => 'string',
            ],
            '_tm_company_website' => [
                'label'             => __( 'Organization URL (Recommended)', WTT_PLUGIN_NAME ),
                'description'       =>  __('The URL of the organization offering the job position.', WTT_PLUGIN_NAME),
                'placeholder'       => get_site_url(),
                'priority'          => 8,
                'data_type'         => 'string',
                'classes'           => ['tm-job-company-website'],
                'sanitize_callback' => [ __CLASS__, 'sanitize_meta_field_url' ],
            ],
            '_tm_company_logo' => [
                'label'             => __( 'Organization Logo (Recommended)', WTT_PLUGIN_NAME ),
                'description'       => __('Logo URL of the organization offering the job position. Leave empty to use your own company information.', WTT_PLUGIN_NAME),
                'placeholder'       => !empty($options['knowledgegraph_logo']) ? $options['knowledgegraph_logo'] : '',
                'priority'          => 9,
                'type'              => 'file',
                'classes'           => ['tm-structured-data-company-logo'],
                'data_type'         => 'string',
                'sanitize_callback' => [ __CLASS__, 'sanitize_meta_field_url' ],
            ],
            '_tm_job_location_streetAddress'    => [
                'label'         => __( 'Location', WTT_PLUGIN_NAME ),
                'description'   => __( 'Leave this blank if the location is not important or to use your own company information', WTT_PLUGIN_NAME ),
                'placeholder'   => !empty($options['knowledgegraph_location_streetAddress']) ? $options['knowledgegraph_location_streetAddress'] : __( 'Street Address', WTT_PLUGIN_NAME ),
                'priority'      => 10,
                'classes'       => ['tm-job-location'],
                'data_type'     => 'string',
            ],
            '_tm_job_location_addressLocality'    => [
                'placeholder'   => !empty($options['knowledgegraph_location_addressLocality']) ? $options['knowledgegraph_location_addressLocality'] : __( 'Locality e.g. "Amsterdam"', WTT_PLUGIN_NAME ),
                'priority'      => 11,
                'classes'       => ['tm-job-location'],
                'data_type'     => 'string',
            ],
            '_tm_job_location_addressRegion'    => [
                'placeholder'   => !empty($options['knowledgegraph_location_addressRegion']) ? $options['knowledgegraph_location_addressRegion'] : __( 'Region', WTT_PLUGIN_NAME ),
                'priority'      => 12,
                'classes'       => ['tm-job-location'],
                'data_type'     => 'string',
            ],
            '_tm_job_location_postalCode'    => [
                'placeholder'   => !empty($options['knowledgegraph_location_postalCode']) ? $options['knowledgegraph_location_postalCode'] : __( 'Postal Code', WTT_PLUGIN_NAME ),
                'priority'      => 13,
                'classes'       => ['tm-job-location'],
                'data_type'     => 'string',
            ],
            '_tm_job_location_addressCountry'    => [
                'placeholder'   => !empty($options['knowledgegraph_location_addressCountry']) ? $options['knowledgegraph_location_addressCountry'] : __( 'Country', WTT_PLUGIN_NAME ),
                'priority'      => 14,
                'classes'           => ['tm-job-location'],
                'data_type'     => 'string',
            ],
        ];

        /**
         * Filters structured data fields
         *
         * @since 3.1.0
         *
         * @param array    $fields  {
         *     Structured data meta fields. Associative array with meta key as the index.
         *     All fields except for `$label` are optional and have working defaults.
         *
         *     @type array $meta_key {
         *         @type string        $label              Label to show for field.
         *         @type string        $placeholder        Placeholder to show in empty form fields.
         *         @type string        $description        Longer description to shown below form field.
         *         @type array         $classes            Classes to apply to form input field.
         *         @type int           $priority           Field placement priority for WP admin. Lower is first.
         *         @type string        $value              Override standard retrieval of meta value in form field.
         *         @type string        $default            Default value on form field if no other value is set for
         *                                                 field.
         *         @type string        $type               Type of form field to render. (Default: 'text').
         *         @type string        $data_type          Data type to cast to. Options: 'string', 'boolean',
         *                                                 'integer', 'number'.
         *                                                 Default: 'string').
         *         @type callable      $sanitize_callback  {
         *             Sanitizes the meta value before saving to database.
         *             Defaults to callable that sanitizes based on the field type.
         *
         *             @param mixed  $meta_value Value of meta field that needs sanitization.
         *             @param string $meta_key   Meta key that is being sanitized.
         *
         *             @return mixed
         *         }
         *     }
         * }
         */
        $fields = apply_filters( 'tm_structured_data_fields', $fields );

        // Ensure default fields are set.
        foreach ( $fields as $key => $field ) {
            $fields[ $key ] = array_merge( $default_field, $field );
        }

        return $fields;
    }

    /**
     * Sanitize URL meta fields.
     *
     * @param string $meta_value Value of meta field that needs sanitization.
     * @return string
     */
    public static function sanitize_meta_field_url( $meta_value ) {
        $meta_value = trim( $meta_value );
        if ( '' === $meta_value ) {
            return $meta_value;
        }
        return esc_url_raw( $meta_value );
    }

    /**
     * Sanitize date meta fields.
     *
     * @param string $meta_value Value of meta field that needs sanitization.
     * @return string
     */
    public static function sanitize_meta_field_date( $meta_value ) {
        $meta_value = trim( $meta_value );

        // Matches yyyy-mm-dd.
        if ( ! preg_match( '/[\d]{4}\-[\d]{2}\-[\d]{2}/', $meta_value ) ) {
            return '';
        }

        // Checks for valid date.
        if ( date( 'Y-m-d', strtotime( $meta_value ) ) !== $meta_value ) {
            return '';
        }

        return $meta_value;
    }

    public static function set_schema_type_selection_fields() {
        $default_field = [
            'label'              => null,
            'placeholder'        => null,
            'description'        => null,
            'priority'           => 10,
            'value'              => null,
            'default'            => null,
            'classes'            => [],
            'type'               => 'text',
            'data_type'          => 'string',
        ];

        $fields = [
            '_tm_schema_type' => [
                'label'             => __( 'Select Schema Type', WTT_PLUGIN_NAME ),
                'priority'          => 1,
                'type'              => 'select',
                'options'           => [
                    "off"         => __("None", WTT_PLUGIN_NAME),
                    "job_posting" => __("Job Posting", WTT_PLUGIN_NAME),
                ],
                'data_type'         => 'string',
            ]
        ];

        $fields = apply_filters( 'set_schema_type_selection_fields', $fields );

        // Ensure default fields are set.
        foreach ( $fields as $key => $field ) {
            $fields[ $key ] = array_merge( $default_field, $field );
        }

        return $fields;
    }

    /**
     * Returns configuration for custom fields on posts/pages.
     *
     * @return array
     */
    public function structured_listing_fields() {
        global $post_id;

        $fields_raw   = self::get_structured_data_fields();
        $fields_schema = self::set_schema_type_selection_fields();
        $fields       = [];

        foreach ( $fields_schema as $meta_key => $field ) {
            $fields[ $meta_key ] = $field;
        }

        foreach ( $fields_raw as $meta_key => $field ) {
            $fields[ $meta_key ] = $field;
        }

        uasort( $fields, [ __CLASS__, 'sort_by_priority' ] );

        return $fields;
    }

    /**
     * Sorts array of custom fields by priority value.
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected static function sort_by_priority( $a, $b ) {
        if ( ! isset( $a['priority'] ) || ! isset( $b['priority'] ) || $a['priority'] === $b['priority'] ) {
            return 0;
        }

        return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
    }


    /**
     * Handles `save_post` action for Structured data.
     *
     * @param int $post_id
     * @param WP_Post $post
     */
    public function tm_save_post( $post_id, $post ) {
        if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( is_int( wp_is_post_revision( $post ) ) ) {
            return;
        }
        if ( is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }

        // Check if our nonce is set.
        if (!isset($_POST['wttcontent'])) {
            return $post_id;
        }

        $nonce = $_POST['wttcontent'];

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, 'wttcallback')) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $schema_type = $_POST['_tm_schema_type'];

        if(isset($schema_type) && $schema_type !== "") {
            do_action( 'tm_save_structured_data', $post_id );
        }

        return;
    }

    /**
     * Handles the actual saving of Structured data fields.
     *
     * @param int     $post_id
     */
    public function save_structured_data( $post_id ) {
        $values = [];
        $results = $this->structured_listing_fields();

        // Save fields.
        foreach ( $results as $key => $field ) {
            if ( isset( $field['type'] ) && 'info' === $field['type'] ) {
                continue;
            }

            // Checkboxes that aren't sent are unchecked.
            if ( 'checkbox' === $field['type'] ) {
                if ( ! empty( $_POST[ $key ] ) ) {
                    $_POST[ $key ] = 1;
                } else {
                    $_POST[ $key ] = 0;
                }
            }

            $values[$key] = $_POST[$key];

            if(isset($values['_tm_schema_type'])) {
                if($values['_tm_schema_type'] == 'job_posting') {
                    update_post_meta($post_id, "_tm_page_settings", $values);
                } else {
                    delete_post_meta($post_id, "_tm_page_settings");
                }
            }
        }
    }
}