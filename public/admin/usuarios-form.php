<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

// Verificar que sea administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit;
}

require_once __DIR__ . '/../../app/models/Usuario.php';
$usuarioModel = new Usuario();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$usuario = null;
$esEdicion = false;

if ($id > 0) {
    $usuario = $usuarioModel->obtenerPorId($id);
    $esEdicion = true;
}

// Procesar formulario
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'usuario' => trim($_POST['usuario']),
        'nombre' => trim($_POST['nombre']),
        'apellido' => trim($_POST['apellido']),
        'email' => trim($_POST['email']),
        'telefono' => trim($_POST['telefono']),
        'rol_id' => intval($_POST['rol_id']),
        'activo' => isset($_POST['activo']) ? 1 : 0
    ];
    
    // Validar campos requeridos
    if (empty($datos['usuario']) || empty($datos['nombre']) || empty($datos['apellido'])) {
        $error = 'Los campos Usuario, Nombre y Apellido son obligatorios';
    } else {
        if ($esEdicion) {
            // Actualizar
            if (!empty($_POST['password'])) {
                $datos['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            if ($usuarioModel->actualizar($id, $datos)) {
                $mensaje = 'Usuario actualizado correctamente';
                $usuario = $usuarioModel->obtenerPorId($id);
            } else {
                $error = 'Error al actualizar el usuario';
            }
        } else {
            // Crear
            if (empty($_POST['password'])) {
                $error = 'La contraseña es obligatoria para usuarios nuevos';
            } else {
                $datos['password'] = $_POST['password'];
                
                if ($usuarioModel->crear($datos)) {
                    $mensaje = 'Usuario creado correctamente';
                    // Limpiar formulario
                    $datos = [];
                } else {
                    $error = 'Error al crear el usuario';
                }
            }
        }
    }
}

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $esEdicion ? 'Editar' : 'Nuevo' ?> Usuario - Admin</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <meta name="theme-color" content="#2563eb">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>🚗 Control Vehicular</h2>
            <button class="sidebar-toggle" id="sidebarToggle">
                <span>☰</span>
            </button>
        </div>
        
        <div class="sidebar-user">
            <div class="user-avatar">👤</div>
            <div class="user-info">
                <strong><?= htmlspecialchars($nombreUsuario) ?></strong>
                <small>🔑 Administrador</small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/public/dashboard.php" class="nav-link">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/usuarios.php" class="nav-link active">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Usuarios</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/vehiculos.php" class="nav-link">
                <span class="nav-icon">🚗</span>
                <span class="nav-text">Vehículos</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/servicios.php" class="nav-link">
                <span class="nav-icon">📋</span>
                <span class="nav-text">Todos los Servicios</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/reportes.php" class="nav-link">
                <span class="nav-icon">📈</span>
                <span class="nav-text">Reportes</span>
            </a>
            <a href="<?= APP_URL ?>/public/index.php?action=logout" class="nav-link nav-link-logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Cerrar Sesión</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <small>© 2025 ServiciosDrive</small>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="dashboard-header">
            <div>
                <h1><?= $esEdicion ? '✏️ Editar' : '➕ Nuevo' ?> Usuario</h1>
                <p class="text-muted"><?= $esEdicion ? 'Modifica los datos del usuario' : 'Registra un nuevo usuario en el sistema' ?></p>
            </div>
            <button class="btn btn-secondary" onclick="location.href='<?= APP_URL ?>/public/admin/usuarios.php'">
                ← Volver
            </button>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success">
                ✓ <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                ⚠ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" class="form-grid">
                <div class="form-group">
                    <label for="usuario">Usuario *</label>
                    <input type="text" id="usuario" name="usuario" 
                           value="<?= $usuario ? htmlspecialchars($usuario['usuario']) : '' ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña <?= !$esEdicion ? '*' : '(dejar en blanco para no cambiar)' ?></label>
                    <input type="password" id="password" name="password" 
                           <?= !$esEdicion ? 'required' : '' ?>>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" 
                           value="<?= $usuario ? htmlspecialchars($usuario['nombre']) : '' ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="apellido">Apellido *</label>
                    <input type="text" id="apellido" name="apellido" 
                           value="<?= $usuario ? htmlspecialchars($usuario['apellido']) : '' ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= $usuario ? htmlspecialchars($usuario['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" 
                           value="<?= $usuario ? htmlspecialchars($usuario['telefono']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="rol_id">Rol *</label>
                    <select id="rol_id" name="rol_id" required>
                        <option value="1" <?= ($usuario && $usuario['rol_id'] == 1) || !$usuario ? 'selected' : '' ?>>Usuario</option>
                        <option value="2" <?= $usuario && $usuario['rol_id'] == 2 ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="activo" 
                               <?= !$usuario || $usuario['activo'] ? 'checked' : '' ?>>
                        Usuario Activo
                    </label>
                </div>
                
                <div class="form-actions" style="grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-primary">
                        <?= $esEdicion ? '💾 Guardar Cambios' : '➕ Crear Usuario' ?>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='<?= APP_URL ?>/public/admin/usuarios.php'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
