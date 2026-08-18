<?php
namespace app\controllers;

use app\models\mainModel;
use app\models\competenciaModel;

/**
 * evaluarController — Controlador de evaluaciones V2
 *
 * Refactorizado para usar HUMRESPUESTA en vez de las tablas V1.
 * Lee competencias dinamicamente desde HUMCOMPETENCIA.
 * Elimina los if/elseif NC001..NC005 y los PREGUNTA1..PREGUNTA11 hardcodeados.
 */
class evaluarController extends mainModel {

    private competenciaModel $compModelo;

    public function __construct() {
        $this->compModelo = new competenciaModel();
    }

    // ── Helpers privados ─────────────────────────────────────────────────────

    public function getPeriodoActivo(): ?array {
        $sql = "SELECT IDPERIODO, NOMBRE,
                       TO_CHAR(FECHAAPERTURA,'DD/MM/YYYY') AS FECHAAPERTURA,
                       TO_CHAR(FECHACIERRE,'DD/MM/YYYY')   AS FECHACIERRE
                FROM VAADINWEB.HUMPERIODOEVALUACION
                WHERE ESTADO = 1
                  AND TRUNC(SYSDATE) BETWEEN TRUNC(FECHAAPERTURA) AND TRUNC(FECHACIERRE)
                  AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return $row ?: null;
    }

    private function sinPeriodo(): string {
        $url = APP_URL . "home/";
        ob_start();
        echo "<script>sessionStorage.setItem('sinPeriodo','1');
              (function(){ if(document.readyState==='loading'){
                  document.addEventListener('DOMContentLoaded',function(){ window.location.replace('$url'); });
              } else { window.location.replace('$url'); } })();</script>";
        return ob_get_clean();
    }

    private function getIdRol(string $nivelCargo): int {
        return $this->compModelo->getRolPorNivelCargo($nivelCargo);
    }

    /** Redirige limpiando buffer si es posible, JS como fallback */
    private function redirigir(string $url): void {
        // Limpiar output buffer si existe
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header("Location: " . $url);
            exit;
        }
        // Fallback JS si headers ya enviados
        echo "<script>window.location.href='" . $url . "';</script>";
    }

    private function mostrarAlerta(string $icon, string $title, string $text): void {
        $text = addslashes($text);
        echo "<script>Swal.fire({icon:'$icon',title:'$title',text:'$text'});</script>";
    }

    // ── Boton autoevaluacion ─────────────────────────────────────────────────

    public function autoEvaluacion(): string {
        $idEmpleado = (int)$_SESSION['idempleado'];
        $periodo    = $this->getPeriodoActivo();
        if (!$periodo) return $this->sinPeriodo();

        $confirmada = $this->compModelo->estaConfirmada(
            (int)$periodo['IDPERIODO'], $idEmpleado, $idEmpleado, 'AUTO'
        );

        ob_start();
        if ($confirmada): ?>
        <div class="flex flex-wrap mt-6 gap-4">
            <button type="button" onclick="showMessageAuto();" class="el-status-btn el-status-done">
                <?= icon('check-circle', 16) ?> Autoevaluacion Completada
            </button>
        </div>
        <?php else: ?>
        <div class="flex flex-wrap mt-6 gap-4">
            <a href="<?= APP_URL ?>autoEvaluacion/" class="el-status-btn el-status-start">
                <?= icon('rocket', 16) ?> Iniciar Autoevaluacion
            </a>
        </div>
        <?php endif;
        echo '<script>function showMessageAuto(){Swal.fire({icon:"success",title:"Excelente",text:"Ya has completado la autoevaluacion",showConfirmButton:false,timer:2000});}</script>';
        return ob_get_clean();
    }

    // ── Tabla de colaboradores a evaluar ─────────────────────────────────────

    public function listarColaboradoresEvaluar(): string {
        $idEmpleado = (int)$_SESSION['idempleado'];
        $nivelCargo = $_SESSION['nivelcargo'] ?? 'NC001';
        $periodo    = $this->getPeriodoActivo();
        if (!$periodo) return $this->sinPeriodo();

        $idPeriodo = (int)$periodo['IDPERIODO'];
        // Determinar si es líder funcional — usar columna ES_LIDER_FUNCIONAL
        $sqlLF = "SELECT ES_LIDER_FUNCIONAL FROM VAADINWEB.HUMEMPLEADOEVAL
                  WHERE IDEMPLEADO = $idEmpleado AND ACTIVO = 1 AND ROWNUM = 1";
        $resLF = $this->ejecutarConsulta($sqlLF);
        $rowLF = $resLF ? oci_fetch_assoc($resLF) : null;
        if ($resLF) oci_free_statement($resLF);
        $esLider = $rowLF && (int)$rowLF['ES_LIDER_FUNCIONAL'] === 1;

        // Colaboradores a evaluar (para líderes: sus subordinados; para colabs: su jefe)
        $personas = $this->getPersonasAEvaluar($idEmpleado, $nivelCargo, $periodo);

        // Todos los empleados buscan su jefe — no solo líderes
        $miJefe = null;
        {
            $sql = "SELECT HE_L.IDEMPLEADO,
                           NVL(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO||' '||NVL(GE.SAPELLIDO,''),
                               HE_L.NOMBRE) AS EMPLEADO,
                           HE_L.CARGO,
                           'NC002' AS CODNIVELCARGO
                    FROM VAADINWEB.HUMEMPLEADOEVAL HE_C
                    JOIN VAADINWEB.HUMEMPLEADOEVAL HE_L ON HE_C.IDEMPLEADO_EVAL = HE_L.IDEMPLEADO
                    LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE_L.IDEMPLEADO = GE.IDEMPLEADO
                    WHERE HE_C.IDEMPLEADO = $idEmpleado
                      AND HE_C.ACTIVO = 1 AND HE_L.ACTIVO = 1
                      AND ROWNUM = 1";
            $res = $this->ejecutarConsulta($sql);
            if ($res) {
                $r = oci_fetch_assoc($res);
                if ($r) {
                    $r['EMPLEADO'] = mb_convert_encoding($r['EMPLEADO'] ?? '', 'UTF-8', 'CP1252');
                    $r['CARGO']    = mb_convert_encoding($r['CARGO']    ?? '', 'UTF-8', 'CP1252');
                    $miJefe = $r;
                }
                oci_free_statement($res);
            }
        }

        ob_start(); ?>

        <?php if ($esLider && !empty($personas)): ?>
        <!-- Sección colaboradores -->
        <div style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                    color:#64748b;margin-bottom:8px;padding:0 4px;">
            Evaluación de desempeño — Colaboradores
        </div>
        <?php endif; ?>

        <table class="el-table">
            <thead>
                <tr>
                    <th>Colaborador</th><th>Progreso</th><th>Estado</th><th>Accion</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($personas as $persona):
                $idEvaluado = (int)$persona['IDEMPLEADO'];
                $nivelEval  = $persona['CODNIVELCARGO'] ?? '';
                $tipoEval   = $this->determinarTipoEval($nivelCargo, $nivelEval, $idEvaluado, $idEmpleado);
                $confirmada = $this->compModelo->estaConfirmada($idPeriodo, $idEvaluado, $idEmpleado, $tipoEval);
            ?>
            <tr>
                <td>
                    <div class="el-emp-name"><?= htmlspecialchars(mb_convert_encoding($persona['EMPLEADO'] ?? '', 'UTF-8', 'CP1252')) ?></div>
                    <div class="el-emp-cargo"><?= htmlspecialchars(mb_convert_encoding($persona['CARGO'] ?? '', 'UTF-8', 'CP1252')) ?></div>
                </td>
                <td>
                    <div class="el-prog-wrap">
                        <div class="el-prog-bar-bg">
                            <div class="el-prog-bar-fill" style="width:<?= $confirmada ? '100' : '0' ?>%"></div>
                        </div>
                        <span class="el-prog-pct"><?= $confirmada ? '100' : '0' ?>%</span>
                    </div>
                </td>
                <td>
                    <?php if ($confirmada): ?>
                        <span class="el-badge el-badge-done"><?= icon('check-circle', 13) ?> Completado</span>
                    <?php else: ?>
                        <span class="el-badge el-badge-pending"><?= icon('clock', 13) ?> Pendiente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($confirmada): ?>
                        <button class="el-action-btn el-action-done" onclick="showMessage();">Ver</button>
                        <?php
                        // Si el colaborador aplica Exp. Azul, mostrar estado
                        $sqlEAColab = "SELECT IDROL, APLICA_EXP_AZUL FROM VAADINWEB.HUMEMPLEADOEVAL
                                       WHERE IDEMPLEADO = $idEvaluado AND ACTIVO = 1 AND ROWNUM = 1";
                        $qEAColab = oci_parse($this->conectar(), $sqlEAColab);
                        oci_execute($qEAColab);
                        $rEAColab = oci_fetch_assoc($qEAColab);
                        $idRolColab  = (int)($rEAColab['IDROL']           ?? 0);
                        // IDROL=1 significa Asistencial en HUMEMPLEADOEVAL — hardcodeado intencionalmente.
                        // HUMEMPLEADOEVAL.IDROL es INDEPENDIENTE de la tabla HUMROL.
                        // No cambiar a consulta dinámica de HUMROL — son sistemas separados.
                        $colabAplicaEA = $idRolColab === 1 || (int)($rEAColab['APLICA_EXP_AZUL'] ?? 0) === 1;
                        oci_free_statement($qEAColab);
                        if ($colabAplicaEA):
                            $eaConf = $this->compModelo->estaConfirmada($idPeriodo, $idEvaluado, $idEmpleado, 'EXPERIENCIA_COLAB');
                        ?>
                        <?php if ($eaConf): ?>
                        <div style="margin-top:6px;">
                            <span style="display:inline-flex;align-items:center;gap:5px;
                                         padding:5px 12px;border-radius:20px;font-size:.72rem;
                                         font-weight:700;background:#dbeafe;color:#1e40af;
                                         border:1.5px solid #93c5fd;cursor:pointer;"
                                  onclick="showMessageEA()">
                                🔵 Exp. Azul &nbsp;✓
                            </span>
                        </div>
                        <?php else: ?>
                        <div style="margin-top:6px;">
                            <button onclick="iniciarEvaluacionEA(<?= $idEvaluado ?>, 'colab')"
                                    style="display:inline-flex;align-items:center;gap:6px;
                                           padding:6px 14px;border-radius:20px;font-size:.72rem;
                                           font-weight:700;background:linear-gradient(135deg,#0058af,#0074e0);
                                           color:#fff;border:none;cursor:pointer;
                                           box-shadow:0 2px 8px rgba(0,88,175,.35);
                                           animation:eaPulse 2s ease-in-out infinite;"
                                    title="Pendiente — Evaluación de Experiencia Azul">
                                🔵 Exp. Azul
                            </button>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="el-action-btn el-action-primary"
                                onclick="iniciarEvaluacion(<?= $idEvaluado ?>, '<?= $tipoEval ?>')">Evaluar</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($miJefe): ?>
        <!-- Sección evaluación al líder -->
        <div style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                    color:#64748b;margin:20px 0 8px;padding:0 4px;">
            Evaluación de liderazgo — Mi líder
        </div>
        <?php
            $idJefe     = (int)$miJefe['IDEMPLEADO'];
            $tipoJefe   = 'COLAB_A_LIDER'; // El jefe siempre es evaluado con COLAB_A_LIDER
            $confJefe   = $this->compModelo->estaConfirmada($idPeriodo, $idJefe, $idEmpleado, $tipoJefe);
        ?>
        <table class="el-table">
            <thead>
                <tr>
                    <th>Líder</th><th>Progreso</th><th>Estado</th><th>Accion</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div class="el-emp-name"><?= htmlspecialchars($miJefe['EMPLEADO']) ?></div>
                    <div class="el-emp-cargo"><?= htmlspecialchars($miJefe['CARGO']) ?></div>
                </td>
                <td>
                    <div class="el-prog-wrap">
                        <div class="el-prog-bar-bg">
                            <div class="el-prog-bar-fill" style="width:<?= $confJefe ? '100' : '0' ?>%"></div>
                        </div>
                        <span class="el-prog-pct"><?= $confJefe ? '100' : '0' ?>%</span>
                    </div>
                </td>
                <td>
                    <?php if ($confJefe): ?>
                        <span class="el-badge el-badge-done"><?= icon('check-circle', 13) ?> Completado</span>
                    <?php else: ?>
                        <span class="el-badge el-badge-pending"><?= icon('clock', 13) ?> Pendiente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($confJefe): ?>
                        <button class="el-action-btn el-action-done" onclick="showMessage();">Ver</button>
                        <?php
                        // Verificar si aplica Exp. Azul (colaborador asistencial)
                        $aplicaEAJefe = 0;
                        $sqlEAJ = "SELECT IDROL, APLICA_EXP_AZUL FROM VAADINWEB.HUMEMPLEADOEVAL
                                   WHERE IDEMPLEADO = $idEmpleado AND ACTIVO = 1 AND ROWNUM = 1";
                        $qEAJ   = oci_parse($this->conectar(), $sqlEAJ);
                        oci_execute($qEAJ);
                        $rEAJ   = oci_fetch_assoc($qEAJ);
                        $idRolEmpleado  = (int)($rEAJ['IDROL']           ?? 0);
                        // IDROL=1 significa Asistencial en HUMEMPLEADOEVAL — hardcodeado intencionalmente.
                        // HUMEMPLEADOEVAL.IDROL es INDEPENDIENTE de la tabla HUMROL.
                        // No cambiar a consulta dinámica de HUMROL — son sistemas separados.
                        $empleadoAplicaEA = $idRolEmpleado === 1 || (int)($rEAJ['APLICA_EXP_AZUL'] ?? 0) === 1;
                        oci_free_statement($qEAJ);

                        // Verificar también si el jefe tiene APLICA_EXP_AZUL — ambos deben aplicar
                        $sqlEAJefeRol = "SELECT IDROL, APLICA_EXP_AZUL FROM VAADINWEB.HUMEMPLEADOEVAL
                                         WHERE IDEMPLEADO = $idJefe AND ACTIVO = 1 AND ROWNUM = 1";
                        $qEAJefeRol   = oci_parse($this->conectar(), $sqlEAJefeRol);
                        oci_execute($qEAJefeRol);
                        $rEAJefeRol   = oci_fetch_assoc($qEAJefeRol);
                        // IDROL=1 significa Asistencial en HUMEMPLEADOEVAL — hardcodeado intencionalmente.
                        // HUMEMPLEADOEVAL.IDROL es INDEPENDIENTE de la tabla HUMROL.
                        // No cambiar a consulta dinámica de HUMROL — son sistemas separados.
                        $jefeAplicaEA = (int)($rEAJefeRol['IDROL'] ?? 0) === 1
                                     || (int)($rEAJefeRol['APLICA_EXP_AZUL'] ?? 0) === 1;
                        oci_free_statement($qEAJefeRol);

                        if ($empleadoAplicaEA && $jefeAplicaEA):
                            $eaConfJefe = $this->compModelo->estaConfirmada($idPeriodo, $idJefe, $idEmpleado, 'EXPERIENCIA_LIDER');
                        ?>
                        <?php if ($eaConfJefe): ?>
                        <div style="margin-top:6px;">
                            <span style="display:inline-flex;align-items:center;gap:5px;
                                         padding:5px 12px;border-radius:20px;font-size:.72rem;
                                         font-weight:700;background:#dbeafe;color:#1e40af;
                                         border:1.5px solid #93c5fd;cursor:pointer;"
                                  onclick="Swal.fire({icon:'success',title:'¡Exp. Azul completada!',text:'¡Excelente! Ya realizaste la evaluación de Experiencia Azul a tu líder.',showConfirmButton:false,timer:2500,timerProgressBar:true,confirmButtonColor:'#0058af'})">
                                🔵 Exp. Azul &nbsp;✓
                            </span>
                        </div>
                        <?php else: ?>
                        <div style="margin-top:6px;">
                            <button onclick="iniciarEvaluacionEA(<?= $idJefe ?>, 'lider')"
                                    style="display:inline-flex;align-items:center;gap:6px;
                                           padding:6px 14px;border-radius:20px;font-size:.72rem;
                                           font-weight:700;background:linear-gradient(135deg,#0058af,#0074e0);
                                           color:#fff;border:none;cursor:pointer;
                                           box-shadow:0 2px 8px rgba(0,88,175,.35);
                                           animation:eaPulse 2s ease-in-out infinite;"
                                    title="Pendiente — Evaluación de Experiencia Azul">
                                🔵 Exp. Azul
                            </button>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="el-action-btn el-action-primary"
                                onclick="iniciarEvaluacion(<?= $idJefe ?>, '<?= $tipoJefe ?>')">Evaluar</button>
                    <?php endif; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php endif; ?>
        <script>
        function iniciarEvaluacion(idEvaluado, tipoEval) {
            var rutas = {
                'LIDER_A_COLAB':     '<?= APP_URL ?>subEvaluacion',
                'COLAB_A_LIDER':     '<?= APP_URL ?>evaluacionLiderazgo',
                'EXPERIENCIA_COLAB': '<?= APP_URL ?>experienciaAzulColab',
                'EXPERIENCIA_LIDER': '<?= APP_URL ?>experienciaAzulLider'
            };
            var ruta = rutas[tipoEval] || '<?= APP_URL ?>subEvaluacion';
            var f = document.createElement('form');
            f.method = 'POST'; f.action = ruta;
            ['empleadoevaluado','tipoEval'].forEach(function(n, i) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = n;
                inp.value = i === 0 ? idEvaluado : tipoEval;
                f.appendChild(inp);
            });
            document.body.appendChild(f); f.submit();
        }
        // Animación pulso botón Exp. Azul pendiente
        const eaStyle = document.createElement('style');
        eaStyle.textContent = `@keyframes eaPulse {
            0%,100% { box-shadow: 0 2px 8px rgba(0,88,175,.35); }
            50% { box-shadow: 0 2px 18px rgba(0,88,175,.65), 0 0 0 4px rgba(0,88,175,.15); }
        }`;
        document.head.appendChild(eaStyle);

        function iniciarEvaluacionEA(idEvaluado, tipo) {
            // tipo: 'colab' = líder evalúa colaborador asistencial (P17-22)
            //       'lider' = colaborador asistencial evalúa a su líder (P23-27)
            const destino = tipo === 'colab' ? '<?= APP_URL ?>experienciaAzulColab/' : '<?= APP_URL ?>experienciaAzulLider/';
            fetch('<?= APP_URL ?>?views=admin&action=setEAEvaluado', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
                body: 'idEvaluado=' + idEvaluado
            }).then(() => {
                window.location.href = destino;
            });
        }
        function showMessageEA() {
            Swal.fire({
                icon: 'success',
                title: '¡Exp. Azul completada!',
                text: '¡Excelente! Ya realizaste la evaluación de Experiencia Azul para este colaborador.',
                confirmButtonColor: '#0058af',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        }
        function showMessage() {
            Swal.fire({icon:'success',title:'Excelente',text:'Ya has evaluado a este colaborador',showConfirmButton:false,timer:2000});
        }
        </script>
        <?php
        return ob_get_clean();
    }

    // ── Guardar evaluacion V2 ────────────────────────────────────────────────

    public function registrarEvaluacion(): void {
        $periodo = $this->getPeriodoActivo();
        if (!$periodo) {
            echo "<script>sessionStorage.setItem('sinPeriodo','1');window.location.href='" . APP_URL . "home/';</script>";
            return;
        }

        $idPeriodo   = (int)$periodo['IDPERIODO'];
        $idEvaluador = (int)$_SESSION['idempleado'];
        $tipoEval    = trim($_POST['tipoEval'] ?? 'AUTO');
        $idEvaluado  = $tipoEval === 'AUTO'
            ? $idEvaluador
            : (int)($_POST['empleadoevaluado'] ?? $idEvaluador);

        $tiposValidos = ['AUTO','LIDER_A_COLAB','COLAB_A_LIDER','EXPERIENCIA_COLAB','EXPERIENCIA_LIDER'];
        if (!in_array($tipoEval, $tiposValidos)) {
            $this->mostrarAlerta('error', 'Error', 'Tipo de evaluacion no valido.');
            return;
        }

        try {
            $nivelCargo   = $_SESSION['nivelcargo'] ?? 'NC001';
            $idRol        = $this->getIdRol($nivelCargo);
            $competencias = $this->compModelo->getCompetenciasPorRolTipo($idRol, $tipoEval);

            if (empty($competencias)) {
                $this->mostrarAlerta('error', 'Error', 'No se encontraron competencias para este tipo de evaluacion.');
                return;
            }

            $guardadas       = 0;
            $justificaciones = [];

            foreach ($competencias as $comp) {
                $idComp  = $comp['IDCOMPETENCIA'];
                $postKey = 'competencia_' . $idComp;
                $justKey = 'justificacion_' . $idComp;

                if (!isset($_POST[$postKey]) || $_POST[$postKey] === '') continue;

                $etiqueta = trim($_POST[$postKey]);
                $opciones = $this->compModelo->getOpcionesPorCompetencia($idComp);
                $idOpcion = null; $valor = null;

                foreach ($opciones as $val => $opc) {
                    if ($opc['ETIQUETA'] === $etiqueta) {
                        $idOpcion = $opc['IDOPCION']; $valor = $val; break;
                    }
                }
                if (!$idOpcion || !$valor) continue;

                $ok = $this->compModelo->guardarRespuesta(
                    $idPeriodo, $idEvaluado, $idEvaluador, $idComp, $idOpcion, $valor, $tipoEval
                );
                if ($ok) {
                    $guardadas++;
                    if (!empty($_POST[$justKey])) {
                        $justificaciones[$idComp] = trim($_POST[$justKey]);
                    }
                }
            }

            if ($guardadas === 0) {
                $this->mostrarAlerta('error', 'Error', 'No se encontraron respuestas para guardar.');
                return;
            }

            $this->compModelo->confirmarEvaluacion($idPeriodo, $idEvaluado, $idEvaluador, $tipoEval);

            foreach ($justificaciones as $idComp => $texto) {
                $idResp = $this->compModelo->getIdRespuesta($idPeriodo, $idEvaluado, $idEvaluador, $idComp, $tipoEval);
                if ($idResp) $this->compModelo->guardarJustificacion($idResp, $texto);
            }

            // ── Limpiar sesión Exp. Azul si terminó ──────────────────────────────
            if (in_array($tipoEval, ['EXPERIENCIA_LIDER', 'EXPERIENCIA_COLAB'])) {
                unset($_SESSION['ea_idempleado_evaluado']);
                $_SESSION['ea_completada'] = true;
                $this->redirigir(APP_URL . "evaluarList/");
                return;
            }

            // ── LIDER_A_COLAB → Exp. Azul solo si el colaborador es ASISTENCIAL (IDROL=1) ──
            if ($tipoEval === 'LIDER_A_COLAB') {
                $connEA = $this->conectar();
                $sqlEA  = "SELECT IDROL, NOMBRE, APLICA_EXP_AZUL FROM VAADINWEB.HUMEMPLEADOEVAL
                            WHERE IDEMPLEADO = $idEvaluado AND ACTIVO = 1 AND ROWNUM = 1";
                $qEA    = oci_parse($connEA, $sqlEA);
                oci_execute($qEA);
                $rEA    = oci_fetch_assoc($qEA);
                $idRolEval   = (int)($rEA['IDROL'] ?? 0);
                $nombreColab = mb_convert_encoding($rEA['NOMBRE'] ?? '', 'UTF-8', 'CP1252');
                oci_free_statement($qEA);

                // IDROL=1 significa Asistencial en HUMEMPLEADOEVAL — hardcodeado intencionalmente.
                // HUMEMPLEADOEVAL.IDROL es INDEPENDIENTE de la tabla HUMROL.
                // No cambiar a consulta dinámica de HUMROL — son sistemas separados.
                if ($idRolEval === 1 || (int)($rEA['APLICA_EXP_AZUL'] ?? 0) === 1) {
                    $yaExpAzul = $this->compModelo->estaConfirmada(
                        $idPeriodo, $idEvaluado, $idEvaluador, 'EXPERIENCIA_COLAB'
                    );
                    if (!$yaExpAzul) {
                        $_SESSION['ea_idempleado_evaluado'] = $idEvaluado;
                        $_SESSION['ea_pendiente']           = 'EXPERIENCIA_COLAB';
                        $_SESSION['ea_nombre_evaluado']     = $nombreColab;
                    }
                }
            }

            // ── COLAB_A_LIDER → Exp. Azul solo si el evaluador es ASISTENCIAL (IDROL=1) ──
            if ($tipoEval === 'COLAB_A_LIDER') {
                $connEA = $this->conectar();
                $sqlEA  = "SELECT IDROL, APLICA_EXP_AZUL FROM VAADINWEB.HUMEMPLEADOEVAL
                            WHERE IDEMPLEADO = $idEvaluador AND ACTIVO = 1 AND ROWNUM = 1";
                $qEA    = oci_parse($connEA, $sqlEA);
                oci_execute($qEA);
                $rEA    = oci_fetch_assoc($qEA);
                $idRolEval2 = (int)($rEA['IDROL'] ?? 0);
                oci_free_statement($qEA);

                // IDROL=1 significa Asistencial en HUMEMPLEADOEVAL — hardcodeado intencionalmente.
                // HUMEMPLEADOEVAL.IDROL es INDEPENDIENTE de la tabla HUMROL.
                // No cambiar a consulta dinámica de HUMROL — son sistemas separados.
                if ($idRolEval2 === 1 || (int)($rEA['APLICA_EXP_AZUL'] ?? 0) === 1) {
                    $yaExpAzul = $this->compModelo->estaConfirmada(
                        $idPeriodo, $idEvaluado, $idEvaluador, 'EXPERIENCIA_LIDER'
                    );
                    if (!$yaExpAzul) {
                        $_SESSION['ea_idempleado_evaluado'] = $idEvaluado;
                        $_SESSION['ea_pendiente']           = 'EXPERIENCIA_LIDER';
                        $_SESSION['ea_nombre_evaluado']     = '';
                    }
                }
            }

            // ── Siempre redirigir a evaluarList — modales se muestran allá ─────────
            $mensajes = [
                'AUTO'             => 'Tu autoevaluación fue guardada correctamente.',
                'LIDER_A_COLAB'    => 'La evaluación del colaborador fue guardada correctamente.',
                'COLAB_A_LIDER'    => 'La evaluación de liderazgo fue guardada correctamente.',
                'EXPERIENCIA_COLAB'=> 'Experiencia Azul guardada correctamente.',
                'EXPERIENCIA_LIDER'=> 'Experiencia Azul guardada correctamente.',
            ];
            if (!isset($_SESSION['ea_pendiente'])) {
                $_SESSION['eval_completada'] = $mensajes[$tipoEval] ?? 'Evaluación guardada correctamente.';
            }
            $this->redirigir(APP_URL . "evaluarList/");
            return;

        } catch (\Exception $e) {
            error_log("Error evaluarController::registrarEvaluacion: " . $e->getMessage());
            $this->mostrarAlerta('error', 'Error inesperado', 'No se pudo guardar la evaluacion. Intenta nuevamente.');
        }
    }

    // ── Helpers jerarquia ────────────────────────────────────────────────────

    private function getPersonasAEvaluar(int $idEmpleado, string $nivelCargo, array $periodo): array {
        // Líder funcional — usar columna ES_LIDER_FUNCIONAL
        $sqlLF2 = "SELECT ES_LIDER_FUNCIONAL FROM VAADINWEB.HUMEMPLEADOEVAL
                   WHERE IDEMPLEADO = $idEmpleado AND ACTIVO = 1 AND ROWNUM = 1";
        $resLF2 = $this->ejecutarConsulta($sqlLF2);
        $rowLF2 = $resLF2 ? oci_fetch_assoc($resLF2) : null;
        if ($resLF2) oci_free_statement($resLF2);
        $esLider = $rowLF2 && (int)$rowLF2['ES_LIDER_FUNCIONAL'] === 1;

        if ($esLider) {
            // Líder → evalúa a sus colaboradores directos
            $sql = "SELECT HE.IDEMPLEADO,
                           NVL(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO||' '||NVL(GE.SAPELLIDO,''),
                               HE.NOMBRE) AS EMPLEADO,
                           HE.CARGO,
                           NVL(VC.CODNIVELCARGO,'NC001') AS CODNIVELCARGO
                    FROM VAADINWEB.HUMEMPLEADOEVAL HE
                    LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
                    LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS VC
                        ON HE.IDEMPLEADO = VC.IDEMPLEADO AND VC.ESPRINCIPAL = 1
                    WHERE HE.IDEMPLEADO_EVAL = $idEmpleado
                      AND HE.ACTIVO = 1
                      AND HE.IDROL IN (1, 2, 3)
                    ORDER BY EMPLEADO ASC";
        } else {
            // Colaborador sin subordinados → su jefe va en sección "Mi Líder"
            // No retornar nada aquí para evitar duplicados
            return [];
            /*
            $sql = "SELECT HE_L.IDEMPLEADO,
                           NVL(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO||' '||NVL(GE.SAPELLIDO,''),
                               HE_L.NOMBRE) AS EMPLEADO,
                           HE_L.CARGO,
                           NVL(VC.CODNIVELCARGO,'NC002') AS CODNIVELCARGO
                    FROM VAADINWEB.HUMEMPLEADOEVAL HE_C
                    JOIN VAADINWEB.HUMEMPLEADOEVAL HE_L
                        ON HE_C.IDEMPLEADO_EVAL = HE_L.IDEMPLEADO
                    LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE_L.IDEMPLEADO = GE.IDEMPLEADO
                    LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS VC
                        ON HE_L.IDEMPLEADO = VC.IDEMPLEADO AND VC.ESPRINCIPAL = 1
                    WHERE HE_C.IDEMPLEADO = $idEmpleado
                      AND HE_C.ACTIVO = 1
                      AND HE_L.ACTIVO = 1";
            */
        }

        $res = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) $rows[] = $r;
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Determina el tipo de evaluación consultando HUMEMPLEADOEVAL
     * Si el evaluado tiene subordinados → es líder funcional → COLAB_A_LIDER
     * Si el evaluado NO tiene subordinados → es colaborador → LIDER_A_COLAB
     */
    private function determinarTipoEval(string $nivelEvaluador, string $nivelEvaluado, int $idEvaluado = 0, int $idEvaluador = 0): string {
        if ($idEvaluado > 0 && $idEvaluador > 0) {
            // Verificar si el evaluado es el jefe directo del evaluador
            // Si IDEMPLEADO_EVAL del evaluador apunta al evaluado → es su jefe → COLAB_A_LIDER
            $sql = "SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMEMPLEADOEVAL
                    WHERE IDEMPLEADO = $idEvaluador
                      AND IDEMPLEADO_EVAL = $idEvaluado
                      AND ACTIVO = 1";
            $res = $this->ejecutarConsulta($sql);
            $row = $res ? oci_fetch_assoc($res) : null;
            if ($res) oci_free_statement($res);
            if ($row && (int)$row['CNT'] > 0) return 'COLAB_A_LIDER';
            // Si no es su jefe → el evaluador evalúa a un subordinado → LIDER_A_COLAB
            return 'LIDER_A_COLAB';
        }
        // Fallback: usar nivelCargo
        $evaluadoEsLider = in_array($nivelEvaluado, ['NC002','NC003','NC004','NC005']);
        return $evaluadoEsLider ? 'COLAB_A_LIDER' : 'LIDER_A_COLAB';
    }
}
