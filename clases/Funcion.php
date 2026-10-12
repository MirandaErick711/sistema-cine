<?php
/**
 * Clase Funcion
 * 
 * Modela las funciones/horarios de proyeccion de peliculas en el cine.
 * Gestiona horarios, fechas, salas y disponibilidad de asientos.
 */
require_once __DIR__ . '/../config/Conexion.php';

class Funcion
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    /**
     * Obtiene todas las funciones disponibles para una pelicula dada.
     * Incluye calculo de asientos ocupados y disponibles.
     * 
     * @param int $peliculaId
     * @return array
     */
    public function obtenerPorPelicula($peliculaId)
    {
        $sql = "SELECT f.*, 
                       (f.capacidad - COALESCE(COUNT(d.id), 0)) AS asientos_disponibles
                FROM funciones f
                LEFT JOIN detalle_reserva d ON f.id = d.funcion_id
                WHERE f.pelicula_id = :pelicula_id 
                  AND f.estado = 'disponible'
                GROUP BY f.id
                ORDER BY f.fecha ASC, f.hora ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pelicula_id' => (int)$peliculaId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene la informacion de una funcion por su ID, incluyendo datos de la pelicula.
     * 
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT f.*, p.titulo AS pelicula_titulo, p.precio AS pelicula_precio, 
                       p.imagen AS pelicula_imagen, p.genero AS pelicula_genero,
                       (f.capacidad - (SELECT COUNT(*) FROM detalle_reserva WHERE funcion_id = f.id)) AS asientos_disponibles
                FROM funciones f
                INNER JOIN peliculas p ON f.pelicula_id = p.id
                WHERE f.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        $funcion = $stmt->fetch();
        return $funcion ?: null;
    }

    /**
     * Obtiene la lista de asientos ya ocupados para una funcion especifica.
     * 
     * @param int $funcionId
     * @return array Lista de strings con los codigos de asiento, ej. ['A1', 'B2']
     */
    public function obtenerAsientosOcupados($funcionId)
    {
        $sql = "SELECT asiento FROM detalle_reserva WHERE funcion_id = :funcion_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':funcion_id' => (int)$funcionId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Obtiene todas las funciones con datos de pelicula (para el panel admin).
     * 
     * @return array
     */
    public function obtenerTodas()
    {
        $sql = "SELECT f.*, p.titulo AS pelicula_titulo 
                FROM funciones f
                INNER JOIN peliculas p ON f.pelicula_id = p.id
                ORDER BY f.fecha DESC, f.hora DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Registra una nueva funcion de proyeccion.
     * 
     * @param array $datos
     * @return int|false
     */
    public function crear($datos)
    {
        $sql = "INSERT INTO funciones (pelicula_id, sala, fecha, hora, capacidad, estado) 
                VALUES (:pelicula_id, :sala, :fecha, :hora, :capacidad, :estado)";
        $stmt = $this->db->prepare($sql);
        $resultado = $stmt->execute([
            ':pelicula_id' => (int)$datos['pelicula_id'],
            ':sala'        => trim($datos['sala']),
            ':fecha'       => trim($datos['fecha']),
            ':hora'        => trim($datos['hora']),
            ':capacidad'   => (int)($datos['capacidad'] ?? 30),
            ':estado'      => in_array($datos['estado'] ?? '', ['disponible', 'agotada', 'cancelada']) ? $datos['estado'] : 'disponible'
        ]);

        return $resultado ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Actualiza una funcion existente.
     * 
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function actualizar($id, $datos)
    {
        $sql = "UPDATE funciones 
                SET pelicula_id = :pelicula_id, sala = :sala, fecha = :fecha, 
                    hora = :hora, capacidad = :capacidad, estado = :estado 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'          => (int)$id,
            ':pelicula_id' => (int)$datos['pelicula_id'],
            ':sala'        => trim($datos['sala']),
            ':fecha'       => trim($datos['fecha']),
            ':hora'        => trim($datos['hora']),
            ':capacidad'   => (int)($datos['capacidad'] ?? 30),
            ':estado'      => in_array($datos['estado'] ?? '', ['disponible', 'agotada', 'cancelada']) ? $datos['estado'] : 'disponible'
        ]);
    }

    /**
     * Cambia el estado de una funcion (disponible, agotada, cancelada).
     * 
     * @param int $id
     * @param string $estado
     * @return bool
     */
    public function cambiarEstado($id, $estado)
    {
        if (!in_array($estado, ['disponible', 'agotada', 'cancelada'])) {
            return false;
        }

        $sql = "UPDATE funciones SET estado = :estado WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':estado' => $estado, ':id' => (int)$id]);
    }

    /**
     * Elimina una funcion si no tiene reservas vinculadas.
     * 
     * @param int $id
     * @return bool
     */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM funciones WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            // Si tiene reservas, se marca como cancelada para no romper relaciones
            $this->cambiarEstado($id, 'cancelada');
            return false;
        }
    }
}
