<?php
require_once __DIR__ . '/../../config/Database.php';

class Usuario {
    private $db;
    private $table = 'usuarios';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Validar credenciales de usuario
     */
    public function login($usuario, $password) {
        try {
            $query = "SELECT id, usuario, password, nombre, apellido, email, activo 
                      FROM {$this->table} 
                      WHERE usuario = :usuario AND activo = 1 
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            
            if ($stmt->rowCount() === 1) {
                $row = $stmt->fetch();
                
                // Verificar password
                if (password_verify($password, $row['password'])) {
                    // Actualizar último acceso
                    $this->actualizarUltimoAcceso($row['id']);
                    
                    // Retornar datos del usuario sin el password
                    unset($row['password']);
                    return $row;
                }
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT id, usuario, nombre, apellido, email, telefono, activo, fecha_registro 
                      FROM {$this->table} 
                      WHERE id = :id 
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar último acceso del usuario
     */
    private function actualizarUltimoAcceso($id) {
        try {
            $query = "UPDATE {$this->table} 
                      SET ultimo_acceso = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar último acceso: " . $e->getMessage());
        }
    }
    
    /**
     * Crear nuevo usuario
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (usuario, password, nombre, apellido, email, telefono) 
                      VALUES (:usuario, :password, :nombre, :apellido, :email, :telefono)";
            
            $stmt = $this->db->prepare($query);
            
            // Hash del password
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            $stmt->bindParam(':usuario', $datos['usuario']);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':apellido', $datos['apellido']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }
}
