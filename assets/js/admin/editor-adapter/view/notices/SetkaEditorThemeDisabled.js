var
    translations = window.setkaEditorAdapterL10n;

module.exports = Backbone.View.extend({

    el: '#wp-kit-notice-' + translations.names.css + '-' + translations.names.css + '-theme-disabled',

    initialize: function() {
        _.bindAll(this, 'themeDisabled', 'themeEnabled');
        this.addEvents();
    },

    addEvents: function() {
        Backbone.on('setka:editor:adapter:editors:setka:themeDisabled', this.themeDisabled);
        Backbone.on('setka:editor:adapter:editors:setka:themeEnabled', this.themeEnabled);
    },

    themeDisabled: function() {
        this.show();
    },

     themeEnabled: function() {
        this.hide();
    },

    show: function() {
        this.$el.removeClass('hidden');
    },

    hide: function() {
        this.$el.addClass('hidden');
    }
});
