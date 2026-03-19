document.addEventListener('DOMContentLoaded', function() {
    const panel = document.getElementById('controlPanel');
    const searchSection = document.getElementById('searchSection');
    const wordsSection = document.getElementById('wordsSection');
    
    const panelToggle = document.getElementById('panelToggle');
    const wordsToggle = document.getElementById('wordsToggle');

    if (!panelToggle || !wordsToggle || !panel) {
        return;
    }

    function refreshPanel() {
        const showSearch = panelToggle.checked;
        const showWords = wordsToggle.checked;

        if (showSearch) {
            searchSection.classList.remove('collapsed');
        } else {
            searchSection.classList.add('collapsed');
        }

        if (showWords) {
            wordsSection.classList.remove('collapsed');
        } else {
            wordsSection.classList.add('collapsed');
        }

        if (!showSearch && !showWords) {
            panel.classList.add('collapsed');
        } else {
            panel.classList.remove('collapsed');
        }
    }

    // Restore states from localStorage
    if (localStorage.getItem('searchVisible') !== null) {
        panelToggle.checked = localStorage.getItem('searchVisible') === 'true';
    }
    if (localStorage.getItem('wordsVisible') !== null) {
        wordsToggle.checked = localStorage.getItem('wordsVisible') === 'true';
    }

    refreshPanel();

    panelToggle.addEventListener('change', function() {
        localStorage.setItem('searchVisible', panelToggle.checked);
        refreshPanel();
    });

    wordsToggle.addEventListener('change', function() {
        localStorage.setItem('wordsVisible', wordsToggle.checked);
        refreshPanel();
    });
});
