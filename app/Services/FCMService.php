<?php

namespace App\Services;

use Google_Client;
use Google_Service_FirebaseCloudMessaging;

class FCMService
{
    protected $client;
    protected $messaging;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setAuthConfig([
            "type" => "service_account",
            "project_id" => config('fcm.project_id'),
            "private_key_id" => config('fcm.private_key_id'),
            "private_key" => config('fcm.private_key'),
            "client_email" => config('fcm.client_email'),
            "client_id" => config('fcm.client_id'),
            "auth_uri" => config('fcm.auth_uri'),
            "token_uri" => config('fcm.token_uri'),
            "auth_provider_x509_cert_url" => config('fcm.auth_provider_x509_cert_url'),
            "client_x509_cert_url" => config('fcm.client_x509_cert_url'),
            "client_secret" => config('fcm.client_secret'),
        ]);
        $this->client->addScope(Google_Service_FirebaseCloudMessaging::CLOUD_PLATFORM);
        $this->messaging = new Google_Service_FirebaseCloudMessaging($this->client);
    }

    public function getAccessToken()
    {
        $savedTokenJson = $this->readSavedToken();

        if ($savedTokenJson) {
            $this->client->setAccessToken($savedTokenJson);
            if ($this->client->isAccessTokenExpired()) {
                $accessToken = $this->generateToken();
                $this->client->setAccessToken($accessToken);
            }
        } else {
            $accessToken = $this->generateToken();
            $this->client->setAccessToken($accessToken);
        }

        return $this->client->getAccessToken()['access_token'];
    }

    protected function readSavedToken()
    {
        $tokenPath = storage_path('app/token.cache');
        if (file_exists($tokenPath)) {
            return json_decode(file_get_contents($tokenPath), true);
        }
        return false;
    }

    protected function writeToken($token)
    {
        file_put_contents(storage_path('app/token.cache'), json_encode($token));
    }

    protected function generateToken()
    {
        $this->client->fetchAccessTokenWithAssertion();
        $accessToken = $this->client->getAccessToken();
        $this->writeToken($accessToken);

        return $accessToken;
    }

    public function sendNotification($token, $title, $body,$redirectUrl="")
    {
        $url = "https://fcm.googleapis.com/v1/projects/" . config('fcm.project_id') . "/messages:send";
        $data = [
            'message' => [
                "data" => [
                    "title" => $title,
                    "body" => $body,
                    "click_action"=>$redirectUrl,
                    "image"=>env('APP_URL').'image/logo.png',
                    "icon"=>env('APP_URL').'image/logo.png',
                ],
                'token' => $token,
            ],
        ];

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->getAccessToken(),
                "Content-Type: application/json",
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
