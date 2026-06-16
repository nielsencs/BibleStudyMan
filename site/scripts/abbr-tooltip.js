// abbr-tooltip.js
// Enables long-press tooltips for <abbr> elements on touch devices
(function() {
  let tooltipBox = null;
  let longPressTimer = null;
  let currentAbbr = null;

  function showTooltip(target, text) {
    hideTooltip();
    tooltipBox = document.createElement('div');
    tooltipBox.className = 'abbr-tooltip-box';
    tooltipBox.innerHTML = `<span>${text}</span><button class="abbr-tooltip-close" aria-label="Close">×</button>`;
    document.body.appendChild(tooltipBox);
    // Position near the abbr
    const rect = target.getBoundingClientRect();
    tooltipBox.style.left = `${rect.left + window.scrollX}px`;
    tooltipBox.style.top = `${rect.bottom + window.scrollY + 8}px`;
    // Close button
    tooltipBox.querySelector('.abbr-tooltip-close').onclick = hideTooltip;
    // Tap outside closes
    setTimeout(() => {
      document.addEventListener('touchstart', outsideHandler, { once: true });
    }, 0);
  }

  function hideTooltip() {
    if (tooltipBox) {
      tooltipBox.remove();
      tooltipBox = null;
      currentAbbr = null;
    }
  }

  function outsideHandler(e) {
    if (tooltipBox && !tooltipBox.contains(e.target)) {
      hideTooltip();
    }
  }

  function isTouchDevice() {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
  }

  function addLongPress(abbr) {
    abbr.addEventListener('touchstart', function(e) {
      if (longPressTimer) clearTimeout(longPressTimer);
      longPressTimer = setTimeout(() => {
        currentAbbr = abbr;
        showTooltip(abbr, abbr.title);
      }, 500); // 500ms long press
    });
    abbr.addEventListener('touchend', function(e) {
      if (longPressTimer) clearTimeout(longPressTimer);
    });
    abbr.addEventListener('touchmove', function(e) {
      if (longPressTimer) clearTimeout(longPressTimer);
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    if (!isTouchDevice()) return;
    document.querySelectorAll('abbr[title]').forEach(addLongPress);
  });
})();
