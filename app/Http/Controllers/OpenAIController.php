<?php

namespace App\Http\Controllers;

use App\Repositories\OpenAIRepoInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Illuminate\Support\Facades\Log;

class OpenAIController extends Controller implements MessageComponentInterface
{
    // protected $openAIRepository;

    // public function __construct(OpenAIRepoInterface $openAIRepository)
    // {
    //     $this->openAIRepository = $openAIRepository;
    // }
    // // Handles WebSocket connections
    // public function onOpen(ConnectionInterface $conn)
    // {
    //     Log::info("New WebSocket connection established: " . $conn->resourceId);
    // }

    // // Handles WebSocket messages
    // public function onMessage(ConnectionInterface $from, $msg)
    // {
    //     Log::info("Message received: " . $msg);

    //     // Optionally process the message and return a response
    //     $response = [
    //         'message' => $msg,
    //         'timestamp' => now(),
    //     ];

    //     $from->send(json_encode($response));
    // }

    // // Handles WebSocket disconnections
    // public function onClose(ConnectionInterface $conn)
    // {
    //     Log::info("WebSocket connection closed: " . $conn->resourceId);
    // }

    // // Handles WebSocket errors
    // public function onError(ConnectionInterface $conn, \Exception $e)
    // {
    //     Log::error("WebSocket error: " . $e->getMessage());
    //     $conn->close();
    // }
    // public function createThread()
    // {

    //     $threadId = $this->openAIRepository->createThread();

    //     if ($threadId) {
    //         return response()->json(['thread_id' => $threadId], 201);
    //     }

    //     return response()->json(['error' => 'Failed to create thread'], 500);
    // }
}
