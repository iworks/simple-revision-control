jQuery(document).ready(function($) {
    $('#simple_revision_control_admin_index .delete').on('click', function() {
        window.console.log('uyes2');
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