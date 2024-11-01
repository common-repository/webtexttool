<?php
$taxonomies_types = get_taxonomies(array('public' => true), 'objects');

if (is_array($taxonomies_types) && $taxonomies_types !== array()) {
    foreach ($taxonomies_types as $tax_type) {

        if(in_array($tax_type->name, array('link_category', 'nav_menu'), true)) {
            continue;
        }

        echo '<h3 id="' . esc_attr($tax_type->name) . '">' . esc_html(ucfirst($tax_type->labels->name)) . ' ('.'<code>'.$tax_type->name . '</code>'. ')</h3>';

        if ($tax_type->name === 'post_format') {
            $wttform->switch_field('disable-post_format', __('Format-based archives', 'webtexttool'), array(__('Enabled', 'webtexttool'), __('Disabled', 'webtexttool')), '');
        }

        echo "<div id='" . esc_attr($tax_type->name) . "-meta-settings'>";

        $wttform->text_field('title-tax-' . $tax_type->name, __('Title', 'webtexttool'), 'wtt_pattern_field', 'wtt_settings');

        if($tax_type->name !== 'post_format') {
            $wttform->switch_field('hidemetabox-tax-' . $tax_type->name, array('off' => 'Show', 'on' => 'Hide'), __('Load SEO title & metadesc', 'webtexttool'), 'wtt_settings');
        }

        echo '</div>';

    }
    unset($tax_type);
}
unset($taxonomies_types);