<?php
// check_auth.php
require_once 'config.php';

header('Content-Type: application/json');

// Verificar si hay una sesión activa
if (isLoggedIn()) {
    echo json_encode([
        'authenticated' => true, 
        'username' => $_SESSION['username'],
        'user_id' => $_SESSION['user_id']
    ]);
} else {
    // Limpiar cualquier sesión residual
    session_unset();
    session_destroy();
    echo json_encode(['authenticated' => false]);
}
?>