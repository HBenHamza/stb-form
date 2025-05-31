<?php
$data = json_decode(file_get_contents('php://input'), true);
$questions = json_decode(file_get_contents('questions.json'), true);

switch ($data['action']) {
    case 'add':
        $newId = max(array_column($questions[$data['type']], 'id') ?: [0]) + 1;
        $questions[$data['type']][] = [
            'id' => $newId,
            'texte' => $data['texte'],
            'choix' => $data['choix']
        ];
        break;
    case 'delete':
        array_splice($questions[$data['type']], $data['index'], 1);
        break;
    case 'edit':
        $questions[$data['type']][$data['index']]['texte'] = $data['texte'];
        $questions[$data['type']][$data['index']]['choix'] = $data['choix'];
        break;
}

file_put_contents('questions.json', json_encode($questions, JSON_PRETTY_PRINT));
echo 'OK';
