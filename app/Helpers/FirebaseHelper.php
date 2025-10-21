<?php

namespace App\Helpers;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FirebaseHelper
{
    public static function sendNotification($fcmToken, $title, $body, $data = [])
    {
        $projectId = config('services.fcm.project_id');
        $keyPath = public_path('e-book-972c1-firebase-adminsdk-fbsvc-8f167832d9.json');

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $keyPath
        );

        $accessToken = $credentials->fetchAuthToken()['access_token'];

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ],
        ];

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        Log::info("FCM response: " . $response->body());

        return $response->json();
    }
}
