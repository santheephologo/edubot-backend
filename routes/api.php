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
    Route::put('/{clientId}/bot/{botId}/token', [ClientController::class, 'updateBotToken']);
    Route::post('/{clientId}/bot', [ClientController::class, 'addBot']);
    Route::put('/bot/tokens/update', [ClientController::class, 'updateTokenUsage']);
    Route::delete('/{clientId}/bot/{botId}', [ClientController::class, 'deleteBot']);
    Route::delete('/{clientId}', [ClientController::class, 'deleteClient']);
});


Route::prefix('bots')->group(function() {
    Route::post('/register', [BotController::class, 'register']);
    Route::put('/{botId}/update', [BotController::class, 'update']);
    Route::get('/', [BotController::class, 'fetchAll']);
    Route::get('/{botId}', [BotController::class, 'fetch']);
    Route::get('/dashboard', [BotController::class, 'dashboard']);
    Route::delete('/{botId}', [BotController::class, 'delete']);
});



Route::post('/store-message', [ChatController::class, 'storeMessage']);
Route::post('/create-new-chat', [ChatController::class, 'createNewChat']);
Route::get('/get-thread-id', [ChatController::class, 'getThreadId']);
Route::get('/get-message', [ChatController::class, 'getMessage']);
Route::get('/list-chats', [ChatController::class, 'listChats']);
