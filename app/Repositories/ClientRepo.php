<?php

namespace App\Repositories;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class ClientRepo implements ClientRepoInterface
{
    // Register client
    public function clientRegister($username, $email, $password, $firstName, $lastName)
    {
        try {
            DB::table('clients')->insert([
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'is_active' => true,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error in clientRegister: ' . $e->getMessage());
            return null;
        }
    }

    // Client login
    public function clientLogin($email, $password)
    {
        try {
            $client = DB::table('users')->where('email', $email)->first();

            if (!$client) {
                return false;
            }

            if (Hash::check($password, $client->password)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error in clientLogin: ' . $e->getMessage());
            return false;
        }
    }

    // Fetch all clients and their bots
    public function fetchClients()
    {
        try {
            $clients = DB::table('users')->get();
            $clientBots = DB::table('user_bots')->get();

            $clientsWithBots = [];
            foreach ($clients as $client) {
                $clientJson = (array) $client;
                $clientJson['bots'] = [];
                $clientsWithBots[] = $clientJson;
            }

            foreach ($clientBots as $bot) {
                foreach ($clientsWithBots as &$client) {
                    if ($bot->user_id == $client['id']) {
                        $client['bots'][] = (array) $bot;
                    }
                }
            }

            return $clientsWithBots;
        } catch (\Exception $e) {
            Log::error('Error in fetchClients: ' . $e->getMessage());
            return false;
        }
    }

    // Return a specific client with its bots
    public function returnClient($username)
    {
        try {
            $client = DB::table('users')->where('username', $username)->first();
            if (!$client) {
                return null;
            }

            $clientBots = DB::table('client_bots')->where('client_id', $client->id)->get();
            $clientJson = (array) $client;
            $clientJson['bots'] = $clientBots->toArray();

            return $clientJson;
        } catch (\Exception $e) {
            Log::error('Error in returnClient: ' . $e->getMessage());
            return null;
        }
    }

    // Return token information for a client
    public function returnTokenInfo($clientId, $botId)
    {
        try {
            $tokenInfo = DB::table('user_bots')->where('user_id', (int)($clientId))->where('bot_id', (int)($botId))->first();
            if ($tokenInfo) {
                return [
                    'remaining_tokens' => $tokenInfo->tokens_remaining,
                    'used_tokens' => $tokenInfo->tokens_used
                ];
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Error in returnTokenInfo: ' . $e->getMessage());
            return null;
        }
    }

    // Update the token count for a specific client bot
    public function updateClientBotToken($clientId, $botId, $addition)
    {
        try {
            $clientBot = DB::table('user_bots')->where('user_id', $clientId)->where('bot_id', $botId)->first();
            if ($clientBot) {
                DB::table('user_bots')
                    ->where('user_id', $clientId)
                    ->where('bot_id', $botId)
                    ->update(['tokens_remaining' => $clientBot->tokens_remaining + $addition,
                                'updated_at' => Carbon::now(),]);

                return true;
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Error in updateClientBotToken: ' . $e->getMessage());
            return null;
        }
    }

    // Add a bot for a client
    public function addBot($clientId, $botId, $botName,  $tknsRemaining, $tknsUsed)
    {
        try {
            DB::table('user_bots')->insert([
                'id' => (string) Str::uuid(),
                'user_id' => $clientId,
                'bot_id' => $botId,
                'name' => $botName,
                'tokens_remaining' => $tknsRemaining,
                'tokens_used' => $tknsUsed,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error in addBot: ' . $e->getMessage());
            return null;
        }
    }

    // Delete a specific client bot
    public function deleteClientBot($clientId, $botId)
    {
        try {
            Log::info('Attempting to delete bot', [
                'user_id' => $clientId,
                'bot_id' => $botId,
            ]);

            $deleted = DB::table('user_bots')
                ->where('user_id', $clientId)
                ->where('bot_id', $botId)
                ->delete();

            Log::info('Delete result', ['deleted' => $deleted]);

            return $deleted > 0; // Returns true if rows were deleted
        } catch (\Exception $e) {
            Log::error('Error in deleteClientBot: ' . $e->getMessage());
            return false; // Return false on exception
        }
    }


    // update token usage
    public function updateTokenUsage($clientId, $botId, $tokensUsage)
    {
        try {
            $clientBot = DB::table('user_bots')->where('user_id', $clientId)->where('bot_id', $botId)->first();
            if ($clientBot) {
                $newTknsRemaining = $clientBot->tokens_remaining - $tokensUsage;
                $newTknsUsed = $clientBot->tokens_used + $tokensUsage;
                if ($newTknsRemaining < 0) {
                    $newTknsRemaining = 0;
                }
                // Update the client bot record
                DB::table('user_bots')
                    ->where('user_id', $clientId)
                    ->where('bot_id', $botId)
                    ->update([
                        'tokens_remaining' => $newTknsRemaining,
                        'tokens_used' => $newTknsUsed,
                        // 'updated_at' => now(), // Optionally update the timestamp
                    ]);
                return true;
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Error in token update: ' . $e->getMessage());
            return null;
        }
    }

    // Delete a client
    public function deleteClient($clientId)
    {
        try {
            $client = DB::table('users')->where('id', $clientId)->first();
            if ($client) {
                DB::table('users')->where('id', $clientId)->delete();
                return true;
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Error in deleteClient: ' . $e->getMessage());
            return null;
        }
    }
}
