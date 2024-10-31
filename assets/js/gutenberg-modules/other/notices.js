import { isEmpty } from 'lodash';
const { dispatch } = wp.data;

function prepareNotices() {
    if(isEmpty(setkaEditorGutenbergModules.notices)) {
        return;
    }

    setkaEditorGutenbergModules.notices.forEach((notice) => {

        if (true !== notice.relevant) {
            return;
        }

        dispatch('core/notices').createNotice(
            notice.status,
            notice.content,
            {
                speak: false,
                __unstableHTML: true,
                isDismissible: notice.isDismissible,
            }
        );
    });
}
prepareNotices();
