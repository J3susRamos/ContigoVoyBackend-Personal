<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubDeployService
{
    public static function dispatch(string $eventType): void
    {
        try {
            $response = Http::withToken(config('services.github.token'))
                ->post(
                    'https://api.github.com/repos/' . config('services.github.repo') . '/dispatches',
                    [
                        'event_type' => $eventType
                    ]
                );

            if ($response->failed()) {
                Log::error('GitHub deploy failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('GitHub deploy exception', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
