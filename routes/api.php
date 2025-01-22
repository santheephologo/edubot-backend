<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenAIController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\ChatController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/', [OpenAIController::class, 'createThread']);


Route::prefix('clients')->group(function() {
    Route::post('/register', [ClientController::class, 'register']);
    Route::post('/login', [ClientController::class, 'login']);
    Route::get('/', [ClientController::class, 'fetchClients']);
    Route::get('/{username}', [ClientController::class, 'returnClient']);
    Route::get('/{clientId}/{botId}/tokens', [ClientController::class, 'returnTokenInfo']);
    Route::put('/token/add', [ClientController::class, 'updateBotToken']);
    Route::post('/bot/add', [ClientController::class, 'addBot']);
    Route::put('/bot/tokens/update', [ClientController::class, 'updateTokenUsage']);
    Route::put('/unsubscribe', [ClientController::class, 'deleteBot']);
    Route::delete('/{clientId}', [ClientController::class, 'deleteClient']);
});


Route::prefix('bots')->group(function() {
    Route::post('/register', [BotController::class, 'register']);
    Route::put('/update', [BotController::class, 'update']);
    Route::get('/', [BotController::class, 'fetchAll']);
    Route::get('/{botId}', [BotController::class, 'fetch']);
    Route::get('/fetch/db', [BotController::class, 'fetchDashboard']);
    Route::delete('/delete/{botId}', [BotController::class, 'delete']);
    Route::get('/addons/fetch', [BotController::class, 'fetchAddons']);
    Route::put('/addons/update', [BotController::class, 'updateAddon']);
    Route::post('/addons/store', [BotController::class, 'storeAddon']);
    Route::delete('/addons/delete/{addonId}', [BotController::class, 'deleteAddon']);
});

Route::prefix('chat')->group(function() {
    Route::post('/store-message', [ChatController::class, 'storeMessage']);
    Route::post('/create-new-chat', [ChatController::class, 'createNewChat']);
    Route::get('/get-thread-id', [ChatController::class, 'getThreadId']);
    Route::get('/get-message/{sessionId}', [ChatController::class, 'getMessage']);
    Route::get('/list-chats/{userId}/{botId}', [ChatController::class, 'listChats']);
});


