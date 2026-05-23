<?php
// Verificar si ya hay una sesión activa antes de iniciar una nueva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar permisos por rol (opcional)
function require_rol($rol) {
    if ($_SESSION['user_rol'] !== $rol && $_SESSION['user_rol'] !== 'ADMIN') {
        header('Location: index.php?error=no_permiso');
        exit;
    }
}

// Obtener usuario actual
function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nombre' => $_SESSION['user_name'] ?? null,
        'usuario' => $_SESSION['user_usuario'] ?? null,
        'rol' => $_SESSION['user_rol'] ?? null
    ];
}
?>