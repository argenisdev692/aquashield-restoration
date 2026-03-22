<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final readonly class RetellAIService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.retellai.com/v2';
        $this->apiKey = config('services.retell.api_key', env('RETELL_AI_API_KEY', ''));
    }

    /**
     * List calls from Retell AI API
     *
     * @param array<string, mixed> $filters
     * @return array<array<string, mixed>>
     */
    public function listCalls(array $filters = []): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/list-calls", $filters);

            if (!$response->successful()) {
                Log::error('Retell AI API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \RuntimeException('Failed to fetch calls from Retell AI: ' . $response->body());
            }

            $data = $response->json();

            return $data['calls'] ?? [];
        } catch (\Exception $e) {
            Log::error('Retell AI service error', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get single call details from Retell AI API
     *
     * @return array<string, mixed>
     */
    public function getCall(string $callId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/get-call/{$callId}");

            if ($response->status() === 404) {
                return null;
            }

            if (!$response->successful()) {
                Log::error('Retell AI API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \RuntimeException('Failed to fetch call from Retell AI: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Retell AI service error', [
                'callId' => $callId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get call audio URL
     */
    public function getCallAudioUrl(string $callId): ?string
    {
        $call = $this->getCall($callId);

        return $call['recording_url'] ?? null;
    }
}
