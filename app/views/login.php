<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4CAF50">
    <meta name="description" content="Sistema de Control Vehicular">
    <title>Login - ServiciosDrive</title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="<?php echo APP_URL; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo APP_URL; ?>/assets/icons/icon-192x192.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="<?php echo APP_URL; ?>/assets/icons/icon-192x192.png" alt="Logo" class="logo" onerror="this.style.display='none'">
                <h1>ServiciosDrive</h1>
                <p>Control Vehicular</p>
            </div>
            
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="usuario">👤 Usuario</label>
                    <input type="text" id="usuario" name="usuario" required autofocus placeholder="Ingrese su usuario">
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Ingrese su contraseña">
                </div>
                
                <div class="form-group" id="vehiculo-group" style="display: none;">
                    <label for="vehiculo_id">🚗 Vehículo</label>
                    <select id="vehiculo_id" name="vehiculo_id">
                        <option value="">Seleccione un vehículo...</option>
                    </select>
                </div>
                
                <div id="mensaje" class="mensaje"></div>
                
                <button type="submit" class="btn btn-primary btn-block" id="btnLogin">
                    🔓 INICIAR SESIÓN
                </button>
            </form>
        </div>
    </div>
    
    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/login.js"></script>
</body>
</html>
