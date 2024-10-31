/* global jQuery, setkaEditorSettingPages */
(function( $ ) {
    $(document).ready(function() {
        // Open external links in our menu in new tab.
        var menuLinks = $('#toplevel_page_setka-editor a');
        _.each(menuLinks, function(link) {
            if(location.hostname !== link.hostname && link.hostname.length) {
                $(link).attr('target', '_blank');
            }
        });
    });
}(jQuery));
