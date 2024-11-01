<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Webtexttool
 * @subpackage Webtexttool/admin
 */
class Webtexttool_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * @var  array  Array of defaults for the option
     */
    protected $defaults = array();

    protected $updated_defaults = array(
        'separator' => 'sc-dash',
        'title-home-wtt' => '{{sitetitle}} {{page}} {{sep}} {{tagline}}',
        'title-archive-wtt' => '{{archive_date}} {{page}} {{sep}} {{sitetitle}}',
        'title-author-wtt' => '',
        'title-search-wtt' => '',
        'title-404-wtt' => '',
        'metadesc-home-wtt' => '',
        'metadesc-author-wtt' => '',
        'metadesc-archive-wtt' => '',
        'noindex-author-wtt' => false,
        'noindex-archive-wtt' => true,
    );

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
     * Register the menu item and its submenus into the WordPress dashboard menu.
     *
     **/
    public function add_plugin_admin_menu()
    {
        add_menu_page(__(WTT_PLUGIN_NAME) . 'for Wordpress', __(WTT_PLUGIN_NAME), $this->get_manage_capability(), 'wtt_dashboard', null,
            plugin_dir_url(__FILE__) . 'images/tm_logo_16.png', '99.5');
        add_submenu_page(
            'wtt_dashboard',
            __(WTT_PLUGIN_NAME) . ' for Wordpress',
            'Dashboard',
            $this->get_manage_capability(),
            'wtt_dashboard',
            array($this, 'display_plugin_setup_page')
        );
        add_submenu_page(
            'wtt_dashboard',
            __(WTT_PLUGIN_NAME). ' Settings',
            'Settings',
            'manage_options',
            'wtt_settings',
            array($this, 'display_plugin_settings_page')
        );
        add_submenu_page(
            'wtt_dashboard',
            __(WTT_PLUGIN_NAME). ' Social',
            'Social',
            'manage_options',
            'wtt_social',
            array($this, 'display_plugin_social_page')
        );
        add_submenu_page(
            'wtt_dashboard',
            __(WTT_PLUGIN_NAME). ' Tools',
            'Tools',
            'manage_options',
            'wtt_tools',
            array($this, 'display_plugin_tools_page')
        );
    }

    /**
     * Applies user capabilities filter in order to overwrite
     *
     * @return mixed|void
     */
    protected function get_manage_capability() {
        return apply_filters( 'wtt_manage_options_capability', 'edit_posts' );
    }

    /**
     * Register the settings
     */
    public function wtt_register_settings()
    {
        register_setting('wtt_settings', 'wtt_settings');
    }

    /**
     * Register the settings
     */
    public function wtt_register_social_settings()
    {
        register_setting('wtt_social', 'wtt_social');
    }

    public function admin_init() {
        $post_types = get_post_types(array('public' => true), 'names');

        if (is_array($post_types) && $post_types !== array()) {
            foreach ($post_types as $type) {
                if ($this->wtt_metabox_hide($type) === false) {
                    add_filter('manage_' . $type . '_posts_columns', array($this, 'set_custom_wtt_columns'), 10, 1);
                    add_action('manage_' . $type . '_posts_custom_column', array($this, 'fill_custom_wtt_columns'), 10, 2);
                }
            }
            unset($pt);
        }
    }

    /**
     * Render the admin page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_setup_page()
    {
        ?>
        <script type="text/javascript">
            (function () {
                var existingWindowDotAngular = window.angular;
                var angular = (window.angular = {});

                <?php
                include dirname(__FILE__) . "/js/wtt-admin.min.js";
                ?>

                angular.element(document).ready(function () {
                    angular.bootstrap(document.getElementById('wtt-dashboard'), ['wttDashboard']);
                    window.angular = existingWindowDotAngular;
                });
            })();
        </script>
        <?php

        wp_enqueue_style('wtt-fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), null, 'all');
        wp_enqueue_style('wtt-admin-css', plugins_url('admin/css/wtt-admin.min.css', dirname(__FILE__)), array(), WTT_VERSION, 'all');
        wp_enqueue_script('wtt-admin-controller', plugins_url('admin/js/app-controller.min.js', dirname(__FILE__)), array(), WTT_VERSION, true );

        require_once('partials/dashboard/wtt-admin-display.php');
    }

    /**
     * Render the social page for this plugin.
     *
     * @since    1.2.4
     */
    public function display_plugin_social_page()
    {
        require_once('partials/social/wtt-social-settings-display.php');
    }

    /**
     * Includes the settings html page.
     */
    public function display_plugin_settings_page()
    {
        require_once('partials/settings/wtt-settings.php');
    }

    public function display_plugin_tools_page()
    {
        require plugin_dir_path(__FILE__) . '/partials/tools/wtt-tools-import.php';
        require_once('partials/tools/wtt-tools.php');
    }

    /**
     * Enqueues custom CSS file for admin screen
     *
     * @since   1.1.2
     * @param   $hook String used to target a specific admin page
     */
    public function enqueue_custom_wtt_css($hook)
    {
        wp_enqueue_style ('wtt_custom_stylesheet', plugins_url('css/wtt-global-page.min.css', __FILE__), array(), $this->version) ;
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
     * Adds a Webtexttool Page Score column to the admin page
     *
     * @param array $columns Current set columns.
     *
     * @return array
     */
    public function set_custom_wtt_columns($columns)
    {
        return array_merge($columns,
            array('wttPageScore' => __('TM Score', WTT_PLUGIN_NAME),
                'wttMetaTitle' => __('TM SEO Title', WTT_PLUGIN_NAME),
                'wttMetaDesc' => __('TM Meta Desc', WTT_PLUGIN_NAME)));
    }

    /**
     * Parses the column.
     *
     * @param string $column The current column.
     * @param integer $post_id ID of requested post.
     *
     * @return string
     */
    public function fill_custom_wtt_columns($column, $post_id)
    {
        switch ($column) {

            /* If displaying the 'wttPageScore' column. */
            case 'wttPageScore' :

                /* Get the post meta. */
                $pageSeoScore = get_post_meta($post_id, '_wtt_page_score', true);
                $pageContentScore = get_post_meta($post_id, 'wtt_content_quality_suggestions', true);
                $wttKeyword = get_post_meta($post_id, '_wtt_post_keyword', true);

                /* If no pageScore is found and no keyword, output a default message. */
                if (empty($pageSeoScore) && empty($wttKeyword) && empty($pageContentScore)) {
                    echo('<div class="wtt-score-text"><a href="' . get_edit_post_link($post_id) . '">'.__("Set your keyword", WTT_PLUGIN_NAME).'</a></div>');
                } else if (empty($pageSeoScore) && !empty($wttKeyword)) {
                    echo('<div class="wtt-score-text"><a href="' . get_edit_post_link($post_id) . '">'.__("Resave this page", WTT_PLUGIN_NAME).'</a></div>');
                } else {
                    echo $pageSeoScore ? $this->create_score_color('SEO', $pageSeoScore) : "";
                    echo $pageContentScore ? $this->create_score_color('CONTENT', round($pageContentScore['PageScore'])) : "";
                }

                break;

            case 'wttMetaDesc' :

                $post = get_post( $post_id, ARRAY_A );
                $wttDescription = $this->replacePatterns(get_post_meta($post_id, '_wtt_post_description', true), $post);

                if ( $wttDescription == '') {
                    echo '<span>&#8212;</span>';
                    return;
                }

                echo esc_html( $wttDescription );

                break;
            case 'wttMetaTitle' :
                $wttTitle = $this->get_page_title($post_id);

                if ( $wttTitle == '') {
                    echo '<span>&#8212;</span>';
                    return;
                }

                echo esc_html( $wttTitle );

                break;
            /* Just break out of the switch statement for everything else. */
            default :
                break;
        }
    }

    /**
     * Get the page title for the column.
     *
     * @param int $post_id Post to retrieve the title for.
     *
     * @return string
     */
    private function get_page_title( $post_id ) {
        $post = get_post( $post_id );

        $fixed_title = $this->getPostMeta( '_wtt_post_title', $post_id );
        if ( $fixed_title !== '' ) {
            return $this->replacePatterns($fixed_title, $post);
        }

        if ( is_object( $post ) && $this->get_wtt_title_from_options( 'title-' . $post->post_type, '' ) !== '' ) {
            $title_template = $this->get_wtt_title_from_options( 'title-' . $post->post_type );

            return $this->replacePatterns( $title_template, $post );
        }

        return $this->replacePatterns( '{{post_title}}', $post );
    }

    private function get_wtt_title_from_options($index, $object = array())
    {
        $wtt_settings = get_option("wtt_settings");

        if (!isset($wtt_settings[$index]) || $wtt_settings[$index] === '') {
            return '';
        }

        return $wtt_settings[$index];
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

        $post_meta = htmlspecialchars(get_post_meta($post_id ? $post_id : $post->ID, $meta, true), ENT_QUOTES);

        if (!empty($post_meta)) {
            return $post_meta;
        }

        return "";
    }

    /**
     * Hooks into admin_init, saves metabox settings for the first time
     *
     */
    public function update_wtt_settings()
    {
        if (!get_option('wtt_options')) {

            $post_type_names = get_post_types(array('public' => true), 'names');

            if ($post_type_names !== array()) {
                foreach ($post_type_names as $pt) {
                    $this->defaults['hidemetabox-' . $pt] = "off";
                }
                unset($pt);
            }

            update_option('wtt_options', $this->defaults);
        }

        if (!get_option('wtt_settings')) {
            $old_wtt_options = get_option('wtt_options');

            $this->upgrade_190($old_wtt_options);
        }
    }

    private function upgrade_190($old_options = null) {
        $post_type_names = get_post_types(array('public' => true), 'names');
        $taxonomy_type_names = get_taxonomies(array('public' => true), 'names');
        $post_custom_object_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');

        if ($post_type_names !== array()) {
            foreach ($post_type_names as $pt) {
                $this->updated_defaults['title-' . $pt] = '{{post_title}} {{page}} {{sep}} {{sitetitle}}';
                $this->updated_defaults['metadesc-' . $pt] = '';
                $this->updated_defaults['noindex-' . $pt] = false;
                $this->updated_defaults['hidemetabox-' . $pt] = $old_options ? $old_options['hidemetabox-' . $pt] : "off";
            }
            unset($pt);
        }

        if ($post_custom_object_types !== array()) {
            $archive = sprintf(__('%s Archive', 'webtexttool'), '{{pt_plural}}');
            foreach ($post_custom_object_types as $pt) {
                if (!$pt->has_archive) {
                    continue;
                }

                $this->updated_defaults['title-ptarchive-' . $pt->name] = $archive . ' {{page}} {{sep}} {{sitetitle}}';
                $this->updated_defaults['metadesc-ptarchive-' . $pt->name] = '';
                $this->updated_defaults['bctitle-ptarchive-' . $pt->name] = '';
                $this->updated_defaults['noindex-ptarchive-' . $pt->name] = false;
            }
            unset($pt);
        }

        if ($taxonomy_type_names !== array()) {
            $archives = sprintf(__('%s Archives', 'webtexttool'), '{{term_title}}');
            foreach ($taxonomy_type_names as $tax) {
                $this->updated_defaults['title-tax-' . $tax] = $archives . ' {{page}} {{sep}} {{sitetitle}}';
                $this->updated_defaults['metadesc-tax-' . $tax] = '';
                $this->updated_defaults['hidemetabox-tax-' . $tax] = "off";

                if ($tax !== 'post_format') {
                    $this->updated_defaults['noindex-tax-' . $tax] = false;
                } else {
                    $this->updated_defaults['noindex-tax-' . $tax] = true;
                }
            }
            unset($tax);
        }

        $merged_array = array_merge($this->defaults, $this->updated_defaults);

        update_option('wtt_settings', $merged_array);
    }

    /**
     * Hooks into admin_init, saves social options for the first time
     *
     */
    public function update_wtt_social_options()
    {
        $site_locale = get_locale();

        $options = array(
            'socialmetabox' => "on",
            'canonical_url' => "off",
            'show_meta_desc' => "on",
            'opengraph_image' => "",
            'og_image_use_specific' => "on",
            'og_image_use_featured' => "on",
            'og_image_use_default' => "on",
            'facebook-site' => "",
            'twitter-site' => "",
            'opengraph' => "on",
            'twitter' => "on",
            'twitter_card_type' => "summary_large_image",
            'wtt_og_locale' => $site_locale,
            'enable_seo_title' => "on"
        );

        if (!get_option('wtt_social')) {
            update_option('wtt_social', $options);
        }
    }

    public function replace_defaults() {
        $this->updated_defaults['title-search-wtt'] = __('You searched for', WTT_PLUGIN_NAME) . ' {{searchphrase}} {{page}} {{sep}} {{sitetitle}}';
        $this->updated_defaults['title-author-wtt'] = '{{post_author}}, ' . __('Author at', WTT_PLUGIN_NAME) . ' {{sitetitle}} {{page}}';
        $this->updated_defaults['title-404-wtt'] = __('Page not found', WTT_PLUGIN_NAME) . ' {{sep}} {{sitetitle}}';
    }

    /**
     * Creates the HTML and colors by the given values.
     *
     * @param $type string the type (i.e. SEO)
     * @param $pageScore string The page score.
     *
     * @return string The HTML for a score color.
     */
    private function create_score_color($type, $pageScore)
    {
        if ($pageScore < "35") {
            $cssClass = "bad";
        } else if ($pageScore >= "35" && $pageScore < "80") {
            $cssClass = "ok";
        } else {
            $cssClass = "good";
        }
        return '<div class="wtt-score-color ' . esc_attr($cssClass) . '">'.$type.': <span>' . esc_attr($pageScore) . '%</span>' . '</div>';
    }

    /**
     * Webtexttool nonce action
     *
     * @return string The nonce action
     */
    public function get_webtexttoolnonce_action()
    {
        return 'my_super_event';
    }

    /**
     * Saves the wtt access token in the WP User Meta table
     *
     */
    public function webtexttool_ajax()
    {
        $output = array("message" => 'server ajax failed'); // set default output message
        $action = $this->get_webtexttoolnonce_action(); // text used to generate or check the nonce.
        $option_key = "webtexttool_auth";
        // check if the nonce and data exist, otherwise exit
        if (array_key_exists('nonce', $_POST) && array_key_exists('data', $_POST)) {
            $nonce = htmlentities($_POST['nonce']);
            if (wp_verify_nonce($nonce, $action)) {
                $post_data = $_POST['data'];

                if(isset($post_data["apiKey"]) || !empty($post_data["apiKey"])) {
                    update_option('tm_api_key', $post_data['apiKey']);

                    $output['message'] = 'success';
                }

                if (isset($post_data["accessToken"]) || !empty($post_data["accessToken"])) {
                    $user_id = get_current_user_id();

                    // check if already exists, replaces update_user_meta
                    if (get_user_meta($user_id, $option_key, true) !== false) {
                        delete_user_meta($user_id, $option_key);
                    }

                    //add the user meta
                    add_user_meta($user_id, $option_key, $post_data['accessToken']);
                    $output['message'] = 'success';
                }
            }
        }

        header("Content-Type: application/json");
        echo json_encode($output);
        die();
    }

    /**
     * Caches the doctypes in the WP Option table
     * @since v3.3.0
     */
    public function tm_save_doctypes()
    {
        $output = ["message" => 'server ajax failed']; // set default output message
        $action = $this->get_webtexttoolnonce_action(); // text used to generate or check the nonce.
        // check if the nonce and data exist, otherwise exit
        if (array_key_exists('nonce', $_POST) && array_key_exists('data', $_POST)) {
            $nonce = htmlentities($_POST['nonce']);
            if (wp_verify_nonce($nonce, $action)) {
                $post_data = $_POST['data'];

                update_option('tm_doctypes', $post_data);
                $output['message'] = 'success';
            }
        }

        header("Content-Type: application/json");
        echo json_encode($output);
        die();
    }

    /**
     * Add plugin notices if other plugins are detected
     */
    public function wtt_plugin_notices()
    {
        $screen = get_current_screen();

        if (($screen->parent_base == 'edit' && $screen->base == 'post')) {

            wp_enqueue_script('wtt_custom_script', plugins_url('js/wtt-edit-page.min.js', __FILE__), array(), $this->version);

            if (is_wp_seo_active()) {
                ?>
                <div style="position: relative; padding-right: 38px;"
                     class="notice notice-warning wtt-plugin-notice is-dismissable">
                    <p>
                        <img style="margin-bottom: -2px;"
                             src="<?php echo plugin_dir_url(__FILE__) . 'images/tm_logo_16.png' ?>"
                             alt="webtexttool logo">
                        <strong>The Yoast SEO plugin has been detected.</strong> The meta description will not be used
                        in the header.
                        Do you want to <a href="<?php echo esc_url(admin_url('admin.php?page=wtt_tools')) ?>"
                                          target="_blank">import its SEO data?</a>
                    </p>
                    <button type="button" class="wtt-notice notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
                <?php
            }
        }
    }

    /**
     * Dismiss the plugin notice
     */
    function dismiss_wtt_plugin_notice()
    {
        $output = array("message" => 'server ajax failed'); // set default output message
        $action = $this->get_webtexttoolnonce_action(); // text used to generate or check the nonce.
        // check if the nonce and data exist, otherwise exit
        if (array_key_exists('nonce', $_POST) && array_key_exists('data', $_POST)) {
            $nonce = htmlentities($_POST['nonce']);
            if (wp_verify_nonce($nonce, $action)) {
                $code = $_POST['data'];

                update_option('wtt-plugin-notice-dismissed', $code);
                $output['message'] = 'success';
            }
        }

        header("Content-Type: application/json");
        echo json_encode($output);
        die();
    }

    /**
     * Adds a localized script to jQuery so it gets auto added to the page on Admin pages,
     * provides ajax url, ajax action, ajax nonce
     *
     */
    public function webtexttoolnonce()
    {
        $objectContent = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce($this->get_webtexttoolnonce_action()),
            'action' => 'webtexttool'
        );

        wp_register_script(strtolower(WTT_PLUGIN_NAME).'-admin-global', false);
        wp_enqueue_script(strtolower(WTT_PLUGIN_NAME).'-admin-global');

        wp_localize_script(strtolower(WTT_PLUGIN_NAME).'-admin-global', 'webtexttoolnonce', $objectContent);
    }

    /**
     * Localizes plugin globals for the dashboard
     */
    public function wtt_admin_plugin_data()
    {
        global $current_screen;

        if ($current_screen->base == 'toplevel_page_wtt_dashboard') {
            $objectContent = array(
                'pluginsUrl' => plugin_dir_url(__FILE__),
                'wtt_base_api_url' => WTT_BASE_API_URL,
                'authcode' => get_user_meta(get_current_user_id(), "webtexttool_auth", true),
                'apiKey' => get_option('tm_api_key'),
                'wtt_short_url' => WTT_SHORT_URL
            );

            wp_localize_script(strtolower(WTT_PLUGIN_NAME).'-admin-global', 'wtt_admin_globals', $objectContent);
        }
    }

    /**
     * Checks if the webtexttool seo metabox is hidden or not
     *
     * @param  string $post_type (optional) The post type to test
     *
     * @return  bool        True or false
     */
    function wtt_metabox_hide($post_type = null)
    {
        if (!isset($post_type) && (isset($GLOBALS['post']) && (is_object($GLOBALS['post']) && isset($GLOBALS['post']->post_type)))) {
            $post_type = $GLOBALS['post']->post_type;
        }

        if (isset($post_type)) {
            $post_types = get_post_types(array('public' => true), 'names');
            $options = get_option('wtt_settings');

            return ((isset($options['hidemetabox-' . $post_type]) && $options['hidemetabox-' . $post_type] === "on") || in_array($post_type, $post_types) === "off");
        }
        return false;
    }
}
