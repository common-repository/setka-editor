var
    adapter = window.setkaEditorAdapter;

module.exports = Backbone.View.extend({

    el: '#post',

    events: {
        submit: 'onSubmit'
    },

    views: {},

    initialize: function() {
        this.createSubViews();
    },

    render: function() {
        this.$el.append(this.views.settings.$el);
    },

    createSubViews: function() {
        this.views.settings = new adapter.view.Settings({
            model: this.model
        });

        this.views.nonce = new adapter.view.Nonce({
            model: this.model
        });

        this.views.postId = new adapter.view.PostId({
            model: this.model
        });

        this.views.originalPostStatus = new adapter.view.OriginalPostStatus({model: this.model})

        this.views.excerpt = new adapter.view.Excerpt({model: this.model});
        this.views.title = new adapter.view.Title({model: this.model});
    },

    onSubmit: function() {
        // Update settings input with actual model data for POST request.
        Backbone.trigger('setka:editor:adapter:save');
        this.views.settings.render();
    }

});
