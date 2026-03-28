<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| All non-API routes serve the React app (index.html from public/)
| React Router handles all the frontend routing client-side.
*/

Route::get('/{any}', function () {
    $indexPath = public_path('index.html');

    // If React build exists, serve it
    if (file_exists($indexPath)) {
        return response()->file($indexPath);
    }

    // Fallback for when React hasn't been built yet
    return response()->json([
        'message' => 'ParaPharmacie API is running. Frontend not built yet.',
        'api'     => url('/api'),
    ]);
})->where('any', '.*');
