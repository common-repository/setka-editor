import SetkaEditor from './SetkaEditor';
import EditorAssets from './EditorAssets';
import AdminMenu from './AdminMenu';
import SetkaIcon from './SetkaIcon';
import EditorStatus from './EditorStatus';

const { RawHTML } = wp.element;
const { __ } = wp.i18n;
const { dispatch } = wp.data;
const { registerBlockType } = wp.blocks;

const alignVariants = ['left', 'center', 'right', 'wide', 'full'];

const assets = new EditorAssets(setkaEditorGutenbergModules.settings.editorConfig, setkaEditorGutenbergModules.wpVersion);

let promise = assets.startLoading(setkaEditorGutenbergModules.settings.themeData);
promise.catch(exception => {
    dispatch('core/notices').createErrorNotice(exception.message, {__unstableHTML: true});
});
assets.setAssetsPromise(promise);

const adminMenu = new AdminMenu();
const editorStatus = new EditorStatus();

registerBlockType('setka-editor/setka-editor', {

    title: __('Setka Editor', 'setka-editor'),

    description: __('Setka Editor allows content teams to create unique layouts that perfectly fit each story without having to code. It also allows you to customize your design elements so your brand style can shine through.', 'setka-editor'),

    keywords: [
        __('template', 'setka-editor'),
        __('column', 'setka-editor'),
        __('animation', 'setka-editor'),
    ],

    icon: SetkaIcon,

    category: 'layout',

    attributes: {
        content: {
            type: 'string',
            source: 'html',
            selector: 'div',
        },
        align: {
            type: 'string',
            default: 'full',
        },
        setkaEditorTheme: {
            type: 'string',
        },
        setkaEditorLayout: {
            type: 'string',
        },
        assets: {
            type: 'object',
            default: assets,
        },
        adminMenu: {
            type: 'object',
            default: adminMenu,
        },
        editorStatus: {
            type: 'object',
            default: editorStatus,
        },
    },

    supports: {
        multiple: false,
        className: false,
        customClassName: false,
        align: ['wide', 'full'],
    },

    edit: SetkaEditor,

    getEditWrapperProps(attributes) {
        const { align } = attributes;

        if (alignVariants.includes(align)) {
            return {'data-align': align};
        }
    },

    save({ attributes }) {
        // Converts attributes names from 'srcSet' -> 'srcset' and <img /> -> <img>.
        let container = document.createElement('div');
        container.innerHTML = attributes.content;
        return <RawHTML>{ container.innerHTML }</RawHTML>;
    },
});
