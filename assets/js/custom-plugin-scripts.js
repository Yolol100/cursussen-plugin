jQuery($ => { 
    // Selectors definiëren
    const toggleSelector = '.cursus-toggle-item';
    const buttonSelector = '.cursus-toggle-button';
    const contentSelector = '.cursus-toggle-content';
    const iconSelector = '.toggle-icon';
    const inschrijvenSelector = '.custom-inschrijven-btn'; // Selector voor inschrijfknop

    // Initialisatie: Open alle toggles bij het laden van de pagina
    $(toggleSelector).each(function() {
        const $toggleItem = $(this);
        const $content = $toggleItem.find(contentSelector);
        const $icon = $toggleItem.find(iconSelector);

        // Voeg de 'active' klasse toe
        $toggleItem.addClass('active');
        // Zorg dat de content zichtbaar is
        $content.show();
        // Stel het icoon in op '-'
        $icon.text('-');
    });

    // Click event voor toggles
    $(buttonSelector).off('click').on('click', function (e) {
        e.preventDefault();

        const $button = $(this);
        const $parent = $button.closest(toggleSelector);
        const $content = $parent.find(contentSelector);
        const $icon = $button.find(iconSelector);

        // Toggle de actieve status zonder andere toggles te beïnvloeden
        if ($parent.hasClass('active')) {
            // Als het item al actief is, sluit het
            $parent.removeClass('active');
            $content.stop(true, true).slideUp('fast');
            // Wijzig het icoon naar '+'
            $icon.text('+');
        } else {
            // Open het huidige item
            $parent.addClass('active');
            $content.stop(true, true).slideDown('fast');
            // Wijzig het icoon naar '-'
            $icon.text('-');
        }
    });

    // Aanpassen van inschrijven-knoppen op basis van inschrijfstatus
    $(inschrijvenSelector).each(function () {
        const $button = $(this);
        const inschrijven = $button.data('inschrijven'); // Haalt de inschrijfstatus op (Geen plekken of inschrijven)

        // Pas de knop aan afhankelijk van de inschrijfstatus
        if (inschrijven === 'Geen plekken') {
            // Cursus heeft geen plekken, knop blijft zichtbaar maar uitgeschakeld
            $button
                .text('Geen plekken')
                .addClass('disabled')
                .attr('disabled', true)
                .css('opacity', '0.5'); // Zorgt voor een visueel verschil
        } else {
            // Cursus is open voor inschrijving
            $button
                .text('Inschrijven')
                .removeClass('disabled')
                .removeAttr('disabled')
                .css('opacity', '1'); // Zorg ervoor dat de knop volledig zichtbaar is
        }
    });
});