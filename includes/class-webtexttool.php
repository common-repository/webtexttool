<?php

/**
 * The core plugin class.
 *
 * Maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Webtexttool
 * @subpackage Webtexttool/includes
 */
class Webtexttool
{

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the core side of the plugin.
     *
     * @since    1.0.0
     * @param $version
     * @param $plugin_name
     */
    public function __construct($version, $plugin_name)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->load_dependencies();
        $this->set_locale();
        $this->call_global_hooks();
        $this->define_admin_hooks();
        $this->define_core_hooks();
        $this->define_social_hooks();

        add_action( 'after_setup_theme', [ $this, 'include_template_functions' ], 11 );
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Webtexttool_i18n. Defines internationalization functionality.
     * - Webtexttool_Admin. Defines all hooks for the admin area.
     * - Webtexttool_Core. Defines all hooks for the core side of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-webtexttool-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-webtexttool-admin.php';

        /**
         * The class responsible for defining all actions that occur in the core of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'core/class-webtexttool-core.php';

        /**
         * The class responsible for rendering the social meta on the front-end
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'core/class-webtexttool-social.php';
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Webtexttool_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Webtexttool_i18n();
        $plugin_i18n->set_domain($this->get_plugin_name());

        add_action('plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain'));

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Webtexttool_Admin($this->get_plugin_name(), $this->get_version());

        add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'), 5);

        add_action('wp_ajax_webtexttool', array($plugin_admin, 'webtexttool_ajax'));
        add_action('wp_ajax_webtexttool_doctypes', array($plugin_admin, 'tm_save_doctypes'));

        add_action('admin_enqueue_scripts', array($plugin_admin, 'webtexttoolnonce'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'wtt_admin_plugin_data'));

        add_action('admin_init', array($plugin_admin, 'admin_init'));

        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_custom_wtt_css'));

        add_action('init', array($plugin_admin, 'update_wtt_settings'), 99);
        add_action('init', array($plugin_admin, 'update_wtt_social_options'), 98);

        if ( method_exists( $plugin_admin, 'replace_defaults' ) ) {
            add_action( 'init', array( $plugin_admin, 'replace_defaults' ), 2 );
        }

        add_action('admin_init', array($plugin_admin, 'wtt_register_settings'));
        add_action('admin_init', array($plugin_admin, 'wtt_register_social_settings'));

        if (!get_option("wtt-plugin-notice-dismissed")) {
            add_action('admin_notices', array($plugin_admin, 'wtt_plugin_notices'));
        }

        add_action('wp_ajax_webtexttool_dismiss_wtt_notice', array($plugin_admin, 'dismiss_wtt_plugin_notice'));
    }

    /**
     * Register all of the hooks related to the core of the plugin
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_core_hooks()
    {

        $plugin_core = new Webtexttool_Core($this->get_plugin_name(), $this->get_version());

        add_action('admin_enqueue_scripts', array($plugin_core, 'wtt_core_plugin_data'));
        add_action('admin_enqueue_scripts', array($plugin_core, 'wtt_enqueue_core_data'), 10);

        // Add meta boxes
        add_action('add_meta_boxes', array($plugin_core, 'add_wtt_meta_box'));
        add_action('add_meta_boxes', array($plugin_core, 'add_wtt_social_meta_box'));

        // Add SEO title and meta description field (after title)
        add_action('edit_form_after_title', array($plugin_core, 'wtt_generate_snippet_preview'));

        //after plugin has loaded, add SEO title and meta description field (to taxonomies)
        add_action('admin_init', array($plugin_core, 'admin_init'));

        // Save post data
        add_action('save_post', array($plugin_core, 'wtt_save_postdata'));
        add_action('edit_attachment', array( $plugin_core, 'wtt_save_postdata'));
        add_action('add_attachment', array( $plugin_core, 'wtt_save_postdata'));

        // Ajax
        add_action('wp_ajax_webtexttool_save_page_data', array($plugin_core, 'wtt_process_ajax'));
        add_action('wp_ajax_webtexttool_convert_divi_shortcodes', array($plugin_core, 'wtt_process_divi_shortcodes'));
        add_action('wp_ajax_webtexttool_search_posts', array($plugin_core, 'wtt_ajax_search_posts'));
        add_action('wp_ajax_webtexttool_convert_shortcodes', array($plugin_core, 'wtt_process_shortcodes'));
        add_action('wp_ajax_webtexttool_do_blocks', array($plugin_core, 'wtt_do_blocks'));
        add_action('wp_ajax_webtexttool_tve_editor_content', array($plugin_core, 'wtt_tve_editor_content'));
        add_action('wp_ajax_webtexttool_content_quality_suggestions', array($plugin_core, 'wtt_save_content_quality_suggestions'));
        add_action('wp_ajax_webtexttool_content_quality_settings', array($plugin_core, 'wtt_save_content_quality_settings'));
        add_action('rwmb_enqueue_scripts', array( $plugin_core, 'enqueueRWMBFields'));
    }

    /**
     * Register all of the hooks related to the social meta of the plugin
     *
     * @since    1.3.0
     * @access   private
     */
    private function define_social_hooks()
    {

        $plugin_social = new Webtexttool_Social($this->get_plugin_name(), $this->get_version());
        $wtt_social = get_option('wtt_social');

        add_action('wp_head', array($plugin_social, 'set_header_meta'), 1);

        // Fix for WP 4.4
        if (isset($wtt_social['enable_seo_title']) && $wtt_social['enable_seo_title'] == 'on') {
            add_filter('pre_get_document_title', array($plugin_social, 'wtt_set_seo_title'), 16);
            add_filter('wp_title', array($plugin_social, 'wtt_set_seo_title'), 16, 3);
        }

        if(isset($wtt_social['socialmetabox']) && $wtt_social['socialmetabox'] == 'on'){
            add_action('save_post', array($plugin_social, 'wtt_save_social_meta'));

            add_action( 'save_post', array($plugin_social, 'tm_save_post' ), 1, 2 );
            add_action( 'tm_save_structured_data', array($plugin_social, 'save_structured_data'), 20, 1 );
        }
    }

    /**
     * Loads plugin's core helper template functions.
     */
    public function include_template_functions() {
        include_once plugin_dir_path(__FILE__) . '/webtexttool-functions.php';
        include_once plugin_dir_path(__FILE__) . '/webtexttool-template.php';
    }

    private function call_global_hooks() {
        add_action('plugins_loaded', array($this, 'update_db_check'));
    }

    /**
     * Update database
     */
    public function update_db_check() {
        $upgrade = false;

        if ( !$v = get_option('wtt_version') ) {

            if ( $this->version <= "1.8.2" ) {

            } else {
                //A new install - set the default data on the database
                $upgrade = true;
            }
        } else {
            if ( $v < $this->version ) {

                //Any version upgrade
                $upgrade = true;
                //Any upgrades right here

            }
        }

        //Set version on database
        if ($upgrade) {
            update_option( 'wtt_version', $this->version );
        }
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}
