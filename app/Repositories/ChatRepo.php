<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatRepo implements ChatRepoInterface
{
   

    public function __construct()
    {
        //$this->openaiRepo = $openaiRepo;
    }

    public function storeMessage($sessionId, $reqMessage, $resMessage)
    {
        try {
            // Fetch the chat history using raw DB query
            $chatHistory = DB::table('chat_histories')->where('session_id', $sessionId)->first();
            \Log::info("Current messages for session {$sessionId}: " . $chatHistory->messages);
            if ($chatHistory) {
                // Log the current state of messages
                \Log::info("(2) Current messages for session {$sessionId}: " . $chatHistory->messages);

                // Decode existing messages
                $messages = json_decode($chatHistory->messages, true);

                // If messages is not an array, initialize it
                // if (!is_array($messages)) {
                //     $messages = [];
                //     \Log::info("Messages initialized as empty array for session {$sessionId}");
                // }

                // Append new messages
                $messages[] = ['sender' => 'User', 'message' => $reqMessage];
                $messages[] = ['sender' => 'Bot', 'message' => $resMessage];

                // Log the new messages array
                \Log::info("Updated messages for session {$sessionId}: " . json_encode($messages));

                // Update the database
                DB::table('chat_histories')
                    ->where('session_id', $sessionId)
                    ->update(['messages' => json_encode($messages)]);

                return $messages; // Return updated messages
            } else {
                \Log::warning("No chat history found for session {$sessionId}");
                return null;
            }
        } catch (\Exception $e) {
            \Log::error("Error storing message for session {$sessionId}: " . $e->getMessage());
            return null;
        }
    }


    public function createNewChat($clientId, $botId, $threadId)
    {
        try {
            $timestamp = Carbon::now()->format('Y-m-d H:i:s');
            $sessionId = "{$clientId}_{$botId}_{$timestamp}";
            //$threadId = $this->openaiRepo->createThread();

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
                ->orderBy('created_at', 'desc') // Order by `created_at` in descending order
                ->get();
            $result = [];
            foreach ($chats as $chat) {
                // Decode messages from JSON
                $messages = json_decode($chat->messages, true);
                $lastMessage = $messages ? end($messages) : null;

                $result[] = [
                    'session_id' => $chat->session_id,
                    'thread_id'=> $chat->thread_id,
                    'msg' => $lastMessage ? $lastMessage['message'] : null,
                    'created_at' => $chat->created_at,
                ];
            }

            return $result;
        } catch (\Exception $e) {
            \Log::error("Error listing chats: " . $e->getMessage());
            return null;
        }
    }
}
