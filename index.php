<?php
require_once 'config.php';

// Si el usuario ya está autenticado, va al timer
if (isLoggedIn()) {
    header('Location: index.html');
    exit;
} else {
    // Si no está autenticado, va a la landing page
    header('Location: landing.html');
    exit;
}
?>