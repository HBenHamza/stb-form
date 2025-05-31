<?php
session_start();

// Auth simple
$adminUser = 'admin';
$adminPass = 'nimda';

if (isset($_POST['username'], $_POST['password'])) {
    if ($_POST['username'] === $adminUser && $_POST['password'] === $adminPass) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Identifiants invalides.";
    }
}

if (!isset($_SESSION['admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <title>Login Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    </head>
    <body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card shadow p-4" style="width: 350px;">
        <h3 class="mb-4 text-center">Connexion Admin</h3>
        <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST" action="admin.php">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Utilisateur" required autofocus>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

$questionsJson = file_get_contents(__DIR__ . '/questions.json');
$questionsData = json_decode($questionsJson, true);

function countResponses($responses, $qid, $choices) {
    $counts = array_fill_keys($choices, 0);
    foreach ($responses as $resp) {
        if (isset($resp[$qid])) {
            foreach ($resp[$qid] as $answer) {
                if (isset($counts[$answer])) {
                    $counts[$answer]++;
                }
            }
        }
    }
    return $counts;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Administration STB Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .chart-container { max-width: 600px; margin: auto; }
        .question-block { background: white; border-radius: 10px; padding: 20px; margin-bottom: 30px; box-shadow: 0 0 10px rgb(0 0 0 / 0.1); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">STB Form - Admin</a>
        <a href="test_stats.php" class="btn btn-warning">Voir stats test (données simulées)</a>
        <div>
            <a href="admin.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container">
    <?php foreach (['client' => 'form_client', 'employe' => 'form_employe'] as $formType => $formKey): ?>
        <h2 class="mb-4"><?= ucfirst($formType) ?> - Statistiques</h2>
        <?php
        $fileData = __DIR__ . '/data_' . $formType . '.json';
        $responses = file_exists($fileData) ? json_decode(file_get_contents($fileData), true) : [];

        foreach ($questionsData[$formKey] as $q):
            $counts = countResponses($responses, (string)$q['id'], $q['choix']);
            $total = array_sum($counts);
            if ($total === 0) $total = 1;
            ?>
            <div class="question-block">
                <h4><?= htmlspecialchars($q['texte']) ?></h4>
                <div class="chart-container">
                    <canvas id="chart_<?= $formType ?>_<?= $q['id'] ?>"></canvas>
                </div>
                <ul class="list-group list-group-horizontal justify-content-center mt-3">
                    <?php foreach ($counts as $label => $count):
                        $percent = round(($count/$total)*100);
                        ?>
                        <li class="list-group-item border-0 px-3">
                            <strong><?= htmlspecialchars($label) ?></strong> : <?= $count ?> (<?= $percent ?>%)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<script>
    const colors = [
        '#0d6efd', '#dc3545', '#ffc107', '#198754', '#6c757d',
        '#fd7e14', '#6610f2', '#20c997', '#0dcaf0', '#d63384'
    ];
    <?php foreach (['client' => 'form_client', 'employe' => 'form_employe'] as $formType => $formKey):
    $fileData = __DIR__ . '/data_' . $formType . '.json';
    $responses = file_exists($fileData) ? json_decode(file_get_contents($fileData), true) : [];
    foreach ($questionsData[$formKey] as $q):
    $counts = [];
    foreach ($q['choix'] as $choice) {
        $counts[$choice] = 0;
    }
    foreach ($responses as $resp) {
        if (isset($resp[(string)$q['id']])) {
            foreach ($resp[(string)$q['id']] as $ans) {
                if (isset($counts[$ans])) $counts[$ans]++;
            }
        }
    }
    $labels = json_encode(array_keys($counts));
    $data = json_encode(array_values($counts));
    ?>
    const ctx_<?= $formType ?>_<?= $q['id'] ?> = document.getElementById('chart_<?= $formType ?>_<?= $q['id'] ?>').getContext('2d');
    new Chart(ctx_<?= $formType ?>_<?= $q['id'] ?>, {
        type: 'pie',
        data: {
            labels: <?= $labels ?>,
            datasets: [{
                data: <?= $data ?>,
                backgroundColor: colors.slice(0, <?= count($q['choix']) ?>)
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    enabled: true
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endforeach; endforeach; ?>
</script>

</body>
</html>
