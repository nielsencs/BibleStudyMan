document.addEventListener('DOMContentLoaded', function() {
    const panel = document.getElementById('controlPanel');
    const planSection = document.getElementById('planSection');
    const findSection = document.getElementById('findSection');
    const prefsSection = document.getElementById('prefsSection');
    
    const planToggle = document.getElementById('planToggle');
    const findToggle = document.getElementById('findToggle');
    const prefsToggle = document.getElementById('prefsToggle');

    if (!planToggle || !findToggle || !prefsToggle || !panel) {
        return;
    }

    function refreshPanel() {
        // const showSearch = planToggle.checked || findToggle.checked; // Show search section if either plan or find is checked
        const showPlan = planToggle.checked;
        const showFind = findToggle.checked;
        const showPrefs = prefsToggle.checked;

        if (showPlan) {
            planSection.classList.remove('collapsed');
        } else {
            planSection.classList.add('collapsed');
        }

        if (showFind) {
            findSection.classList.remove('collapsed');
        } else {
            findSection.classList.add('collapsed');
        }

        if (showPrefs) {
            prefsSection.classList.remove('collapsed');
        } else {
            prefsSection.classList.add('collapsed');
        }

        if (!showPlan && !showFind && !showPrefs) {
            panel.classList.add('collapsed');
        } else {
            panel.classList.remove('collapsed');
        }
    }

    // Restore states from localStorage
    if (localStorage.getItem('searchVisible') !== null) {
        // panelToggle.checked = localStorage.getItem('searchVisible') === 'true';
        planToggle.checked = localStorage.getItem('planVisible') === 'true';
        findToggle.checked = localStorage.getItem('findVisible') === 'true';
    }
    if (localStorage.getItem('prefsVisible') !== null) {
        prefsToggle.checked = localStorage.getItem('prefsVisible') === 'true';
    }

    refreshPanel();

    planToggle.addEventListener('change', function() {
        localStorage.setItem('planVisible', planToggle.checked);
        refreshPanel();
    });

    findToggle.addEventListener('change', function() {
        localStorage.setItem('findVisible', findToggle.checked);
        refreshPanel();
    });

    prefsToggle.addEventListener('change', function() {
        localStorage.setItem('prefsVisible', prefsToggle.checked);
        refreshPanel();
    });
});
