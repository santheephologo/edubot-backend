<?php

namespace App\Http\Controllers;

use App\Repositories\ChatRepo;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $chatRepo;

    public function __construct(ChatRepo $chatRepo)
    {
        $this->chatRepo = $chatRepo;
    }

    // Store a new message in a chat history
    public function storeMessage(Request $request)
    {
        $sessionId = $request->input('session_id');
        $reqMessage = $request->input('req_message');
        $resMessage = $request->input('res_message');

        $result = $this->chatRepo->storeMessage($sessionId, $reqMessage, $resMessage);

        if ($result) {
            return response()->json(['success' => true, 'message' => 'Message stored successfully'], 201);
        }

        return response()->json(['success' => false, 'message' => 'Error storing message'], 500);
    }

    // Create a new chat session
    public function createNewChat(Request $request)
    {
        $clientId = $request->input('client_id');
        $botId = $request->input('bot_id');
        $threadId = $request->input('thread_id');
        $sessionId = $this->chatRepo->createNewChat($clientId, $botId, $threadId);

        if ($sessionId) {
            return response()->json(['success' => true, 'session_id' => $sessionId], 201);
        }

        return response()->json(['success' => false, 'message' => 'Error creating new chat'], 500);
    }

    // Get the thread ID of a session
    public function getThreadId(Request $request)
    {
        $sessionId = $request->input('session_id');
        $threadId = $this->chatRepo->getThreadId($sessionId);

        if ($threadId) {
            return response()->json(['success' => true, 'thread_id' => $threadId], 200);
        }

        return response()->json(['success' => false, 'message' => 'Error fetching thread ID'], 500);
    }

    // Get all messages from a specific session
    public function getMessage($sessionId)
    {
        //$sessionId = $request->input('session_id');
        $messages = $this->chatRepo->getMessage($sessionId);

        if ($messages !== null) {
            return response()->json(['success' => true, 'messages' => $messages], 200);
        }

        return response()->json(['success' => false, 'message' => 'Error fetching messages'], 500);
    }

    // List chats based on session ID prefix
    public function listChats($userId, $botId)
    {
        $sessionIdPrefix = `${userId}_${botId}`;
        $chats = $this->chatRepo->listChats($sessionIdPrefix);

        if ($chats !== null) {
            return response()->json(['success' => true, 'chats' => $chats], 200);
        }

        return response()->json(['success' => false, 'message' => 'Error listing chats'], 500);
    }
}
