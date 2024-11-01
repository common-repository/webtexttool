<?php

echo '<div id="archives-pages-settings">';
echo '<h3>' . esc_html__( 'Archive Settings', 'webtexttool' ) . '</h3>';
echo '<p><strong>' . esc_html__( 'Author archives', 'webtexttool' ) . '</strong><br/>';
$wttform->text_field( 'title-author-wtt', __( 'Title', 'webtexttool' ), 'wtt_pattern_field', 'wtt_settings' );
$wttform->text_area( 'metadesc-author-wtt', __( 'Meta description', 'webtexttool' ), 'wtt_pattern_field', 'wtt_settings' );
echo '</p>';
echo '<p><strong>' . esc_html__( 'Date archives', 'webtexttool' ) . '</strong><br/>';
$wttform->text_field( 'title-archive-wtt', __( 'Title', 'webtexttool' ), 'wtt_pattern_field', 'wtt_settings' );
$wttform->text_area( 'metadesc-archive-wtt', __( 'Meta description', 'webtexttool' ), 'wtt_pattern_field', 'wtt_settings' );
echo '</p>';
echo '</div>';

echo '<div id="miscellaneous-pages-settings">';
echo '<h3>' . esc_html__( 'Miscellaneous', 'webtexttool' ) . '</h3>';
echo '<p><strong>' . esc_html__( 'Search', 'webtexttool' ) . '</strong><br/>';
$wttform->text_field( 'title-search-wtt', __( 'Title', 'webtexttool' ), 'wtt_pattern_field', 'wtt_settings' );
echo '</p>';
echo '<p><strong>' . esc_html__( '404', 'webtexttool' ) . '</strong><br/>';
$wttform->text_field( 'title-404-wtt', __( 'Title', 'webtexttool' ), 'wtt_pattern_field', 'wtt_settings' );
echo '</p>';
echo '</div>';
