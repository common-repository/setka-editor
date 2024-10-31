export default class AssetsLoadError extends Error {

    fetchError

    constructor(message, fetchError = null) {
        super(message);
        this.fetchError = fetchError;
    }

    static create(fetchError = null) {
        return new this(this.findMessage(), fetchError);
    }

    static findMessage() {
        let notices = this.getNotices();
        for (let i = 0; i < notices.length; i++) {
            if ('setka-editor-assets-load-error' === notices[i].name) {
                return notices[i].content;
            }
        }
        throw new Error('Not found required notice');
    }

    static getNotices() {
        return setkaEditorGutenbergModules.notices;
    }
}
