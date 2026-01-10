<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Servicio - Sistema de Control Vehicular</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/styles.css">
    <link rel="manifest" href="<?php echo APP_URL; ?>/manifest.json">
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
                <strong><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?></strong>
                <small><?php echo isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2 ? '🔑 Administrador' : htmlspecialchars($_SESSION['vehiculo_info'] ?? 'Sin vehículo'); ?></small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?php echo APP_URL; ?>/public/dashboard.php" class="nav-link">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            
            <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2): ?>
                <!-- Menú Administrador -->
                <a href="<?php echo APP_URL; ?>/public/admin/usuarios.php" class="nav-link">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Usuarios</span>
                </a>
                <a href="<?php echo APP_URL; ?>/public/admin/vehiculos.php" class="nav-link">
                    <span class="nav-icon">🚗</span>
                    <span class="nav-text">Vehículos</span>
                </a>
                <a href="<?php echo APP_URL; ?>/public/admin/servicios.php" class="nav-link">
                    <span class="nav-icon">📋</span>
                    <span class="nav-text">Servicios</span>
                </a>
                <a href="<?php echo APP_URL; ?>/public/admin/reportes.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Reportes</span>
                </a>                <a href="<?php echo APP_URL; ?>/public/admin/incidencias.php" class="nav-link">
                    <span class="nav-icon">⚠️</span>
                    <span class="nav-text">Incidencias/PQRs</span>
                </a>
                <a href="<?php echo APP_URL; ?>/public/admin/tipificaciones.php" class="nav-link">
                    <span class="nav-icon">🏷️</span>
                    <span class="nav-text">Tipificaciones</span>
                </a>            <?php else: ?>
                <!-- Menú Conductor -->
                <a href="<?php echo APP_URL; ?>/public/registrar-servicio.php" class="nav-link active">
                    <span class="nav-icon">📝</span>
                    <span class="nav-text">Registrar Servicio</span>
                </a>
                <a href="<?php echo APP_URL; ?>/public/registrar-gasto.php" class="nav-link">
                    <span class="nav-icon">💰</span>
                    <span class="nav-text">Registrar Gasto</span>
                </a>
                <a href="<?php echo APP_URL; ?>/public/historial.php" class="nav-link">
                    <span class="nav-icon">📋</span>
                    <span class="nav-text">Historial Servicios</span>
                </a>
                <a href="<?php echo APP_URL; ?>/public/historial-gastos.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Historial Gastos</span>
                </a>
                <a href="<?php echo APP_URL; ?>/public/incidencias.php" class="nav-link">
                    <span class="nav-icon">⚠️</span>
                    <span class="nav-text">Incidencias/PQRs</span>
                </a>
            <?php endif; ?>
            
            <a href="<?php echo APP_URL; ?>/public/index.php?action=logout" class="nav-link nav-logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Cerrar Sesión</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1>Registrar Nuevo Servicio</h1>
            <p>Ingresa los datos del servicio que vas a realizar</p>
        </div>

        <?php if (isset($sesionActiva) && $sesionActiva): ?>
            <div class="info-card" style="display: flex; gap: 30px; align-items: center; margin-bottom: 25px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 32px;">🚗</span>
                        <div>
                            <small style="color: #666; font-size: 12px; display: block;">Vehículo</small>
                            <strong style="font-size: 16px; color: #333;">
                                <?php 
                                    $vehiculoInfo = '';
                                    if (isset($sesionActiva['placa'])) {
                                        $vehiculoInfo = htmlspecialchars($sesionActiva['placa']);
                                        if (isset($sesionActiva['marca']) && isset($sesionActiva['modelo'])) {
                                            $vehiculoInfo .= ' - ' . htmlspecialchars($sesionActiva['marca']) . ' ' . htmlspecialchars($sesionActiva['modelo']);
                                        }
                                    } else {
                                        $vehiculoInfo = 'No disponible';
                                    }
                                    echo $vehiculoInfo;
                                ?>
                            </strong>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 32px;">🕐</span>
                        <div>
                            <small style="color: #666; font-size: 12px; display: block;">Sesión iniciada</small>
                            <strong style="font-size: 16px; color: #333;">
                                <?php echo isset($sesionActiva['fecha_inicio']) ? date('d/m/Y H:i', strtotime($sesionActiva['fecha_inicio'])) : 'N/A'; ?>
                            </strong>
                        </div>
                    </div>
                </div>

            <div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden;">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border: none;">
                    <h2 style="color: white; margin: 0; font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 28px;">📝</span> Información del Servicio
                    </h2>
                    <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 14px;">Complete los datos para iniciar el nuevo servicio</p>
                </div>
                <div class="card-body" style="padding: 40px;">
                    <form id="formRegistrarServicio" method="POST" action="<?php echo APP_URL; ?>/public/registrar-servicio.php?action=crear">
                        <div class="form-row" style="margin-bottom: 28px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="tipo_servicio" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                    <span style="color: #667eea;">🚖</span> Tipo de Servicio <span style="color: #ef4444;">*</span>
                                </label>
                                <select id="tipo_servicio" name="tipo_servicio" required style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px; background: #f8fafc; transition: all 0.3s; cursor: pointer; font-weight: 500;">
                                    <option value="">Seleccione el tipo de servicio...</option>
                                    <option value="Taxi">🚕 Taxi</option>
                                    <option value="Uber">🚗 Uber</option>
                                    <option value="Cabify">🚙 Cabify</option>
                                    <option value="Didi">🚘 Didi</option>
                                    <option value="Otro">📦 Otro</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="origen" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                    <span style="color: #10b981;">📍</span> Punto de Origen <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="text" id="origen" name="origen" required placeholder="Ingrese la dirección de origen" style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="destino" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                    <span style="color: #ef4444;">📍</span> Punto de Destino <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="text" id="destino" name="destino" required placeholder="Ingrese la dirección de destino" style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="fecha_servicio" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                    <span style="color: #3b82f6;">📅</span> Fecha/Hora del Servicio <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="datetime-local" id="fecha_servicio" name="fecha_servicio" required style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                                <small style="display: block; margin-top: 8px; color: #64748b; font-size: 13px;">Se establece automáticamente la fecha y hora actual</small>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="kilometros_recorridos" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                    <span style="color: #f59e0b;">🛣️</span> Kilómetros Recorridos <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" id="kilometros_recorridos" name="kilometros_recorridos" step="0.01" required placeholder="Ej: 15.75" style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                                <small style="display: block; margin-top: 8px; color: #64748b; font-size: 13px;">💡 Ingresa el kilometraje REAL recorrido (sin redondear)</small>
                            </div>
                        </div>

                        <div class="form-row" style="margin-bottom: 32px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="notas" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                    <span style="color: #8b5cf6;">📋</span> Notas Adicionales
                                </label>
                                <textarea id="notas" name="notas" rows="3" placeholder="Información adicional sobre el servicio..." style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s; resize: vertical; font-family: inherit;"></textarea>
                            </div>
                        </div>

                        <div class="form-actions" style="display: flex; gap: 16px; padding-top: 24px; border-top: 2px solid #f1f5f9;">
                            <button type="submit" class="btn btn-primary" style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 18px 32px; border: none; border-radius: 12px; font-size: 17px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3); display: flex; align-items: center; justify-content: center; gap: 10px;">
                                <span style="font-size: 20px;">✓</span> Registrar Servicio
                            </button>
                            <a href="<?php echo APP_URL; ?>/public/dashboard.php" style="flex: 0.4; background: #f1f5f9; color: #64748b; padding: 18px 32px; border: none; border-radius: 12px; font-size: 17px; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;">
                                <span style="font-size: 18px;">←</span> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>        
        <?php endif; ?>

        <!-- Formulario siempre visible -->
        <div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border: none;">
                <h2 style="color: white; margin: 0; font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">📝</span> Registrar Nuevo Servicio
                </h2>
                <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 14px;">Complete los datos del servicio realizado</p>
            </div>
            <div class="card-body" style="padding: 40px;">
                <form id="formRegistrarServicio" method="POST" action="<?php echo APP_URL; ?>/public/registrar-servicio.php?action=crear">
                    <div class="form-row" style="margin-bottom: 28px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="tipo_servicio" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #667eea;">🚖</span> Tipo de Servicio <span style="color: #ef4444;">*</span>
                            </label>
                            <select id="tipo_servicio" name="tipo_servicio" required style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px; background: #f8fafc; transition: all 0.3s; cursor: pointer; font-weight: 500;">
                                <option value="">Seleccione el tipo de servicio...</option>
                                <option value="Taxi">🚕 Taxi</option>
                                <option value="Uber">🚗 Uber</option>
                                <option value="Cabify">🚙 Cabify</option>
                                <option value="Didi">🚘 Didi</option>
                                <option value="Otro">📦 Otro</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="origen" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #10b981;">📍</span> Punto de Origen <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" id="origen" name="origen" required placeholder="Ingrese la dirección de origen" style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="destino" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #ef4444;">📍</span> Punto de Destino <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" id="destino" name="destino" required placeholder="Ingrese la dirección de destino" style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="fecha_servicio" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #3b82f6;">📅</span> Fecha/Hora del Servicio <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="datetime-local" id="fecha_servicio" name="fecha_servicio" required style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                            <small style="display: block; margin-top: 8px; color: #64748b; font-size: 13px;">Se establece automáticamente la fecha y hora actual</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="kilometros_recorridos" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #f59e0b;">🛣️</span> Kilómetros Recorridos <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="number" id="kilometros_recorridos" name="kilometros_recorridos" step="0.01" required placeholder="Ej: 15.75" style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                            <small style="display: block; margin-top: 8px; color: #64748b; font-size: 13px;">💡 Ingresa el kilometraje REAL recorrido (sin redondear)</small>
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 32px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="notas" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #8b5cf6;">📋</span> Notas Adicionales
                            </label>
                            <textarea id="notas" name="notas" rows="3" placeholder="Información adicional sobre el servicio..." style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s; resize: vertical; font-family: inherit;"></textarea>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; gap: 16px; padding-top: 24px; border-top: 2px solid #f1f5f9;">
                        <button type="submit" class="btn btn-primary" style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 18px 32px; border: none; border-radius: 12px; font-size: 17px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3); display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span style="font-size: 20px;">✓</span> Registrar Servicio
                        </button>
                        <a href="<?php echo APP_URL; ?>/public/dashboard.php" style="flex: 0.4; background: #f1f5f9; color: #64748b; padding: 18px 32px; border: none; border-radius: 12px; font-size: 17px; font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span style="font-size: 18px;">←</span> Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/servicio.js"></script>
    <script>
        // Toggle sidebar en móvil
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Establecer fecha y hora actual automáticamente
        document.addEventListener('DOMContentLoaded', function() {
            const fechaServicioInput = document.getElementById('fecha_servicio');
            if (fechaServicioInput) {
                const ahora = new Date();
                // Ajustar a la zona horaria local
                ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
                fechaServicioInput.value = ahora.toISOString().slice(0, 16);
            }
        });
    </script>
</body>
</html>
