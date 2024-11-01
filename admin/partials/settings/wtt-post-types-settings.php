<?php
$post_types = get_post_types(array('public' => true), 'objects');

if (is_array($post_types) && $post_types !== array()) {
    foreach ($post_types as $post_type) {
        $name = $post_type->name;

        echo "<script>
                    jQuery(document).ready(function($) {
                         $('#title_".$name."').click(function() {
                            $('#title-".$name."').val($('#title-".$name."').val() + ' ' + $('#title_".$name."').attr('data-tag'));
                        });
                        $('#sitetitle_".$name."').click(function() {
                            $('#title-".$name."').val($('#title-".$name."').val() + ' ' + $('#sitetitle_".$name."').attr('data-tag'));
                        });
                    });
                </script>";

        echo '<div id="' . esc_attr($name) . '-meta-settings">';
        echo '<h3 id="' . esc_attr($name) . '">' . esc_html(ucfirst($post_type->labels->name)) . ' ('.'<code>'. esc_attr($name) . '</code>'. ')</h3>';

        $wttform->text_field('title-' . $name, __('Title', 'webtexttool'), 'wtt_pattern_field', 'wtt_settings');

        echo '<div class="wtt-wrap-tags">';
        echo '<span id="title_' . $name . '" data-tag="{{post_title}}" class="wtt-tag-title"><span class="dashicons dashicons-plus"></span>' . __('Post Title', 'webtexttool') . '</span>';
        echo '<span id="sitetitle_' . $name . '" data-tag="{{sitetitle}}" class="wtt-tag-title"><span class="dashicons dashicons-plus"></span>' . __('Site Title', 'webtexttool') . '</span>';
        echo '<span id="wtt-quick-help" class="wtt-tag-title wtt-more-tags"><span class="dashicons dashicons-arrow-down"></span>' . __('More tags', 'webtexttool') . '</span>';
        echo '</div>';

        $wttform->switch_field('hidemetabox-' . $name, array('off' => 'Show', 'on' => 'Hide'), __('Load SEO optimization', 'webtexttool'), 'wtt_settings');

        echo '</div>';

    }
    unset($post_type);
}
unset($post_types);