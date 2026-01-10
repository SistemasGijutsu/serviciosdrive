<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

// Verificar que sea administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit;
}

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiempos de Espera - Admin</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
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
                <strong><?= htmlspecialchars($nombreUsuario) ?></strong>
                <small>🔑 Administrador</small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/public/dashboard.php" class="nav-link">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/usuarios.php" class="nav-link">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Usuarios</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/vehiculos.php" class="nav-link">
                <span class="nav-icon">🚗</span>
                <span class="nav-text">Vehículos</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/servicios.php" class="nav-link">
                <span class="nav-icon">📋</span>
                <span class="nav-text">Todos los Servicios</span>
            </a>
            
            <!-- Dropdown de Reportes -->
            <div class="nav-dropdown">
                <button class="nav-dropdown-toggle active open" id="reportesToggle">
                    <span class="nav-icon">📈</span>
                    <span class="nav-text">Reportes</span>
                    <span class="nav-dropdown-arrow">▼</span>
                </button>
                <div class="nav-dropdown-menu show" id="reportesMenu">
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=resumen" class="nav-link">
                        <span class="nav-text">📊 Resumen General</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=gastos" class="nav-link">
                        <span class="nav-text">💰 Reporte de Gastos</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=servicios" class="nav-link">
                        <span class="nav-text">📋 Reporte de Servicios</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=conductor" class="nav-link">
                        <span class="nav-text">👤 Por Conductor</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=vehiculo" class="nav-link">
                        <span class="nav-text">🚗 Por Vehículo</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=fechas" class="nav-link">
                        <span class="nav-text">📅 Por Fechas</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=trayectos" class="nav-link">
                        <span class="nav-text">🗺️ Trayectos</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/tiempos-espera.php" class="nav-link active">
                        <span class="nav-text">⏱️ Tiempos de Espera</span>
                    </a>
                </div>
            </div>
            
            <a href="<?= APP_URL ?>/public/admin/incidencias.php" class="nav-link">
                <span class="nav-icon">⚠️</span>
                <span class="nav-text">Incidencias/PQRs</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/tipificaciones.php" class="nav-link">
                <span class="nav-icon">🏷️</span>
                <span class="nav-text">Tipificaciones</span>
            </a>
            <a href="<?= APP_URL ?>/public/index.php?action=logout" class="nav-link nav-link-logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Cerrar Sesión</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <small>© 2025 ServiciosDrive</small>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="dashboard-header">
            <h1>⏱️ Tiempos de Espera entre Servicios</h1>
            <p class="text-muted">Análisis de tiempos de espera de conductores entre servicios</p>
        </div>

        <!-- Filtros -->
        <div class="card">
            <div class="card-header">
                <h2>🔍 Filtros de Búsqueda</h2>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="filtro_conductor">CONDUCTOR:</label>
                        <select id="filtro_conductor" class="form-control">
                            <option value="">Todos los conductores</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="filtro_vehiculo">VEHÍCULO:</label>
                        <select id="filtro_vehiculo" class="form-control">
                            <option value="">Todos los vehículos</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="filtro_fecha_desde">DESDE:</label>
                        <input type="date" id="filtro_fecha_desde" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="filtro_fecha_hasta">HASTA:</label>
                        <input type="date" id="filtro_fecha_hasta" class="form-control">
                    </div>
                </div>
                
                <div style="margin-top: 15px; display: flex; align-items: center; gap: 15px;">
                    <label class="checkbox-label">
                        <input type="checkbox" id="solo_con_espera" checked>
                        <span>Mostrar solo servicios con tiempo de espera</span>
                    </label>
                    <button onclick="aplicarFiltros()" class="btn btn-primary">Buscar</button>
                    <button onclick="limpiarFiltros()" class="btn btn-secondary">Limpiar</button>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid" id="stats-cards" style="margin-top: 20px;">
            <!-- Se llenarán dinámicamente -->
        </div>

        <!-- Tabs -->
        <div class="tabs-container" style="margin-top: 20px;">
            <div class="tabs">
                <button class="tab-btn active" onclick="cambiarTab('detalle')">Detalle de Servicios</button>
                <button class="tab-btn" onclick="cambiarTab('por-conductor')">Por Conductor</button>
            </div>
        </div>

        <!-- Contenido: Detalle de servicios -->
        <div id="tab-detalle" class="tab-content active">
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>📋 Detalle de Tiempos de Espera</h2>
                    <button onclick="exportarDetalle()" class="btn btn-success">📥 Exportar CSV</button>
                </div>
                <div class="card-body">
                    <div id="tabla-detalle" class="table-responsive">
                        <div class="loading">Cargando datos...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido: Por conductor -->
        <div id="tab-por-conductor" class="tab-content" style="display: none;">
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>👥 Promedios por Conductor</h2>
                    <button onclick="exportarPorConductor()" class="btn btn-success">📥 Exportar CSV</button>
                </div>
                <div class="card-body">
                    <div id="tabla-por-conductor" class="table-responsive">
                        <div class="loading">Cargando datos...</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let datosDetalle = [];
        let datosPorConductor = [];
        
        // Cargar conductores y vehículos para los filtros
        async function cargarFiltros() {
            try {
                const respConductores = await fetch('<?= APP_URL ?>/public/api/reportes.php?action=obtener_conductores');
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
                
                const respVehiculos = await fetch('<?= APP_URL ?>/public/api/reportes.php?action=obtener_vehiculos');
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
            document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
            
            event.target.classList.add('active');
            document.getElementById(`tab-${tab}`).style.display = 'block';
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
                const response = await fetch(`<?= APP_URL ?>/public/api/reportes.php?${params}`);
                const data = await response.json();
                
                if (data.success && data.datos) {
                    const stats = data.datos;
                    const statsHtml = `
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">📋</div>
                            <div class="stat-content">
                                <div class="stat-label">Total de Servicios</div>
                                <div class="stat-value">${stats.total_servicios || 0}</div>
                            </div>
                        </div>
                        <div class="stat-card stat-card-info">
                            <div class="stat-icon">⏱️</div>
                            <div class="stat-content">
                                <div class="stat-label">Con Tiempo de Espera</div>
                                <div class="stat-value">${stats.servicios_con_espera || 0}</div>
                            </div>
                        </div>
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">📊</div>
                            <div class="stat-content">
                                <div class="stat-label">Promedio de Espera</div>
                                <div class="stat-value">${formatearMinutos(stats.promedio_espera_minutos)}</div>
                            </div>
                        </div>
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">⬇️</div>
                            <div class="stat-content">
                                <div class="stat-label">Mínimo</div>
                                <div class="stat-value">${formatearMinutos(stats.minimo_espera_minutos)}</div>
                            </div>
                        </div>
                        <div class="stat-card stat-card-danger">
                            <div class="stat-icon">⬆️</div>
                            <div class="stat-content">
                                <div class="stat-label">Máximo</div>
                                <div class="stat-value">${formatearMinutos(stats.maximo_espera_minutos)}</div>
                            </div>
                        </div>
                        <div class="stat-card stat-card-secondary">
                            <div class="stat-icon">⏳</div>
                            <div class="stat-content">
                                <div class="stat-label">Total de Espera</div>
                                <div class="stat-value">${formatearMinutos(stats.total_espera_minutos)}</div>
                                <div class="stat-sublabel">${Math.round((stats.total_espera_minutos || 0) / 60)} horas</div>
                            </div>
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
                const response = await fetch(`<?= APP_URL ?>/public/api/reportes.php?${params}`);
                const data = await response.json();
                
                datosDetalle = data.datos || [];
                
                if (!data.success || datosDetalle.length === 0) {
                    document.getElementById('tabla-detalle').innerHTML = '<div class="no-data">No se encontraron datos</div>';
                    return;
                }
                
                let html = `
                    <table class="data-table">
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
                            <td><span class="badge badge-secondary">${servicio.placa}</span> ${servicio.vehiculo}</td>
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
                const response = await fetch(`<?= APP_URL ?>/public/api/reportes.php?${params}`);
                const data = await response.json();
                
                datosPorConductor = data.datos || [];
                
                if (!data.success || datosPorConductor.length === 0) {
                    document.getElementById('tabla-por-conductor').innerHTML = '<div class="no-data">No se encontraron datos</div>';
                    return;
                }
                
                let html = `
                    <table class="data-table">
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
            
            // Sidebar toggle
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            sidebarToggle?.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
            
            // Dropdown toggle
            const reportesToggle = document.getElementById('reportesToggle');
            const reportesMenu = document.getElementById('reportesMenu');
            
            reportesToggle?.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('open');
                reportesMenu.classList.toggle('show');
            });
        };
    </script>
</body>
</html>
