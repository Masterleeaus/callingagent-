/**
 * Titan Go — Voice IO + Session Layer
 * - NO reasoning, NO intent inference, NO execution.
 * - Forwards user input verbatim to Titan Zero.
 * - Renders and speaks Titan Zero output verbatim.
 */
(function () {
  const root = document.getElementById('titan-go-root');
  if (!root) return;

  const chatbotUuid = root.dataset.uuid;
  const sessionId = root.dataset.sessionId || (crypto.randomUUID ? crypto.randomUUID() : String(Date.now()));
  const mode = root.dataset.mode || 'go';
  const context = root.dataset.context || 'unknown';
  const silencePromptMs = parseInt(root.dataset.silencePromptMs || '120000', 10);
  const silenceEndMs = parseInt(root.dataset.silenceEndMs || '240000', 10);
  const proUrlDefault = root.dataset.proUrl || '/dashboard';
  const beepEnabled = root.dataset.beep === '1';
  const holdToTalk = root.dataset.holdToTalk === '1';
  const restrictButtons = root.dataset.restrictButtons === '1';
  const streamingEnabled = root.dataset.streamingEnabled === '1';

  const micBtn = document.getElementById('titan-go-mic-btn');
  const retryBtn = document.getElementById('titan-go-retry-btn');
  const stopBtn = document.getElementById('titan-go-stop-btn');
  const transcriptEl = document.getElementById('titan-go-transcript');
  const inputEl = document.getElementById('titan-go-text-input');
  const messagesEl = document.getElementById('titan-go-messages');

  const ALL_BUTTONS = ['YES', 'NO', 'REPEAT', 'CANCEL', 'OPEN_PRO'];
  const btnEls = Array.from(root.querySelectorAll('[data-titan-go-btn]'));

  let recognition = null;
  let listening = false;
  let silencePromptTimer = null;
  let silenceEndTimer = null;
  let gotValidityHint = false;

  // ---------- UI helpers ----------
  function appendMessage(text, role) {
    const div = document.createElement('div');
    div.className = 'titan-go-msg titan-go-' + (role || 'system');
    div.innerText = text;
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function setMicUI(isListening) {
    listening = isListening;
    if (!micBtn) return;
    micBtn.classList.toggle('is-listening', isListening);
    micBtn.innerText = isListening ? 'Listening…' : 'Tap to talk';
  }

  function showRetry(show) {
    if (!retryBtn) return;
    retryBtn.style.display = show ? 'inline-block' : 'none';
  }

  function showTapToResume() {
    let resume = document.getElementById('titan-go-resume');
    if (!resume) {
      resume = document.createElement('button');
      resume.id = 'titan-go-resume';
      resume.innerText = 'Tap to resume';
      resume.setAttribute('aria-label', 'Tap to resume listening');
      resume.style.margin = '8px';
      resume.addEventListener('click', () => startListening());
      root.appendChild(resume);
    }
  }
  function hideTapToResume() {
    const resume = document.getElementById('titan-go-resume');
    if (resume) resume.remove();
  }

  // ---------- Audio cues ----------
  function beep() {
    if (!beepEnabled) return;
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const o = ctx.createOscillator();
      const g = ctx.createGain();
      o.frequency.value = 880;
      g.gain.value = 0.03;
      o.connect(g);
      g.connect(ctx.destination);
      o.start();
      setTimeout(() => {
        o.stop();
        ctx.close();
      }, 80);
    } catch (_) {}
  }

  // ---------- Button validity (hybrid restricted) ----------
  function setButtonsValidity(validButtons, invalidButtons) {
    if (Array.isArray(validButtons) || Array.isArray(invalidButtons)) gotValidityHint = true;

    // If restricted-by-default and no hint yet, only allow CANCEL + OPEN_PRO.
    if (restrictButtons && !gotValidityHint) {
      btnEls.forEach((el) => {
        const id = el.dataset.titanGoBtn;
        const enabled = (id === 'CANCEL' || id === 'OPEN_PRO');
        el.disabled = !enabled;
        el.style.opacity = enabled ? '1' : '0.4';
      });
      return;
    }

    const valid = Array.isArray(validButtons) ? new Set(validButtons) : null;
    const invalid = Array.isArray(invalidButtons) ? new Set(invalidButtons) : null;

    btnEls.forEach((el) => {
      const id = el.dataset.titanGoBtn;
      let enabled = true;
      if (valid) enabled = valid.has(id);
      if (invalid) enabled = !invalid.has(id);
      el.disabled = !enabled;
      el.style.opacity = enabled ? '1' : '0.4';
    });
  }

  // ---------- Silence timers ----------
  function clearSilenceTimers() {
    if (silencePromptTimer) clearTimeout(silencePromptTimer);
    if (silenceEndTimer) clearTimeout(silenceEndTimer);
    silencePromptTimer = null;
    silenceEndTimer = null;
  }

  function armSilenceTimers() {
    clearSilenceTimers();

    silencePromptTimer = setTimeout(() => {
      appendMessage('Anything else you need?', 'assistant');
      speak('Anything else you need?');
    }, silencePromptMs);

    silenceEndTimer = setTimeout(() => {
      stopListening();
      appendMessage('Okay, I’ll stop listening.', 'assistant');
      speak('Okay, I’ll stop listening.');
      showTapToResume();
    }, silenceEndMs);
  }

  // ---------- Speech output ----------
  function speak(text) {
    if (!text) return;
    if (!('speechSynthesis' in window)) return;
    const u = new SpeechSynthesisUtterance(text);
    window.speechSynthesis.speak(u);
  }

  // ---------- Titan Zero forwarding (verbatim) ----------
  async function sendToTitanGo(inputType, text, buttonId) {
    // Contract: text is always required (even for buttons)
    const payload = {
      session_id: sessionId,
      source: 'titan-go',
      mode: mode,
      input_type: inputType,
      text: text
    };
    payload.context = context || 'unknown';
    if (inputType === 'button' && buttonId) payload.button_id = buttonId;

    armSilenceTimers();
    hideTapToResume();
    showRetry(false);

    const res = await fetch(`/api/v2/chatbot-voice/${encodeURIComponent(chatbotUuid)}/go/input`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await res.json().catch(() => null);
    if (!data) return;

    // Optional: hints + pro_url from Titan Zero (Titan Go does not invent these)
    setButtonsValidity(data.valid_buttons, data.invalid_buttons);

    // Multiple messages may arrive — render + speak in order, verbatim.
    if (Array.isArray(data.messages)) {
      for (const m of data.messages) {
        if (!m || !m.text) continue;
        appendMessage(m.text, m.role || 'assistant');
        speak(m.text);
      }
    }

    // Optional: server can tell the proper pro URL; otherwise fallback to dataset/config.
    const proUrl = data.pro_url || proUrlDefault;
    root.dataset.proUrl = proUrl;
  }

  // ---------- Streaming (optional, future-proof) ----------
  function tryStreamFrom(data) {
    if (!streamingEnabled) return false;
    if (!data || !data.stream_url) return false;

    try {
      const es = new EventSource(data.stream_url);
      es.onmessage = (evt) => {
        try {
          const j = JSON.parse(evt.data);
          if (j && j.text) {
            appendMessage(j.text, j.role || 'assistant');
            speak(j.text);
          }
        } catch (_) {}
      };
      es.onerror = () => es.close();
      return true;
    } catch (_) {
      return false;
    }
  }

  // ---------- Speech input ----------
  function startListening() {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) {
      appendMessage('Voice not supported in this browser. Type instead.', 'system');
      showRetry(false);
      return;
    }

    hideTapToResume();
    showRetry(false);
    transcriptEl.innerText = '';
    beep();

    recognition = new SR();
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.lang = 'en-US';

    let finalText = '';

    recognition.onresult = (event) => {
      let interim = '';
      for (let i = event.resultIndex; i < event.results.length; i++) {
        const t = event.results[i][0].transcript;
        if (event.results[i].isFinal) finalText += t;
        else interim += t;
      }
      const display = (interim || finalText).trim();
      if (display) transcriptEl.innerText = 'You said… ' + display;
    };

    recognition.onerror = (e) => {
      setMicUI(false);
      if (e && (e.error === 'not-allowed' || e.error === 'service-not-allowed')) {
        appendMessage('Microphone permission denied. Type instead.', 'system');
      } else {
        appendMessage('Couldn’t use voice. Type instead.', 'system');
      }
      showRetry(true);
    };

    recognition.onend = async () => {
      setMicUI(false);
      const t = (finalText || (transcriptEl.innerText || '').replace(/^You said…\s*/i, '')).trim();
      transcriptEl.innerText = t ? ('You said… ' + t) : '';
      if (!t) {
        appendMessage('Didn’t catch that. Tap Retry or type instead.', 'system');
        showRetry(true);
        return;
      }
      appendMessage(t, 'user');
      transcriptEl.innerText = '';
      await sendToTitanGo('voice', t, null);
    };

    setMicUI(true);
    armSilenceTimers();
    recognition.start();
  }

  function stopListening() {
    clearSilenceTimers();
    try { recognition && recognition.stop(); } catch (_) {}
    recognition = null;
    transcriptEl.innerText = '';
    setMicUI(false);
  }

  // ---------- Wiring ----------
  if (micBtn) {
    if (holdToTalk) {
      micBtn.innerText = 'Hold to talk';
      micBtn.addEventListener('pointerdown', () => startListening());
      micBtn.addEventListener('pointerup', () => stopListening());
      micBtn.addEventListener('pointercancel', () => stopListening());
    } else {
      micBtn.addEventListener('click', () => {
        if (listening) stopListening();
        else startListening();
      });
    }
  }

  if (retryBtn) retryBtn.addEventListener('click', () => startListening());
  if (stopBtn) stopBtn.addEventListener('click', () => stopListening());

  if (inputEl) {
    inputEl.addEventListener('keydown', async (e) => {
      if (e.key !== 'Enter') return;
      const t = (inputEl.value || '').trim();
      if (!t) return;
      inputEl.value = '';
      appendMessage(t, 'user');
      await sendToTitanGo('text', t, null);
    });
  }

  btnEls.forEach((el) => {
    el.addEventListener('click', async () => {
      const id = el.dataset.titanGoBtn;

      if (id === 'OPEN_PRO') {
        // Let Titan Zero also receive the signal; navigation is additive
        const target = root.dataset.proUrl || proUrlDefault;
        window.top ? (window.top.location.href = target) : (window.location.href = target);
      }

      // Contract: text required even for buttons. Use canonical token as text (no inference).
      await sendToTitanGo('button', id, id);
    });
  });

  // Initial state: hybrid restricted (if configured)
  setButtonsValidity(null, null);
})();
