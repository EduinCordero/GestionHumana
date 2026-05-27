<?php
// Configurar NLS_LANG para Oracle antes de cualquier conexión
putenv('NLS_LANG=SPANISH_SPAIN.WE8MSWIN1252');

// Cargar funciones de encoding para conversión UTF-8 ↔ Windows-1252
require_once __DIR__ . '/config/encoding.php';

spl_autoload_register(function($clase){
    // Convertir los namespaces en rutas de archivos
    $clase = str_replace("\\", "/", $clase);

    // Construir la ruta del archivo
    $ruta = __DIR__ . "/$clase.php";

    // Verificar si el archivo existe antes de incluirlo
    if (file_exists($ruta)) {
        require_once $ruta;
    } else {
        error_log("Autoload: No se encontró la clase '$clase' en '$ruta'");
    }
});
