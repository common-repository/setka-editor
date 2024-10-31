require("core-js/stable");
require("regenerator-runtime/runtime");

/* global jQuery, setkaEditorAdapterL10n */
(function( $ ) {
    /**
     * To run an editor we need:
     * 1. Load JSON from Setka Server.
     * 2. Wait for DOM-ready.
     * 3. Wait for TinyMCE (because it enqueued not as all JS files in WordPress).
     */

    var domPromise = new Promise(function(resolve, reject) {
        $(document).ready(resolve);
    });

    var assetsPromise = $.getJSON(setkaEditorAdapterL10n.settings.themeData)
        .error(function() {
            var notice = new setkaEditorAdapter.view.notices.AssetsLoadError();
            notice.show();
        });

    var tinyPromise = new Promise(function(resolve, reject) {
        function tick() {
            if(_.isUndefined(window.switchEditors)) {
                // TinyMCE is not exists at all.
                clearTimeout(interval);
                resolve();
            }

            if(!_.isUndefined(window.tinyMCE)) {
                clearTimeout(interval);
                resolve();
            }

            if(counter > 30) {
                clearTimeout(interval);
                reject('TinyMCE await timeout.');
            }

            counter++;
        }
        var counter = 0;
        var interval = setInterval(tick, 1000);
    });

    Promise.all([domPromise, assetsPromise, tinyPromise])
        .then(function(result) {
            init(result[1]);
        })
        .catch(function (error) {
            console.error(error);
        });

    function init(response) {
        var EditorConfigModel = setkaEditorAdapter.model.EditorConfig;
        var EditorResourcesModel = setkaEditorAdapter.model.EditorResources;
        var PageView = setkaEditorAdapter.view.Page;
        var FormModel = setkaEditorAdapter.model.Form;
        var translations = setkaEditorAdapterL10n;
        var editorConfig = _.omit(translations.settings.editorConfig, _.isNull);

        var settings = {
            textareaId: 'content',
            editorConfig: new EditorConfigModel(
                // Merge post specific settings with defaults
                _.extend(response.config, editorConfig)
            ),
            editorResources: new EditorResourcesModel(response.assets),
            useSetkaEditor: translations.settings.useSetkaEditor
        };

        // Auto init editor from /wp-admin/post-new.php?setka-editor-auto-init
        var uri = new URI(window.location.href);
        var uriQuery = uri.search(true);
        if(typeof uriQuery[translations.names.css + '-auto-init'] !== 'undefined') {
            settings.useSetkaEditor = true;
        }

        window.setkaEditorPlugin = new PageView({
            model: new FormModel(settings)
        });
    }
}(jQuery));
