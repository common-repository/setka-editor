
import UnexistedBlockInstanceError from './errors/UnexistedBlockInstanceError';

export default class EditorStatus {

    /**
     * @var {object|null}
     */
    _editor;

    /**
     * @type {boolean}
     * @private
     */
    _editorRunning = false;

    /**
     * @type {object}
     * @private
     */
    _blockInstances = {};

    /**
     * @type {string|null}
     * @private
     */
    _blockInstanceActiveID;

    /**
     * @return {Object|null}
     */
    getEditor() {
        return this._editor;
    }

    /**
     * @param {Object} editor
     */
    maySetEditor(editor) {
        if(!this._editor) {
            this._editor = editor;
        }
    }

    /**
     * @param {string} id
     * @throws {UnexistedBlockInstanceError}
     */
    setEditorRunningOn(id) {
        this._editorRunning = true;

        if(this._checkBlockInstance(id)) {
            this._blockInstanceActiveID = id;
        }
    }

    /**
     * @param {string} id
     * @throws {UnexistedBlockInstanceError}
     */
    setEditorRunningOff(id) {
        if(this._checkBlockInstance(id)) {
            this._blockInstanceActiveID = null;
        }
        this._editorRunning = false;
    }

    /**
     * @return {boolean}
     */
    isEditorRunning() {
        if (this._editor && typeof this._editor.isRunning === 'function') {
            return this._editor.isRunning() && this._editorRunning;
        } else {
            return this._editorRunning;
        }
    }

    /**
     * @param {string} id
     * @return {boolean}
     */
    isEditorRunningInBlock(id) {
        return this.isEditorRunning() && this._blockInstanceActiveID === id;
    }

    /**
     * @param {string} id
     */
    addBlockInstance(id) {
        this._blockInstances[id] = null;
    }

    /**
     * @param {string} id
     * @throws {UnexistedBlockInstanceError}
     */
    removeBlockInstance(id) {
        if (this._checkBlockInstance(id)) {
            delete this._blockInstances[id];
        }
    }

    /**
     * @param {string} id
     * @throws {UnexistedBlockInstanceError}
     * @return {boolean}
     * @private
     */
    _checkBlockInstance(id) {
        if (this._blockInstances.hasOwnProperty(id)) {
            return true;
        } else {
            throw new UnexistedBlockInstanceError();
        }
    }
}
