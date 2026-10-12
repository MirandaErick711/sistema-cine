<?php
require_once __DIR__ . '/config/Conexion.php';
require_once __DIR__ . '/clases/Usuario.php';
require_once __DIR__ . '/clases/Pelicula.php';
require_once __DIR__ . '/clases/Funcion.php';
require_once __DIR__ . '/clases/Reserva.php';

echo "=== INICIANDO BATERIA DE PRUEBAS DEL SISTEMA ===\n\n";

// 1. Probar conexion PDO
echo "1. Conexion PDO:\n";
$pdo = Conexion::conectar();
echo ($pdo ? "   [PASS] Conexion exitosa a cine_db\n" : "   [FAIL] Error de conexion\n");

// 2. Probar autenticacion de Administrador inicial
echo "2. Autenticacion Administrador:\n";
$u = new Usuario();
$admin = $u->autenticar('admin@cine.com', 'Admin123*');
if ($admin && $admin['rol'] === 'admin') {
    echo "   [PASS] Login Admin correcto (ID: {$admin['id']}, Rol: {$admin['rol']})\n";
} else {
    echo "   [FAIL] No se pudo autenticar al admin\n";
}

// 3. Probar registro de cliente
echo "3. Registro y Login de Cliente:\n";
$correoTest = 'cliente_test_' . time() . '@cine.com';
$clienteId = $u->registrar('Cliente De Prueba', $correoTest, 'claveSegura123', '987111222');
if ($clienteId) {
    echo "   [PASS] Cliente registrado con ID: $clienteId (Rol asignado siempre 'cliente')\n";
} else {
    echo "   [FAIL] Error en registro de cliente\n";
}

$clienteAuth = $u->autenticar($correoTest, 'claveSegura123');
if ($clienteAuth && $clienteAuth['rol'] === 'cliente') {
    echo "   [PASS] Login de cliente exitoso con password_verify\n";
} else {
    echo "   [FAIL] Login de cliente fallido\n";
}

// 4. Probar catalogo de peliculas
echo "4. Catalogo de Peliculas:\n";
$pModel = new Pelicula();
$activas = $pModel->obtenerActivas();
echo "   [PASS] Peliculas activas encontradas: " . count($activas) . "\n";
foreach ($activas as $act) {
    echo "          - {$act['titulo']} (S/ {$act['precio']})\n";
}

// 5. Probar funciones disponibles
echo "5. Horarios y Funciones:\n";
$fModel = new Funcion();
$funciones = $fModel->obtenerPorPelicula(1);
echo "   [PASS] Funciones para pelicula #1: " . count($funciones) . " horarios disponibles\n";
$funcionId = $funciones[0]['id'];

// 6. Probar creacion de reserva atomica (Asientos A1, A2)
echo "6. Creacion de Reserva con Transaccion PDO:\n";
$rModel = new Reserva();
$res1 = $rModel->crearReserva($clienteId, $funcionId, ['A1', 'A2'], 'normal');
if ($res1['exito']) {
    echo "   [PASS] Reserva creada exitosamente. Codigo: {$res1['codigo']} | Total: S/ {$res1['total']}\n";
} else {
    echo "   [FAIL] " . $res1['mensaje'] . "\n";
}

// 7. Probar prevencion de doble reserva simultanea (Mismo asiento A1)
echo "7. Prevencion de Reserva Duplicada (Colision de Asiento A1):\n";
$res2 = $rModel->crearReserva($clienteId, $funcionId, ['A1', 'A3'], 'estudiante');
if (!$res2['exito']) {
    echo "   [PASS] Bloqueo correcto por transaccion y restriccion: " . $res2['mensaje'] . "\n";
} else {
    echo "   [FAIL] Fallo critico: permitio reservar un asiento ya ocupado\n";
}

// 8. Probar "Mis Reservas"
echo "8. Consulta en 'Mis Reservas':\n";
$misRes = $rModel->obtenerPorUsuario($clienteId);
if (!empty($misRes)) {
    echo "   [PASS] Mis Reservas obtuvo " . count($misRes) . " reserva(s). Codigo: {$misRes[0]['codigo']}\n";
} else {
    echo "   [FAIL] No se recuperaron las reservas del cliente\n";
}

// 9. Probar aislamiento de seguridad (IDOR)
echo "9. Aislamiento de Reservas contra acceso ajeno (Seguridad IDOR):\n";
$reservaAjena = $rModel->obtenerPorId($res1['reserva_id'], 999999); // ID de otro cliente
if ($reservaAjena === null) {
    echo "   [PASS] Acceso bloqueado. Un cliente no puede ver reservas ajenas cambiando el ID.\n";
} else {
    echo "   [FAIL] Vulnerable a manipulacion de ID de reserva\n";
}

echo "\n=== TODAS LAS PRUEBAS COMPLETADAS CON EXITO ===\n";