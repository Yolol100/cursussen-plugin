(function () {
    'use strict';

    function toggleAvailablePlaces(select) {
        var targetId = select.getAttribute('data-cursussen-toggle-target');
        var row = targetId ? document.getElementById(targetId) : null;
        if (!row) {
            return;
        }
        row.style.display = select.value === 'Vol' ? 'none' : 'table-row';
    }

    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('inschrijven');
        if (!select) {
            return;
        }

        toggleAvailablePlaces(select);
        select.addEventListener('change', function () {
            toggleAvailablePlaces(select);
        });
    });
})();
