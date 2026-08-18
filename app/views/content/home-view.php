<?php
use app\models\reportModel;
use app\models\acuerdoModel;

$modelo        = new reportModel();
$acuerdoModelo = new acuerdoModel();

$idempleado = (int)$_SESSION['idempleado'];
$nivelCargo = $_SESSION['nivelcargo'] ?? 'NC001';
$nombres    = $_SESSION['nombres'] ?? '';
$apellidos  = $_SESSION['apellidos'] ?? '';
$esAdmin    = (int)($_SESSION['esadmin'] ?? 0);

// Animación de bienvenida post-login
$showWelcome = ($_GET['welcome'] ?? '') === '1';

// Cargo desde HUMEMPLEADOEVAL (administrado por el director desde el panel)
$cargoReal = '';
try {
    $connMethod = new ReflectionMethod($modelo, 'conectar');
    $connMethod->setAccessible(true);
    $conn = $connMethod->invoke($modelo);
    $sqlCargo = "SELECT CARGO FROM VAADINWEB.HUMEMPLEADOEVAL WHERE IDEMPLEADO = :id AND ROWNUM = 1";
    $qCargo   = oci_parse($conn, $sqlCargo);
    oci_bind_by_name($qCargo, ':id', $idempleado);
    oci_execute($qCargo);
    $rCargo   = oci_fetch_assoc($qCargo);
    $cargoReal = fromOracleEncoding($rCargo['CARGO'] ?? '');
    oci_free_statement($qCargo);
} catch (Exception $e) {
    $cargoReal = '';
}

// Período activo
$periodoActivo = $modelo->getPeriodoActivo();
$idPeriodo     = $periodoActivo ? (int)$periodoActivo['IDPERIODO'] : 0;

// Autoevaluación
$autoEval  = $modelo->getAutoEvaluacion($idempleado, $periodoActivo);
$tieneAuto = !empty($autoEval);

// Fue evaluado
$recibidasColab = $modelo->getEvaluacionesRecibidasColaborador($idempleado, $periodoActivo);
$fueEvaluado    = !empty($recibidasColab);

// Evaluó a otros — contar realizadas vs total a evaluar
$realizadasColab = $modelo->getEvaluacionesRealizadasColaborador($idempleado, $periodoActivo);
$realizadasLider = $modelo->getEvaluacionesRealizadasLiderazgo($idempleado, $periodoActivo);
$totalRealizadas = count($realizadasColab) + count($realizadasLider);

// Total de personas que debe evaluar
$esLider = in_array($nivelCargo, ['NC002','NC003','NC004','NC005']);
$equipo  = [];
$equipoTotal    = 0;
$equipoCompleto = 0;

if ($esLider) {
    $equipo         = $modelo->getEquipoACargo($idempleado, $nivelCargo, $periodoActivo);
    $equipoTotal    = count($equipo);
    $equipoCompleto = count(array_filter($equipo, fn($c) => $c['ESTADO'] === 'completo'));
    // El líder evalúa a su equipo Y a su propio líder/superior
    $totalAEvaluar  = $equipoTotal + 1;
} else {
    // Colaboradores evalúan a su líder (1 persona)
    $totalAEvaluar = 1;
}

$evaluoAOtros   = $totalRealizadas > 0;
$evaluoCompleto = ($totalAEvaluar > 0 && $totalRealizadas >= $totalAEvaluar);
$evaluoLabel    = $totalAEvaluar > 0 ? "$totalRealizadas/$totalAEvaluar" : ($evaluoAOtros ? 'Realizado' : 'Pendiente');

// Plan de mejora (Flujo 1 — colaborador)
$acuerdos          = $acuerdoModelo->getAcuerdosColaborador($idempleado, $idPeriodo);
$totalAcuerdos     = count($acuerdos);
$aprobados         = count(array_filter($acuerdos, fn($a) => $a['ESTADO'] === 'APROBADO'));
$respondidos       = count(array_filter($acuerdos, fn($a) => in_array($a['ESTADO'], ['RESPONDIDO','APROBADO'])));
$pendientesAcuerdo = $totalAcuerdos - $respondidos;

// Plan de mejora EA (Flujo 3 — solo visible si el colaborador ya firmó)
$acuerdosEAHome    = $idPeriodo ? $acuerdoModelo->getAcuerdosColaboradorEA($idempleado, $idPeriodo) : [];
$totalEAHome       = count($acuerdosEAHome);
$firmadoEAHome     = $totalEAHome > 0 && (int)($acuerdosEAHome[0]['FIRMADO_COLAB'] ?? 0) === 1;
$aprobadosEAHome   = $firmadoEAHome ? count(array_filter($acuerdosEAHome, fn($a) => $a['ESTADO'] === 'APROBADO'))   : 0;
$respondidosEAHome = $firmadoEAHome ? count(array_filter($acuerdosEAHome, fn($a) => in_array($a['ESTADO'], ['RESPONDIDO','APROBADO']))) : 0;
$pendientesEAHome  = $firmadoEAHome ? ($totalEAHome - $respondidosEAHome) : 0;

// Estado del feedback para el colaborador (2 estados: reunión futura / feedback completo)
$feedbackEstado = null;
if ($idPeriodo) {
    $fbModel        = new \app\models\feedbackModel();
    $feedbackEstado = $fbModel->getFeedbackEstado($idempleado, $idPeriodo);
}

// Estado del feedback Experiencia Azul (TIPO_FEEDBACK=3) — para colaboradores asistenciales
$feedbackEAEstado = null;
if ($idPeriodo) {
    try {
        $fbModelEA    = new \app\models\feedbackModel();
        $feedbackEAEstado = $fbModelEA->getFeedbackEAEstado($idempleado, $idPeriodo);
    } catch (Exception $e) {
        $feedbackEAEstado = null;
    }
}

// Estado del feedback de liderazgo (TIPO_FEEDBACK=2) — banner para líderes con feedback pendiente de firma
$feedbackDirEstado = null;
if ($esLider && $idPeriodo) {
    try {
        $fbModelDir    = new \app\models\feedbackModel();
        $feedbackDirEstado = $fbModelDir->getFeedbackDirEstadoLider($idempleado, $idPeriodo);
    } catch (Exception $e) {
        $feedbackDirEstado = null;
    }
}

// Es líder funcional
$esLiderFuncional = false;
if ($idPeriodo) {
    try {
        $connMethod = new ReflectionMethod($modelo, 'conectar');
        $connMethod->setAccessible(true);
        $conn  = $connMethod->invoke($modelo);
        $sqlLF = "SELECT ES_LIDER_FUNCIONAL FROM VAADINWEB.HUMEMPLEADOEVAL
                  WHERE IDEMPLEADO = :id AND ACTIVO = 1 AND ROWNUM = 1";
        $qLF   = oci_parse($conn, $sqlLF);
        oci_bind_by_name($qLF, ':id', $idempleado);
        if (oci_execute($qLF)) {
            $rowLF = oci_fetch_assoc($qLF);
            $esLiderFuncional = $rowLF && (int)$rowLF['ES_LIDER_FUNCIONAL'] === 1;
        }
        oci_free_statement($qLF);
    } catch (Exception $e) {
        $esLiderFuncional = false;
    }
}

// Acuerdos P12-P16 del líder usando feedbackModel (devuelve FIRMADO_COLAB correcto)
$acuerdosLiderHome = [];
if ($esLiderFuncional && $idPeriodo) {
    $fbModelHome       = new app\models\feedbackModel();
    $acuerdosLiderHome = $fbModelHome->getAcuerdosLider($idempleado, $idPeriodo);
}

// Feedback director pendiente (HUMFEEDBACK con TIPO_FEEDBACK=2 y FIRMADO_LIDER=0)
$feedbackDirPend = false;
if ($esLiderFuncional && $idPeriodo) {
    try {
        $connMethod2 = new ReflectionMethod($modelo, 'conectar');
        $connMethod2->setAccessible(true);
        $conn2   = $connMethod2->invoke($modelo);
        $sqlDir  = "SELECT COUNT(*) AS TOTAL FROM VAADINWEB.HUMFEEDBACK
                    WHERE IDEMPLEADO    = :id
                      AND IDPERIODO     = :periodo
                      AND TIPO_FEEDBACK = 2
                      AND (FIRMADO_LIDER IS NULL OR FIRMADO_LIDER = 0)";
        $qDir    = oci_parse($conn2, $sqlDir);
        oci_bind_by_name($qDir, ':id',      $idempleado);
        oci_bind_by_name($qDir, ':periodo', $idPeriodo);
        if (oci_execute($qDir)) {
            $rowDir = oci_fetch_assoc($qDir);
            $feedbackDirPend = (int)($rowDir['TOTAL'] ?? 0) > 0;
        }
        oci_free_statement($qDir);
    } catch (Exception $e) {
        $feedbackDirPend = false;
    }
}

// Feedbacks agendados con firma pendiente (para líderes/directores)
$feedbacksPendLider = [];
if ($esLiderFuncional && $idPeriodo) {
    try {
        $fbPendModel        = new \app\models\feedbackModel();
        $feedbacksPendLider = $fbPendModel->getFeedbacksPendientesLider($idempleado, $idPeriodo);
    } catch (Exception $e) {
        $feedbacksPendLider = [];
    }
}

// Progreso personal
$completados = (int)$tieneAuto + (int)$fueEvaluado + (int)$evaluoCompleto;
$pctPersonal = round($completados / 3 * 100);

// Período
$fechaCierre   = $periodoActivo ? $periodoActivo['FECHACIERRE'] : null;
$diasRestantes = null;
if ($fechaCierre) {
    $hoy = new DateTime();
    $fin = DateTime::createFromFormat('d/m/Y H:i:s', $fechaCierre . ' 23:59:59');
    if ($fin) {
        $diasRestantes = $fin >= $hoy ? (int)$hoy->diff($fin)->days : -1;
    }
}
if ($diasRestantes === null)  { $colorP='#64748b'; $labelP='Sin período activo'; }
elseif ($diasRestantes < 0)   { $colorP='#dc2626'; $labelP='Período cerrado'; }
elseif ($diasRestantes <= 3)  { $colorP='#f97316'; $labelP="Cierra en $diasRestantes día(s)"; }
elseif ($diasRestantes <= 7)  { $colorP='#eab308'; $labelP="Cierra en $diasRestantes días"; }
else                          { $colorP='#0058af'; $labelP="$diasRestantes días restantes"; }

// Saludo
$hora   = (int)date('H');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');

// Frase motivacional aleatoria
$frases = [
    'Tu compromiso nos inspira, tu crecimiento nos fortalece.',
    'Cada evaluación es una oportunidad para crecer juntos.',
    'Tu impacto en la Clínica Zayma trasciende cada día.',
    'Servir con excelencia y calidez humana es nuestra misión.',
];
$frase = $frases[array_rand($frases)];
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap');

.hm-root {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    padding: 68px 20px 56px;
    max-width: 1280px;
    margin: 0 auto;
    color: #1e293b;
}

/* ── HERO ── */
.hm-hero {
    background: linear-gradient(135deg, #0058af 0%, #1a73e8 60%, #2563eb 100%);
    border-radius: 20px;
    padding: 36px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,88,175,0.25);
}
.hm-hero::before {
    content:'';
    position:absolute;
    width:300px;height:300px;
    border-radius:50%;
    background:rgba(255,255,255,0.06);
    top:-80px;right:-60px;
    pointer-events:none;
}
.hm-hero::after {
    content:'';
    position:absolute;
    width:180px;height:180px;
    border-radius:50%;
    background:rgba(255,255,255,0.04);
    bottom:-50px;left:200px;
    pointer-events:none;
}
.hm-hero-left { z-index:1; flex:1; }
.hm-greeting {
    font-size:11px;letter-spacing:2px;text-transform:uppercase;
    color:rgba(255,255,255,0.55);margin-bottom:6px;
}
.hm-username {
    font-size:28px;font-weight:600;color:#fff;
    line-height:1.2;margin-bottom:4px;
}
.hm-cargo {
    font-size:14px;color:rgba(255,255,255,0.65);margin-bottom:12px;
}
.hm-frase {
    font-size:12px;color:rgba(255,255,255,0.5);
    font-style:italic;max-width:420px;line-height:1.5;
    border-left:2px solid rgba(255,255,255,0.25);
    padding-left:10px;
}
.hm-hero-right {
    z-index:1;display:flex;flex-direction:column;
    align-items:flex-end;gap:12px;flex-shrink:0;
}
.hm-period-badge {
    display:inline-flex;align-items:center;gap:7px;
    border-radius:99px;padding:6px 14px;
    font-size:12px;font-weight:500;
    background:rgba(255,255,255,0.15);
    border:1px solid rgba(255,255,255,0.25);
    color:#fff;
}
.hm-period-dot {
    width:7px;height:7px;border-radius:50%;
    animation:hmPulse 2s infinite;
}
@keyframes hmPulse{0%,100%{opacity:1;}50%{opacity:0.35;}}

.hm-ring-wrap {
    display:flex;align-items:center;gap:14px;
    background:rgba(255,255,255,0.12);
    border:1px solid rgba(255,255,255,0.2);
    border-radius:14px;padding:14px 20px;
}
.hm-ring-svg { transform:rotate(-90deg); }
.hm-ring-track { fill:none;stroke:rgba(255,255,255,0.15);stroke-width:5; }
.hm-ring-fill {
    fill:none;stroke:#fff;stroke-width:5;stroke-linecap:round;
    stroke-dasharray:113;stroke-dashoffset:113;
    transition:stroke-dashoffset 1.3s cubic-bezier(0.4,0,0.2,1);
}
.hm-ring-pct {
    font-size:24px;font-weight:600;color:#fff;display:block;line-height:1;
}
.hm-ring-lbl { font-size:11px;color:rgba(255,255,255,0.5);margin-top:2px; }

/* ── GRID ── */
.hm-row { display:grid;gap:16px;margin-bottom:16px; }
.hm-col3 { grid-template-columns:1fr 1fr 1fr; }
.hm-col2 { grid-template-columns:1fr 1fr; }

/* ── CARDS ── */
.hm-card {
    background:#fff;border-radius:16px;
    border:1px solid #e2e8f0;
    padding:24px 26px;
    box-shadow:0 1px 4px rgba(0,0,0,0.04);
}
.hm-card-label {
    font-size:11px;letter-spacing:1.5px;text-transform:uppercase;
    color:#94a3b8;margin-bottom:18px;font-weight:500;
}
.hm-card-title {
    font-size:28px;font-weight:600;color:#1e293b;line-height:1;
    margin-bottom:4px;
}
.hm-card-sub { font-size:13px;color:#64748b;margin-bottom:18px; }

/* ── STEPS ── */
.hm-steps { display:flex;flex-direction:column;gap:10px; }
.hm-step {
    display:flex;align-items:center;gap:12px;
    padding:12px 14px;border-radius:12px;
    background:#f8fafc;border:1px solid #e2e8f0;
    cursor:pointer;transition:all 0.15s;text-decoration:none;
}
.hm-step:hover { border-color:#0058af;background:#f0f6ff; }
.hm-step-icon {
    width:36px;height:36px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-size:16px;flex-shrink:0;
}
.hm-step-title { font-size:14px;font-weight:500;color:#1e293b;display:block;margin-bottom:2px; }
.hm-step-sub   { font-size:11px;color:#94a3b8;display:block; }
.hm-step-badge {
    font-size:11px;font-weight:600;padding:3px 10px;
    border-radius:99px;white-space:nowrap;margin-left:auto;flex-shrink:0;
}
.bd { background:#dcfce7;color:#15803d; }
.bp { background:#fef9c3;color:#854d0e; }
.bn { background:#f1f5f9;color:#64748b; }
.bi { background:#dbeafe;color:#1d4ed8; }

/* ── BARS ── */
.hm-bar-track {
    background:#f1f5f9;border-radius:99px;height:7px;
    overflow:hidden;margin:6px 0 4px;
}
.hm-bar-fill {
    height:100%;border-radius:99px;width:0;
    background:linear-gradient(90deg,#0058af,#2563eb);
    transition:width 1.2s cubic-bezier(0.4,0,0.2,1);
}
.hm-bar-meta {
    display:flex;justify-content:space-between;
    font-size:11px;color:#94a3b8;
}

/* ── PLAN STATES ── */
.hm-plan-row { display:flex;gap:8px;margin-bottom:16px; }
.hm-plan-box {
    flex:1;padding:12px 10px;border-radius:12px;
    border:1px solid;text-align:center;
}
.hm-plan-n { font-size:26px;font-weight:600;display:block;line-height:1; }
.hm-plan-l { font-size:10px;display:block;margin-top:3px; }

/* ── ACTIONS ── */
.hm-action {
    display:flex;align-items:center;gap:14px;
    padding:18px 20px;border-radius:14px;
    background:linear-gradient(135deg,#0058af,#2563eb);
    color:#fff;cursor:pointer;border:none;width:100%;
    text-align:left;transition:transform 0.15s,box-shadow 0.15s;
    box-shadow:0 2px 12px rgba(0,88,175,0.25);
}
.hm-action:hover { transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,88,175,0.3); }
.hm-action-icon {
    width:44px;height:44px;border-radius:12px;
    background:rgba(255,255,255,0.15);
    display:flex;align-items:center;justify-content:center;
    font-size:20px;flex-shrink:0;
}
.hm-action-title { font-size:15px;font-weight:600;display:block; }
.hm-action-sub   { font-size:12px;color:rgba(255,255,255,0.6);display:block;margin-top:2px; }
.hm-action-arr   { margin-left:auto;font-size:20px;color:rgba(255,255,255,0.4); }

/* ── LINK ── */
.hm-link {
    display:inline-block;margin-top:14px;font-size:12px;
    color:#0058af;text-decoration:none;font-weight:500;
}
.hm-link:hover { text-decoration:underline; }

/* ── MOTIVACIONAL ── */
.hm-motive {
    text-align:center;padding:16px;
    background:#f0f6ff;border-radius:12px;
    font-size:13px;color:#1d4ed8;line-height:1.6;
    border:1px solid #dbeafe;
}

/* ── FADE ANIMATIONS ── */
.hm-fade { opacity:0;transform:translateY(12px);animation:hmUp 0.5s ease forwards; }
@keyframes hmUp { to { opacity:1;transform:translateY(0); } }
.d1{animation-delay:.05s}.d2{animation-delay:.12s}
.d3{animation-delay:.19s}.d4{animation-delay:.26s}
.d5{animation-delay:.33s}.d6{animation-delay:.4s}

@media(max-width:768px){
    .hm-hero{flex-direction:column;align-items:flex-start;padding:26px 22px;}
    .hm-hero-right{align-items:flex-start;}
    .hm-col3,.hm-col2{grid-template-columns:1fr;}
    .hm-username{font-size:22px;}
}
</style>

<div class="hm-root">

<!-- ── HERO ── -->
<div class="hm-hero hm-fade d1">
    <div class="hm-hero-left">
        <div class="hm-greeting"><?= $saludo ?>, bienvenido</div>
        <div class="hm-username"><?= htmlspecialchars($nombres . ' ' . $apellidos) ?></div>
        <div class="hm-cargo"><?= htmlspecialchars($cargoReal ?: $nivelCargo) ?></div>
        <div class="hm-frase"><?= icon('lightbulb', 14) ?> <?= htmlspecialchars($frase) ?></div>
    </div>
    <div class="hm-hero-right">
        <?php if ($periodoActivo): ?>
        <div class="hm-period-badge">
            <div class="hm-period-dot" style="background:<?= $colorP ?>;"></div>
            <?= htmlspecialchars($labelP) ?>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:10px;">
            <!-- Ring evaluaciones -->
            <div class="hm-ring-wrap">
                <svg class="hm-ring-svg" width="48" height="48" viewBox="0 0 48 48">
                    <circle class="hm-ring-track" cx="24" cy="24" r="15"/>
                    <circle class="hm-ring-fill" id="hmRing" cx="24" cy="24" r="15"/>
                </svg>
                <div>
                    <span class="hm-ring-pct" id="hmPct" style="font-size:20px;">0%</span>
                    <div class="hm-ring-lbl">Evaluaciones</div>
                </div>
            </div>
            <!-- Ring planes -->
            <?php if ($totalAcuerdos > 0 || !empty($acuerdosLiderHome)): ?>
            <?php
                // Calcular aprobados liderazgo aquí — $aprobL se define más abajo en la vista
                $aprobLiderRing = !empty($acuerdosLiderHome)
                    ? count(array_filter($acuerdosLiderHome, fn($a) => $a['ESTADO'] === 'APROBADO'))
                    : 0;
                $totalPlanesH = $totalAcuerdos + count($acuerdosLiderHome);
                $aprobPlanesH = $aprobados + $aprobLiderRing;
                $pctPlanesH   = $totalPlanesH > 0 ? round($aprobPlanesH / $totalPlanesH * 100) : 0;
            ?>
            <div class="hm-ring-wrap">
                <svg class="hm-ring-svg" width="48" height="48" viewBox="0 0 48 48">
                    <circle class="hm-ring-track" cx="24" cy="24" r="15"/>
                    <circle class="hm-ring-fill" id="hmRing2" cx="24" cy="24" r="15"
                            style="stroke:rgba(255,255,255,0.6);"/>
                </svg>
                <div>
                    <span class="hm-ring-pct" id="hmPct2" style="font-size:20px;">0%</span>
                    <div class="hm-ring-lbl"><?= !empty($acuerdosLiderHome) && $totalAcuerdos > 0 ? 'Planes' : ($totalAcuerdos > 0 ? 'Mi plan' : 'Liderazgo') ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── ESTADO FEEDBACK ── -->
<?php if ($feedbackEstado):
    $fbFirmadoLider = (int)($feedbackEstado['FIRMADO_LIDER'] ?? 0);
    $fbFirmadoColab = (int)($feedbackEstado['FIRMADO_COLAB'] ?? 0);
    $fbCompleto     = $fbFirmadoLider && $fbFirmadoColab;
    $fbNombreLider  = htmlspecialchars($feedbackEstado['NOMBRE_LIDER'] ?? '');
    $fbFechaRaw     = $feedbackEstado['FECHA_FEEDBACK'] ?? '';
    $fbPartes       = explode(' ', $fbFechaRaw);
    $fbFecha        = $fbPartes[0] ?? '';
    $fbHora         = $fbPartes[1] ?? '';
    $fbFechaFirma   = $feedbackEstado['FECHA_FIRMA'] ?? '';
    // Mostrar banner pendiente siempre que el feedback esté registrado y no se haya completado la firma
    $fbPendiente = !$fbCompleto;
?>

<?php if (!$fbFirmadoColab): ?>
<!-- Feedback pendiente de firma — banner azul -->
<div class="hm-fade d2" style="
    background:linear-gradient(135deg,#eff6ff,#dbeafe);
    border:1.5px solid #93c5fd;
    border-radius:16px;padding:16px 22px;
    display:flex;align-items:center;gap:16px;
    margin-bottom:16px;
    box-shadow:0 1px 4px rgba(37,99,235,0.08);">
    <div style="flex-shrink:0;color:#1d4ed8;"><?= icon('calendar', 28) ?></div>
    <div style="flex:1;">
        <div style="font-size:.78rem;font-weight:700;letter-spacing:1px;
                    text-transform:uppercase;color:#1d4ed8;margin-bottom:3px;">
            Feedback recibido — pendiente de firma
        </div>
        <div style="font-size:.92rem;font-weight:600;color:#1e293b;">
            Tu líder <strong><?= $fbNombreLider ?></strong> registró tu retroalimentación
            <?php if ($fbFecha): ?>el <strong><?= htmlspecialchars($fbFecha) ?></strong><?php endif; ?>
        </div>
        <div style="font-size:.78rem;color:#1d4ed8;margin-top:6px;">
            <?= icon('pencil', 14) ?> Ingresa al módulo de <strong>Feedback</strong> para firmar tu conformidad.
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php if ($feedbackEAEstado): ?>
<?php
$eaPartes      = explode(' ', $feedbackEAEstado['FECHA_FEEDBACK'] ?? '');
$eaFecha       = $eaPartes[0] ?? '';
$eaHora        = $eaPartes[1] ?? '';
$eaNombreLider = htmlspecialchars($feedbackEAEstado['NOMBRE_LIDER'] ?? '');
?>
<!-- Banner Experiencia Azul — feedback recibido -->
<div class="hm-fade d2" style="
    background:linear-gradient(135deg,#ecfeff,#cffafe);
    border:1.5px solid #67e8f9;
    border-radius:16px;padding:16px 22px;
    display:flex;align-items:center;gap:16px;
    margin-bottom:16px;
    box-shadow:0 1px 4px rgba(8,145,178,0.10);">
    <div style="width:44px;height:44px;border-radius:12px;flex-shrink:0;
                background:linear-gradient(135deg,#0891b2,#06b6d4);
                display:flex;align-items:center;justify-content:center;color:#fff;">
        <?= icon('star', 22) ?>
    </div>
    <div style="flex:1;">
        <div style="font-size:.78rem;font-weight:700;letter-spacing:1px;
                    text-transform:uppercase;color:#0891b2;margin-bottom:3px;">
            Feedback Experiencia Azul recibido
        </div>
        <div style="font-size:.92rem;font-weight:600;color:#1e293b;">
            Tu líder <strong><?= $eaNombreLider ?></strong> registró tu retroalimentación de Experiencia Azul
        </div>
        <?php if ($eaFecha): ?>
        <div style="font-size:.78rem;color:#0891b2;margin-top:4px;">
            <?= icon('calendar', 14) ?> <strong><?= htmlspecialchars($eaFecha) ?></strong>
            <?php if ($eaHora): ?>&nbsp;<?= icon('clock', 14) ?> <strong><?= htmlspecialchars($eaHora) ?></strong><?php endif; ?>
        </div>
        <?php endif; ?>
        <div style="font-size:.78rem;color:#0891b2;margin-top:6px;">
            <?= icon('pencil', 14) ?> Ingresa al m&oacute;dulo de <strong>Feedback</strong> para firmar tu conformidad.
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($feedbackDirEstado):
$dirPartes      = explode(' ', $feedbackDirEstado['FECHA_FEEDBACK'] ?? '');
$dirFecha       = $dirPartes[0] ?? '';
$dirHora        = $dirPartes[1] ?? '';
$dirNombreDir   = htmlspecialchars($feedbackDirEstado['NOMBRE_DIRECTOR'] ?? '');
?>
<!-- Banner Flujo 2 — líder con feedback de liderazgo pendiente de firma -->
<div class="hm-fade d2" style="
    background:linear-gradient(135deg,#fffbeb,#fef3c7);
    border:1.5px solid #fcd34d;
    border-radius:16px;padding:16px 22px;
    display:flex;align-items:center;gap:16px;
    margin-bottom:16px;
    box-shadow:0 1px 4px rgba(245,158,11,0.10);">
    <div style="width:44px;height:44px;border-radius:12px;flex-shrink:0;
                background:linear-gradient(135deg,#d97706,#f59e0b);
                display:flex;align-items:center;justify-content:center;color:#fff;">
        <?= icon('award', 22) ?>
    </div>
    <div style="flex:1;">
        <div style="font-size:.78rem;font-weight:700;letter-spacing:1px;
                    text-transform:uppercase;color:#92400e;margin-bottom:3px;">
            Feedback de liderazgo — pendiente de firma
        </div>
        <div style="font-size:.92rem;font-weight:600;color:#1e293b;">
            Tu director <strong><?= $dirNombreDir ?></strong> registró tu retroalimentación de liderazgo
            <?php if ($dirFecha): ?>el <strong><?= htmlspecialchars($dirFecha) ?></strong><?php endif; ?>
        </div>
        <div style="font-size:.78rem;color:#92400e;margin-top:6px;">
            <?= icon('pencil', 14) ?> Ingresa al módulo de <strong>Feedback</strong> para firmar tu conformidad.
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── PENDIENTES DE FIRMA — visible solo para líderes/directores ── -->
<?php if (!empty($feedbacksPendLider)):
    // Agrupar por tipo
    $pendColab  = array_values(array_filter($feedbacksPendLider, fn($r) => $r['TIPO_FEEDBACK'] === 1));
    $pendLiderF = array_values(array_filter($feedbacksPendLider, fn($r) => $r['TIPO_FEEDBACK'] === 2));
    $pendEA     = array_values(array_filter($feedbacksPendLider, fn($r) => $r['TIPO_FEEDBACK'] === 3));
    $totalPend  = count($feedbacksPendLider);
    $colsPend   = (($pendColab ? 1 : 0) + ($pendLiderF ? 1 : 0) + ($pendEA ? 1 : 0)) >= 2 ? '1fr 1fr' : '1fr';
?>
<div id="hm-pend-widget" class="hm-card hm-fade d2" style="margin-bottom:16px;border-left:4px solid #f59e0b;padding:20px 24px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <div style="width:34px;height:34px;border-radius:10px;flex-shrink:0;
                    background:linear-gradient(135deg,#f59e0b,#fbbf24);
                    display:flex;align-items:center;justify-content:center;">
            <?= icon('bell', 16, '#fff') ?>
        </div>
        <div>
            <div style="font-size:.75rem;font-weight:700;letter-spacing:1.2px;
                        text-transform:uppercase;color:#92400e;">Feedbacks pendientes de firma</div>
            <div style="font-size:.8rem;color:#b45309;margin-top:1px;">
                <?= $totalPend !== 1 ? 'Sesiones programadas' : 'Sesión programada' ?> en espera de realizarse
            </div>
        </div>
        <div style="margin-left:auto;background:#fef3c7;border:1px solid #fde68a;
                    border-radius:99px;padding:4px 14px;font-size:.8rem;
                    font-weight:700;color:#92400e;">
            <?= $totalPend ?> pendiente<?= $totalPend !== 1 ? 's' : '' ?>
        </div>
    </div>

    <div style="display:grid;gap:10px;grid-template-columns:<?= $colsPend ?>">

    <?php if (!empty($pendColab)): ?>
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 16px;">
        <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">
            <div style="width:8px;height:8px;border-radius:50%;background:#2563eb;flex-shrink:0;"></div>
            <span style="font-size:.72rem;font-weight:700;letter-spacing:1px;
                         text-transform:uppercase;color:#1d4ed8;">
                Flujo 1 · Feedback de desempeño
            </span>
        </div>
        <div style="display:flex;flex-direction:column;gap:7px;">
        <?php foreach ($pendColab as $p): ?>
            <div style="display:flex;align-items:center;gap:10px;
                        background:#fff;border:1px solid #dbeafe;
                        border-radius:8px;padding:9px 12px;">
                <div style="flex-shrink:0;color:#2563eb;"><?= icon('user', 14) ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.84rem;font-weight:600;color:#1e293b;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($p['NOMBRE_EMPLEADO']) ?>
                    </div>
                    <?php if ($p['FECHA_FEEDBACK']): ?>
                    <div style="font-size:.72rem;color:#3b82f6;margin-top:2px;">
                        <?= icon('calendar', 11) ?> <?= htmlspecialchars($p['FECHA_FEEDBACK']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="flex-shrink:0;background:#fef3c7;border-radius:6px;
                            padding:3px 9px;font-size:.69rem;font-weight:700;color:#92400e;">
                    Sin firma
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($pendLiderF)): ?>
    <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:12px;padding:14px 16px;">
        <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">
            <div style="width:8px;height:8px;border-radius:50%;background:#7c3aed;flex-shrink:0;"></div>
            <span style="font-size:.72rem;font-weight:700;letter-spacing:1px;
                         text-transform:uppercase;color:#6d28d9;">
                Flujo 2 · Feedback de liderazgo
            </span>
        </div>
        <div style="display:flex;flex-direction:column;gap:7px;">
        <?php foreach ($pendLiderF as $p): ?>
            <div style="display:flex;align-items:center;gap:10px;
                        background:#fff;border:1px solid #e9d5ff;
                        border-radius:8px;padding:9px 12px;">
                <div style="flex-shrink:0;color:#7c3aed;"><?= icon('award', 14) ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.84rem;font-weight:600;color:#1e293b;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($p['NOMBRE_EMPLEADO']) ?>
                    </div>
                    <?php if ($p['FECHA_FEEDBACK']): ?>
                    <div style="font-size:.72rem;color:#7c3aed;margin-top:2px;">
                        <?= icon('calendar', 11) ?> <?= htmlspecialchars($p['FECHA_FEEDBACK']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="flex-shrink:0;background:#fef3c7;border-radius:6px;
                            padding:3px 9px;font-size:.69rem;font-weight:700;color:#92400e;">
                    Sin firma
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($pendEA)): ?>
    <div style="background:#ecfeff;border:1px solid #a5f3fc;border-radius:12px;padding:14px 16px;">
        <div style="display:flex;align-items:center;gap:7px;margin-bottom:10px;">
            <div style="width:8px;height:8px;border-radius:50%;background:#0891b2;flex-shrink:0;"></div>
            <span style="font-size:.72rem;font-weight:700;letter-spacing:1px;
                         text-transform:uppercase;color:#0e7490;">
                Flujo 3 · Experiencia Azul
            </span>
        </div>
        <div style="display:flex;flex-direction:column;gap:7px;">
        <?php foreach ($pendEA as $p): ?>
            <div style="display:flex;align-items:center;gap:10px;
                        background:#fff;border:1px solid #cffafe;
                        border-radius:8px;padding:9px 12px;">
                <div style="flex-shrink:0;color:#0891b2;"><?= icon('star', 14) ?></div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.84rem;font-weight:600;color:#1e293b;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($p['NOMBRE_EMPLEADO']) ?>
                    </div>
                    <?php if ($p['FECHA_FEEDBACK']): ?>
                    <div style="font-size:.72rem;color:#0891b2;margin-top:2px;">
                        <?= icon('calendar', 11) ?> <?= htmlspecialchars($p['FECHA_FEEDBACK']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="flex-shrink:0;background:#fef3c7;border-radius:6px;
                            padding:3px 9px;font-size:.69rem;font-weight:700;color:#92400e;">
                    Sin firma
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    </div><!-- /grid -->

    <div style="margin-top:12px;font-size:.75rem;color:#b45309;">
        <?= icon('info', 12) ?> Una vez realizada la sesión, el colaborador o líder firma el recibido directamente en esta plataforma para desbloquear su <strong>Plan de Mejora</strong>.
    </div>
</div>
<?php endif; ?>

<!-- ── FILA 1: Evaluaciones + Equipo ── -->
<div class="hm-row <?= $esLider ? 'hm-col2' : '' ?>" style="<?= !$esLider ? 'grid-template-columns:1fr;' : '' ?>">

    <!-- MIS EVALUACIONES -->
    <div class="hm-card hm-fade d2">
        <div class="hm-card-label">Mis evaluaciones</div>
        <div class="hm-steps">

            <!-- Autoevaluación -->
            <div class="hm-step" onclick="location.href='<?= APP_URL ?>evaluarList/'">
                <div class="hm-step-icon" style="background:<?= $tieneAuto ? '#dcfce7' : '#eff6ff' ?>;">
                    <?= icon($tieneAuto ? 'check-circle' : 'file-text', 14) ?>
                </div>
                <div>
                    <span class="hm-step-title">Autoevaluación</span>
                    <span class="hm-step-sub">Evalúa tu propio desempeño</span>
                </div>
                <span class="hm-step-badge <?= $tieneAuto ? 'bd' : 'bp' ?>">
                    <?= $tieneAuto ? 'Completa' : 'Pendiente' ?>
                </span>
            </div>

            <!-- Fue evaluado -->
            <div class="hm-step">
                <div class="hm-step-icon" style="background:<?= $fueEvaluado ? '#dcfce7' : '#f8fafc' ?>;">
                    <?= icon($fueEvaluado ? 'check-circle' : 'user', 14) ?>
                </div>
                <div>
                    <span class="hm-step-title">Evaluación recibida</span>
                    <span class="hm-step-sub">Tu líder te evaluó</span>
                </div>
                <span class="hm-step-badge <?= $fueEvaluado ? 'bd' : 'bn' ?>">
                    <?= $fueEvaluado ? 'Recibida' : 'Esperando' ?>
                </span>
            </div>

            <!-- Evaluó a otros -->
            <div class="hm-step" onclick="location.href='<?= APP_URL ?>evaluarList/'">
                <div class="hm-step-icon" style="background:<?= $evaluoCompleto ? '#dcfce7' : ($evaluoAOtros ? '#eff6ff' : '#f8fafc') ?>;">
                    <?= icon($evaluoCompleto ? 'check-circle' : ($evaluoAOtros ? 'refresh' : 'users'), 14) ?>
                </div>
                <div>
                    <span class="hm-step-title">Evaluó a otros</span>
                    <span class="hm-step-sub">
                        <?php if ($totalAEvaluar > 0): ?>
                            <?= $totalRealizadas ?> de <?= $totalAEvaluar ?> evaluaciones
                        <?php else: ?>
                            Evaluaciones realizadas
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($totalAEvaluar > 0): ?>
                <span class="hm-step-badge <?= $evaluoCompleto ? 'bd' : ($evaluoAOtros ? 'bi' : 'bp') ?>">
                    <?= $totalRealizadas ?>/<?= $totalAEvaluar ?>
                </span>
                <?php else: ?>
                <span class="hm-step-badge <?= $evaluoAOtros ? 'bd' : 'bp' ?>">
                    <?= $evaluoAOtros ? 'Realizado' : 'Pendiente' ?>
                </span>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- EQUIPO — solo líderes -->
    <?php if ($esLider): ?>
    <div class="hm-card hm-fade d3">
        <div class="hm-card-label">Mi equipo</div>
        <div class="hm-card-title">
            <?= $equipoCompleto ?><span style="font-size:18px;color:#94a3b8;font-weight:400;"> / <?= $equipoTotal ?></span>
        </div>
        <div class="hm-card-sub">Colaboradores con evaluación completa</div>
        <div class="hm-bar-track">
            <div class="hm-bar-fill" style="width:<?= $equipoTotal > 0 ? round($equipoCompleto/$equipoTotal*100) : 0 ?>%;"></div>
        </div>
        <div class="hm-bar-meta">
            <span>Avance del equipo</span>
            <span><?= $equipoTotal > 0 ? round($equipoCompleto/$equipoTotal*100) : 0 ?>%</span>
        </div>
        <a class="hm-link" href="<?= APP_URL ?>reportes/?tab=equipo">Ver mi equipo →</a>
    </div>
    <?php endif; ?>

</div>

<!-- ── FILA 2: Planes de mejora ── -->
<?php
$tieneColab = $totalAcuerdos > 0;
$tieneLider = $esLiderFuncional && !empty($acuerdosLiderHome);
$tieneEAH   = $firmadoEAHome && $totalEAHome > 0;
$cantPlanes = (int)$tieneColab + (int)$tieneLider + (int)$tieneEAH;
$colsPlan   = match($cantPlanes) { 2 => 'hm-col2', 3 => 'hm-col3', default => '' };
?>
<?php if ($tieneColab || $tieneLider || $tieneEAH): ?>
<div class="hm-row <?= $colsPlan ?>">

    <!-- PLAN DE MEJORA COLABORADOR -->
    <?php if ($tieneColab): ?>
    <div class="hm-card hm-fade d3">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
            <div style="width:28px;height:28px;border-radius:8px;flex-shrink:0;
                        background:linear-gradient(135deg,#0058af,#2563eb);
                        display:flex;align-items:center;justify-content:center;">
                <?= icon('user', 14, '#fff') ?>
            </div>
            <div class="hm-card-label" style="margin:0;">Mi plan de mejora · Colaborador</div>
        </div>
        <div class="hm-plan-row">
            <div class="hm-plan-box" style="background:#fef9c3;border-color:#fde68a;">
                <span class="hm-plan-n" style="color:#92400e;"><?= $pendientesAcuerdo ?></span>
                <span class="hm-plan-l" style="color:#b45309;">Pendientes</span>
            </div>
            <div class="hm-plan-box" style="background:#dbeafe;border-color:#bfdbfe;">
                <span class="hm-plan-n" style="color:#1d4ed8;"><?= $respondidos - $aprobados ?></span>
                <span class="hm-plan-l" style="color:#2563eb;">En revisión</span>
            </div>
            <div class="hm-plan-box" style="background:#dcfce7;border-color:#bbf7d0;">
                <span class="hm-plan-n" style="color:#15803d;"><?= $aprobados ?></span>
                <span class="hm-plan-l" style="color:#166534;">Aprobados</span>
            </div>
        </div>
        <div class="hm-card-label" style="margin-bottom:4px;">Progreso de aprobación</div>
        <div class="hm-bar-track">
            <div class="hm-bar-fill" style="width:<?= $totalAcuerdos > 0 ? round($aprobados/$totalAcuerdos*100) : 0 ?>%;"></div>
        </div>
        <div class="hm-bar-meta">
            <span><?= $aprobados ?>/<?= $totalAcuerdos ?> aprobados</span>
            <span><?= $totalAcuerdos > 0 ? round($aprobados/$totalAcuerdos*100) : 0 ?>%</span>
        </div>
        <a class="hm-link" href="<?= APP_URL ?>reportes/?tab=plan&sub=colab">Ver mi plan →</a>
    </div>
    <?php endif; ?>

    <!-- PLAN DE LIDERAZGO -->
    <?php if ($tieneLider): ?>
    <?php
        $totalL  = count($acuerdosLiderHome);
        $aprobL  = count(array_filter($acuerdosLiderHome, fn($a) => $a['ESTADO'] === 'APROBADO'));
        $respL   = count(array_filter($acuerdosLiderHome, fn($a) => in_array($a['ESTADO'], ['RESPONDIDO','APROBADO'])));
        $pendL   = $totalL - $respL;
        $firmadoL = !$feedbackDirPend;
    ?>
    <div class="hm-card hm-fade d4">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
            <div style="width:28px;height:28px;border-radius:8px;flex-shrink:0;
                        background:linear-gradient(135deg,#0058af,#2563eb);
                        display:flex;align-items:center;justify-content:center;">
                <?= icon('target', 14, '#fff') ?>
            </div>
            <div class="hm-card-label" style="margin:0;">Mi plan de mejora · Liderazgo</div>
        </div>
        <?php if (!$firmadoL): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:14px;
                    background:#fff7ed;border:1px solid #fcd34d;border-radius:12px;">
            <?= icon('lock', 16) ?>
            <div style="font-size:.84rem;color:#92400e;font-weight:500;">
                Pendiente de firma con tu director
            </div>
        </div>
        <a class="hm-link" href="<?= APP_URL ?>reportes/?tab=plan&sub=lider">Ir a mi plan →</a>
        <?php else: ?>
        <div class="hm-plan-row">
            <div class="hm-plan-box" style="background:#fef9c3;border-color:#fde68a;">
                <span class="hm-plan-n" style="color:#92400e;"><?= $pendL ?></span>
                <span class="hm-plan-l" style="color:#b45309;">Pendientes</span>
            </div>
            <div class="hm-plan-box" style="background:#dbeafe;border-color:#bfdbfe;">
                <span class="hm-plan-n" style="color:#1d4ed8;"><?= $respL - $aprobL ?></span>
                <span class="hm-plan-l" style="color:#2563eb;">En revisión</span>
            </div>
            <div class="hm-plan-box" style="background:#dcfce7;border-color:#bbf7d0;">
                <span class="hm-plan-n" style="color:#15803d;"><?= $aprobL ?></span>
                <span class="hm-plan-l" style="color:#166534;">Aprobados</span>
            </div>
        </div>
        <div class="hm-card-label" style="margin-bottom:4px;">Progreso competencias de liderazgo</div>
        <div class="hm-bar-track">
            <div class="hm-bar-fill" style="width:<?= $totalL > 0 ? round($aprobL/$totalL*100) : 0 ?>%;"></div>
        </div>
        <div class="hm-bar-meta">
            <span><?= $aprobL ?>/<?= $totalL ?> aprobados</span>
            <span><?= $totalL > 0 ? round($aprobL/$totalL*100) : 0 ?>%</span>
        </div>
        <a class="hm-link" href="<?= APP_URL ?>reportes/?tab=plan&sub=lider">Ver mi plan de liderazgo →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- PLAN DE MEJORA EXPERIENCIA AZUL -->
    <?php if ($tieneEAH): ?>
    <div class="hm-card hm-fade d<?= $cantPlanes >= 3 ? '5' : '4' ?>"
         style="border-top:3px solid #06b6d4;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
            <div style="width:28px;height:28px;border-radius:8px;flex-shrink:0;
                        background:linear-gradient(135deg,#0891b2,#06b6d4);
                        display:flex;align-items:center;justify-content:center;">
                <?= icon('star', 14, '#fff') ?>
            </div>
            <div class="hm-card-label" style="margin:0;color:#0891b2;">Mi plan de mejora · Experiencia Azul</div>
        </div>
        <div class="hm-plan-row">
            <div class="hm-plan-box" style="background:#fef9c3;border-color:#fde68a;">
                <span class="hm-plan-n" style="color:#92400e;"><?= $pendientesEAHome ?></span>
                <span class="hm-plan-l" style="color:#b45309;">Pendientes</span>
            </div>
            <div class="hm-plan-box" style="background:#cffafe;border-color:#a5f3fc;">
                <span class="hm-plan-n" style="color:#0c4a6e;"><?= $respondidosEAHome - $aprobadosEAHome ?></span>
                <span class="hm-plan-l" style="color:#0891b2;">En revisión</span>
            </div>
            <div class="hm-plan-box" style="background:#dcfce7;border-color:#bbf7d0;">
                <span class="hm-plan-n" style="color:#15803d;"><?= $aprobadosEAHome ?></span>
                <span class="hm-plan-l" style="color:#166534;">Aprobados</span>
            </div>
        </div>
        <?php $pctEAHome = $totalEAHome > 0 ? round($aprobadosEAHome/$totalEAHome*100) : 0; ?>
        <div class="hm-card-label" style="margin-bottom:4px;color:#0891b2;">Progreso competencias EA</div>
        <div class="hm-bar-track">
            <div class="hm-bar-fill" style="width:<?= $pctEAHome ?>%;background:linear-gradient(90deg,#0891b2,#06b6d4);"></div>
        </div>
        <div class="hm-bar-meta">
            <span><?= $aprobadosEAHome ?>/<?= $totalEAHome ?> aprobados</span>
            <span><?= $totalEAHome > 0 ? round($aprobadosEAHome/$totalEAHome*100) : 0 ?>%</span>
        </div>
        <a class="hm-link" href="<?= APP_URL ?>reportes/?tab=plan&sub=ea"
           style="color:#0891b2;">Ver mi plan EA →</a>
    </div>
    <?php endif; ?>

</div>
<?php endif; ?>

<!-- ── ACCIONES RÁPIDAS ── -->
<div class="hm-row <?= $esLiderFuncional ? 'hm-col3' : 'hm-col2' ?> hm-fade d5">
    <button class="hm-action" onclick="location.href='<?= APP_URL ?>evaluarList/'">
        <div class="hm-action-icon"><?= icon('rocket', 20, '#fff') ?></div>
        <div>
            <span class="hm-action-title">Iniciar evaluación</span>
            <span class="hm-action-sub">Autoevaluación y evaluación a otros</span>
        </div>
        <span class="hm-action-arr">›</span>
    </button>
    <?php if ($esLiderFuncional): ?>
    <button class="hm-action" onclick="location.href='<?= APP_URL ?>feedback/'">
        <div class="hm-action-icon"><?= icon('message-circle', 20, '#fff') ?></div>
        <div>
            <span class="hm-action-title">Dar feedback</span>
            <span class="hm-action-sub">Reunión y objetivos con tu equipo</span>
        </div>
        <span class="hm-action-arr">›</span>
    </button>
    <?php endif; ?>
    <button class="hm-action" onclick="location.href='<?= APP_URL ?>reportes/'">
        <div class="hm-action-icon"><?= icon('bar-chart', 20, '#fff') ?></div>
        <div>
            <span class="hm-action-title">Ver reportes</span>
            <span class="hm-action-sub">Resultados, indicadores y plan de mejora</span>
        </div>
        <span class="hm-action-arr">›</span>
    </button>
</div>

<!-- ── MENSAJE MOTIVACIONAL ── -->
<div class="hm-motive hm-fade d5">
    <?php if ($pctPersonal === 100): ?>
        <?= icon('award', 14) ?> ¡Completaste todas tus evaluaciones del período! Gracias por tu compromiso con la excelencia.
    <?php elseif ($pctPersonal > 0): ?>
        <?= icon('trending-up', 14) ?> Vas por buen camino. Recuerda completar tus evaluaciones antes del cierre del período.
    <?php else: ?>
        <?= icon('info', 14) ?> Aún no has iniciado tus evaluaciones. <strong>¡Empieza hoy y marca la diferencia!</strong>
    <?php endif; ?>
</div>

</div>

<?php if ($showWelcome): ?>
<!-- ── SPLASH DE BIENVENIDA ── -->
<div id="hmSplash" style="
    position:fixed;inset:0;z-index:9999;
    display:flex;flex-direction:column;
    align-items:center;justify-content:center;
    background:#0058af;
    animation:splashOut 0.5s ease 2.4s forwards;">

    <style>
        @keyframes splashOut {
            to { opacity:0; pointer-events:none; transform:scale(1.04); }
        }
        @keyframes splashRing {
            from { stroke-dashoffset:220; }
            to   { stroke-dashoffset:0; }
        }
        @keyframes splashFadeUp {
            from { opacity:0; transform:translateY(16px); }
            to   { opacity:1; transform:translateY(0); }
        }
        @keyframes splashBar {
            from { width:0; }
            to   { width:100%; }
        }
    </style>

    <!-- Anillo animado -->
    <div style="position:relative;width:110px;height:110px;margin-bottom:28px;">
        <svg viewBox="0 0 110 110" width="110" height="110" style="transform:rotate(-90deg);">
            <circle cx="55" cy="55" r="46" fill="none"
                    stroke="rgba(255,255,255,0.12)" stroke-width="6"/>
            <circle cx="55" cy="55" r="46" fill="none"
                    stroke="rgba(255,255,255,0.9)" stroke-width="6"
                    stroke-linecap="round"
                    stroke-dasharray="289"
                    stroke-dashoffset="289"
                    style="animation:splashRing 1s ease 0.2s forwards;"/>
        </svg>
        <div style="
            position:absolute;inset:14px;
            background:rgba(255,255,255,0.12);
            border-radius:50%;
            display:flex;align-items:center;justify-content:center;">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                <path d="M20 4L32 11V29L20 36L8 29V11Z" fill="rgba(255,255,255,0.95)"/>
                <path d="M20 10L28 14.5V23.5L20 28L12 23.5V14.5Z" fill="#0058af"/>
            </svg>
        </div>
    </div>

    <!-- Texto -->
    <div style="text-align:center;animation:splashFadeUp 0.6s ease 0.6s both;">
        <div style="font-size:11px;letter-spacing:3px;text-transform:uppercase;
                    color:rgba(255,255,255,0.5);margin-bottom:8px;
                    font-family:'Plus Jakarta Sans',sans-serif;">
            Clínica Zayma · Gestión Humana
        </div>
        <div style="font-size:26px;font-weight:600;color:#fff;margin-bottom:4px;
                    font-family:'Plus Jakarta Sans',sans-serif;">
            Bienvenido, <?= htmlspecialchars($nombres) ?>
        </div>
        <div style="font-size:14px;color:rgba(255,255,255,0.55);
                    font-family:'Plus Jakarta Sans',sans-serif;">
            Plataforma de Evaluación de Desempeño
        </div>
    </div>

    <!-- Barra de progreso -->
    <div style="
        margin-top:36px;
        width:200px;height:3px;
        background:rgba(255,255,255,0.15);
        border-radius:99px;overflow:hidden;
        animation:splashFadeUp 0.6s ease 0.8s both;">
        <div style="
            height:100%;background:rgba(255,255,255,0.8);
            border-radius:99px;width:0;
            animation:splashBar 2s ease 0.9s forwards;">
        </div>
    </div>
</div>
<script>
document.getElementById('hmSplash').addEventListener('animationend', function(e) {
    if (e.animationName === 'splashOut') {
        this.remove();
        // Limpiar el parámetro welcome de la URL
        history.replaceState(null, '', window.location.pathname);
    }
});
</script>
<?php endif; ?>

<script>
// Mostrar alerta de período no disponible si viene de evaluarController
if (sessionStorage.getItem('sinPeriodo') === '1') {
    sessionStorage.removeItem('sinPeriodo');
    // Swal está cargado sincrónicamente desde head.php — se puede llamar directo
    Swal.fire({
        icon: 'warning',
        title: 'Período no disponible',
        text: 'No hay un período de evaluación activo en este momento. Contacta al área de Gestión Humana.',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#0058af'
    });
}
(function(){
    // Ring evaluaciones
    const pct    = <?= $pctPersonal ?>;
    const ring   = document.getElementById('hmRing');
    const pctEl  = document.getElementById('hmPct');
    const circum = 94; // r=15 → 2π×15 ≈ 94
    setTimeout(() => {
        if (ring) ring.style.strokeDashoffset = circum - (circum * pct / 100);
        let cur = 0, step = Math.max(pct / 40, 0.5);
        const t = setInterval(() => {
            cur = Math.min(cur + step, pct);
            if (pctEl) pctEl.textContent = Math.round(cur) + '%';
            if (cur >= pct) clearInterval(t);
        }, 16);
    }, 500);

    // Ring planes (si existe)
    const pct2   = <?= isset($pctPlanesH) ? $pctPlanesH : 0 ?>;
    const ring2  = document.getElementById('hmRing2');
    const pctEl2 = document.getElementById('hmPct2');
    if (ring2 && pctEl2) {
        ring2.style.strokeDasharray  = circum;
        ring2.style.strokeDashoffset = circum;
        setTimeout(() => {
            ring2.style.strokeDashoffset = circum - (circum * pct2 / 100);
            let cur2 = 0, step2 = Math.max(pct2 / 40, 0.5);
            const t2 = setInterval(() => {
                cur2 = Math.min(cur2 + step2, pct2);
                pctEl2.textContent = Math.round(cur2) + '%';
                if (cur2 >= pct2) clearInterval(t2);
            }, 16);
        }, 700);
    }
})();
</script>

<!-- ══════════════════════════════════════════════════════════
     TOUR INTERACTIVO — Fase 2 · Panel de Inicio
     Driver.js 1.3.1 · Carga diferida · Reversible sin impacto
══════════════════════════════════════════════════════════ -->

<!-- Botón flotante ayuda -->
<button id="hm-tour-btn"
        onclick="iniciarTourHome()"
        title="Tour guiado — cómo usar este panel"
        aria-label="Iniciar tour guiado"
        style="position:fixed;bottom:28px;right:28px;z-index:890;
               width:50px;height:50px;border-radius:50%;border:none;
               background:linear-gradient(135deg,#0058af,#0074e0);
               color:#fff;cursor:pointer;
               box-shadow:0 4px 16px rgba(0,88,175,.40);
               display:flex;align-items:center;justify-content:center;
               transition:transform .18s,box-shadow .18s;font-family:inherit;">
    <i class="ti ti-help" style="font-size:22px;pointer-events:none;line-height:1;"></i>
</button>

<!-- Tooltip -->
<div id="hm-tour-tip"
     style="position:fixed;bottom:36px;right:88px;z-index:889;
            background:#1e293b;color:#fff;font-size:11px;font-weight:600;
            padding:5px 12px;border-radius:7px;white-space:nowrap;
            pointer-events:none;opacity:0;transition:opacity .18s;
            font-family:'Plus Jakarta Sans',sans-serif;">
    Tour guiado
    <div style="position:absolute;right:-5px;top:50%;transform:translateY(-50%);
                width:0;height:0;border:5px solid transparent;
                border-left-color:#1e293b;"></div>
</div>

<script>
// ── Tooltip hover ─────────────────────────────────────────────────────────────
(function(){
    var btn = document.getElementById('hm-tour-btn');
    var tip = document.getElementById('hm-tour-tip');
    if (!btn || !tip) return;
    btn.addEventListener('mouseenter', function(){
        tip.style.opacity   = '1';
        btn.style.transform = 'scale(1.08)';
        btn.style.boxShadow = '0 6px 24px rgba(0,88,175,.55)';
    });
    btn.addEventListener('mouseleave', function(){
        tip.style.opacity   = '0';
        btn.style.transform = '';
        btn.style.boxShadow = '0 4px 16px rgba(0,88,175,.40)';
    });
})();

// ── Carga diferida Driver.js (mismo CSS override que Feedback) ────────────────
function _hmLoadDriver(cb) {
    if (window._fbDriverReady) { cb(); return; }
    var cssLink  = document.createElement('link');
    cssLink.rel  = 'stylesheet';
    cssLink.href = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css';
    document.head.appendChild(cssLink);
    var style    = document.createElement('style');
    style.id     = 'gh-driver-overrides';
    style.textContent =
        '.driver-popover{border-radius:16px!important;padding:0!important;' +
        'box-shadow:0 24px 64px rgba(0,0,0,.20),0 2px 8px rgba(0,0,0,.08)!important;' +
        'font-family:"Plus Jakarta Sans",-apple-system,sans-serif!important;' +
        'max-width:420px!important;min-width:300px!important;overflow:hidden!important;' +
        'border:1px solid #e2e8f0!important;}' +
        '.driver-popover *{box-sizing:border-box!important;}' +
        '.driver-popover-title{font-size:14px!important;font-weight:700!important;' +
        'color:#1e293b!important;padding:16px 40px 10px 20px!important;margin:0!important;' +
        'line-height:1.35!important;border-bottom:1px solid #f1f5f9!important;' +
        'display:flex!important;align-items:center!important;gap:8px!important;}' +
        '.driver-popover-description{font-size:12.5px!important;color:#475569!important;' +
        'line-height:1.75!important;padding:12px 20px 16px!important;margin:0!important;}' +
        '.driver-popover-footer{padding:10px 20px 12px!important;' +
        'border-top:1px solid #f1f5f9!important;display:flex!important;align-items:center!important;' +
        'justify-content:space-between!important;gap:8px!important;' +
        'background:#f8fafc!important;border-radius:0 0 16px 16px!important;}' +
        '.driver-popover-progress-text{font-size:10.5px!important;color:#94a3b8!important;' +
        'font-family:"DM Mono",monospace!important;letter-spacing:.04em!important;flex:1!important;}' +
        '.driver-popover-prev-btn{border:1.5px solid #e2e8f0!important;border-radius:8px!important;' +
        'padding:6px 15px!important;font-size:12px!important;font-weight:600!important;' +
        'cursor:pointer!important;background:#fff!important;color:#64748b!important;}' +
        '.driver-popover-prev-btn:hover{background:#f1f5f9!important;}' +
        '.driver-popover-next-btn{border:none!important;border-radius:8px!important;' +
        'padding:9px 22px!important;font-size:13px!important;font-weight:700!important;' +
        'letter-spacing:0!important;cursor:pointer!important;' +
        '-webkit-font-smoothing:antialiased!important;text-shadow:none!important;' +
        'background:#0058af!important;color:#fff!important;}' +
        '.driver-popover-next-btn:hover{background:#004a9a!important;}' +
        '.driver-popover-close-btn{color:#94a3b8!important;background:none!important;' +
        'border:none!important;cursor:pointer!important;font-size:17px!important;' +
        'line-height:1!important;opacity:.7!important;}' +
        '.driver-popover-close-btn:hover{opacity:1!important;}' +
        '.driver-popover-arrow-side-left .driver-popover-arrow{border-left-color:#fff!important;}' +
        '.driver-popover-arrow-side-right .driver-popover-arrow{border-right-color:#fff!important;}' +
        '.driver-popover-arrow-side-top .driver-popover-arrow{border-top-color:#fff!important;}' +
        '.driver-popover-arrow-side-bottom .driver-popover-arrow{border-bottom-color:#fff!important;}';
    document.head.appendChild(style);
    var scr    = document.createElement('script');
    scr.src    = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js';
    scr.onload = function() { window._fbDriverReady = true; cb(); };
    document.head.appendChild(scr);
}

// ── Tour principal ────────────────────────────────────────────────────────────
function iniciarTourHome() {
    _hmLoadDriver(function() {
        var driverFn     = window.driver.js.driver;
        var esLider      = <?= $esLider           ? 'true' : 'false' ?>;
        var esLiderFn    = <?= $esLiderFuncional  ? 'true' : 'false' ?>;
        var tieneColab   = <?= ($totalAcuerdos > 0)                         ? 'true' : 'false' ?>;
        var tieneLider   = <?= ($esLiderFuncional && !empty($acuerdosLiderHome)) ? 'true' : 'false' ?>;
        var tienePeriodo = <?= $periodoActivo ? 'true' : 'false' ?>;
        var tienePendFb  = <?= !empty($feedbacksPendLider) ? 'true' : 'false' ?>;

        function ic(name, color) {
            color = color || '#0058af';
            return '<i class="ti ti-' + name + '" style="color:' + color +
                   ';font-size:16px;flex-shrink:0;"></i>';
        }

        // Resolver elemento DOM para "Mi equipo" (segunda .hm-card si existe)
        var cards        = document.querySelectorAll('.hm-card');
        var equipoCard   = (esLider && cards.length > 1) ? cards[1] : null;

        var pasos = [];

        // ── 0. Bienvenida ───────────────────────────────────────────────────
        pasos.push({
            popover: {
                title:       ic('home') + ' Panel de inicio',
                description: 'Este es tu punto de partida en la plataforma de Evaluación de Desempeño. ' +
                             'Desde aquí ves tu progreso personal, accedes a evaluaciones, ' +
                             'revisas tu plan de mejora y navegas a cualquier módulo de forma rápida.',
                side: 'over', align: 'center'
            }
        });

        // ── 1. Hero — nombre, cargo y anillo ───────────────────────────────
        if (document.querySelector('.hm-hero')) {
            pasos.push({
                element: '.hm-hero',
                popover: {
                    title:       ic('user-circle') + ' Tu perfil y estado del período',
                    description: 'Muestra tu nombre, cargo y los días restantes del período activo. ' +
                                 'Los <strong>anillos</strong> a la derecha reflejan el porcentaje completado ' +
                                 'de evaluaciones y plan de mejora.',
                    side: 'bottom', align: 'start'
                }
            });
        }

        // ── 2. Ring de progreso ─────────────────────────────────────────────
        if (document.querySelector('.hm-ring-wrap')) {
            pasos.push({
                element: '.hm-ring-wrap',
                popover: {
                    title:       ic('chart-donut') + ' Progreso de evaluaciones',
                    description: 'El indicador circular refleja cuántas de tus evaluaciones completaste: ' +
                                 'autoevaluación, evaluación recibida y evaluación a otros. ' +
                                 '<strong>100%</strong> significa que las tres están listas.',
                    side: 'left', align: 'center'
                }
            });
        }

        if (tienePeriodo) {

            // ── 3. Card: Mis evaluaciones ───────────────────────────────────
            if (document.querySelector('.hm-card')) {
                pasos.push({
                    element: '.hm-card',
                    popover: {
                        title:       ic('clipboard-list') + ' Mis evaluaciones',
                        description: 'Resume los 3 pasos de evaluación del período. ' +
                                     'Los badges verdes indican completado, los amarillos están pendientes.',
                        side: 'right', align: 'start'
                    }
                });
            }

            // ── 4. Autoevaluación ───────────────────────────────────────────
            if (document.querySelector('.hm-steps .hm-step')) {
                pasos.push({
                    element: '.hm-steps .hm-step:first-child',
                    popover: {
                        title:       ic('file-pencil') + ' Autoevaluación',
                        description: 'Evalúas tu propio desempeño en las competencias del período. ' +
                                     'Haz clic aquí o en <strong>Iniciar evaluación</strong> para completarla. ' +
                                     'Es el primer paso obligatorio.',
                        side: 'bottom', align: 'start'
                    }
                });
            }

            // ── 5. Evaluó a otros ───────────────────────────────────────────
            if (document.querySelector('.hm-steps .hm-step:last-child')) {
                pasos.push({
                    element: '.hm-steps .hm-step:last-child',
                    popover: {
                        title:       ic('users') + ' Evaluó a otros',
                        description: 'Indica cuántas evaluaciones realizaste sobre tus compañeros o equipo. ' +
                                     'El formato <strong>X/Y</strong> muestra completadas versus total requerido.',
                        side: 'top', align: 'start'
                    }
                });
            }

            // ── 6. Mi equipo (solo líderes) ─────────────────────────────────
            if (esLider && equipoCard) {
                pasos.push({
                    element: equipoCard,
                    popover: {
                        title:       ic('users-group') + ' Mi equipo',
                        description: 'Como líder ves cuántos colaboradores a tu cargo completaron sus evaluaciones. ' +
                                     'La barra de progreso muestra el avance general del equipo en el período.',
                        side: 'left', align: 'start'
                    }
                });
            }

            // ── 7. Widget feedbacks pendientes (solo líderes/directores) ────
            if (esLiderFn && tienePendFb && document.getElementById('hm-pend-widget')) {
                pasos.push({
                    element: '#hm-pend-widget',
                    popover: {
                        title:       ic('bell','#d97706') + ' Sesiones de feedback pendientes',
                        description: 'Este panel aparece cuando tienes reuniones de retroalimentación ' +
                                     'programadas que aún no se han realizado ni firmado.<br><br>' +
                                     '<strong style="color:#1d4ed8;">Flujo 1 · Desempeño</strong> — reunión líder → colaborador.<br>' +
                                     '<strong style="color:#6d28d9;">Flujo 2 · Liderazgo</strong> — reunión director → líder.<br>' +
                                     '<strong style="color:#0891b2;">Flujo 3 · Experiencia Azul</strong> — reunión líder → personal asistencial.<br><br>' +
                                     'Cada tarjeta muestra el nombre y la fecha pactada. ' +
                                     'El chip <strong>Sin firma</strong> desaparecerá cuando el colaborador o líder ' +
                                     'firme el recibido en la sesión de <strong>Feedback</strong>.',
                        side: 'bottom', align: 'start'
                    }
                });
            }

            // ── 8. Plan de mejora (si tiene acuerdos) ──────────────────────
            if ((tieneColab || tieneLider) && document.querySelector('.hm-plan-row')) {
                pasos.push({
                    element: '.hm-plan-row',
                    popover: {
                        title:       ic('target') + ' Mi plan de mejora',
                        description: 'Muestra los <strong>Objetivos SMART</strong> que tu líder te asignó en la sesión de feedback. ' +
                                     '<strong>Pendientes</strong> esperan tu respuesta, <strong>en revisión</strong> los ve tu líder y ' +
                                     '<strong>aprobados</strong> están cerrados.',
                        side: 'bottom', align: 'start'
                    }
                });
            }

        } // fin tienePeriodo

        // ── 8. Acciones rápidas ─────────────────────────────────────────────
        if (document.querySelector('.hm-action')) {
            pasos.push({
                element: document.querySelector('.hm-action').parentElement,
                popover: {
                    title:       ic('bolt') + ' Acciones rápidas',
                    description: 'Accesos directos a los módulos más usados:<br><br>' +
                                 ic('rocket','#64748b') + ' <strong>Iniciar evaluación</strong> — autoevaluación y evaluación a otros<br>' +
                                 (esLiderFn ? ic('message-circle','#64748b') + ' <strong>Dar feedback</strong> — reunión y objetivos SMART<br>' : '') +
                                 ic('bar-chart','#64748b') + ' <strong>Ver reportes</strong> — resultados y plan de mejora',
                    side: 'top', align: 'start'
                }
            });
        }

        // ── Final ───────────────────────────────────────────────────────────
        pasos.push({
            popover: {
                title:       ic('circle-check','#15803d') + ' Listo para comenzar',
                description: 'Ya conoces el panel de inicio. Navega usando el menú lateral o las acciones rápidas. ' +
                             'Puedes repetir este tour cuando quieras pulsando el botón ' +
                             '<span style="display:inline-flex;align-items:center;justify-content:center;' +
                             'width:20px;height:20px;border-radius:50%;vertical-align:middle;margin:0 2px;' +
                             'background:linear-gradient(135deg,#0058af,#0074e0);' +
                             'color:#fff;font-size:11px;font-weight:700;line-height:1;">?</span>' +
                             ' en la esquina inferior derecha.',
                side: 'over', align: 'center'
            }
        });

        var tour;
        tour = driverFn({
            animate:      true,
            overlayColor: 'rgba(5, 15, 45, 0.60)',
            smoothScroll: true,
            showProgress: true,
            progressText: 'Paso {{current}} de {{total}}',
            nextBtnText:  'Siguiente',
            prevBtnText:  'Anterior',
            doneBtnText:  'Entendido',
            steps:        pasos,
            onNextClick: function() {
                if (tour.hasNextStep()) {
                    tour.moveNext();
                } else {
                    tour.destroy();
                }
            },
            onCloseClick: function() {
                tour.destroy();
            }
        });

        tour.drive();
    });
}
</script>
