document.addEventListener("DOMContentLoaded", function () {
    const globalFunctionsAvailable =
        typeof window.speakText === "function" &&
        typeof window.addToSpeechQueue === "function" &&
        typeof window.startSpeechQueue === "function" &&
        typeof window.stopSpeechQueue === "function";

    if (!globalFunctionsAvailable) {
        console.error(
            "Fungsi TTS global tidak ditemukan. Pastikan index.js dimuat terlebih dahulu."
        );
        return;
    }

    let berandaSpeechInitialized = false;

    function initBerandaSpeechFeatures() {
        if (berandaSpeechInitialized) return;
        berandaSpeechInitialized = true;
        console.log("[Beranda] Inisialisasi Fitur Speech...");

        let autoReadTexts = [];
        const autoElement = document.getElementById("autoSpeak");
        const renunganTitleElement = document.querySelector(
            "div.container > h2.display-6"
        );
        const speakableColumns = document.querySelectorAll(
            ".speakable.col-lg-4"
        );

        if (autoElement) {
            let headerText = "";
            autoElement.querySelectorAll("h1, blockquote p, figcaption").forEach((el) => {
                headerText += (el.innerText || el.textContent).trim() + ". ";
            });
            if (headerText.trim()) {
                autoReadTexts.push(headerText.trim());
            }
        }
        if (renunganTitleElement) {
            const titleText = (
                renunganTitleElement.innerText ||
                renunganTitleElement.textContent
            ).trim();
            if (titleText) {
                autoReadTexts.push(titleText);
            }
        }

        speakableColumns.forEach((col, index) => {
            const card = col.querySelector(".card");
            if (card) {
                const title = card.querySelector(".card-title");
                const subtitle = card.querySelector(".card-subtitle");
                let cardText = "";
                if (title) {
                    cardText += `Renungan ${index + 1}: ${(
                        title.innerText || title.textContent
                    ).trim()}.`;
                } else {
                    cardText += `Renungan ${index + 1}: Tanpa Judul.`;
                }
                if (subtitle) {
                    const subtitleText = (
                        subtitle.innerText || subtitle.textContent
                    )
                        .replace(/^Bacaan:/i, "")
                        .trim();
                    if (subtitleText) {
                        cardText += ` Bacaan Alkitab: ${subtitleText}.`;
                    }
                }
                autoReadTexts.push(cardText);
            }
        });

        if (autoReadTexts.length > 0) {
            window.addToSpeechQueue(autoReadTexts);
        }

        if (window.speechEnabled) {
            setTimeout(() => {
                window.startSpeechQueue();
            }, 50);
        }

        const speakableElements = document.querySelectorAll(".speakable");
        speakableElements.forEach(function (el) {
            el.style.cursor = "pointer";
            el.addEventListener("click", function (event) {
                if (
                    event.target.closest("a, button") ||
                    !window.speechEnabled
                ) {
                    return;
                }
                window.stopSpeechQueue();
                let textToSpeak = "";
                const title = el.querySelector("h1, h5");
                const subtitle = el.querySelector(".card-subtitle");
                const blockquote = el.querySelector("blockquote p");
                if (title) {
                    textToSpeak = (title.innerText || title.textContent).trim();
                    if (subtitle) {
                        const subtitleText = (
                            subtitle.innerText || subtitle.textContent
                        )
                            .replace(/^Bacaan:/i, "")
                            .trim();
                        if (subtitleText)
                            textToSpeak += `. Bacaan Alkitab: ${subtitleText}.`;
                    }
                    if (blockquote) {
                        const quoteText = (
                            blockquote.innerText || blockquote.textContent
                        ).trim();
                        if (quoteText) textToSpeak += `. ${quoteText}.`;
                    }
                } else {
                    textToSpeak = el.innerText;
                }

                if (textToSpeak.trim()) {
                    window.speakText(textToSpeak.trim());
                }
            });
        });
    }

    function runInitWhenReady() {
        if (
            typeof window.speakText === "function" &&
            !berandaSpeechInitialized
        ) {
            initBerandaSpeechFeatures();
        } else if (berandaSpeechInitialized) {
        } else {
            console.error("Fungsi TTS global tidak tersedia saat init.");
        }
    }
    if (window.speechSynthesis) {
        const synth = window.speechSynthesis;
        let initTimeout;
        const triggerInit = () => {
            clearTimeout(initTimeout);
            runInitWhenReady();
        };
        if (synth.getVoices().length > 0) {
            runInitWhenReady();
        } else if (synth.onvoiceschanged !== undefined) {
            synth.onvoiceschanged = triggerInit;
            initTimeout = setTimeout(() => {
                triggerInit();
            }, 2500);
        } else {
            setTimeout(triggerInit, 500);
        }
    } else {
        console.warn("Speech Synthesis tidak didukung...");
    }
});
