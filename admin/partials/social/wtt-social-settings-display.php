<link rel="stylesheet"
      href="<?php echo plugins_url('../css/wtt-social-page.min.css', dirname(__FILE__)) . "?ver=" . WTT_VERSION; ?>">
<link rel="stylesheet"
      href="<?php echo plugins_url('../css/wtt-admin.min.css', dirname(__FILE__)) . "?ver=" . WTT_VERSION; ?>">
<script type="text/javascript"
        src="<?php echo plugins_url('../js/wtt-settings.min.js', dirname(__FILE__)) . "?ver=" . WTT_VERSION; ?>"></script>

<?php
wp_enqueue_media();
$wttform = WTT_Form::get_instance();
$wtt_social = get_option('wtt_social');
echo '<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("#wtt_social_language_option").find("select#wtt_og_locale").val("' . $wtt_social['wtt_og_locale'] . '");
    });
</script>';
?>

<div id="wtt-settings" class="wrap">

    <div id="poststuff">

        <div id="post-body" class="metabox-holder columns-2">

            <div id="post-body-content">

                <div class="postbox">

                    <h3 class="postbox-title">
                        <span><?php echo __(WTT_PLUGIN_NAME); ?> Social</span>
                        <img class="webtexttool-logo" width="150"
                             src="<?php echo plugins_url('../images/tm_logo.png', dirname(__FILE__)); ?>"
                             alt="textmetrics-logo"/>
                    </h3>

                    <div class="inside">

                        <form id="wtt_settings_form" name="settings"
                              action="<?php echo esc_url(admin_url('options.php')) ?>" method="post">

                            <div id="wtt_settings_body">
                                <div>
                                    <div id="wtt_social_general_settings" class="wtt_withborder">
                                        <h3 style="font-weight: bold;">General SEO settings</h3>
                                        <p class="description">You can enable or disable the <?php echo __(WTT_PLUGIN_NAME); ?> SEO settings metabox on the post pages.</p>
                                        <?php
                                        include_once('wtt-social-general-settings.php');
                                        ?>
                                    </div>

                                    <div id="wtt_social_image_settings" class="wtt_withborder">
                                        <h3 style="font-weight: bold;">Image settings</h3>
                                        <p class="description">A URL (with http(s)://) to an image. The recommended
                                            resolution for an image is 1200 pixels x 627 pixels.</p>
                                        <?php
                                        include_once('wtt-social-image-settings.php');
                                        ?>
                                    </div>

                                    <div id="wtt_social_media_accounts" class="wtt_withborder">
                                        <h3 style="font-weight: bold;">Social media accounts</h3>
                                        <p class="description">Facebook page url and Twitter username (without @).</p>
                                        <?php
                                        include_once('wtt-social-accounts.php');
                                        ?>
                                    </div>

                                    <div id="wtt_social_media_settings" class="wtt_withborder">
                                        <h3 style="font-weight: bold;">Social media options</h3>
                                        <p class="description">Select which tags should be added to <code>
                                                &lt;head&gt;</code> section of your site.</p>
                                        <?php
                                        include_once('wtt-social-options.php');
                                        ?>
                                    </div>

                                    <div id="wtt_social_language_option" class="wtt_withborder">
                                        <h3 style="font-weight: bold;">Social media language option</h3>
                                        <p class="description">Select the language you're using on social media.</p>
                                        <?php
                                        include_once('wtt-social-languages.php');
                                        ?>
                                    </div>

                                    <div id="wtt_social_schema_settings" class="wtt_withborder">
                                        <h3 style="font-weight: bold;">Schema.org settings</h3>
                                        <p class="description">The Schema.org markup is shown as metadata in your site. This is also known as the "Knowledge Graph" and "Structured Data".</p>
                                        <?php
                                        include_once('wtt-social-schema.php');
                                        ?>
                                    </div>

                                    <div id="wtt_settings_submit">
                                        <?php
                                        settings_fields('wtt_social');
                                        submit_button();
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>