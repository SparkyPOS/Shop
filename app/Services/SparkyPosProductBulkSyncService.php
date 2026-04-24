<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SparkyPosProductBulkSyncService
{
    public function start(array $payload = []): array
    {
        return $this->send('post', '/api/sync/products/bulk-sync/start', $payload);
    }

    public function status(?string $syncId = null): array
    {
        $query = [];
        if (!empty($syncId)) {
            $query['sync_id'] = $syncId;
        }

        return $this->send('get', '/api/sync/products/bulk-sync/status', $query);
    }

    public function cancel(?string $syncId = null): array
    {
        $payload = [];
        if (!empty($syncId)) {
            $payload['sync_id'] = $syncId;
        }

        return $this->send('post', '/api/sync/products/bulk-sync/cancel', $payload);
    }

    private function send(string $method, string $path, array $payload): array
    {
        $baseUrl = rtrim((string) config('sync.base_url', ''), '/');
        if ($baseUrl === '') {
            return [
                'success' => false,
                'message' => 'SYNC_BASE_URL is not configured.',
                'data' => [],
                'http_status' => 422,
            ];
        }

        $token = (string) config('sync.token', env('SYNC_TOKEN', ''));

        try {
            $request = Http::timeout(20)->acceptJson();
            if ($token !== '') {
                $request = $request
                    ->withToken($token)
                    ->withHeaders(['X-Sync-Token' => $token]);
            }

            $url = $baseUrl . $path;
            $response = $method === 'get'
                ? $request->get($url, $payload)
                : $request->post($url, $payload);

            $json = $response->json();
            if (!is_array($json)) {
                $json = [];
            }

            return [
                'success' => (bool) ($json['success'] ?? $response->successful()),
                'message' => $json['message'] ?? ($response->successful() ? null : 'Unable to sync products right now.'),
                'data' => is_array($json['data'] ?? null) ? $json['data'] : [],
                'http_status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::warning('shopsync.bulk.request_failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Unable to connect to SparkyPOS sync API.',
                'data' => [],
                'http_status' => 502,
            ];
        }
    }
}
