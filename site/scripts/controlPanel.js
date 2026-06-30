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
