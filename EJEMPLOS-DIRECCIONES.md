# 🇨🇴 Ejemplos de Direcciones para Distance Matrix

## ✅ FORMATO CORRECTO

### 1️⃣ **Con Ciudad Completa** (RECOMENDADO)
```
Origen: Cra 58 # 73-05, Medellín, Antioquia, Colombia
Destino: Calle 10 # 20-30, Medellín, Antioquia, Colombia
```

### 2️⃣ **Usando el Selector de Ciudad**
En el formulario:
- **Ciudad**: Medellín (seleccionar en el dropdown)
- **Origen**: Cra 58 # 73-05
- **Destino**: Calle 10 # 20-30

El sistema agregará automáticamente ", Medellín, Antioquia, Colombia"

### 3️⃣ **Con Coordenadas GPS** (MÁS PRECISO)
```
Origen: 6.2442,-75.5812
Destino: 6.2486,-75.5742
```

### 4️⃣ **Usando Geolocalización**
Haz clic en el botón **"📍 Usar mi ubicación actual"** para capturar tu posición GPS actual.

---

## 🗺️ Ejemplos Reales por Ciudad

### **MEDELLÍN**

#### Rutas Cortas (Zona Centro)
```
Origen: Parque Lleras, El Poblado, Medellín
Destino: Estadio Atanasio Girardot, Medellín
Distancia: ~3.5 km
```

```
Origen: Centro Comercial Santa Fe, Medellín
Destino: Aeropuerto Olaya Herrera, Medellín
Distancia: ~5 km
```

#### Rutas Medianas
```
Origen: Universidad de Antioquia, Medellín
Destino: Parque Arví, Medellín
Distancia: ~12 km
```

#### Rutas Largas (Área Metropolitana)
```
Origen: Parque Principal, Envigado, Antioquia
Destino: Parque Principal, Sabaneta, Antioquia
Distancia: ~8 km
```

```
Origen: Bello, Antioquia, Colombia
Destino: Caldas, Antioquia, Colombia
Distancia: ~28 km
```

---

### **BOGOTÁ**

#### Zona Norte
```
Origen: Centro Comercial Santafé, Bogotá
Destino: Unicentro, Bogotá
Distancia: ~4 km
```

#### Centro - Norte
```
Origen: Plaza de Bolívar, Bogotá
Destino: Parque 93, Bogotá
Distancia: ~8 km
```

#### Aeropuerto
```
Origen: Aeropuerto El Dorado, Bogotá
Destino: Zona T, Bogotá
Distancia: ~15 km
```

---

### **CALI**

```
Origen: Terminal de Transportes, Cali
Destino: Unicentro, Cali
Distancia: ~7 km
```

```
Origen: Cristo Rey, Cali
Destino: Estadio Pascual Guerrero, Cali
Distancia: ~5 km
```

---

### **BARRANQUILLA**

```
Origen: Centro Comercial Buenavista, Barranquilla
Destino: Parque Cultural del Caribe, Barranquilla
Distancia: ~4 km
```

---

### **CARTAGENA**

```
Origen: Ciudad Amurallada, Cartagena
Destino: Castillo San Felipe, Cartagena
Distancia: ~2 km
```

```
Origen: Aeropuerto Rafael Núñez, Cartagena
Destino: Centro Histórico, Cartagena
Distancia: ~6 km
```

---

## 🚫 ERRORES COMUNES

### ❌ **Direcciones Incompletas**
```
❌ INCORRECTO:
   Origen: Cra 58 # 73-05
   Destino: Calle 10 # 20-30
   
   Problema: Falta la ciudad
```

### ✅ **Solución:**
```
✅ CORRECTO:
   1. Seleccionar ciudad en el dropdown
   2. Ingresar solo la dirección
   3. El sistema agregará la ciudad automáticamente
   
   O escribir completo:
   Origen: Cra 58 # 73-05, Medellín, Colombia
```

---

## 💡 TIPS PRO

### 1. **Usar Lugares Conocidos**
En lugar de direcciones, puedes usar nombres de lugares:
```
Origen: Parque Lleras, Medellín
Destino: Centro Comercial Oviedo, Medellín
```

### 2. **Usar Geolocalización para Origen**
Si estás en el punto de partida:
1. Haz clic en "📍 Usar mi ubicación actual"
2. El sistema capturará tus coordenadas GPS
3. Más preciso que escribir la dirección

### 3. **Validar con Google Maps**
Si tienes dudas de si la dirección es válida:
1. Busca la dirección en Google Maps
2. Si la encuentra, funcionará en Distance Matrix

### 4. **Formato de Coordenadas**
```
Formato: latitud,longitud
Ejemplo: 6.2442,-75.5812
         ↑       ↑
      Latitud  Longitud (negativa en Colombia)
```

---

## 🧪 Ejemplos para Pruebas

### Test Rápido (Misma Ciudad)
```
Ciudad: Medellín
Origen: Parque Lleras
Destino: Centro Comercial Santafé
Resultado esperado: ~2-3 km
```

### Test Medio (Área Metropolitana)
```
Ciudad: Medellín
Origen: Medellín, Antioquia
Destino: Envigado, Antioquia
Resultado esperado: ~8-10 km
```

### Test Largo (Entre Ciudades)
```
Origen: Medellín, Antioquia, Colombia
Destino: Bogotá, Colombia
Resultado esperado: ~400+ km
```

### Test con Coordenadas
```
Origen: 6.2442,-75.5812 (Medellín centro)
Destino: 6.2486,-75.5742 (El Poblado)
Resultado esperado: ~1-2 km
```

---

## 📱 En Móvil

La geolocalización funciona mejor en dispositivos móviles:

1. **Abre la página en tu celular**
2. **Permite acceso a ubicación** cuando el navegador lo solicite
3. **Haz clic en "📍 Usar mi ubicación actual"**
4. **Se capturará tu GPS automáticamente**

Esto es ideal para conductores en ruta.

---

## 🆘 Solución de Problemas

### "No se pudo calcular la distancia"
✅ **Soluciones:**
1. Verifica que seleccionaste una ciudad
2. Escribe direcciones más específicas
3. Usa nombres de lugares conocidos
4. Prueba con coordenadas GPS
5. Usa el botón de geolocalización

### "Error de conexión"
✅ **Verifica:**
1. Conexión a internet
2. XAMPP está corriendo
3. API Key es válida en `config/distancematrix.php`

### "Ubicación no encontrada"
✅ **Prueba:**
1. Agregar más detalles: barrio, ciudad, departamento
2. Usar nombres de lugares en vez de direcciones
3. Usar coordenadas GPS directamente

---

## 🎯 Recomendación para Producción

Para tu sistema de taxis/servicios:

1. **Siempre usar geolocalización para ORIGEN** → Más preciso
2. **Cliente escribe DESTINO** → Puede ser dirección o lugar conocido
3. **Ciudad por defecto** → Configurar Medellín como predeterminado
4. **Validar al enviar** → Calcular distancia antes de guardar el servicio

---

¿Tienes dudas? Prueba con la página de test:
```
http://localhost/serviciosdrive/test-distancematrix.html
```
