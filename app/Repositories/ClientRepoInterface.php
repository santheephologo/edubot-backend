<?php

namespace App\Repositories;

interface ClientRepoInterface
{
    public function clientRegister($username, $email, $password, $firstName, $lastName);
    public function clientLogin($email, $password);
    public function fetchClients();
    public function returnClient($username);
    public function returnTokenInfo($clientId, $botId);
    public function updateClientBotToken($clientId, $botId, $addition);
    public function addBot($clientId, $botName, $botId, $tknsRemaining, $tknsUsed);
    public function deleteClientBot($clientId, $botId);
    public function deleteClient($clientId);
    public function updateTokenUsage($clientId, $botId, $tokensUsage);
}
