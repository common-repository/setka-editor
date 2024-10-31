var
    translations = window.setkaEditorAdapterL10n,
    AbstractTab = require('./AbstractTab');

module.exports = AbstractTab.extend({

    events: {
        click: 'onClick'
    },

    onClick: function(event) {
        if( this.model.get('editorInitialized') ) {
            Backbone.trigger('setka:editor:adapter:defaultTabClick', event);
            event.stopImmediatePropagation();

            // This part of code can add confirm window and only if user click "ok" then switch to default editor.

            /*if (confirm(translations.view.editor.switchToDefaultEditorsConfirm)) {
                Backbone.trigger('setka:editor:adapter:editorTabs:default:click');
            }
            else {
                // Keeps the rest of the handlers from being executed
                event.stopImmediatePropagation();
            }*/
        }
    },
});
