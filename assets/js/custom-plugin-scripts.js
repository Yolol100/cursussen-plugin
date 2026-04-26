(function ($) {
    'use strict';

    $(function () {
        $('[data-sda-cursussen]').each(function () {
            var $wrapper = $(this);

            $wrapper.on('click', '.sda-cursussen__toggle', function () {
                var $button = $(this);
                var panelId = $button.attr('aria-controls');
                var $panel = panelId ? $('#' + panelId) : $button.closest('.sda-cursussen__item').find('.sda-cursussen__panel').first();
                var isOpen = $button.attr('aria-expanded') === 'true';

                $button.attr('aria-expanded', isOpen ? 'false' : 'true');
                $button.find('.sda-cursussen__toggle-icon').text(isOpen ? '+' : '−');
                $panel.prop('hidden', isOpen);
                $button.closest('.sda-cursussen__item').toggleClass('is-open', !isOpen);
            });
        });
    });
})(jQuery);
