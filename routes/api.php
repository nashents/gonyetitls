<?php

use App\Http\Controllers\EzyTrackWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// EzyTrack's Device Manager pushes real-time position updates here. The
// default 60/min "api" throttle is sized for logged-in users, not a shared
// device-manager egress IP pushing on behalf of a whole fleet, so this route
// gets its own higher limit instead.
Route::post('/webhooks/ezytrack', [EzyTrackWebhookController::class, 'store'])
    ->withoutMiddleware('throttle:api')
    ->middleware(['ezytrack.token', 'throttle:600,1'])
    ->name('webhooks.ezytrack');
