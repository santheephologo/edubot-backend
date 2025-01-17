<?php

namespace App\Repositories;

interface ChatRepoInterface
{
    public function storeMessage($sessionId, $reqMessage, $resMessage);
    public function createNewChat($clientId, $botId, $threadId);
    public function getThreadId($sessionId);
    public function getMessage($sessionId);
    public function listChats($sessionIdPrefix);
}
