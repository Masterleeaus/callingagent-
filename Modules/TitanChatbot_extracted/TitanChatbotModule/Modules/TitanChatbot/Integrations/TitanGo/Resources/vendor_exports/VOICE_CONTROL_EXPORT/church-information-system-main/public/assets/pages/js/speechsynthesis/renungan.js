document.addEventListener("DOMContentLoaded", function () {
    const synth = window.speechSynthesis;

    if (!synth || !window.SpeechSynthesisUtterance) {
        console.warn(
            "[Renungan TTS] Speech Synthesis tidak didukung oleh browser ini."
        );
        return;
    }
    if (typeof window.speechEnabled === "undefined") {
        console.error(
            "[Renungan TTS] Variabel TTS global (window.speechEnabled) tidak ditemukan."
        );
        window.speechEnabled = false;
    }

    let autoplayQueueInternal = [];
    let currentAutoplayIndexInternal = -1;
    let currentHighlightedElInternal = null;
    let isAutoplayActiveInternal = false;
    let hasAutoplayRunThisSessionInternal = false;
    let initialVoiceCheckTimeout;
    let renunganCardCounter = 0;

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
        text = text.replace(/(?:https?|ftp):\/\/[\n\S]+/g, "");
        text = text.replace(/&copy;/gi, "hak cipta");
        text = text.replace(/&reg;/gi, "merek terdaftar");
        text = text.replace(/^Bacaan:/i, "");
        return text.replace(/\s+/g, " ").trim();
    }

    function removeHighlight() {
        if (currentHighlightedElInternal) {
            currentHighlightedElInternal.classList.remove(
                "speakable-highlight"
            );
            currentHighlightedElInternal = null;
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

        const generallyCleanedText = cleanTextInternal(text);
        if (!generallyCleanedText) {
            if (onEndCallback) onEndCallback();
            return;
        }

        const utterance = new SpeechSynthesisUtterance(generallyCleanedText);
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
                currentHighlightedElInternal = element;
            }
        };
        utterance.onend = () => {
            removeHighlight();
            if (onEndCallback) onEndCallback();
        };
        utterance.onerror = (event) => {
            console.error(
                "[Renungan TTS] Speech Synthesis Error:",
                event.error
            );
            removeHighlight();
            if (onEndCallback) onEndCallback();
        };

        try {
            synth.speak(utterance);
        } catch (e) {
            console.error("[Renungan TTS] Gagal memulai synth.speak:", e);
            removeHighlight();
            if (onEndCallback) onEndCallback();
        }
    }

    function getTextForElement(
        element,
        forAutoplay = false,
        autoplayIndex = 0
    ) {
        let compositeText = "";

        if (element.id === "renungan-header-section") {
            const segments = element.querySelectorAll(".tts-segment");
            if (segments.length > 0) {
                segments.forEach((segment) => {
                    compositeText +=
                        (segment.innerText || segment.textContent).trim() +
                        ". ";
                });
            } else {
                compositeText = (
                    element.innerText || element.textContent
                ).trim();
            }
        } else if (element.classList.contains("renungan-card-item")) {
            const titleSegment = element.querySelector(
                ".card-title .tts-segment"
            );
            const alkitabSegment = element.querySelector(
                ".card-subtitle .tts-segment"
            );

            const titleText = titleSegment
                ? (titleSegment.innerText || titleSegment.textContent).trim()
                : "";
            const alkitabText = alkitabSegment
                ? (alkitabSegment.innerText || alkitabSegment.textContent)
                      .trim()
                      .replace(/^Bacaan:/i, "")
                      .trim()
                : "";

            if (forAutoplay) {
                compositeText = titleText
                    ? `Renungan ${autoplayIndex}: ${titleText}.`
                    : `Renungan ${autoplayIndex}.`;
                if (alkitabText && alkitabText.toLowerCase() !== "n/a") {
                    compositeText += ` Bacaan Alkitab: ${alkitabText}.`;
                }
            } else {
                if (titleText) {
                    compositeText += titleText + ". ";
                }
                if (alkitabText && alkitabText.toLowerCase() !== "n/a") {
                    compositeText += "Bacaan Alkitab: " + alkitabText + ".";
                }
            }
        } else {
            compositeText = (element.innerText || element.textContent).trim();
        }
        return compositeText.trim();
    }

    function processAutoplayQueueInternal() {
        if (
            !isAutoplayActiveInternal ||
            currentAutoplayIndexInternal >= autoplayQueueInternal.length ||
            !window.speechEnabled
        ) {
            stopAutoplayInternal(false);
            return;
        }
        const item = autoplayQueueInternal[currentAutoplayIndexInternal];
        speakAndHighlight(item.text, item.element, () => {
            currentAutoplayIndexInternal++;
            setTimeout(processAutoplayQueueInternal, 300);
        });
    }

    function startAutoplayInternal() {
        if (
            !window.speechEnabled ||
            hasAutoplayRunThisSessionInternal ||
            autoplayQueueInternal.length === 0
        ) {
            return;
        }
        if (synth.getVoices().length === 0) {
            return;
        }
        isAutoplayActiveInternal = true;
        currentAutoplayIndexInternal = 0;
        hasAutoplayRunThisSessionInternal = true;
        processAutoplayQueueInternal();
    }

    function stopAutoplayInternal(cancelSpeech = true) {
        isAutoplayActiveInternal = false;
        removeHighlight();
        if (cancelSpeech && (synth.speaking || synth.pending)) {
            synth.cancel();
        }
    }

    function prepareAndStartAutoplay() {
        autoplayQueueInternal = [];
        renunganCardCounter = 0;

        const headerSection = document.getElementById(
            "renungan-header-section"
        );
        if (headerSection) {
            autoplayQueueInternal.push({
                element: headerSection,
                text: getTextForElement(headerSection),
            });
        }

        const renunganCards = document.querySelectorAll(
            "#renungan-container .renungan-card-item.speakable"
        );
        const maxAutoplayCards = 2;
        for (
            let i = 0;
            i < Math.min(renunganCards.length, maxAutoplayCards);
            i++
        ) {
            renunganCardCounter++;
            autoplayQueueInternal.push({
                element: renunganCards[i],
                text: getTextForElement(
                    renunganCards[i],
                    true,
                    renunganCardCounter
                ),
            });
        }
        startAutoplayInternal();
    }

    function initPageTTS() {
        document.body.addEventListener("click", function (event) {
            const speakableElement = event.target.closest(".speakable");

            if (!speakableElement || event.target.closest("a, button")) {
                return;
            }

            if (!window.speechEnabled) {
                if (synth.speaking || synth.pending) synth.cancel();
                removeHighlight();
                return;
            }
            stopAutoplayInternal();
            const textToSpeak = getTextForElement(speakableElement);
            speakAndHighlight(textToSpeak, speakableElement, null);
        });

        document
            .querySelectorAll(".speakable")
            .forEach((el) => (el.style.cursor = "pointer"));
    }

    function initTTSFromVoiceCheck() {
        clearTimeout(initialVoiceCheckTimeout);
        prepareAndStartAutoplay();
    }

    initPageTTS();

    if (synth.getVoices().length > 0) {
        initTTSFromVoiceCheck();
    } else if (typeof synth.onvoiceschanged !== "undefined") {
        synth.onvoiceschanged = initTTSFromVoiceCheck;
        initialVoiceCheckTimeout = setTimeout(initTTSFromVoiceCheck, 2500);
    } else {
        initialVoiceCheckTimeout = setTimeout(initTTSFromVoiceCheck, 500);
    }

    document.addEventListener("tts-global-disabled", () => {
        stopAutoplayInternal();
    });

    window.addEventListener("beforeunload", () => {
        stopAutoplayInternal();
    });

    const renunganContainer = document.getElementById("renungan-container");
    if (renunganContainer) {
        const observer = new MutationObserver((mutationsList) => {
            for (const mutation of mutationsList) {
                if (mutation.type === "childList") {
                    mutation.addedNodes.forEach((node) => {
                        if (
                            node.nodeType === Node.ELEMENT_NODE &&
                            node.matches(".speakable")
                        ) {
                            node.style.cursor = "pointer";
                        } else if (node.nodeType === Node.ELEMENT_NODE) {
                            node.querySelectorAll(".speakable").forEach(
                                (speakableChild) => {
                                    speakableChild.style.cursor = "pointer";
                                }
                            );
                        }
                    });
                }
            }
        });
        observer.observe(renunganContainer, { childList: true, subtree: true });
    }
});
