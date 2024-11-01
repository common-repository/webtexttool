<?php global $post; ?>
<div id="tabs-2" style="padding: 10px 0 10px 0;">
    <div id="wtt-meta-social-section" class="wtt-meta-section">
        <table class="wtt-social-meta-table">
            <tbody>
            <tr>
                <th scope="row"><label class="wtt-social-meta-header" for="wtt_opengraph-title">Social Meta
                        Title</label>
                </th>
            </tr>
            <tr>
                <td><input type="text" id="wtt_opengraph-title" size="36" name="wtt_opengraph-title"
                           value="<?php echo((get_post_meta($post->ID, '_wtt_opengraph-title', true) <> '') ? htmlentities(get_post_meta($post->ID, '_wtt_opengraph-title', true)) : '') ?>"
                           class="large-text"
                           aria-describedby="wtt_opengraph-title-desc">
                    <p class="wtt_meta_description_info">A concise title for the related content. Keep this field
                        empty
                        to use the default page title instead.</p>
                </td>
                <td valign="top">
                    <p id='wtt_title_info'></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label class="wtt-social-meta-header" for="wtt_opengraph-description">Social Meta
                        Description</label></th>
            </tr>
            <tr>
                <td><textarea class="large-text" rows="3" id="wtt_opengraph-description"
                              name="wtt_opengraph-description"
                              aria-describedby="wtt_opengraph-description-desc"><?php echo((get_post_meta($post->ID, '_wtt_opengraph-description', true) <> '') ? get_post_meta($post->ID, '_wtt_opengraph-description', true) : '') ?></textarea>
                    <p class="wtt_meta_description_info">A description that concisely summarizes the content. Keep
                        this
                        field empty to use the page description instead.</p>
                </td>
                <td valign="top">
                    <p id='wtt_description_info'></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label class="wtt-social-meta-header" for="wtt_opengraph-image">Social Meta
                        Image</label>
                </th>
            </tr>
            <tr>
                <td><input id="wtt_opengraph-image" type="text" size="36"
                           name="wtt_opengraph-image"
                           value="<?php echo((get_post_meta($post->ID, '_wtt_opengraph-image', true) <> '') ? get_post_meta($post->ID, '_wtt_opengraph-image', true) : '') ?>"
                           aria-describedby="wtt_opengraph-image-desc">
                    <input id="wtt_opengraph-image_button" class="wtt_image_upload_button button"
                           type="button" value="Upload image">
                    <input id="wtt_opengraph_image_button_clear" class="button" type="button" value="Clear field"/>
                    <p class="wtt_meta_description_info">A URL to a unique image representing the content of the
                        page.
                        The recommended resolution is 1200 pixels x 627 pixels.</p>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th scope="row"><label class="wtt-social-meta-header" for="wtt_canonical-link">Canonical URL</label>
                </th>
            </tr>
            <tr>
                <td>
                    <input id="wtt_canonical-link" class="large-text" type="text"
                           placeholder="Paste URL or type to search"
                           name="wtt_canonical-link"
                           value="<?php echo((get_post_meta($post->ID, '_wtt_canonical-link', true) <> '') ? get_post_meta($post->ID, '_wtt_canonical-link', true) : '') ?>">
                    <p class="wtt_meta_description_info">A canonical tag is all about duplicate content and
                        preferred
                        content. Keep this field empty to use the permalink instead.</p>
                </td>
                <td>&nbsp;</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>