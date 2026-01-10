<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

$authController = new AuthController();
$authController->verificarAutenticacion();

// Verificar que es administrador
if ($_SESSION['rol_id'] != 2) {
    $_SESSION['mensaje'] = 'No tienes permisos para acceder a esta página';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiempos de Espera - Servicios Drive</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/styles.css">
    <style>
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
        }
        
        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-card .subtitle {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        
        .tab {
            padding: 10px 20px;
            background: white;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .export-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .export-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📊 Tiempos de Espera entre Servicios</h1>
            <p>Análisis de tiempos de espera de conductores entre servicios</p>
            <a href="<?php echo APP_URL; ?>/public/dashboard.php" class="btn-back">← Volver al Dashboard</a>
        </header>

        <!-- Filtros -->
        <div class="filters-section">
            <h2>Filtros de Búsqueda</h2>
            <div class="filter-grid">
                <div class="form-group">
                    <label for="filtro_conductor">Conductor:</label>
                    <select id="filtro_conductor" class="form-control">
                        <option value="">Todos los conductores</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="filtro_vehiculo">Vehículo:</label>
                    <select id="filtro_vehiculo" class="form-control">
                        <option value="">Todos los vehículos</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="filtro_fecha_desde">Desde:</label>
                    <input type="date" id="filtro_fecha_desde" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="filtro_fecha_hasta">Hasta:</label>
                    <input type="date" id="filtro_fecha_hasta" class="form-control">
                </div>
            </div>
            
            <div style="display: flex; align-items: center;">
                <label style="margin-right: 10px;">
                    <input type="checkbox" id="solo_con_espera" checked>
                    Mostrar solo servicios con tiempo de espera
                </label>
                <button onclick="aplicarFiltros()" class="btn btn-primary">Buscar</button>
                <button onclick="limpiarFiltros()" class="btn" style="margin-left: 10px;">Limpiar</button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-cards" id="stats-cards">
            <!-- Se llenarán dinámicamente -->
        </div>

        <!-- Pestañas -->
        <div class="tabs">
            <div class="tab active" onclick="cambiarTab('detalle')">Detalle de Servicios</div>
            <div class="tab" onclick="cambiarTab('por-conductor')">Por Conductor</div>
        </div>

        <!-- Contenido: Detalle de servicios -->
        <div id="tab-detalle" class="tab-content active">
            <div class="table-container">
                <h2>Detalle de Tiempos de Espera
                    <button onclick="exportarDetalle()" class="export-btn">📥 Exportar a CSV</button>
                </h2>
                <div id="tabla-detalle">
                    <div class="loading">Cargando datos...</div>
                </div>
            </div>
        </div>

        <!-- Contenido: Por conductor -->
        <div id="tab-por-conductor" class="tab-content">
            <div class="table-container">
                <h2>Promedios por Conductor
                    <button onclick="exportarPorConductor()" class="export-btn">📥 Exportar a CSV</button>
                </h2>
                <div id="tabla-por-conductor">
                    <div class="loading">Cargando datos...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let datosDetalle = [];
        let datosPorConductor = [];
        
        // Cargar conductores y vehículos para los filtros
        async function cargarFiltros() {
            try {
                // Cargar conductores
                const respConductores = await fetch('<?php echo APP_URL; ?>/public/api/reportes.php?action=obtener_conductores');
                const conductores = await respConductores.json();
                
                const selectConductor = document.getElementById('filtro_conductor');
                if (conductores.success && conductores.datos) {
                    conductores.datos.forEach(c => {
                        const option = document.createElement('option');
                        option.value = c.id;
                        option.textContent = `${c.nombre} ${c.apellido}`;
                        selectConductor.appendChild(option);
                    });
                }
                
                // Cargar vehículos
                const respVehiculos = await fetch('<?php echo APP_URL; ?>/public/api/reportes.php?action=obtener_vehiculos');
                const vehiculos = await respVehiculos.json();
                
                const selectVehiculo = document.getElementById('filtro_vehiculo');
                if (vehiculos.success && vehiculos.datos) {
                    vehiculos.datos.forEach(v => {
                        const option = document.createElement('option');
                        option.value = v.id;
                        option.textContent = `${v.marca} ${v.modelo} (${v.placa})`;
                        selectVehiculo.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar filtros:', error);
            }
        }
        
        // Cambiar entre pestañas
        function cambiarTab(tab) {
            // Actualizar tabs
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(`tab-${tab}`).classList.add('active');
        }
        
        // Aplicar filtros
        async function aplicarFiltros() {
            const filtros = {
                usuario_id: document.getElementById('filtro_conductor').value,
                vehiculo_id: document.getElementById('filtro_vehiculo').value,
                fecha_desde: document.getElementById('filtro_fecha_desde').value,
                fecha_hasta: document.getElementById('filtro_fecha_hasta').value,
                solo_con_espera: document.getElementById('solo_con_espera').checked ? 1 : 0
            };
            
            await Promise.all([
                cargarEstadisticas(filtros),
                cargarDetalleEspera(filtros),
                cargarReportePorConductor(filtros)
            ]);
        }
        
        // Limpiar filtros
        function limpiarFiltros() {
            document.getElementById('filtro_conductor').value = '';
            document.getElementById('filtro_vehiculo').value = '';
            document.getElementById('filtro_fecha_desde').value = '';
            document.getElementById('filtro_fecha_hasta').value = '';
            document.getElementById('solo_con_espera').checked = true;
            aplicarFiltros();
        }
        
        // Cargar estadísticas
        async function cargarEstadisticas(filtros = {}) {
            try {
                const params = new URLSearchParams({action: 'estadisticas_tiempos_espera', ...filtros});
                const response = await fetch(`<?php echo APP_URL; ?>/public/api/reportes.php?${params}`);
                const data = await response.json();
                
                if (data.success && data.datos) {
                    const stats = data.datos;
                    const statsHtml = `
                        <div class="stat-card">
                            <h3>Total de Servicios</h3>
                            <div class="value">${stats.total_servicios || 0}</div>
                        </div>
                        <div class="stat-card">
                            <h3>Con Tiempo de Espera</h3>
                            <div class="value">${stats.servicios_con_espera || 0}</div>
                        </div>
                        <div class="stat-card">
                            <h3>Promedio de Espera</h3>
                            <div class="value">${formatearMinutos(stats.promedio_espera_minutos)}</div>
                        </div>
                        <div class="stat-card">
                            <h3>Mínimo</h3>
                            <div class="value">${formatearMinutos(stats.minimo_espera_minutos)}</div>
                        </div>
                        <div class="stat-card">
                            <h3>Máximo</h3>
                            <div class="value">${formatearMinutos(stats.maximo_espera_minutos)}</div>
                        </div>
                        <div class="stat-card">
                            <h3>Total de Espera</h3>
                            <div class="value">${formatearMinutos(stats.total_espera_minutos)}</div>
                            <div class="subtitle">${Math.round((stats.total_espera_minutos || 0) / 60)} horas</div>
                        </div>
                    `;
                    document.getElementById('stats-cards').innerHTML = statsHtml;
                }
            } catch (error) {
                console.error('Error al cargar estadísticas:', error);
            }
        }
        
        // Cargar detalle de tiempos de espera
        async function cargarDetalleEspera(filtros = {}) {
            try {
                const params = new URLSearchParams({action: 'tiempos_espera', limite: 200, ...filtros});
                const response = await fetch(`<?php echo APP_URL; ?>/public/api/reportes.php?${params}`);
                const data = await response.json();
                
                datosDetalle = data.datos || [];
                
                if (!data.success || datosDetalle.length === 0) {
                    document.getElementById('tabla-detalle').innerHTML = '<div class="no-data">No se encontraron datos</div>';
                    return;
                }
                
                let html = `
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Conductor</th>
                                <th>Vehículo</th>
                                <th>Trayecto</th>
                                <th>Tiempo de Espera</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                datosDetalle.forEach(servicio => {
                    const badgeClass = getBadgeClass(servicio.tiempo_espera_minutos);
                    html += `
                        <tr>
                            <td>${formatearFecha(servicio.fecha_servicio)}</td>
                            <td>${servicio.conductor}</td>
                            <td>${servicio.placa} - ${servicio.vehiculo}</td>
                            <td>${servicio.trayecto}</td>
                            <td><span class="badge ${badgeClass}">${servicio.tiempo_espera_formato}</span></td>
                        </tr>
                    `;
                });
                
                html += `
                        </tbody>
                    </table>
                `;
                
                document.getElementById('tabla-detalle').innerHTML = html;
            } catch (error) {
                console.error('Error al cargar detalle:', error);
                document.getElementById('tabla-detalle').innerHTML = '<div class="no-data">Error al cargar datos</div>';
            }
        }
        
        // Cargar reporte por conductor
        async function cargarReportePorConductor(filtros = {}) {
            try {
                const params = new URLSearchParams({action: 'reporte_espera_por_conductor', ...filtros});
                const response = await fetch(`<?php echo APP_URL; ?>/public/api/reportes.php?${params}`);
                const data = await response.json();
                
                datosPorConductor = data.datos || [];
                
                if (!data.success || datosPorConductor.length === 0) {
                    document.getElementById('tabla-por-conductor').innerHTML = '<div class="no-data">No se encontraron datos</div>';
                    return;
                }
                
                let html = `
                    <table>
                        <thead>
                            <tr>
                                <th>Conductor</th>
                                <th>Total Servicios</th>
                                <th>Servicios con Espera</th>
                                <th>Promedio de Espera</th>
                                <th>Mínimo</th>
                                <th>Máximo</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                datosPorConductor.forEach(conductor => {
                    const badgeClass = getBadgeClass(conductor.promedio_espera_minutos);
                    html += `
                        <tr>
                            <td><strong>${conductor.conductor}</strong></td>
                            <td>${conductor.total_servicios}</td>
                            <td>${conductor.servicios_con_espera}</td>
                            <td><span class="badge ${badgeClass}">${conductor.promedio_formato}</span></td>
                            <td>${formatearMinutos(conductor.minimo_espera_minutos)}</td>
                            <td>${formatearMinutos(conductor.maximo_espera_minutos)}</td>
                        </tr>
                    `;
                });
                
                html += `
                        </tbody>
                    </table>
                `;
                
                document.getElementById('tabla-por-conductor').innerHTML = html;
            } catch (error) {
                console.error('Error al cargar reporte por conductor:', error);
                document.getElementById('tabla-por-conductor').innerHTML = '<div class="no-data">Error al cargar datos</div>';
            }
        }
        
        // Formatear minutos a formato legible
        function formatearMinutos(minutos) {
            if (!minutos && minutos !== 0) return 'N/A';
            const horas = Math.floor(minutos / 60);
            const mins = Math.round(minutos % 60);
            return `${horas}h ${mins}m`;
        }
        
        // Formatear fecha
        function formatearFecha(fecha) {
            const date = new Date(fecha);
            return date.toLocaleString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Obtener clase de badge según el tiempo
        function getBadgeClass(minutos) {
            if (!minutos) return 'badge-info';
            if (minutos < 15) return 'badge-success';
            if (minutos < 30) return 'badge-warning';
            return 'badge-danger';
        }
        
        // Exportar detalle a CSV
        function exportarDetalle() {
            if (datosDetalle.length === 0) {
                alert('No hay datos para exportar');
                return;
            }
            
            let csv = 'Fecha/Hora,Conductor,Vehículo,Trayecto,Tiempo de Espera (min)\n';
            datosDetalle.forEach(servicio => {
                csv += `"${servicio.fecha_servicio}","${servicio.conductor}","${servicio.placa} - ${servicio.vehiculo}","${servicio.trayecto}","${servicio.tiempo_espera_minutos || 0}"\n`;
            });
            
            descargarCSV(csv, 'tiempos_espera_detalle.csv');
        }
        
        // Exportar por conductor a CSV
        function exportarPorConductor() {
            if (datosPorConductor.length === 0) {
                alert('No hay datos para exportar');
                return;
            }
            
            let csv = 'Conductor,Total Servicios,Servicios con Espera,Promedio Espera (min),Mínimo (min),Máximo (min)\n';
            datosPorConductor.forEach(conductor => {
                csv += `"${conductor.conductor}","${conductor.total_servicios}","${conductor.servicios_con_espera}","${Math.round(conductor.promedio_espera_minutos)}","${conductor.minimo_espera_minutos}","${conductor.maximo_espera_minutos}"\n`;
            });
            
            descargarCSV(csv, 'tiempos_espera_por_conductor.csv');
        }
        
        // Descargar CSV
        function descargarCSV(contenido, nombreArchivo) {
            const blob = new Blob([contenido], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', nombreArchivo);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Inicializar al cargar la página
        window.onload = function() {
            cargarFiltros();
            aplicarFiltros();
        };
    </script>
</body>
</html>
