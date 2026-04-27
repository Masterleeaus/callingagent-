document.addEventListener("DOMContentLoaded", function () {
    const synth = window.speechSynthesis;

    if (!synth || !window.SpeechSynthesisUtterance) {
        console.warn("Speech Synthesis tidak didukung oleh browser ini.");
        return;
    }

    let speakableElementsOnPage = [];
    let autoplayQueue = [];
    let currentAutoplayIndex = -1;
    let currentHighlightedElement = null;
    let isAutoplayActive = false;
    let hasAutoplayRunThisSession = false;

    function getSelectedVoiceInternal() {
        const voices = synth.getVoices();
        let indonesianVoice = voices.find(
            (v) =>
                v.lang.toLowerCase().startsWith("id") &&
                (v.name.toLowerCase().includes("female") ||
                    (v.gender && v.gender.toLowerCase() === "female"))
        );
        if (!indonesianVoice) {
            indonesianVoice = voices.find((v) =>
                v.lang.toLowerCase().startsWith("id")
            );
        }
        return (
            indonesianVoice ||
            voices.find((v) => v.default) ||
            (voices.length > 0 ? voices[0] : null)
        );
    }

    function cleanTextInternal(text) {
        if (!text) return "";
        text = text.replace(/GPIB/gi, "G P I B");
        return text.replace(/\s+/g, " ").trim();
    }

    function removeHighlight() {
        if (currentHighlightedElement) {
            currentHighlightedElement.classList.remove("speakable-highlight");
            currentHighlightedElement = null;
        }
    }

    function speakAndHighlight(text, element, onEndCallback) {
        if (!window.speechEnabled || !text) {
            removeHighlight();
            if (onEndCallback) onEndCallback();
            return;
        }

        if (synth.speaking || synth.pending) {
            synth.cancel();
        }
        removeHighlight();

        const cleanedText = cleanTextInternal(text);
        if (!cleanedText) {
            if (onEndCallback) onEndCallback();
            return;
        }

        const utterance = new SpeechSynthesisUtterance(cleanedText);
        const voice = getSelectedVoiceInternal();

        if (voice) {
            utterance.voice = voice;
            utterance.lang = voice.lang;
        } else {
            utterance.lang = "id-ID";
        }
        utterance.rate = 1.1;
        utterance.pitch = 1;

        utterance.onstart = () => {
            if (element) {
                element.classList.add("speakable-highlight");
                currentHighlightedElement = element;
            }
        };

        utterance.onend = () => {
            removeHighlight();
            if (onEndCallback) {
                onEndCallback();
            }
        };

        utterance.onerror = (event) => {
            console.error("Speech Synthesis Error:", event.error);
            removeHighlight();
            if (onEndCallback) {
                onEndCallback();
            }
        };

        try {
            synth.speak(utterance);
        } catch (e) {
            console.error("Gagal memulai synth.speak:", e);
            removeHighlight();
            if (onEndCallback) onEndCallback();
        }
    }

    function getTextForElement(element) {
        let textToSpeak = "";
        if (
            element.tagName === "H2" &&
            element.classList.contains("text-brown")
        ) {
            // Judul Utama
            textToSpeak = element.textContent.trim();
        } else if (
            element.classList.contains("tataIbadah-item") &&
            element.classList.contains("card")
        ) {
            const titleEl = element.querySelector(".card-title");
            const subtitleEl = element.querySelector(".card-subtitle");
            if (titleEl) {
                textToSpeak += titleEl.textContent.trim() + ". ";
            }
            if (subtitleEl) {
                textToSpeak += subtitleEl.textContent
                    .replace(/\s+/g, " ")
                    .trim();
            }
        } else {
            textToSpeak = element.textContent.trim();
        }
        return textToSpeak;
    }

    function processAutoplayQueue() {
        if (
            !isAutoplayActive ||
            currentAutoplayIndex >= autoplayQueue.length ||
            !window.speechEnabled
        ) {
            stopAutoplay(false);
            return;
        }

        const item = autoplayQueue[currentAutoplayIndex];
        speakAndHighlight(item.text, item.element, () => {
            currentAutoplayIndex++;
            setTimeout(processAutoplayQueue, 300);
        });
    }

    function startAutoplay() {
        if (
            !window.speechEnabled ||
            hasAutoplayRunThisSession ||
            autoplayQueue.length === 0
        ) {
            return;
        }

        if (synth.getVoices().length === 0) {
            synth.onvoiceschanged = () => {
                synth.onvoiceschanged = null;
                if (!hasAutoplayRunThisSession && window.speechEnabled) {
                    isAutoplayActive = true;
                    currentAutoplayIndex = 0;
                    hasAutoplayRunThisSession = true;
                    processAutoplayQueue();
                }
            };
            return;
        }

        isAutoplayActive = true;
        currentAutoplayIndex = 0;
        hasAutoplayRunThisSession = true;
        processAutoplayQueue();
    }

    function stopAutoplay(cancelSpeech = true) {
        isAutoplayActive = false;
        currentAutoplayIndex = -1;
        removeHighlight();
        if (cancelSpeech && (synth.speaking || synth.pending)) {
            synth.cancel();
        }
    }

    function initializePageTTS() {
        speakableElementsOnPage = document.querySelectorAll(".speakable");
        autoplayQueue = [];

        const mainTitle = document.querySelector("h2.speakable.text-brown");
        if (mainTitle) {
            autoplayQueue.push({
                element: mainTitle,
                text: getTextForElement(mainTitle),
            });
        }

        const tataIbadahItems = document.querySelectorAll(
            ".tataIbadah-item.speakable"
        );
        const maxAutoplayItems = 2;
        for (
            let i = 0;
            i < Math.min(tataIbadahItems.length, maxAutoplayItems);
            i++
        ) {
            autoplayQueue.push({
                element: tataIbadahItems[i],
                text: getTextForElement(tataIbadahItems[i]),
            });
        }

        speakableElementsOnPage.forEach((element) => {
            element.style.cursor = "pointer";
            element.setAttribute("title", "Klik untuk membacakan teks");

            element.addEventListener("click", function () {
                if (!window.speechEnabled) {
                    if (synth.speaking || synth.pending) synth.cancel();
                    removeHighlight();
                    return;
                }
                stopAutoplay();
                const textToSpeak = getTextForElement(this);
                speakAndHighlight(textToSpeak, this, null);
            });
        });

        setTimeout(startAutoplay, 500);
    }

    document.addEventListener("tts-global-disabled", () => {
        stopAutoplay();
    });

    window.addEventListener("beforeunload", () => {
        stopAutoplay();
    });

    initializePageTTS();
});
