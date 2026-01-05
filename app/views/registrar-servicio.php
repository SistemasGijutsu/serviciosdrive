<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4CAF50">
    <title>Registrar Servicio - ServiciosDrive</title>
    
    <link rel="manifest" href="<?php echo APP_URL; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo APP_URL; ?>/assets/icons/icon-192x192.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><?php echo $servicioActivo ? 'Servicio en Curso' : 'Nuevo Servicio'; ?></h1>
            <p><strong><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></strong></p>
            <p>🚗 <?php echo htmlspecialchars($sesionActiva['marca'] . ' ' . $sesionActiva['modelo'] . ' - ' . $sesionActiva['placa']); ?></p>
        </header>
        
        <div class="content">
            <?php if ($servicioActivo): ?>
                <!-- Servicio Activo -->
                <div class="servicio-activo-card">
                    <h2>📍 Servicio en Curso</h2>
                    <div class="info-card">
                        <p><strong>Origen:</strong> <?php echo htmlspecialchars($servicioActivo['origen']); ?></p>
                        <p><strong>Destino:</strong> <?php echo htmlspecialchars($servicioActivo['destino']); ?></p>
                        <p><strong>Tipo:</strong> <?php echo htmlspecialchars($servicioActivo['tipo_servicio'] ?: 'General'); ?></p>
                        <p><strong>Inicio:</strong> <?php echo date('d/m/Y H:i', strtotime($servicioActivo['fecha_inicio'])); ?></p>
                        <?php if ($servicioActivo['kilometraje_inicio']): ?>
                            <p><strong>Km inicial:</strong> <?php echo number_format($servicioActivo['kilometraje_inicio']); ?> km</p>
                        <?php endif; ?>
                        <?php if ($servicioActivo['notas']): ?>
                            <p><strong>Notas:</strong> <?php echo nl2br(htmlspecialchars($servicioActivo['notas'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Formulario para finalizar -->
                    <form id="formFinalizarServicio" class="mt-20">
                        <h3>Finalizar Servicio</h3>
                        
                        <div class="form-group">
                            <label for="kilometraje_fin">Kilometraje final:</label>
                            <input type="number" id="kilometraje_fin" name="kilometraje_fin" min="<?php echo $servicioActivo['kilometraje_inicio'] ?: 0; ?>" step="1">
                            <small>Opcional: Ingrese el kilometraje al finalizar</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="costo">Costo del servicio ($):</label>
                            <input type="number" id="costo" name="costo" min="0" step="0.01" placeholder="0.00">
                            <small>Opcional: Monto cobrado por el servicio</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="notas_fin">Notas adicionales:</label>
                            <textarea id="notas_fin" name="notas" rows="3" placeholder="Observaciones al finalizar..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-danger btn-block">
                            Finalizar Servicio
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Formulario para nuevo servicio -->
                <form id="formNuevoServicio" class="servicio-form">
                    <h2>📝 Registrar Nuevo Servicio</h2>
                    
                    <div class="form-group">
                        <label for="origen">Origen: *</label>
                        <input type="text" id="origen" name="origen" required placeholder="Punto de partida">
                    </div>
                    
                    <div class="form-group">
                        <label for="destino">Destino: *</label>
                        <input type="text" id="destino" name="destino" required placeholder="Punto de llegada">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_servicio">Tipo de servicio:</label>
                        <select id="tipo_servicio" name="tipo_servicio">
                            <option value="">Seleccionar...</option>
                            <option value="Carga">Carga</option>
                            <option value="Pasajero">Pasajero</option>
                            <option value="Traslado">Traslado</option>
                            <option value="Reparto">Reparto</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="kilometraje_inicio">Kilometraje inicial:</label>
                        <input type="number" id="kilometraje_inicio" name="kilometraje_inicio" min="0" step="1" 
                               value="<?php echo $sesionActiva['kilometraje_inicio'] ?? ''; ?>">
                        <small>Opcional: Kilometraje al iniciar el servicio</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="notas">Notas:</label>
                        <textarea id="notas" name="notas" rows="3" placeholder="Observaciones del servicio..."></textarea>
                    </div>
                    
                    <div id="mensaje" class="mensaje"></div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Iniciar Servicio
                    </button>
                </form>
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
                <a href="<?php echo APP_URL; ?>/public/historial.php" class="btn btn-secondary">
                    📊 Ver Historial
                </a>
            </div>
        </footer>
    </div>
    
    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/servicio.js"></script>
</body>
</html>
