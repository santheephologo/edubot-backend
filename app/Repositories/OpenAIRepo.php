<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OpenAIRepo implements OpenAIRepoInterface
{
    public function checkClientExists($clientId): bool{
        return DB::connection('edubot')->table('clients')->where('id', $clientId)->exists();
    }
    public function updateTokenUsage($clientId, $botId, $tknsUsed): bool{
        // Start a transaction to ensure atomic operations
        DB::beginTransaction();

        try {
            // Lock the record for update
            $clientBot = DB::connection('edubot')->table('client_bots')
                ->where('client_id', $clientId)
                ->where('bot_id', $botId)
                ->lockForUpdate()
                ->first();

            if ($clientBot) {
                // Update the token usage and remaining tokens
                DB::connection('edubot')->table('client_bots')
                    ->where('client_id', $clientId)
                    ->where('bot_id', $botId)
                    ->update([
                        'tkns_used' => DB::raw('tkns_used + ' . (int)$tknsUsed),
                        'tkns_remaining' => DB::raw('tkns_remaining - ' . (int)$tknsUsed),
                    ]);

                // Commit the transaction
                DB::commit();
                return true;
            }
        } catch (\Exception $e) {
            // In case of error, rollback the transaction
            DB::rollBack();
        }

        return false;
    }
    public function createThread(): ?string{
        $apiToken = getenv("OPENAI_API_KEY");
        $url = 'https://api.openai.com/v1/threads';
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiToken,
            'OpenAI-Beta' => 'assistants=v2',
        ];

        $response = Http::withHeaders($headers)->post($url);

        if ($response->successful()) {
            return $response->json()['id'];
        }

        return null;
    }
    public function getTokenCount(array $messages): int{
        $model = "gpt-3.5-turbo-0125";
        // Token encoding approximation for cl100k_base
        // Replace this with a proper tokenizer library if available
        $encoding = function ($text) {
            // Example approximation: split by spaces and count the words
            // Refine this logic based on OpenAI's token rules for higher accuracy
            return count(explode(' ', $text));
        };

        $numTokens = 0;

        if ($model === "gpt-3.5-turbo-0125") {
            foreach ($messages as $message) {
                $numTokens += 4; // Base tokens per message
                foreach ($message as $key => $value) {
                    $numTokens += $encoding($value);
                    if ($key === "name") {
                        $numTokens -= 1; // Adjust for omitted role token
                    }
                }
            }
            $numTokens += 2; // Tokens for the primed reply
            return $numTokens;
        }

        throw new \Exception("getTokenCount is not implemented for model {$model}");
    }
    public function sendMessageThread($threadId, $message){
        $apiToken = getenv("OPENAI_API_KEY");
        $url = "https://api.openai.com/v1/threads/{$threadId}/messages";
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$apiToken}",
            'OpenAI-Beta' => 'assistants=v2',
        ];
        $data = [
            "role" => "user",
            "content" => $message,
        ];

        $numTokens = $this->getTokenCount($data);

        $response = Http::withHeaders($headers)->post($url, $data);
        return [
            'response' => $response->json(),
            'num_tokens' => $numTokens,
        ];
    }
    public function runThread( $threadId, $assistantId){
        // Define the URL
        $url = "https://api.openai.com/v1/threads/{$threadId}/runs";

        // Define the headers
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiToken,
            'OpenAI-Beta' => 'assistants=v2',
        ];

        // Define the data payload
        $data = [
            "assistant_id" => $assistantId,
        ];

        // Send the POST request
        $response = Http::withHeaders($headers)->post($url, $data);

        // Check if the response is successful
        if ($response->successful()) {
            return $response->json()['id'];
        }

        // Handle errors
        return response()->json([
            'error' => $response->json(),
            'status' => $response->status()
        ], $response->status());
    }
    public function checkRunStatus($apiToken, $threadId, $runId){
        // Define the URL
        $url = "https://api.openai.com/v1/threads/{$threadId}/runs/{$runId}";

        // Define the headers
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiToken,
            'OpenAI-Beta' => 'assistants=v2',
        ];

        // Send the GET request
        $response = Http::withHeaders($headers)->get($url);

        // Check if the response is successful
        if ($response->successful()) {
            return $response->json()['status'];
        }

        // Handle errors
        return response()->json([
            'error' => $response->json(),
            'status' => $response->status()
        ], $response->status());
    }
    public function retrieveMessage($apiToken, $threadId, $clientId){
        // Define the URL
        $url = "https://api.openai.com/v1/threads/{$threadId}/messages";

        // Define the headers
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiToken,
            'OpenAI-Beta' => 'assistants=v2',
        ];

        // Send the GET request
        $response = Http::withHeaders($headers)->get($url);

        // Check if the response is successful
        if ($response->successful()) {
            $replyData = $response->json()['data'][0]['content'][0]['text']['value'];

            // Example token count logic
            $messages = [["role" => "assistant", "content" => $replyData]];
            $replyTokens = $this->getTokenCount($messages);

            // Example updateTokenUsage method
            // $this->updateTokenUsage($clientId, $replyTokens);

            return [
                'reply_data' => $replyData,
                'reply_tokens' => $replyTokens,
            ];
        }

        // Handle errors
        return response()->json([
            'error' => $response->json(),
            'status' => $response->status()
        ], $response->status());
    }
    public function moderateContent($apiToken, $message){
        // Define the Moderation API URL
        $url = "https://api.openai.com/v1/moderations";

        // Set the headers
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiToken,
        ];

        // Prepare the data payload
        $data = ['input' => $message];

        // Send the POST request
        $response = Http::withHeaders($headers)->post($url, $data);

        // Check if the response is successful
        if ($response->successful()) {
            $result = $response->json();
            $flagged = $result['results'][0]['flagged'];
            $categories = $result['results'][0]['categories'];

            // Log or return the moderation result
            return [
                'flagged' => $flagged,
                'categories' => $categories,
            ];
        } else {
            // Handle errors
            throw new \Exception('Moderation API Error: ' . $response->body());
        }
    }
    public function checkRemainingTokens($clientId, $botId){
        // Query the `client_bots` table to fetch the remaining tokens
        $clientBot = DB::connection('edubot')->table('client_bots')
            ->where('client_id', $clientId)
            ->where('bot_id', $botId)
            ->first();

        // Check if a record is found and return the remaining tokens, or null if not found
        return $clientBot ? $clientBot->tkns_remaining : null;
    }
    public function processRequest(){

    }
    public function connectModel($message, $clientId, $botId, $sessionId, $threadId){
        try {
            // Check remaining tokens
            $remainingTokens = $this->checkRemainingTokens($clientId, $botId);

            // Calculate message token count
            $messageTokens = $this->getTokenCount([["role" => "user", "content" => $message]]);
        
            // Token check conditions
            if (($messageTokens * 2 >= $remainingTokens) || ($remainingTokens < 1000)) {
                return response()->json(["error" => "Remaining tokens are too low. Please recharge to get replies"], 400);
            }

            if ($remainingTokens <= 0) {
                return response()->json(["error" => "Token limit reached"], 400);
            }

            // Check content moderation
            $moderationResult = $this->moderateContent(env('OPENAI_API_KEY'), $message);

            if ($moderationResult['flagged']) {
                return response()->json([
                    "error" => "Message contains content that violates our policy.",
                    "categories" => $moderationResult['categories']
                ], 400);
            }

            // Process request (replace with actual logic)
            $result = $this->processRequest($message, $botId, $sessionId, $threadId);

            // Update token usage
            $this->updateTokenUsage($clientId, $botId, $result['total_tokens']);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

}
