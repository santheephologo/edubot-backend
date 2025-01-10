<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Services\OpenAiRepoInterface;
use Carbon\Carbon;

class ChatRepo implements ChatRepoInterface
{
    protected $openaiRepo;

    public function __construct(OpenAiRepoInterface $openaiRepo)
    {
        $this->openaiRepo = $openaiRepo;
    }

    public function storeMessage($sessionId, $reqMessage, $resMessage)
    {
        try {
            // Fetch the chat history using raw DB query
            $chatHistory = DB::table('chat_histories')->where('session_id', $sessionId)->first();

            if ($chatHistory) {
                // Retrieve existing messages from the 'messages' column and decode it
                $messages = json_decode($chatHistory->messages, true);

                // If no messages exist, initialize as an empty array
                if (!$messages) {
                    $messages = [];
                }

                // Add the new messages to the array
                $messages[] = ['sender' => 'User', 'message' => $reqMessage];
                $messages[] = ['sender' => 'Bot', 'message' => $resMessage];

                // Update the messages in the database as a JSON string
                DB::table('chat_histories')->where('session_id', $sessionId)
                    ->update(['messages' => json_encode($messages)]);

                return $chatHistory;
            }

            return null;
        } catch (\Exception $e) {
            \Log::error("Error storing message: " . $e->getMessage());
            return null;
        }
    }

    public function createNewChat($clientId, $botId)
    {
        try {
            $timestamp = Carbon::now()->format('Y-m-d H:i:s');
            $sessionId = "{$clientId}_{$botId}_{$timestamp}";
            $threadId = $this->openaiRepo->createThread();

            // Insert new chat history using raw DB query
            DB::table('chat_histories')->insert([
                'session_id' => $sessionId,
                'thread_id' => $threadId,
                'messages' => json_encode([]) // Initialize with empty messages
            ]);

            return $sessionId;
        } catch (\Exception $e) {
            \Log::error("Error creating new chat: " . $e->getMessage());
            return null;
        }
    }

    public function getThreadId($sessionId)
    {
        try {
            // Fetch thread ID using raw DB query
            $chatHistory = DB::table('chat_histories')->where('session_id', $sessionId)->first();
            return $chatHistory ? $chatHistory->thread_id : null;
        } catch (\Exception $e) {
            \Log::error("Error fetching thread ID: " . $e->getMessage());
            return null;
        }
    }

    public function getMessage($sessionId)
    {
        try {
            // Fetch chat history and retrieve messages using raw DB query
            $chatHistory = DB::table('chat_histories')->where('session_id', $sessionId)->first();
            if ($chatHistory) {
                $messages = json_decode($chatHistory->messages, true);
                return $messages ? $messages : null;
            }
            return null;
        } catch (\Exception $e) {
            \Log::error("Error fetching messages: " . $e->getMessage());
            return null;
        }
    }

    public function listChats($sessionIdPrefix)
    {
        try {
            // List all chats for the session ID prefix using raw DB query
            $chats = DB::table('chat_histories')
                ->where('session_id', 'like', "{$sessionIdPrefix}%")
                ->get();

            $result = [];
            foreach ($chats as $chat) {
                // Decode messages from JSON
                $messages = json_decode($chat->messages, true);
                $lastMessage = $messages ? end($messages) : null;

                $result[] = [
                    'session_id' => $chat->session_id,
                    'msg' => $lastMessage ? $lastMessage['message'] : null
                ];
            }

            return $result;
        } catch (\Exception $e) {
            \Log::error("Error listing chats: " . $e->getMessage());
            return null;
        }
    }
}
