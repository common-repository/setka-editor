const { Warning, PlainText } = wp.editor;
const { select, dispatch, subscribe } = wp.data;
//import Button from '@wordpress/components'; TODO: refactor without global variable usage
const { Component, RawHTML } = wp.element;
const { __ } = wp.i18n;

import EditorStatus from './EditorStatus';

const BLOCK_SELECTED_HTML_CLASS = 'setka-editor-selected';

export default class SetkaEditor extends Component {

    /**
     * @type {EditorAssets}
     */
    assets = null;

    /**
     * @type {AdminMenu}
     */
    adminMenu = null;

    /**
     * @type {EditorStatus}
     */
    _editorStatus;

    state = {
        renderComponent: 'editor', // 'html-editor' | 'warning' | 'assets-error'
    };

    /**
     * Store previous content for Setka Editor (used in componentDidUpdate).
     * @type {Object}
     */
    contentSavedIntoProps;

    _onChangeUnsubscribe;

    constructor(props) {
        super(props);
        this.saveContent = this.saveContent.bind(this);
        this.assets = props.attributes.assets;
        this.adminMenu = props.attributes.adminMenu;
        this._editorStatus = props.attributes.editorStatus;

        this._editorStatus.addBlockInstance(this.props.clientId);

        this._onChange = this._onChange.bind(this);
    }

    componentDidMount() {
        this.assets.assetsPromise.then(
            () => {this.initialize()},
            exception => {this.showAssetsLoadError()}
        )
    }

    componentWillUnmount() {
        if (this._editorStatus.isEditorRunningInBlock(this.props.clientId)) {
            this.stopEditor();
        }
    }

    initialize() {
        this._editorStatus.maySetEditor(window.SetkaEditor);

        if(this._editorStatus.isEditorRunning()) {
            return;
        }

        // Fold admin menu (left column) if page less than 1340px and greater than 782 in width
        // On medium screens all panels automatically collapse.
        if(document.body.clientWidth < 1340 && select('core/viewport').isViewportMatch('>= medium')) {
            this.adminMenu.fold();

            if(document.body.clientWidth < 1216) {
                dispatch('core/edit-post').closeGeneralSidebar();
            }
        }

        try {
            this.startEditor();
            this.setupIsSelected();
        } catch (error) {
            this.showErrors();
        }
    }

    /**
     * @throws {Error}
     */
    startEditor() {
        window.SetkaEditor.start(
            {
                ...this.assets.getConfig(),
                ...{
                    onPostContentChange: this.saveContent,
                }
            },
            this.assets.assets
        );

        if(this.isContentExists()) {
            this.replaceContentAndAssets(this.props.attributes.content);
        }

        this._onChangeUnsubscribe = subscribe(this._onChange);
        this._editorStatus.setEditorRunningOn(this.props.clientId);
    }

    stopEditor() {
        this._editorStatus.setEditorRunningOff(this.props.clientId);
        this._onChangeUnsubscribe();
        window.SetkaEditor.stop();
    }

    _onChange() {
        if(this._editorStatus.isEditorRunningInBlock(this.props.clientId) &&
            'function' === typeof window.SetkaEditor.setHeaderTopOffset &&
            'function' === typeof window.SetkaEditor.setFooterBottomOffset
        ) {
            window.SetkaEditor.setHeaderTopOffset(this.assets.getHeaderTopOffset());
            window.SetkaEditor.setFooterBottomOffset(this.assets.getFooterBottomOffset());
        }
    }

    /**
     * This method created for support undo/redo events.
     *
     * @param prevProps {Object} Previous properties value.
     *
     * @see handleAssets
     */
    componentDidUpdate(prevProps) {
        this.setupIsSelected();

        if(this._editorStatus.isEditorRunningInBlock(this.props.clientId)
            &&
            this.isContentExists()
            &&
            'string' === typeof this.contentSavedIntoProps
            &&
            this.contentSavedIntoProps !== this.props.attributes.content
            &&
            prevProps.attributes.content !== this.props.attributes.content
        ) {
            try {
                this.replaceContentAndAssets(this.props.attributes.content);
            } catch (error) {
                this.showErrors(error);
            }
        }
    }

    /**
     * Called by Setka Editor. Puts content and meta from Setka Editor to Gutenberg.
     */
    saveContent() {
        if(!this._editorStatus.isEditorRunningInBlock(this.props.clientId)) {
            return;
        }

        let theme = window.SetkaEditor.getCurrentTheme();
        let attributes = {
            content: window.SetkaEditor.getHTML(),
            setkaEditorTheme: theme.id,
            setkaEditorLayout: window.SetkaEditor.getCurrentLayout().id,
        };

        this.contentSavedIntoProps = attributes.content;
        this.props.setAttributes(attributes);
    }

    /**
     * Replace content and assets in Setka Editor.
     *
     * @param {string} content
     */
    replaceContentAndAssets(content) {
        window.SetkaEditor.replaceHTML(content);
    }

    render() {
        switch (this.state.renderComponent) {
            case 'warning':
            default:
                return <Warning key="setka-warning" actions={[
                    <wp.components.Button key="show-html-editor" isLarge onClick={ this.showHtmlEditor }>{ __('Show HTML', 'setka-editor') }</wp.components.Button>,
                    <wp.components.Button key="support" isLarge onClick={ () => { window.open('https://editor-help.setka.io') } }>{ __('Help Center', 'setka-editor') }</wp.components.Button>,
                    <wp.components.Button key="settings" isLarge onClick={ () => { window.open(setkaEditorGutenbergModules.settingsUrl) } }>{ __('Plugin Settings', 'setka-editor') }</wp.components.Button>,
                ]}>
                    <RawHTML>{ this.assets.getAssetsError().message }</RawHTML>
                </Warning>;

            case 'assets-error':
                return <Warning key="setka-assets-error">
                    <RawHTML>{ this.assets.getAssetsError().message }</RawHTML>
                </Warning>;

            case 'html-editor':
                return <PlainText
                    value={ this.props.attributes.content }
                    onChange={ (content) => this.props.setAttributes({ content }) } />;

            case 'editor':
                return <div>
                    <div key="editor" id="setka-editor" className="stk-editor"/>
                </div>;
        }
    }

    showHtmlEditor = () => {
        this.setState({renderComponent: 'html-editor'});
    }

    showErrors() {
        this.stopEditor();
        this.setState({renderComponent: 'warning'});
    }

    showAssetsLoadError() {
        this.setState({renderComponent: 'assets-error'});
    }

    /**
     * @return {boolean}
     */
    isContentExists() {
        return 'string' === typeof this.props.attributes.content && '' !== this.props.attributes.content;
    }

    /**
     * Setup additional CSS class for <html> tag if block selected.
     */
    setupIsSelected() {
        document.documentElement.classList.toggle(BLOCK_SELECTED_HTML_CLASS, this.props.isSelected);
    }
}
