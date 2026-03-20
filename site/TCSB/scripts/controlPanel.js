document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('controlPanel');

    const sections = [
        { button: document.getElementById('planToggle'),  section: document.getElementById('planSection'),  key: 'planVisible' },
        { button: document.getElementById('findToggle'),  section: document.getElementById('findSection'),  key: 'findVisible' },
        { button: document.getElementById('prefsToggle'), section: document.getElementById('prefsSection'), key: 'prefsVisible' }
    ];

    if (!panel) {
        return;
    }

    sections.forEach(function (item, index) {
        if (!item.button || !item.section) {
            return;
        }

        let isOpen = localStorage.getItem(item.key);
        if (isOpen === null) {
            isOpen = (index === 0); // default: Plan open, others closed
        } else {
            isOpen = (isOpen === 'true');
        }

        item.button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        item.section.classList.toggle('collapsed', !isOpen);

        item.button.addEventListener('click', function () {
            const nowOpen = item.button.getAttribute('aria-expanded') !== 'true';
            item.button.setAttribute('aria-expanded', nowOpen ? 'true' : 'false');
            item.section.classList.toggle('collapsed', !nowOpen);
            localStorage.setItem(item.key, nowOpen);
        });
    });
});