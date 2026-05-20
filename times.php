<?php
require_once 'config.php';

header('Content-Type: application/json');

// Debug: Verificamos si llega la sesión
error_log("Times.php called - User ID: " . ($_SESSION['user_id'] ?? 'NULL'));

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'save_time':
            saveTime();
            break;
        case 'get_times':
            getTimes();
            break;
        case 'delete_time':
            deleteTime();
            break;
        case 'update_penalty':
            updatePenalty();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}

function saveTime() {
    $user_id = getCurrentUserId();
    $category = $_POST['category'] ?? '';
    $time_value = floatval($_POST['time_value'] ?? 0);
    $scramble = $_POST['scramble'] ?? '';
    $penalty = $_POST['penalty'] ?? 'none';
    
    if (empty($category) || $time_value <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        return;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO times (user_id, category, time_value, scramble, penalty) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $category, $time_value, $scramble, $penalty]);
        
        $lastId = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'id' => $lastId]);
        
    } catch(PDOException $e) {
        error_log("Error saving time: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
    }
}

function getTimes() {
    $user_id = getCurrentUserId();
    $category = $_POST['category'] ?? '333';
    $limit = intval($_POST['limit'] ?? 100);
    
    try {
        $pdo = getDBConnection();
        // CORRECCIÓN CRÍTICA AQUÍ:
        // No pasamos el limit en el execute array, usamos bindValue para asegurar que sea INT
        $stmt = $pdo->prepare("SELECT * FROM times WHERE user_id = :user_id AND category = :category ORDER BY created_at DESC LIMIT :limit");
        
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $times = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'times' => $times]);
        
    } catch(PDOException $e) {
        error_log("Error getting times: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al obtener tiempos: ' . $e->getMessage()]);
    }
}

function deleteTime() {
    $user_id = getCurrentUserId();
    $time_id = intval($_POST['time_id'] ?? 0);
    
    if ($time_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM times WHERE id = ? AND user_id = ?");
        $stmt->execute([$time_id, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Tiempo eliminado']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
    }
}

function updatePenalty() {
    $user_id = getCurrentUserId();
    $time_id = intval($_POST['time_id'] ?? 0);
    $penalty = $_POST['penalty'] ?? 'none';
    
    try {
        $pdo = getDBConnection();
        // Verificamos que el tiempo pertenezca al usuario antes de actualizar
        $stmt = $pdo->prepare("UPDATE times SET penalty = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$penalty, $time_id, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Penalización actualizada']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
    }
}
?>