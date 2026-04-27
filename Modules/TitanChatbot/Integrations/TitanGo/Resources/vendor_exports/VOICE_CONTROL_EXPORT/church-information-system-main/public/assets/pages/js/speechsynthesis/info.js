document.addEventListener("DOMContentLoaded", function () {
    const synthesisAvailable =
        "speechSynthesis" in window && "SpeechSynthesisUtterance" in window;
    const synth = synthesisAvailable ? window.speechSynthesis : null;
    let currentlyHighlightedElement = null;
    let voicesLoaded = false;
    let speakableElements = [];
    let isAutoPlaying = false;
    let currentAutoPlayIndex = -1;

    if (!synthesisAvailable) {
        return;
    }

    function removeHighlight() {
        if (currentlyHighlightedElement) {
            currentlyHighlightedElement.classList.remove("highlight");
            currentlyHighlightedElement = null;
        }
    }

    function highlightElement(element) {
        removeHighlight();
        if (element) {
            element.classList.add("highlight");
            currentlyHighlightedElement = element;
        }
    }

    function preprocessInfoText(text) {
        if (!text) return "";
        try {
            text = text.replace(/GPIB/gi, "G P I B");
            text = text.replace(/No\./gi, "Nomor");
            text = text.replace(/\b(tgl|tanggal)\b/gi, "tanggal");
            text = text.replace(/SK\. /gi, "S K ");
            text = text.replace(/([.,:;?!])(?=[^\s])/g, "$1 ");
            return text.replace(/\s+/g, " ").trim();
        } catch (e) {
            return (text || "").replace(/\s+/g, " ").trim();
        }
    }

    function stopAllSpeech() {
        isAutoPlaying = false;
        currentAutoPlayIndex = -1;
        if (synth && (synth.speaking || synth.paused)) {
            synth.cancel();
        }
        removeHighlight();
    }

    function speakElement(element, isAutoSequence = false, nextIndex = -1) {
        if (
            typeof window.speechEnabled !== "undefined" &&
            !window.speechEnabled
        ) {
            stopAllSpeech();
            return;
        }
        if (!element || !synth || !voicesLoaded) return;

        const textToSpeak = (
            element.innerText ||
            element.textContent ||
            ""
        ).trim();
        if (!textToSpeak) {
            if (isAutoSequence) {
                playNextSpeakable(nextIndex);
            }
            return;
        }

        if (!isAutoSequence || nextIndex === 1) {
            if (synth.speaking || synth.paused) {
                synth.cancel();
            }
            removeHighlight();
        }
        isAutoPlaying = isAutoSequence;

        setTimeout(() => {
            if (
                typeof window.speechEnabled !== "undefined" &&
                !window.speechEnabled
            ) {
                stopAllSpeech();
                return;
            }
            if (isAutoSequence && !isAutoPlaying && nextIndex > 0) {
                return; 
            }

            const processedText = preprocessInfoText(textToSpeak);
            if (!processedText) {
                if (isAutoSequence) playNextSpeakable(nextIndex);
                return;
            }

            const utterance = new SpeechSynthesisUtterance(processedText);
            const voices = synth.getVoices();
            let indoVoice = voices.find((v) => v.lang === "id-ID");
            if (!indoVoice) {
                indoVoice = voices.find((v) => v.default) || voices[0];
                if (indoVoice) utterance.lang = indoVoice.lang;
            } else {
                utterance.lang = "id-ID";
            }
            if (indoVoice) utterance.voice = indoVoice;

            utterance.rate = 1.0;
            utterance.pitch = 1.0;

            if (isAutoSequence) {
                currentAutoPlayIndex = nextIndex - 1;
            }

            utterance.onstart = () => {
                if (
                    typeof window.speechEnabled !== "undefined" &&
                    !window.speechEnabled
                ) {
                    stopAllSpeech();
                    return;
                }
                highlightElement(element);
            };

            utterance.onend = () => {
                if (isAutoSequence && isAutoPlaying) {
                    playNextSpeakable(nextIndex);
                } else if (!isAutoSequence) {
                    removeHighlight();
                }
            };

            utterance.onerror = (event) => {
                removeHighlight();
                if (isAutoSequence) isAutoPlaying = false; // Hentikan sequence jika error
                if (
                    event.error !== "interrupted" &&
                    event.error !== "canceled"
                ) {
                    console.error("Speech Synthesis Error:", event.error);
                }
            };

            try {
                synth.speak(utterance);
            } catch (e) {
                console.error("Gagal memulai synth.speak:", e);
                removeHighlight();
                if (isAutoSequence) isAutoPlaying = false;
            }
        }, 50);
    }

    function playNextSpeakable(index) {
        if (!isAutoPlaying || index >= speakableElements.length) {
            isAutoPlaying = false;
            currentAutoPlayIndex = -1;
            removeHighlight();
            return;
        }
        const element = speakableElements[index];
        if (element) {
            speakElement(element, true, index + 1);
        } else {
            playNextSpeakable(index + 1);
        }
    }

    function startAutoPlay() {
        if (
            typeof window.speechEnabled !== "undefined" &&
            !window.speechEnabled
        ) {
            return;
        }
        if (!voicesLoaded || !synth) return;

        stopAllSpeech();
        isAutoPlaying = true;
        currentAutoPlayIndex = -1;
        speakableElements = document.querySelectorAll(".speakable");
        if (speakableElements.length > 0) {
            playNextSpeakable(0);
        } else {
            isAutoPlaying = false;
        }
    }

    function initializeTTSAndListeners() {
        voicesLoaded = true;
        speakableElements = document.querySelectorAll(".speakable");

        speakableElements.forEach((element) => {
            element.removeEventListener("click", handleSpeakableClick);
            element.addEventListener("click", handleSpeakableClick);
        });

        startAutoPlay();
    }

    function handleSpeakableClick(event) {
        if (
            typeof window.speechEnabled !== "undefined" &&
            !window.speechEnabled
        ) {
            stopAllSpeech();
            return;
        }
        isAutoPlaying = false;
        speakElement(this, false);
    }

    document.addEventListener("tts-state-changed", (event) => {
        if (event.detail && event.detail.enabled === false) {
            stopAllSpeech();
        } else if (event.detail && event.detail.enabled === true) {
            // Jika diaktifkan dan tidak sedang auto playing, mulai lagi? Opsional.
            // if (!isAutoPlaying) {
            //     startAutoPlay();
            // }
        }
    });

    if (synth.getVoices().length !== 0) {
        initializeTTSAndListeners();
    } else {
        synth.onvoiceschanged = initializeTTSAndListeners;
        setTimeout(() => {
            if (!voicesLoaded) {
                initializeTTSAndListeners();
            }
        }, 1000);
    }

    window.addEventListener("beforeunload", stopAllSpeech);
});
