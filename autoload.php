<?php
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
