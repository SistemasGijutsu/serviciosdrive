<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4CAF50">
    <title>Historial - ServiciosDrive</title>
    
    <link rel="manifest" href="<?php echo APP_URL; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo APP_URL; ?>/assets/icons/icon-192x192.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📊 Historial de Servicios</h1>
            <p><strong><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></strong></p>
        </header>
        
        <!-- Estadísticas -->
        <?php if ($estadisticas && $estadisticas['total_servicios'] > 0): ?>
        <div class="content estadisticas-card">
            <h2>Resumen General</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $estadisticas['total_servicios']; ?></div>
                    <div class="stat-label">Servicios Realizados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($estadisticas['km_totales']); ?></div>
                    <div class="stat-label">Kilómetros Recorridos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo round($estadisticas['minutos_totales'] / 60, 1); ?></div>
                    <div class="stat-label">Horas Totales</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">$<?php echo number_format($estadisticas['costo_total'], 2); ?></div>
                    <div class="stat-label">Total Generado</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Lista de servicios -->
        <div class="content">
            <h2>Servicios Registrados</h2>
            
            <?php if (!empty($historial)): ?>
                <div class="historial-list">
                    <?php foreach ($historial as $servicio): ?>
                        <div class="historial-item <?php echo $servicio['estado']; ?>">
                            <div class="historial-header">
                                <div class="historial-fecha">
                                    📅 <?php echo date('d/m/Y H:i', strtotime($servicio['fecha_inicio'])); ?>
                                </div>
                                <div class="historial-estado estado-<?php echo $servicio['estado']; ?>">
                                    <?php 
                                        $estados = [
                                            'en_curso' => '🟡 En Curso',
                                            'finalizado' => '✅ Finalizado',
                                            'cancelado' => '❌ Cancelado'
                                        ];
                                        echo $estados[$servicio['estado']] ?? $servicio['estado'];
                                    ?>
                                </div>
                            </div>
                            
                            <div class="historial-body">
                                <p><strong>🚗 Vehículo:</strong> <?php echo htmlspecialchars($servicio['vehiculo_info']); ?></p>
                                <p><strong>📍 Origen:</strong> <?php echo htmlspecialchars($servicio['origen']); ?></p>
                                <p><strong>📍 Destino:</strong> <?php echo htmlspecialchars($servicio['destino']); ?></p>
                                
                                <?php if ($servicio['tipo_servicio']): ?>
                                    <p><strong>Tipo:</strong> <?php echo htmlspecialchars($servicio['tipo_servicio']); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($servicio['estado'] == 'finalizado'): ?>
                                    <div class="historial-metrics">
                                        <?php if ($servicio['kilometraje_recorrido']): ?>
                                            <span class="metric">🛣️ <?php echo number_format($servicio['kilometraje_recorrido']); ?> km</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($servicio['duracion_minutos']): ?>
                                            <span class="metric">⏱️ <?php echo round($servicio['duracion_minutos'] / 60, 1); ?> hrs</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($servicio['costo']): ?>
                                            <span class="metric">💰 $<?php echo number_format($servicio['costo'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($servicio['notas']): ?>
                                    <div class="historial-notas">
                                        <strong>Notas:</strong>
                                        <p><?php echo nl2br(htmlspecialchars($servicio['notas'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>No hay servicios registrados aún.</p>
                    <a href="<?php echo APP_URL; ?>/public/registrar-servicio.php" class="btn btn-primary mt-20">
                        Registrar Primer Servicio
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <footer class="footer">
            <div class="footer-buttons">
                <a href="<?php echo APP_URL; ?>/public/dashboard.php" class="btn btn-secondary">
                    ← Volver
                </a>
                <a href="<?php echo APP_URL; ?>/public/index.php?action=logout" class="btn btn-secondary">
                    Cerrar Sesión
                </a>
            </div>
        </footer>
    </div>
    
    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
</body>
</html>
