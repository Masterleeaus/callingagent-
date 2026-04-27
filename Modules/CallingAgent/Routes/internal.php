<?php
use Illuminate\Support\Facades\Route;
Route::prefix('internal/calling-agent')->group(function () { Route::get('/health', fn()=>['ok'=>true,'module'=>'CallingAgent']); });
