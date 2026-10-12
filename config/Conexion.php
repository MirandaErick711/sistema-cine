<?php
/**
 * Clase Conexion
 * 
 * Gestiona la conexion a la base de datos MySQL/MariaDB mediante PDO
 * Aplica el patron Singleton opcional o instanciacion directa con manejo de excepciones
 */
class Conexion
{
    private static $host = "localhost";
    private static $db   = "cine_db";
    private static $user = "root";
    private static $pass = "";
    private static $charset = "utf8mb4";
    
    // Instancia estatica de PDO para reutilizar la conexion en la misma peticion
    private static $pdo = null;

    /**
     * Obtiene una instancia activa de PDO
     * Si no existe, la crea con las opciones seguras de conexion
     * 
     * @return PDO
     * @throws Exception Si no se logra conectar a la base de datos
     */
    public static function conectar()
    {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones ante errores SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arreglos asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Consultas preparadas reales en el motor
            ];

            try {
                self::$pdo = new PDO($dsn, self::$user, self::$pass, $opciones);
            } catch (PDOException $e) {
                // Registro del error en el log del servidor sin mostrar datos sensibles al usuario
                error_log("Error de conexion a la base de datos: " . $e->getMessage());
                die("Error en el sistema: No se pudo establecer la conexion con la base de datos. Por favor, intente mas tarde.");
            }
        }

        return self::$pdo;
    }
}
