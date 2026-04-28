<x-app-layout>
    <div class="m-auto h-screen flex flex-col justify-center items-center content-center">

        {{-- Card Div --}}
        <div class="text-center rounded-md m-4 mb-8 p-4 flex flex-col justify-center w-full md:w-10/12 xl:w-2/3 shadow-lg">

            @if (session('side') == 0)
                <p id="read" class="m-8 break-word font-qs text-3xl">{{ $cards[$index]->front; }}</p>
            
            @else
                <p id="read" class="m-8 break-word font-qs text-3xl">{{ $cards[$index]->back; }}</p>
                {{ session(['index' => session('index') - 1]); }}
            @endif

        </div>

        {{-- Flip Button --}}
        <a href="{{ route('study.show', $deck) }}"><x-primary-button>Next</x-primary-button></a>


        {{-- ****************************************** --}}
        {{--              VOICE CONTROLLER              --}}
        {{-- ****************************************** --}}

        @if (session('voice') == "true")
            {{-- Passing voice rate from php into JS --}}
            <div id="rate" style="display: none">{{ session('rate'); }}</div>
            <div id="voice_style" style="display: none">{{ session('voice_style'); }}</div>
            {{-- Text To Speech Script --}}
            <script src="{{ asset('js/text_to_speech.js') }}"></script>
            {{-- Speech Recognition Script --}}
            <script type="text/javascript" src="{{ asset('js/controllers/study_speech_controller.js') }}"></script>
        @endif

    </div>
</x-app-layout>