document.addEventListener('DOMContentLoaded', function() {
    const panel = document.getElementById('controlPanel');
    const toggle = document.getElementById('panelToggle');
    
    // Restore panel state from localStorage if it exists
    if (localStorage.getItem('panelCollapsed') === 'true') {
        panel.classList.add('collapsed');
        toggle.checked = false;
    }
    
    toggle.addEventListener('click', function() {
        panel.classList.toggle('collapsed');
        localStorage.setItem('panelCollapsed', panel.classList.contains('collapsed'));
    });
});
