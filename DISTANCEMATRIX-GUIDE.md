# 🗺️ Integración Distance Matrix API

API de Distance Matrix AI integrada para calcular distancias y tiempos entre ubicaciones.

## 📋 Configuración

### API Key
La API Key está configurada en: `config/distancematrix.php`

```php
define('DISTANCE_MATRIX_API_KEY', 'TU_API_KEY_AQUI');
```

**⚠️ IMPORTANTE:** Reemplaza la API Key con la tuya de Postman.

---

## 🚀 Uso en el Sistema

### 1️⃣ Desde el Formulario de Servicios

El formulario en `registrar-servicio.php` ya tiene integrado el cálculo automático:

1. Ingresa **origen** y **destino**
2. Haz clic en **"Calcular Distancia Automáticamente"**
3. Se autocompletará el campo de **kilómetros recorridos**

---

## 💻 Ejemplos de Código

### ✅ JavaScript (Frontend)

```javascript
// Calcular desde direcciones
const resultado = await DistanceMatrixUtil.calcularDistanciaDirecciones(
    "Medellín, Colombia",
    "Bogotá, Colombia"
);

console.log(resultado.distancia.kilometros); // 411.5
console.log(resultado.duracion.texto);       // "7 hours 30 mins"

// Calcular desde coordenadas
const resultado2 = await DistanceMatrixUtil.calcularDistanciaCoordenadas(
    6.2442, -75.5812,  // Medellín
    4.7110, -74.0721   // Bogotá
);

console.log(resultado2.distancia.texto); // "411.5 km"
```

### ✅ PHP (Backend)

```php
require_once 'config/distancematrix.php';

// Calcular distancia
$resultado = calcularDistancia(
    "Medellín, Colombia",
    "Bogotá, Colombia"
);

if ($resultado) {
    echo "Distancia: " . $resultado['distancia']['texto'];
    echo "Duración: " . $resultado['duracion']['texto'];
    echo "Kilómetros: " . $resultado['distancia']['kilometros'];
}
```

### ✅ Usando el API Endpoint

```javascript
// Petición GET
const response = await fetch(
    '/serviciosdrive/public/api/distancematrix.php?origen=Medellín&destino=Bogotá'
);
const data = await response.json();

// Petición POST
const formData = new FormData();
formData.append('origen', 'Calle 10 # 20-30, Medellín');
formData.append('destino', 'Carrera 7 # 32-10, Bogotá');

const response2 = await fetch('/serviciosdrive/public/api/distancematrix.php', {
    method: 'POST',
    body: formData
});
const data2 = await response2.json();
```

---

## 📊 Estructura de Respuesta

```json
{
    "success": true,
    "distancia": {
        "valor": 411500,          // metros
        "texto": "411.5 km",
        "kilometros": 411.5       // convertido a km
    },
    "duracion": {
        "valor": 27000,           // segundos
        "texto": "7 hours 30 mins",
        "minutos": 450.0          // convertido a minutos
    },
    "origen": "Medellín, Antioquia, Colombia",
    "destino": "Bogotá, Colombia"
}
```

---

## 🎯 Casos de Uso

### 1. Calcular distancia en registro de servicio
Ya implementado en el formulario de servicios.

### 2. Calcular múltiples puntos (ruta)
```php
$puntos = [
    "6.2442,-75.5812",  // Medellín
    "6.1701,-75.6058",  // Envigado
    "6.1675,-75.5983"   // Sabaneta
];

$distanciaTotal = calcularDistanciaTotal($puntos);
echo "Distancia total: {$distanciaTotal} km";
```

### 3. Validar distancia en PHP antes de guardar
```php
// En ServicioController.php
public function crear() {
    $origen = $_POST['origen'];
    $destino = $_POST['destino'];
    
    // Calcular distancia real
    require_once __DIR__ . '/../../config/distancematrix.php';
    $resultado = calcularDistancia($origen, $destino);
    
    if ($resultado) {
        // Guardar con la distancia calculada
        $datos['kilometros_recorridos'] = $resultado['distancia']['kilometros'];
    }
    
    // Continuar con el registro...
}
```

---

## 🔧 Solución de Problemas

### Error: "No se pudo calcular la distancia"
- ✅ Verifica que la API Key sea válida
- ✅ Confirma que las direcciones/coordenadas sean correctas
- ✅ Revisa el formato: direcciones como texto o coordenadas como "lat,lng"

### Error: CORS
Si usas desde otro dominio:
```php
// En api/distancematrix.php ya está configurado:
header('Access-Control-Allow-Origin: *');
```

### API Key inválida
Verifica en Postman que tu API Key funcione:
```
GET https://api.distancematrix.ai/maps/api/distancematrix/json?origins=6.2442,-75.5812&destinations=4.7110,-74.0721&key=TU_API_KEY
```

---

## 📌 Archivos Creados

```
config/
  └── distancematrix.php          # Configuración y funciones PHP

public/
  └── api/
      └── distancematrix.php      # Endpoint API

  └── js/
      └── distancematrix-util.js  # Utilidades JavaScript

app/
  └── views/
      └── registrar-servicio.php  # Formulario con cálculo automático
```

---

## 🎓 Próximos Pasos

### Funcionalidades adicionales que puedes implementar:

1. **Mostrar mapa con la ruta**
```javascript
// Integrar con Leaflet o Google Maps
```

2. **Calcular costo basado en distancia**
```php
$tarifa_por_km = 2500; // COP
$costo = $resultado['distancia']['kilometros'] * $tarifa_por_km;
```

3. **Guardar historial de rutas**
```sql
ALTER TABLE servicios ADD COLUMN distancia_calculada_km DECIMAL(10,2);
ALTER TABLE servicios ADD COLUMN duracion_estimada_minutos INT;
```

4. **Notificar si la distancia es muy diferente**
```javascript
const kmIngresado = parseFloat(document.getElementById('kilometros_recorridos').value);
const kmCalculado = resultado.distancia.kilometros;
const diferencia = Math.abs(kmIngresado - kmCalculado);

if (diferencia > 5) {
    alert('⚠️ La distancia ingresada difiere de la calculada');
}
```

---

## 📞 Soporte

- [Documentación Distance Matrix AI](https://distancematrix.ai/documentation)
- [Panel de API Keys](https://distancematrix.ai/dashboard)
