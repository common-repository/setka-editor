var
    translations = window.setkaEditorAdapterL10n;

module.exports = Backbone.View.extend({

    el: '#wp-kit-notice-' + translations.names.css + '-assets-load-error',

    show: function() {
        this.$el.removeClass('hidden');
    },

    hide: function() {
        this.$el.addClass('hidden');
    }
});
