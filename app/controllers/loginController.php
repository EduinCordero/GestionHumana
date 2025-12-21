<?php
    namespace app\controllers;
    use app\models\mainModel;

    class loginController extends mainModel{
        /*--------------------- Iniciar sesión ---------------------*/
        public function iniciarSesionController(){
            try {
                # Iniciar sesión si no está iniciada
                if (session_status() === PHP_SESSION_NONE) {
                    session_set_cookie_params([
                        'lifetime' => 0,
                        'path' => '/',
                        'domain' => $_SERVER['HTTP_HOST'],
                        'secure' => isset($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Strict'
                        ]);
                        session_name(APP_SESSION_NAME);
                        session_start();
                        session_regenerate_id(true); // Previene fijación de sesión
                }

                #Almacenar datos
                $identificacion = $this->limpiarCadena($_POST['identificacion']);
                $password = $_POST['password'];

                # Verificando campos obligatorios #
                if (empty($identificacion) || empty($password)) {
                    $this->mostrarAlerta('error', 'Ocurrió un error', 'No has llenado todos los campos obligatorios.');
                    exit();
                }

                $consulta = "SELECT
                    EM.IDEMPLEADO,
                    EM.SEXO,
                    UB.IDDEPENDENCIA,
                    HUM.IDUSUARIO,
                    HUM.IDENTIFICACION,
                    HUM.ROL,
                    PNOMBRE AS NOMBRES,
                    PAPELLIDO AS APELLIDOS,
                    CA.CODNIVELCARGO,
                    GE.DESCRIPCION AS NIVELCARGO,
                    HUM.PASSWORD
                FROM VAADINWEB.HUMUSUARIOS HUM
                INNER JOIN ZAYMAWEB.GHEMPEMPLEADOS EM ON TRIM(HUM.IDENTIFICACION) = TRIM(EM.IDENTIFICACION)
                INNER JOIN ZAYMAWEB.GHEMPUBICACION UB ON EM.IDEMPLEADO = UB.IDEMPLEADO AND UB.TIPO = 'ACTUAL'
                INNER JOIN ZAYMAWEB.GHEMPCARGOS CA ON UB.IDEMPCARGO = CA.IDEMPCARGO
                LEFT JOIN ZAYMAWEB.GEGENERICA GE ON CA.CODNIVELCARGO = GE.CODIGO AND GE.CAMPO = 'NIVELCARGO'
                WHERE ESTADOEMPLEADO = 1 AND HUM.CUENTA_ACTIVA = 1 AND HUM.IDENTIFICACION='$identificacion'";

                $resultado = $this->ejecutarConsulta($consulta);
                $usuarioEncontrado = oci_fetch_assoc($resultado);

                if($usuarioEncontrado){
                    if($usuarioEncontrado['IDENTIFICACION']==$identificacion && password_verify($password,$usuarioEncontrado['PASSWORD'])){

                        $_SESSION['id']=$usuarioEncontrado['IDUSUARIO'];
                        $_SESSION['identificacion']=$usuarioEncontrado['IDENTIFICACION'];
                        $_SESSION['nombres']=$usuarioEncontrado['NOMBRES'];
                        $_SESSION['apellidos']=$usuarioEncontrado['APELLIDOS'];
                        $_SESSION['idempleado']=$usuarioEncontrado['IDEMPLEADO'];
                        $_SESSION['sexo']=$usuarioEncontrado['SEXO'];
                        $_SESSION['nivelcargo']=$usuarioEncontrado['CODNIVELCARGO'];
                        $_SESSION['rol']=$usuarioEncontrado['ROL'];

                        if(headers_sent()){
                            echo "<script> window.location.href='".APP_URL."home/'; </script>";
                        }else{
                            header("Location: ".APP_URL."home/");
                        }

                    }else{
                        $this->mostrarAlerta('error', 'Ocurrió un error', 'Valide los datos ingresados, usuario o clave incorrectos');
                    }
                }else{
                    $this->mostrarAlerta('error', 'Ocurrió un error', 'Su cuenta no se encuentra activa, por favor activela para poder ingresar');
                }
            } catch (\Exception $e) {
                error_log("Error en iniciarSesionController: " . $e->getMessage());
                $this->mostrarAlerta('error', 'Error inesperado', 'Ocurrió un error, inténtalo de nuevo.');
            }
        }

        /*--------------------- Cerrar sesión ---------------------*/
        public function cerrarSesionControlador(){
            if (session_status() === PHP_SESSION_ACTIVE) { // Verifica si la sesión ya está activa
                session_destroy();
            }
            if (headers_sent()) {
                echo "<script>window.location.href='".APP_URL."login/';</script>";
                exit();
            } else {
                header("Location: " . APP_URL . "login/");
                exit();
            }
        }

        /*--------------------- Activar cuenta ---------------------*/
        public function activarCuentaController(){
            try {
                # Almacenar datos
                $identificacion = $this->limpiarCadena($_POST['identificacion']);
                $password = trim($_POST['password']);

                if ($identificacion != "") {
                    $consulta = "SELECT 
                        TRUNC(SYSDATE - CA.FECHAINGRESO) AS DIAS
                    FROM ZAYMAWEB.GHEMPEMPLEADOS HUM
                    INNER JOIN ZAYMAWEB.GHEMPCONTRATACIONES CA ON HUM.IDEMPLEADO = CA.IDEMPLEADO AND CA.ESTADO = 1
                    AND CA.ESPRINCIPAL = 1
                    WHERE HUM.IDENTIFICACION = :identificacion AND HUM.ESTADOEMPLEADO = 1";

                    $conexion = $this->conectar();
                    $query = oci_parse($conexion, $consulta);

                    oci_bind_by_name($query, ":identificacion", $identificacion);

                    # Ejecutar la consulta
                    oci_execute($query);
                    $resultado = oci_fetch_assoc($query);

                    if ($resultado['DIAS'] <= 90) {
                        echo "<script>
                            Swal.fire({ icon: 'error', title: 'No cumple con el tiempo', text: 'El colaborador debe tener más de 3 meses laborando para realizar la evaluación' }).then(() => {
                                window.location.href = '".APP_URL."login/';
                            });
                        </script>";
                        exit();
                    }
                }
            
                if ($identificacion != "") {
                    $consulta = "SELECT COUNT(*) AS CANTIDAD
                    FROM ZAYMAWEB.GHEMPEMPLEADOS HUM
                    INNER JOIN ZAYMAWEB.GHEMPCONTRATACIONES CA ON HUM.IDEMPLEADO = CA.IDEMPLEADO AND CA.ESTADO = 1
                    AND CA.ESPRINCIPAL = 1
                    WHERE HUM.IDENTIFICACION = :identificacion AND HUM.ESTADOEMPLEADO = 1";

                    $conexion = $this->conectar();
                    $query = oci_parse($conexion, $consulta);

                    oci_bind_by_name($query, ":identificacion", $identificacion);

                    # Ejecutar la consulta
                    oci_execute($query);
                    $resultado = oci_fetch_assoc($query);

                    if ($resultado['CANTIDAD'] == 0) {
                        echo "<script>
                            Swal.fire({ icon: 'error', title: 'Identificacion sin contrato', text: 'El numero de indentificacion no esta registrado en el modulo de gestion humana' }).then(() => {
                                window.location.href = '".APP_URL."login/';
                            });
                        </script>";
                        exit();
                    }
                }

                if ($identificacion != "") {
                    $consulta = "SELECT COUNT(*) AS CANTIDAD FROM VAADINWEB.HUMUSUARIOS WHERE IDENTIFICACION = :identificacion";
                    $conexion = $this->conectar();
                    $query = oci_parse($conexion, $consulta);

                    oci_bind_by_name($query, ":identificacion", $identificacion);

                    # Ejecutar la consulta
                    oci_execute($query);

                    # Obtener el resultado de la consulta
                    $resultado = oci_fetch_assoc($query);

                    # Verificar si el número de identificación ya está registrado
                    if ($resultado['CANTIDAD'] > 0) {
                        echo "<script>
                            Swal.fire({ icon: 'error', title: 'Cuenta Activada', text: 'Valide los datos ingresados, el número de identificación ya se encuentra activo.' }).then(() => {
                                window.location.href = '".APP_URL."login/';
                            });
                        </script>";
                        exit();
                    }
                }

                // Generar token de activación
                $token = bin2hex(random_bytes(32)); // Token seguro de 64 caracteres

                $idusuarioConsulta = "SELECT CASE WHEN IDUSUARIO IS NULL THEN 1 ELSE IDUSUARIO END AS IDUSUARIO
                FROM (
                    SELECT MAX(IDUSUARIO)+1 AS IDUSUARIO FROM VAADINWEB.HUMUSUARIOS 
                )";

                $conexion = $this->conectar();
                $queryMaxId = oci_parse($conexion, $idusuarioConsulta);
                oci_execute($queryMaxId);
                
                // Obtener el siguiente ID para el usuario
                $resultadoMaxId = oci_fetch_assoc($queryMaxId);
                $idusuario = $resultadoMaxId['IDUSUARIO'];
                
                // Encriptar la contraseña
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // Consulta para insertar el nuevo usuario
                $consulta = "INSERT INTO VAADINWEB.HUMUSUARIOS (IDUSUARIO, IDENTIFICACION, PASSWORD, ROL, TOKEN_ACTIVACION, CUENTA_ACTIVA
                ) VALUES (:id, :identificacion, :password, 'USER',:token , 0)";
                
                // Preparar la consulta
                $queryInsert = oci_parse($conexion, $consulta);
                
                // Vincular los parámetros
                oci_bind_by_name($queryInsert, ":id", $idusuario);
                oci_bind_by_name($queryInsert, ":identificacion", $identificacion);
                oci_bind_by_name($queryInsert, ":password", $hashedPassword);
                oci_bind_by_name($queryInsert, ":token", $token);
                
                // Ejecutar la consulta
                oci_execute($queryInsert, OCI_COMMIT_ON_SUCCESS);

                $enviarcorreo = "BEGIN ZAYMAWEB.PKT_NOTIFICACION_MAIL.SEND_MAIL_ACTIVARCUENTA(:identificacion); END;";
                $conexion = $this->conectar();
                $enviar = oci_parse($conexion, $enviarcorreo);
                oci_bind_by_name($enviar, ":identificacion", $identificacion);

                $consultacorreo = "SELECT EMAIL FROM ZAYMAWEB.GHEMPEMPLEADOS WHERE IDENTIFICACION = :identificacion";
                $conexion = $this->conectar();
                $query = oci_parse($conexion, $consultacorreo);
                oci_bind_by_name($query, ":identificacion", $identificacion);

                # Ejecutar la consulta
                oci_execute($query);

                # Obtener el resultado de la consulta
                $resultado = oci_fetch_assoc($query);

                // Ejecutar la consulta
                if (!oci_execute($enviar)) {
                    $e = oci_error($enviar);
                    die("Error en la ejecución del procedimiento: " . $e['message']);
                }else{
                    echo "<script>
                        Swal.fire({ 
                            icon: 'success', 
                            title: 'Revisa tu correo', 
                            text: 'Te hemos enviado un enlace de activación a tu correo electrónico " . $resultado['EMAIL'] . "' 
                        }).then(() => {
                            window.location.href = '" . APP_URL . "login/';
                        });
                    </script>";

                    oci_close($conexion);
                }
                        
            } catch (\Exception $e) {
                error_log("Error en activarCuentaController: " . $e->getMessage());
                $this->mostrarAlerta('error', 'Error inesperado', 'No se pudo activar la cuenta.');
            }
        }
		
		
	/*--------------------- Activar cuenta ---------------------*/
	public function ActualizarCuentaController(){
		try {
			# Almacenar datos
			$identificacion = $this->limpiarCadena($_POST['identificacion']);
			$password = trim($_POST['password']);
			
			// Encriptar la contraseña
			$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
			
			// Consulta para insertar el nuevo usuario
			$consulta = "UPDATE VAADINWEB.HUMUSUARIOS SET PASSWORD = :password WHERE IDENTIFICACION = :identificacion";
			
			// Preparar la consulta
			$conexion = $this->conectar();
			$queryInsert = oci_parse($conexion, $consulta);
			
			// Vincular los parámetros
			oci_bind_by_name($queryInsert, ":identificacion", $identificacion);
			oci_bind_by_name($queryInsert, ":password", $hashedPassword);
			
			// Ejecutar la consulta
			oci_execute($queryInsert, OCI_COMMIT_ON_SUCCESS);
			
			// Ejecutar la consulta
			if (!oci_execute($queryInsert)) {
				$e = oci_error($queryInsert);
				die("Error en la ejecución del procedimiento: " . $e['message']);
			}else{
				echo "<script>
					Swal.fire({ 
						icon: 'success', 
						title: 'Credenciales actualizados', 
						text: 'Hemos actualizado con exito los cambios solicitados' 
					}).then(() => {
						window.location.href = '" . APP_URL . "seguridad/';
					});
				</script>";

				oci_close($conexion);
			}
					
		} catch (\Exception $e) {
			error_log("Error en ActualizarCuentaController: " . $e->getMessage());
			$this->mostrarAlerta('error', 'Error inesperado', 'No se pudo actualizar la cuenta.');
		}
	}
	

    /*--------------------- Función para mostrar alertas ---------------------*/
    private function mostrarAlerta($icon, $title, $text) {
        echo "<script>
            Swal.fire({
                icon: '$icon',
                title: '$title',
                text: '$text'
            });
        </script>";
    }
        
}