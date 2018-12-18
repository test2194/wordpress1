jQuery(document).ready(function($) {
    $(document).on( 'click', '.astore-welcome-notice .notice-dismiss', function() {

    $.ajax({
        url: astore_admin.ajaxurl,
        data: {
            action: 'astore_dismiss_notice'
        }
    })

})
});