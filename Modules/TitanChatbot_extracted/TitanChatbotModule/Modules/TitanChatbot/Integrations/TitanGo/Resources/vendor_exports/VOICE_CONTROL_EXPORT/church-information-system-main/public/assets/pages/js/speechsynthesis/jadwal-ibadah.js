document.addEventListener("DOMContentLoaded", function () {
    const synth = window.speechSynthesis;

    if (!synth || !window.SpeechSynthesisUtterance) {
        console.warn(
            "[Jadwal Ibadah] Speech Synthesis tidak didukung oleh browser ini."
        );
        return;
    }
    if (typeof window.speechEnabled === "undefined") {
        console.error(
            "[Jadwal Ibadah] Variabel TTS global (window.speechEnabled) tidak ditemukan. Pastikan index.js dimuat."
        );
        window.speechEnabled = false;
    }

    let autoplayQueueInternal = [];
    let currentAutoplayIndexInternal = -1;
    let currentHighlightedElInternal = null;
    let isAutoplayActiveInternal = false;
    let hasAutoplayRunThisSessionInternal = false;
    let initialVoiceCheckTimeout;

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
        return text.replace(/\s+/g, " ").trim();
    }

    function convertRomanNumeralsInSchedule(text) {
        if (!text) return "";
        const romanMap = {
            I: "1",
            II: "2",
            III: "3",
            IV: "4",
            V: "5",
            VI: "6",
            VII: "7",
            VIII: "8",
            IX: "9",
            X: "10",
        };
        let processedText = text;
        for (const roman in romanMap) {
            const regex = new RegExp(`\\b${roman}\\b`, "gi");
            processedText = processedText.replace(regex, romanMap[roman]);
        }
        return processedText;
    }

    function formatJamForSpeech(jamText) {
        if (!jamText) return "";
        let formatted = jamText.trim();
        let isRange = false;

        const rangeRegex =
            /(\d{1,2})[:.](\d{2})\s*-\s*(\d{1,2})[:.](\d{2})\s*(WIB)?/gi;
        formatted = formatted.replace(
            rangeRegex,
            (match, hh1, mm1, hh2, mm2, wib) => {
                isRange = true;
                const hour1 = parseInt(hh1, 10);
                const minute1String =
                    mm1 === "00" ? "" : `${parseInt(mm1, 10)}`;
                const time1Speech = `pukul ${hour1}${
                    minute1String ? " " + minute1String : ""
                }`;

                const hour2 = parseInt(hh2, 10);
                const minute2String =
                    mm2 === "00" ? "" : `${parseInt(mm2, 10)}`;
                const time2Speech = `pukul ${hour2}${
                    minute2String ? " " + minute2String : ""
                }`;

                const wibSuffix = wib ? " Waktu Indonesia Barat" : "";
                return `${time1Speech} sampai ${time2Speech}${wibSuffix}`;
            }
        );

        if (!isRange) {
            formatted = formatted.replace(
                /(\d{1,2})[:.](\d{2})\s*WIB/gi,
                (match, hh, mm) => {
                    const hour = parseInt(hh, 10);
                    const minuteString =
                        mm === "00" ? "" : `${parseInt(mm, 10)}`;
                    return `pukul ${hour}${
                        minuteString ? " " + minuteString : ""
                    } Waktu Indonesia Barat`;
                }
            );

            if (
                !/Waktu Indonesia Barat/i.test(formatted) &&
                !(
                    /\bWIB\b/i.test(formatted) &&
                    formatted
                        .substring(formatted.search(/\d{1,2}[:.]\d{2}/))
                        .toUpperCase()
                        .includes("WIB")
                )
            ) {
                formatted = formatted.replace(
                    /(\d{1,2})[:.](\d{2})/gi,
                    (match, hh, mm) => {
                        const hour = parseInt(hh, 10);
                        const minuteString =
                            mm === "00" ? "" : `${parseInt(mm, 10)}`;
                        return `pukul ${hour}${
                            minuteString ? " " + minuteString : ""
                        }`;
                    }
                );
            }
        }
        return formatted.replace(/\s+/g, " ").trim();
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
                "[Jadwal Ibadah] Speech Synthesis Error:",
                event.error
            );
            removeHighlight();
            if (onEndCallback) onEndCallback();
        };

        try {
            synth.speak(utterance);
        } catch (e) {
            console.error("[Jadwal Ibadah] Gagal memulai synth.speak:", e);
            removeHighlight();
            if (onEndCallback) onEndCallback();
        }
    }

    function getTextForElement(element) {
        let compositeText = "";
        const mainTitleH2 = element.matches("h2.speakable.text-brown")
            ? element
            : null;
        const cardElement = element.matches(".card.speakable") ? element : null;

        if (mainTitleH2) {
            compositeText = (
                mainTitleH2.innerText || mainTitleH2.textContent
            ).trim();
        } else if (cardElement) {
            const cardTitleEl = cardElement.querySelector(".card-title");
            const tableEl = cardElement.querySelector("table");
            let cardTitleText = "";
            let tableContentText = "";
            let tableHasData = false;

            if (cardTitleEl) {
                cardTitleText =
                    (cardTitleEl.innerText || cardTitleEl.textContent).trim() +
                    ". ";
            }

            if (tableEl) {
                const rows = tableEl.querySelectorAll("tbody tr");
                rows.forEach((row) => {
                    const cells = row.querySelectorAll("td");
                    if (
                        cells.length === 1 &&
                        cells[0].hasAttribute("colspan")
                    ) {
                        tableContentText +=
                            (
                                cells[0].innerText || cells[0].textContent
                            ).trim() + ". ";
                        tableHasData = true;
                    } else if (cells.length > 0) {
                        tableHasData = true;
                        let rowTexts = [];
                        let keteranganText;

                        if (cells.length === 2) {
                            // Keterangan, Jam
                            keteranganText = (
                                cells[0].innerText || cells[0].textContent
                            ).trim();
                            rowTexts.push(
                                convertRomanNumeralsInSchedule(keteranganText)
                            );
                            rowTexts.push(
                                formatJamForSpeech(
                                    cells[1].innerText || cells[1].textContent
                                )
                            );
                        } else if (cells.length === 3) {
                            // Keterangan, Hari, Jam
                            keteranganText = (
                                cells[0].innerText || cells[0].textContent
                            ).trim();
                            rowTexts.push(
                                convertRomanNumeralsInSchedule(keteranganText)
                            );
                            rowTexts.push(
                                `hari ${(
                                    cells[1].innerText || cells[1].textContent
                                ).trim()}`
                            );
                            rowTexts.push(
                                formatJamForSpeech(
                                    cells[2].innerText || cells[2].textContent
                                )
                            );
                        } else {
                            cells.forEach((cell, index) => {
                                if (index === 0) {
                                    keteranganText = (
                                        cell.innerText || cell.textContent
                                    ).trim();
                                    rowTexts.push(
                                        convertRomanNumeralsInSchedule(
                                            keteranganText
                                        )
                                    );
                                } else {
                                    rowTexts.push(
                                        (
                                            cell.innerText || cell.textContent
                                        ).trim()
                                    );
                                }
                            });
                        }
                        tableContentText += rowTexts.join(", ") + ". ";
                    }
                });
                if (!tableHasData && rows.length === 0) {
                    tableContentText +=
                        "Tidak ada entri jadwal untuk kategori ini. ";
                }
            }
            compositeText = cardTitleText + tableContentText;
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

    function initPageTTS() {
        const allSpeakableElements = document.querySelectorAll(".speakable");
        autoplayQueueInternal = [];

        const mainTitleEl = document.querySelector("h2.speakable.text-brown");
        if (mainTitleEl) {
            autoplayQueueInternal.push({
                element: mainTitleEl,
                text: getTextForElement(mainTitleEl),
            });
        }

        const cardElements = document.querySelectorAll(".card.speakable");
        const maxAutoplayCards = 2;
        for (
            let i = 0;
            i < Math.min(cardElements.length, maxAutoplayCards);
            i++
        ) {
            autoplayQueueInternal.push({
                element: cardElements[i],
                text: getTextForElement(cardElements[i]),
            });
        }

        allSpeakableElements.forEach((element) => {
            element.style.cursor = "pointer";
            element.setAttribute("title", "Klik untuk membacakan teks");
            element.addEventListener("click", function (event) {
                if (event.target.closest("a, button")) {
                    return;
                }
                if (!window.speechEnabled) {
                    if (synth.speaking || synth.pending) synth.cancel();
                    removeHighlight();
                    return;
                }
                stopAutoplayInternal();
                const textToSpeak = getTextForElement(this);
                speakAndHighlight(textToSpeak, this, null);
            });
        });
    }

    function initTTSFromVoiceCheck() {
        clearTimeout(initialVoiceCheckTimeout);
        initPageTTS();
        startAutoplayInternal();
    }

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
});
