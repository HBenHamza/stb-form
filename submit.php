<?php
session_start();

$formType = $_GET['form'] ?? 'client';
if (!in_array($formType, ['client', 'employe'])) {
    die('Formulaire invalide');
}

$fileData = __DIR__ . '/data_' . $formType . '.json';

// Lecture des données existantes
if (file_exists($fileData)) {
    $allResponses = json_decode(file_get_contents($fileData), true);
    if (!is_array($allResponses)) $allResponses = [];
} else {
    $allResponses = [];
}

// Préparer la réponse de l'utilisateur
$response = [];
foreach ($_POST as $key => $values) {
    if (strpos($key, 'q_') === 0) {
        $qid = substr($key, 2);
        // Les valeurs sont en tableau (checkbox multiple)
        if (is_array($values)) {
            $response[$qid] = $values;
        } else {
            $response[$qid] = [$values];
        }
    }
}

// Optionnel : empêcher double soumission, ici basique par session
if (isset($_SESSION['submitted_'.$formType])) {
    die("Vous avez déjà soumis ce formulaire.");
}

$allResponses[] = $response;
file_put_contents($fileData, json_encode($allResponses, JSON_PRETTY_PRINT));

$_SESSION['submitted_'.$formType] = true;

header('Location: thankyou.php');
exit;
