<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\AuthController;
use App\Services\TicketService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Ticket Create API
    Route::post('/tickets', function (Request $request, TicketService $service) {
        $validated = $request->validate([
            'subject' => 'required|string|min:5',
            'message' => 'required|string|min:10',
        ]);

        $ticket = $service->storeTicket($validated);
        return response()->json(['status' => true, 'data' => $ticket]);
    });

    // Ticket List API
    Route::get('/tickets', function () {
        return response()->json(App\Models\Ticket::where('user_id', Auth::id())->latest()->get());
    });
});