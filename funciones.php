<?php
/**
 * funciones.php
 * 
 * Funciones de utilidad global para el sistema de Cine:
 * - Manejo seguro de sesiones PHP
 * - Control de autenticacion y autorizacion por roles (admin / cliente)
 * - Proteccion contra ataques CSRF (Cross-Site Request Forgery)
 * - Limpieza y escape contra ataques XSS
 * - Reutilizacion de la funcion clasica obtenerPelicula() para mantener compatibilidad
 */

// Iniciar sesion segura si no esta activa
if (session_status() === PHP_SESSION_NONE) {
    // Buenas practicas de seguridad en cookies de sesion
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

require_once __DIR__ . '/config/Conexion.php';
require_once __DIR__ . '/clases/Pelicula.php';
require_once __DIR__ . '/clases/Usuario.php';
require_once __DIR__ . '/clases/Funcion.php';
require_once __DIR__ . '/clases/Reserva.php';

/**
 * Escapa cadenas para prevenir ataques XSS al imprimir en el HTML
 * 
 * @param string|null $texto
 * @return string
 */
function e($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Genera y almacena un token CSRF unico en la sesion del usuario
 * 
 * @return string
 */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Imprime el input oculto con el token CSRF para formularios POST
 */
function csrf_field()
{
    echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Valida que el token CSRF enviado coincida con el almacenado en la sesion
 * 
 * @return bool
 */
function csrf_validar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die('Error de seguridad (CSRF): La solicitud no es valida o ha expirado. Por favor, vuelva a intentar.');
        }
    }
    return true;
}

/**
 * Comprueba si hay un usuario autenticado en el sistema
 * 
 * @return bool
 */
function estaAutenticado()
{
    return !empty($_SESSION['usuario_id']);
}

/**
 * Comprueba si el usuario autenticado tiene rol de administrador
 * 
 * @return bool
 */
function esAdmin()
{
    return estaAutenticado() && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

/**
 * Obtiene los datos del usuario logueado almacenados en sesion
 * 
 * @return array|null
 */
function usuarioActual()
{
    if (!estaAutenticado()) {
        return null;
    }
    return [
        'id'     => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'] ?? '',
        'correo' => $_SESSION['usuario_correo'] ?? '',
        'rol'    => $_SESSION['usuario_rol'] ?? 'cliente',
    ];
}

/**
 * Protege una pagina requiriendo que el usuario este autenticado
 * Si no lo esta, redirige a login.php
 */
function requerirAutenticacion()
{
    if (!estaAutenticado()) {
        $_SESSION['mensaje_error'] = "Debe iniciar sesion para acceder a esta seccion.";
        header("Location: login.php");
        exit;
    }
}

/**
 * Protege el panel de administracion requiriendo rol 'admin'
 * Si el usuario no es admin, bloquea el acceso en el servidor
 */
function requerirAdmin()
{
    if (!esAdmin()) {
        header("Location: ../login.php?error=acceso_denegado");
        exit;
    }
}

/**
 * Funcion de compatibilidad con el proyecto existente
 * Obtiene una pelicula por su ID consultando a la base de datos a traves de la clase Pelicula
 * 
 * @param int $id
 * @return array|null
 */
function obtenerPelicula($id)
{
    $peliculaModel = new Pelicula();
    return $peliculaModel->obtenerPorId($id);
}