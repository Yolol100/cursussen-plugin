(function () {
    'use strict';

    function getPanel(button) {
        var panelId = button.getAttribute('aria-controls');
        if (panelId) {
            return document.getElementById(panelId);
        }

        var item = button.closest('.sda-cursussen__item');
        return item ? item.querySelector('.sda-cursussen__panel') : null;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-sda-cursussen]').forEach(function (wrapper) {
            wrapper.addEventListener('click', function (event) {
                var button = event.target.closest('.sda-cursussen__toggle');
                if (!button || !wrapper.contains(button)) {
                    return;
                }

                var panel = getPanel(button);
                var item = button.closest('.sda-cursussen__item');
                var icon = button.querySelector('.sda-cursussen__toggle-icon');
                var isOpen = button.getAttribute('aria-expanded') === 'true';

                button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

                if (icon) {
                    icon.textContent = isOpen ? '+' : '−';
                }

                if (panel) {
                    panel.hidden = isOpen;
                }

                if (item) {
                    item.classList.toggle('is-open', !isOpen);
                }
            });
        });
    });
})();
