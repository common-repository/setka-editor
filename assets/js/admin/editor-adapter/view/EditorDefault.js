
module.exports = Backbone.View.extend({

    DOM: {},

    initialize: function() {
        this.setupDOM();
        _.bindAll(this, 'syncModel', 'setContent');
    },

    setupDOM: function() {
        // Textarea (<textarea>)
        this.DOM.textarea = this.$('#' + this.model.get('textareaId'));
    },

    render: function() {
        this.setContent();
    },

    show: function() {
        this.$el.show();
        Backbone.off('setka:editor:adapter:save', this.setContent);
    },

    hide: function() {
        this.$el.hide();
        Backbone.on('setka:editor:adapter:save', this.setContent);
    },

    _getContent: function() {
        return this.DOM.textarea.val();
    },

    setContent: function() {
        this.DOM.textarea.val(this.model.get('editorContent'));
    },

    syncModel: function() {
        this.model.set('editorContent', this._getContent());
    }

});
