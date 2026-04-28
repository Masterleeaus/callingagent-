document.addEventListener("DOMContentLoaded", () => {
    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;
    const toggleButton = document.getElementById("speech-rec-toggle");
    const statusIndicator = document.getElementById("speech-rec-status");
    const buttonIcon = toggleButton ? toggleButton.querySelector("i") : null;

    let recognition = null;
    let isListening = false;

    const commandConfig = {
        NAV_JADWAL: {
            keywords: ["jadwal", "ibadah"],
            url: "/jadwal-ibadah",
            feedback: "Membuka Jadwal Ibadah...",
        },
        NAV_BERANDA: {
            keywords: ["beranda", "utama", "home", "kembali"],
            url: "/",
            feedback: "Membuka Beranda...",
        },
        NAV_RENUNGAN: {
            keywords: ["renungan", "baca"],
            url: "/renungan",
            feedback: "Membuka Renungan...",
        },
        NAV_WARTA: {
            keywords: ["warta", "jemaat"],
            url: "/warta-jemaat",
            feedback: "Membuka Warta Jemaat...",
        },
        NAV_TATA_IBADAH: {
            keywords: ["tata", "tata ibadah"],
            url: "/tata-ibadah",
            feedback: "Membuka Tata Ibadah...",
        },
        NAV_INFO: {
            keywords: ["info", "informasi", "tentang"],
            url: "/info",
            feedback: "Membuka Info...",
        },
    };

    if (!SpeechRecognition || !toggleButton || !buttonIcon) {
        if (toggleButton) toggleButton.style.display = "none";
        return;
    }

    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.lang = "id-ID";
    recognition.interimResults = false;

    function startRecognition() {
        if (isListening) return;
        if (window.speechSynthesis && window.speechSynthesis.speaking) {
            updateUI("error", "TTS sedang aktif");
            setTimeout(() => {
                if (!isListening) updateUI("off");
            }, 2000);
            return;
        }
        try {
            recognition.start();
            isListening = true;
            updateUI("listening");
        } catch (error) {
            updateUI("error", "Gagal memulai.");
            isListening = false;
        }
    }

    function stopRecognition() {
        if (!isListening && recognition) {
            try {
                recognition.abort();
            } catch (e) {}
        }
        if (!isListening) return;
        try {
            recognition.stop();
            isListening = false;
        } catch (error) {
            updateUI("off");
            isListening = false;
        }
    }

    function updateUI(state, message = "") {
        switch (state) {
            case "listening":
                buttonIcon.className = "fas fa-microphone";
                toggleButton.classList.remove(
                    "btn-success",
                    "btn-danger",
                    "btn-success"
                );
                toggleButton.classList.add("btn-primary");
                if (statusIndicator)
                    statusIndicator.textContent = "Mendengarkan...";
                toggleButton.title = "Nonaktifkan Perintah Suara";
                break;
            case "processing":
                buttonIcon.className = "fas fa-spinner fa-spin";
                toggleButton.classList.remove(
                    "btn-primary",
                    "btn-danger",
                    "btn-success"
                );
                toggleButton.classList.add("btn-success");
                if (statusIndicator)
                    statusIndicator.textContent = "Memproses...";
                toggleButton.title = "Memproses Perintah Suara";
                break;
            case "success":
                buttonIcon.className = "fas fa-check";
                toggleButton.classList.remove(
                    "btn-primary",
                    "btn-danger",
                    "btn-success"
                );
                toggleButton.classList.add("btn-success");
                if (statusIndicator)
                    statusIndicator.textContent =
                        message || "Perintah dikenali!";
                toggleButton.title = "Perintah Suara Berhasil";
                break;
            case "error":
                buttonIcon.className = "fas fa-exclamation-triangle";
                toggleButton.classList.remove(
                    "btn-primary",
                    "btn-success",
                    "btn-success"
                );
                toggleButton.classList.add("btn-danger");
                if (statusIndicator)
                    statusIndicator.textContent = `Error: ${
                        message || "Tidak diketahui"
                    }`;
                toggleButton.title = `Error: ${message || "Tidak diketahui"}`;
                if (message !== "Izin mikrofon ditolak.") {
                    setTimeout(() => {
                        if (!isListening) updateUI("off");
                    }, 3000);
                }
                break;
            case "off":
            default:
                buttonIcon.className = "fas fa-microphone-slash";
                toggleButton.classList.remove(
                    "btn-primary",
                    "btn-danger",
                    "btn-success",
                    "btn-success"
                );
                toggleButton.classList.add("btn-success");
                if (statusIndicator)
                    statusIndicator.textContent = "Perintah Suara Nonaktif";
                toggleButton.title = "Aktifkan Perintah Suara";
                break;
        }
    }

    recognition.onstart = () => {};

    recognition.onresult = (event) => {
        updateUI("processing");
        const transcript = event.results[event.results.length - 1][0].transcript
            .trim()
            .toLowerCase();

        let matchedCommand = null;

        for (const commandId in commandConfig) {
            const config = commandConfig[commandId];
            const atLeastOneKeywordFound = config.keywords.some((keyword) =>
                transcript.includes(keyword)
            );

            if (atLeastOneKeywordFound) {
                matchedCommand = config;
                break;
            }
        }

        if (matchedCommand) {
            updateUI("success", matchedCommand.feedback);
            stopRecognition();

            setTimeout(() => {
                window.location.href = matchedCommand.url;
            }, 700);
        } else {
            updateUI(
                "error",
                `Tidak dikenal: "${transcript.substring(0, 30)}${
                    transcript.length > 30 ? "..." : ""
                }"`
            );
        }
    };

    recognition.onerror = (event) => {
        isListening = false;
        let errorMessage = event.error;
        if (event.error === "no-speech")
            errorMessage = "Tidak ada suara terdeteksi.";
        else if (event.error === "audio-capture")
            errorMessage = "Mikrofon bermasalah.";
        else if (event.error === "not-allowed")
            errorMessage = "Izin mikrofon ditolak.";
        else if (event.error === "network") errorMessage = "Masalah jaringan.";
        else errorMessage = "Error tidak diketahui.";

        updateUI("error", errorMessage);
    };

    recognition.onend = () => {
        const wasListening = isListening;
        isListening = false;
        const currentState = buttonIcon.className;
        if (
            wasListening &&
            !currentState.includes("fa-check") &&
            !currentState.includes("fa-exclamation-triangle")
        ) {
            updateUI("off");
        }
    };

    toggleButton.addEventListener("click", () => {
        if (isListening) {
            stopRecognition();
        } else {
            startRecognition();
        }
    });

    window.addEventListener("beforeunload", () => {
        if (isListening) {
            stopRecognition();
        }
    });

    updateUI("off");
});
