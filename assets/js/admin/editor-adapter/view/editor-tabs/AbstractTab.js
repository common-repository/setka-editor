var translations = window.setkaEditorAdapterL10n;

module.exports = Backbone.View.extend({

    classes: {
        muted: translations.names.css + '-switch-editor-muted'
    },

    mute: function() {
        this.$el.addClass(this.classes.muted);
        return this;
    },

    unMute: function() {
        this.$el.removeClass(this.classes.muted);
        return this;
    },

    isMuted: function() {
        return this.$el.hasClass(this.classes.muted);
    }
});
