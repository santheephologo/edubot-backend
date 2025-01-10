<?php

namespace App\Http\Controllers;

use App\Repositories\ClientRepoInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    protected $clientRepo;

    // Dependency injection of the ClientRepo
    public function __construct(ClientRepoInterface $clientRepo)
    {
        $this->clientRepo = $clientRepo;
    }

    // Client registration endpoint
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:clients',
            'email' => 'required|email|unique:clients',
            'password' => 'required|min:6',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->clientRepo->clientRegister(
            $request->username, 
            $request->email, 
            $request->password, 
            $request->first_name, 
            $request->last_name
        );

        if ($result) {
            return response()->json(['message' => 'Client registered successfully'], 201);
        }

        return response()->json(['message' => 'Registration failed'], 500);
    }

    // Client login endpoint
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->clientRepo->clientLogin($request->email, $request->password);

        if ($result) {
            return response()->json(['message' => 'Login successful'], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Fetch all clients with their bots
    public function fetchClients()
    {
        $clients = $this->clientRepo->fetchClients();

        if ($clients) {
            return response()->json(['clients' => $clients], 200);
        }

        return response()->json(['message' => 'Error fetching clients'], 500);
    }

    // Get specific client details along with their bots
    public function returnClient($username)
    {
        $client = $this->clientRepo->returnClient($username);

        if ($client) {
            return response()->json(['client' => $client], 200);
        }

        return response()->json(['message' => 'Client not found'], 404);
    }

    // Get token information for a specific client
    public function returnTokenInfo($username)
    {
        $tokenInfo = $this->clientRepo->returnTokenInfo($username);

        if ($tokenInfo) {
            return response()->json(['token_info' => $tokenInfo], 200);
        }

        return response()->json(['message' => 'Client not found or error retrieving token info'], 404);
    }

    // Update token information for a bot
    public function updateBotToken(Request $request, $clientId, $botId)
    {
        $validator = Validator::make($request->all(), [
            'addition' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->clientRepo->updateClientBotToken($clientId, $botId, $request->addition);

        if ($result) {
            return response()->json(['message' => 'Bot token updated successfully'], 200);
        }

        return response()->json(['message' => 'Error updating bot token'], 500);
    }

    // Add a new bot to a client
    public function addBot(Request $request, $clientId)
    {
        $validator = Validator::make($request->all(), [
            'bot_name' => 'required|string',
            'bot_id' => 'required|integer',
            'tkns_remaining' => 'required|integer',
            'tkns_used' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->clientRepo->addBot(
            $clientId, 
            $request->bot_name, 
            $request->bot_id, 
            $request->tkns_remaining, 
            $request->tkns_used
        );

        if ($result) {
            return response()->json(['message' => 'Bot added successfully'], 201);
        }

        return response()->json(['message' => 'Error adding bot'], 500);
    }

    // Delete a client bot
    public function deleteBot($clientId, $botId)
    {
        $result = $this->clientRepo->deleteClientBot($clientId, $botId);

        if ($result) {
            return response()->json(['message' => 'Bot deleted successfully'], 200);
        }

        return response()->json(['message' => 'Error deleting bot'], 500);
    }

    // Delete a client
    public function deleteClient($clientId)
    {
        $result = $this->clientRepo->deleteClient($clientId);

        if ($result) {
            return response()->json(['message' => 'Client deleted successfully'], 200);
        }

        return response()->json(['message' => 'Error deleting client'], 500);
    }
}
