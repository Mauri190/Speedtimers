<?php
// Configuración de la base de datos
define('DB_HOST', 'sql306.infinityfree.com');
define('DB_NAME', 'if0_41938707_speedtimer');
define('DB_USER', 'if0_41938707');
define('DB_PASS', 'Vos13073');

// Iniciar sesión solo si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        error_log('Error de conexión a la base de datos: ' . $e->getMessage());
        die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
    }
}

// Verificar si el usuario está autenticado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

// Obtener ID del usuario actual
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Obtener nombre de usuario actual
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

// Debug function para verificar sesión
function debugSession() {
    error_log("Session Debug - user_id: " . ($_SESSION['user_id'] ?? 'NULL') . ", username: " . ($_SESSION['username'] ?? 'NULL'));
}
?>
