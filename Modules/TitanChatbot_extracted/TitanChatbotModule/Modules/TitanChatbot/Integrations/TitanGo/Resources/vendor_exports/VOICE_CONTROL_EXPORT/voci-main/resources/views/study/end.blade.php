<x-app-layout>
    <div id="deck_uuid" style="display: none">{{ $deck->uuid; }}</div>
        <div class="h-screen flex flex-col justify-center items-center content-center">
            
            {{-- Out of cards. --}}
            <p id="read" class="font-qs text-3xl">Out of cards.</p>

            {{-- Reshuffle Button --}}
            <form class="m-8" action="{{ route('study.initialize', $deck) }}" method="get">
                <input type="hidden" name="mode" value="{{ (session('voice') == "true") ? "on" : "" }}">
                <x-primary-button>Reshuffle</x-primary-button>
            </form>

            {{-- Decks Button --}}
            <a href="{{ route('decks.index') }}"><x-primary-button>Decks</x-primary-button></a>

        </div>

    {{-- ****************************************** --}}
    {{--              VOICE CONTROLLER              --}}
    {{-- ****************************************** --}}

    @if (session('voice') == "true")
        {{-- Passing php parameters into JS --}}
        <div id="rate" style="display: none">{{ session('rate'); }}</div>
        <div id="voice_style" style="display: none">{{ session('voice_style'); }}</div>
        {{-- Text To Speech Script --}}
        <script src="{{ asset('js/text_to_speech.js') }}"></script>
        {{-- Speech Recognition Script --}}
        <script type="text/javascript" src="{{ asset('js/controllers/study_end_speech_controller.js') }}"></script>
    @endif

</x-app-layout>