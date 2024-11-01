<link rel="stylesheet"
      href="<?php echo plugins_url('../css/wtt-tools-page.min.css', dirname(__FILE__)) . "?ver=" . WTT_VERSION; ?>">

<div id="wtt-tools" class="wrap">

    <div id="poststuff">

        <div id="post-body" class="metabox-holder columns-2">

            <div id="post-body-content">

                <div class="postbox">

                    <h3 class="postbox-title">
                        <span><?php echo __(WTT_PLUGIN_NAME); ?> Tools</span>
                        <img class="webtexttool-logo" width="150"
                             src="<?php echo plugins_url('../images/tm_logo.png', dirname(__FILE__)); ?>"
                             alt="textmetrics-logo"/>
                    </h3>

                    <div class="inside">

                        <form id="wtt_tools_form" name="tools"
                              action="<?php echo esc_url(admin_url('admin.php?page=wtt_tools')) ?>" method="post">

                            <div id="wtt_tools_body">
                                <div class="wtt_withborder">

                                    <h3>Import SEO data from other plugins:</h3>
                                    <p><strong>Disclaimer:</strong> <?php echo __(WTT_PLUGIN_NAME); ?> is not responsible for any data loss. Use this tool at your own risk.</p>
                                    <p class="description"><span>Use the drop down below to choose which plugin you wish to
                                            import SEO data from.</span></p>
                                    <p class="description"><span>
                                        Click "Analyze" for a list of SEO data that can be imported into <?php echo __(WTT_PLUGIN_NAME); ?>,
                                            along with the number of records that will be imported.</span></p>
                                    <p class="description"><span>
                                        <strong>Please Note:</strong> Some plugins do not share similar data, or they
                                        store data in a non-standard way.
                                        If we cannot import this data, it will remain unchanged in your database. Any
                                        compatible SEO data will be displayed for you to review.
                                        If a post or page already has SEO data in <?php echo __(WTT_PLUGIN_NAME); ?>, we will not import the
                                            data from another plugin.</span></p>
                                    <p class="description"><span>
                                        Click "Convert" to perform the import. After the import has completed, you will
                                        be alerted to how many records were imported, and how many records had to be
                                            ignored, based on the criteria above.</span></p>
                                    <p class="description">
                                        <span>
                                        If you want to delete the old SEO data after import, select the "Delete the old
                                        SEO data after import?" option below.
                                        </span>
                                    </p>

                                    <?php

                                    wp_nonce_field('webtexttool');

                                    global $_wtt_seo_plugins;

                                    echo '<div>';
                                    echo '<span>Import SEO data from: </span>';

                                    echo '<select name="platform_old">';

                                    foreach ($_wtt_seo_plugins as $platform => $data) {
                                        if ($platform !== 'Webtexttool') {
                                            printf('<option value="%s" %s>%s</option>', $platform, selected($platform, isset($_POST['platform_old']) ? $_POST['platform_old'] : '', 0), $platform);
                                        }
                                    }

                                    echo '</select>' . "\n\n";
                                    echo '</div>';
                                    ?>

                                    <div id="wtt_settings_submit">
                                        <input type="submit" class="button button-default" name="analyze"
                                               value="Analyze"/>
                                        <input type="submit" class="button-primary" value="Convert"
                                               onclick="return confirm('Are you sure you want to import the SEO data?');"/>
                                        <br><br>
                                        <input class="checkbox" type="checkbox" id="delete_old_data"
                                               name="delete_old_data" value="on">
                                        <label style="font-weight: 600;" class="checkbox" for="delete_old_data">Delete
                                            the old SEO data after
                                            import?</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <?php
                        wtt_import_action();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
