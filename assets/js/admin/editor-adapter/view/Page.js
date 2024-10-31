var
    adapter = window.setkaEditorAdapter,
    $ = jQuery,
    translations = window.setkaEditorAdapterL10n;

let User = require('../model/User');

/**
 * Page which may have Admin Menu, Sidebar (Side Sortables WordPress Meta boxes),
 * Editor and other things.
 */
module.exports = Backbone.View.extend({

    views: {},

    utils: {},

    DOM: {},

    /**
     * @property {User}
     */
    user: null,

    initialize: function() {
        this.user = new User(this.model.get('editorConfig').get('user').capabilities);

        this.setupDOM();
        this.createUtils();

        // Init children views
        this.createSubViews();

        // Events
        _.bindAll(this, 'tryToEnableSetka', 'switchToSetkaEditor', 'switchToDefaultEditor', 'onPostBoxesColumns');
        this.addEvents();

        // Render
        this.render();

        // Auto init editor if post created with this editor
        if(this.model.get('useSetkaEditor')) {
            this.switchToSetkaEditor();
        } else if(this.model.isPublished()) {
            this.muteSetkaEditor();
        }
    },

    setupDOM: function() {
        this.DOM.wp_content_wrap = $('#wp-content-wrap');
    },

    createUtils: function() {
        this.utils.autosave = new adapter.utils.AutoSave({
            model: this.model
        });

        this.utils.editorExpand = {
            editorExpand: new adapter.utils.editorExpand.EditorExpand({
                model: this.model
            })
        };
    },

    createSubViews: function() {

        // HTML
        this.views.html = new adapter.view.HTML();

        // WordPress Admin Menu (dark left vertical side)
        this.views.adminMenu = new adapter.view.AdminMenu();

        // Screen Options (#screen-meta)
        this.views.screenMeta = {
            editorExpand: new adapter.view.screenMeta.EditorExpand()
        };

        // Notices
        this.views.notices = {
            setkaEditorThemeDisabled: new adapter.view.notices.SetkaEditorThemeDisabled(),
            localStorage: new adapter.view.notices.LocalStorage({model: this.model}),
        };

        this.views.postStuff = new adapter.view.postStuff.PostStuff({
            model: this.model
        });

        // Main form on post.php pages
        this.views.form = new adapter.view.Form({
            model: this.model
        });

        // Add Media button
        this.views.addMediaButton = new adapter.view.AddMediaButton({
            model: this.model
        });

        // Editors tabs (Visual, Text,..)
        this.views.editorTabs = new adapter.view.EditorTabs({
            el: '#wp-' + this.model.get('textareaId') + '-wrap .wp-editor-tabs',
            model: this.model
        });

        // Default editor (textarea)
        this.views.editorDefault = new adapter.view.EditorDefault({
            el: '#wp-' + this.model.get('textareaId') + '-editor-container',
            model: this.model
        });

        // Grid editor
        this.views.setkaEditor = new adapter.view.Editor({
            model: this.model,
            user: this.user,
        });

        // Pointers
        this.views.pointers = {
          disabledTabs: new adapter.view.pointers.DisabledTabsPointer()
        };
    },

    addEvents: function() {
        Backbone.on('setka:editor:adapter:editorTabs:setka:click', this.tryToEnableSetka);
        Backbone.on('setka:editor:adapter:editorTabs:default:click', this.switchToDefaultEditor);
    },

    render: function() {

        this.views.form.render();

        this.views.editorTabs.render();

        this.views.setkaEditor.render();
        this.DOM.wp_content_wrap
            .append(this.views.setkaEditor.$el);
    },

    muteSetkaEditor: function() {
        this.views.editorTabs.muteSetka();
    },

    tryToEnableSetka: function() {
        if(this.model.isPublished()) {
            this._showPostPublishedTooltip();
        } else {
            this.switchToSetkaEditor();
        }
    },

    _showPostPublishedTooltip: function() {
        this._showTooltip(translations.pointers.publishedPost);
    },

    _showTooltip: function(pointer) {
        $(pointer.target).pointer(pointer.options).pointer('open');
    },

    switchToSetkaEditor: function() {
        if( this.views.setkaEditor.isThemeDisabled() ) {
            Backbone.trigger('setka:editor:adapter:editors:setka:themeDisabled');
        } else {
            Backbone.trigger('setka:editor:adapter:editors:setka:themeEnabled');
        }

        // Switch to HTML editor if TinyMCE is available
        if(!_.isUndefined(window.switchEditors)) {
            window.switchEditors.go(this.model.get('textareaId'), 'html');
        }

        // Update content in model (textarea -> model)
        this.views.editorDefault.syncModel();

        // Show confirm window and switch only if user click OK
        if(!_.isEmpty(this.model.get('editorContent'))
            // Only for NOT Setka Editor posts
            && !this.model.get('useSetkaEditor')
            && !confirm(translations.view.editor.switchToSetkaEditorConfirm))
            return;


        // Start the editor

        // Toggle wrapper classes
        this.DOM.wp_content_wrap
            .removeClass('html-active tmce-active')
            .addClass(translations.names.css + '-active');


        // Switch editors
        this.views.setkaEditor.show();
        this.views.editorDefault.hide();

        // Mute default editor tabs
        this.views.editorTabs.muteDefaults();

        // Try to increase horizontal space (width) for editor
        // by collapsing (folding) admin menu
        if( ! this.isEditorFitToSize() ) {
            this.views.adminMenu.fold();
        }
        // by collapsing right meta boxes
        if(this.model.get('postBoxesColumns') === 2) {
            this.views.postStuff.collapse();
        }

        // WordPress can change the number of cols and we need
        // adopt to this changes.
        this.model.on('change:postBoxesColumns', this.onPostBoxesColumns);
    },

    switchToDefaultEditor: function() {
        // Toggle wrapper classes
        this.DOM.wp_content_wrap
            .removeClass(translations.names.css + '-active')
            .addClass('html-active');

        this.views.editorDefault.setContent();

        // Switch editors
        this.views.editorDefault.show();
        this.views.setkaEditor.hide();

        // Unmute default editor tabs
        this.views.editorTabs.unMuteDefaults();

        // WordPress can change the number of cols and we need
        // adopt to this changes.
        this.model.off('change:postBoxesColumns', this.onPostBoxesColumns);

        this.views.postStuff.unCollapse();
    },

    isEditorFitToSize: function() {
        return window.innerWidth > 1360;
    },

    onPostBoxesColumns: function () {
        if(this.model.get('postBoxesColumns') === 2) {
            this.views.postStuff.collapse();
        } else {
            this.views.postStuff.unCollapse();
        }
    },

});
