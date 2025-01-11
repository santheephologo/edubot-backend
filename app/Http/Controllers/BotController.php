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
        ]);

        $result = $this->botRepo->botRegister($data['name'], $data['assistant_id']);
        
        if ($result) {
            return response()->json(['message' => 'Bot registered successfully.'], 201);
        } else {
            return response()->json(['message' => 'Bot with this name already exists.'], 400);
        }
    }

    // Update a bot
    public function update(Request $request, $botId)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'assistant_id' => 'required|string',
        ]);

        $result = $this->botRepo->updateBot($data['name'], $botId, $data['assistant_id']);
        
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
}
