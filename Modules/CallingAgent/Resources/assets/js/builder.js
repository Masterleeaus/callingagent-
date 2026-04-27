(function () {
  const root = document.querySelector('[data-calling-agent-builder]');
  if (!root) return;
  root.querySelectorAll('[data-builder-tab]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = button.getAttribute('data-builder-tab');
      root.querySelectorAll('[data-builder-panel]').forEach((panel) => {
        panel.hidden = panel.getAttribute('data-builder-panel') !== target;
      });
    });
  });
})();
