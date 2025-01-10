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
    public function botRegister($name, $assistantId)
    {
        try {
            $existingBot = DB::table('bots')->where('name', $name)->first();
            if ($existingBot) {
                return null; // Bot already exists
            }

            DB::table('bots')->insert([
                'name' => $name,
                'assistant_id' => $assistantId,
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
    public function updateBot($name, $botId, $assistantId)
    {
        try {
            $bot = DB::table('bots')->where('id', $botId)->first();
            if ($bot) {
                DB::table('bots')->where('id', $botId)->update([
                    'name' => $name,
                    'assistant_id' => $assistantId,
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
}
