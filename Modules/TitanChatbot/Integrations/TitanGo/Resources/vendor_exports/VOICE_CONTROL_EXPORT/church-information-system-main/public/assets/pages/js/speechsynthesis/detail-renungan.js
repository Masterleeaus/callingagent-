document.addEventListener("DOMContentLoaded", function () {
    const playPauseBtn = document.getElementById("playPauseBtn");
    const stopBtn = document.getElementById("stopBtn");
    const renunganMeta = document.getElementById("renungan-meta");
    const renunganBody = document.getElementById("renungan-body-content");
    const synthesisAvailable =
        "speechSynthesis" in window && "SpeechSynthesisUtterance" in window;
    const synth = synthesisAvailable ? window.speechSynthesis : null;

    if (!synthesisAvailable) {
        const player = document.getElementById("speechPlayer");
        if (player) player.style.display = "none";
        return;
    }
    if (!playPauseBtn || !stopBtn || !renunganMeta || !renunganBody) {
        return;
    }

    let mainPlayerUtterance = null;
    let isMainPlayerPaused = false;
    let fullTextChunksCache = null;
    let currentlyHighlightedElement = null;
    let currentChunkIndex = -1;

    function localFixBibleCitation(text) {
        if (!text) return "";
        try {
            return text.replace(
                /(\b[A-Za-z]+(?:\s[A-Za-z]+){0,2})\s?(\d{1,3})\s?[:.]\s?(\d{1,3})(?:\s?[-–—]\s?(\d{1,3}))?/g,
                function (match, book, chapter, verseStart, verseEnd) {
                    book = book.replace(/<[^>]*>/g, "").trim();
                    let result = `${book} pasal ${parseInt(
                        chapter,
                        10
                    )} ayat ${parseInt(verseStart, 10)}`;
                    if (verseEnd) result += ` sampai ${parseInt(verseEnd, 10)}`;
                    return result;
                }
            );
        } catch (e) {
            return text;
        }
    }
    function localPreprocessText(text) {
        if (!text) return "";
        try {
            text = localFixBibleCitation(text);
            text = text.replace(/GPIB/gi, "G P I B");
            text = text.replace(/Pdt\./gi, "Pendeta");
            text = text.replace(/Pnt\./gi, "Penatua");
            text = text.replace(/Dkn\./gi, "Diaken");
            text = text.replace(/(?:https?|ftp):\/\/[\n\S]+/g, "");
            text = text.replace(
                /(\d{1,2})\s?[:.]\s?(\d{2})\s*-\s*(\d{1,2})\s?[:.]\s?(\d{2})\s*WIB/gi,
                (m, sH, sM, eH, eM) =>
                    `pukul ${parseInt(sH, 10)} ${
                        parseInt(sM, 10) === 0 ? "" : sM
                    } sampai pukul ${parseInt(eH, 10)} ${
                        parseInt(eM, 10) === 0 ? "" : eM
                    } Waktu Indonesia Barat`
            );
            text = text.replace(
                /(\d{1,2})\s?[:.]\s?(\d{2})\s*WIB/gi,
                (m, h, i) =>
                    `pukul ${parseInt(h, 10)} ${
                        parseInt(i, 10) === 0 ? "" : i
                    } Waktu Indonesia Barat`
            );
            text = text.replace(
                /(\d{1,2})\s?[:.]\s?(\d{2})(?!\s*[:.])/g,
                (m, h, i) =>
                    `pukul ${parseInt(h, 10)} ${parseInt(i, 10) === 0 ? "" : i}`
            );
            text = text
                .replace(/([.,?!:;])\s*/g, "$1 ")
                .replace(/\s+/g, " ")
                .trim();
            return text;
        } catch (e) {
            return text.replace(/\s+/g, " ").trim();
        }
    }

    function updateMainPlayerUI(state) {
        switch (state) {
            case "playing":
                playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                playPauseBtn.title = "Pause Seluruh Renungan";
                playPauseBtn.classList.add("playing");
                stopBtn.disabled = false;
                playPauseBtn.disabled = false;
                isMainPlayerPaused = false;
                break;
            case "paused":
                playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
                playPauseBtn.title = "Lanjutkan Seluruh Renungan";
                playPauseBtn.classList.add("playing");
                stopBtn.disabled = false;
                playPauseBtn.disabled = false;
                break;
            case "stopped":
            default:
                playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
                playPauseBtn.title = "Play Seluruh Renungan";
                playPauseBtn.classList.remove("playing");
                stopBtn.disabled = true;
                playPauseBtn.disabled = false;
                isMainPlayerPaused = false;
                mainPlayerUtterance = null;
                currentChunkIndex = -1;
                break;
        }
    }

    function removeHighlight() {
        if (currentlyHighlightedElement) {
            currentlyHighlightedElement.classList.remove("speaking-highlight");
            currentlyHighlightedElement = null;
        }
    }

    function highlightElement(element) {
        removeHighlight();
        if (element) {
            element.classList.add("speaking-highlight");
            currentlyHighlightedElement = element;
        }
    }

    function speakText(
        text,
        elementToHighlight = null,
        isFromMainPlayer = false,
        chunkIdx = -1
    ) {
        if (!text || !synth) return;

        if (!isFromMainPlayer) {
            synth.cancel();
            removeHighlight();
            updateMainPlayerUI("stopped");
        }

        setTimeout(
            () => {
                const processedText = localPreprocessText(text);
                if (!processedText) {
                    if (isFromMainPlayer) {
                        speakChunk(chunkIdx + 1);
                    }
                    return;
                }

                const utterance = new SpeechSynthesisUtterance(processedText);
                const voice =
                    typeof window.getSelectedVoice === "function"
                        ? window.getSelectedVoice()
                        : null;

                if (voice) {
                    utterance.voice = voice;
                    utterance.lang = voice.lang;
                } else {
                    utterance.lang = "id-ID";
                }
                utterance.rate = 1.0;
                utterance.pitch = 1;

                if (isFromMainPlayer) {
                    mainPlayerUtterance = utterance;
                    currentChunkIndex = chunkIdx;
                }

                utterance.onstart = () => {
                    if (elementToHighlight) {
                        highlightElement(elementToHighlight);
                    }
                    if (isFromMainPlayer && !isMainPlayerPaused) {
                        updateMainPlayerUI("playing");
                    }
                };

                utterance.onend = () => {
                    if (isFromMainPlayer) {
                        if (!isMainPlayerPaused) {
                            speakChunk(chunkIdx + 1);
                        }
                    } else {
                        removeHighlight();
                        updateMainPlayerUI("stopped");
                    }
                };

                utterance.onpause = () => {
                    if (isFromMainPlayer) {
                        isMainPlayerPaused = true;
                        updateMainPlayerUI("paused");
                    }
                };

                utterance.onresume = () => {
                    if (isFromMainPlayer) {
                        isMainPlayerPaused = false;
                        updateMainPlayerUI("playing");
                    }
                };

                utterance.onerror = (event) => {
                    removeHighlight();
                    if (event.error !== "interrupted") {
                        console.error("Utterance Error:", event.error);
                        alert(`Error TTS: ${event.error}`);
                    }
                    if (isFromMainPlayer) {
                        updateMainPlayerUI("stopped");
                    }
                };

                synth.speak(utterance);
            },
            isFromMainPlayer ? 50 : 100
        );
    }

    function collectTextChunks() {
        if (fullTextChunksCache) return fullTextChunksCache;

        const chunks = [];
        const processNode = (node) => {
            const text = (node.innerText || node.textContent || "").trim();
            if (text) {
                chunks.push({ element: node, text: text });
            }
        };

        if (renunganMeta) {
            renunganMeta.querySelectorAll("h1, h6").forEach(processNode);
        }
        if (renunganBody) {
            renunganBody
                .querySelectorAll("p, h1, h2, h3, h4, h5, h6, li, blockquote")
                .forEach(processNode);
        }

        fullTextChunksCache = chunks;
        return fullTextChunksCache;
    }

    function speakChunk(index) {
        const chunks = collectTextChunks();
        if (index >= 0 && index < chunks.length) {
            const chunk = chunks[index];
            speakText(chunk.text, chunk.element, true, index);
        } else {
            removeHighlight();
            updateMainPlayerUI("stopped");
        }
    }

    function playFullRenungan() {
        if (
            typeof window.speechEnabled !== "undefined" &&
            !window.speechEnabled
        ) {
            alert("Fitur pembaca teks tidak aktif.");
            updateMainPlayerUI("stopped");
            return;
        }
        synth.cancel();
        removeHighlight();
        isMainPlayerPaused = false;
        currentChunkIndex = -1;
        speakChunk(0);
    }

    function stopAllReading() {
        synth.cancel();
        removeHighlight();
        updateMainPlayerUI("stopped");
    }

    playPauseBtn.addEventListener("click", () => {
        if (!synth) return;

        if (synth.paused && isMainPlayerPaused && mainPlayerUtterance) {
            synth.resume();
        } else if (
            synth.speaking &&
            !isMainPlayerPaused &&
            mainPlayerUtterance
        ) {
            synth.pause();
        } else {
            playFullRenungan();
        }
    });

    stopBtn.addEventListener("click", stopAllReading);

    function handleContentClick(event) {
        const targetElement = event.target.closest(
            "h1, h2, h3, h4, h5, h6, p, li, blockquote"
        );
        if (targetElement && synth) {
            const textToSpeak = (
                targetElement.innerText ||
                targetElement.textContent ||
                ""
            ).trim();
            if (textToSpeak) {
                speakText(textToSpeak, targetElement, false);
            }
        }
    }

    renunganMeta.addEventListener("click", handleContentClick);
    renunganBody.addEventListener("click", handleContentClick);

    document.addEventListener("tts-global-disabled", stopAllReading);
    window.addEventListener("beforeunload", () => {
        if (synth && (synth.speaking || synth.paused)) {
            synth.cancel();
        }
    });

    updateMainPlayerUI("stopped");
    playFullRenungan();
});
