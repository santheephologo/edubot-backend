<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BotRepo implements BotRepoInterface
{
    public function __construct()
    {
        // Optionally, you can inject any dependencies here
    }

    // Bot registration
    public function botRegister($name, $assistantId, $defaultTokens)
    {
        try {
            $existingBot = DB::table('bots')->where('name', $name)->first();
            if ($existingBot) {
                return null; // Bot already exists
            }

            DB::table('bots')->insert([
                'name' => $name,
                'assistant_id' => $assistantId,
                'default_tokens' => $defaultTokens,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Error in bot registration: " . $e->getMessage());
            return null;
        }
    }

    // Update bot information
    public function updateBot($name, $botId, $assistantId, $defaultTokens)
    {
        try {
            $bot = DB::table('bots')->where('id', $botId)->first();
            if ($bot) {
                DB::table('bots')->where('id', $botId)->update([
                    'name' => $name,
                    'assistant_id' => $assistantId,
                    'default_tokens' => $defaultTokens,
                    'updated_at' => Carbon::now(),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Error in updating bot: " . $e->getMessage());
            return null;
        }
    }

    // Fetch all bots with user count
    public function fetchBots()
    {
        try {
        
            $bots = DB::table('bots')->get();
            $clientBots = DB::table('client_bots')->get();

            //dd($bots);
            $botUserCount = [];
            foreach ($clientBots as $clientBot) {
                if (isset($botUserCount[$clientBot->bot_id])) {
                    $botUserCount[$clientBot->bot_id]++;
                } else {
                    $botUserCount[$clientBot->bot_id] = 1;
                }
            }
            
            $botList = [];
            foreach ($bots as $bot) {
                $botJson = (array) $bot;
                $botJson['no_of_users'] = isset($botUserCount[$bot->id]) ? $botUserCount[$bot->id] : 0;
                $botList[] = $botJson;
            }
            
            return $botList;
        } catch (\Exception $e) {
            \Log::error("Error fetching bots: " . $e->getMessage());
            return null;
        }
    }

    // Fetch dashboard data (client and bot counts)
    public function fetchDashboard()
    {
        try {
            $clientCount = DB::table('clients')->count();
            $botCount = DB::table('bots')->count();

            return [
                'client_count' => $clientCount,
                'bot_count' => $botCount,
            ];
        } catch (\Exception $e) {
            \Log::error("Error fetching dashboard data: " . $e->getMessage());
            return null;
        }
    }

    // Fetch a single bot by ID
    public function returnBot($botId)
    {
        try {
            $bot = DB::table('bots')->where('id', $botId)->first();
            return $bot ? (array) $bot : null;
        } catch (\Exception $e) {
            \Log::error("Error fetching bot: " . $e->getMessage());
            return null;
        }
    }

    // Delete bot by ID
    public function deleteBot($botId)
    {
        try {
            $bot = DB::table('bots')->where('id', $botId)->first();
            if ($bot) {
                DB::table('bots')->where('id', $botId)->delete();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Error deleting bot: " . $e->getMessage());
            return null;
        }
    }

    public function fetchAddons()
    {
        try {
            $addons = DB::table('addons')->get();
            \Log::info('Addons fetched successfully', ['count' => $addons->count()]);
            return $addons;
        } catch (\Exception $e) {
            \Log::error("Error fetching addons : " . $e->getMessage());
            return null;
        }
    }

    // Update addon
    public function updateAddon($addonId, $data)
    {
        try {
            $addon = DB::table('addons')->where('id', $addonId)->first();
            if ($addon) {
                DB::table('addons')->where('id', $addonId)->update($data);
                \Log::info("Addon updated successfully", ['addonId' => $addonId]);
                return $data;
            }
            return null;
        } catch (\Exception $e) {
            \Log::error("Error updating addon: " . $e->getMessage());
            return null;
        }
    }

    // Store addon
    public function storeAddon($data)
    {
        try {
            $addonId = DB::table('addons')->insertGetId([
                'tokens' => $data['tokens'],
                'price' => $data['price'] ?? null,
                // Add other fields as necessary
            ]);
            \Log::info("Addon stored successfully", ['addonId' => $addonId]);
            return ['id' => $addonId, 'tokens' => $data['tokens'], 'price' => $data['price']];
        } catch (\Exception $e) {
            \Log::error("Error storing addon: " . $e->getMessage());
            return null;
        }
    }

    // Delete addon
    public function deleteAddon($addonId)
    {
        try {
            $addon = DB::table('addons')->where('id', $addonId)->first();
            if ($addon) {
                DB::table('addons')->where('id', $addonId)->delete();
                \Log::info("Addon deleted successfully", ['addonId' => $addonId]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error("Error deleting addon: " . $e->getMessage());
            return false;
        }
    }
}
