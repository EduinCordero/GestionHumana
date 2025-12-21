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