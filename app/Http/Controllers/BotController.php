<?php

namespace App\Http\Controllers;

use App\Repositories\BotRepoInterface;
use Illuminate\Http\Request;

class BotController extends Controller
{
    protected $botRepo;

    public function __construct(BotRepoInterface $botRepo)
    {
        $this->botRepo = $botRepo;
    }

    // Register a bot
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'assistant_id' => 'required|string',
            'default_tokens' => 'required|integer',
        ]);

        $result = $this->botRepo->botRegister($data['name'], $data['assistant_id'], $data['default_tokens']);
        
        if ($result) {
            return response()->json(['message' => 'Bot registered successfully.'], 201);
        } else {
            return response()->json(['message' => 'Bot with this name already exists.'], 400);
        }
    }

    // Update a bot
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'bot_id' => 'required',
            'assistant_id' => 'required|string',
            'default_tokens' => 'required|integer',
        ]);

        $result = $this->botRepo->updateBot($data['name'], $data['bot_id'], $data['assistant_id'], $data['default_tokens']);
        
        if ($result) {
            return response()->json(['message' => 'Bot updated successfully.'], 200);
        } else {
            return response()->json(['message' => 'Bot not found.'], 404);
        }
    }

    // Fetch all bots
    public function fetchAll()
    {
        $bots = $this->botRepo->fetchBots();
        
        if ($bots !== null) {
            return response()->json($bots, 200);
        } else {
            return response()->json(['message' => 'Error fetching bots.'], 500);
        }
    }

    // Fetch a single bot
    public function fetch($botId)
    {

        $bot = $this->botRepo->returnBot($botId);
        return response()->json($bot, 200);
        // if ($bot) {
        //     return response()->json($bot, 200);
        // } else {
        //     return response()->json(['message' => 'Bot not found.'], 404);
        // }
    }

    public function fetchDashboard()
    {

        $bot = $this->botRepo->fetchDashboard();
        return response()->json($bot, 200);
        // if ($bot) {
        //     return response()->json($bot, 200);
        // } else {
        //     return response()->json(['message' => 'Bot not found.'], 404);
        // }
    }


    // Fetch dashboard data
    public function dashboard()
    {
        $dashboard = $this->botRepo->fetchDashboard();
        
        if ($dashboard) {
            return response()->json($dashboard, 200);
        } else {
            return response()->json(['message' => 'Error fetching dashboard data.'], 500);
        }
    }

    // Delete a bot
    public function delete($botId)
    {
        $result = $this->botRepo->deleteBot($botId);
        
        if ($result) {
            return response()->json(['message' => 'Bot deleted successfully.'], 200);
        } else {
            return response()->json(['message' => 'Bot not found.'], 404);
        }
    }

    public function fetchAddons()
    {
        $addons = $this->botRepo->fetchAddons();
        
        if ($addons !== null) {
            return response()->json($addons, 200);
        } else {
            return response()->json(['message' => 'Error fetching addons.'], 500);
        }
    }

    // Update addon
    public function updateAddon(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required',
                'tokens' => 'required|integer',
                'price' => 'nullable|numeric|between:0,99999999.99',
                // Add other validation rules as necessary
            ]);

            $addon = $this->botRepo->updateAddon($validated['id'], $validated);
            if ($addon) {
                return response()->json($addon, 200);
            } else {
                return response()->json(['message' => 'Addon not found.'], 404);
            }
        } catch (\Exception $e) {
            \Log::error("Error updating addon: " . $e->getMessage());
            return response()->json(['message' => 'Error updating addon.'], 500);
        }
    }

    // Store addon
    public function storeAddon(Request $request)
    {
        try {
            $validated = $request->validate([
                'tokens' => 'required|integer',
                'price' => 'nullable|numeric|between:0,99999999.99',
                // Add other validation rules as necessary
            ]);

            $addon = $this->botRepo->storeAddon($validated);
            return response()->json($addon, 201);  // Created
        } catch (\Exception $e) {
            \Log::error("Error storing addon: " . $e->getMessage());
            return response()->json(['message' => 'Error storing addon.'], 500);
        }
    }

    // Delete addon
    public function deleteAddon($addonId)
    {
        try {
            $deleted = $this->botRepo->deleteAddon($addonId);
            if ($deleted) {
                return response()->json(['message' => 'Addon deleted successfully.'], 200);
            } else {
                return response()->json(['message' => 'Addon not found.'], 404);
            }
        } catch (\Exception $e) {
            \Log::error("Error deleting addon: " . $e->getMessage());
            return response()->json(['message' => 'Error deleting addon.'], 500);
        }
    }
}
