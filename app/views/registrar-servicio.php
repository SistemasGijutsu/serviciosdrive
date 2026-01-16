<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Servicio - Sistema de Control Vehicular</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/styles.css">
    <link rel="manifest" href="<?php echo APP_URL; ?>/public/manifest.json">
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
        
        <?php if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2): ?>
        <!-- Contenedor para gestión de turnos (en sidebar para conductores) -->
        <div id="turnoContainer" class="turno-container-sidebar"></div>
        <?php endif; ?>
        
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
                    <form id="formRegistrarServicio" method="POST" action="registrar-servicio.php?action=crear">
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
                <form id="formRegistrarServicio" method="POST" action="registrar-servicio.php?action=crear">
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

                    <!-- Campo de ciudad -->
                    <div class="form-row" style="margin-bottom: 28px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="ciudad" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #3b82f6;">🏙️</span> Ciudad <span style="color: #ef4444;">*</span>
                            </label>
                            <select id="ciudad" name="ciudad" required style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px; background: #f8fafc; transition: all 0.3s; cursor: pointer; font-weight: 500;">
                                <option value="">Seleccione la ciudad...</option>
                                <option value="Medellín, Antioquia, Colombia" selected>Medellín</option>
                                <option value="Bogotá, Colombia">Bogotá</option>
                                <option value="Cali, Valle del Cauca, Colombia">Cali</option>
                                <option value="Barranquilla, Atlántico, Colombia">Barranquilla</option>
                                <option value="Cartagena, Bolívar, Colombia">Cartagena</option>
                                <option value="Bucaramanga, Santander, Colombia">Bucaramanga</option>
                                <option value="Pereira, Risaralda, Colombia">Pereira</option>
                                <option value="Manizales, Caldas, Colombia">Manizales</option>
                                <option value="Santa Marta, Magdalena, Colombia">Santa Marta</option>
                            </select>
                            <small style="display: block; margin-top: 8px; color: #64748b; font-size: 13px;">💡 Selecciona la ciudad donde se realizó el servicio</small>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 20px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="origen" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #10b981;">📍</span> Dirección de Origen <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" id="origen" name="origen" required placeholder="Ej: Cra 58 # 73-05" style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                            <div style="display: flex; gap: 8px; margin-top: 8px;">
                                <button type="button" onclick="obtenerUbicacionActual('origen')" style="background: #f1f5f9; color: #475569; padding: 8px 14px; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 6px;">
                                    <span>📍</span> Usar mi ubicación
                                </button>
                                <button type="button" onclick="limpiarCampo('origen')" style="background: #fef2f2; color: #dc2626; padding: 8px 14px; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 6px;">
                                    <span>🗑️</span> Limpiar
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="destino" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                                <span style="color: #ef4444;">📍</span> Dirección de Destino <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" id="destino" name="destino" required placeholder="Ej: Calle 10 # 20-30" style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;">
                            <div style="display: flex; gap: 8px; margin-top: 8px;">
                                <button type="button" onclick="obtenerUbicacionActual('destino')" style="background: #f1f5f9; color: #475569; padding: 8px 14px; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 6px;">
                                    <span>📍</span> Usar mi ubicación
                                </button>
                                <button type="button" onclick="limpiarCampo('destino')" style="background: #fef2f2; color: #dc2626; padding: 8px 14px; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 6px;">
                                    <span>🗑️</span> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Campos ocultos para coordenadas -->
                    <input type="hidden" id="origen_coords" name="origen_coords">
                    <input type="hidden" id="destino_coords" name="destino_coords">

                    <!-- Botón para calcular distancia automática -->
                    <div style="margin-bottom: 20px; text-align: center;">
                        <button type="button" id="btnCalcularDistancia" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 28px; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); display: inline-flex; align-items: center; gap: 8px;">
                            <span style="font-size: 18px;">🗺️</span> Calcular Distancia Automáticamente
                        </button>
                    </div>

                    <!-- Resultado del cálculo -->
                    <div id="resultadoDistancia" style="display: none; margin-bottom: 20px; padding: 16px; background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%); border-radius: 12px; border-left: 4px solid #3b82f6;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <span style="font-size: 24px;">📊</span>
                            <strong style="color: #1e40af; font-size: 16px;">Distancia Calculada:</strong>
                        </div>
                        <div id="infoDistancia" style="color: #1e3a8a; font-size: 15px; line-height: 1.6;"></div>
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

    <script src="<?php echo APP_URL; ?>/public/js/config.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/offline-manager.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/turnos.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/distancematrix-util.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/servicio.js"></script>
    <script>
        // Pasar información del rol al JavaScript
        document.body.dataset.esAdmin = '<?php echo (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2) ? "true" : "false"; ?>';
        
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

            // ========== GEOLOCALIZACIÓN ==========
            window.obtenerUbicacionActual = function(campo) {
                if (!navigator.geolocation) {
                    mostrarMensaje('❌ Tu navegador no soporta geolocalización', 'error');
                    return;
                }

                mostrarMensaje('📍 Obteniendo tu ubicación...', 'info');

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const coords = `${lat},${lng}`;
                        
                        console.log('📍 Ubicación obtenida:', coords);
                        
                        // Guardar coordenadas en campo oculto
                        document.getElementById(campo + '_coords').value = coords;
                        
                        // Mostrar coordenadas en el input de forma amigable
                        const inputField = document.getElementById(campo);
                        inputField.value = `📍 Mi ubicación actual`;
                        inputField.dataset.coords = coords; // Guardar coords en data attribute
                        inputField.style.background = '#dcfce7';
                        inputField.style.borderColor = '#10b981';
                        inputField.readOnly = true; // Evitar que editen
                        
                        mostrarMensaje('✅ Ubicación obtenida correctamente', 'success');
                        
                        setTimeout(() => {
                            inputField.style.background = '#dcfce7';
                            inputField.style.borderColor = '#10b981';
                        }, 2000);
                    },
                    function(error) {
                        console.error('Error de geolocalización:', error);
                        mostrarMensaje('❌ No se pudo obtener la ubicación: ' + error.message, 'error');
                    }
                );
            };

            // Limpiar campo y permitir escribir dirección manual
            window.limpiarCampo = function(campo) {
                const inputField = document.getElementById(campo);
                const coordsField = document.getElementById(campo + '_coords');
                
                inputField.value = '';
                inputField.readOnly = false;
                inputField.style.background = '#f8fafc';
                inputField.style.borderColor = '#e2e8f0';
                inputField.dataset.coords = '';
                coordsField.value = '';
                
                inputField.focus();
                mostrarMensaje('✅ Campo limpiado. Puedes escribir una dirección', 'success');
            };

            // ========== CÁLCULO AUTOMÁTICO DE DISTANCIA ==========
            const btnCalcular = document.getElementById('btnCalcularDistancia');
            const inputOrigen = document.getElementById('origen');
            const inputDestino = document.getElementById('destino');
            const inputCiudad = document.getElementById('ciudad');
            const inputKm = document.getElementById('kilometros_recorridos');
            const resultadoDiv = document.getElementById('resultadoDistancia');
            const infoDiv = document.getElementById('infoDistancia');

            if (btnCalcular) {
                btnCalcular.addEventListener('click', async function() {
                    let origen = inputOrigen.value.trim();
                    let destino = inputDestino.value.trim();
                    const ciudad = inputCiudad.value.trim();

                    // Validar campos básicos
                    if (!origen || !destino) {
                        mostrarMensaje('⚠️ Por favor ingresa origen y destino', 'warning');
                        return;
                    }

                    // Obtener coordenadas si existen
                    const origenCoords = document.getElementById('origen_coords').value.trim();
                    const destinoCoords = document.getElementById('destino_coords').value.trim();

                    // Preparar origen
                    if (origenCoords) {
                        // Usar coordenadas GPS
                        origen = origenCoords;
                        console.log('🎯 Usando coordenadas GPS para origen:', origen);
                    } else {
                        // Usar dirección + ciudad
                        if (!ciudad) {
                            mostrarMensaje('⚠️ Por favor selecciona la ciudad', 'warning');
                            return;
                        }
                        // Limpiar emoji si existe
                        origen = origen.replace(/📍/g, '').trim();
                        if (origen.toLowerCase() !== 'mi ubicación actual') {
                            origen = `${origen}, ${ciudad}`;
                        } else {
                            mostrarMensaje('⚠️ Error con la ubicación. Intenta capturarla de nuevo.', 'error');
                            return;
                        }
                        console.log('📍 Usando dirección para origen:', origen);
                    }

                    // Preparar destino
                    if (destinoCoords) {
                        // Usar coordenadas GPS
                        destino = destinoCoords;
                        console.log('🎯 Usando coordenadas GPS para destino:', destino);
                    } else {
                        // Usar dirección + ciudad
                        if (!ciudad) {
                            mostrarMensaje('⚠️ Por favor selecciona la ciudad', 'warning');
                            return;
                        }
                        // Limpiar emoji si existe
                        destino = destino.replace(/📍/g, '').trim();
                        if (destino.toLowerCase() !== 'mi ubicación actual') {
                            destino = `${destino}, ${ciudad}`;
                        } else {
                            mostrarMensaje('⚠️ Error con la ubicación. Intenta capturarla de nuevo.', 'error');
                            return;
                        }
                        console.log('📍 Usando dirección para destino:', destino);
                    }

                    // Cambiar botón a estado de carga
                    btnCalcular.disabled = true;
                    btnCalcular.innerHTML = '<span style="font-size: 18px;">⏳</span> Calculando distancia...';

                    try {
                        console.log('🌐 Calculando distancia entre:', { origen, destino });
                        
                        // Llamar a la API de Distance Matrix
                        const resultado = await DistanceMatrixUtil.calcularDistanciaDirecciones(origen, destino);

                        if (resultado.success) {
                            console.log('✅ Resultado:', resultado);
                            
                            // Mostrar resultado
                            resultadoDiv.style.display = 'block';
                            infoDiv.innerHTML = `
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                    <div>
                                        <strong>📏 Distancia:</strong> ${resultado.distancia.texto}
                                    </div>
                                    <div>
                                        <strong>⏱️ Tiempo estimado:</strong> ${resultado.duracion.texto}
                                    </div>
                                </div>
                                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #93c5fd;">
                                    <strong>🎯 Kilómetros:</strong> ${resultado.distancia.kilometros} km
                                </div>
                            `;

                            // ✅ AUTOCOMPLETAR EL CAMPO DE KILÓMETROS
                            inputKm.value = resultado.distancia.kilometros;
                            inputKm.style.background = '#dcfce7';
                            inputKm.style.borderColor = '#10b981';
                            inputKm.style.fontWeight = '600';

                            mostrarMensaje('✅ Distancia calculada: ' + resultado.distancia.kilometros + ' km', 'success');

                            // Hacer scroll al campo de kilómetros
                            inputKm.scrollIntoView({ behavior: 'smooth', block: 'center' });

                            // Restaurar estilo después de 3 segundos
                            setTimeout(() => {
                                inputKm.style.background = '#f8fafc';
                                inputKm.style.borderColor = '#e2e8f0';
                                inputKm.style.fontWeight = 'normal';
                            }, 3000);
                        }
                    } catch (error) {
                        console.error('❌ Error al calcular distancia:', error);
                        mostrarMensaje('❌ ' + error.message, 'error');
                        resultadoDiv.style.display = 'none';
                    } finally {
                        // Restaurar botón
                        btnCalcular.disabled = false;
                        btnCalcular.innerHTML = '<span style="font-size: 18px;">🗺️</span> Calcular Distancia Automáticamente';
                    }
                });
            }
        });
    </script>
</body>
</html>
