var
    adapter = window.setkaEditorAdapter,
    translations = window.setkaEditorAdapterL10n,
    $ = jQuery,
    dompurify = window.DOMPurify;

module.exports = Backbone.View.extend({

    /**
     * @property {User}
     */
    user: null,

    DOM: {},

    attributes: function() {
        return {
            id: 'content-' + translations.names.css,
            class: translations.names.css + '-wrapper',
            style: 'display: none;'
        };
    },

    initialize: function(options) {
        this.user = options.user;
        this.setupDOM();
        this.addEvents();
    },

    setupDOM: function() {
        this.DOM.document = $(document);
    },

    addEvents: function() {
        _.bindAll(this, 'syncModel', 'replaceContent');
    },

    render: function() {

        this.DOM.stk_editor_wrapper_2 =
            $('<div></div>')
                .addClass(translations.names.css + '-wrapper-2');

        this.$el.append(this.DOM.stk_editor_wrapper_2);

        return this;
    },

    show: function() {
        // If already initialized do nothing
        if( !this.model.get('editorInitialized') ) {

            // Create brand new editor container
            this.DOM.stk_editor_wrapper_2.html('<div id="setka-editor" class="stk-editor"></div>');

            // Show editor container
            this.$el.show();

            // Editor Start
            let editorConfig = {...this.model.get('editorConfig').toJSON(), ...{onPostContentChange: this.syncModel}}
            SetkaEditor.start(
                editorConfig,
                this.model.get('editorResources').toJSON()
            );

            // Insert content
            this.model.set('editorContent', this._preparePlainContent(this.model.get('editorContent')));
            this.replaceContent(this.model.get('editorContent'));

            // Find DOM elements which used in some functions of this view
            this.DOM.stk_editor = this.$('.stk-editor');

            // Set editorInitialized flag to true
            this.model.set('editorInitialized', true);

            Backbone.on('setka:editor:adapter:replace', this.replaceContent);

            Backbone.trigger('setka:editor:adapter:editors:setka:launch');
        }
    },

    hide: function() {
        SetkaEditor.stop();
        this.model.set('editorInitialized', false);
        this.$el.hide();
        Backbone.off('setka:editor:adapter:replace', this.replaceContent);
        Backbone.trigger('setka:editor:adapter:editors:setka:stop');
    },

    replaceContent: function(content) {
        SetkaEditor.replaceHTML(content);
    },

    _getContent: function() {
        var container = document.createElement('div');
        container.innerHTML = SetkaEditor.getHTML();
        return container.innerHTML;
    },

    /**
     * Parse content from WordPress textarea before inserting in our editor. Wrap plain text inside paragraphs (<p>).
     * If content not looks like plain text do nothing at now. This logic may have issues with parsing content but
     * it's ok. As last part of this method content puts inside the div with `stk-post` class.
     *
     * @param content string
     * @returns string
     * @private
     */
    _preparePlainContent: function(content) {
        
        if (!this.user.hasCapability('unfiltered_html')) {
            content = dompurify.sanitize(content);
        }

        var div = document.createElement('div');
        div.innerHTML = content;

        var newContent = '';
        var needWrapper = false;

        for(var i = 0; i < div.childNodes.length; i++) {
            var node = div.childNodes[i];

            // 3 means TEXT_NODE (plain text)
            if(node.nodeType === 3) {

                // The node must contain something.
                // Nodes with only spaces, line breaks and tabs will be skipped
                if(node.textContent.trim() === '') {
                    continue;
                }

                // Prepare content with autop()
                var wrapped = adapter.utils.autop(node.textContent);

                // If one line of text (without any \n symbols) autop() do nothing.
                // We manually wrap it in <p>.
                if(wrapped.indexOf('<p>') === -1) {
                    wrapped = '<p>' + wrapped + '</p>';
                }

                newContent += wrapped;
                needWrapper = true;
            } else {
                if(!jQuery(node).hasClass('stk-post')) {
                    needWrapper = true;
                }
                newContent += node.outerHTML;
            }
        }

        // Add wrapper if not founded
        if( needWrapper ) {
            newContent = '<div class="stk-post">' + newContent + '</div>';
        }

        return newContent;
    },

    syncModel: function() {
        if(!this.isEditorRunning()) {
            return;
        }

        this.model.set('editorContent', this._getContent());
        this.model.get('editorConfig').set('layout', SetkaEditor.getCurrentLayout().id);
        this.model.get('editorConfig').set('theme', SetkaEditor.getCurrentTheme().id);

        Backbone.trigger('setka:editor:adapter:save');
    },

    isThemeDisabled: function() {
        var slug = this.model.get('editorConfig').get('theme');
        var theme = this.model.get('editorResources').getThemeBySlug(slug);

        return theme ? theme.isDisabled() : false;
    },

    /**
     * @return {boolean}
     */
    isEditorRunning: function() {
        if (typeof SetkaEditor.isRunning === 'function') {
            return SetkaEditor.isRunning() && this.model.get('editorInitialized');
        } else {
            return this.model.get('editorInitialized');
        }
    },

});
