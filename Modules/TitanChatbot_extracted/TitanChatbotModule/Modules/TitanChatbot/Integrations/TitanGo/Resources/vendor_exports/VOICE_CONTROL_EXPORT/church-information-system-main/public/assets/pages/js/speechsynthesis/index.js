(function () {
    "use strict";

    const synthesisAvailable =
        "speechSynthesis" in window && "SpeechSynthesisUtterance" in window;
    const synth = synthesisAvailable ? window.speechSynthesis : null;
    let voices = [];
    window.speechEnabled = true;

    let utteranceQueue = [];
    let isSpeakingQueue = false;
    let currentQueueUtterance = null;

    const supportEl = document.getElementById("browserSupport");
    const toggleBtn = document.getElementById("btnToggleSpeech");

    function checkBrowserSupport() {
        if (!supportEl) return;
        let supportText = "";
        if (synthesisAvailable) {
            supportText +=
                '<i class="fas fa-check-circle text-success me-1"></i>TTS Didukung. ';
        } else {
            supportText +=
                '<i class="fas fa-times-circle text-danger me-1"></i>TTS Tidak Didukung. ';
            window.speechEnabled = false;
            if (toggleBtn) {
                toggleBtn.disabled = true;
                updateToggleButton(toggleBtn, false);
            }
        }
        supportEl.innerHTML = supportText;
    }

    function loadVoices() {
        if (!synthesisAvailable) return;
        try {
            voices = synth
                .getVoices()
                .filter(
                    (voice) =>
                        voice.lang && voice.lang.toLowerCase().startsWith("id")
                );
        } catch (e) {
            console.error("Error getting voices:", e);
            voices = [];
        }
    }

    function getSelectedVoice() {
        if (!synthesisAvailable) return null;
        if (voices.length === 0) loadVoices();
        if (voices.length === 0) {
            let allVoices = [];
            try {
                allVoices = synth.getVoices();
            } catch (e) {}
            return (
                allVoices.find((voice) =>
                    voice.lang.toLowerCase().startsWith("en")
                ) ||
                allVoices[0] ||
                null
            );
        }
        let femaleVoice = voices.find(
            (voice) =>
                voice.name.toLowerCase().includes("female") ||
                (voice.gender && voice.gender.toLowerCase() === "female")
        );
        return femaleVoice || voices[0] || null;
    }

    function fixBibleCitation(text) {
        if (!text) return "";
        try {
            return text.replace(
                /(\b[A-Za-z]+(?:\s[A-Za-z]+){0,2})\s?(\d{1,3})\s?[:.]\s?(\d{1,3})(?:\s?[-–—]\s?(\d{1,3}))?/g,
                function (match, book, chapter, verseStart, verseEnd) {
                    book = book.replace(/<[^>]*>/g, "").trim();
                    let result =
                        book +
                        " pasal " +
                        parseInt(chapter, 10) +
                        " ayat " +
                        parseInt(verseStart, 10);
                    if (verseEnd) {
                        result += " sampai " + parseInt(verseEnd, 10);
                    }
                    return result;
                }
            );
        } catch (e) {
            return text;
        }
    }

    function preprocessText(text) {
        if (!text) return "";
        try {
            text = fixBibleCitation(text);
            text = text.replace(/GPIB/gi, "G P I B");
            text = text.replace(/(?:https?|ftp):\/\/[\n\S]+/g, "");
            text = text.replace(/&copy;/gi, "hak cipta");
            text = text.replace(/&reg;/gi, "merek terdaftar");
            text = text.replace(
                /(\d{1,2})\.(\d{2})\s*-\s*(\d{1,2})\.(\d{2})\s*WIB/gi,
                (m, sH, sM, eH, eM) =>
                    `pukul ${parseInt(sH, 10)} ${
                        parseInt(sM, 10) === 0 ? "" : sM
                    } sampai pukul ${parseInt(eH, 10)} ${
                        parseInt(eM, 10) === 0 ? "" : eM
                    } Waktu Indonesia Barat`
            );
            text = text.replace(
                /(\d{1,2})[:.](\d{2})\s*WIB/gi,
                (m, h, i) =>
                    `pukul ${parseInt(h, 10)} ${
                        parseInt(i, 10) === 0 ? "" : i
                    } Waktu Indonesia Barat`
            );
            text = text.replace(
                /(\d{1,2})[:.](\d{2})(?![:.\d\s]*(WIB|-|\.\d))/g,
                (m, h, i) =>
                    `pukul ${parseInt(h, 10)} ${parseInt(i, 10) === 0 ? "" : i}`
            );
            text = text
                .replace(/([.?!;])\s+/g, "$1 ")
                .replace(/\s+/g, " ")
                .trim();
            return text;
        } catch (e) {
            return text.replace(/\s+/g, " ").trim();
        }
    }

    window.speakText = function (textToSpeak) {
        if (
            !synthesisAvailable ||
            !window.speechEnabled ||
            !textToSpeak ||
            typeof textToSpeak !== "string"
        ) {
            stopSpeechQueue();
            if (synth && synth.speaking) {
                try {
                    synth.cancel();
                } catch (e) {}
            }
            return;
        }
        stopSpeechQueue();

        const processedText = preprocessText(textToSpeak);
        if (!processedText) return;

        const utterance = new SpeechSynthesisUtterance(processedText);
        const selectedVoice = getSelectedVoice();
        if (selectedVoice) {
            utterance.voice = selectedVoice;
            utterance.lang = selectedVoice.lang;
        } else {
            utterance.lang = "id-ID";
        }
        utterance.rate = 1.1;
        utterance.pitch = 1;

        utterance.onerror = function (event) {
            console.error("Utterance Error (Direct Speak)", event.error);
            showFeedback(`Error TTS: ${event.error}`, "error");
        };

        try {
            if (synth) {
                if (synth.speaking || synth.paused) {
                    synth.cancel();
                } // Pastikan cancel sebelum speak baru
                synth.speak(utterance);
            }
        } catch (e) {
            console.error("Speak Error:", e);
        }
    };

    function processSpeechQueue() {
        if (
            !synthesisAvailable ||
            !window.speechEnabled ||
            utteranceQueue.length === 0
        ) {
            isSpeakingQueue = false;
            currentQueueUtterance = null;
            return;
        }
        isSpeakingQueue = true;

        const textToSpeak = utteranceQueue.shift();
        const processedText = preprocessText(textToSpeak);
        if (!processedText) {
            setTimeout(processSpeechQueue, 50);
            return;
        }

        currentQueueUtterance = new SpeechSynthesisUtterance(processedText);
        const selectedVoice = getSelectedVoice();
        if (selectedVoice) {
            currentQueueUtterance.voice = selectedVoice;
            currentQueueUtterance.lang = selectedVoice.lang;
        } else {
            currentQueueUtterance.lang = "id-ID";
        }
        currentQueueUtterance.rate = 1.1;
        currentQueueUtterance.pitch = 1;

        currentQueueUtterance.onend = function () {
            currentQueueUtterance = null;
            setTimeout(processSpeechQueue, 200);
        };
        currentQueueUtterance.onerror = function (event) {
            console.error("Utterance Error (Queue)", event.error);
            showFeedback(`Error saat membaca antrian: ${event.error}`, "error");
            currentQueueUtterance = null;
            stopSpeechQueue();
        };

        setTimeout(() => {
            try {
                if (synth && !synth.speaking && !synth.paused) {
                    synth.speak(currentQueueUtterance);
                } else if (synth && (synth.speaking || synth.paused)) {
                    utteranceQueue.unshift(textToSpeak);
                    isSpeakingQueue = false;
                }
            } catch (e) {
                console.error("Error calling synth.speak from queue:", e);
                stopSpeechQueue();
            }
        }, 50);
    }

    window.addToSpeechQueue = function (items) {
        const itemsToAdd = Array.isArray(items) ? items : [items];
        itemsToAdd.forEach((item) => {
            if (typeof item === "string" && item.trim().length > 0) {
                utteranceQueue.push(item.trim());
            }
        });
    };

    window.startSpeechQueue = function () {
        if (!synthesisAvailable || !window.speechEnabled) return;
        if (synth && synth.speaking) {
            try {
                synth.cancel();
            } catch (e) {}
        }
        if (isSpeakingQueue) {
            return;
        }
        if (currentQueueUtterance) {
            currentQueueUtterance.onend = null;
            currentQueueUtterance = null;
        }
        if (utteranceQueue.length > 0) {
            processSpeechQueue();
        }
    };

    window.stopSpeechQueue = function () {
        utteranceQueue = [];
        isSpeakingQueue = false;
        if (currentQueueUtterance) {
            currentQueueUtterance.onend = null;
            currentQueueUtterance = null;
        }
        if (synth && synth.speaking) {
            try {
                synth.cancel();
            } catch (e) {}
        }
    };

    function updateToggleButton(button, enabled) {
        if (!button) return;
        button.disabled = !synthesisAvailable;
        if (enabled && synthesisAvailable) {
            button.classList.remove("btn-danger");
            button.classList.add("btn-success");
            button.title = "Nonaktifkan Pembaca Teks";
            button.innerHTML = '<i class="fas fa-volume-up fa-fw"></i>';
        } else {
            button.classList.remove("btn-success");
            button.classList.add("btn-danger");
            button.title = "Aktifkan Pembaca Teks";
            button.innerHTML = '<i class="fas fa-volume-mute fa-fw"></i>';
        }
    }

    function initializeGlobalSpeech() {
        checkBrowserSupport();
        if (synthesisAvailable) {
            setTimeout(() => {
                loadVoices();
                if (synth && synth.onvoiceschanged !== undefined) {
                    synth.onvoiceschanged = loadVoices;
                }
            }, 100);
            if (toggleBtn) {
                updateToggleButton(toggleBtn, window.speechEnabled);
                toggleBtn.addEventListener("click", function () {
                    window.speechEnabled = !window.speechEnabled;
                    updateToggleButton(this, window.speechEnabled);
                    if (!window.speechEnabled) {
                        stopSpeechQueue();
                        document.dispatchEvent(
                            new CustomEvent("tts-global-disabled")
                        );
                    } else {
                        document.dispatchEvent(
                            new CustomEvent("tts-global-enabled")
                        );
                    }
                });
            } else {
                if (!synthesisAvailable) window.speechEnabled = false;
            }
        } else {
            window.speechEnabled = false;
            if (toggleBtn) updateToggleButton(toggleBtn, false);
        }
    }

    function showFeedback(message, type = "info") {
        console.log(`Feedback (${type}): ${message}`);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeGlobalSpeech);
    } else {
        initializeGlobalSpeech();
    }
})();
