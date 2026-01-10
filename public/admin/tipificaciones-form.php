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

require_once __DIR__ . '/../../app/models/TipificacionSesion.php';
$tipificacionModel = new TipificacionSesion();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipificacion = null;
$esEdicion = false;

if ($id > 0) {
    $tipificacion = $tipificacionModel->obtenerPorId($id);
    $esEdicion = true;
}

// Procesar formulario
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $color = trim($_POST['color']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validar campos requeridos
    if (empty($nombre)) {
        $error = 'El campo Nombre es obligatorio';
    } else {
        if ($esEdicion) {
            // Actualizar
            $resultado = $tipificacionModel->actualizar($id, $nombre, $descripcion, $color, $activo);
            if ($resultado['success']) {
                $mensaje = $resultado['message'];
                $tipificacion = $tipificacionModel->obtenerPorId($id);
            } else {
                $error = $resultado['message'];
            }
        } else {
            // Crear
            $resultado = $tipificacionModel->crear($nombre, $descripcion, $color, $activo);
            if ($resultado['success']) {
                $mensaje = 'Tipificación creada correctamente';
                // Limpiar formulario
                $nombre = '';
                $descripcion = '';
                $color = '#6c757d';
            } else {
                $error = $resultado['message'];
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
    <title><?= $esEdicion ? 'Editar' : 'Nueva' ?> Tipificación - Admin</title>
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
            <a href="<?= APP_URL ?>/public/admin/usuarios.php" class="nav-link">
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
            <a href="<?= APP_URL ?>/public/admin/incidencias.php" class="nav-link">
                <span class="nav-icon">⚠️</span>
                <span class="nav-text">Incidencias/PQRs</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/tipificaciones.php" class="nav-link active">
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
            <div class="page-header">
                <h1><?= $esEdicion ? 'Editar Tipificación' : 'Nueva Tipificación' ?></h1>
                <p class="text-muted"><?= $esEdicion ? 'Actualiza la información de la tipificación en el sistema' : 'Registra una nueva tipificación en el sistema' ?></p>
            </div>
            <button class="btn btn-secondary" onclick="location.href='<?= APP_URL ?>/public/admin/tipificaciones.php'">
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
                ✗ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               class="form-control" 
                               value="<?= htmlspecialchars($tipificacion['nombre'] ?? '') ?>" 
                               required
                               maxlength="100"
                               placeholder="Ej: Viaje Completado">
                        <small class="form-hint">Nombre descriptivo de la tipificación</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="color">Color</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="color" 
                                   id="color" 
                                   name="color" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($tipificacion['color'] ?? '#6c757d') ?>"
                                   style="width: 80px; height: 40px; padding: 2px;">
                            <div id="colorPreview" style="flex: 1; height: 40px; border-radius: 4px; border: 1px solid #ddd; background-color: <?= htmlspecialchars($tipificacion['color'] ?? '#6c757d') ?>;"></div>
                        </div>
                        <small class="form-hint">Color para identificar visualmente la tipificación</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" 
                              name="descripcion" 
                              class="form-control" 
                              rows="3"
                              maxlength="255"
                              placeholder="Descripción opcional de la tipificación"><?= htmlspecialchars($tipificacion['descripcion'] ?? '') ?></textarea>
                    <small class="form-hint">Información adicional sobre la tipificación</small>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               name="activo" 
                               <?= ($esEdicion && !$tipificacion['activo']) ? '' : 'checked' ?>>
                        <span>Tipificación activa</span>
                    </label>
                    <small class="form-hint">Solo las tipificaciones activas estarán disponibles para seleccionar</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $esEdicion ? '💾 Actualizar' : '➕ Crear' ?> Tipificación
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='<?= APP_URL ?>/public/admin/tipificaciones.php'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
        
        <?php if ($esEdicion): ?>
        <div class="card">
            <h3>⚠️ Zona de Peligro</h3>
            <p class="text-muted">Las siguientes acciones son irreversibles</p>
            <button class="btn btn-danger" onclick="confirmarEliminar()">
                🗑️ Eliminar Tipificación
            </button>
        </div>
        <?php endif; ?>
    </main>
    
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script>
        // Actualizar preview del color
        const colorInput = document.getElementById('color');
        const colorPreview = document.getElementById('colorPreview');
        
        colorInput.addEventListener('input', function() {
            colorPreview.style.backgroundColor = this.value;
        });
        
        function confirmarEliminar() {
            if (confirm('¿Estás seguro de que deseas eliminar esta tipificación?\n\nSi hay sesiones usando esta tipificación, solo se desactivará.')) {
                fetch('<?= APP_URL ?>/public/api/tipificaciones.php?id=<?= $id ?>', {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Tipificación eliminada correctamente');
                        location.href = '<?= APP_URL ?>/public/admin/tipificaciones.php';
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo eliminar la tipificación'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la tipificación');
                });
            }
        }
    </script>
</body>
</html>
