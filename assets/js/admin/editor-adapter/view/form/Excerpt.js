
module.exports = Backbone.View.extend({

    el: '#excerpt',

    initialize: function() {
        _.bindAll(this, 'changeEditorInitialized', 'render');
        this.model.on('change:editorInitialized', this.changeEditorInitialized);
    },

    changeEditorInitialized: function() {
        this.model.get('editorInitialized') ? this.setupEvents() : this.removeEvents();
    },

    setupEvents: function () {
        this.model.on('change:excerpt', this.render);
    },

    removeEvents: function() {
        this.model.off('change:excerpt', this.render);
    },

    render: function() {
        this.$el.val(this.model.get('excerpt'));
    },
});
