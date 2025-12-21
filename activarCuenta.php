<?php
    namespace app\controllers;

    if(isset($_GET['token'])){
        $token = $_GET['token'];

        // Conexión a la base de datos
        require_once "app/models/mainModel.php";
        
        // Crear conexión
        $modelo = new \app\models\mainModel();
        $conexion = $modelo->conectar();

        // Verificar si el token es válido
        $consulta = "SELECT IDUSUARIO FROM VAADINWEB.HUMUSUARIOS WHERE TOKEN_ACTIVACION = :token AND CUENTA_ACTIVA = 0";
        $query = oci_parse($conexion, $consulta);
        oci_bind_by_name($query, ":token", $token);
        oci_execute($query);
        $resultado = oci_fetch_assoc($query);

        if($resultado){
            $idusuario = $resultado['IDUSUARIO'];

            // Activar la cuenta
            $update = "UPDATE VAADINWEB.HUMUSUARIOS SET CUENTA_ACTIVA = 1 WHERE IDUSUARIO = :idusuario";
            $queryUpdate = oci_parse($conexion, $update);
            oci_bind_by_name($queryUpdate, ":idusuario", $idusuario);
            oci_execute($queryUpdate, OCI_COMMIT_ON_SUCCESS);

            // Redirección al login
            header("Location: http://10.2.202.204/GestionHumana/login/?message=Cuenta activada con éxito");
            exit();
        } else {
            header("Location: http://10.2.202.204/GestionHumana/login/?message=Token inválido o ya usado.");
            exit();
        }
    }
?>
