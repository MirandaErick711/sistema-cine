<?php
/**
 * logout.php
 * 
 * Cierra la sesion del usuario de forma limpia y segura,
 * destruyendo todas las variables de sesion y la cookie asociada.
 */
require_once "funciones.php";

// Vaciar arreglo de sesion
$_SESSION = [];

// Eliminar la cookie de sesion en el navegador si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesion en el servidor
session_destroy();

// Redirigir a la pagina de inicio
header("Location: index.php");
exit;
