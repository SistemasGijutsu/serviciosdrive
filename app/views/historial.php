<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Servicios - Sistema de Control Vehicular</title>
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
                <small><?php echo htmlspecialchars($_SESSION['vehiculo_info'] ?? 'Sin vehículo'); ?></small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?php echo APP_URL; ?>/public/dashboard.php" class="nav-link">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>/public/registrar-servicio.php" class="nav-link">
                <span class="nav-icon">➕</span>
                <span class="nav-text">Nuevo Servicio</span>
            </a>
            <a href="<?php echo APP_URL; ?>/public/historial.php" class="nav-link active">
                <span class="nav-icon">📜</span>
                <span class="nav-text">Historial</span>
            </a>
            <a href="<?php echo APP_URL; ?>/public/index.php?action=logout" class="nav-link nav-logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Cerrar Sesión</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1>📜 Historial de Servicios</h1>
            <p>Registro completo de todos tus servicios realizados</p>
        </div>

        <?php if (isset($estadisticas) && $estadisticas): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 16px; color: white; box-shadow: 0 4px 20px rgba(102,126,234,0.3);">
                    <div style="font-size: 36px; font-weight: 700; margin-bottom: 5px;">
                        <?php echo $estadisticas['total_servicios'] ?? 0; ?>
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">Total Servicios</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px; border-radius: 16px; color: white; box-shadow: 0 4px 20px rgba(245,87,108,0.3);">
                    <div style="font-size: 36px; font-weight: 700; margin-bottom: 5px;">
                        <?php echo number_format($estadisticas['km_totales'] ?? 0, 1); ?> km
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">Kilómetros Totales</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 25px; border-radius: 16px; color: white; box-shadow: 0 4px 20px rgba(79,172,254,0.3);">
                    <div style="font-size: 36px; font-weight: 700; margin-bottom: 5px;">
                        $<?php echo number_format($estadisticas['total_ingresos'] ?? 0, 2); ?>
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">Ingresos Totales</div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border: none;">
                <h2 style="color: white; margin: 0; font-size: 20px; font-weight: 600;">📋 Registro de Servicios</h2>
            </div>
            <div class="card-body" style="padding: 30px;">
                <?php if (isset($historial) && count($historial) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($historial as $servicio): ?>
                            <div style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 20px; transition: all 0.3s; border-left: 5px solid #10b981;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                    <div>
                                        <div style="font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 5px;">
                                            <?php echo htmlspecialchars($servicio['tipo_servicio'] ?? 'Servicio'); ?>
                                        </div>
                                        <div style="font-size: 13px; color: #64748b;">
                                            📅 <?php echo date('d/m/Y H:i', strtotime($servicio['fecha_servicio'])); ?>
                                        </div>
                                    </div>
                                    <span style="padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #d1fae5; color: #065f46;">
                                        ✓ Registrado
                                    </span>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; padding: 15px; background: #f8fafc; border-radius: 8px;">
                                    <div>
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">📍 Origen</div>
                                        <div style="font-weight: 500; color: #1e293b;"><?php echo htmlspecialchars($servicio['origen'] ?? 'N/A'); ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">🎯 Destino</div>
                                        <div style="font-weight: 500; color: #1e293b;"><?php echo htmlspecialchars($servicio['destino'] ?? 'N/A'); ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">🛣️ Kilómetros Recorridos</div>
                                        <div style="font-weight: 600; color: #10b981; font-size: 16px;">
                                            <?php echo number_format($servicio['kilometros_recorridos'], 2); ?> km
                                        </div>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">🚗 Vehículo</div>
                                        <div style="font-weight: 500; color: #1e293b;"><?php echo htmlspecialchars($servicio['vehiculo_info'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($servicio['notas'])): ?>
                                <div style="margin-top: 12px; padding: 12px; background: white; border-radius: 8px; border-left: 3px solid #8b5cf6;">
                                    <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">📝 Notas</div>
                                    <div style="font-size: 14px; color: #475569;"><?php echo nl2br(htmlspecialchars($servicio['notas'])); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
                        <div style="font-size: 64px; margin-bottom: 16px;">📭</div>
                        <h3 style="color: #475569; margin-bottom: 8px;">No hay servicios registrados</h3>
                        <p style="margin-bottom: 24px;">Comienza a registrar tus servicios para ver el historial aquí</p>
                        <a href="<?php echo APP_URL; ?>/public/registrar-servicio.php" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 28px; border-radius: 10px; text-decoration: none; font-weight: 600;">
                            ➕ Registrar Primer Servicio
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
