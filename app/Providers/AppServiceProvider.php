<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\OpenAIRepoInterface;
use App\Repositories\OpenAIRepo;
use App\Repositories\BotRepoInterface;
use App\Repositories\BotRepo;
use App\Repositories\ChatInterface;
use App\Repositories\ChatRepo;
use App\Repositories\ClientInterface;
use App\Repositories\ClientRepo;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OpenAIRepoInterface::class, OpenAIRepo::class);
        $this->app->bind(ChatRepoInterface::class, ChatRepo::class);
        $this->app->bind(BotRepoInterface::class, BotRepo::class);
        $this->app->bind(ClientRepoInterface::class, ClientRepo::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
