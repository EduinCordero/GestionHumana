<?php
    namespace app\models;

    if (file_exists(__DIR__ . "/../../config/pdoconfig.php")) {
        require_once __DIR__ . "/../../config/pdoconfig.php";
    }

    class mainModel {
        public function conectar() {
            $host = DB_HOST;
            $port = DB_PORT;
            $dbname = DB_NAME;
            $username = DB_USER;
            $password = DB_PASSWORD;

            $cadenaConexion = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$dbname)))";
            $conexion = oci_connect($username, $password, $cadenaConexion);
            
            if (!$conexion) {
                $e = oci_error();
                error_log("Error de conexión Oracle: " . $e['message']);
                throw new \Exception("No se pudo conectar a la base de datos");
            }

			return $conexion;
		}

        /** Convierte UTF-8 a Windows-1252 para insertar en Oracle WE8MSWIN1252 */
        protected function toOracle(string $str): string {
            return mb_convert_encoding(trim($str), 'Windows-1252', 'UTF-8');
        }

        /** Convierte Windows-1252 a UTF-8 al leer de Oracle */
        protected function fromOracle(string $str): string {
            return mb_convert_encoding($str, 'UTF-8', 'Windows-1252');
        }

        /*---------- Ejecutar consulta ----------*/
        protected function ejecutarConsulta($consulta){
            $conexion = $this->conectar();
            $query = oci_parse($conexion, $consulta);
            if (!oci_execute($query)) {
                $error = oci_error($query);
                error_log("Error en consulta: " . $error['message']);
                return false;
            }
            
            return $query;
        }

        /*---------- Ejecutar consulta y convertir resultado a UTF-8 ----------*/
        protected function ejecutarConsultaUTF8($consulta){
            $query = $this->ejecutarConsulta($consulta);
            if (!$query) return false;
            
            $rows = [];
            while ($row = oci_fetch_assoc($query)) {
                // Convertir cada campo de Windows-1252 a UTF-8
                $convertedRow = [];
                foreach ($row as $key => $value) {
                    if (is_string($value)) {
                        $convertedRow[$key] = fromOracleEncoding($value);
                    } else {
                        $convertedRow[$key] = $value;
                    }
                }
                $rows[] = $convertedRow;
            }
            oci_free_statement($query);
            return $rows;
        }

        /*---------- Ejecutar consulta y obtener un solo registro convertido ----------*/
        protected function ejecutarConsultaUnicaUTF8($consulta){
            $query = $this->ejecutarConsulta($consulta);
            if (!$query) return null;
            
            $row = oci_fetch_assoc($query);
            if ($row) {
                // Convertir cada campo de Windows-1252 a UTF-8
                $convertedRow = [];
                foreach ($row as $key => $value) {
                    if (is_string($value)) {
                        $convertedRow[$key] = fromOracleEncoding($value);
                    } else {
                        $convertedRow[$key] = $value;
                    }
                }
                oci_free_statement($query);
                return $convertedRow;
            }
            oci_free_statement($query);
            return null;
        }

        /*---------- Limpiar cadena de texto ----------*/
        function limpiarCadenas($cadena){
            $cadena = trim($cadena);
            $cadena = stripslashes($cadena);
            $cadena = str_ireplace("<script>","",$cadena);
            $cadena = str_ireplace("</script>","",$cadena);
            $cadena = str_ireplace("SELECT * FROM","",$cadena);
            $cadena = str_ireplace("DELETE FROM","",$cadena);
            $cadena = str_ireplace("?>","",$cadena);
            $cadena = str_ireplace("DROP TABLE","",$cadena);
            $cadena = str_ireplace(":","",$cadena);

            return $cadena;
        }

        //*---------- Limpiar cadena de texto ----------*/
        public function limpiarCadena($cadena){
            $palabras=["<script>","</script>","SELECT * FROM","DELETE FROM","INSERT INTO",
            "DROP TABLE","DROP DATABASE","--","?>","==","::","=","<?php","SHOW DATABASES"];

            $cadena = stripslashes($cadena);
            $cadena = trim($cadena);

            foreach($palabras as $palabra){
                $cadena = str_ireplace($palabra, "",$cadena);
            }

            $cadena = stripslashes($cadena);
            $cadena = trim($cadena);

            return $cadena;
        }

        /*---------- Verificar datos con expresión regular ----------*/
        protected function verificarDatos($filtro,$cadena){
            if(preg_match("/^".$filtro."$/", $cadena)){
                return false;
            } else {
                return true;
            }
        }

        /*---------- Eliminar registro con consulta preparada ----------*/
        protected function eliminarRegistro($conn,$tabla,$campo,$id){
            $consulta = "DELETE FROM $tabla WHERE $campo=$id";
            $sql=oci_parse($conn,$consulta);
            oci_execute($sql);
            return $sql;
        }

        /*---------- Período de evaluación activo ----------*/
        public function getPeriodoActivo() {
            $sql = "SELECT IDPERIODO, NOMBRE FROM VAADINWEB.HUMPERIODOEVALUACION
                    WHERE ESTADO = 1
                      AND TRUNC(SYSDATE) BETWEEN TRUNC(FECHAAPERTURA) AND TRUNC(FECHACIERRE)
                      AND ROWNUM = 1";
            $res = $this->ejecutarConsulta($sql);
            $row = $res ? oci_fetch_assoc($res) : null;
            if ($res) oci_free_statement($res);
            if (is_array($row)) $row['NOMBRE'] = fromOracleEncoding($row['NOMBRE'] ?? '');
            return $row ?: null;
        }

        /*---------- Listar usuarios ----------*/
        public function ListarUsuarios($conn){
            $usuarios = "SELECT A.USUARIO, A.NOMBRES||' '||A.APELLIDOS AS NOMBRE, 
                    A.EMAIL
                FROM VAADINWEB.AUDIUSUARIOS A
            ";
            $query = oci_parse($conn, $usuarios);
            oci_execute($query);
            $usuarios = [];
            while ($row = oci_fetch_assoc($query)) {
                $usuarios[] = $row;
            }
            oci_free_statement($query);
            return $usuarios;
        }
}
