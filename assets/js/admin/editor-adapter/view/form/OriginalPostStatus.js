module.exports = Backbone.View.extend({

    el: '#original_post_status',

    initialize: function() {
        this.model.set('postStatus', this.get());
    },

    get: function() {
        return this.$el.val();
    },
});
