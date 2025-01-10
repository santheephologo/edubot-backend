<?php

namespace App\Repositories;

interface BotRepoInterface
{
    public function botRegister($name, $assistantId);
    
    public function updateBot($name, $botId, $assistantId);
    
    public function fetchBots();
    
    public function fetchDashboard();
    
    public function returnBot($botId);
    
    public function deleteBot($botId);
}
