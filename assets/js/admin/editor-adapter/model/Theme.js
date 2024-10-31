
module.exports = Backbone.Model.extend({

    isDisabled: function() {
        var disabled = this.get('disabled');

        if(!_.isUndefined(this.get('disabled')) && disabled === true)
            return true;

        return false;
    },

});
