/**
 * Class for interate with Admin Menu in WordPress.
 */
export default class AdminMenu {

    collapseButton = null

    constructor() {
        window.addEventListener('DOMContentLoaded', () => {
            this.collapseButton = document.getElementById('collapse-button');
        });

    }

    /**
     * Shows state of Admin Menu.
     *
     * @return {boolean} True if menu is folded.
     */
    isFolded() {
        return document.body.classList.contains('folded');
    }

    fold() {
        if(!this.isFolded()) {
            let event = this.createClickEvent();
            this.collapseButton
                .dispatchEvent(event);
        }
        return this;
    }

    unFold() {
        if(this.isFolded()) {
            let event = this.createClickEvent();
            this.collapseButton
                .dispatchEvent(event);
        }
        return this;
    }

    /**
     * Creates a click event for WordPress.
     *
     * @return {Event} Click event.
     */
    createClickEvent() {
        let event = new Event('click');
        event.namespace = 'collapse-menu';
        return event;
    }
}
