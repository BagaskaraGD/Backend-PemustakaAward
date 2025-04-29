<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('mahal');
});
Route::get('/test-web', function() {
    return 'WEB Route OK';
});