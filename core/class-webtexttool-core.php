<?php

/**
 * The core functionality of the plugin.
 *
 * @link       http://webtexttool.com
 * @since      1.0.0
 *
 * @package    Webtexttool
 * @subpackage Webtexttool/core
 */
class Webtexttool_Core
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

    protected $fields = array();

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
     * Renders the seo meta box on selected post types
     *
     */
    public function add_wtt_meta_box()
    {
        $post_types = get_post_types(array('public' => true));

        if (is_array($post_types) && $post_types !== array()) {
            foreach ($post_types as $post_type) {
                if ($this->wtt_metabox_hide($post_type) === false) {

                    add_meta_box('postwebtexttool', WTT_PLUGIN_NAME, array(
                        $this,
                        'wtt_sidebar_meta_box',
                    ), $post_type, 'side', 'high');
                }
            }
        }
    }

    /**
     * Renders the wtt social meta box on selected post types
     *
     */
    public function add_wtt_social_meta_box()
    {
        $post_types = get_post_types(array('public' => true), 'names');
        $wtt_social = get_option('wtt_social');
        $scr = get_current_screen();

        if ($this->is_active()) {
            if (is_array($post_types) && $post_types !== array()) {
                if ($this->wtt_metabox_hide($scr->post_type) === false && in_array($scr->post_type, $post_types)) {
                    add_meta_box('postwebtexttool-social', WTT_PLUGIN_NAME . ' SEO Settings', array(
                        $this,
                        'wtt_social_meta_box',
                    ), $post_types, 'normal', 'low');
                }
            }
        }
        if ($wtt_social['socialmetabox'] == "on") {
            add_meta_box('postwebtexttool-social', WTT_PLUGIN_NAME . ' SEO Settings', array(
                $this,
                'wtt_social_meta_box',
            ), $post_types, 'normal', 'low');
        }
    }

    public function admin_init() {
        $taxonomies = get_taxonomies(array('public' => true), 'names');

        // Add SEO title and meta description field (to taxonomies)
        foreach ($taxonomies as $key => $value) {
            add_action($key.'_edit_form_fields', array($this, 'wtt_taxonomy_snippet_preview_fields'));
            add_action('edit_'.$key, array($this, 'wtt_tax_save_term'));
        }
    }

    private function get_tax_input_type() {
        if ( ! empty( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            return INPUT_POST;
        }

        return INPUT_GET;
    }

    private function wtt_metabox_hide_tax( $taxonomy = null ) {
        $get_tax_types = filter_input( $this->get_tax_input_type(), 'taxonomy' );

        if ( ! isset( $taxonomy ) && $get_tax_types ) {
            $taxonomy = sanitize_text_field( $get_tax_types );
        }

        if ( isset( $taxonomy ) ) {
            $tax_types = get_taxonomies( array( 'public' => true ), 'names' );
            $wtt_settings = get_option( 'wtt_settings' );

            return ( ( isset( $wtt_settings[ 'hidemetabox-tax-' . $taxonomy ] ) && $wtt_settings[ 'hidemetabox-tax-' . $taxonomy ] === "on" ) || in_array( $taxonomy, $tax_types, true ) === "off" );
        }

        return false;
    }

    /**
     * Checks if the webtexttool metabox is hidden or not
     *
     * @param   string $post_type (optional) The post type to test
     *
     * @return  bool
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

    /**
     * Render the HTML for the social metabox
     * @param $post
     */
    public function wtt_social_meta_box($post)
    {
        $current_id = get_the_id();
        $origin = 'post';
        $post_types = get_post_types(array('public' => true));
        $scr = get_current_screen();
        $wtt_social = get_option('wtt_social');
        $is_active = $this->is_active();

        wp_nonce_field('wttcallback', 'wttcontent');

        global $wp_scripts;
        $jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
        wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', [], $jquery_version );

        wp_register_script( 'jquery-tiptip', plugins_url('core/js/jquery-tiptip/jquery.tipTip.min.js', dirname(__FILE__)), [ 'jquery' ], '1.3', true );
        wp_register_script( 'jquery-validate', plugins_url('core/js/jquery.validate.min.js', dirname(__FILE__)), [ 'jquery' ], '1.19.0', true );

        wp_enqueue_style('wtt-social-metabox-stylesheet', plugins_url('core/css/wtt-social-metabox.min.css', dirname(__FILE__)), array(), WTT_VERSION, 'all');
        wp_enqueue_script('wtt-social-metabox-script', plugins_url('core/js/wtt-social-metabox.min.js', dirname(__FILE__)), array('jquery', 'jquery-ui-tabs', 'jquery-ui-datepicker', 'jquery-tiptip', 'jquery-validate'), WTT_VERSION, true);

        echo '<div id="wtt-tabs" data_id="' . $current_id . '" data_origin="' . $origin . '">';

        echo '<ul>';
        if (!$is_active) {
            echo '<li><a href="#tabs-2"><span class="dashicons dashicons-share"></span>' . __('Social', 'webtexttool') . '</a></li>';
            echo '<li><a href="#tabs-3"><span class="dashicons dashicons-megaphone"></span>' . __('Schema', 'webtexttool') . '</a></li>';
        } else {
            if($is_active) {
                if (is_array($post_types) && $post_types !== array()) {
                    if ($this->wtt_metabox_hide($scr->post_type) === false && in_array($scr->post_type, $post_types)) {
                        echo '<li><a href="#tabs-1"><span class="dashicons dashicons-edit"></span>' . __('Titles settings', 'webtexttool') . '</a></li>';
                    }
                }
            }
            if ($wtt_social['socialmetabox'] == "on") {
                echo '<li><a href="#tabs-2"><span class="dashicons dashicons-share"></span>' . __('Social', 'webtexttool') . '</a></li>';
                echo '<li><a href="#tabs-3"><span class="dashicons dashicons-megaphone"></span>' . __('Schema', 'webtexttool') . '</a></li>';
            }
        }
        echo '</ul>';

        if ($is_active) {
            if (is_array($post_types) && $post_types !== array()) {
                if ($this->wtt_metabox_hide($scr->post_type) === false && in_array($scr->post_type, $post_types)) {
                    echo '<div id="tabs-1" style="padding: 10px 0 10px 0;">';
                    $this->wtt_generate_snippet_preview($post);
                    echo '</div>';
                }
            }
        }

        if ($wtt_social['socialmetabox'] == "on") {
            require_once('partials/WTT_Social.php');
            $this->display_structured_data($post);
        }

        echo '<div><img class="textmetrics-logo" src="'. plugins_url("core/images/tm_logo.png", dirname(__FILE__)) .'" alt="textmetrics-logo"/></div>';
        echo '</div>';
    }

    /**
     * Displays label and checkbox input field.
     *
     * @param string $key
     * @param array  $field
     */
    public static function input_checkbox( $key, $field ) {
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="tm-form-field tm-form-field-checkbox">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?></label>
            <input type="checkbox" class="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $field['value'], 1 ); ?> />
            <?php if ( ! empty( $field['description'] ) ) : ?>
                <span class="tm_description"><?php echo wp_kses_post( $field['description'] ); ?></span>
            <?php endif; ?>
        </p>
        <?php
    }

    /**
     * Displays label and file input field.
     *
     * @param string $key
     * @param array  $field
     */
    public static function input_file( $key, $field ) {
        if ( empty( $field['placeholder'] ) ) {
            $field['placeholder'] = 'https://';
        }
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="tm-form-field">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>:
                <?php if ( ! empty( $field['description'] ) ) : ?>
                    <span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
                <?php endif; ?>
            </label>
            <span class="tm_file_url"><input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" /><button class="button button-small tm_upload_file_button" data-uploader_button_text="<?php esc_attr_e( 'Use file', WTT_PLUGIN_NAME ); ?>"><?php esc_html_e( 'Upload', WTT_PLUGIN_NAME ); ?></button></span>
        </p>
        <?php
    }

    /**
     * Displays label and text input field.
     *
     * @param string $key
     * @param array  $field
     */
    public static function input_text( $key, $field ) {
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        if ( ! empty( $field['classes'] ) ) {
            $classes = implode( ' ', is_array( $field['classes'] ) ? $field['classes'] : [ $field['classes'] ] );
        } else {
            $classes = '';
        }
        ?>
        <p class="tm-form-field">
            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>
                <?php if ( ! empty( $field['description'] ) ) : ?>
                    <span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
                <?php endif; ?>
            </label>
            <input type="text" autocomplete="off" name="<?php echo esc_attr( $name ); ?>" data-msg-regex="<?php echo !empty($field['attributes']) ? $field['attributes']['data-msg-regex'] : ''; ?>" data-validate-pattern="<?php echo !empty($field['attributes']) ? $field['attributes']['data-validate-pattern'] : ''; ?>" data-rule-regex="<?php echo !empty($field['attributes']) ? $field['attributes']['data-rule-regex'] : ''; ?>" class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
        </p>
        <?php
    }

    /**
     * Displays label and select input field.
     *
     * @param string $key
     * @param array  $field
     */
    public static function input_select( $key, $field ) {
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="tm-form-field">
            <label for="<?php echo esc_attr( $key ); ?>">
                <?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>:
                <?php if ( ! empty( $field['description'] ) ) : ?>
                    <span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
                <?php endif; ?>
            </label>
            <select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>">
                <?php foreach ( $field['options'] as $key => $value ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>"
                        <?php
                        if ( isset( $field['value'] ) ) {
                            selected( $field['value'], $key );
                        }
                        ?>
                    ><?php echo esc_html( $value ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    /**
     * Displays label and radio input field.
     *
     * @param string $key
     * @param array  $field
     */
    public static function input_radio( $key, $field ) {
        if ( ! empty( $field['name'] ) ) {
            $name = $field['name'];
        } else {
            $name = $key;
        }
        ?>
        <p class="tm-form-field tm-form-field-checkbox">
            <label><?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?></label>
            <?php foreach ( $field['options'] as $option_key => $value ) : ?>
                <label><input type="radio" class="radio" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( $field['value'], $option_key ); ?> /> <?php echo esc_html( $value ); ?></label>
            <?php endforeach; ?>
            <?php if ( ! empty( $field['description'] ) ) : ?>
                <span class="tm_description"><?php echo wp_kses_post( $field['description'] ); ?></span>
            <?php endif; ?>
        </p>

        <?php
    }

    /**
     * Displays metadata fields for posts/pages.
     *
     * @param int|WP_Post $post
     */
    public function display_structured_data( $post ) {
        global $post;

        $post_id = $post->ID;

        echo '<div id="tabs-3" style="padding: 10px 0 10px 0;">';

        $fields = Webtexttool_Social::set_schema_type_selection_fields();

        echo '<div class="tm_structured_meta_data">';
        foreach($fields as $key => $field) {
            $type2 = ! empty( $field['type'] ) ? $field['type'] : 'text';

            if ( ! isset( $field['value'] ) && metadata_exists( 'post', $post_id, '_tm_page_settings') ) {
                $metaValues = get_post_meta($post_id, "_tm_page_settings", true);
                $value = $metaValues[$key];

                $field['value'] = $value;
            }

            if ( method_exists( $this, 'input_' . $type2 ) ) {
                call_user_func( [ $this, 'input_' . $type2 ], $key, $field );
            }
        }
        echo '</div>';

        echo '<div class="tm_structured_meta_data" id="tm_job_schema_type">';

        foreach ( Webtexttool_Social::get_structured_data_fields() as $key => $field ) {

            $type = ! empty( $field['type'] ) ? $field['type'] : 'text';

            if ( ! isset( $field['value'] ) && metadata_exists( 'post', $post_id, '_tm_page_settings') ) {
                $metaValues = get_post_meta($post_id, "_tm_page_settings", true);
                $value = $metaValues[$key];

                $field['value'] = $value;
            }

            if ( ! isset( $field['value'] ) && isset( $field['default'] ) ) {
                $field['value'] = $field['default'];
            }

            if ( method_exists( $this, 'input_' . $type ) ) {
                call_user_func( [ $this, 'input_' . $type ], $key, $field );  // i.e. $TYPE = text so it calls function input_text (see above)
            }
        }

        echo '</div>';
        echo '</div>';
    }

    /**
     * Renders the core of the plugin and the hidden input fields
     *
     * @param array $post The post object
     */
    public function wtt_sidebar_meta_box($post)
    {
        include_once("partials/WTT_Core.php");

        ?>
        <script type="text/javascript">
            (function () {
                var existingWindowDotAngular = window.angular;
                var angular = (window.angular = {});

                <?php
                include dirname(__FILE__) . "/js/wtt-core.min.js";
                ?>

                angular.element(document).ready(function () {
                    angular.bootstrap(document.getElementById('wtt-dashboard'), ['wttDashboard']);
                    window.angular = existingWindowDotAngular;
                });
            })();
        </script>

        <?php
        wp_enqueue_style('wtt-fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), null, 'all');
        wp_enqueue_style('wtt-core', plugins_url('core/css/wtt-core.min.css', dirname(__FILE__)), array(), WTT_VERSION, 'all');

        if ($this->is_active()) {
            wp_enqueue_style('wtt-core-2', plugins_url('core/css/wtt-core-2.min.css', dirname(__FILE__)), array(), WTT_VERSION, 'all');
        }

        wp_enqueue_script( 'wtt-get-html-content', plugins_url('core/js/getHtmlContent.js', dirname(__FILE__)), array(), WTT_VERSION, true );
        wp_enqueue_script( 'wtt-observe-dom', plugins_url('core/js/observeDom.js', dirname(__FILE__)), array(), WTT_VERSION, true );
        wp_enqueue_script('wtt-mark', plugins_url('core/js/jquery.mark.min.js', dirname(__FILE__)), array('jquery'), WTT_VERSION, true);
        wp_enqueue_script( 'wtt-edit-page-controller', plugins_url('core/js/edit-page-controller.min.js', dirname(__FILE__)), array(), WTT_VERSION, true );

        wp_nonce_field('wttcallback', 'wttcontent');

        $languageCode = get_post_meta($post->ID, '_wtt_post_languageCode', true);
        $tags = get_post_meta($post->ID, '_wtt_post_synonyms', true);

        echo '<input type="hidden" id="wtt-language-code-field" name="wtt_language_code_field" value="' . sanitize_text_field($languageCode) . '"/>';

        echo '<ul id="wttSynonymTags" name="wttSynonymTags" style="list-style:none; margin:0;">';
        if (!empty($tags)) {
            foreach ($tags as $key => $n) : ?>
                <li><input type="hidden" id="wtt-synonym-tags" name="wtt_synonym_tags[]"
                           value="<?php echo esc_attr($n); ?>"/></li>
            <?php
            endforeach;
        }
        echo '</ul>';
    }

    public function wtt_taxonomy_snippet_preview_fields($term)
    {
        $wtt_settings = get_option('wtt_settings');
        $title = get_term_meta($term->term_id, '_wtt_term_title', true);
        $desc = get_term_meta($term->term_id, '_wtt_term_description', true);
        $placeholder = isset($wtt_settings['title-tax-' . $term->taxonomy]) ? $wtt_settings['title-tax-' . $term->taxonomy] : 'SEO Title';
        $default_title = $term ? $term->name . ' - ' . get_bloginfo('name') : ' - ' . get_bloginfo('name');
        $default_permalink = $term ? get_term_link($term) : get_bloginfo('url') . '/';

        if($this->wtt_metabox_hide_tax($term->taxonomy) === false) {
            wp_nonce_field('wttcallback', 'wttcontent');
            wp_enqueue_style('wtt-core-css', plugins_url('core/css/wtt-core.min.css', dirname(__FILE__)), WTT_VERSION);
            wp_enqueue_script('wtt-snippet-preview-editor-js', plugins_url('core/js/wtt-snippet-preview-editor.min.js', dirname(__FILE__)), array('jquery'), WTT_VERSION, true);

            ?>
            <tr id="term-wtt" class="form-field">
                <th scope="row">
                    <?php _e('Textmetrics SEO', 'webtexttool'); ?>
                </th>
                <td>
                    <div id="wtt_cpt">
                        <div class="wtt-inside">
                            <?php $this->generate_snippet_preview_template($title, $desc, $placeholder, $default_title, $default_permalink); ?>
                        </div>
                    </div>
                </td>
            </tr>
            <?php
        }
    }

    /**
     * Renders the SEO title and meta description field with the Google snippet preview
     *
     * @param array $post The current post type object
     */
    public function wtt_generate_snippet_preview($post)
    {
        $post_types = get_post_types(array('public' => true));
        $scr = get_current_screen();
        $wtt_settings = get_option('wtt_settings');

        if (is_array($post_types) && $post_types !== array()) {
            if ($this->wtt_metabox_hide($scr->post_type) === false && in_array($scr->post_type, $post_types)) {

                wp_enqueue_script('wtt-snippet-preview-editor-js', plugins_url('core/js/wtt-snippet-preview-editor.min.js', dirname(__FILE__)), array('jquery'), WTT_VERSION, true);

                $title = get_post_meta($post->ID, '_wtt_post_title', true);
                $description = get_post_meta($post->ID, '_wtt_post_description', true);
                $placeholder = isset($wtt_settings['title-' . $scr->post_type]) && $wtt_settings['title-' . $scr->post_type] !== '' ? $wtt_settings['title-' . $scr->post_type] : '';
                $default_title = get_the_title() ? get_the_title() . ' - ' . get_bloginfo('name') : ' - ' . get_bloginfo('name');
                $default_permalink = get_the_permalink() ? get_the_permalink() : get_bloginfo('url') . '/';
                ?>

                <div class="wtt-preview-wrapper" role="button" data-toggle="collapse"
                     data-target="#wtt_snippet_editor_form" aria-expanded="false"
                     aria-controls="wtt_snippet_editor_form">
                    <span class="wtt-arrow-down">
                        <i class="fa fa-angle-double-down"></i>
                    </span>
                </div>

                <?php

                $this->generate_snippet_preview_template($title, $description, $placeholder, $default_title, $default_permalink);
            }
        }
    }

    private function generate_snippet_preview_template($title, $description, $placeholder = null, $default_title = null, $default_permalink = null)
    {
        ?>
        <div id="<?php echo ($this->is_active() ? "wtt_snippet_editor_form_2" : "wtt_snippet_editor_form") ?>" class="collapse in">
            <div id="wtt_box_left">
                <div id="wtt_title_wrap">
                    <label for="wtt_title">
                        <span id="wtt_title_label">SEO title</span>
                        <button type="button" class="btn-info-d" data-toggle="popover" data-trigger="hover"
                                data-placement="right" data-content="This title is shown in the search results, so make sure it's a catchy phrase."><i
                                    class="fa fa-question-circle"></i></button>
                    </label>
                    <div id="wtt_title_collapse" class="">
                        <input id="wtt_title" placeholder="<?php echo $placeholder; ?>" name="wtt_post_title"
                               autocomplete="off"
                               spellcheck="true" value="<?php echo esc_attr($title); ?>">
                    </div>
                </div>

                <div id="wtt_desc_wrap">
                    <label for="wtt_description">
                        <span id="wtt_desc_label">Meta description</span>
                        <button type="button" class="btn-info-d" data-toggle="popover" data-trigger="hover"
                                data-placement="top"
                                data-content="This is the summary of your page that will be shown in the search results and what a potential visitor of your page will see. So it’s important to create a catchy description of your page.">
                            <i class="fa fa-question-circle"></i></button>
                    </label>
                    <textarea id="wtt_description" placeholder="Page Description"
                              name="wtt_post_description"><?php echo esc_textarea($description); ?></textarea>
                </div>
            </div>

            <div id="wtt_box_right">
                        <span class="wtt-pull-right wtt-close-icon" data-effect="fadeOut" role="button"
                              data-toggle="collapse" data-target="#wtt_snippet_editor_form" aria-expanded="false"
                              aria-controls="wtt_snippet_editor_form"><i class="fa fa-times"></i></span>

                <div id="wtt_snippet_preview">
                    <div class="google-snippet-preview">
                        <label for="wtt_google_snippet_preview">
                            <span class="wtt_snippet_label">Google Snippet Preview</span>
                            <button type="button" class="btn-info-d" data-toggle="popover" data-trigger="hover"
                                    data-placement="top"
                                    data-content="This is what your page will look like in Google search results.">
                                <i class="fa fa-question-circle"></i>
                            </button>
                        </label>
                        <p></p>
                        <?php
                        echo '<div class="wtt-snippet-title" id="wtt-snippet-title">' . $default_title . '</div>';
                        echo '<div class="wtt-snippet-permalink">' . $default_permalink . '</div>';
                        ?>
                        <div class="wtt-snippet-description" id="wtt-snippet-description"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Hooks into the save term function and saves webtexttool fields
     *
     * @param $term_id
     * @return mixed
     */
    public function wtt_tax_save_term($term_id)
    {
        // Check if our nonce is set.
        if (!isset($_POST['wttcontent'])) {
            return $term_id;
        }

        $nonce = $_POST['wttcontent'];

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce, 'wttcallback')) {
            return $term_id;
        }

        $title = wp_strip_all_tags($_POST['wtt_post_title']);
        $description = stripslashes(str_replace(array("\r\n", "\r", "\n"), '', $_POST['wtt_post_description']));

        if (isset($_POST['wtt_post_title']) && ($_POST['wtt_post_title']) <> "") {
            update_term_meta($term_id, '_wtt_term_title', $title);
        } else {
            delete_term_meta($term_id, '_wtt_term_title', $title);
        }

        if (isset($_POST['wtt_post_description']) ) {
            update_term_meta($term_id, '_wtt_term_description', $description);
        } else {
            delete_term_meta($term_id, '_wtt_term_description', $description);
        }

        return $term_id;
    }

    /**
     * Hooks into the save post function and saves webtexttool fields
     *
     * @param $post_id
     * @return mixed
     */
    function wtt_save_postdata($post_id)
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
        $keyword = stripslashes($_POST['wtt_keyword_field']);
        $title = wp_strip_all_tags($_POST['wtt_post_title']);
        $description = stripslashes(str_replace(array("\r\n", "\r", "\n"), '', $_POST['wtt_post_description']));
        $languageCode = $_POST['wtt_language_code_field'];
        $pageScore = str_replace("%", '', $_POST['wtt_page_score_field']);
        $synonyms = isset($_POST['wtt_synonym_tags']) ? $_POST['wtt_synonym_tags'] : '';

        // Update the meta field in the database.
        if (isset($_POST['wtt_keyword_field']) && ($_POST['wtt_keyword_field']) <> "") {
            update_post_meta($post_id, '_wtt_post_keyword', $keyword);
        } else {
            delete_post_meta($post_id, '_wtt_post_keyword', $keyword);
        }

        if (isset($_POST['wtt_post_title']) && ($_POST['wtt_post_title']) <> "") {
            update_post_meta($post_id, '_wtt_post_title', $title);
        } else {
            delete_post_meta($post_id, '_wtt_post_title', $title);
        }

        if (isset($_POST['wtt_post_description']) && ($_POST['wtt_post_description']) <> "") {
            update_post_meta($post_id, '_wtt_post_description', $description);
        } else {
            delete_post_meta($post_id, '_wtt_post_description', $description);
        }

        if (isset($_POST['wtt_page_score_field']) && ($_POST['wtt_page_score_field']) <> "") {
            update_post_meta($post_id, '_wtt_page_score', $pageScore);
        } else {
            delete_post_meta($post_id, '_wtt_page_score', $pageScore);
        }

        if (isset($_POST['wtt_language_code_field']) && ($_POST['wtt_language_code_field']) <> "") {
            update_post_meta($post_id, '_wtt_post_languageCode', $languageCode);
        } else {
            delete_post_meta($post_id, '_wtt_post_languageCode', $languageCode);
        }

        if (isset($_POST['wtt_synonym_tags']) && ($_POST['wtt_synonym_tags']) <> "") {
            update_post_meta($post_id, '_wtt_post_synonyms', $synonyms);
        } else {
            delete_post_meta($post_id, '_wtt_post_synonyms', $synonyms);
        }

        return $post_id;
    }

    /**
     * Webtexttool nonce action string to compare the ajax calls
     *
     * @return string
     */
    public function get_webtexttoolnonce_action()
    {
        return 'my_super_event';
    }

    /**
     * Saves the page settings in the WP Post Meta table
     *
     */
    public function wtt_process_ajax()
    {
        $output = array("message" => 'process server ajax failed'); // set default output message
        $action = $this->get_webtexttoolnonce_action(); // text used to generate or check the nonce.

        // check if the nonce and data exist, otherwise exit
        if (array_key_exists('nonce', $_POST) && array_key_exists('data', $_POST) && array_key_exists('postId', $_POST)) {
            $nonce = htmlentities($_POST['nonce']);
            if (wp_verify_nonce($nonce, $action)) {
                $data = $_POST['data'];
                $pageid = $_POST['postId'];

                $option_key = $data['option'];
                $option_value = $data['value'];

                // check if already exists, replaces update_post_meta
                if (get_post_meta($pageid, $option_key, true) !== false) {
                    $output['message'] = 'success delete';
                    delete_post_meta($pageid, $option_key);
                }

                //add the post meta
                add_post_meta($pageid, $option_key, $option_value);
                $output['message'] = 'success';
            }
        }

        header("Content-Type: application/json");
        echo json_encode($output);
        die();
    }

    /**
     * Ajax save call for content quality suggestions
     */
    public function wtt_save_content_quality_suggestions() {
        $output = array("message" => 'server ajax failed'); // set default output message
        $action = $this->get_webtexttoolnonce_action(); // text used to generate or check the nonce.
        $option_key = "wtt_content_quality_suggestions";

        // check if the nonce and data exist, otherwise exit
        if (array_key_exists('nonce', $_POST) && array_key_exists('data', $_POST) && array_key_exists('postId', $_POST)) {
            $nonce = htmlentities($_POST['nonce']);
            if (wp_verify_nonce($nonce, $action)) {
                $data = $_POST['data'];
                $pageid = $_POST['postId'];

                // check if already exists, replaces update_post_meta
                if (get_post_meta($pageid, $option_key, true) !== false) {
                    $output['message'] = 'success';
                    delete_post_meta($pageid, $option_key);
                }

                //add the post meta
                add_post_meta($pageid, $option_key, $data);
                $output['message'] = 'success';
            }
        }

        header("Content-Type: application/json");
        echo json_encode($output);
        die();
    }

    /**
     * Ajax save call for content quality settings
     */
    public function wtt_save_content_quality_settings() {
        $output = array("message" => 'server ajax failed'); // set default output message
        $action = $this->get_webtexttoolnonce_action(); // text used to generate or check the nonce.
        $option_key = "wtt_content_quality_settings";

        // check if the nonce and data exist, otherwise exit
        if (array_key_exists('nonce', $_POST) && array_key_exists('data', $_POST) && array_key_exists('postId', $_POST)) {
            $nonce = htmlentities($_POST['nonce']);
            if (wp_verify_nonce($nonce, $action)) {
                $data = $_POST['data'];
                $pageid = $_POST['postId'];

                // check if already exists, replaces update_post_meta
                if (get_post_meta($pageid, $option_key, true) !== false) {
                    $output['message'] = $data;
                    delete_post_meta($pageid, $option_key);
                }

                //add the post meta
                add_post_meta($pageid, $option_key, $data);
                $output['message'] = $data;
            }
        }

        header("Content-Type: application/json");
        echo json_encode($output);
        die();
    }

    /**
     * Converts Divi shortcodes from the editor content to html content
     */
    public function wtt_process_divi_shortcodes()
    {
        if (!wp_verify_nonce($_POST['nonce'], $this->get_webtexttoolnonce_action())) {
            die(-1);
        }

        $unprocessed_data = str_replace('\\', '', $_POST['unprocessed_data']);

        echo do_shortcode($unprocessed_data);

        die();
    }

    /**
     * Converts shortcodes for different plugins
     *
     */
    public function wtt_process_shortcodes()
    {
        if (!wp_verify_nonce($_POST['nonce'], $this->get_webtexttoolnonce_action())) {
            die(-1);
        }

        $shortcodes = filter_input(INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        $parsed_shortcodes = array();

        foreach ($shortcodes as $shortcode) {
            $parsed_shortcodes[] = array(
                'shortcode' => $shortcode,
                'output' => do_shortcode($shortcode),
            );
        }

        wp_die(wp_json_encode($parsed_shortcodes));
    }

    /**
     * Returns the TCB-saved post content, stripped of tags
     *
     * @return void
     */
    public function wtt_tve_editor_content()
    {
        $id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);

        global $post;
        $post = get_post($id);

        ob_start();
        $all_content = tve_editor_content($post->post_content, 'tcb_content');
        ob_end_clean();

        wp_send_json(array(
            'post_id' => $post->ID,
            'content' => $all_content,
        ));
    }

    /**
     * Parses dynamic blocks out of `post_content` and re-renders them.
     *
     * @since 3.0.2
     *
     * @return string Rendered post content.
     */
    public function wtt_do_blocks() {
        if (!wp_verify_nonce($_POST['nonce'], $this->get_webtexttoolnonce_action())) {
            die(-1);
        }

        $content = $_POST['data'];

        // If there are blocks in this content, we shouldn't run wpautop() on it later.
        $priority = has_filter( 'the_content', 'wpautop' );
        if ( false !== $priority && doing_filter( 'the_content' ) && has_blocks( $content ) ) {
            remove_filter( 'the_content', 'wpautop', $priority );
            add_filter( 'the_content', '_restore_wpautop_hook', $priority + 1 );
        }

        $blocks = parse_blocks( $content );
        $output = '';

        foreach ( $blocks as $block ) {
            $output .= render_block( $block );
        }

        //wp_die($output);
        wp_send_json(array(
            'content' => $output,
        ));
    }

    /**
     * Ajax search for all the posts and returns the post data (label, link and type)
     *
     */
    public function wtt_ajax_search_posts()
    {
        $term = strtolower($_GET['term']);
        $suggestions = array();

        $loop = new WP_Query('s=' . $term);

        while ($loop->have_posts()) {
            $loop->the_post();
            $suggestion = array();
            $suggestion['label'] = get_the_title();
            $suggestion['link'] = get_permalink();
            $suggestion['type'] = get_post_type();

            $suggestions[] = $suggestion;
        }

        wp_reset_query();

        $response = json_encode($suggestions);
        echo $response;
        exit();
    }

    /**
     * Simple templating function
     *
     * @param $template - Name of the PHP file that acts as a template
     * @return string - Output of the template file.
     */
    private function render_php($template)
    {
        ob_start();
        include(dirname(__FILE__) . '/partials/directives/' . $template);
        $var = ob_get_contents();
        ob_end_clean();
        return $var;
    }

    /**
     * Localizes plugin globals for the edit post page
     */
    public function wtt_core_plugin_data()
    {
        global $post, $current_screen;
        $wtt_settings = get_option('wtt_settings');
        wp_register_script( strtolower(WTT_PLUGIN_NAME) . '-post-edit', false);
        wp_enqueue_script(strtolower(WTT_PLUGIN_NAME) .'-post-edit');

        if ($current_screen->parent_base == 'edit' || $current_screen->base == 'post') {
            $objectContent = array(
                'wttContentQuality' => $this->render_php('wtt-content-quality.php'),
                'wttSuggestionsCategory' => $this->render_php('wtt-suggestions-category.php'),
                'wttPageSlideout' => $this->render_php('wtt-page-slideout.php'),
                'suggestJob' => $this->render_php('suggest-job.html'),
                'contentGenerator' => $this->render_php('content-generator.html'),
                'DocumentTypeTemplates' => get_option('tm_doctypes'),
                'fusion_content' => get_post_meta($post->ID, 'fusion_builder_status', true) === 'active',
                'beaver_builder' => get_post_meta($post->ID, '_fl_builder_enabled', true),
                'oxygen_content' => $this->is_oxygen_active() ? do_shortcode(get_post_meta($post->ID, 'ct_builder_shortcodes', true)) : "",
                'divi_content' => $this->is_divi_active() ? do_shortcode($post->post_content) : "",
                'siteUrl' => get_home_url(),
                'pluginsUrl' => plugin_dir_url(__FILE__),
                'postId' => $post->ID,
                'wtt_base_api_url' => WTT_BASE_API_URL,
                'authcode' => get_user_meta(get_current_user_id(), "webtexttool_auth", true),
                'apiKey' => get_option('tm_api_key'),
                'permalink' => esc_url(get_permalink($post->ID)),
                'processPageTitleAsH1' => get_post_meta($post->ID, 'wtt_process_page_title', true),
                'ruleSet' => get_post_meta($post->ID, 'tm_ruleset', true),
                'jobTitleData' => get_post_meta($post->ID, 'tm_job_title_data', true),
                'pageScore' => get_post_meta($post->ID, '_wtt_page_score', true),
                'wtt_short_url' => WTT_SHORT_URL,
                'fieldSelectorsACF' => $this->get_acf_field_selectors(),
                'blacklistTypeACF' => $this->get_acf_blacklist_type(),
                'wtt_shortcode_tags' => $this->get_shortcode_tags(),
                'vc_enabled' => get_post_meta($post->ID, '_wpb_vc_js_status', true) === 'true',
                'avia_enabled' => get_post_meta($post->ID, '_aviaLayoutBuilder_active', true) === 'active',
                'tcb_editor_enabled' => (int)get_post_meta($post->ID, 'tcb_editor_enabled', true) === 1,
                'tcb_editor_disabled' => (int)get_post_meta($post->ID, 'tcb_editor_disabled', true) === 1,
                'acfVersion' => get_option('acf_version'),
                'acfOptionEnabled' => isset($wtt_settings['enable-acf']) && $wtt_settings['enable-acf'] === "on",
                'rwmbEnabled' => isset($wtt_settings['enable-rwmb']) && $wtt_settings['enable-rwmb'] === "on",
                'getLastSuggestions' => get_post_meta($post->ID, 'wtt_content_quality_suggestions', true),
                'getCQSettings' => get_post_meta($post->ID, 'wtt_content_quality_settings', true),
                'rwmbFields' => $this->fields,
                'current_screen' => $current_screen->base,
                'validationl10n' =>
                [
                    'regexErrorDefault'    => __( 'Please use the correct format.', WTT_PLUGIN_NAME ),
                    'requiredErrorDefault' => __( 'This field is required.', WTT_PLUGIN_NAME ),
                    'emailErrorDefault'    => __( 'Please enter a valid email address.', WTT_PLUGIN_NAME ),
                    'urlErrorDefault'      => __( 'Please enter a valid URL.', WTT_PLUGIN_NAME ),
                ]
            );

            wp_localize_script(strtolower(WTT_PLUGIN_NAME) .'-post-edit', 'wtt_globals', $objectContent);
        }
    }

    /**
     * Check if oxygen page builder is active
     *
     * @return bool
     */
    private function is_oxygen_active() {
        if (defined('CT_VERSION')) {
            return true;
        }
        return false;
    }

    /**
     * Determines if Divi builder is active
     *
     * @return bool
     */
    private function is_divi_active() {
        if (defined('ET_BUILDER_VERSION')) {
            return true;
        }
        return false;
    }

    public function wtt_enqueue_core_data()
    {
        global $post, $tag, $current_screen;
        $term_id  = filter_input( INPUT_GET, 'tag_ID' );
        $getTaxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_DEFAULT, array( 'options' => array( 'default' => '' ) ));
        $term     = get_term_by( 'id', $term_id, $getTaxonomy );

        $objectContent = array();

        if ($current_screen->parent_base == 'edit' || $current_screen->base == 'post') {
            $objectContent = array(
                'title_template' => $this->get_wtt_settings_template('title-' . $post->post_type),
//                'metadesc_template' => $this->get_wtt_settings_template('metadesc-'.$post->post_type),
                'replace_vars' => $this->get_replacement_vars(),
            );
        }
        elseif (is_object( $term ) && property_exists( $term, 'taxonomy' )) {
            $objectContent = array(
                'title_template' => $this->get_wtt_settings_template('title-tax-' . $tag->taxonomy),
//                'metadesc_template' => $this->get_wtt_settings_template('metadesc-tax-'. $tag->taxonomy),
                'replace_vars' => $this->get_tax_replacement_vars(),
            );
        }
        wp_localize_script(strtolower(WTT_PLUGIN_NAME) .'-post-edit', 'wttReplaceVars', $objectContent);
    }

    protected function get_wtt_term_taxonomies( $post_id = null ) {

        if ( null === $post_id ) {
            $post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
        }

        $post_type      = get_post_type( $post_id );
        $all_taxonomies = get_object_taxonomies( $post_type, 'objects' );

        return $all_taxonomies;
    }

    private function get_wtt_settings_template($index)
    {
        $wtt_settings = get_option('wtt_settings');
        $template = '';

        if (isset($wtt_settings[$index]) && $wtt_settings[$index] !== '') {
            $template = $wtt_settings[$index];
        }

        return $template;
    }


    private function get_replacement_vars()
    {
        global $post;

        $replaced_vars = array();

        $default_vars = array(
            'post_title',
            'post_date',
            'sitetitle',
            'tagline',
            'sep',
            'page',
            'post_author',
            'post_tag'
        );

        foreach ($default_vars as $var) {
            $replaced_vars[$var] = $this->replacePatterns('{{' . $var . '}}', $post);
        }

        return $replaced_vars;
    }

    private function get_tax_replacement_vars()
    {
        global $tag;

        $replaced_vars = array();

        $default_vars = array(
            'post_date',
            'sitetitle',
            'tagline',
            'sep',
            'page',
            'term_title',
            'term_description',
            'category_description',
            'tag_description'
        );

        foreach ($default_vars as $var) {
            $replaced_vars[$var] = $this->replacePatterns('{{' . $var . '}}', $tag);
        }

        return $replaced_vars;
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

    private function get_shortcode_tags()
    {
        $shortcode_tags = array();

        foreach ($GLOBALS['shortcode_tags'] as $tag => $description) {
            array_push($shortcode_tags, $tag);
        }

        return $shortcode_tags;
    }


    private function get_acf_field_selectors()
    {
        if (class_exists('acf')) {
            return array(
                ".acf-taxonomy-field",
                "input[type=email][id^=acf]",
                "input[type=hidden].acf-image-value",
                "input[type=text][id^=acf]",
                "input[type=url][id^=acf]",
                "textarea[id^=acf]",
                "textarea[id^=wysiwyg-acf]"
            );
        }
        return "";
    }

    private function get_acf_blacklist_type()
    {
        if (class_exists('acf')) {
            return array(
                'number',
                'password',
                'file',
                'select',
                'checkbox',
                'radio',
                'true_false',
                'post_object',
                'page_link',
                'relationship',
                'user',
                'date_picker',
                'color_picker',
                'message',
                'tab',
                'repeater',
                'flexible_content',
                'group',
            );
        }
        return "";
    }

    public function enqueueRWMBFields(RW_Meta_Box $meta_box)
    {
        // Only for posts.
        $screen = get_current_screen();
        if ('post' !== $screen->base) {
            return;
        }

        // Get all field IDs that adds content.
        $this->add_fields($meta_box->fields);

        if (empty($this->fields)) {
            return;
        }

        // Send list of fields to fields variable.
        return $this->fields;
    }

    protected function add_fields($fields)
    {
        array_walk($fields, array($this, 'add_field'));
    }

    protected function add_field($field)
    {
        // Add sub-fields recursively.
        if (isset($field['fields'])) {
            $this->add_fields($field['fields']);
        }

        // Add the single field.
        if ($this->is_analyzable($field)) {
            $this->fields[] = $field['id'];
        }
    }

    protected function is_analyzable($field)
    {
        return !in_array($field['id'], $this->fields, true);
    }

    /**
     * Check if Block Editor is active.
     *
     * @return bool
     */
    private function is_active() {
        global $post;

        // Gutenberg plugin is installed and activated.
        $gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

        // Block editor since 5.0.
        $block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

        if ( !$gutenberg && !$block_editor ) {
            return false;
        }

        $old_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '<' );

        // support for older WP versions (< 5.0) with gutenberg plugin enabled
        if ( $old_editor ) {
            if(function_exists('is_gutenberg_page') && is_gutenberg_page()) {
                return true;
            }
            return false;
        }

        if ( $this->is_classic_editor_plugin_active() ) {
            $editor_option       = get_option( 'classic-editor-replace' );
            $block_editor_active = array( 'no-replace', 'block' );

            $allow_users = ( get_option( 'classic-editor-allow-users' ) === 'allow' );
            $which_editor = get_post_meta( isset($post) ? $post->ID : "", 'classic-editor-remember', true );

            // If allow users is enabled, check which editor is selected (overwrites existing options)
            if($allow_users) {
                if(!empty($which_editor)) {
                    // The editor choice will be "remembered" when the post is opened in either Classic or Block editor.
                    if ( 'classic-editor' === $which_editor ) {
                        return false;
                    } elseif ( 'block-editor' === $which_editor ) {
                        return true;
                    }
                }
            }

            return in_array( $editor_option, $block_editor_active, true );
        }

        $current_screen = get_current_screen();

        //fallback
        if ( isset( $_GET['classic-editor']) || (method_exists( $current_screen, 'is_block_editor' ) && !$current_screen->is_block_editor)) {
            return false;
        }

        return true;
    }

    /**
     * Check if Classic Editor plugin is active.
     *
     * @return bool
     */
    private function is_classic_editor_plugin_active() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
            return true;
        }

        return false;
    }
}