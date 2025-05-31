<?php
session_start();

$questionsJson = file_get_contents(__DIR__ . '/questions.json');
$questionsData = json_decode($questionsJson, true);

$formType = $_GET['form'] ?? 'client';
if (!in_array($formType, ['client', 'employe'])) {
    die('Formulaire invalide');
}
$formKey = $formType === 'client' ? 'form_client' : 'form_employe';
$questions = $questionsData[$formKey] ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Formulaire <?=htmlspecialchars(ucfirst($formType))?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">Formulaire <?=htmlspecialchars(ucfirst($formType))?></h1>
    <form id="form" method="POST" action="submit.php?form=<?=htmlspecialchars($formType)?>">
        <?php foreach ($questions as $q): ?>
            <div class="mb-3">
                <label class="form-label fw-bold"><?=htmlspecialchars($q['texte'])?></label><br>
                <?php foreach ($q['choix'] as $choice): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="q_<?=$q['id']?>[]" value="<?=htmlspecialchars($choice)?>" id="q<?=$q['id']?>_<?=preg_replace('/\W+/', '', $choice)?>">
                        <label class="form-check-label" for="q<?=$q['id']?>_<?=preg_replace('/\W+/', '', $choice)?>">
                            <?=htmlspecialchars($choice)?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
</div>
</body>
</html>
