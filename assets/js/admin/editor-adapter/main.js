/* global jQuery, tinyMCE, Backbone */

var setkaEditorAdapter = {};

// Store everything globally
window.setkaEditorAdapter = setkaEditorAdapter;

// Models
setkaEditorAdapter.model = {

    Form: require('./model/Form'),
    EditorConfig: require('./model/EditorConfig'),
    EditorResources: require('./model/EditorResources'),
    Theme: require('./model/Theme'),
    Layout: require('./model/Layout')
};

// Collections
setkaEditorAdapter.collection = {
    Themes: require('./collection/Themes'),
    Layouts: require('./collection/Layouts')
};

// Views
setkaEditorAdapter.view = {
    // Main view ever
    Page: require('./view/Page'),

    HTML: require('./view/HTML'),

    screenMeta: {
        EditorExpand: require('./view/screen-meta/EditorExpand')
    },

    notices: {
        Prototype: require('./view/notices/Prototype'),
        AssetsLoadError: require('./view/notices/AssetsLoadError'),
        LocalStorage: require('./view/notices/LocalStorage'),
        SetkaEditorThemeDisabled: require('./view/notices/SetkaEditorThemeDisabled')
    },

    // Add Media button
    AddMediaButton: require('./view/AddMediaButton'),

    // Tabs
    EditorTabs: require('./view/editor-tabs/EditorTabs'),
    EditorTabDefault: require('./view/editor-tabs/TabDefault'),
    EditorTabSetka: require('./view/editor-tabs/TabSetka'),

    // Editors
    Editor: require('./view/EditorSetka'),
    EditorDefault: require('./view/EditorDefault'),

    postStuff: {
        PostStuff: require('./view/poststuff/PostStuff'),

        // Post Body
        postBody: {
            PostBody: require('./view/poststuff/post-body/PostBody')
        }
    },

    // Admin Menu
    AdminMenu: require('./view/AdminMenu'),

    // Form
    Excerpt: require('./view/form/Excerpt'),
    Form: require('./view/form/Form'),
    Nonce: require('./view/form/Nonce'),
    OriginalPostStatus: require('./view/form/OriginalPostStatus'),
    PostId: require('./view/form/PostId'),
    Settings: require('./view/form/Settings'),
    Title: require('./view/form/Title'),

    pointers: {
        DisabledTabsPointer: require('./view/pointers/DisabledTabsPointer')
    }
};

// Utils
setkaEditorAdapter.utils = {
    editorExpand: {
        EditorExpand: require('./utils/editor-expand/EditorExpand')
    },
    AutoSave: require('./utils/AutoSave'),
    autop: require('./utils/autop'),
};
