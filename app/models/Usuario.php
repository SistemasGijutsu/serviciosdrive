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
            $query = "SELECT id, usuario, password, nombre, apellido, email, rol_id, activo 
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
     * Obtener todos los usuarios
     */
    public function obtenerTodos() {
        try {
            $query = "SELECT u.id, u.usuario, u.nombre, u.apellido, u.email, u.telefono, 
                             u.rol_id, r.nombre as rol, u.activo, u.fecha_registro, u.ultimo_acceso
                      FROM {$this->table} u
                      LEFT JOIN roles r ON u.rol_id = r.id
                      ORDER BY u.fecha_registro DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear nuevo usuario
     */
    public function crear($datos) {
        try {
            // Preparar hash de contraseña
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            $rol_id = isset($datos['rol_id']) ? $datos['rol_id'] : 1;

            $query = "INSERT INTO {$this->table} 
                      (usuario, password, nombre, apellido, email, telefono, rol_id) 
                      VALUES (:usuario, :password, :nombre, :apellido, :email, :telefono, :rol_id)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario', $datos['usuario']);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':apellido', $datos['apellido']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':rol_id', $rol_id);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function actualizar($id, $datos) {
        try {
            $query = "UPDATE {$this->table} 
                      SET usuario = :usuario, nombre = :nombre, apellido = :apellido, 
                          email = :email, telefono = :telefono, rol_id = :rol_id, activo = :activo";
            
            // Si se proporciona password, actualizarlo
            if (!empty($datos['password'])) {
                $query .= ", password = :password";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':usuario', $datos['usuario']);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':apellido', $datos['apellido']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':rol_id', $datos['rol_id']);
            $stmt->bindParam(':activo', $datos['activo']);
            
            if (!empty($datos['password'])) {
                $stmt->bindParam(':password', $datos['password']);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar usuario (soft delete)
     */
    public function eliminar($id) {
        try {
            $query = "UPDATE {$this->table} SET activo = 0 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return false;
        }
    }
    
}
