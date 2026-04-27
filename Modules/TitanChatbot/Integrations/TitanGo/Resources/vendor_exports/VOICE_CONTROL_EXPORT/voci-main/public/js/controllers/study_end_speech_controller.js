// This script is used in the study/end view
// It handles all of the speech recognition for that page.

if ('webkitSpeechRecognition' in window) {
    var recognition = new webkitSpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.lang = 'en-US';

    recognition.onend = function() {
        recognition.start();
    };

    recognition.onresult = function(event) {
        var final_transcript = '';

        for (var i = event.resultIndex; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
                final_transcript += event.results[i][0].transcript;
            }
        }

        // *********************************************************************************
        //                                      COMMANDS
        // *********************************************************************************

        if (final_transcript.includes("reshuffle")){
            let deck_uuid = document.getElementById("deck_uuid").textContent;
            
            // Get the root URL
            var rootUrl = window.location.origin;

            // Navigate to the root directory
            window.location.href = rootUrl + '/study/initialize/' + deck_uuid;

        }
        else if (final_transcript.includes("again")){
            let deck_uuid = document.getElementById("deck_uuid").textContent;

            // Get the root URL
            var rootUrl = window.location.origin;

            // Navigate to the root directory
            window.location.href = rootUrl + '/study/initialize/' + deck_uuid;
        }
        else if (final_transcript.includes("decks") ||
            final_transcript.includes("exit")){

            // Get the root URL
            var rootUrl = window.location.origin;

            // Navigate to the root directory
            window.location.href = rootUrl + '/decks/';
        }
        else if (final_transcript.includes("repeat") || 
                final_transcript.includes("recite")){

            // Run text to speech script
            speechSynthesis.speak(utterance);
    }
    };

    recognition.start();
    } else {
    console.warn('Webkit Speech Recognition API is not supported.');
    }