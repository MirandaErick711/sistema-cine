<?php
/**
 * admin/guardar_pelicula.php
 * 
 * Controlador para procesar el guardado (insercion o actualizacion) de una pelicula
 * Aplica validaciones estrictas en el servidor, proteccion CSRF y
 * subida segura de imagenes validando tipos MIME reales y extensiones
 */
require_once __DIR__ . '/../funciones.php';
requerirAdmin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: peliculas.php");
    exit;
}

csrf_validar();

$id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$titulo    = trim($_POST['titulo'] ?? '');
$genero    = trim($_POST['genero'] ?? '');
$duracion  = trim($_POST['duracion'] ?? '');
$precio    = (float)($_POST['precio'] ?? 0);
$sinopsis  = trim($_POST['sinopsis'] ?? '');
$trailer   = trim($_POST['trailer'] ?? '');
$estado    = in_array($_POST['estado'] ?? '', ['activa', 'inactiva']) ? $_POST['estado'] : 'activa';

$peliculaModel = new Pelicula();
$peliculaExistente = null;

if ($id > 0) {
    $peliculaExistente = $peliculaModel->obtenerPorId($id);
    if (!$peliculaExistente) {
        $_SESSION['mensaje_error'] = "Pelicula no encontrada.";
        header("Location: peliculas.php");
        exit;
    }
}

// Validaciones minimas
if (empty($titulo) || empty($genero) || empty($duracion) || $precio <= 0) {
    $_SESSION['mensaje_error'] = "Por favor complete todos los campos obligatorios correctamente.";
    header("Location: pelicula_form.php" . ($id > 0 ? "?id=$id" : ""));
    exit;
}

// Manejo seguro de la imagen
$rutaImagen = $peliculaExistente ? $peliculaExistente['imagen'] : 'img/demon.jpg'; // Imagen por defecto si no sube

if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {
    $archivoTmp = $_FILES['imagen_archivo']['tmp_name'];
    $nombreOriginal = $_FILES['imagen_archivo']['name'];
    $tamano = $_FILES['imagen_archivo']['size'];

    // 1. Validar tamaño (maximo 2MB)
    if ($tamano > 2 * 1024 * 1024) {
        $_SESSION['mensaje_error'] = "La imagen supera el limite de 2MB.";
        header("Location: pelicula_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    // 2. Validar extension
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $extensionesPermitidas)) {
        $_SESSION['mensaje_error'] = "Formato de archivo no permitido. Solo se aceptan JPG, PNG y WEBP.";
        header("Location: pelicula_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    // 3. Validar tipo MIME real con getimagesize()
    $infoImagen = @getimagesize($archivoTmp);
    if ($infoImagen === false) {
        $_SESSION['mensaje_error'] = "El archivo subido no es una imagen valida.";
        header("Location: pelicula_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    // 4. Generar nombre de archivo seguro y unico
    $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower(substr($titulo, 0, 15)));
    $nombreFinal = $nombreLimpio . '_' . time() . '.' . $extension;
    $destinoFisico = __DIR__ . '/../img/' . $nombreFinal;

    if (move_uploaded_file($archivoTmp, $destinoFisico)) {
        $rutaImagen = 'img/' . $nombreFinal;
    } else {
        $_SESSION['mensaje_error'] = "Error al guardar el archivo en el servidor.";
        header("Location: pelicula_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }
}

// Preparar arreglo de datos
$datos = [
    'titulo'   => $titulo,
    'genero'   => $genero,
    'duracion' => $duracion,
    'precio'   => $precio,
    'sinopsis' => $sinopsis,
    'trailer'  => $trailer,
    'imagen'   => $rutaImagen,
    'estado'   => $estado
];

if ($id > 0) {
    // Actualizar pelicula
    $ok = $peliculaModel->actualizar($id, $datos);
    if ($ok) {
        $_SESSION['mensaje_exito'] = "Pelicula '{$titulo}' actualizada exitosamente.";
    } else {
        $_SESSION['mensaje_error'] = "Error al actualizar la pelicula.";
    }
} else {
    // Crear nueva pelicula
    $nuevoId = $peliculaModel->crear($datos);
    if ($nuevoId) {
        $_SESSION['mensaje_exito'] = "Pelicula '{$titulo}' registrada correctamente.";
    } else {
        $_SESSION['mensaje_error'] = "Error al registrar la pelicula.";
    }
}

header("Location: peliculas.php");
exit;
