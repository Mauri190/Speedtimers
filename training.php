<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'save_training_time':
            saveTrainingTime();
            break;
        case 'get_training_times':
            getTrainingTimes();
            break;
        case 'delete_training_time':
            deleteTrainingTime();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}

function saveTrainingTime() {
    $user_id = getCurrentUserId();
    $puzzle_type = $_POST['puzzle_type'] ?? '';
    $phase = $_POST['phase'] ?? '';
    $time_value = floatval($_POST['time_value'] ?? 0);
    $scramble = $_POST['scramble'] ?? '';
    
    if (empty($puzzle_type) || empty($phase) || $time_value <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        return;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO training_sessions (user_id, puzzle_type, phase, time_value, scramble) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $puzzle_type, $phase, $time_value, $scramble]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar tiempo de entrenamiento: ' . $e->getMessage()]);
    }
}

function getTrainingTimes() {
    $user_id = getCurrentUserId();
    $puzzle_type = $_POST['puzzle_type'] ?? '';
    $phase = $_POST['phase'] ?? '';
    $limit = intval($_POST['limit'] ?? 100);
    
    try {
        $pdo = getDBConnection();
        
        if (!empty($puzzle_type) && !empty($phase)) {
            $stmt = $pdo->prepare("SELECT * FROM training_sessions WHERE user_id = ? AND puzzle_type = ? AND phase = ? ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$user_id, $puzzle_type, $phase, $limit]);
        } elseif (!empty($puzzle_type)) {
            $stmt = $pdo->prepare("SELECT * FROM training_sessions WHERE user_id = ? AND puzzle_type = ? ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$user_id, $puzzle_type, $limit]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM training_sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$user_id, $limit]);
        }
        
        $times = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'times' => $times]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener tiempos de entrenamiento: ' . $e->getMessage()]);
    }
}

function deleteTrainingTime() {
    $user_id = getCurrentUserId();
    $training_id = intval($_POST['training_id'] ?? 0);
    
    if ($training_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM training_sessions WHERE id = ? AND user_id = ?");
        $stmt->execute([$training_id, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Tiempo de entrenamiento eliminado']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar tiempo de entrenamiento: ' . $e->getMessage()]);
    }
}
?>