<?php
namespace App\Service;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Messaging;

class FirebaseService
{
    private Auth $auth;
    private Messaging $messaging;

    public function __construct()
    {
        $firebase = (new Factory())
            ->withServiceAccount(__DIR__ . '/../../config/firebase/firebase_credentials.json');

        $this->auth = $firebase->createAuth();
        $this->messaging = $firebase->createMessaging();
    }

    public function getAuth(): Auth
    {
        return $this->auth;
    }

    public function getMessaging(): Messaging
    {
        return $this->messaging;
    }
        public function sendNotification(string $deviceToken, string $title, string $body): void
    {
        $message = Messaging\CloudMessage::withTarget('token', $deviceToken)
            ->withNotification([
                'title' => $title,
                'body' => $body,
            ]);

        $this->messaging->send($message);
    }

}
