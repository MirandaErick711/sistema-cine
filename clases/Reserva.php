<?php
/**
 * Clase Reserva
 * 
 * Modela las operaciones de creacion, consulta y actualizacion de reservas.
 * Implementa transacciones de base de datos (PDO Transactions) para garantizar
 * que una reserva y sus asientos asociados se registren de forma atomica
 * y sin posibilidad de doble reserva simultanea.
 */
require_once __DIR__ . '/../config/Conexion.php';

class Reserva
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    /**
     * Genera un codigo unico y legible de reserva
     * @return string
     */
    public static function generarCodigo()
    {
        return 'RES-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    /**
     * Crea una reserva utilizando una transaccion SQL.
     * Calcula los precios en el servidor e inserta los detalles de los asientos.
     * Si un asiento ya estaba ocupado, la transaccion hace ROLLBACK automatico.
     * 
     * @param int $usuarioId
     * @param int $funcionId
     * @param array $asientos Arreglo de strings con los asientos elegidos, ej: ['A1', 'A2']
     * @param string $tipoEntrada 'normal' o 'estudiante' (aplica 20% descuento)
     * @return array Arreglo con estado ['exito' => bool, 'mensaje' => string, 'codigo' => string|null]
     */
    public function crearReserva($usuarioId, $funcionId, array $asientos, $tipoEntrada = 'normal')
    {
        if (empty($asientos)) {
            return ['exito' => false, 'mensaje' => 'Debe seleccionar al menos un asiento.', 'codigo' => null];
        }

        try {
            // Iniciar transaccion atomica
            $this->db->beginTransaction();

            // 1. Obtener la funcion y el precio de la pelicula directamente de la BD (con bloqueo de lectura FOR UPDATE)
            $sqlFuncion = "SELECT f.*, p.precio AS precio_base, p.titulo AS pelicula_titulo 
                           FROM funciones f 
                           INNER JOIN peliculas p ON f.pelicula_id = p.id 
                           WHERE f.id = :id AND f.estado = 'disponible' 
                           FOR UPDATE";
            $stmtFuncion = $this->db->prepare($sqlFuncion);
            $stmtFuncion->execute([':id' => (int)$funcionId]);
            $funcion = $stmtFuncion->fetch();

            if (!$funcion) {
                $this->db->rollBack();
                return ['exito' => false, 'mensaje' => 'La funcion seleccionada no existe o no se encuentra disponible.', 'codigo' => null];
            }

            // 2. Verificar disponibilidad de los asientos elegidos
            $placeholders = implode(',', array_fill(0, count($asientos), '?'));
            $sqlCheck = "SELECT asiento FROM detalle_reserva 
                         WHERE funcion_id = ? AND asiento IN ($placeholders)";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $parametros = array_merge([(int)$funcionId], $asientos);
            $stmtCheck->execute($parametros);
            $ocupados = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ocupados)) {
                $this->db->rollBack();
                $asientosOcupadosStr = implode(', ', $ocupados);
                return [
                    'exito' => false, 
                    'mensaje' => "El/Los asiento(s) {$asientosOcupadosStr} ya han sido reservados por otro cliente. Por favor, seleccione otros.",
                    'codigo' => null
                ];
            }

            // 3. Calculo seguro de precios en el SERVIDOR (sin confiar en el cliente)
            $precioUnitario = (float)$funcion['precio_base'];
            if ($tipoEntrada === 'estudiante') {
                $precioUnitario = round($precioUnitario * 0.80, 2); // 20% de descuento
            }

            $cantidad = count($asientos);
            $total = round($precioUnitario * $cantidad, 2);
            $codigo = self::generarCodigo();

            // 4. Insertar la cabecera de la reserva
            $sqlReserva = "INSERT INTO reservas (codigo, usuario_id, funcion_id, cantidad_entradas, total, tipo_entrada, estado) 
                           VALUES (:codigo, :usuario_id, :funcion_id, :cantidad, :total, :tipo_entrada, 'confirmada')";
            $stmtReserva = $this->db->prepare($sqlReserva);
            $stmtReserva->execute([
                ':codigo'       => $codigo,
                ':usuario_id'   => (int)$usuarioId,
                ':funcion_id'   => (int)$funcionId,
                ':cantidad'     => $cantidad,
                ':total'        => $total,
                ':tipo_entrada' => $tipoEntrada === 'estudiante' ? 'estudiante' : 'normal'
            ]);

            $reservaId = (int)$this->db->lastInsertId();

            // 5. Insertar cada asiento en detalle_reserva
            $sqlDetalle = "INSERT INTO detalle_reserva (reserva_id, funcion_id, asiento, precio_unitario) 
                           VALUES (:reserva_id, :funcion_id, :asiento, :precio_unitario)";
            $stmtDetalle = $this->db->prepare($sqlDetalle);

            foreach ($asientos as $asiento) {
                $stmtDetalle->execute([
                    ':reserva_id'       => $reservaId,
                    ':funcion_id'       => (int)$funcionId,
                    ':asiento'          => trim($asiento),
                    ':precio_unitario'  => $precioUnitario
                ]);
            }

            // Confirmar la transaccion
            $this->db->commit();

            return [
                'exito'   => true, 
                'mensaje' => '¡Reserva realizada con exito!', 
                'codigo'  => $codigo,
                'reserva_id' => $reservaId,
                'total'   => $total
            ];

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error al crear reserva: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Ocurrio un error al procesar la reserva. Por favor intente nuevamente.', 'codigo' => null];
        }
    }

    /**
     * Obtiene las reservas de un usuario especifico para su panel "Mis Reservas".
     * Protege el acceso asegurando que solo se listen las pertenecientes al usuario.
     * 
     * @param int $usuarioId
     * @return array
     */
    public function obtenerPorUsuario($usuarioId)
    {
        $sql = "SELECT r.*, 
                       p.titulo AS pelicula_titulo, p.imagen AS pelicula_imagen,
                       f.sala, f.fecha, f.hora,
                       GROUP_CONCAT(d.asiento ORDER BY d.asiento ASC SEPARATOR ', ') AS asientos
                FROM reservas r
                INNER JOIN funciones f ON r.funcion_id = f.id
                INNER JOIN peliculas p ON f.pelicula_id = p.id
                LEFT JOIN detalle_reserva d ON r.id = d.reserva_id
                WHERE r.usuario_id = :usuario_id
                GROUP BY r.id
                ORDER BY r.creado_en DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => (int)$usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los detalles de una reserva verificando que pertenezca al usuario (o si es admin).
     * 
     * @param int $id ID de la reserva
     * @param int|null $usuarioId Si se pasa, restringe la consulta al usuario
     * @return array|null
     */
    public function obtenerPorId($id, $usuarioId = null)
    {
        $sql = "SELECT r.*, 
                       u.nombre AS usuario_nombre, u.correo AS usuario_correo,
                       p.titulo AS pelicula_titulo, p.imagen AS pelicula_imagen,
                       f.sala, f.fecha, f.hora,
                       GROUP_CONCAT(d.asiento ORDER BY d.asiento ASC SEPARATOR ', ') AS asientos
                FROM reservas r
                INNER JOIN usuarios u ON r.usuario_id = u.id
                INNER JOIN funciones f ON r.funcion_id = f.id
                INNER JOIN peliculas p ON f.pelicula_id = p.id
                LEFT JOIN detalle_reserva d ON r.id = d.reserva_id
                WHERE r.id = :id";
        
        $parametros = [':id' => (int)$id];

        if ($usuarioId !== null) {
            $sql .= " AND r.usuario_id = :usuario_id";
            $parametros[':usuario_id'] = (int)$usuarioId;
        }

        $sql .= " GROUP BY r.id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        $reserva = $stmt->fetch();
        return $reserva ?: null;
    }

    /**
     * Obtiene todas las reservas registradas (para el panel administrativo).
     * 
     * @return array
     */
    public function obtenerTodas()
    {
        $sql = "SELECT r.*, 
                       u.nombre AS usuario_nombre, u.correo AS usuario_correo,
                       p.titulo AS pelicula_titulo,
                       f.sala, f.fecha, f.hora,
                       GROUP_CONCAT(d.asiento ORDER BY d.asiento ASC SEPARATOR ', ') AS asientos
                FROM reservas r
                INNER JOIN usuarios u ON r.usuario_id = u.id
                INNER JOIN funciones f ON r.funcion_id = f.id
                INNER JOIN peliculas p ON f.pelicula_id = p.id
                LEFT JOIN detalle_reserva d ON r.id = d.reserva_id
                GROUP BY r.id
                ORDER BY r.creado_en DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Permite al administrador cambiar el estado de una reserva (confirmada, cancelada, finalizada).
     * 
     * @param int $id
     * @param string $nuevoEstado
     * @return bool
     */
    public function cambiarEstado($id, $nuevoEstado)
    {
        if (!in_array($nuevoEstado, ['confirmada', 'cancelada', 'finalizada'])) {
            return false;
        }

        $sql = "UPDATE reservas SET estado = :estado WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id'     => (int)$id
        ]);
    }

    /**
     * Cuenta el total de reservas registradas.
     * 
     * @return int
     */
    public function contarTotal()
    {
        $sql = "SELECT COUNT(*) FROM reservas";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    /**
     * Obtiene el ingreso total recaudado por reservas confirmadas.
     * 
     * @return float
     */
    public function totalRecaudado()
    {
        $sql = "SELECT COALESCE(SUM(total), 0) FROM reservas WHERE estado = 'confirmada'";
        return (float)$this->db->query($sql)->fetchColumn();
    }
}
