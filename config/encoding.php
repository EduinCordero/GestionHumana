<?php
/**
 * Funciones de conversión de encoding entre UTF-8 (PHP/HTML) y WE8MSWIN1252 (Oracle)
 * 
 * BD Oracle: WE8MSWIN1252 (Windows-1252)
 * HTML: UTF-8
 * Conexión: Sin especificar charset en oci_connect (usa NLS_LANG del entorno)
 */

/**
 * Convierte de UTF-8 (HTML) a Windows-1252 (Oracle)
 * Uso: Antes de oci_bind_by_name()
 * 
 * @param string $text Texto en UTF-8 del formulario HTML
 * @return string Texto convertido a Windows-1252 para Oracle
 */
function toOracleEncoding($text) {
    if (empty($text) || !is_string($text)) {
        return $text;
    }
    
    // Convertir UTF-8 → Windows-1252
    $converted = mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
    
    // Verificar si la conversión fue exitosa
    if ($converted === false) {
        // Si falla, intentar con iconv como alternativa
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
        if ($converted === false) {
            // Como último recurso, devolver el original
            return $text;
        }
    }
    
    return $converted;
}

/**
 * Convierte de Windows-1252 (Oracle) a UTF-8 (HTML)
 * Uso: Después de oci_fetch_assoc()
 * 
 * @param string $text Texto en Windows-1252 desde Oracle
 * @return string Texto convertido a UTF-8 para mostrar en HTML
 */
function fromOracleEncoding($text) {
    if (empty($text) || !is_string($text)) {
        return $text;
    }
    
    // Convertir Windows-1252 → UTF-8
    $converted = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
    
    // Verificar si la conversión fue exitosa
    if ($converted === false) {
        // Si falla, intentar con iconv como alternativa
        $converted = @iconv('Windows-1252', 'UTF-8', $text);
        if ($converted === false) {
            // Como último recurso, devolver el original
            return $text;
        }
    }
    
    return $converted;
}

/**
 * Convierte un array recursivamente de Windows-1252 a UTF-8
 * Uso: Con resultados de oci_fetch_assoc()
 * 
 * @param array $array Array con datos de Oracle
 * @return array Array convertido a UTF-8
 */
function convertArrayFromOracle($array) {
    if (!is_array($array)) {
        return $array;
    }
    
    $converted = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $converted[$key] = convertArrayFromOracle($value);
        } else if (is_string($value)) {
            $converted[$key] = fromOracleEncoding($value);
        } else {
            $converted[$key] = $value;
        }
    }
    
    return $converted;
}

/**
 * Convierte un array recursivamente de UTF-8 a Windows-1252
 * Uso: Antes de oci_bind_by_name() con datos de formulario
 * 
 * @param array $array Array con datos del formulario (UTF-8)
 * @return array Array convertido a Windows-1252
 */
function convertArrayToOracle($array) {
    if (!is_array($array)) {
        return $array;
    }
    
    $converted = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $converted[$key] = convertArrayToOracle($value);
        } else if (is_string($value)) {
            $converted[$key] = toOracleEncoding($value);
        } else {
            $converted[$key] = $value;
        }
    }
    
    return $converted;
}

/**
 * Detecta si un texto está correctamente codificado
 * Útil para debugging
 * 
 * @param string $text Texto a verificar
 * @return array Array con información de encoding
 */
function detectEncoding($text) {
    return [
        'detected' => mb_detect_encoding($text, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true),
        'is_valid_utf8' => mb_check_encoding($text, 'UTF-8'),
        'is_valid_win1252' => mb_check_encoding($text, 'Windows-1252'),
    ];
}
?>
