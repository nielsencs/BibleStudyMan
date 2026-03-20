document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('controlPanel');
    const planSection = document.getElementById('planSection');
    const findSection = document.getElementById('findSection');
    const prefsSection = document.getElementById('prefsSection');

    const planToggle = document.getElementById('planToggle');
    const findToggle = document.getElementById('findToggle');
    const prefsToggle = document.getElementById('prefsToggle');

    if (!panel || !planSection || !findSection || !prefsSection ||
        !planToggle || !findToggle || !prefsToggle) {
        return;
    }

    function setSectionVisible(section, visible) {
        section.classList.toggle('collapsed', !visible);
    }

    function refreshPanel() {
        const showPlan = !!planToggle.checked;
        const showFind = !!findToggle.checked;
        const showPrefs = !!prefsToggle.checked;

        setSectionVisible(planSection, showPlan);
        setSectionVisible(findSection, showFind);
        setSectionVisible(prefsSection, showPrefs);

        panel.classList.toggle('collapsed', !showPlan && !showFind && !showPrefs);
    }

    function restoreToggle(toggle, storageKey, defaultValue) {
        const saved = localStorage.getItem(storageKey);
        toggle.checked = (saved === null) ? defaultValue : (saved === 'true');
    }

    function saveAndRefresh(toggle, storageKey) {
        localStorage.setItem(storageKey, toggle.checked ? 'true' : 'false');
        refreshPanel();
    }

    restoreToggle(planToggle, 'planVisible', true);
    restoreToggle(findToggle, 'findVisible', true);
    restoreToggle(prefsToggle, 'prefsVisible', false);

    refreshPanel();

    planToggle.addEventListener('change', function () {
        saveAndRefresh(planToggle, 'planVisible');
    });

    findToggle.addEventListener('change', function () {
        saveAndRefresh(findToggle, 'findVisible');
    });

    prefsToggle.addEventListener('change', function () {
        saveAndRefresh(prefsToggle, 'prefsVisible');
    });
});