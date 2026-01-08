<?php
// Test simple de subida de imagen
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simular sesión de usuario
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 2;
    $_SESSION['nombre_completo'] = 'Juan Pérez';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen_comprobante'])) {
    header('Content-Type: application/json');
    
    $file = $_FILES['imagen_comprobante'];
    
    // Validar errores
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error en la subida: ' . $file['error']
        ]);
        exit;
    }
    
    // Validar tipo MIME
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $tiposPermitidos)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Tipo de archivo no permitido: ' . $mimeType
        ]);
        exit;
    }
    
    // Validar tamaño (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Archivo demasiado grande'
        ]);
        exit;
    }
    
    // Crear directorio si no existe
    $directorioBase = __DIR__ . '/uploads/gastos';
    if (!is_dir($directorioBase)) {
        if (!mkdir($directorioBase, 0755, true)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No se pudo crear el directorio'
            ]);
            exit;
        }
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombreArchivo = 'gasto_' . time() . '_' . uniqid() . '.' . $extension;
    $rutaCompleta = $directorioBase . '/' . $nombreArchivo;
    $rutaRelativa = 'uploads/gastos/' . $nombreArchivo;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $rutaCompleta)) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Imagen subida correctamente',
            'ruta' => $rutaRelativa,
            'ruta_completa' => $rutaCompleta,
            'tamano' => $file['size'],
            'tipo' => $mimeType
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al mover el archivo'
        ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            white-space: pre-wrap;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        img {
            max-width: 100%;
            margin-top: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <h1>🧪 Test de Subida de Imagen</h1>
    <form method="POST" enctype="multipart/form-data" id="testForm">
        <div>
            <label>Seleccionar imagen:</label><br>
            <input type="file" name="imagen_comprobante" accept="image/*" required>
        </div>
        <br>
        <button type="submit">Subir Imagen</button>
    </form>
    
    <div id="resultado"></div>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultadoDiv = document.getElementById('resultado');
            
            try {
                const response = await fetch('test-upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultadoDiv.className = 'result success';
                    resultadoDiv.innerHTML = `
                        <strong>✅ Éxito!</strong><br>
                        Ruta: ${data.ruta}<br>
                        Tamaño: ${(data.tamano / 1024).toFixed(2)} KB<br>
                        Tipo: ${data.tipo}<br>
                        <img src="${data.ruta}" alt="Imagen subida">
                    `;
                } else {
                    resultadoDiv.className = 'result error';
                    resultadoDiv.innerHTML = `<strong>❌ Error:</strong><br>${data.mensaje}`;
                }
            } catch (error) {
                resultadoDiv.className = 'result error';
                resultadoDiv.innerHTML = `<strong>❌ Error:</strong><br>${error.message}`;
            }
        });
    </script>
</body>
</html>
