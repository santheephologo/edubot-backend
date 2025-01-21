<?php

namespace App\Repositories;

interface BotRepoInterface
{
    public function botRegister($name, $assistantId, $defaultTokens);
    
    public function updateBot($name, $botId, $assistantId, $defaultTokens);
    
    public function fetchBots();
    
    public function fetchDashboard();
    
    public function returnBot($botId);
    
    public function deleteBot($botId);

    public function fetchAddons();

    public function updateAddon($addonId, $data);

    public function storeAddon($data);
}
