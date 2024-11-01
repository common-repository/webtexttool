<script type="text/javascript"
        src="<?php echo plugins_url('../js/wtt-tab.min.js', dirname(__FILE__)) . "?ver=" . WTT_VERSION; ?>"></script>
<link rel="stylesheet"
      href="<?php echo plugins_url('../css/wtt-settings-page.min.css', dirname(__FILE__)) . "?ver=" . WTT_VERSION; ?>">
<link rel="stylesheet"
      href="<?php echo plugins_url('../css/wtt-admin.min.css', dirname(__FILE__)) . "?ver=" . WTT_VERSION; ?>">

<div id="wtt-settings" class="wrap">

    <h1 id="wtt-title"><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
    ?>

    <!-- Nav tabs -->
    <div class="wtt-card">
        <ul class="wtt-nav wtt-nav-tabs" role="tablist" id="myTabs">
            <li role="presentation" class="active">
                <a href="#home" aria-controls="home" role="tab" data-toggle="tab">Home</a>
            </li>
            <li role="presentation" >
                <a href="#separator" aria-controls="separator" role="tab" data-toggle="tab">Separator</a>
            </li>
            <li role="presentation" >
                <a href="#post_types" aria-controls="profile" role="tab" data-toggle="tab">Post Types</a>
            </li>
            <li role="presentation">
                <a href="#taxonomies" aria-controls="messages" role="tab" data-toggle="tab">Taxonomies</a>
            </li>
            <li role="presentation">
                <a href="#other" aria-controls="settings" role="tab" data-toggle="tab">Other</a>
            </li>
        </ul>
        <div class="wtt-header-logo">
            <img class="wtt-main-logo" width="120px"
                 src="<?php echo plugins_url('../images/tm_logo.png', dirname(__FILE__)); ?>"
                 alt="wtt logo"/>
        </div>

        <script>
            var currTab = localStorage.getItem("tab");

            //on load, load last nav tab
            jQuery(document).ready(function($) {
                $('#myTabs a[href="#'+ currTab +'"]').tab('show');

                $('.wtt-more-tags').on('click', function() {
                    if($("#wtt-patterns-list").hasClass("display-none")) {
                        $('#wtt-patterns-list').removeClass('display-none');
                        $('#wtt-patterns-list').addClass('display-block');
                    } else {
                        $('#wtt-patterns-list').removeClass('display-block');
                        $('#wtt-patterns-list').addClass('display-none');
                    }
                });

                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    if((e.target).toString().indexOf("separator") > 0) {
                        $("#wtt-patterns-list").addClass("display-none");
                    }
                });

                $('#title_home_wtt').click(function() {
                    $('#title-home-wtt').val($('#title-home-wtt').val() + ' ' + $('#title_home_wtt').attr('data-tag'));
                });
                $('#tagline_home_wtt').click(function() {
                    $('#title-home-wtt').val($('#title-home-wtt').val() + ' ' + $('#tagline_home_wtt').attr('data-tag'));
                });
                $('#tagline_desc_home_wtt').click(function() {
                    $('#metadesc-home-wtt').val($('#metadesc-home-wtt').val() + $('#tagline_home_wtt').attr('data-tag'));
                });
            });

            jQuery.wtt_patterns_list = jQuery.parseJSON("<?php echo addslashes(WTT_PATTERNS) ?>");
        </script>

        <!-- Tab panes -->
        <form id="wtt_settings_form" name="settings" action="<?php echo esc_url(admin_url('options.php')) ?>"
              method="post">
            <div class="inside" style="display: table;">

                <div id="wtt_settings_body">

                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade in active" id="home">

                            <div>
                                <div id="wtt_home_option" class="wtt_withborder">
                                    <h3><?php echo __('Home', WTT_PLUGIN_NAME);?></h3>
                                    <p class="description">Customize your title & meta description for homepage.</p>

                                    <div id="home-container">
                                        <?php
                                        $wttform = WTT_Form::get_instance();
                                        $wttform->text_field('title-home-wtt', __('Site title', 'webtexttool'), 'wtt_pattern_field', 'wtt_settings');

                                        echo '<div class="wtt-wrap-tags">';
                                        echo '<span id="title_home_wtt" data-tag="{{sitetitle}}" class="wtt-tag-title"><span class="dashicons dashicons-plus"></span>' . __('Site Title', 'webtexttool') . '</span>';
                                        echo '<span id="tagline_home_wtt" data-tag="{{tagline}}" class="wtt-tag-title"><span class="dashicons dashicons-plus"></span>' . __('Tagline', 'webtexttool') . '</span>';
                                        echo '<span id="wtt-quick-help" class="wtt-tag-title wtt-more-tags"><span class="dashicons dashicons-arrow-down"></span>' . __('More tags', 'webtexttool') . '</span>';
                                        echo '</div>';

                                        $wttform->text_area('metadesc-home-wtt', __('Meta description', 'webtexttool'), 'wtt_pattern_field', 'wtt_settings');

                                        echo '<div class="wtt-wrap-tags">';
                                        echo '<span id="tagline_desc_home_wtt" data-tag="{{tagline}}" class="wtt-tag-title"><span class="dashicons dashicons-plus"></span>' . __('Tagline', 'webtexttool') . '</span>';
                                        echo '<span id="wtt-quick-help" class="wtt-tag-title wtt-more-tags"><span class="dashicons dashicons-arrow-down"></span>' . __('More tags', 'webtexttool') . '</span>';
                                        echo '</div>';
                                        ?>
                                    </div>

                                    <div style="clear: both;"></div>

                                </div>
                            </div>

                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="separator">

                            <div>
                                <div id="wtt_separator_option" class="wtt_withborder">
                                    <h3><?php echo __('Title Separator', WTT_PLUGIN_NAME);?></h3>
                                    <p class="description">Places a separator between the elements of the post title or description.</p>

                                    <div id="sep-container">
                                        <?php
                                        $wttform->radio('separator', json_decode(WTT_SEPARATORS, true));
                                        ?>
                                    </div>

                                    <div style="clear: both;"></div>

                                </div>
                            </div>

                        </div>
                        <div role="tabpanel" class="tab-pane fade " id="post_types">
                            <div>
                                <div id="wtt_post_type_option" class="wtt_withborder">
                                    <h3 style="font-weight: bold;">Single Post Types</h3>
                                    <p class="description">Note: only post types which have their own page (like
                                        posts or pages) are supported.</p>
                                    <?php
                                    include_once('wtt-post-types-settings.php');
                                    ?>
                                </div>

                                <?php if (class_exists('acf')) { ?>

                                    <div id="wtt_acf_option" class="wtt_withborder">
                                        <h3 style="font-weight: bold;">Advanced Custom Fields analysis:</h3>
                                        <p class="description">Ensure that <?php echo __(WTT_PLUGIN_NAME); ?> analyzes all Advanced
                                            Custom Fields content.</p>
                                        <?php
                                        include_once('wtt-acf-settings.php');
                                        ?>
                                    </div>

                                <?php } ?>

                                <?php if (class_exists('RWMB_Loader')) { ?>

                                    <div id="wtt_rwmb_option" class="wtt_withborder">
                                        <h3 style="font-weight: bold;">MetaBox.io analysis:</h3>
                                        <p class="description">Ensure that <?php echo __(WTT_PLUGIN_NAME); ?> analyzes all MetaBox.io
                                            content.</p>
                                        <?php
                                        include_once('wtt-rwmb-settings.php');
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="taxonomies">
                            <div id="wtt_taxonomies_option" class="wtt_withborder">
                                <h3 style="font-weight: bold;">Taxonomies</h3>
                                <p class="description">Customize your metas for all taxonomies archives.</p>
                                <?php
                                include_once('wtt-taxonomies-settings.php');
                                ?>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="other">
                            <div id="wtt_other_option" class="wtt_withborder">
                                <h3 style="font-weight: bold;">Other</h3>
                                <p class="description">Customize your metas for all other archives.</p>
                                <?php
                                include_once('wtt-other-settings.php');
                                ?>
                            </div>
                        </div>

                        <div id="wtt_settings_submit">
                            <?php
                            settings_fields('wtt_settings');
                            submit_button();
                            ?>
                        </div>
                    </div>
                </div>

                <div id="wtt-patterns-list" class="display-none">
                    <h3>Use of TM patterns</h3>
                    <table style="padding-top: 20px;">
                        <tbody>
                        <?php
                        $all_patterns = json_decode(WTT_PATTERNS, true);
                        foreach ($all_patterns as $pattern => $details) { ?>
                            <tr>
                                <td><code><?php echo $pattern ?></code></td>
                                <td><?php echo $details ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </form>
    </div>
</div>