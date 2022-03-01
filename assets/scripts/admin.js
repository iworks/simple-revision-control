/**
 * Simple Revision Control - v2.1.0
 * http://iworks.pl/en/plugins/fleet/
 * Copyright (c) 2022; * Licensed GPLv2+
 */
jQuery(document).ready(function($) {
    $('#simple_revision_control_admin_index .delete').on('click', function(e) {
        var $spinner = $('.spinner', $(this).parent());
        var $button = $(this);
        var data;
        e.preventDefault();
        $spinner.addClass('is-active');
        $button.attr('disabled', 'disabled');
        data = {
            action: 'simple_revision_control_delete_revisions',
            posttype: $button.data('posttype'),
            _wpnonce: $button.data('nonce')
        };
        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                window.location.reload();
                return;
            }
            $button.parent().html(
                '<div class="notice notice-error"><p>' +
                response.data +
                '</p></div>'
            );
        });

        return false;
    });
    /**
     * show/hide after load
     */
    $('#simple_revision_control_admin_index input[type=radio]:checked').each(function() {
        if ('custom' !== $(this).val()) {
            $(this).closest('tr').next().hide();
        }
    });
    $('#simple_revision_control_admin_index input[type=radio]').on('change', function() {
        if ($(this).is(':checked')) {
            if ('custom' !== $(this).val()) {
                $(this).closest('tr').next().hide();
            } else {
                $(this).closest('tr').next().show();
            }
        }
    });

});