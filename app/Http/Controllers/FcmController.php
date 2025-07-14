<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FcmController extends Controller
{
    public function sendFcmNotification(Request $request)
    {
        $fcm = $request->target_token ?? "cxqfUI32RCaxYhL-D3ks_Z:APA91bGDG-HHxeKboIn-tGxVSkeyP_9IRJAWoWC1rjD06Zfbqvm959G6mNTgkebDGFvFgtY2OVJnZLdjzqag1pLD4cV5p8lViVzx9cBWnrd6Jo6IZIDOdSk";
        $title = $request->title ?? "Titele FCM Demo Test";
        $description = $request->body ?? "Description FCM Demo Test";
        $projectId = "justusgrouppushnotification";

        $credentialsFilePath = Storage::path('app/json/file.json');

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        if (isset($token['error'])) {
            return response()->json([
                'message' => 'Error fetching access token',
                'error' => $token['error']
            ], 500);
        }

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $fcm,
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                ],
            ]
        ];

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            $results = [
                'message' => 'Curl Error: ' . $err
            ];
        } else {
            $results = [
                'message' => 'Notification has been sent',
                'response' => json_decode($response, true)
            ];
        }
        return $this->returnJsonHeader($results);
    }

    public function kirimNotifikasi(array $data)
    {
        $fcm = $request->target_token ?? "cxqfUI32RCaxYhL-D3ks_Z:APA91bGDG-HHxeKboIn-tGxVSkeyP_9IRJAWoWC1rjD06Zfbqvm959G6mNTgkebDGFvFgtY2OVJnZLdjzqag1pLD4cV5p8lViVzx9cBWnrd6Jo6IZIDOdSk";
        $title = $data['title'] ?? "Titele FCM Demo Test";
        $description = $data['body'] ?? "Description FCM Demo Test";
        $projectId = "justusgrouppushnotification";

        $credentialsFilePath = Storage::path('app/json/file.json');

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        if (isset($token['error'])) {
            return response()->json([
                'message' => 'Error fetching access token',
                'error' => $token['error']
            ], 500);
        }

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $fcm,
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                ],
            ]
        ];

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            $results = [
                'message' => 'Curl Error: ' . $err
            ];
        } else {
            $results = [
                'message' => 'Notification has been sent',
                'response' => json_decode($response, true)
            ];
        }
        return $this->returnJsonHeader($results);

        // Kirim notifikasi via FCM atau simpan ke database, dll
        // ...
    }

    public function sendTopicNotification(Request $request)
    {
        $title = $request->title ?? "Titele FCM Demo Test";
        $body = $request->body ?? "Description FCM Demo Test";
        $projectId = "justusgrouppushnotification";

        $credentialsFilePath = Storage::path('app/json/file.json');

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        if (isset($token['error'])) {
            return response()->json([
                'message' => 'Error fetching access token',
                'error' => $token['error']
            ], 500);
        }

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "topic" => "member_topic",
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
                "data" => [
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    "id" => "1",
                    "status" => "done",
                    "photo" => "photo_birthday_promo.png"
                ]
            ]
        ];


        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            $results = [
                'message' => 'Curl Error: ' . $err
            ];
        } else {
            $results = [
                'message' => 'Notification has been sent',
                'response' => json_decode($response, true)
            ];
        }
        return $this->returnJsonHeader($results);
    }

    public function test(Request $request)
    {
        $title = $request->title ?? "Titele FCM Demo Test";
        $body = $request->body ?? "Description FCM Demo Test";

        $token = $request->chunks ?? 'cXsirmOCRVOvKrsBS1RQ4A:APA91bH9G4FlfFTzVhoqWGMDvYKUOeCN406cOA75-bakvD11mAHZK4UQIY-JxObCBzJT_rJcBExhQG3AmwQN_RseRgnmY9P0CqKlFHREIlSL18tBkQWt85U'; // ganti dengan device token
        $accessToken = 'ya29.c.c0ASRK0GZsZOBX5KpohfFPHANKubV4mixtCAmGLrt-jteseSEPLnfJFIMybDLyXb6dbLgkc7PQsaYPjuJTUAwGVLJSxpOfZlNy4V1Hk3ixrSxslY1ZncRmkECCOH8x9G6L56843Ll4DqxrEMsFRmwuAP2YS5rXCC4bri1ILWT0f2EhrK-HozjT_TEUoDM3BHGlH_DHZ59k1bQDL4F-n7vHgJNMtFya8zJGsUbnvQAxu8LPflR8oMsPkBn4wFl9LqcCcfCeQqPSDBvyOWp4vq1XBNbjIld08NdSGIwf_oMPES4mAwc4j_inoEkcwMCf15Y4k20aGzh8Gq26iaBjQkEmC_bxtyIHWej1wVn8lQ-OM-70Li9b_wHmw4fiT385DkoBk15_8Z9xzF-lZmjqS8xoRlUw9FpnX5ZSyuRtBJ2gqlb8YfioR7p__6dcu5IMRJ36pjc11a30_QuF6JieJy_dqsj3u1jzz8nS2nzqR6wi4-fgqbajfrd2cQZts7M0Zdnr7YsRd0629J8e05mgUl9u619Si1I7ozh3YSd47YlpSZocWqMyVUM7h8iO23O_3w7rpV5vjYjjVxiBmorX8vdMeZe4eekYaa_dFJ9rrv7myJmYM2MasW_4wJm_iowFaBcgiJezkpems91ly2djwon-cm3nqnQV25yzfYnpqlU1rgJoneR_xf0l9hvr9oRgXIze3iUdI76kpfhY6S6e-aYkymIViMe81tOggbyvdeiY19Voh_gvwoI0ORfniYMXmVU6ZMO7sxYBYt5gszBVfxtUeWizjt6zVvyvY96iUOYFkgM2pw14nVoQiMJqZi7qfI7hYqQ48ykxIe1IzcppJyXjcmfRnsFqrFWsMW78f3pmu-1v-ncdyO9qsbyog8d-ZzUm3_4zpdFfX3U7Ozx4gq0Z0FcjBjrW1sbw3gYoviUhOgh73kR71h6p36kqtic5l3builWg30qSivwys_zrpgzqpFj4YzB6u4eRk0gygrixXzjOFapwQRFM8mm'; // ganti dengan valid OAuth 2.0 bearer token

        $response = Http::withToken($accessToken)
            ->post('https://fcm.googleapis.com/v1/projects/mysoftpaperwork/messages:send', [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ]
            ]);

        return response()->json([
            'status' => $response->status(),
            'response' => $response->json(),
        ]);
    }
}
