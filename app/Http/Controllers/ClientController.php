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
    public function returnTokenInfo($clientId, $botId)
    {
        $tokenInfo = $this->clientRepo->returnTokenInfo($clientId, $botId);

        if ($tokenInfo) {
            return response()->json($tokenInfo, 200);
        }

        return response()->json(['message' => 'Client not found or error retrieving token info'], 404);
    }

    // Update token information for a bot
    public function updateBotToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'addition' => 'required|integer',
            'bot_id' => 'required',
            'client_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->clientRepo->updateClientBotToken($request->client_id, $request->bot_id, $request->addition);

        if ($result) {
            return response()->json(['message' => 'Bot token updated successfully'], 200);
        }

        return response()->json(['message' => 'Error updating bot token'], 500);
    }

    // Add a new bot to a client
    public function addBot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'bot_id' => 'required',
            'bot_name' => 'required|string',
            'tkns_remaining' => 'required|integer',
            'tkns_used' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->clientRepo->addBot(
            $request->client_id, 
            $request->bot_id, 
            $request->bot_name, 
            $request->tkns_remaining, 
            $request->tkns_used
        );

        if ($result) {
            return response()->json(['message' => 'Bot added successfully'], 201);
        }

        return response()->json(['message' => 'Error adding bot'], 500);
    }

    // Delete a client bot
    public function deleteBot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'bot_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $result = $this->clientRepo->deleteClientBot($request->client_id, $request->bot_id);

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
    /**
     * Update token usage for a client bot.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTokenUsage(Request $request)
    {
        // Validate input data
        $validatedData = $request->validate([
            'client_id' => 'required',
            'bot_id' => 'required',
            'token_usage' => 'required',
        ]);

        $clientId = $validatedData['client_id'];
        $botId = $validatedData['bot_id'];
        $tokensUsage = $validatedData['token_usage'];

        try {
            // Call repository method to handle the operation
            $result = $this->clientRepo->updateTokenUsage($clientId, $botId, $tokensUsage);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Token usage updated successfully.',
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Client bot not found or could not be updated.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in token update: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating token usage.',
            ], 500);
        }
    }
}
