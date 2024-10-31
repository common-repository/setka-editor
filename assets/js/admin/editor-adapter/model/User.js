module.exports = Backbone.View.extend({

    capabilities: [],

    constructor: function(capabilities) {
        this.capabilities = capabilities;
    },

    hasCapability: function(capability) {
        return(
            _.has(this.capabilities, capability)
            &&
            true === this.capabilities[capability]
        );
    }
});
