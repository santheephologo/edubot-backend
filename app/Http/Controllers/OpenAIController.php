<?php

namespace App\Http\Controllers;

use App\Repositories\OpenAIRepoInterface;

class OpenAIController extends Controller
{
    protected $openAIRepository;

    public function __construct(OpenAIRepoInterface $openAIRepository)
    {
        $this->openAIRepository = $openAIRepository;
    }

    public function createThread()
    {

        $threadId = $this->openAIRepository->createThread();

        if ($threadId) {
            return response()->json(['thread_id' => $threadId], 201);
        }

        return response()->json(['error' => 'Failed to create thread'], 500);
    }
}
