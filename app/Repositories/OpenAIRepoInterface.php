<?php

namespace App\Repositories;

interface OpenAIRepoInterface
{
    public function checkClientExists($clientId): bool;

    public function updateTokenUsage($clientId, $botId, $tknsUsed): bool;

    public function createThread(): ?string;

    public function sendMessageThread();
    
}
