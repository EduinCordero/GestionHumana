<?php
    namespace app\controllers;
    use app\models\mainModel;

    class loginController extends mainModel{

        /*--------------------- Iniciar sesión ---------------------*/
        // Sin cambios respecto al original
        public function iniciarSesionController(){
            try {
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
                        session_regenerate_id(true);
                }

                $identificacion = $this->limpiarCadena($_POST['identificacion']);
                $password = $_POST['password'];

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
                    HUM.PASSWORD,
                    HUM.ESADMIN,
                    HUM.VER_DETALLE_REP,
                    NVL(HUM.CONTRASENA_TEMP, 0) AS CONTRASENA_TEMP
                FROM VAADINWEB.HUMUSUARIOS HUM
                INNER JOIN ZAYMAWEB.GHEMPEMPLEADOS EM ON TRIM(HUM.IDENTIFICACION) = TRIM(EM.IDENTIFICACION)
                INNER JOIN ZAYMAWEB.GHEMPUBICACION UB ON EM.IDEMPLEADO = UB.IDEMPLEADO AND UB.TIPO = 'ACTUAL'
                INNER JOIN ZAYMAWEB.GHEMPCARGOS CA ON UB.IDEMPCARGO = CA.IDEMPCARGO
                LEFT JOIN ZAYMAWEB.GEGENERICA GE ON CA.CODNIVELCARGO = GE.CODIGO AND GE.CAMPO = 'NIVELCARGO'
                WHERE ESTADOEMPLEADO = 1 AND HUM.CUENTA_ACTIVA = 1 AND HUM.IDENTIFICACION = :identificacion";

                $conexion  = $this->conectar();
                $qLogin    = oci_parse($conexion, $consulta);
                oci_bind_by_name($qLogin, ':identificacion', $identificacion);
                oci_execute($qLogin);
                $usuarioEncontrado = oci_fetch_assoc($qLogin);
                oci_free_statement($qLogin);

                if($usuarioEncontrado){
                    if($usuarioEncontrado['IDENTIFICACION']==$identificacion && password_verify($password,$usuarioEncontrado['PASSWORD'])){

                        $_SESSION['id']=$usuarioEncontrado['IDUSUARIO'];
                        $_SESSION['identificacion']=$usuarioEncontrado['IDENTIFICACION'];
                        $_SESSION['nombres']=fromOracleEncoding($usuarioEncontrado['NOMBRES']);
                        $_SESSION['apellidos']=fromOracleEncoding($usuarioEncontrado['APELLIDOS']);
                        $_SESSION['idempleado']=$usuarioEncontrado['IDEMPLEADO'];
                        $_SESSION['sexo']=$usuarioEncontrado['SEXO'];
                        $_SESSION['nivelcargo']=$usuarioEncontrado['CODNIVELCARGO'];
                        $_SESSION['rol']=$usuarioEncontrado['ROL'];
                        $_SESSION['esadmin']=(int)($usuarioEncontrado['ESADMIN'] ?? 0);
                        $_SESSION['ver_detalle_rep']=(int)($usuarioEncontrado['VER_DETALLE_REP'] ?? 0);

                        // Si tiene contraseña temporal, redirigir a cambio obligatorio
                        if ((int)($usuarioEncontrado['CONTRASENA_TEMP'] ?? 0) === 1) {
                            if(headers_sent()){
                                echo "<script> window.location.href='".APP_URL."seguridad/'; </script>";
                            }else{
                                header("Location: ".APP_URL."seguridad/");
                            }
                        } else {
                            if(headers_sent()){
                                echo "<script> window.location.href='".APP_URL."home/?welcome=1'; </script>";
                            }else{
                                header("Location: ".APP_URL."home/?welcome=1");
                            }
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
        // Sin cambios respecto al original
        public function cerrarSesionControlador(){
            if (session_status() === PHP_SESSION_ACTIVE) {
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
                $identificacion = $this->limpiarCadena($_POST['identificacion']);
                $password       = trim($_POST['password']);

                // Validar campos
                if (empty($identificacion) || empty($password)) {
                    $this->mostrarAlerta('error', 'Campos requeridos', 'Ingresa tu número de identificación y una contraseña.');
                    return;
                }

                // Validar longitud mínima de contraseña
                if (strlen($password) < 6) {
                    $this->mostrarAlerta('error', 'Contraseña inválida', 'La contraseña debe tener al menos 6 caracteres.');
                    return;
                }

                $conexion = $this->conectar();

                // ── Validación 1: empleado registrado en nómina con +90 días ──
                $sqlVal = "SELECT
                    TRUNC(SYSDATE - CA.FECHAINGRESO) AS DIAS,
                    EM.IDEMPLEADO
                FROM ZAYMAWEB.GHEMPEMPLEADOS EM
                INNER JOIN ZAYMAWEB.GHEMPCONTRATACIONES CA
                    ON EM.IDEMPLEADO = CA.IDEMPLEADO
                    AND CA.ESTADO = 1 AND CA.ESPRINCIPAL = 1
                WHERE EM.IDENTIFICACION = :identificacion
                  AND EM.ESTADOEMPLEADO = 1";
                $qVal = oci_parse($conexion, $sqlVal);
                oci_bind_by_name($qVal, ':identificacion', $identificacion);
                oci_execute($qVal);
                $resVal = oci_fetch_assoc($qVal);
                oci_free_statement($qVal);

                if (!$resVal) {
                    $this->mostrarAlerta('error', 'Identificación no encontrada',
                        'El número de identificación no está registrado en el sistema de nómina.');
                    return;
                }

                if ((int)$resVal['DIAS'] <= 90) {
                    $this->mostrarAlerta('error', 'No cumple el tiempo mínimo',
                        'Debes tener más de 3 meses laborando para acceder a la plataforma.');
                    return;
                }

                // ── Validación 2: cuenta no existente aún ─────────────────────
                $sqlExiste = "SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMUSUARIOS WHERE IDENTIFICACION = :identificacion";
                $qExiste   = oci_parse($conexion, $sqlExiste);
                oci_bind_by_name($qExiste, ':identificacion', $identificacion);
                oci_execute($qExiste);
                $resExiste = oci_fetch_assoc($qExiste);
                oci_free_statement($qExiste);

                if ((int)$resExiste['CNT'] > 0) {
                    $this->mostrarAlerta('error', 'Cuenta ya existe',
                        'Ya tienes una cuenta registrada. Si olvidaste tu contraseña, contacta al área de Gestión Humana.');
                    return;
                }

                // ── Crear usuario con cuenta activa inmediatamente ─────────────
                $sqlMaxId = "SELECT NVL(MAX(IDUSUARIO), 0) + 1 AS IDUSUARIO FROM VAADINWEB.HUMUSUARIOS";
                $qMaxId   = oci_parse($conexion, $sqlMaxId);
                oci_execute($qMaxId);
                $resMaxId = oci_fetch_assoc($qMaxId);
                $idusuario = (int)$resMaxId['IDUSUARIO'];
                oci_free_statement($qMaxId);

                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                $sqlInsert = "INSERT INTO VAADINWEB.HUMUSUARIOS
                    (IDUSUARIO, IDENTIFICACION, PASSWORD, ROL, CUENTA_ACTIVA)
                    VALUES (:id, :identificacion, :password, 'USER', 1)";
                $qInsert = oci_parse($conexion, $sqlInsert);
                oci_bind_by_name($qInsert, ':id',             $idusuario);
                oci_bind_by_name($qInsert, ':identificacion', $identificacion);
                oci_bind_by_name($qInsert, ':password',       $hashedPassword);
                $ok = oci_execute($qInsert, OCI_COMMIT_ON_SUCCESS);
                oci_free_statement($qInsert);

                if ($ok) {
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: '¡Cuenta creada!',
                            text: 'Tu cuenta ha sido activada exitosamente. Ya puedes iniciar sesión.',
                            confirmButtonText: 'Iniciar sesión'
                        }).then(() => {
                            window.location.href = '" . APP_URL . "login/';
                        });
                    </script>";
                } else {
                    $this->mostrarAlerta('error', 'Error al crear la cuenta', 'Ocurrió un error. Por favor intenta nuevamente.');
                }

            } catch (\Exception $e) {
                error_log("Error en activarCuentaController: " . $e->getMessage());
                $this->mostrarAlerta('error', 'Error inesperado', 'No se pudo crear la cuenta.');
            }
        }

        /*--------------------- Resetear contraseña (Admin) ---------------------*/
        public function resetearPasswordController(){
            try {
                // Solo admins pueden resetear
                if (!isset($_SESSION['esadmin']) || $_SESSION['esadmin'] != 1) {
                    echo json_encode(['ok' => false, 'msg' => 'Sin permisos.']);
                    return;
                }

                $idUsuario      = (int)($_POST['idUsuario'] ?? 0);
                $identificacion = trim($_POST['identificacion'] ?? '');

                if (!$idUsuario || !$identificacion) {
                    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos.']);
                    return;
                }

                // Contraseña temporal = número de identificación
                $passwordTemporal = password_hash($identificacion, PASSWORD_BCRYPT);

                $conexion = $this->conectar();
                $sql = "UPDATE VAADINWEB.HUMUSUARIOS
                        SET PASSWORD = :pwd, CONTRASENA_TEMP = 1
                        WHERE IDUSUARIO = :id";
                $query = oci_parse($conexion, $sql);
                oci_bind_by_name($query, ':pwd', $passwordTemporal);
                oci_bind_by_name($query, ':id',  $idUsuario);
                $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
                oci_free_statement($query);

                echo json_encode(['ok' => $ok, 'msg' => $ok ? 'Contraseña reseteada.' : 'Error al resetear.']);

            } catch (\Exception $e) {
                error_log("Error en resetearPasswordController: " . $e->getMessage());
                echo json_encode(['ok' => false, 'msg' => 'Error inesperado.']);
            }
        }

        public function ActualizarCuentaController(){
            try {
                $identificacion = $this->limpiarCadena($_POST['identificacion']);
                $password       = trim($_POST['password']);
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                $consulta    = "UPDATE VAADINWEB.HUMUSUARIOS SET PASSWORD = :password WHERE IDENTIFICACION = :identificacion";
                $conexion    = $this->conectar();
                $queryInsert = oci_parse($conexion, $consulta);
                oci_bind_by_name($queryInsert, ":identificacion", $identificacion);
                oci_bind_by_name($queryInsert, ":password",       $hashedPassword);

                $ok = oci_execute($queryInsert, OCI_COMMIT_ON_SUCCESS);

                if ($ok) {
                    // Limpiar contraseña temporal
                    $sqlTemp = "UPDATE VAADINWEB.HUMUSUARIOS SET CONTRASENA_TEMP = 0 WHERE IDENTIFICACION = :identificacion";
                    $qTemp   = oci_parse($conexion, $sqlTemp);
                    oci_bind_by_name($qTemp, ':identificacion', $identificacion);
                    oci_execute($qTemp, OCI_COMMIT_ON_SUCCESS);
                    oci_free_statement($qTemp);

                    echo "<script>
                        Swal.fire({ 
                            icon: 'success', 
                            title: 'Credenciales actualizados', 
                            text: 'Tu contraseña ha sido actualizada exitosamente.'
                        }).then(() => {
                            window.location.href = '" . APP_URL . "home/';
                        });
                    </script>";
                    oci_close($conexion);
                }

            } catch (\Exception $e) {
                error_log("Error en ActualizarCuentaController: " . $e->getMessage());
                $this->mostrarAlerta('error', 'Error inesperado', 'No se pudo actualizar la cuenta.');
            }
        }

        /*--------------------- Exponer conexión para vistas ─────────────*/
        // Usado por login-view.php para la consulta AJAX del correo enmascarado
        public function conectarPublico() {
            return $this->conectar();
        }

        /*--------------------- Función para mostrar alertas ---------------------*/
        // Corregido: json_encode evita XSS cuando el texto contiene comillas
        private function mostrarAlerta($icon, $title, $text) {
            $data = json_encode([
                'icon'  => $icon,
                'title' => $title,
                'text'  => $text
            ], JSON_HEX_QUOT | JSON_HEX_TAG);
            echo "<script>Swal.fire($data);</script>";
        }
    }
