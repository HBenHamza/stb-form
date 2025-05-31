<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Questions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .question-block {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
        }
        .question-type {
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
<div class="container my-5">
    <h2 class="mb-4 text-center">🛠️ Gestion des Questions (Admin)</h2>

    <div id="questionList"></div>

    <hr>
    <h4>➕ Ajouter une question</h4>
    <form id="addForm" class="mb-5">
        <div class="mb-3">
            <label class="form-label">Formulaire</label>
            <select class="form-select" name="form_type" required>
                <option value="form_client">Client</option>
                <option value="form_employe">Employé</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Texte de la question</label>
            <input type="text" name="texte" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Choix (un par ligne)</label>
            <textarea name="choix" class="form-control" rows="3" required></textarea>
        </div>
        <button class="btn btn-success">Ajouter</button>
    </form>
</div>

<script>
    function loadQuestions() {
        fetch('questions.json')
            .then(res => res.json())
            .then(data => {
                let html = '';
                for (let type in data) {
                    html += `<h5 class="mt-4">${type === 'form_client' ? 'Questions Clients' : 'Questions Employés'}</h5>`;
                    data[type].forEach((q, index) => {
                        const choices = q.choix.map(c => `<li>${c}</li>`).join('');
                        html += `
                        <div class="question-block">
                            <strong>${q.texte}</strong>
                            <ul>${choices}</ul>
                            <button class="btn btn-sm btn-warning me-2" onclick="editQuestion('${type}', ${index})">✏️ Modifier</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteQuestion('${type}', ${index})">🗑️ Supprimer</button>
                        </div>
                    `;
                    });
                }
                document.getElementById('questionList').innerHTML = html;
            });
    }

    function deleteQuestion(type, index) {
        if (!confirm("Supprimer cette question ?")) return;
        fetch('save_questions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', type, index })
        }).then(() => loadQuestions());
    }

    function editQuestion(type, index) {
        const newText = prompt("Nouveau texte de la question ?");
        if (!newText) return;
        const newChoices = prompt("Nouveaux choix (séparés par des virgules) ?");
        if (!newChoices) return;

        fetch('save_questions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'edit',
                type,
                index,
                texte: newText,
                choix: newChoices.split(',').map(c => c.trim())
            })
        }).then(() => loadQuestions());
    }

    document.getElementById('addForm').addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            action: 'add',
            type: formData.get('form_type'),
            texte: formData.get('texte'),
            choix: formData.get('choix').split('\n').map(c => c.trim())
        };
        fetch('save_questions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(() => {
            e.target.reset();
            loadQuestions();
        });
    });

    loadQuestions();
</script>
</body>
</html>
