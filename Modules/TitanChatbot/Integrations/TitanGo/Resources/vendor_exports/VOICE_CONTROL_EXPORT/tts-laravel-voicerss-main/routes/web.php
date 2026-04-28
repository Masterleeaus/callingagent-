<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TTSController;

// Rota inicial (formulário)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rota que processa o texto e gera o áudio
Route::post('/speak', [TTSController::class, 'speak'])->name('speak');
