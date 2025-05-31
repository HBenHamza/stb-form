<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: admin.php');
    exit;
}

$questions = json_decode(file_get_contents('questions.json'), true);
$data = json_decode(file_get_contents('test_data.json'), true);

function countResponses($responses, $qid, $choices) {
    $count = array_fill_keys($choices, 0);
    foreach ($responses as $resp) {
        if (isset($resp[$qid])) {
            foreach ($resp[$qid] as $val) {
                if (isset($count[$val])) {
                    $count[$val]++;
                }
            }
        }
    }
    return $count;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Simulation des Statistiques</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
<div class="container">
    <h2 class="my-4 text-center">📊 Simulation des Statistiques – Admin</h2>

    <h3>Clients</h3>
    <?php foreach ($questions['form_client'] as $q):
        $qId = (string)$q['id'];
        $counts = countResponses($data['client'], $qId, $q['choix']);
        $labels = json_encode(array_keys($counts));
        $values = json_encode(array_values($counts));
        ?>
        <div class="chart-container">
            <h5><?= htmlspecialchars($q['texte']) ?></h5>
            <canvas id="chart_client_<?= $qId ?>"></canvas>
        </div>
        <script>
            new Chart(document.getElementById("chart_client_<?= $qId ?>"), {
                type: "pie",
                data: {
                    labels: <?= $labels ?>,
                    datasets: [{
                        data: <?= $values ?>,
                        backgroundColor: ['#0d6efd', '#dc3545', '#ffc107', '#198754']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        </script>
    <?php endforeach; ?>

    <h3>Employés</h3>
    <?php foreach ($questions['form_employe'] as $q):
        $qId = (string)$q['id'];
        $counts = countResponses($data['employe'], $qId, $q['choix']);
        $labels = json_encode(array_keys($counts));
        $values = json_encode(array_values($counts));
        ?>
        <div class="chart-container">
            <h5><?= htmlspecialchars($q['texte']) ?></h5>
            <canvas id="chart_employe_<?= $qId ?>"></canvas>
        </div>
        <script>
            new Chart(document.getElementById("chart_employe_<?= $qId ?>"), {
                type: "pie",
                data: {
                    labels: <?= $labels ?>,
                    datasets: [{
                        data: <?= $values ?>,
                        backgroundColor: ['#0d6efd', '#dc3545', '#ffc107', '#198754']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        </script>
    <?php endforeach; ?>
</div>
</body>
</html>
