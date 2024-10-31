import { parse as queryParse } from  'query-string';
const { dispatch, select, subscribe } = wp.data;
const { createBlock } = wp.blocks;

let query = queryParse(window.location.search);

if('object' === typeof query && 'undefined' !== typeof query[setkaEditorGutenbergModules.name + '-auto-init']) {
    let callback = () => {
        if(select('core/editor').isEditedPostEmpty()
            &&
            select('core/editor').isEditedPostNew()
        ) {
            unsubscribe();
            let block = createBlock('setka-editor/setka-editor');
            dispatch('core/editor').insertBlocks(block);
        }
    };

    let unsubscribe = subscribe(callback);
}
