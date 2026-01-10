<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Servicio.php';
require_once __DIR__ . '/../../app/models/Gasto.php';
require_once __DIR__ . '/../../app/models/Usuario.php';
require_once __DIR__ . '/../../app/models/Vehiculo.php';

header('Content-Type: application/json');

// Verificar autenticación y rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$servicioModel = new Servicio();
$gastoModel = new Gasto();
$usuarioModel = new Usuario();
$vehiculoModel = new Vehiculo();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'reporte_conductor':
        $usuario_id = $_GET['usuario_id'] ?? null;
        $fecha_desde = $_GET['fecha_desde'] ?? null;
        $fecha_hasta = $_GET['fecha_hasta'] ?? null;
        
        $datos = $servicioModel->obtenerReporteConductor($usuario_id, $fecha_desde, $fecha_hasta);
        echo json_encode(['success' => true, 'datos' => $datos]);
        break;
        
    case 'reporte_vehiculo':
        $vehiculo_id = $_GET['vehiculo_id'] ?? null;
        $fecha_desde = $_GET['fecha_desde'] ?? null;
        $fecha_hasta = $_GET['fecha_hasta'] ?? null;
        
        $datos = $servicioModel->obtenerReporteVehiculo($vehiculo_id, $fecha_desde, $fecha_hasta);
        echo json_encode(['success' => true, 'datos' => $datos]);
        break;
        
    case 'reporte_fechas':
        $fecha_desde = $_GET['fecha_desde'] ?? null;
        $fecha_hasta = $_GET['fecha_hasta'] ?? null;
        
        $datos = $servicioModel->obtenerReporteFechas($fecha_desde, $fecha_hasta);
        echo json_encode(['success' => true, 'datos' => $datos]);
        break;
        
    case 'reporte_trayectos':
        $filtros = [
            'usuario_id' => $_GET['usuario_id'] ?? null,
            'vehiculo_id' => $_GET['vehiculo_id'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null
        ];
        
        $datos = $servicioModel->obtenerReporteTrayectos($filtros);
        echo json_encode(['success' => true, 'datos' => $datos]);
        break;
        
    case 'reporte_gastos':
        $filtros = [
            'usuario_id' => $_GET['usuario_id'] ?? null,
            'vehiculo_id' => $_GET['vehiculo_id'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
            'tipo_gasto' => $_GET['tipo_gasto'] ?? null,
            'limite' => $_GET['limite'] ?? 100
        ];
        
        $datos = $gastoModel->obtenerReporteGastos($filtros);
        echo json_encode(['success' => true, 'datos' => $datos]);
        break;
        
    case 'reporte_servicios':
        $filtros = [
            'usuario_id' => $_GET['usuario_id'] ?? null,
            'vehiculo_id' => $_GET['vehiculo_id'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
            'tipo_servicio' => $_GET['tipo_servicio'] ?? null,
            'limite' => $_GET['limite'] ?? 100
        ];
        
        $datos = $servicioModel->obtenerReporteServicios($filtros);
        echo json_encode(['success' => true, 'datos' => $datos]);
        break;
        
    case 'obtener_conductores':
        $conductores = $usuarioModel->obtenerTodos();
        $conductores = array_filter($conductores, function($u) {
            return $u['rol_id'] == 1 && $u['activo'] == 1;
        });
        echo json_encode(['success' => true, 'datos' => array_values($conductores)]);
        break;
        
    case 'obtener_vehiculos':
        $vehiculos = $vehiculoModel->obtenerTodosActivos();
        echo json_encode(['success' => true, 'datos' => $vehiculos]);
        break;
        
    case 'tiempos_espera':
        $filtros = [
            'usuario_id' => $_GET['usuario_id'] ?? null,
            'vehiculo_id' => $_GET['vehiculo_id'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
            'solo_con_espera' => isset($_GET['solo_con_espera']) ? (bool)$_GET['solo_con_espera'] : false,
            'limite' => $_GET['limite'] ?? 100
        ];
        
        $datos = $servicioModel->obtenerReporteTiemposEspera($filtros);
        echo json_encode(['success' => true, 'datos' => $datos]);
        break;
        
    case 'estadisticas_tiempos_espera':
        $filtros = [
            'usuario_id' => $_GET['usuario_id'] ?? null,
            'vehiculo_id' => $_GET['vehiculo_id'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null
        ];
        
        $datos = $servicioModel->obtenerEstadisticasTiemposEspera($filtros);
        echo json_encode(['success' => true, 'datos' => $datos]);
        break;
        
    case 'reporte_espera_por_conductor':
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null
        ];
        
        $datos = $servicioModel->obtenerReporteEsperaPorConductor($filtros);
        echo json_encode(['success' => true, 'datos' => $datos]);
        break;
        
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}


