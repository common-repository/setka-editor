var
    translations = window.setkaEditorAdapterL10n,
    $ = jQuery,
    autosave = wp.autosave;

module.exports = Backbone.View.extend({

    el: '#local-storage-notice',

    initialize: function() {
        _.bindAll(this, 'onEditorInitialized', 'restoreAutosave', 'hide');
        this.model.on('change:editorInitialized', this.onEditorInitialized);
    },

    onEditorInitialized: function() {
        if(this.model.get('editorInitialized')) {
            this.setupEvents();
        } else {
            this.restoreDefaultEvents();
        }
    },

    setupEvents: function() {
        this.$el.find('.restore-backup').off('click.autosave-local').on('click.autosave-local', this.restoreAutosave);
    },

    restoreDefaultEvents: function() {
        this.$el.find('.restore-backup').off('click.autosave-local');
        this.hide();
    },

    restoreAutosave: function() {
        var data = autosave.local.getSavedPostData();

        if(data) {
            this.model.set('title', data.post_title);
            this.model.set('excerpt', data.excerpt);
            Backbone.trigger('setka:editor:adapter:replace', data.content);
        }

        this.hide();
    },

    hide: function() {
        this.$el.fadeTo( 250, 0, () => {
            this.$el.slideUp( 150 );
        });
    },

});
