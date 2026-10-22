<?php

namespace App\Services\Analisis;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmClient
{
    public function aktif(): bool
    {
        $c = config('fahrimart.analisis');

        return $c['insight_driver'] === 'llm' && filled($c['ai_api_key']) && filled($c['ai_model']);
    }

    public function model(): ?string
    {
        return config('fahrimart.analisis.ai_model');
    }

    public function minta(string $sistem, string $pengguna): ?string
    {
        $c = config('fahrimart.analisis');

        try {
            $respons = Http::withHeaders([
                'x-api-key' => $c['ai_api_key'],
                'anthropic-version' => '2023-06-01',
            ])
                ->timeout($c['ai_timeout'])
                ->acceptJson()
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $c['ai_model'],
                    'max_tokens' => 1500,
                    'system' => $sistem,
                    'messages' => [['role' => 'user', 'content' => $pengguna]],
                ]);

            if (! $respons->successful()) {
                Log::warning('LLM insight gagal', ['status' => $respons->status()]);

                return null;
            }

            $teks = collect($respons->json('content', []))
                ->where('type', 'text')
                ->pluck('text')
                ->implode('');

            return $teks !== '' ? $teks : null;
        } catch (\Throwable $e) {
            Log::warning('LLM insight error', ['pesan' => $e->getMessage()]);

            return null;
        }
    }
}