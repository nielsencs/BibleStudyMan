document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('controlPanel');
    const searchSection = document.getElementById('searchSection');
    const wordsSection = document.getElementById('wordsSection');

    const panelToggle = document.getElementById('panelToggle');
    const wordsToggle = document.getElementById('wordsToggle');

    if (!panel || !searchSection || !wordsSection || !panelToggle || !wordsToggle) {
        return;
    }

    function setSectionVisible(section, visible) {
        section.classList.toggle('collapsed', !visible);
    }

    function refreshPanel() {
        const showSearch = !!panelToggle.checked;
        const showWords = !!wordsToggle.checked;

        setSectionVisible(searchSection, showSearch);
        setSectionVisible(wordsSection, showWords);
        panel.classList.toggle('collapsed', !showSearch && !showWords);
    }

    function restoreToggle(toggle, storageKey, defaultValue) {
        const saved = localStorage.getItem(storageKey);
        toggle.checked = (saved === null) ? defaultValue : (saved === 'true');
    }

    function saveAndRefresh(toggle, storageKey) {
        localStorage.setItem(storageKey, toggle.checked ? 'true' : 'false');
        refreshPanel();
    }

    restoreToggle(panelToggle, 'searchVisible', true);
    restoreToggle(wordsToggle, 'wordsVisible', true);

    refreshPanel();

    panelToggle.addEventListener('change', function () {
        saveAndRefresh(panelToggle, 'searchVisible');
    });

    wordsToggle.addEventListener('change', function () {
        saveAndRefresh(wordsToggle, 'wordsVisible');
    });
});

// Plural you choice: remembered in this browser (LUKE #194). The page URL
// carries it (pluralYou=...) so server-rendered links and searches follow it.
function savePluralYou(tValue) {
    localStorage.setItem('pluralYou', tValue);
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('pluralYou');
    if (!select) {
        return;
    }
    const url = new URL(window.location.href);
    const saved = localStorage.getItem('pluralYou');
    if (!url.searchParams.has('pluralYou') && saved && saved !== 'youall' && url.search.length > 1) {
        const known = Array.prototype.some.call(select.options, function (o) { return o.value === saved; });
        if (known) {
            url.searchParams.set('pluralYou', saved);
            window.location.replace(url.toString());
        }
    }
});
