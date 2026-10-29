document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('controlPanel');
    const planSection = document.getElementById('planSection');
    const findSection = document.getElementById('findSection');
    const prefsSection = document.getElementById('prefsSection');

    const findToggle = document.getElementById('findToggle');
    const prefsToggle = document.getElementById('prefsToggle');

    if (!panel || !planSection || !findSection || !prefsSection ||
        !findToggle || !prefsToggle) {
        return;
    }

    function setSectionVisible(section, visible) {
        section.classList.toggle('collapsed', !visible);
    }

    function refreshPanel() {
        const showSearch = !!findToggle.checked;
        const showPrefs = !!prefsToggle.checked;

        setSectionVisible(planSection, showSearch);
        setSectionVisible(findSection, showSearch);
        setSectionVisible(prefsSection, showPrefs);

        panel.classList.toggle('collapsed', !showSearch && !showPrefs);
    }

    function restoreToggle(toggle, storageKey, defaultValue) {
        const saved = localStorage.getItem(storageKey);
        toggle.checked = (saved === null) ? defaultValue : (saved === 'true');
    }

    function saveAndRefresh(toggle, storageKey) {
        localStorage.setItem(storageKey, toggle.checked ? 'true' : 'false');
        refreshPanel();
    }

    restoreToggle(findToggle, 'findVisible', true);
    restoreToggle(prefsToggle, 'prefsVisible', false);

    refreshPanel();

    findToggle.addEventListener('change', function () {
        saveAndRefresh(findToggle, 'findVisible');
    });

    prefsToggle.addEventListener('change', function () {
        saveAndRefresh(prefsToggle, 'prefsVisible');
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
