<?php
/**
 * Clase Usuario
 * 
 * Modela los datos de un usuario del sistema y gestiona las operaciones
 * de registro, autenticacion, validacion y consulta de usuarios.
 */
require_once __DIR__ . '/../config/Conexion.php';

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    /**
     * Registra un nuevo cliente en el sistema.
     * Siempre asigna por seguridad el rol 'cliente'.
     * 
     * @param string $nombre
     * @param string $correo
     * @param string $password En texto plano, sera encriptada aqui
     * @param string $telefono
     * @return bool|int ID del usuario insertado o false
     */
    public function registrar($nombre, $correo, $password, $telefono = null)
    {
        // Encriptar la contraseña con BCRYPT de manera segura
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $rol = 'cliente'; // Regla de negocio: autoregistro solo crea clientes

        $sql = "INSERT INTO usuarios (nombre, correo, password, telefono, rol, estado) 
                VALUES (:nombre, :correo, :password, :telefono, :rol, 'activo')";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            ':nombre'   => trim($nombre),
            ':correo'   => trim(strtolower($correo)),
            ':password' => $passwordHash,
            ':telefono' => $telefono ? trim($telefono) : null,
            ':rol'      => $rol
        ]);

        return $resultado ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Autentica a un usuario verificando su correo y contraseña encriptada.
     * 
     * @param string $correo
     * @param string $password
     * @return array|false Arreglo con datos del usuario si es valido, false si falla
     */
    public function autenticar($correo, $password)
    {
        $sql = "SELECT id, nombre, correo, password, rol, estado 
                FROM usuarios 
                WHERE correo = :correo AND estado = 'activo' 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => trim(strtolower($correo))]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Eliminar la contraseña del arreglo devuelto por seguridad
            unset($usuario['password']);
            return $usuario;
        }

        return false;
    }

    /**
     * Comprueba si un correo ya se encuentra registrado.
     * 
     * @param string $correo
     * @return bool
     */
    public function existeCorreo($correo)
    {
        $sql = "SELECT id FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => trim(strtolower($correo))]);
        return (bool)$stmt->fetch();
    }

    /**
     * Obtiene los datos de un usuario por su ID.
     * 
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT id, nombre, correo, telefono, rol, estado, creado_en 
                FROM usuarios 
                WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Permite crear o actualizar un usuario administrador.
     * Se utiliza en la configuracion inicial del sistema.
     */
    public function crearOActualizarAdmin($nombre, $correo, $password, $telefono = null)
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $correo = trim(strtolower($correo));

        if ($this->existeCorreo($correo)) {
            $sql = "UPDATE usuarios 
                    SET nombre = :nombre, password = :password, telefono = :telefono, rol = 'admin', estado = 'activo' 
                    WHERE correo = :correo";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre'   => trim($nombre),
                ':password' => $passwordHash,
                ':telefono' => $telefono,
                ':correo'   => $correo
            ]);
        } else {
            $sql = "INSERT INTO usuarios (nombre, correo, password, telefono, rol, estado) 
                    VALUES (:nombre, :correo, :password, :telefono, 'admin', 'activo')";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre'   => trim($nombre),
                ':correo'   => $correo,
                ':password' => $passwordHash,
                ':telefono' => $telefono
            ]);
        }
    }
}
