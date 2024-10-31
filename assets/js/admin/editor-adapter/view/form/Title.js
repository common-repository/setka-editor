
module.exports = Backbone.View.extend({

    el: '#title',

    initialize: function() {
        _.bindAll(this, 'changeEditorInitialized', 'render');
        this.model.on('change:editorInitialized', this.changeEditorInitialized);
    },

    changeEditorInitialized: function() {
        this.model.get('editorInitialized') ? this.setupEvents() : this.removeEvents();
    },

    setupEvents: function () {
        this.model.on('change:title', this.render);
    },

    removeEvents: function() {
        this.model.off('change:title', this.render);
    },

    render: function() {
        this.$el.val(this.model.get('title'));
    },
});
