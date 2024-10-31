var
    adapter = window.setkaEditorAdapter,
    translations = setkaEditorAdapterL10n;

module.exports = Backbone.View.extend({

    el: '#poststuff',

    views: {}, DOM: {},

    classes: {
        collapsed: translations.names.css + '-poststuff-container-1-collapsed'
    },

    initialize: function() {
        _.bindAll(this, 'collapse', 'unCollapse');
        this.createSubViews();
    },

    createSubViews: function() {
        this.views.postBody = new adapter.view.postStuff.postBody.PostBody({
            model: this.model
        });
    },

    collapse: function() {
        this.$el.addClass(this.classes.collapsed);
        Backbone.trigger('setka:editor:adapter:poststuff:collapsed');
    },

    unCollapse: function() {
        this.$el.removeClass(this.classes.collapsed);
        Backbone.trigger('setka:editor:adapter:poststuff:unCollapsed');
    }

});
