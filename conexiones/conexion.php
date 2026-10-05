<?php 

require_once __DIR__.'/config.php'; 
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conexion = mysqli_connect($db_host, $db_usuario, $db_password, $db_nombre);
    mysqli_set_charset($conexion, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    exit('No se pudo conectar a la base de datos.');
}
