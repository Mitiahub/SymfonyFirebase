<?php

namespace App\Controller;

use App\Service\FirebaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    private const FIREBASE_SIGNUP_URL = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=AIzaSyDEvPkzOB9lEoaukwaLq88S9i5SbjSYeao';
    private const FIREBASE_LOGIN_URL = 'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=AIzaSyDEvPkzOB9lEoaukwaLq88S9i5SbjSYeao';

    #[Route('/api/auth/register', name: 'auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'Email et mot de passe requis'], 400);
        }

        $postData = json_encode([
            'email' => $data['email'],
            'password' => $data['password'],
            'returnSecureToken' => true
        ]);

        $ch = curl_init(self::FIREBASE_SIGNUP_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseData = json_decode($response, true);

        if ($httpCode !== 200) {
            if (isset($responseData['error']['message']) && $responseData['error']['message'] === "EMAIL_EXISTS") {
                return $this->json(['error' => 'Cet email est déjà utilisé.'], 400);
            }
            return $this->json(['error' => 'Erreur Firebase : ' . json_encode($responseData)], 400);
        }

        return $this->json([
            'message' => 'Utilisateur créé avec succès',
            'uid' => $responseData['localId'],
            'email' => $responseData['email']
        ]);
    }

    #[Route('/api/auth/login', name: 'auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'Email et mot de passe requis'], 400);
        }

        try {
            $response = $this->firebaseSignInWithEmailAndPassword($data['email'], $data['password']);

            return $this->json([
                'message' => 'Connexion réussie',
                'idToken' => $response['idToken'],
                'refreshToken' => $response['refreshToken'],
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur de connexion : ' . $e->getMessage()], 401);
        }
    }

    private function firebaseSignInWithEmailAndPassword(string $email, string $password): array
    {
        $postData = json_encode([
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true,
        ]);

        $ch = curl_init(self::FIREBASE_LOGIN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Erreur de requête CURL : ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Erreur Firebase : ' . $response);
        }

        return json_decode($response, true);
    }

    #[Route('/api/auth/verify', name: 'auth_verify', methods: ['POST'])]
    public function verify(Request $request, FirebaseService $firebaseService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['idToken'])) {
            return $this->json(['error' => 'Token manquant'], 400);
        }

        try {
            $auth = $firebaseService->getAuth();
            $verifiedIdToken = $auth->verifyIdToken($data['idToken']);

            return $this->json([
                'message' => 'Token valide',
                'uid' => $verifiedIdToken->claims()->get('sub'),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Le token fourni est invalide : ' . $e->getMessage()], 400);
        }
    }

    #[Route('/api/notify', name: 'send_notification', methods: ['POST'])]
    public function sendNotification(Request $request, FirebaseService $firebaseService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['deviceToken']) || empty($data['title']) || empty($data['body'])) {
            return $this->json(['error' => 'Device token, titre et corps requis'], 400);
        }

        try {
            $firebaseService->sendNotification(
                $data['deviceToken'],
                $data['title'],
                $data['body']
            );

            return $this->json(['message' => 'Notification envoyée']);
        } catch (\Exception $e) {
            return $this->json([['error' => 'Erreur lors de l\'envoi de la notification : ' . $e->getMessage()], 500]);
        }
    }
}
