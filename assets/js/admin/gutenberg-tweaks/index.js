document.addEventListener('DOMContentLoaded', function() {
    let dropdown = document.querySelector('#split-page-title-action .dropdown');

    if (!dropdown || !dropdown.firstChild) {
        return;
    }

    let link = document.createElement('a');
    link.setAttribute('href', dropdown.firstChild.getAttribute('href') + '?setka-editor-auto-init');
    link.innerText = 'Setka Editor';

    dropdown.appendChild(link);
});
