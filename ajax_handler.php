<?php
// ajax_handler.php
header('Content-Type: application/json');

$formType = $_POST['formType'] ?? '';
$action = $_POST['action'] ?? 'submit';

if (!in_array($formType, ['client','employe'])) {
    echo json_encode(['status'=>'error','message'=>'Type de formulaire invalide']);
    exit;
}

// Questions en dur (identiques à formulaire.php)
if ($formType === 'client') {
    $questions = [
        ['id'=>1, 'choix'=>['Service rapide','Amabilité du personnel','Propreté des locaux']],
        ['id'=>2, 'choix'=>['Accueil chaleureux','Conseils pertinents','Temps d’attente réduit']],
        ['id'=>3, 'choix'=>['Plus d’agences','Meilleure communication','Services digitaux améliorés']],
        ['id'=>4, 'choix'=>['Téléphone','Email','Agence']],
        ['id'=>5, 'choix'=>['Disponibilité','Amabilité','Compétence']],
        ['id'=>6, 'choix'=>['Application mobile','Site web','Services SMS']],
        ['id'=>7, 'choix'=>['Suffisants','À étendre','À réduire']],
        ['id'=>8, 'choix'=>['Bonne','Acceptable','Insuffisante']],
        ['id'=>9, 'choix'=>['Oui','Non']],
        ['id'=>10,'choix'=>['Oui','Non']],
    ];
} else {
    $questions = [
        ['id'=>1, 'choix'=>['Intranet','CRM','Messagerie']],
        ['id'=>2, 'choix'=>['Technique','Relation client','Management']],
        ['id'=>3, 'choix'=>['Logiciel X','Application Y','Outil Z']],
        ['id'=>4, 'choix'=>['Process simplifiés','Meilleure communication','Plus de ressources']],
        ['id'=>5, 'choix'=>['Écoute','Soutien','Clarté des objectifs']],
    ];
}

define('DATA_FILE', __DIR__."/data_{$formType}.json");
if (!file_exists(DATA_FILE)) file_put_contents(DATA_FILE, json_encode([]));
$data = json_decode(file_get_contents(DATA_FILE), true);

if ($action === 'get_stats') {
    // Calcul stats pour chaque question
    $stats = [];
    foreach ($questions as $q) {
        $qid = 'q_'.$q['id'];
        $counts = array_fill_keys($q['choix'], 0);

        foreach ($data as $resp) {
            if (!isset($resp['reponses'][$qid])) continue;
            foreach ($resp['reponses'][$qid] as $answer) {
                if (isset($counts[$answer])) $counts[$answer]++;
            }
        }
        $total = count($data);
        if ($total === 0) $total = 1; // éviter div 0

        $percentages = [];
        foreach ($counts as $k => $v) {
            $percentages[$k] = round($v * 100 / $total);
        }
        $stats[$qid] = $percentages;
    }
    echo json_encode(['status'=>'success','stats'=>$stats,'questions'=>$questions]);
    exit;
}

// Sinon => réception réponse formulaire

// Limiter une réponse par IP
$ip = $_SERVER['REMOTE_ADDR'];
foreach ($data as $resp) {
    if ($resp['ip'] === $ip) {
        echo json_encode(['status'=>'error','message'=>'Vous avez déjà répondu à ce formulaire.']);
        exit;
    }
}

// Validation et récupération réponses
$reponses = [];
foreach ($questions as $q) {
    $qid = 'q_'.$q['id'];
    if (!isset($_POST[$qid]) || !is_array($_POST[$qid]) || count($_POST[$qid]) === 0) {
        echo json_encode(['status'=>'error', 'message'=>"Vous devez cocher au moins une réponse pour la question #{$q['id']}."]);
        exit;
    }

    // Valider chaque réponse est dans choix autorisés
    foreach ($_POST[$qid] as $rep) {
        if (!in_array($rep, $q['choix'])) {
            echo json_encode(['status'=>'error', 'message'=>'Réponse invalide détectée.']);
            exit;
        }
    }

    $reponses[$qid] = $_POST[$qid];
}

// Enregistrer la réponse
$data[] = ['ip'=>$ip, 'reponses'=>$reponses];
file_put_contents(DATA_FILE, json_encode($data));

// Recalcul stats (même code que pour get_stats)
$stats = [];
foreach ($questions as $q) {
    $qid = 'q_'.$q['id'];
    $counts = array_fill_keys($q['choix'], 0);
    foreach ($data as $resp) {
        if (!isset($resp['reponses'][$qid])) continue;
        foreach ($resp['reponses'][$qid] as $answer) {
            if (isset($counts[$answer])) $counts[$answer]++;
        }
    }
    $total = count($data);
    if ($total === 0) $total = 1;
    $percentages = [];
    foreach ($counts as $k => $v) {
        $percentages[$k] = round($v * 100 / $total);
    }
    $stats[$qid] = $percentages;
}

echo json_encode(['status'=>'success','stats'=>$stats,'questions'=>$questions]);
exit;
