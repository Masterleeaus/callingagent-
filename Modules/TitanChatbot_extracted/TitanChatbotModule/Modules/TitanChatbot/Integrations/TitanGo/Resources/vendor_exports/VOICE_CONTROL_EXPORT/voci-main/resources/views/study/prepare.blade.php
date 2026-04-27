<x-app-layout>

    {{-- Initializing Voices for Voice Selection --}}
    <script defer>

        setTimeout(() => {
            let speech = new SpeechSynthesisUtterance();
            let voices = [];
            const voicesDropdown = document.querySelector('[name="voice"]');
            voices = speechSynthesis.getVoices();
            voices_dropdown.innerHTML = voices.filter(voice => voice.lang.includes('en')).map(voice => `<option value="${voice.name}">${voice.name} (${voice.lang})</option>`).join('');
        }, 50);

    </script>

    {{-- Stopping Voice (If left running) --}}
    <script defer type="text/javascript" src="{{ asset('js/stop_speech.js') }}"></script>

    {{-- Setup Form --}}
    <form class="w-full h-full md:w-2/3 xl:w-1/3 flex flex-col justify-center items-center rounded-md shadow-lg p-8 m-16 outline outline-1 outline-white" method="get" action="{{ route('study.initialize', $deck) }}">
        <div class="flex justify-center items-center">
            
            {{-- Mode Selection --}}
            <p>Classic</p>

            {{-- Slider Checkbox --}}
            <div class="flex items-center justify-center w-full m-8">
                <label for="toogleA" class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="hidden" name="mode" value="off">
                        <input id="toogleA" name="mode" type="checkbox" checked class="sr-only" />
                        <div class="w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                        <div class="dot absolute w-6 h-6 bg-white rounded-full shadow -left-1 -top-1 transition"></div>
                    </div>
                </label>
            </div>

            <p>Voice</p>
        </div>

        {{-- Speed Selection --}}
        <label>Voice Speed</label>
        <input type="range" min="0.5" max="1.3" step="0.001" value="0.8" id="rate" name="rate" class="w-full h-2 bg-gray-400 rounded-lg appearance-none cursor-pointer mb-12 slider">

        {{-- Voice Selection --}}
        <label>Select Voice</label>
        <select class="w-full" name="voice_style" id="voices_dropdown">
            <option value=""></option>
        </select>

        {{-- Begin Button --}}
        <x-primary-button class="bg-green-500 mt-8">Begin</x-primary-button>

    </form>

</x-app-layout>