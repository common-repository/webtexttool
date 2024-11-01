<?php
/**
 * Textmetrics SEO plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Textmetrics
 * Plugin URI:        https://www.textmetrics.com
 * Description:       Textmetrics is the easiest way to create SEO proof content to rank higher and get more traffic. Realtime optimization, keyword research and more.
 * Version:           3.6.1
 * Author:            Textmetrics
 * Author URI:        https://www.textmetrics.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       webtexttool
 * Domain Path:       /languages
 */

define('WTT_VERSION', '3.6.1');
define('WTT_SHORT_URL', "api.textmetrics.com");
define('WTT_BASE_API_URL', 'https://api.textmetrics.com/');
define('WTT_PLUGIN_NAME', 'Textmetrics');

define('WTT_PATTERNS', json_encode(array(
    '{{sitetitle}}' => __("Display the site title (set in Settings > General)", WTT_PLUGIN_NAME),
    '{{tagline}}' => __("Display the site tagline (set in Settings > General)", WTT_PLUGIN_NAME),
    '{{post_title}}' => __("Displays the title of the post/page", WTT_PLUGIN_NAME),
    '{{post_excerpt}}' => __("Will display an excerpt from the post/page (if not customized, the excerpt will be auto-generated)", WTT_PLUGIN_NAME),
    '{{post_date}}' => __("Displays the publish date of the post/page", WTT_PLUGIN_NAME),
    '{{post_author}}' => __("Displays the author's nicename", WTT_PLUGIN_NAME),
    '{{post_category}}' => __("Adds the post category (several categories will be comma-separated)", WTT_PLUGIN_NAME),
    '{{post_tag}}' => __("Adds the current tag(s) to the post description", WTT_PLUGIN_NAME),
    '{{category_title}}' => __("Display the page title for category archive.", WTT_PLUGIN_NAME),
    '{{category_description}}' => __("Display the category description.", WTT_PLUGIN_NAME),
    '{{tag_title}}'  => __("Display the page title for tag post archive", WTT_PLUGIN_NAME),
    '{{tag_description}}'  => __("Display the page description for tag post archive", WTT_PLUGIN_NAME),
    '{{term_title}}'  => __("Display page title for taxonomy term archive", WTT_PLUGIN_NAME),
    '{{term_description}}'  => __("Display the tag description.", WTT_PLUGIN_NAME),
    '{{searchphrase}}'  => __("Displays the search phrase (if it appears in the post)", WTT_PLUGIN_NAME),
    '{{page}}'  => __("Displays the number of the current page (i.e. 1 of 6)", WTT_PLUGIN_NAME),
    '{{sep}}' => __("Places a separator between the elements", WTT_PLUGIN_NAME),
    '{{pt_plural}}'  => __("Display the plural post type archive name.", WTT_PLUGIN_NAME),
    '{{archive_date}}'  => __("Display the archive title based on the queried object.", WTT_PLUGIN_NAME),
    '{{wc_single_cat}}'  => __("Display a single product category.", WTT_PLUGIN_NAME),
    '{{wc_single_tag}}'  => __("Display a single product tag.", WTT_PLUGIN_NAME),
    '{{wc_single_short_desc}}'  => __("Display a single product short description.", WTT_PLUGIN_NAME),
)));

define('WTT_SEPARATORS', json_encode(array(
    'sc-dash' => '-',
    'sc-ndash' => '&ndash;',
    'sc-mdash' => '&mdash;',
    'sc-middot' => '&middot;',
    'sc-bull' => '&bull;',
    'sc-star' => '*',
    'sc-smstar' => '&#8902;',
    'sc-pipe' => '|',
    'sc-tilde' => '~',
    'sc-laquo' => '&laquo;',
    'sc-raquo' => '&raquo;',
    'sc-lt' => '&lt;',
    'sc-gt' => '&gt;',
)));

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The code that runs during plugin activation.
 */
function activate_webtexttool()
{

}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_webtexttool()
{

}

register_activation_hook(__FILE__, 'activate_webtexttool');
register_deactivation_hook(__FILE__, 'deactivate_webtexttool');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and core specific hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-webtexttool.php';
require plugin_dir_path(__FILE__) . 'admin/class-webtexttool-form.php';
require plugin_dir_path(__FILE__) . 'admin/class-webtexttool-replace-patterns.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_webtexttool()
{
    new Webtexttool(WTT_VERSION, WTT_PLUGIN_NAME);
}

run_webtexttool();
