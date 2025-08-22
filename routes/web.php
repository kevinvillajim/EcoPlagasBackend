<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-web', function () {
    return response()->json(['message' => 'Web route working']);
});

// Login route for compatibility (redirects to API docs or SPA)
Route::get('/login', function () {
    return response()->json([
        'message' => 'This is an API endpoint. Please use POST to /api/auth/login with credentials.',
        'api_login_url' => url('/api/auth/login')
    ], 200);
})->name('login');
