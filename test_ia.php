<?php
// ─────────────────────────────────────────────
//  test_ia.php — Page de diagnostic du chatbot
//  Ouvrez cette page dans votre navigateur
//  Supprimez ce fichier après les tests !
// ─────────────────────────────────────────────
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Diagnostic IA — EduGuide SN</title>
<style>
body{font-family:monospace;padding:2rem;background:#f5f5f5;max-width:700px;margin:0 auto}
h2{font-family:sans-serif}
.ok{background:#dcfce7;border:1px solid #86efac;padding:10px 14px;border-radius:8px;color:#166534;margin:8px 0}
.fail{background:#fee2e2;border:1px solid #fca5a5;padding:10px 14px;border-radius:8px;color:#991b1b;margin:8px 0}
.warn{background:#fef9c3;border:1px solid #fde047;padding:10px 14px;border-radius:8px;color:#713f12;margin:8px 0}
pre{background:#1e293b;color:#e2e8f0;padding:12px;border-radius:8px;overflow:auto;font-size:.82rem}
</style>
</head>
<body>
<h2>🔍 Diagnostic Chatbot IA — EduGuide SN</h2>

<?php
// 1. Vérifier la clé
$key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
if (empty($key) || $key === 'VOTRE_CLE_GEMINI_ICI'):?>
<div class="fail">❌ <strong>Clé API manquante</strong> — Ouvrez <code>includes/config.php</code> et remplacez <code>VOTRE_CLE_GEMINI_ICI</code> par votre vraie clé.</div>
<?php else:?>
<div class="ok">✅ Clé API configurée : <code><?= substr($key,0,12) ?>...</code></div>
<?php endif;?>

<?php
// 2. Vérifier cURL
if(function_exists('curl_init')):?>
<div class="ok">✅ cURL disponible (version <?= curl_version()['version'] ?>)</div>
<?php else:?>
<div class="fail">❌ <strong>cURL non disponible</strong> — Dans XAMPP, ouvrez <code>php.ini</code> et décommentez <code>extension=curl</code> puis redémarrez Apache.</div>
<?php endif;?>

<?php
// 3. Vérifier allow_url_fopen
if(ini_get('allow_url_fopen')):?>
<div class="ok">✅ allow_url_fopen activé</div>
<?php else:?>
<div class="warn">⚠️ allow_url_fopen désactivé (pas grave si cURL est disponible)</div>
<?php endif;?>

<?php
// 4. Tester l'appel API Gemini en direct
if (!empty($key) && $key !== 'VOTRE_CLE_GEMINI_ICI' && function_exists('curl_init')):
    $payload = json_encode([
        'contents' => [['role'=>'user','parts'=>[['text'=>'Réponds juste: OK']]]],
        'generationConfig' => ['maxOutputTokens'=>20]
    ]);
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key='.$key;
    $ch = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$payload,
        CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
        CURLOPT_TIMEOUT=>15, CURLOPT_SSL_VERIFYPEER=>false, CURLOPT_SSL_VERIFYHOST=>false,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code= curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($res === false || !empty($err)):?>
<div class="fail">❌ <strong>Erreur réseau</strong> : <?= htmlspecialchars($err) ?><br>→ Vérifiez que XAMPP peut accéder à Internet.</div>
    <?php else:
        $data = json_decode($res,true);
        if(isset($data['error'])):?>
<div class="fail">❌ <strong>Erreur API Google (<?= $code ?>)</strong> : <?= htmlspecialchars($data['error']['message']) ?></div>
<pre><?= htmlspecialchars(json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php elseif(isset($data['candidates'][0]['content']['parts'][0]['text'])):?>
<div class="ok">✅ <strong>API Gemini fonctionne !</strong> Réponse : "<?= htmlspecialchars($data['candidates'][0]['content']['parts'][0]['text']) ?>"</div>
        <?php else:?>
<div class="warn">⚠️ Réponse inattendue :<pre><?= htmlspecialchars(json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre></div>
        <?php endif;
    endif;
endif;?>

<hr style="margin:24px 0">
<p style="font-size:.82rem;color:#666">⚠️ Supprimez ce fichier (<code>test_ia.php</code>) après les tests.</p>
</body></html>
