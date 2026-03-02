<?php
// ─────────────────────────────────────────────────────────
//  ai_proxy.php — Proxy vers Google Gemini (GRATUIT)
//  Utilise cURL (plus fiable que file_get_contents)
// ─────────────────────────────────────────────────────────
require_once 'includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Vérifier que cURL est disponible ──
if (!function_exists('curl_init')) {
    echo json_encode(['error' => 'cURL non disponible sur ce serveur. Activez l\'extension cURL dans php.ini (extension=curl).']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']); exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || !isset($body['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Requête invalide']); exit;
}

$API_KEY = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

if (empty($API_KEY) || $API_KEY === 'VOTRE_CLE_GEMINI_ICI') {
    echo json_encode(['error' => '⚙️ Clé API manquante — Ouvrez includes/config.php et remplacez VOTRE_CLE_GEMINI_ICI par votre vraie clé Gemini (gratuite sur aistudio.google.com/app/apikey)']);
    exit;
}

// ── Construire le contenu pour Gemini ──
$systemPrompt = $body['system'] ?? '';
$messages     = $body['messages'] ?? [];

$contents = [];
$firstUser = true;
foreach ($messages as $msg) {
    $role = $msg['role'] === 'user' ? 'user' : 'model';
    // On injecte le system prompt dans le 1er message utilisateur
    if ($role === 'user' && $firstUser) {
        $text    = $systemPrompt . "\n\n---\n\n" . $msg['content'];
        $firstUser = false;
    } else {
        $text = $msg['content'];
    }
    $contents[] = [
        'role'  => $role,
        'parts' => [['text' => $text]]
    ];
}

// S'il n'y a aucun message, créer un message vide
if (empty($contents)) {
    echo json_encode(['error' => 'Aucun message à envoyer']); exit;
}

$payload = json_encode([
    'contents'         => $contents,
    'generationConfig' => [
        'maxOutputTokens' => 800,
        'temperature'     => 0.75,
    ],
    'safetySettings'   => [
        ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_NONE'],
        ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_NONE'],
        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
    ]
], JSON_UNESCAPED_UNICODE);

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=' . urlencode($API_KEY);

// ── Appel cURL ──
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false, // Nécessaire sur certains XAMPP/WAMP
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// ── Erreur cURL (réseau) ──
if ($response === false || !empty($curlError)) {
    echo json_encode(['error' => 'Erreur réseau : ' . ($curlError ?: 'Impossible de contacter Google. Vérifiez votre connexion Internet.')]);
    exit;
}

$data = json_decode($response, true);

// ── Erreur retournée par Google ──
if (isset($data['error'])) {
    $msg = $data['error']['message'] ?? 'Erreur inconnue';
    $code = $data['error']['code'] ?? $httpCode;
    // Messages d'erreur plus clairs
    if (str_contains($msg, 'API_KEY_INVALID') || $code === 400) {
        $msg = 'Clé API invalide. Vérifiez votre clé Gemini dans includes/config.php';
    } elseif ($code === 429) {
        $msg = 'Limite de requêtes atteinte. Réessayez dans quelques secondes.';
    } elseif ($code === 403) {
        $msg = 'Accès refusé. Vérifiez que l\'API Gemini est activée sur votre compte Google.';
    }
    echo json_encode(['error' => $msg]); exit;
}

// ── Extraire la réponse ──
$text = $data['candidates'][0]['content']['parts'][0]['text']
     ?? $data['candidates'][0]['output']
     ?? null;

if (!$text) {
    // Vérifier si bloqué par les filtres de sécurité
    $reason = $data['candidates'][0]['finishReason'] ?? '';
    if ($reason === 'SAFETY') {
        $text = 'Je ne peux pas répondre à cette question pour des raisons de sécurité.';
    } else {
        echo json_encode(['error' => 'Réponse vide de l\'API. Réessayez. (debug: ' . json_encode($data) . ')']);
        exit;
    }
}

// ── Retourner la réponse (format compatible avec orientation.php) ──
echo json_encode([
    'content' => [['type' => 'text', 'text' => $text]]
]);
