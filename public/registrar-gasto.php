<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Vehiculo.php';
require_once __DIR__ . '/../app/models/SesionTrabajo.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$vehiculoInfo = $_SESSION['vehiculo_info'] ?? 'Sin vehículo asignado';
$esAdmin = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2;

// No se requiere sesión activa para registrar gastos
$sesionModel = new SesionTrabajo();
$sesionActiva = null;
$mensajeError = null;

// Los gastos pueden registrarse en cualquier momento
if (!$esAdmin) {
    // Obtener sesión si existe (para mostrar info), pero NO es obligatoria
    $sesionActiva = $sesionModel->obtenerSesionActiva($_SESSION['usuario_id']);
} else {
    // Para administradores, crear una "sesión virtual" para mostrar info
    $sesionActiva = [
        'fecha_inicio' => date('Y-m-d H:i:s'),
        'placa' => 'N/A',
        'marca' => 'Administrador',
        'modelo' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Gasto - Sistema de Control Vehicular</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <style>
        .expense-types-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        
        .expense-type-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .expense-type-card:hover {
            border-color: #f59e0b;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
            transform: translateY(-2px);
        }
        
        .expense-type-card.selected {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        
        .expense-type-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .expense-type-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 16px;
        }
        
        .amount-preview {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .amount-preview-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .amount-preview-value {
            font-size: 36px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <!-- Botón menú hamburguesa para móvil -->
    <button class="menu-toggle" id="menuToggle">☰</button>
    
    <!-- Overlay para cerrar sidebar en móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Mensaje flotante -->
    <div id="mensaje" class="mensaje"></div>
    
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
                <small><?= htmlspecialchars($vehiculoInfo) ?></small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/public/dashboard.php" class="nav-link">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            
            <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2): ?>
                <!-- Menú Administrador -->
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
                    <span class="nav-text">Servicios</span>
                </a>
                <a href="<?= APP_URL ?>/public/admin/reportes.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Reportes</span>
                </a>
                <a href="<?= APP_URL ?>/public/admin/incidencias.php" class="nav-link">
                    <span class="nav-icon">⚠️</span>
                    <span class="nav-text">Incidencias/PQRs</span>
                </a>
            <?php else: ?>
                <!-- Menú Conductor -->
                <a href="<?= APP_URL ?>/public/registrar-servicio.php" class="nav-link">
                    <span class="nav-icon">📝</span>
                    <span class="nav-text">Registrar Servicio</span>
                </a>
                <a href="<?= APP_URL ?>/public/registrar-gasto.php" class="nav-link active">
                    <span class="nav-icon">💰</span>
                    <span class="nav-text">Registrar Gasto</span>
                </a>
                <a href="<?= APP_URL ?>/public/historial.php" class="nav-link">
                    <span class="nav-icon">📋</span>
                    <span class="nav-text">Historial Servicios</span>
                </a>
                <a href="<?= APP_URL ?>/public/historial-gastos.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Historial Gastos</span>
                </a>
                <a href="<?= APP_URL ?>/public/incidencias.php" class="nav-link">
                    <span class="nav-icon">⚠️</span>
                    <span class="nav-text">Incidencias/PQRs</span>
                </a>
            <?php endif; ?>
            
            <a href="<?= APP_URL ?>/public/index.php?action=logout" class="nav-link nav-logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Cerrar Sesión</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <small>© 2025 ServiciosDrive</small>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1>💰 Registrar Gasto del Vehículo</h1>
            <p>Registra tanqueos, reparaciones, mantenimiento y otros gastos</p>
        </div>
        
        <?php if (isset($mensajeError)): ?>
            <div class="alert alert-warning" style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; border-radius: 12px; margin-bottom: 25px;">
                ⚠️ <?= htmlspecialchars($mensajeError) ?>
            </div>
        <?php else: ?>
            
        <!-- Información del vehículo -->
        <?php if ($sesionActiva): ?>
        <div class="info-card" style="display: flex; gap: 30px; align-items: center; margin-bottom: 25px; padding: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 32px;">🚗</span>
                <div>
                    <small style="color: #666; font-size: 12px; display: block;">Vehículo</small>
                    <strong style="font-size: 16px; color: #333;"><?= htmlspecialchars($vehiculoInfo) ?></strong>
                </div>
            </div>
            <?php if (isset($sesionActiva['fecha_inicio'])): ?>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 32px;">🕐</span>
                <div>
                    <small style="color: #666; font-size: 12px; display: block;">Sesión iniciada</small>
                    <strong style="font-size: 16px; color: #333;"><?= date('d/m/Y H:i', strtotime($sesionActiva['fecha_inicio'])) ?></strong>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Formulario de Registro de Gasto -->
        <div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; border: none;">
                <h2 style="color: white; margin: 0; font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">💳</span> Información del Gasto
                </h2>
                <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 14px;">Complete los datos del gasto realizado</p>
            </div>
            <div class="card-body" style="padding: 40px;">
                <?php if (isset($_SESSION['error_mensaje'])): ?>
                    <div class="alert alert-error" style="background: #fee; border-left: 4px solid #ef4444; padding: 16px; border-radius: 8px; margin-bottom: 25px;">
                        ⚠️ <?= htmlspecialchars($_SESSION['error_mensaje']) ?>
                    </div>
                    <?php unset($_SESSION['error_mensaje']); ?>
                <?php endif; ?>
                
                <form id="formRegistrarGasto" method="POST" action="<?= APP_URL ?>/public/api/gasto.php?action=crear">
                    <!-- Vista previa del monto -->
                    <div class="amount-preview" id="amountPreview" style="display: none;">
                        <div class="amount-preview-label">Monto Total del Gasto</div>
                        <div class="amount-preview-value" id="amountPreviewValue">$0.00</div>
                    </div>
                    
                    <!-- Tipo de Gasto - Cards visuales -->
                    <div class="form-group" style="margin-bottom: 32px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 16px; font-size: 15px;">
                            <span style="color: #f59e0b;">🏷️</span> Tipo de Gasto <span style="color: #ef4444;">*</span>
                        </label>
                        
                        <div class="expense-types-grid">
                            <div class="expense-type-card" data-type="tanqueo">
                                <div class="expense-type-icon">⛽</div>
                                <div class="expense-type-name">Tanqueo</div>
                            </div>
                            <div class="expense-type-card" data-type="arreglo">
                                <div class="expense-type-icon">🔧</div>
                                <div class="expense-type-name">Reparación</div>
                            </div>
                            <div class="expense-type-card" data-type="neumatico">
                                <div class="expense-type-icon">🛞</div>
                                <div class="expense-type-name">Neumáticos</div>
                            </div>
                            <div class="expense-type-card" data-type="mantenimiento">
                                <div class="expense-type-icon">🔩</div>
                                <div class="expense-type-name">Mantenimiento</div>
                            </div>
                            <div class="expense-type-card" data-type="compra">
                                <div class="expense-type-icon">🛒</div>
                                <div class="expense-type-name">Compra</div>
                            </div>
                            <div class="expense-type-card" data-type="otro">
                                <div class="expense-type-icon">📦</div>
                                <div class="expense-type-name">Otro</div>
                            </div>
                        </div>
                        
                        <input type="hidden" id="tipoGasto" name="tipo_gasto" required>
<!-- Grid de campos -->
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 28px;">
                        <!-- Monto -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="monto" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #10b981;">💵</span> Monto <span style="color: #ef4444;">*</span>
                            </label>
                            <input 
                                type="number" 
                                id="monto" 
                                name="monto" 
                                step="0.01" 
                                min="0.01"
                                placeholder="Ejemplo: 50000"
                                required
                                style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;"
                            >
                        </div>
                        
                        <!-- Kilometraje Actual -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="kilometrajeActual" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #3b82f6;">🛣️</span> Kilometraje Actual
                            </label>
                            <input 
                                type="number" 
                                id="kilometrajeActual" 
                                name="kilometraje_actual" 
                                min="0"
                                placeholder="Ejemplo: 45000"
                                style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;"
                            >
                            <small style="display: block; margin-top: 8px; color: #64748b; font-size: 13px;">Opcional: Kilometraje del vehículo</small>
                        </div>
                    </div>
                    
                    <!-- Fecha del Gasto -->
                    <div class="form-group" style="margin-bottom: 28px;">
                        <label for="fechaGasto" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                            <span style="color: #8b5cf6;">📅</span> Fecha del Gasto
                        </label>
                        <input 
                            type="datetime-local" 
                            id="fechaGasto" 
                            name="fecha_gasto"
                            style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;"
                        >
                        <small style="display: block; margin-top: 8px; color: #64748b; font-size: 13px;">Se establece automáticamente la fecha y hora actual. Puedes modificarla si es necesario</small>
                    </div>
                    
                    <!-- Descripción -->
                    <div class="form-group" style="margin-bottom: 28px;">
                        <label for="descripcion" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                            <span style="color: #f59e0b;">📝</span> Descripción del Gasto <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea 
                            id="descripcion" 
                            name="descripcion" 
                            rows="3" 
                            placeholder="Ejemplo: Tanqueada completa, 40 litros de gasolina extra"
                            required
                            style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s; resize: vertical; font-family: inherit;"
                        ></textarea>
                    </div>
                    
                    <!-- Notas -->
                    <div class="form-group" style="margin-bottom: 32px;">
                        <label for="notas" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                            <span style="color: #6366f1;">💬</span> Notas Adicionales
                        </label>
                        <textarea 
                            id="notas" 
                            name="notas" 
                            rows="2" 
                            placeholder="Observaciones adicionales (opcional)"
                            style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s; resize: vertical; font-family: inherit;"
                        ></textarea>
                    </div>
                    
                    <!-- Imagen del Comprobante -->
                    <div class="form-group" style="margin-bottom: 32px;">
                        <label for="imagenComprobante" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                            <span style="color: #10b981;">📷</span> Imagen del Comprobante (Opcional)
                        </label>
                        <div style="border: 2px dashed #e2e8f0; border-radius: 12px; padding: 30px; text-align: center; background: #f8fafc; transition: all 0.3s;" id="dropZone">
                            <input 
                                type="file" 
                                id="imagenComprobante" 
                                name="imagen_comprobante" 
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                style="display: none;"
                            >
                            <div id="filePreview" style="display: none;">
                                <img id="previewImage" src="" alt="Vista previa" style="max-width: 300px; max-height: 300px; border-radius: 8px; margin-bottom: 15px;">
                                <p style="color: #10b981; font-weight: 600; margin: 10px 0;" id="fileName"></p>
                                <button type="button" onclick="cambiarImagen()" style="background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; margin-top: 10px;">
                                    🔄 Cambiar imagen
                                </button>
                            </div>
                            <div id="uploadPrompt">
                                <div style="font-size: 48px; margin-bottom: 15px;">📸</div>
                                <p style="color: #64748b; font-size: 15px; margin-bottom: 12px;">
                                    <strong>Toca aquí para subir la foto del comprobante</strong>
                                </p>
                                <p style="color: #94a3b8; font-size: 13px;">
                                    Formatos permitidos: JPG, PNG, WEBP (máximo 5MB)
                                </p>
                                <button type="button" onclick="document.getElementById('imagenComprobante').click()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 14px 28px; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 15px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                                    📂 Seleccionar Imagen
                                </button>
                            </div>
                        </div>
                        <small style="display: block; margin-top: 8px; color: #64748b; font-size: 13px;">
                            Sube una foto del ticket o comprobante del gasto (opcional)
                        </small>
                    </div>
                    
                    <!-- Botones -->
                    <div class="form-actions" style="display: flex; gap: 16px; padding-top: 24px; border-top: 2px solid #f1f5f9;">
                        <button type="submit" id="btnGuardar" class="btn btn-primary" style="flex: 1; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 18px 32px; border: none; border-radius: 12px; font-size: 17px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 16px rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span style="font-size: 20px;">💾</span> Registrar Gasto
                        </button>
                        <a href="historial-gastos.php" style="flex: 0.4; background: #f1f5f9; color: #64748b; padding: 18px 32px; border: none; border-radius: 12px; font-size: 17px; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span style="font-size: 18px;">📋</span> Ver Historial
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Ayuda rápida -->
        <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 16px; padding: 30px; margin-top: 30px; color: white; box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);">
            <h3 style="margin: 0 0 20px 0; font-size: 20px; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">💡</span> Tipos de gastos que puedes registrar
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">⛽ Tanqueo</strong>
                    <small style="opacity: 0.9;">Recargas de combustible</small>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">🔧 Reparación</strong>
                    <small style="opacity: 0.9;">Arreglos mecánicos, eléctricos</small>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">🛞 Neumáticos</strong>
                    <small style="opacity: 0.9;">Espichadas, cambios</small>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">🔩 Mantenimiento</strong>
                    <small style="opacity: 0.9;">Aceite, filtros, revisiones</small>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">🛒 Compras</strong>
                    <small style="opacity: 0.9;">Accesorios, repuestos</small>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <strong style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">📦 Otro</strong>
                    <small style="opacity: 0.9;">Cualquier otro gasto</small>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </main>
    
    <script>
        // Definir APP_URL globalmente
        const APP_URL = '<?= APP_URL ?>';
    </script>
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script src="<?= APP_URL ?>/public/js/gasto.js"></script>
    <script>
        // Toggle sidebar en móvil
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Establecer fecha y hora actual automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            const fechaGastoInput = document.getElementById('fechaGasto');
            if (fechaGastoInput) {
                const ahora = new Date();
                // Ajustar a la zona horaria local
                ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
                fechaGastoInput.value = ahora.toISOString().slice(0, 16);
            }
        });
        
        // Selección visual de tipo de gasto
        document.querySelectorAll('.expense-type-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remover selección de todos
                document.querySelectorAll('.expense-type-card').forEach(c => c.classList.remove('selected'));
                
                // Seleccionar este
                this.classList.add('selected');
                
                // Actualizar valor del input oculto
                const tipo = this.dataset.type;
                document.getElementById('tipoGasto').value = tipo;
            });
        });
        
        // Vista previa del monto
        const montoInput = document.getElementById('monto');
        const amountPreview = document.getElementById('amountPreview');
        const amountPreviewValue = document.getElementById('amountPreviewValue');
        
        montoInput.addEventListener('input', function() {
            const valor = parseFloat(this.value);
            if (!isNaN(valor) && valor > 0) {
                amountPreviewValue.textContent = new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(valor);
                amountPreview.style.display = 'block';
            } else {
                amountPreview.style.display = 'none';
            }
        });
        
        // Validación del formulario
        document.getElementById('formRegistrarGasto').addEventListener('submit', function(e) {
            const tipoGasto = document.getElementById('tipoGasto').value;
            if (!tipoGasto) {
                e.preventDefault();
                mostrarMensaje('Por favor selecciona un tipo de gasto', 'error');
                return false;
            }
        });
        
        // Animaciones al hacer hover en inputs
        document.querySelectorAll('input, textarea, select').forEach(el => {
            el.addEventListener('focus', function() {
                this.style.borderColor = '#f59e0b';
                this.style.boxShadow = '0 0 0 3px rgba(245, 158, 11, 0.1)';
            });
            
            el.addEventListener('blur', function() {
                this.style.borderColor = '#e2e8f0';
                this.style.boxShadow = 'none';
            });
        });
        
        // Manejo de la imagen del comprobante
        const inputImagen = document.getElementById('imagenComprobante');
        const dropZone = document.getElementById('dropZone');
        const filePreview = document.getElementById('filePreview');
        const uploadPrompt = document.getElementById('uploadPrompt');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        
        inputImagen.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                mostrarVistaPrevia(file);
            }
        });
        
        // Drag and drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#10b981';
            this.style.background = '#f0fdf4';
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#e2e8f0';
            this.style.background = '#f8fafc';
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#e2e8f0';
            this.style.background = '#f8fafc';
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                inputImagen.files = e.dataTransfer.files;
                mostrarVistaPrevia(file);
            } else {
                mostrarMensaje('Por favor sube una imagen válida', 'error');
            }
        });
        
        // Click en la zona de drop
        dropZone.addEventListener('click', function(e) {
            if (e.target === dropZone || e.target.closest('#uploadPrompt')) {
                inputImagen.click();
            }
        });
        
        function mostrarVistaPrevia(file) {
            // Validar tamaño
            if (file.size > 5 * 1024 * 1024) {
                mostrarMensaje('La imagen es demasiado grande. Máximo 5MB', 'error');
                return;
            }
            
            // Validar tipo
            const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!tiposPermitidos.includes(file.type)) {
                mostrarMensaje('Tipo de archivo no permitido. Solo JPG, PNG y WEBP', 'error');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                fileName.textContent = file.name;
                uploadPrompt.style.display = 'none';
                filePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        
        function cambiarImagen() {
            inputImagen.value = '';
            uploadPrompt.style.display = 'block';
            filePreview.style.display = 'none';
        }
    </script>
</body>
</html>
