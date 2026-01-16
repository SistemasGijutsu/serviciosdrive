<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Control Vehicular</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <link rel="manifest" href="<?= APP_URL ?>/public/manifest.json">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/public/icons/apple-touch-icon.svg">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ServiciosDrive">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="logo">🚗</div>
                    <h1>Control Vehicular</h1>
                    <p>Sistema de gestión de servicios</p>
                </div>
                
                <div id="mensaje"></div>
                
                <form id="loginForm" method="POST" action="<?= APP_URL ?>/public/index.php?action=login">
                    <div class="form-group">
                        <label for="usuario">Usuario</label>
                        <input type="text" id="usuario" name="usuario" 
                               placeholder="Ingresa tu usuario"
                               autocomplete="username"
                               required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" 
                               placeholder="Ingresa tu contraseña"
                               autocomplete="current-password"
                               required>
                    </div>
                    
                    <!-- Campo de selección de vehículo (oculto inicialmente) -->
                    <div class="form-group" id="vehiculo-group" style="display: none;">
                        <label for="vehiculo_id">Vehículo</label>
                        <select id="vehiculo_id" name="vehiculo_id" class="form-control">
                            <option value="">Seleccione un vehículo...</option>
                        </select>
                    </div>
                    
                    <input type="hidden" name="step" value="1">
                    
                    <button type="submit" class="btn btn-primary btn-block" id="btnLogin">
                        Iniciar Sesión
                    </button>
                </form>
                
                <div class="login-footer">
                    <small>© 2026 ServiciosDrive</small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?= APP_URL ?>/public/js/config.js"></script>
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script src="<?= APP_URL ?>/public/js/login.js"></script>
</body>
</html>
