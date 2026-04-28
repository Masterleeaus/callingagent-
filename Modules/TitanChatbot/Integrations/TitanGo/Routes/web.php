<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth'],
    'prefix' => 'dashboard/user/titango',
    'as' => 'dashboard.user.titango.',
    'namespace' => 'Modules\\TitanGo\\Http\\Controllers',
], function () {
    Route::get('/upgrade', 'UpgradeController@index')->name('upgrade');

    Route::group(['middleware' => ['titango.entitled']], function () {
        Route::get('/voice', 'VoiceController@index')->name('voice');
        Route::post('/voice/execute', 'VoiceController@execute')->name('voice.execute');
        Route::post('/voice/tts', 'VoiceTtsController@speak')->name('voice.tts');
    });
});

Route::group([
    'middleware' => ['web', 'auth', 'admin'],
    'prefix' => 'dashboard/admin/settings/titango',
    'as' => 'dashboard.admin.titango.',
    'namespace' => 'Modules\\TitanGo\\Http\\Controllers\\Admin',
], function () {
    Route::get('/', 'SettingsController@index')->name('settings');
    Route::post('/', 'SettingsController@update')->name('settings.update');
});
