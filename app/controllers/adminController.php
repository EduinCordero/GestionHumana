<?php
namespace app\controllers;

use app\models\mainModel;
use app\models\adminModel;

class adminController extends mainModel {

    private $modelo;

    public function __construct() {
        $this->modelo = new adminModel();
    }

    public function panelAdmin() {

        // ── Seguridad: solo admins ────────────────────────────────────────────
        if (!isset($_SESSION['esadmin']) || $_SESSION['esadmin'] != 1) {
            echo "<script>window.location.href='" . APP_URL . "home/';</script>"; exit();
        }

        // ── AJAX: buscar empleado por cédula ─────────────────────────────────
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' &&
            ($_GET['action'] ?? '') === 'buscarEmpleadoCedula' &&
            isset($_GET['cedula'])
        ) {
            header('Content-Type: application/json');
            $emp = $this->modelo->buscarEmpleadoPorCedula(trim($_GET['cedula']));
            echo json_encode($emp ?: ['error' => 'No encontrado en GHEMPEMPLEADOS']);
            exit();
        }

        // ── AJAX: resetear contraseña ────────────────────────────────────────
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' &&
            ($_POST['action'] ?? '') === 'resetearPassword'
        ) {
            header('Content-Type: application/json');
            $idUsuario      = (int)($_POST['idUsuario'] ?? 0);
            $identificacion = trim($_POST['identificacion'] ?? '');
            if ($idUsuario && $identificacion) {
                $conn = $this->conectar();
                $pwdTemp = password_hash($identificacion, PASSWORD_BCRYPT);
                $uno = 1;
                $sql = "UPDATE VAADINWEB.HUMUSUARIOS SET PASSWORD = :pwd, CONTRASENA_TEMP = :tmp WHERE IDUSUARIO = :id";
                $q   = oci_parse($conn, $sql);
                oci_bind_by_name($q, ':pwd', $pwdTemp);
                oci_bind_by_name($q, ':tmp', $uno);
                oci_bind_by_name($q, ':id',  $idUsuario);
                $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
                oci_free_statement($q);
                echo json_encode(['ok' => $ok, 'msg' => $ok ? 'Contraseña reseteada.' : 'Error al resetear.']);
            } else {
                echo json_encode(['ok' => false, 'msg' => 'Datos inválidos.']);
            }
            exit();
        }
        // ─────────────────────────────────────────────────────────────────────

        // ── Exportación CSV/Excel — delega a exportar.php (fuera del framework) ─
        if (isset($_GET['action']) && $_GET['action'] === 'exportar' && isset($_GET['tipo'])) {
            $tipo    = urlencode($_GET['tipo']);
            $tabUrl  = rtrim(APP_URL, '/');
            // Redirigir a exportar.php en la raíz del proyecto — fuera del ob_start del framework
            header('Location: ' . str_replace('/GestionHumana/', '/GestionHumana/exportar.php', $tabUrl) . '?tipo=' . $tipo);
            exit();
        }

        // ────────────────────────────────────────────────────────────────────

        $activeTab          = $_POST['activeTab'] ?? $_GET['tab'] ?? 'periodos';
        $resultadosUsuarios = [];
        $searchTermUsuarios = '';
        $notifMasiva_ok     = false;
        $notifMasiva_total  = 0;
        $estadoEquipo       = [];
        $resumenEquipo      = ['total' => 0, 'completo' => 0, 'en_progreso' => 0, 'sin_iniciar' => 0];
        $feedbackActivo     = false;
        $maxObjetivos       = 3;

        // Siempre necesario — usado por POST handlers y la vista
        $periodoActivo = $this->modelo->getPeriodoActivo();

        // ── Acciones POST ─────────────────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

            // AJAX — Notificación individual (retorna JSON, sin recarga de página)
            if ($_POST['action'] === 'enviarNotifIndividualAjax') {
                header('Content-Type: application/json; charset=utf-8');
                $idColab = (int)($_POST['idColaborador'] ?? 0);

                if (!$idColab) {
                    echo json_encode(['ok' => false, 'nombre' => '', 'msg' => 'ID de colaborador inválido.']);
                    exit();
                }

                $emailColab    = $this->modelo->getEmailColaborador($idColab);
                $nombreColab   = $this->modelo->getNombreColaborador($idColab);
                $nombreDisplay = !empty($nombreColab) ? $nombreColab : "Colaborador #$idColab";

                if (empty($emailColab)) {
                    echo json_encode([
                        'ok'     => false,
                        'nombre' => $nombreDisplay,
                        'msg'    => "$nombreDisplay no tiene correo registrado en ningún sistema (HUMEMPLEADOEVAL ni GHEMPEMPLEADOS).",
                    ]);
                    exit();
                }

                $conn  = $this->conectar();
                $sql   = "BEGIN ZAYMAWEB.PKT_NOTIFICACION_MAIL.SEND_MAIL_RECORDATORIO(:destino, :ref, :tipo); END;";

                // Notificar al colaborador
                $tipoC = 'COLABORADOR';
                $stmt  = oci_parse($conn, $sql);
                oci_bind_by_name($stmt, ':destino', $idColab);
                oci_bind_by_name($stmt, ':ref',     $idColab);
                oci_bind_by_name($stmt, ':tipo',    $tipoC);
                oci_execute($stmt);
                oci_free_statement($stmt);

                // Notificar al líder solo si tiene correo
                $lider       = $this->modelo->getLiderDeColaborador($idColab);
                $liderNombre = '';
                if ($lider && !empty($lider['IDEMPLEADO']) && !empty(trim($lider['EMAIL'] ?? ''))) {
                    $idLider     = (int)$lider['IDEMPLEADO'];
                    $liderNombre = $lider['EMPLEADO'] ?? '';
                    $stmtL = oci_parse($conn, $sql);
                    $tipoL = 'LIDER';
                    oci_bind_by_name($stmtL, ':destino', $idLider);
                    oci_bind_by_name($stmtL, ':ref',     $idColab);
                    oci_bind_by_name($stmtL, ':tipo',    $tipoL);
                    oci_execute($stmtL);
                    oci_free_statement($stmtL);
                }

                $this->modelo->actualizarUltimaNotif($idColab);

                echo json_encode([
                    'ok'     => true,
                    'nombre' => $nombreDisplay,
                    'lider'  => $liderNombre,
                    'email'  => $emailColab,
                ]);
                exit();
            }

            // EDITAR MÁXIMO DE OBJETIVOS SMART (aplica a los 3 flujos)
            if ($_POST['action'] === 'setMaxObjetivos') {
                $val = max(1, min(10, (int)($_POST['valor'] ?? 3)));
                $ok  = $this->modelo->setConfigParam('MAX_OBJETIVOS', (string)$val);
                if ($ok) {
                    $_SESSION['admin_ok'] = "Límite de objetivos SMART actualizado a $val por colaborador/líder.";
                } else {
                    $_SESSION['admin_error'] = 'Error al actualizar el límite de objetivos.';
                }
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=periodos';</script>"; exit();
            }

            // TOGGLE MÓDULO FEEDBACK (habilitar / deshabilitar)
            if ($_POST['action'] === 'toggleFeedbackActivo') {
                $nuevoValor = (int)($_POST['valor'] ?? 0);
                $ok = $this->modelo->setFeedbackActivo($nuevoValor);
                if ($ok) {
                    $_SESSION['admin_ok'] = $nuevoValor
                        ? 'Módulo de Feedback habilitado. Los líderes ya pueden acceder.'
                        : 'Módulo de Feedback deshabilitado. Los líderes verán el mensaje de no disponible.';
                } else {
                    $_SESSION['admin_error'] = 'Error al actualizar el estado del módulo de Feedback.';
                }
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=periodos';</script>"; exit();
            }

            // CREAR COLABORADOR EVAL
            if ($_POST['action'] === 'crearEmpleadoEval' && isset($_POST['idempleado'])) {
                $ok = $this->modelo->crearEmpleadoEval(
                    (int)$_POST['idempleado'],
                    trim($_POST['identificacion'] ?? ''),
                    trim($_POST['nombre']         ?? ''),
                    trim($_POST['cargo']          ?? ''),
                    (int)($_POST['idRol']          ?? 2),
                    (int)($_POST['idJefe']         ?? 0),
                    (int)($_POST['aplicaExpAzul']  ?? 0),
                    trim($_POST['proceso']         ?? ''),
                    trim($_POST['email']           ?? ''),
                    trim($_POST['celular']         ?? ''),
                    trim($_POST['nombreJefe']      ?? '')
                );
                if ($ok) {
                    $_SESSION['admin_ok'] = 'Colaborador agregado correctamente.';
                } else {
                    $_SESSION['admin_error'] = 'Error al agregar. Puede que ya exista en el sistema.';
                }
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=colaboradores';</script>"; exit();
            }

            // EDITAR EMPLEADO EVAL
            if ($_POST['action'] === 'editarEmpleadoEval' && isset($_POST['idAsignacion'])) {
                $ok = $this->modelo->editarEmpleadoEval(
                    (int)$_POST['idAsignacion'],
                    (int)($_POST['idRol']              ?? 2),
                    (int)($_POST['aplicaExpAzul']      ?? 0),
                    trim($_POST['cargo']           ?? ''),
                    trim($_POST['proceso']         ?? ''),
                    trim($_POST['email']           ?? ''),
                    trim($_POST['celular']         ?? ''),
                    (int)($_POST['idJefe']         ?? 0),
                    trim($_POST['nombreJefe']      ?? ''),
                    (int)($_POST['esDirector']     ?? 0),
                    (int)($_POST['esLiderFuncional'] ?? 0)
                );
                $_SESSION['admin_ok']    = $ok ? 'Registro actualizado.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al actualizar.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=colaboradores';</script>"; exit();
            }

            // TOGGLE EMPLEADO EVAL
            if ($_POST['action'] === 'toggleEmpleadoEval' && isset($_POST['idAsignacion'])) {
                $ok = $this->modelo->toggleEmpleadoEval(
                    (int)$_POST['idAsignacion'],
                    (int)($_POST['activo'] ?? 0)
                );
                $_SESSION['admin_ok']    = $ok ? 'Estado actualizado.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al actualizar.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=colaboradores';</script>"; exit();
            }

            // CREAR PERÍODO
            if ($_POST['action'] === 'crearPeriodo') {
                $nombre    = trim($_POST['nombre']    ?? '');
                $apertura  = trim($_POST['apertura']  ?? '');
                $cierre    = trim($_POST['cierre']    ?? '');
                $obs       = trim($_POST['observacion'] ?? '');
                $idUsuario = $_SESSION['id'] ?? 0;

                if ($nombre && $apertura && $cierre) {
                    // Las fechas vienen en YYYY-MM-DD desde el input HTML date
                    // El modelo se encarga de la conversión a TO_DATE Oracle
                    $ok = $this->modelo->crearPeriodo($nombre, $apertura, $cierre, $obs, $idUsuario);
                    $_SESSION['admin_ok']    = $ok ? "Período '$nombre' creado correctamente." : '';
                    $_SESSION['admin_error'] = $ok ? '' : 'Error al crear el período. Verifica las fechas.';
                } else {
                    $_SESSION['admin_error'] = 'Completa todos los campos obligatorios.';
                }
                $activeTab = 'periodos';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=periodos';</script>"; exit();
            }

            // ACTIVAR PERÍODO
            if ($_POST['action'] === 'activarPeriodo' && isset($_POST['idPeriodo'])) {
                $ok = $this->modelo->activarPeriodo((int)$_POST['idPeriodo']);
                $_SESSION['admin_ok']    = $ok ? 'Período activado correctamente.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al activar el período.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=periodos';</script>"; exit();
            }

            // EDITAR FECHA DE CIERRE
            if ($_POST['action'] === 'editarCierrePeriodo' && isset($_POST['idPeriodo']) && isset($_POST['nuevoCierre'])) {
                $ok = $this->modelo->editarCierrePeriodo((int)$_POST['idPeriodo'], trim($_POST['nuevoCierre']));
                $_SESSION['admin_ok']    = $ok ? 'Fecha de cierre actualizada correctamente.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al actualizar la fecha de cierre.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=periodos';</script>"; exit();
            }


            // CERRAR PERÍODO
            if ($_POST['action'] === 'cerrarPeriodo' && isset($_POST['idPeriodo'])) {
                $ok = $this->modelo->cerrarPeriodo((int)$_POST['idPeriodo']);
                $_SESSION['admin_ok']    = $ok ? 'Período cerrado correctamente.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al cerrar el período.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=periodos';</script>"; exit();
            }

            // BUSCAR USUARIOS
            if ($_POST['action'] === 'buscarUsuarios') {
                $searchTermUsuarios = trim($_POST['searchTerm'] ?? '');
                if (strlen($searchTermUsuarios) >= 2) {
                    $resultadosUsuarios = $this->modelo->buscarUsuarios($searchTermUsuarios);
                }
                $activeTab = 'usuarios';
            }

            // ACTUALIZAR PERMISOS
            if ($_POST['action'] === 'actualizarPermisos' && isset($_POST['idUsuario'])) {
                $idUsuario    = (int)$_POST['idUsuario'];
                $esAdmin      = isset($_POST['esadmin'])      ? 1 : 0;
                $verDetalle   = isset($_POST['ver_detalle'])  ? 1 : 0;
                $ok = $this->modelo->actualizarPermisos($idUsuario, $esAdmin, $verDetalle);
                $_SESSION['admin_ok']    = $ok ? 'Permisos actualizados correctamente.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al actualizar permisos.';
                $searchTermUsuarios = trim($_POST['searchTerm'] ?? '');
                if ($searchTermUsuarios) {
                    $resultadosUsuarios = $this->modelo->buscarUsuarios($searchTermUsuarios);
                }
                $activeTab = 'usuarios';
            }

            // CREAR OBJETIVO
            if ($_POST['action'] === 'crearObjetivo') {
                $numComp      = (int)($_POST['num_competencia'] ?? 0);
                $calificacion = (int)($_POST['calificacion']    ?? 0);
                $objetivo     = trim($_POST['objetivo']         ?? '');
                $idUsuario    = $_SESSION['id'] ?? 0;
                if ($numComp && $calificacion && $objetivo) {
                    $ok = $this->modelo->crearObjetivo(
                        $numComp, $calificacion, $objetivo, $idUsuario,
                        trim($_POST['modelo']          ?? ''),
                        trim($_POST['indicador']        ?? ''),
                        trim($_POST['meta']             ?? ''),
                        trim($_POST['plazo']            ?? ''),
                        trim($_POST['evidencia']        ?? ''),
                        trim($_POST['seguimiento']      ?? ''),
                        trim($_POST['uso_recomendado']  ?? '')
                    );
                    $_SESSION['admin_ok']    = $ok ? 'Objetivo creado correctamente.' : '';
                    $_SESSION['admin_error'] = $ok ? '' : 'Error al crear el objetivo.';
                } else {
                    $_SESSION['admin_error'] = 'Completa todos los campos obligatorios.';
                }
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=objetivos';</script>"; exit();
            }

            // EDITAR OBJETIVO
            if ($_POST['action'] === 'editarObjetivo' && isset($_POST['idObjetivo'])) {
                $numComp      = (int)($_POST['num_competencia'] ?? 0);
                $calificacion = (int)($_POST['calificacion']    ?? 0);
                $objetivo     = trim($_POST['objetivo']         ?? '');
                if ($numComp && $calificacion && $objetivo) {
                    $ok = $this->modelo->editarObjetivo(
                        (int)$_POST['idObjetivo'],
                        $numComp, $calificacion, $objetivo,
                        trim($_POST['modelo']          ?? ''),
                        trim($_POST['indicador']        ?? ''),
                        trim($_POST['meta']             ?? ''),
                        trim($_POST['plazo']            ?? ''),
                        trim($_POST['evidencia']        ?? ''),
                        trim($_POST['seguimiento']      ?? ''),
                        trim($_POST['uso_recomendado']  ?? '')
                    );
                    $_SESSION['admin_ok']    = $ok ? 'Objetivo actualizado correctamente.' : '';
                    $_SESSION['admin_error'] = $ok ? '' : 'Error al actualizar el objetivo.';
                } else {
                    $_SESSION['admin_error'] = 'Completa todos los campos obligatorios.';
                }
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=objetivos';</script>"; exit();
            }

            // EDITAR COMPETENCIA
            if ($_POST['action'] === 'editarCompetencia' && isset($_POST['idCompetencia'])) {
                $ok = $this->modelo->editarCompetencia(
                    (int)$_POST['idCompetencia'],
                    trim($_POST['nombre']   ?? ''),
                    trim($_POST['pregunta'] ?? '')
                );
                $_SESSION['admin_ok']    = $ok ? 'Competencia actualizada.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al actualizar.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=competencias';</script>"; exit();
            }

            // TOGGLE COMPETENCIA
            if ($_POST['action'] === 'toggleCompetencia' && isset($_POST['idCompetencia'])) {
                $ok = $this->modelo->toggleCompetencia(
                    (int)$_POST['idCompetencia'],
                    (int)($_POST['activo'] ?? 0)
                );
                $_SESSION['admin_ok']    = $ok ? 'Estado actualizado.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al actualizar.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=competencias';</script>"; exit();
            }

            // EDITAR OPCIÓN COMPETENCIA (tooltip)
            if ($_POST['action'] === 'editarOpcionComp' && isset($_POST['idOpcionComp'])) {
                $idOpc = (int)$_POST['idOpcionComp'];
                $desc  = trim($_POST['descripcion'] ?? '');
                $ok = $this->modelo->editarOpcionCompetencia($idOpc, $desc);
                $_SESSION['admin_ok']    = $ok ? 'Descripción actualizada.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al actualizar.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=competencias';</script>"; exit();
            }

            // TOGGLE OBJETIVO (activar/desactivar)
            if ($_POST['action'] === 'toggleObjetivo' && isset($_POST['idObjetivo'])) {
                $ok = $this->modelo->toggleObjetivo((int)$_POST['idObjetivo'], (int)($_POST['activo'] ?? 0));
                $_SESSION['admin_ok']    = $ok ? 'Objetivo actualizado.' : '';
                $_SESSION['admin_error'] = $ok ? '' : 'Error al actualizar el objetivo.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=objetivos';</script>"; exit();
            }

            // NOTIFICACIÓN MASIVA
            if ($_POST['action'] === 'enviarNotifMasiva') {
                $estadoEquipo = $this->modelo->getEstadoEquipoCompleto($periodoActivo);
                $conn         = $this->conectar();
                $sql          = "BEGIN ZAYMAWEB.PKT_NOTIFICACION_MAIL.SEND_MAIL_RECORDATORIO(:destino, :ref, :tipo); END;";
                $enviados     = 0;
                $sinCorreo    = [];

                foreach ($estadoEquipo as $col) {
                    if ($col['ESTADO'] === 'completo') continue; // ya terminó, no notificar

                    $idColab    = (int)$col['IDEMPLEADO'];
                    $emailColab = trim($col['EMAIL'] ?? ''); // NVL(HE.EMAIL, GE.EMAIL) ya aplicado en la consulta

                    // Omitir si no tiene correo en ningún sistema
                    if (empty($emailColab)) {
                        $sinCorreo[] = $col['EMPLEADO'];
                        continue;
                    }

                    // Notificar al colaborador
                    $stmt  = oci_parse($conn, $sql);
                    $tipoC = 'COLABORADOR';
                    oci_bind_by_name($stmt, ':destino', $idColab);
                    oci_bind_by_name($stmt, ':ref',     $idColab);
                    oci_bind_by_name($stmt, ':tipo',    $tipoC);
                    oci_execute($stmt);
                    oci_free_statement($stmt);

                    // Notificar al líder solo si tiene correo registrado
                    $lider = $this->modelo->getLiderDeColaborador($idColab);
                    if ($lider && !empty($lider['IDEMPLEADO']) && !empty(trim($lider['EMAIL'] ?? ''))) {
                        $idLider = (int)$lider['IDEMPLEADO'];
                        $stmtL   = oci_parse($conn, $sql);
                        $tipoL   = 'LIDER';
                        oci_bind_by_name($stmtL, ':destino', $idLider);
                        oci_bind_by_name($stmtL, ':ref',     $idColab);
                        oci_bind_by_name($stmtL, ':tipo',    $tipoL);
                        oci_execute($stmtL);
                        oci_free_statement($stmtL);
                    }
                    // Guardar fecha último envío
                    $this->modelo->actualizarUltimaNotif($idColab);
                    $enviados++;
                }

                $resumenEquipo     = $this->modelo->getResumenEstado($estadoEquipo);
                $notifMasiva_ok    = true;
                $notifMasiva_total = $enviados;
                $notifSinCorreo    = $sinCorreo;
                $activeTab         = 'seguimiento';
            }

            // NOTIFICACIÓN INDIVIDUAL
            if ($_POST['action'] === 'enviarNotifIndividual' && isset($_POST['idColaborador'])) {
                $idColab = (int)$_POST['idColaborador'];

                // Validar correo del colaborador antes de llamar al paquete Oracle
                $emailColab    = $this->modelo->getEmailColaborador($idColab);
                $nombreColab   = $this->modelo->getNombreColaborador($idColab);
                $nombreDisplay = !empty($nombreColab) ? $nombreColab : "Colaborador #$idColab";
                if (empty($emailColab)) {
                    $_SESSION['admin_error'] = "$nombreDisplay no tiene correo registrado en ningún sistema (HUMEMPLEADOEVAL ni GHEMPEMPLEADOS). No se envió el recordatorio.";
                    echo "<script>window.location.href='" . APP_URL . "admin/?tab=seguimiento';</script>"; exit();
                }

                $conn = $this->conectar();
                $sql  = "BEGIN ZAYMAWEB.PKT_NOTIFICACION_MAIL.SEND_MAIL_RECORDATORIO(:destino, :ref, :tipo); END;";

                $tipoC = 'COLABORADOR';
                $stmt  = oci_parse($conn, $sql);
                oci_bind_by_name($stmt, ':destino', $idColab);
                oci_bind_by_name($stmt, ':ref',     $idColab);
                oci_bind_by_name($stmt, ':tipo',    $tipoC);
                oci_execute($stmt);
                oci_free_statement($stmt);

                // Notificar al líder solo si tiene correo registrado
                $lider = $this->modelo->getLiderDeColaborador($idColab);
                if ($lider && !empty($lider['IDEMPLEADO']) && !empty(trim($lider['EMAIL'] ?? ''))) {
                    $idLider = (int)$lider['IDEMPLEADO'];
                    $stmtL   = oci_parse($conn, $sql);
                    $tipoL   = 'LIDER';
                    oci_bind_by_name($stmtL, ':destino', $idLider);
                    oci_bind_by_name($stmtL, ':ref',     $idColab);
                    oci_bind_by_name($stmtL, ':tipo',    $tipoL);
                    oci_execute($stmtL);
                    oci_free_statement($stmtL);
                }

                // Guardar fecha último envío
                $this->modelo->actualizarUltimaNotif($idColab);
                $_SESSION['admin_ok'] = 'Recordatorio enviado al colaborador y su líder.';
                echo "<script>window.location.href='" . APP_URL . "admin/?tab=seguimiento';</script>"; exit();
            }
        }

        // ── Carga condicional por pestaña ─────────────────────────────────────
        $periodos      = [];
        $avanceProceso = [];
        $promedioComp  = [];
        $promedioLider = [];
        $dictLider     = [];
        $lideresEval   = [];
        $buscarEval    = '';
        $soloActivos   = (int)($_GET['soloActivos'] ?? 1);
        $empleadosEval = [];
        $competencias  = [];
        $opcionesComp  = [];
        $objetivos     = [];
        $dictColab     = [];

        switch ($activeTab) {
            case 'periodos':
                $periodos       = $this->modelo->getPeriodos();
                $feedbackActivo = $this->modelo->getFeedbackActivo();
                $maxObjetivos   = (int)$this->modelo->getConfigParam('MAX_OBJETIVOS', '3');
                break;

            case 'seguimiento':
                if (empty($estadoEquipo)) {
                    $estadoEquipo  = $this->modelo->getEstadoEquipoCompleto($periodoActivo);
                    $resumenEquipo = $this->modelo->getResumenEstado($estadoEquipo);
                }
                break;

            case 'avance':
                $avanceProceso = $this->modelo->getAvancePorProceso($periodoActivo);
                break;

            case 'promedios':
                $promedioComp  = $this->modelo->getPromedioCompetenciasPorProceso($periodoActivo);
                $promedioLider = $this->modelo->getPromedioLiderazgoPorProceso($periodoActivo);
                $dictColab     = \app\models\reportModel::getDictColaborador();
                $cmInst = new \app\models\competenciaModel();
                foreach ($cmInst->getCompetencias() as $comp) {
                    $np = (int)$comp['NUM_PREGUNTA'];
                    if ($np >= 12 && $np <= 16) $dictLider[$np] = $comp['NOMBRE'];
                }
                break;

            case 'usuarios':
                break;

            case 'objetivos':
                $objetivos = $this->modelo->getObjetivos();
                $cmInst    = new \app\models\competenciaModel();
                $dictColab = $cmInst->getDictCompetencias(); // P1-P16
                break;

            case 'competencias':
                $competencias = $this->modelo->getCompetencias();
                $opcionesComp = $this->modelo->getTodasOpcionesCompetencias();
                break;

            case 'colaboradores':
                $buscarEval    = trim($_POST['buscarEval'] ?? $_GET['buscarEval'] ?? '');
                $lideresEval   = $this->modelo->getLideresEval();
                $empleadosEval = $this->modelo->getEmpleadosEval($buscarEval, $soloActivos);
                break;
        }

        $mensajeOk  = $_SESSION['admin_ok']    ?? '';
        $mensajeErr = $_SESSION['admin_error'] ?? '';
        unset($_SESSION['admin_ok'], $_SESSION['admin_error']);

        $data = [
            'activeTab'          => $activeTab,
            'mensajeOk'          => $mensajeOk,
            'mensajeErr'         => $mensajeErr,
            'periodoActivo'      => $periodoActivo,
            'periodos'           => $periodos,
            'avanceProceso'      => $avanceProceso,
            'promedioComp'       => $promedioComp,
            'estadoEquipo'       => $estadoEquipo,
            'resumenEquipo'      => $resumenEquipo,
            'resultadosUsuarios' => $resultadosUsuarios,
            'searchTermUsuarios' => $searchTermUsuarios,
            'notifMasiva_ok'     => $notifMasiva_ok,
            'notifMasiva_total'  => $notifMasiva_total,
            'notifSinCorreo'     => $notifSinCorreo ?? [],
            'feedbackActivo'     => $feedbackActivo,
            'maxObjetivos'       => $maxObjetivos,
            'dictColab'          => $dictColab,
            'objetivos'          => $objetivos,
            'empleadosEval'      => $empleadosEval,
            'lideresEval'        => $lideresEval,
            'buscarEval'         => $buscarEval,
            'soloActivos'        => $soloActivos,
            'competencias'       => $competencias,
            'promedioLider'      => $promedioLider,
            'dictLider'          => $dictLider,
            'opcionesComp'       => $opcionesComp,
        ];

        extract($data);
        require_once "./app/views/content/admin-view.php";
    }
}
?>
