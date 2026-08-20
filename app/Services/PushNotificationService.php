<?php

namespace App\Services;

use App\Models\DeviceToken;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send a push notification to every device registered for the user.
     */
    public function sendToUser(int $userId, string $title, string $message, array $payload = [], string $type = 'system'): void
    {
        $tokens = DeviceToken::query()->where('user_id', $userId)->get();

        foreach ($tokens as $token) {
            $this->sendToDevice($token, $title, $message, $payload, $type);
        }
    }

    /**
     * Send a push notification to a single device token via FCM HTTP v1.
     */
    public function sendToDevice(DeviceToken $token, string $title, string $message, array $payload = [], string $type = 'system'): void
    {
        $projectId = $this->projectId();

        if (! $projectId || ! $this->accessToken()) {
            return;
        }

        $data = [
            'type' => $type,
        ];

        if (isset($payload['related_id'])) {
            $data['related_id'] = (string) $payload['related_id'];
        }

        foreach ($payload as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $data[$key] = (string) $value;
            } else {
                $data[$key] = json_encode($value);
            }
        }

        try {
            $response = Http::withToken($this->accessToken())
                ->acceptJson()
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $token->device_token,
                        'notification' => [
                            'title' => $title,
                            'body' => $message,
                        ],
                        'data' => $data,
                    ],
                ]);

            if (in_array($response->status(), [404, 410], true)) {
                $token->delete();
            } elseif ($response->failed()) {
                Log::warning('FCM push failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('FCM push exception', ['error' => $e->getMessage()]);
        }
    }

    private function projectId(): ?string
    {
        $projectId = config('services.fcm.project_id');

        if (! $projectId && $this->serviceAccount()) {
            $projectId = $this->serviceAccount()['project_id'] ?? null;
        }

        return $projectId ?: null;
    }

    /**
     * Cached OAuth2 access token for the FCM service account (expires in 1h).
     */
    private function accessToken(): ?string
    {
        return Cache::remember('fcm_access_token', 3300, function () {
            return $this->fetchAccessToken();
        });
    }

    private function fetchAccessToken(): ?string
    {
        $account = $this->serviceAccount();

        if (! $account || empty($account['client_email']) || empty($account['private_key'])) {
            Log::warning('FCM service account missing: set FCM_PROJECT_ID and FCM_SERVICE_ACCOUNT_PATH.');
            return null;
        }

        $now = time();

        $jwt = JWT::encode([
            'iss' => $account['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $account['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], $account['private_key'], 'RS256');

        try {
            $response = Http::asForm()->post(
                $account['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]
            );

            if ($response->failed()) {
                Log::warning('FCM token exchange failed', ['body' => $response->body()]);
                return null;
            }

            return $response->json('access_token');
        } catch (\Throwable $e) {
            Log::warning('FCM token exchange exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function serviceAccount(): ?array
    {
        $path = config('services.fcm.service_account_path');

        if (! $path || ! is_file($path)) {
            return null;
        }

        $json = json_decode(file_get_contents($path), true);

        return is_array($json) ? $json : null;
    }
}