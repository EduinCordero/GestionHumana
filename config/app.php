<?php

// Detecta si la conexión es HTTPS
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

// Obtiene el host (IP o dominio) y puerto (si existe)
$host = $_SERVER['HTTP_HOST']; // ya incluye :8087 si aplica

// Ruta base del proyecto
$project_folder = "/GestionHumana";

// Construye URL base
$base_url = $protocol . "://" . $host . $project_folder;

// Define constantes globales
define("APP_URL", rtrim($base_url, '/') . "/");
define("APP_NAME", "GestionHumana");
define("APP_SESSION_NAME", "session");

// Zona horaria
date_default_timezone_set("America/Bogota");

