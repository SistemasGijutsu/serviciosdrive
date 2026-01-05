<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4CAF50">
    <title>Seleccionar Vehículo - ServiciosDrive</title>
    
    <link rel="manifest" href="<?php echo APP_URL; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo APP_URL; ?>/assets/icons/icon-192x192.png">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Seleccionar Vehículo</h1>
            <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></strong></p>
        </header>
        
        <div class="content">
            <div class="vehiculos-grid">
                <?php if (!empty($vehiculos)): ?>
                    <?php foreach ($vehiculos as $vehiculo): ?>
                        <div class="vehiculo-card" data-id="<?php echo $vehiculo['id']; ?>">
                            <div class="vehiculo-icon">🚗</div>
                            <h3><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></h3>
                            <p class="vehiculo-placa"><?php echo htmlspecialchars($vehiculo['placa']); ?></p>
                            <p class="vehiculo-info">
                                <?php echo htmlspecialchars($vehiculo['tipo']); ?> - 
                                <?php echo htmlspecialchars($vehiculo['color']); ?> - 
                                <?php echo htmlspecialchars($vehiculo['anio']); ?>
                            </p>
                            <p class="vehiculo-km">Km: <?php echo number_format($vehiculo['kilometraje']); ?></p>
                            <button class="btn btn-primary btn-seleccionar" 
                                    onclick="seleccionarVehiculo(<?php echo $vehiculo['id']; ?>, '<?php echo htmlspecialchars($vehiculo['placa']); ?>')">
                                Seleccionar
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">No hay vehículos disponibles en este momento.</p>
                <?php endif; ?>
            </div>
            
            <div id="mensaje" class="mensaje"></div>
        </div>
        
        <footer class="footer">
            <a href="<?php echo APP_URL; ?>/public/index.php?action=logout" class="btn btn-secondary">
                Cerrar Sesión
            </a>
        </footer>
    </div>
    
    <!-- Modal para kilometraje -->
    <div id="modalKilometraje" class="modal">
        <div class="modal-content">
            <h2>Registrar Kilometraje Inicial</h2>
            <form id="formKilometraje">
                <input type="hidden" id="vehiculo_id_modal" name="vehiculo_id">
                <div class="form-group">
                    <label for="kilometraje">Kilometraje actual:</label>
                    <input type="number" id="kilometraje" name="kilometraje" min="0" step="1">
                    <small>Opcional: Ingrese el kilometraje actual del vehículo</small>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/seleccionar-vehiculo.js"></script>
</body>
</html>
