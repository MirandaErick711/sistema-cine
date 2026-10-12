<?php
/**
 * admin/eliminar_pelicula.php
 * 
 * Controlador para activar, desactivar o eliminar una pelicula
 * Protegido con requerirAdmin() y verificacion de token CSRF
 */
require_once __DIR__ . '/../funciones.php';
requerirAdmin();

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$accion = $_GET['accion'] ?? '';
$token  = $_GET['token'] ?? '';

// Validar token CSRF para peticiones GET criticas
if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    die("Solicitud invalida o expirada. Por favor vuelva a intentarlo desde el panel.");
}

$peliculaModel = new Pelicula();
$pelicula = $peliculaModel->obtenerPorId($id);

if (!$pelicula) {
    $_SESSION['mensaje_error'] = "Pelicula no encontrada.";
    header("Location: peliculas.php");
    exit;
}

if ($accion === 'desactivar') {
    $peliculaModel->cambiarEstado($id, 'inactiva');
    $_SESSION['mensaje_exito'] = "La pelicula '{$pelicula['titulo']}' fue retirada de la cartelera (inactiva).";
} elseif ($accion === 'activar') {
    $peliculaModel->cambiarEstado($id, 'activa');
    $_SESSION['mensaje_exito'] = "La pelicula '{$pelicula['titulo']}' fue activada en la cartelera.";
} elseif ($accion === 'eliminar') {
    // Si la pelicula tiene reservas asociadas, la restriccion de clave foranea
    // la mantiene segura y el metodo eliminar() la marca como inactiva
    $eliminado = $peliculaModel->eliminar($id);
    if ($eliminado) {
        $_SESSION['mensaje_exito'] = "La pelicula '{$pelicula['titulo']}' ha sido eliminada del sistema.";
    } else {
        $_SESSION['mensaje_error'] = "La pelicula '{$pelicula['titulo']}' tiene historial de funciones o reservas. Se cambio su estado a inactiva para proteger la integridad de los datos.";
    }
}

header("Location: peliculas.php");
exit;
