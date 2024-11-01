jQuery(document).on( 'click', '.wtt-notice.notice-dismiss', function() {
    jQuery.ajax({
        url: webtexttoolnonce.ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            nonce: webtexttoolnonce.nonce,
            action: webtexttoolnonce.action + '_dismiss_wtt_notice',
            data: 1
        },
        success: function() {
            jQuery(".wtt-plugin-notice").remove();
        },
        error: function(error) {
            console.error(error.message);
        }
    })
})