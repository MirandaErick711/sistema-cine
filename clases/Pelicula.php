<?php
/**
 * Clase Pelicula
 * 
 * Modela las operaciones CRUD y de consulta para las peliculas del cine.
 * Permite listar peliculas activas para la cartelera publica y
 * administrar todo el catalogo desde el panel de control.
 */
require_once __DIR__ . '/../config/Conexion.php';

class Pelicula
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    /**
     * Obtiene todas las peliculas activas para la cartelera publica.
     * Soporta busqueda opcional por titulo o genero.
     * 
     * @param string $buscar
     * @return array
     */
    public function obtenerActivas($buscar = "")
    {
        if (!empty($buscar)) {
            $sql = "SELECT * FROM peliculas 
                    WHERE estado = 'activa' AND (titulo LIKE :buscar OR genero LIKE :buscar2) 
                    ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $termino = "%" . trim($buscar) . "%";
            $stmt->execute([':buscar' => $termino, ':buscar2' => $termino]);
        } else {
            $sql = "SELECT * FROM peliculas WHERE estado = 'activa' ORDER BY id DESC";
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Obtiene todas las peliculas registradas (activas e inactivas) para el panel de administracion.
     * 
     * @return array
     */
    public function obtenerTodas()
    {
        $sql = "SELECT * FROM peliculas ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los datos detallados de una pelicula a partir de su ID.
     * 
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM peliculas WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        $pelicula = $stmt->fetch();
        return $pelicula ?: null;
    }

    /**
     * Registra una nueva pelicula en la base de datos.
     * 
     * @param array $datos
     * @return int|false ID de la pelicula creada o false
     */
    public function crear($datos)
    {
        $sql = "INSERT INTO peliculas (titulo, sinopsis, genero, duracion, precio, imagen, trailer, estado) 
                VALUES (:titulo, :sinopsis, :genero, :duracion, :precio, :imagen, :trailer, :estado)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            ':titulo'   => trim($datos['titulo']),
            ':sinopsis' => trim($datos['sinopsis'] ?? ''),
            ':genero'   => trim($datos['genero']),
            ':duracion' => trim($datos['duracion']),
            ':precio'   => (float)$datos['precio'],
            ':imagen'   => trim($datos['imagen']),
            ':trailer'  => trim($datos['trailer'] ?? ''),
            ':estado'   => in_array($datos['estado'] ?? '', ['activa', 'inactiva']) ? $datos['estado'] : 'activa'
        ]);

        return $resultado ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Actualiza la informacion de una pelicula existente.
     * 
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function actualizar($id, $datos)
    {
        $sql = "UPDATE peliculas 
                SET titulo = :titulo, sinopsis = :sinopsis, genero = :genero, 
                    duracion = :duracion, precio = :precio, imagen = :imagen, 
                    trailer = :trailer, estado = :estado 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'       => (int)$id,
            ':titulo'   => trim($datos['titulo']),
            ':sinopsis' => trim($datos['sinopsis'] ?? ''),
            ':genero'   => trim($datos['genero']),
            ':duracion' => trim($datos['duracion']),
            ':precio'   => (float)$datos['precio'],
            ':imagen'   => trim($datos['imagen']),
            ':trailer'  => trim($datos['trailer'] ?? ''),
            ':estado'   => in_array($datos['estado'] ?? '', ['activa', 'inactiva']) ? $datos['estado'] : 'activa'
        ]);
    }

    /**
     * Cambia el estado de una pelicula (activa o inactiva).
     * Permite retirar o habilitar peliculas en cartelera sin borrar registros historicos.
     * 
     * @param int $id
     * @param string $nuevoEstado 'activa' o 'inactiva'
     * @return bool
     */
    public function cambiarEstado($id, $nuevoEstado)
    {
        if (!in_array($nuevoEstado, ['activa', 'inactiva'])) {
            return false;
        }

        $sql = "UPDATE peliculas SET estado = :estado WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id'     => (int)$id
        ]);
    }

    /**
     * Elimina una pelicula de la base de datos si no tiene reservas o dependencias.
     * Si tiene funciones o reservas asociadas, se recomienda marcarla como 'inactiva'
     * para preservar la integridad de datos.
     * 
     * @param int $id
     * @return bool
     */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM peliculas WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            // Si la clave foranea bloquea la eliminacion (tiene funciones o reservas asociadas)
            // se desactiva en su lugar
            $this->cambiarEstado($id, 'inactiva');
            return false;
        }
    }

    /**
     * Cuenta el total de peliculas activas registradas.
     * 
     * @return int
     */
    public function contarActivas()
    {
        $sql = "SELECT COUNT(*) FROM peliculas WHERE estado = 'activa'";
        return (int)$this->db->query($sql)->fetchColumn();
    }
}
