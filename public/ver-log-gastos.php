<?php
// Mostrar últimas líneas del log de depuración de gastos
$logFile = sys_get_temp_dir() . '/serviciosdrive_gastos.log';
header('Content-Type: text/plain; charset=UTF-8');
if (!file_exists($logFile)) {
    echo "Log no encontrado: $logFile\n";
    exit;
}
$content = file_get_contents($logFile);
$lines = explode("\n", trim($content));
$last = array_slice($lines, -30);
foreach ($last as $line) {
    echo $line . "\n";
}
