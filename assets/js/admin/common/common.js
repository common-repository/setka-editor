/* global jQuery, setkaEditorCommon */
(function($) {
    $(document).ready(function() {

        function disableNotice(event) {
            wp.ajax.post(
                setkaEditorCommon.ajaxName,
                {
                    actionName: setkaEditorCommon.notices.dismissAction,
                    noticeClass: $(event.target.parentElement).data('notice-class'),
                }
            );
        }

        setkaEditorCommon.notices.dismissIds.forEach(function(id) {
            $('#' + id + ' .notice-dismiss').click(disableNotice);
        });
    });
}(jQuery));
