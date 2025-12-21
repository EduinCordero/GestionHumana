<?php
// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_name(APP_SESSION_NAME);

    // Configurar opciones de sesión seguras
    session_set_cookie_params([
        'lifetime' => 3600,       // Duración de la sesión en segundos (1 hora)
        'path' => '/',            // Disponible en toda la aplicación
        // 'domain' => $_SERVER['HTTP_HOST'], // <--- COMENTAR O ELIMINAR ESTA LÍNEA
        'secure' => isset($_SERVER['HTTPS']), // Solo en HTTPS si está disponible
        'httponly' => true,       // Evita acceso a cookies por JavaScript
        'samesite' => 'Lax'       // Protección contra ataques CSRF
    ]);

    session_start();

    // Regenerar ID de sesión periódicamente para evitar session fixation
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
        session_regenerate_id(true);
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // Cada 30 min
        $_SESSION['last_regeneration'] = time();
        session_regenerate_id(true);
    }
}