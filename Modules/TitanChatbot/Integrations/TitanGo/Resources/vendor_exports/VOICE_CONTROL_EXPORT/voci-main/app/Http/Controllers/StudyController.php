<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class StudyController extends Controller
{
    /**
     * prime is used to show the user the study preparation screen.
     */
    public function prime(Deck $deck){
        
        if($deck->user_id != Auth::id()){
            return abort(403);
        }

        return view('study.prepare')->with('deck', $deck);
    }

    // Initialize sets up the enviroment for the user to study a given deck
    public function initialize(Deck $deck, Request $request){
        
        if($deck->user_id != Auth::id()){
            return abort(403);
        }

        // Setting the rate of the voice, defaults to 1
        if (($request->input('rate')) != null)
            session(['rate' => $request->input('rate')]);
        else
            session(['rate' => 1]);

        // Setting the voice, if set
        if (($request->input('voice_style')) != null)
            session(['voice_style' => $request->input('voice_style')]);

        // Determining whether the user wants voice mode or not.

        if ($request->input('mode') == "off")
            session(['voice' => "false"]);
        else
            session(['voice' => "true"]);

        // Obtaining the array of cards to study
        $cards = (DB::table('cards')->select('front', 'back')->where('deck_id', $deck->deck_id)->get())->toArray();
        shuffle($cards);

        // Setup session variables to study
        session(['study_deck' => $cards]);
        session(['index' => count($cards) - 1]);
        session(['side' => 1]);

        return to_route('study.show', $deck);
    }

    public function show(Deck $deck){
        
        // Stop voice 
        echo '<script> speechSynthesis.cancel(); </script>';

        // Check if user is authenticated
        if($deck->user_id != Auth::id()){
            return abort(403);
        }

        $cards = session('study_deck');
        $index = session('index');

        // Flipping the side of the card
        $side = (session('side') == 0) ? 1 : 0;
        session(['side' => $side]);

        // Displaying either the study/show screen, or the study/end.
        if (session('index') >= 0){
            return view('study.show')->with('cards', $cards)->with('index', $index)->with('deck', $deck);
        }
        else {
            return view('study.end')->with('deck', $deck);
        }
    }
}
