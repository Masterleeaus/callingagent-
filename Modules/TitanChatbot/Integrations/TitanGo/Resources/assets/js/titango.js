(() => {
  const qs = (id) => document.getElementById(id);

  const startBtn = qs('tg_start');
  const stopBtn = qs('tg_stop');
  const transcriptEl = qs('tg_transcript');
  const statusEl = qs('tg_status');

  const previewBtn = qs('tg_preview');
  const previewBox = qs('tg_preview_box');
  const previewJson = qs('tg_preview_json');
  const confirmBtn = qs('tg_confirm');
  const cancelBtn = qs('tg_cancel');

  const resultBox = qs('tg_result_box');
  const resultJson = qs('tg_result_json');

  const recordTypeEl = qs('tg_record_type');
  const recordIdEl = qs('tg_record_id');

  const speakToggle = qs('tg_speak_toggle');

  let recognition = null;
  let lastPreviewPayload = null;

  // Browser STT (MVP)
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRecognition) {
    recognition = new SpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.lang = document.documentElement.lang || 'en-AU';

    recognition.onresult = (event) => {
      let finalText = '';
      let interim = '';
      for (let i = event.resultIndex; i < event.results.length; i++) {
        const txt = event.results[i][0].transcript;
        if (event.results[i].isFinal) finalText += txt + ' ';
        else interim += txt;
      }
      if (finalText.trim()) transcriptEl.value = (transcriptEl.value + ' ' + finalText).trim();
      statusEl.textContent = interim ? ('Listening… ' + interim) : 'Listening…';
    };

    recognition.onerror = (e) => statusEl.textContent = 'Voice error: ' + e.error;
    recognition.onend = () => {
      startBtn.disabled = false;
      stopBtn.disabled = true;
      statusEl.textContent = 'Stopped.';
    };
  } else {
    statusEl.textContent = 'Speech recognition not supported in this browser. You can still type.';
  }

  function speak(text) {
    try {
      if (!speakToggle || !speakToggle.checked) return;
      if (!('speechSynthesis' in window)) return;
      const u = new SpeechSynthesisUtterance(text);
      u.lang = document.documentElement.lang || 'en-AU';
      window.speechSynthesis.cancel();
      window.speechSynthesis.speak(u);
    } catch (_) {}
  }

  startBtn && (startBtn.onclick = () => {
    if (!recognition) return;
    startBtn.disabled = true;
    stopBtn.disabled = false;
    statusEl.textContent = 'Listening…';
    recognition.start();
  });

  stopBtn && (stopBtn.onclick = () => {
    if (!recognition) return;
    recognition.stop();
  });

  previewBtn && (previewBtn.onclick = async () => {
    resultBox.classList.add('d-none');
    previewBox.classList.add('d-none');

    const transcript = transcriptEl.value.trim();
    if (!transcript) return alert('Please speak or type a command.');

    const body = {
      transcript,
      record_type: recordTypeEl.value || null,
      record_id: recordIdEl.value ? parseInt(recordIdEl.value, 10) : null,
      preview_only: true
    };

    const res = await fetch(window.TITANGO_EXECUTE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.TITANGO_CSRF
      },
      body: JSON.stringify(body)
    });

    const data = await res.json();
    if (!res.ok) return alert(data.message || 'Preview failed.');

    lastPreviewPayload = data.payload;
    previewJson.textContent = JSON.stringify(data.payload, null, 2);
    previewBox.classList.remove('d-none');
    speak('Ready to run ' + (data.payload.action || 'action') + '. Please confirm.');
  });

  cancelBtn && (cancelBtn.onclick = () => {
    lastPreviewPayload = null;
    previewBox.classList.add('d-none');
  });

  confirmBtn && (confirmBtn.onclick = async () => {
    if (!lastPreviewPayload) return;

    const res = await fetch(window.TITANGO_EXECUTE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.TITANGO_CSRF
      },
      body: JSON.stringify({...lastPreviewPayload, confirm: true})
    });

    const data = await res.json();
    resultJson.textContent = JSON.stringify(data, null, 2);
    resultBox.classList.remove('d-none');
    previewBox.classList.add('d-none');

    if (data?.ok) {
      speak('Done.');
    } else {
      speak('Action failed.');
    }
  });
})();
