(function () {
  const root = document.querySelector('[data-calling-agent-builder]');
  if (!root) return;

  // ── Tab switching ──────────────────────────────────────────────────────────
  root.querySelectorAll('[data-builder-tab]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = button.getAttribute('data-builder-tab');
      root.querySelectorAll('[data-builder-panel]').forEach((panel) => {
        panel.hidden = panel.getAttribute('data-builder-panel') !== target;
      });
      root.querySelectorAll('.ca-tab-btn').forEach((btn) => {
        btn.classList.toggle('ca-tab-active', btn === button);
      });
    });
  });

  // ── Agent selection ────────────────────────────────────────────────────────
  let currentAgentId = null;

  function getSaveUrl() {
    if (!currentAgentId) return null;
    return root.dataset.builderSaveUrl.replace('__AGENT_ID__', currentAgentId);
  }

  function getLoadUrl() {
    if (!currentAgentId) return null;
    return root.dataset.builderLoadUrl.replace('__AGENT_ID__', currentAgentId);
  }

  function getAssignUrl() {
    if (!currentAgentId) return null;
    return root.dataset.builderAssignUrl.replace('__AGENT_ID__', currentAgentId);
  }

  // ── Helpers ────────────────────────────────────────────────────────────────
  function setStatus(msg, color) {
    const el = document.getElementById('ca-save-status');
    if (el) { el.textContent = msg; el.style.color = color || '#64748b'; }
  }

  function getCSRFToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function getFieldValue(field) {
    if (field.type === 'checkbox') return field.checked;
    return field.value;
  }

  /**
   * Collect all [data-builder-field] inputs into a nested object.
   * A field named "settings.persona" becomes { settings: { persona: value } }.
   */
  function collectFormData() {
    const data = {};
    root.querySelectorAll('[data-builder-field]').forEach((field) => {
      const path = field.getAttribute('data-builder-field').split('.');
      let cursor = data;
      for (let i = 0; i < path.length - 1; i++) {
        if (!cursor[path[i]]) cursor[path[i]] = {};
        cursor = cursor[path[i]];
      }
      cursor[path[path.length - 1]] = getFieldValue(field);
    });
    return data;
  }

  /**
   * Populate form fields from a loaded settings object.
   * Supports nested paths like "settings.persona".
   */
  function populateFormData(payload) {
    root.querySelectorAll('[data-builder-field]').forEach((field) => {
      const path = field.getAttribute('data-builder-field').split('.');
      let value = payload;
      for (const key of path) {
        if (value == null) return;
        value = value[key];
      }
      if (value == null) return;
      if (field.type === 'checkbox') {
        field.checked = Boolean(value);
      } else {
        field.value = typeof value === 'object' ? JSON.stringify(value) : value;
      }
    });
  }

  // ── Load agent settings ────────────────────────────────────────────────────
  function loadAgent(agentId) {
    const url = root.dataset.builderLoadUrl.replace('__AGENT_ID__', agentId);
    setStatus('Loading…');
    fetch(url, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
      credentials: 'same-origin',
    })
      .then((r) => r.json())
      .then((data) => {
        // Merge agent top-level + settings into one flat search object
        const merged = Object.assign({}, data.agent || {}, { settings: data.settings || {} });
        populateFormData(merged);

        // Show webhook URLs
        const urlsEl = document.getElementById('ca-webhook-urls');
        if (urlsEl && data.webhook_urls) {
          urlsEl.textContent = Object.entries(data.webhook_urls)
            .map(([k, v]) => k.padEnd(16) + v)
            .join('\n');
        }

        setStatus('Settings loaded ✓', '#10b981');
      })
      .catch((err) => {
        setStatus('Failed to load: ' + err.message, '#ef4444');
      });
  }

  // ── Save agent settings ────────────────────────────────────────────────────
  function saveAgent() {
    const url = getSaveUrl();
    if (!url) { setStatus('Select an agent first.', '#f59e0b'); return; }
    setStatus('Saving…');
    const data = collectFormData();
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCSRFToken(),
      },
      credentials: 'same-origin',
      body: JSON.stringify(data),
    })
      .then((r) => r.json())
      .then((resp) => {
        if (resp.success) {
          setStatus('Saved ✓', '#10b981');
        } else {
          setStatus('Save failed', '#ef4444');
        }
      })
      .catch((err) => {
        setStatus('Error: ' + err.message, '#ef4444');
      });
  }

  // ── Assign phone number ────────────────────────────────────────────────────
  const assignBtn = document.getElementById('ca-assign-number-btn');
  if (assignBtn) {
    assignBtn.addEventListener('click', () => {
      const url = getAssignUrl();
      if (!url) { setStatus('Select an agent first.', '#f59e0b'); return; }
      const number = document.getElementById('ca-phone-number').value.trim();
      if (!number) { setStatus('Enter a phone number first.', '#f59e0b'); return; }
      setStatus('Assigning…');
      fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken(),
        },
        credentials: 'same-origin',
        body: JSON.stringify({ number }),
      })
        .then((r) => r.json())
        .then((resp) => {
          if (resp.success) {
            setStatus('Number assigned ✓', '#10b981');
            // Refresh webhook URLs
            if (resp.webhook_urls) {
              const urlsEl = document.getElementById('ca-webhook-urls');
              if (urlsEl) {
                urlsEl.textContent = Object.entries(resp.webhook_urls)
                  .map(([k, v]) => k.padEnd(16) + v)
                  .join('\n');
              }
            }
          } else {
            setStatus('Assignment failed', '#ef4444');
          }
        })
        .catch((err) => setStatus('Error: ' + err.message, '#ef4444'));
    });
  }

  // ── Agent selector change ──────────────────────────────────────────────────
  const agentSelect = root.querySelector('[data-builder-agent-select]');
  if (agentSelect) {
    agentSelect.addEventListener('change', () => {
      currentAgentId = agentSelect.value || null;
      if (currentAgentId) loadAgent(currentAgentId);
    });
    // Auto-load if only one option
    if (agentSelect.options.length === 2) {
      agentSelect.selectedIndex = 1;
      agentSelect.dispatchEvent(new Event('change'));
    }
  }

  // ── Save button ────────────────────────────────────────────────────────────
  const saveBtn = document.getElementById('ca-save-btn');
  if (saveBtn) {
    saveBtn.addEventListener('click', saveAgent);
  }
})();
