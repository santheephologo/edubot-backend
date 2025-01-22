<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Bot;
use App\Models\User;
use App\Models\Addon;
use App\Models\UserBot;
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
            // Check if the bot already exists
            $existingBot = Bot::where('name', $name)->first();
            if ($existingBot) {
                return null; // Bot already exists
            }

            // Create the new bot using the Bot model
            $bot = Bot::create([
                'name' => $name,
                'assistant_id' => $assistantId,
                'default_tokens' => $defaultTokens,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return true; // Bot registered successfully
        } catch (\Exception $e) {
            \Log::error("Error in bot registration: " . $e->getMessage());
            return null;
        }
    }

    // Update bot information
    public function updateBot($name, $botId, $assistantId, $defaultTokens)
    {
        try {
            // Find the bot by its ID using the Bot model
            $bot = Bot::find($botId);

            if ($bot) {
                // Update the bot attributes and save it
                $bot->update([
                    'name' => $name,
                    'assistant_id' => $assistantId,
                    'default_tokens' => $defaultTokens,
                    'updated_at' => Carbon::now(),
                ]);

                return true;
            }

            return false; // Bot not found
        } catch (\Exception $e) {
            \Log::error("Error in updating bot: " . $e->getMessage());
            return null; // Handle any error
        }
    }


    // Fetch all bots with user count
    public function fetchBots()
    {
        try {
            // Fetch all bots with their associated user bot data
            $bots = Bot::all(); // Retrieve all bots
            $userBots = UserBot::all(); // Retrieve all user-bot associations

            // Count the number of users per bot
            $botUserCount = $userBots->groupBy('bot_id')->map->count();

            // Prepare the bot list with the user count
            $botList = $bots->map(function ($bot) use ($botUserCount) {
                return [
                    'id' => $bot->id,
                    'name' => $bot->name,
                    'assistant_id' => $bot->assistant_id,
                    'default_tokens' => $bot->default_tokens,
                    'is_active' => $bot->is_active,
                    'created_at' => $bot->created_at,
                    'updated_at' => $bot->updated_at,
                    'no_of_users' => $botUserCount->get($bot->id, 0), // Default to 0 if no users
                ];
            });

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
            // Fetch counts using Eloquent models
            $clientCount = User::count();
            $botCount = Bot::count();

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
            // Fetch the bot using Eloquent
            $bot = Bot::find($botId);

            // Return the bot as an array if found, otherwise null
            return $bot ? $bot->toArray() : null;
        } catch (\Exception $e) {
            \Log::error("Error fetching bot: " . $e->getMessage());
            return null;
        }
    }

    // Delete bot by ID
    public function deleteBot($botId)
    {
        try {
            // Attempt to delete the bot by its ID using Eloquent's destroy method
            $deleted = Bot::destroy($botId);

            // Return true if the bot was deleted, otherwise false
            return $deleted > 0;
        } catch (\Exception $e) {
            \Log::error("Error deleting bot: " . $e->getMessage());
            return null;
        }
    }

    public function fetchAddons()
    {
        try {
            // Retrieve all addons using Eloquent
            $addons = Addon::all();

            // Log the count of addons fetched
            \Log::info('Addons fetched successfully', ['count' => $addons->count()]);

            // Return the addons collection
            return $addons;
        } catch (\Exception $e) {
            // Log any errors that occur during the fetch
            \Log::error("Error fetching addons: " . $e->getMessage());
            return null;
        }
    }   

    // Update addon
    public function updateAddon($addonId, $data)
    {
        try {
            // Retrieve the addon using Eloquent
            $addon = Addon::find($addonId);

            // Check if the addon exists
            if ($addon) {
                // Update the addon with the provided data
                $addon->update($data);

                // Log success
                \Log::info("Addon updated successfully", ['addonId' => $addonId]);

                // Return the updated data
                return $addon;
            }

            // Return null if the addon was not found
            return null;
        } catch (\Exception $e) {
            // Log any errors
            \Log::error("Error updating addon: " . $e->getMessage());
            return null;
        }
    }

    // Store addon
    public function storeAddon($data)
    {
        try {
            // Create a new Addon using Eloquent
            $addon = Addon::create([
                'tokens' => $data['tokens'],
                'price' => $data['price'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                // Add other fields as necessary
            ]);

            // Log the successful creation
            \Log::info("Addon stored successfully", ['addonId' => $addon->id]);

            // Return the stored addon details
            return [
                'id' => $addon->id,
                'tokens' => $addon->tokens,
                'price' => $addon->price,
            ];
        } catch (\Exception $e) {
            // Log any errors
            \Log::error("Error storing addon: " . $e->getMessage());
            return null;
        }
    }

    // Delete addon
    public function deleteAddon($addonId)
    {
        try {
            // Find the addon by its ID
            $addon = Addon::find($addonId);
        
            if ($addon) {
                // Delete the addon
                $addon->delete();
            
                // Log the successful deletion
                \Log::info("Addon deleted successfully", ['addonId' => $addonId]);
            
                return true;
            }
        
            return false;
        } catch (\Exception $e) {
            // Log any errors
            \Log::error("Error deleting addon: " . $e->getMessage());
            return false;
        }
    }
}
