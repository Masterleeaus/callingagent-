// Stop speech is used to prevent 

if (speechSynthesis.speaking) {
    
    setTimeout(() => {
        speechSynthesis.cancel();
    }, 50);

    if (sayTimeout !== null)
        clearTimeout(sayTimeout);

    sayTimeout = setTimeout(function () { say(text); }, 250);
}