<?php
/**
 * Modelo base mejorado con manejo correcto de encoding
 * Hereda de mainModel y proporciona métodos para conversión UTF-8 ↔ Oracle WE8MSWIN1252
 */

namespace app\models;

class EncodingModel extends mainModel {
    
    /**
     * Ejecuta una consulta SELECT y retorna array con UTF-8 convertido
     * 
     * @param string $sql Consulta SQL
     * @return array Array de resultados convertidos a UTF-8
     */
    protected function selectToUTF8($sql) {
        $conexion = $this->conectar();
        $query = oci_parse($conexion, $sql);
        if (!oci_execute($query)) {
            $error = oci_error($query);
            error_log("Error en consulta SELECT: " . $error['message']);
            oci_free_statement($query);
            oci_close($conexion);
            return [];
        }
        
        $rows = [];
        while ($row = oci_fetch_assoc($query)) {
            // Convertir cada campo de Windows-1252 a UTF-8
            $row = $this->convertRowFromOracle($row);
            $rows[] = $row;
        }
        
        oci_free_statement($query);
        oci_close($conexion);
        return $rows;
    }

    /**
     * Ejecuta una consulta SELECT y retorna un solo registro con UTF-8 convertido
     * 
     * @param string $sql Consulta SQL
     * @return array|null Registro convertido a UTF-8 o null si no hay resultados
     */
    protected function selectOneToUTF8($sql) {
        $conexion = $this->conectar();
        $query = oci_parse($conexion, $sql);
        if (!oci_execute($query)) {
            $error = oci_error($query);
            error_log("Error en consulta SELECT: " . $error['message']);
            oci_free_statement($query);
            oci_close($conexion);
            return null;
        }
        
        $row = oci_fetch_assoc($query);
        if ($row) {
            $row = $this->convertRowFromOracle($row);
        }
        
        oci_free_statement($query);
        oci_close($conexion);
        return $row;
    }

    /**
     * Ejecuta INSERT/UPDATE/DELETE con parámetros convertidos a Windows-1252
     * 
     * @param string $sql Consulta SQL con placeholders (:param)
     * @param array $params Array asociativo con parámetros [':param' => $valor]
     * @return bool true si fue exitoso
     */
    protected function executeWithParams($sql, $params = []) {
        $conexion = $this->conectar();
        $query = oci_parse($conexion, $sql);
        
        // Procesar parámetros: convertir strings de UTF-8 a Windows-1252
        foreach ($params as $placeholder => $value) {
            if (is_string($value)) {
                // Convertir de UTF-8 a Windows-1252
                $value = toOracleEncoding($value);
            }
            oci_bind_by_name($query, $placeholder, $params[$placeholder], -1);
            // Re-asignar el valor convertido
            $params[$placeholder] = $value;
            oci_bind_by_name($query, $placeholder, $params[$placeholder], -1);
        }
        
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        
        if (!$ok) {
            $error = oci_error($query);
            error_log("Error en ejecución: " . $error['message']);
        }
        
        oci_free_statement($query);
        oci_close($conexion);
        return $ok;
    }

    /**
     * Convierte un registro (row) de Oracle (Windows-1252) a UTF-8
     * 
     * @param array $row Registro de oci_fetch_assoc
     * @return array Registro con strings convertidos a UTF-8
     */
    protected function convertRowFromOracle($row) {
        if (!is_array($row)) {
            return $row;
        }
        
        $converted = [];
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $converted[$key] = fromOracleEncoding($value);
            } else {
                $converted[$key] = $value;
            }
        }
        return $converted;
    }

    /**
     * Convierte un registro de UTF-8 a Oracle (Windows-1252)
     * 
     * @param array $row Registro con datos en UTF-8
     * @return array Registro con strings convertidos a Windows-1252
     */
    protected function convertRowToOracle($row) {
        if (!is_array($row)) {
            return $row;
        }
        
        $converted = [];
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $converted[$key] = toOracleEncoding($value);
            } else {
                $converted[$key] = $value;
            }
        }
        return $converted;
    }
}
?>
