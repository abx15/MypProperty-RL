<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API versioning
Route::prefix('v1')->group(function () {
    require __DIR__.'/api/v1.php';
});

// API info endpoint
Route::get('/info', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'status' => 'active',
        'endpoints' => [
            'v1' => url('/api/v1')
        ]
    ]);
});
