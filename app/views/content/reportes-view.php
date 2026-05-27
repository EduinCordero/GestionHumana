<?php
// ── Variables del controlador ─────────────────────────────────────────────
$idEmpCargo          = $idEmpCargo          ?? '';
$activeTab           = $activeTab           ?? 'autoeval';
$nivelCargo          = $_SESSION['nivelcargo']      ?? '';
$verDetalleRep       = $_SESSION['ver_detalle_rep'] ?? 0;
$puedeVerDetalle     = $verDetalleRep == 1;
$idTrabajo           = $idTrabajo           ?? ($idEmpleado ?? 0);
$idEmpleado          = $idEmpleado          ?? 0;
$searchTerm          = $searchTerm          ?? '';
$resultadosBusqueda  = $resultadosBusqueda  ?? [];
$periodoActivo       = $periodoActivo       ?? null;
$acuerdosColaborador = $acuerdosColaborador ?? [];
$colsConMejora       = $colsConMejora       ?? [];
$estadoPlanEquipo    = $estadoPlanEquipo    ?? [];

// Variables por defecto para evitar errores
$resumenEquipo = $resumenEquipo ?? ['total' => 0, 'completo' => 0, 'en_progreso' => 0, 'sin_iniciar' => 0];

// ── Datos V2 — vienen indexados por IDCOMPETENCIA ────────────────────────
// $autoeval      = [IDCOMPETENCIA => ['VALOR'=>N, 'ETIQUETA'=>'...', 'NUM_PREGUNTA'=>N, 'NOMBRE_COMP'=>'...']]
// $recibidasColab = [[ID_EVALUADOR, EVALUADOR, RESPUESTAS[IDCOMPETENCIA=>datos]]]
// $recibidasLider = igual estructura
// $realizadasColab/Lider = [[ID_EVALUADO, EVALUADO, CARGO_EVALUADO, FECHACONFIRMA]]
// $agregados['desempeno'] = [IDCOMPETENCIA => ['PROMEDIO'=>3.5, 'NUM_PREGUNTA'=>N, 'NOMBRE_COMP'=>'...']]
// $agregados['liderazgo'] = igual
$autoeval        = $autoeval        ?? [];
$recibidasColab  = $recibidasColab  ?? [];
$recibidasLider  = $recibidasLider  ?? [];
$realizadasColab = $realizadas['colab'] ?? [];
$realizadasLider = $realizadas['lider'] ?? [];
$agregados       = $agregados       ?? ['desempeno' => [], 'liderazgo' => []];
$evaluadores     = $evaluadores     ?? [];

// ── Diccionarios desde BD ─────────────────────────────────────────────────
// $dictColab  = [IDCOMPETENCIA => NOMBRE] para competencias 1-11
// $dictLider  = [IDCOMPETENCIA => NOMBRE] para competencias 12-16
$dictColab = $dictColab ?? [];
$dictLider = $dictLider ?? [];

// ── Escala numérica V2 ────────────────────────────────────────────────────
// Escala Desempeño General (dimensiones 1-4)
$escala = [
    'Sobresaliente'   => 5,
    'Acorde'          => 4,
    'Aceptable'       => 3,
    'Requiere mejora' => 2,
    'Insuficiente'    => 1,
];

// Escala Experiencia Azul (dimensiones 5-6)
$escalaAzul = [
    'Referente'   => 5,
    'Consistente' => 4,
    'Esperado'    => 3,
    'Inconsistente'=> 2,
    'Crítico'     => 1,
];

// ── Helpers para gráficas — extraen labels y valores de datos V2 ──────────

// Datos autoevaluación para Chart.js
$autoLabels = [];
$autoValues = [];
foreach ($autoeval as $idComp => $datos) {
    if (($datos['NUM_PREGUNTA'] ?? 0) > 11) continue; // solo competencias 1-11
    $autoLabels[] = mb_substr($datos['NOMBRE_COMP'] ?? '', 0, 22) . (mb_strlen($datos['NOMBRE_COMP'] ?? '') > 22 ? '…' : '');
    $autoValues[] = $datos['VALOR'] ?? 0;
}

// Datos indicadores para Chart.js
$indLabels    = [];
$indAutoVals  = [];
$indLiderVals = [];

// Lider recibido más reciente (primer evaluador de LIDER_A_COLAB)
$liderReciente = !empty($recibidasColab) ? $recibidasColab[0] : null;

foreach ($dictColab as $idComp => $nombre) {
    $indLabels[]   = mb_substr($nombre, 0, 20) . '…';
    $autoV  = $autoeval[$idComp]['VALOR'] ?? 0;
    $liderV = $liderReciente ? ($liderReciente['RESPUESTAS'][$idComp]['VALOR'] ?? 0) : 0;
    $indAutoVals[]  = $autoV;
    $indLiderVals[] = $liderV;
}

// Radar labels y valores para indicadores agregados
$radarLabels    = [];
$radarDesempeno = [];
$radarLiderazgo = [];

foreach ($agregados['desempeno'] as $idComp => $datos) {
    $radarLabels[]    = mb_substr($datos['NOMBRE_COMP'] ?? '', 0, 18) . '…';
    $radarDesempeno[] = $datos['PROMEDIO'] ?? 0;
}
foreach ($agregados['liderazgo'] as $idComp => $datos) {
    $radarLiderazgo[] = $datos['PROMEDIO'] ?? 0;
}

// ── Promedios generales ───────────────────────────────────────────────────

// Promedio autoevaluación global
$sumaAutoG = 0; $cntAutoG = 0;
foreach ($autoeval as $idComp => $datos) {
    if (($datos['NUM_PREGUNTA'] ?? 0) > 11) continue;
    if ($datos['VALOR'] ?? 0) { $sumaAutoG += $datos['VALOR']; $cntAutoG++; }
}
$promedioAutoG = $cntAutoG ? round($sumaAutoG / $cntAutoG, 2) : 0;

// Promedio lider (primer evaluador recibido)
$sumaLiderG = 0; $cntLiderG = 0;
if ($liderReciente) {
    foreach ($liderReciente['RESPUESTAS'] ?? [] as $idComp => $resp) {
        if ($resp['VALOR'] ?? 0) { $sumaLiderG += $resp['VALOR']; $cntLiderG++; }
    }
}
$promedioLiderG = $cntLiderG ? round($sumaLiderG / $cntLiderG, 2) : 0;
$promedioFinal  = ($cntAutoG && $cntLiderG) ? round(($sumaAutoG + $sumaLiderG) / ($cntAutoG + $cntLiderG), 2) : 0;

// Promedio agregados desempeño
$finalD = 0;
if (!empty($agregados['desempeno'])) {
    $sum = array_sum(array_column($agregados['desempeno'], 'PROMEDIO'));
    $cnt = count($agregados['desempeno']);
    $finalD = $cnt ? round($sum / $cnt, 2) : 0;
}

// Promedio agregados liderazgo
$finalL = 0;
if (!empty($agregados['liderazgo'])) {
    $sum = array_sum(array_column($agregados['liderazgo'], 'PROMEDIO'));
    $cnt = count($agregados['liderazgo']);
    $finalL = $cnt ? round($sum / $cnt, 2) : 0;
}

$totalGeneral = ($finalD || $finalL)
    ? round((($finalD ?: 0) + ($finalL ?: 0)) / (($finalD ? 1 : 0) + ($finalL ? 1 : 0)), 2)
    : 0;

// ── Tabs disponibles ─────────────────────────────────────────────────────
$tabs = [
    'autoeval'    => ['label' => 'Autoevaluación',    'icon' => '<i class="ti ti-user-check"></i>'],
    'recibidas'   => ['label' => 'Recibidas',          'icon' => '<i class="ti ti-arrow-down-circle"></i>'],
    'realizadas'  => ['label' => 'Realizadas',         'icon' => '<i class="ti ti-arrow-up-circle"></i>'],
];
if (in_array($nivelCargo, ['NC002','NC003','NC004','NC005'])) {
    $tabs['equipo'] = ['label' => 'Mi Equipo', 'icon' => '<i class="ti ti-users"></i>'];
}
$tabs['plan'] = ['label' => 'Mi Plan de Mejora', 'icon' => '<i class="ti ti-target"></i>'];

// Variables para la sección Líder dentro del tab plan
$acuerdosLider    = $acuerdosLider    ?? [];
$esLiderFuncional = $esLiderFuncional ?? false;

// cntAuto para stat card
$cntAuto = $cntAutoG;

// ── Helpers ───────────────────────────────────────────────────────────────
function colorScore($v) {
    if ($v >= 4.5) return '#10b981';
    if ($v >= 3.5) return '#3b82f6';
    if ($v >= 2.5) return '#f59e0b';
    return '#ef4444';
}
function labelScore($v) {
    if ($v >= 4.5) return 'Sobresaliente';
    if ($v >= 3.5) return 'Acorde';
    if ($v >= 2.5) return 'Aceptable';
    if ($v > 0)    return 'Requiere mejora';
    return 'Sin datos';
}
?>

<!--- ══════════════════════════════════════════════
      ESTILOS INTERNOS DE LA VISTA DE REPORTES
══════════════════════════════════════════════ --->
<style>
/* ── Fuentes ── */
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

/* ── Variables ── */
:root {
    --rep-bg:        #f0f4ff;
    --rep-card:      #ffffff;
    --rep-border:    #e2e8f0;
    --rep-text:      #1e293b;
    --rep-muted:     #64748b;
    --rep-accent:    #0058af;
    --rep-accent2:   #0058af;/*-#7c3aed*/
    --rep-green:     #10b981;
    --rep-amber:     #f59e0b;
    --rep-red:       #ef4444;
    --rep-blue:      #3b82f6;
    --rep-radius:    16px;
    --rep-shadow:    0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(79,70,229,.08);
    --rep-shadow-lg: 0 8px 32px rgba(79,70,229,.14);
}

/* ── Reset dentro de la sección ── */
.rp-root * { box-sizing: border-box; }
.rp-root { font-family: 'DM Sans', sans-serif; color: var(--rep-text); }

/* ── Layout ── */
.rp-wrap    { padding: 68px 20px 60px; max-width: 1280px; margin: 0 auto; }
.rp-header  { margin-bottom: 20px; }
.rp-hero    { display: flex; align-items: center; gap: 16px; margin-bottom: 6px; }
.rp-icon    { width: 48px; height: 48px; border-radius: 14px;
              background: linear-gradient(135deg, var(--rep-accent), var(--rep-accent2));
              display: flex; align-items: center; justify-content: center;
              font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(79,70,229,.35); }
.rp-title   { font-size: clamp(1.4rem, 3vw, 2rem); font-weight: 700; letter-spacing: -.5px; }
.rp-subtitle{ font-size: .9rem; color: var(--rep-muted); margin-top: 2px; }

/* ── Tabs ── */
.rp-tabs    { display: flex; gap: 4px; background: var(--rep-card);
              padding: 6px; border-radius: 14px; border: 1px solid var(--rep-border);
              box-shadow: var(--rep-shadow); overflow-x: auto; margin-bottom: 28px;
              scrollbar-width: none; }
.rp-tabs::-webkit-scrollbar { display: none; }
.rp-tab     { flex-shrink: 0; padding: 8px 18px; border-radius: 10px;
              font-size: .85rem; font-weight: 600; cursor: pointer;
              border: none; background: transparent; color: var(--rep-muted);
              transition: all .2s; display: flex; align-items: center; gap: 6px; }
.rp-tab:hover { background: #f1f5f9; color: var(--rep-text); }
.rp-tab.active { background: linear-gradient(135deg, var(--rep-accent), var(--rep-accent2));
                 color: #fff; box-shadow: 0 2px 8px rgba(79,70,229,.3); }
.rp-tab-icon { font-size: 1.1rem; opacity: .85; line-height: 1; display: flex; align-items: center; }

/* ── Paneles ── */
.rp-panel { display: none; animation: rpFadeIn .3s ease; }
.rp-panel.active { display: block; }
@keyframes rpFadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

/* ── Cards ── */
.rp-card    { background: var(--rep-card); border-radius: var(--rep-radius);
              border: 1px solid var(--rep-border); box-shadow: var(--rep-shadow);
              padding: 24px; }
.rp-card-sm { padding: 18px; }
.rp-grid-2  { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px; }
.rp-grid-3  { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }

/* ── Stat cards ── */
.rp-stat    { border-radius: var(--rep-radius); padding: 24px 20px;
              position: relative; overflow: hidden; }
.rp-stat-bg { position: absolute; inset: 0; opacity: .07; }
.rp-stat-label { font-size: .72rem; font-weight: 700; letter-spacing: .08em;
                 text-transform: uppercase; margin-bottom: 8px; }
.rp-stat-value { font-size: 2.6rem; font-weight: 700; line-height: 1;
                 font-family: 'DM Mono', monospace; }
.rp-stat-sub   { font-size: .78rem; margin-top: 6px; opacity: .7; }
.rp-stat-pill  { display: inline-block; font-size: .7rem; font-weight: 700;
                 padding: 2px 10px; border-radius: 20px;
                 background: rgba(255,255,255,.25); margin-top: 8px; }

/* ── Chart wrapper ── */
.rp-chart-wrap { position: relative; }
.rp-chart-title { font-size: .82rem; font-weight: 700; letter-spacing: .04em;
                  text-transform: uppercase; color: var(--rep-muted); margin-bottom: 16px; }
canvas { display: block; }

/* ── Tabla moderna ── */
.rp-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.rp-table thead th { background: #f8fafc; color: var(--rep-muted);
                     font-size: .72rem; font-weight: 700; letter-spacing: .06em;
                     text-transform: uppercase; padding: 10px 14px;
                     border-bottom: 2px solid var(--rep-border); text-align: left; }
.rp-table thead th:not(:first-child) { text-align: center; }
.rp-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
.rp-table tbody tr:hover { background: #f8fafc; }
.rp-table tbody td { padding: 11px 14px; vertical-align: middle; }
.rp-table tbody td:not(:first-child) { text-align: center; }
.rp-table tfoot td { padding: 12px 14px; background: #f8fafc;
                     font-weight: 700; border-top: 2px solid var(--rep-border); }
.rp-table tfoot td:not(:first-child) { text-align: center; }

/* ── Badges ── */
.rp-badge { display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: .72rem; font-weight: 700; }
.rp-badge-5 { background: #d1fae5; color: #065f46; }
.rp-badge-4 { background: #dbeafe; color: #1e40af; }
.rp-badge-3 { background: #fef3c7; color: #92400e; }
.rp-badge-2 { background: #fee2e2; color: #991b1b; }
.rp-badge-0 { background: #f1f5f9; color: #64748b; }

/* ── Brecha ── */
.rp-brecha-pos { color: #059669; font-weight: 700; }
.rp-brecha-neg { color: #dc2626; font-weight: 700; }
.rp-brecha-zer { color: #94a3b8; }

/* ── Progress bar ── */
.rp-bar { height: 6px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
.rp-bar-fill { height: 100%; border-radius: 4px; transition: width 1s ease; }

/* ── Evaluadores ── */
.rp-ev-card { display: flex; align-items: center; gap: 12px;
              padding: 12px 16px; background: #f8fafc;
              border-radius: 12px; border: 1px solid #e2e8f0; }
.rp-ev-avatar { width: 38px; height: 38px; border-radius: 50%;
                background: linear-gradient(135deg, var(--rep-accent), var(--rep-accent2));
                color: #fff; display: flex; align-items: center; justify-content: center;
                font-weight: 700; font-size: .85rem; flex-shrink: 0; }
.rp-ev-name  { font-weight: 600; font-size: .85rem; line-height: 1.3; }
.rp-ev-meta  { font-size: .72rem; color: var(--rep-muted); }

/* ── Búsqueda seguimiento ── */
.rp-search { display: flex; gap: 10px; margin-bottom: 24px; }
.rp-search input { flex: 1; padding: 10px 16px; border: 1.5px solid var(--rep-border);
                   border-radius: 10px; font-size: .9rem; font-family: inherit;
                   outline: none; transition: border-color .2s; background: #fff; }
.rp-search input:focus { border-color: var(--rep-accent); }
.rp-search button { padding: 10px 22px; background: linear-gradient(135deg, var(--rep-accent), var(--rep-accent2));
                    color: #fff; border: none; border-radius: 10px;
                    font-weight: 700; font-size: .88rem; cursor: pointer;
                    font-family: inherit; transition: opacity .2s; }
.rp-search button:hover { opacity: .9; }

/* ── Estado vacío ── */
.rp-empty { text-align: center; padding: 48px 20px; color: var(--rep-muted); }
.rp-empty-icon { font-size: 2.5rem; margin-bottom: 12px; opacity: .4; }
.rp-empty-text { font-size: .9rem; }

/* ── Fecha chip ── */
.rp-date { font-size: .72rem; font-family: 'DM Mono', monospace;
           background: #f1f5f9; padding: 2px 8px; border-radius: 6px;
           color: var(--rep-muted); }

/* ── Sección encabezado ── */
.rp-section-head { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.rp-section-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.rp-section-title { font-size: 1rem; font-weight: 700; }
.rp-section-sub   { font-size: .8rem; color: var(--rep-muted); margin-top: 2px; }

/* ── Animación entrada tarjetas ── */
.rp-animate { opacity: 0; transform: translateY(12px);
              animation: rpSlideUp .4s ease forwards; }
.rp-animate:nth-child(2) { animation-delay: .08s; }
.rp-animate:nth-child(3) { animation-delay: .16s; }
@keyframes rpSlideUp { to { opacity:1; transform:translateY(0); } }

/* ── Responsive ── */
@media (max-width: 640px) {
    .rp-wrap { padding: 4px 12px 40px; }
    .rp-stat-value { font-size: 2rem; }
    .rp-grid-2 { grid-template-columns: 1fr; }
}
</style>

<!-- ══════════════════════════════════════════════
     ESTRUCTURA PRINCIPAL
══════════════════════════════════════════════ -->
<div class="rp-root">
<div class="rp-wrap">

    <!-- Hero — mismo diseño que Feedback -->
    <?php
    $cierreDt2 = $periodoActivo ? DateTime::createFromFormat('d/m/Y', $periodoActivo['FECHACIERRE']) : null;
    $diasRest2 = $cierreDt2 ? ceil(($cierreDt2->getTimestamp() - time()) / 86400) : 0;
    if ($diasRest2 <= 0)        { $colorP2='#dc2626'; $labelP2='Período cerrado'; }
    elseif ($diasRest2 <= 3)    { $colorP2='#f97316'; $labelP2="Cierra en $diasRest2 día(s)"; }
    elseif ($diasRest2 <= 7)    { $colorP2='#eab308'; $labelP2="Cierra en $diasRest2 días"; }
    else                        { $colorP2='#0058af'; $labelP2="$diasRest2 días restantes"; }
    ?>
    <div id="rp-hero" style="background:linear-gradient(135deg,#0058af 0%,#2563eb 100%);
                border-radius:20px;padding:28px 36px;
                display:flex;align-items:center;justify-content:space-between;gap:24px;
                margin-bottom:20px;position:relative;overflow:hidden;
                box-shadow:0 4px 20px rgba(0,88,175,0.2);">
        <div style="position:absolute;width:280px;height:280px;border-radius:50%;
                    background:rgba(255,255,255,0.06);top:-80px;right:-60px;"></div>
        <div style="z-index:1;">
            <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;
                        color:rgba(255,255,255,0.55);margin-bottom:6px;
                        font-family:'Plus Jakarta Sans',sans-serif;">
                Clínica Zayma · Evaluación de Desempeño
            </div>
            <div style="font-size:22px;font-weight:600;color:#fff;margin-bottom:4px;
                        font-family:'Plus Jakarta Sans',sans-serif;">
                Mis Reportes de Evaluación
            </div>
            <div style="font-size:13px;color:rgba(255,255,255,0.6);">
                Resultados, indicadores y seguimiento de desempeño
            </div>
        </div>
        <div style="z-index:1;flex-shrink:0;">
            <?php if ($periodoActivo): ?>
            <div style="display:inline-flex;align-items:center;gap:7px;
                        border-radius:99px;padding:6px 14px;font-size:12px;font-weight:500;
                        background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);color:#fff;">
                <div style="width:7px;height:7px;border-radius:50%;background:<?= $colorP2 ?>;
                            animation:fbPulse 2s infinite;"></div>
                <?= htmlspecialchars($periodoActivo['NOMBRE']) ?> · <?= $labelP2 ?>
            </div>
            <?php else: ?>
            <div style="display:inline-flex;align-items:center;gap:7px;
                        border-radius:99px;padding:6px 14px;font-size:12px;
                        background:rgba(239,68,68,0.2);border:1px solid rgba(239,68,68,0.4);color:#fff;">
                <?= icon('alert-circle', 14) ?> Sin período activo
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pestañas -->
    <div class="rp-tabs" id="rpTabs">
        <?php foreach ($tabs as $id => $info): ?>
        <button class="rp-tab <?= $id === $activeTab ? 'active' : '' ?>"
                onclick="rpSwitch('<?= $id ?>', this)">
            <span class="rp-tab-icon"><?= $info['icon'] ?></span>
            <?= $info['label'] ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- ══════════════════════════════════
         PANEL 1 — AUTOEVALUACIÓN
    ══════════════════════════════════ -->
    <div id="rp-autoeval" class="rp-panel <?= $activeTab === 'autoeval' ? 'active' : '' ?>">
        <?php if (!empty($autoeval)): ?>

        <!-- Stat cards superiores -->
        <div class="rp-grid-3 rp-animate" style="margin-bottom:24px;">
            <?php
            $promedioAuto = $promedioAutoG;
            $colorAuto    = colorScore($promedioAuto);
            $labelAuto    = labelScore($promedioAuto);
            ?>
            <div class="rp-stat rp-animate" style="background:linear-gradient(135deg,<?= $colorAuto ?>22,<?= $colorAuto ?>11);border:1.5px solid <?= $colorAuto ?>44;">
                <div class="rp-stat-label" style="color:<?= $colorAuto ?>">Promedio global</div>
                <div class="rp-stat-value" style="color:<?= $colorAuto ?>"><?= $promedioAuto ?></div>
                <div class="rp-stat-sub">de 5.0 posibles</div>
                <div class="rp-stat-pill" style="background:<?= $colorAuto ?>22;color:<?= $colorAuto ?>"><?= $labelAuto ?></div>
            </div>
            <div class="rp-stat rp-animate" style="background:linear-gradient(135deg,#6366f122,#6366f111);border:1.5px solid #6366f144;">
                <div class="rp-stat-label" style="color:#6366f1">Competencias</div>
                <div class="rp-stat-value" style="color:#6366f1"><?= $cntAuto ?></div>
                <div class="rp-stat-sub">evaluadas de <?= count($autoeval) ?></div>
            </div>
            <div class="rp-stat rp-animate" style="background:linear-gradient(135deg,#f59e0b22,#f59e0b11);border:1.5px solid #f59e0b44;">
                <div class="rp-stat-label" style="color:#d97706">Última evaluación</div>
                <?php
                $fechaAuto = '';
                foreach ($autoeval as $d) { if (!empty($d['FECHARESPUESTA'])) { $fechaAuto = $d['FECHARESPUESTA']; break; } }
                ?>
                <div class="rp-stat-value" style="color:#d97706;font-size:1.4rem;"><?= $fechaAuto ? substr($fechaAuto, 0, 10) : '—' ?></div>
                <div class="rp-stat-sub">Registro más reciente</div>
            </div>
        </div>

        <div class="rp-grid-2">
            <!-- Gráfica radar -->
            <div class="rp-card">
                <div class="rp-chart-title">Perfil de competencias</div>
                <div class="rp-chart-wrap" style="height:280px;">
                    <canvas id="chartAutoRadar"></canvas>
                </div>
            </div>
            <!-- Gráfica barras -->
            <div class="rp-card">
                <div class="rp-chart-title">Calificación por competencia</div>
                <div class="rp-chart-wrap" style="height:280px;">
                    <canvas id="chartAutoBars"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla detalle -->
        <div class="rp-card" style="margin-top:20px;">
            <div class="rp-chart-title" style="margin-bottom:12px;">Detalle por competencia</div>
            <div style="overflow-x:auto;">
                <table class="rp-table">
                    <thead>
                        <tr>
                            <th>Competencia</th>
                            <th>Calificación</th>
                            <th style="width:180px;">Nivel</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($autoeval as $idComp => $datos):
                            if (($datos['NUM_PREGUNTA'] ?? 0) > 11) continue;
                            $raw = $datos['ETIQUETA'] ?? '';
                            $val = $datos['VALOR']    ?? 0;
                            $pct = $val * 20;
                            $nombre = $datos['NOMBRE_COMP'] ?? '';
                            $badgeClass = $val >= 4.5 ? 'rp-badge-5' : ($val >= 3.5 ? 'rp-badge-4' : ($val >= 2.5 ? 'rp-badge-3' : ($val > 0 ? 'rp-badge-2' : 'rp-badge-0')));
                        ?>
                        <tr>
                            <td style="font-weight:500;"><?= htmlspecialchars($nombre) ?></td>
                            <td><span class="rp-badge <?= $badgeClass ?>"><?= $raw ?: 'N/A' ?></span></td>
                            <td>
                                <div class="rp-bar">
                                    <div class="rp-bar-fill" style="width:<?= $pct ?>%;background:<?= colorScore($val) ?>;"></div>
                                </div>
                            </td>
                            <td style="font-family:'DM Mono',monospace;font-weight:600;color:<?= colorScore($val) ?>;"><?= $val ?: '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php else: ?>
        <div class="rp-card">
            <div class="rp-empty">
                <div class="rp-empty-icon"><?= icon('clipboard', 32) ?></div>
                <div class="rp-empty-text">No se encontraron registros de autoevaluación.</div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════
         PANEL 2 — RECIBIDAS
    ══════════════════════════════════ -->
    <div id="rp-recibidas" class="rp-panel <?= $activeTab === 'recibidas' ? 'active' : '' ?>">
        <?php
        $totalRec      = count($recibidasColab);
        $totalRecLider = count($recibidasLider);
        $esLiderRep    = in_array($nivelCargo, ['NC002','NC003','NC004','NC005']);
        ?>
        <div class="rp-grid-2">
            <!-- Como Colaborador -->
            <div class="rp-card" style="display:flex;flex-direction:column;align-items:center;
                                        justify-content:center;padding:40px 24px;text-align:center;">
                <div style="font-size:11px;letter-spacing:1.5px;text-transform:uppercase;
                            color:var(--rep-muted);margin-bottom:20px;font-weight:600;">
                    Como Colaborador
                </div>
                <?php if ($totalRec > 0): ?>
                <div style="font-size:56px;font-weight:800;color:#0058af;
                            font-family:'DM Mono',monospace;line-height:1;margin-bottom:8px;">
                    <?= $totalRec ?>
                </div>
                <div style="font-size:.85rem;color:#475569;margin-bottom:6px;">
                    evaluación<?= $totalRec > 1 ? 'es' : '' ?> recibida<?= $totalRec > 1 ? 's' : '' ?>
                </div>
                <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 14px;
                            border-radius:20px;background:#e0f2fe;color:#0284c7;font-size:.78rem;font-weight:600;">
                    <?= icon('check-circle', 13) ?> Tu desempeño fue evaluado
                </div>
                <?php else: ?>
                <div style="font-size:40px;font-weight:800;color:#cbd5e1;font-family:'DM Mono',monospace;
                            line-height:1;margin-bottom:8px;">—</div>
                <div style="font-size:.85rem;color:var(--rep-muted);">
                    Aún no has recibido evaluaciones en este período.
                </div>
                <?php endif; ?>
            </div>

            <!-- Como Líder -->
            <?php if ($esLiderRep): ?>
            <div class="rp-card" style="display:flex;flex-direction:column;align-items:center;
                                        justify-content:center;padding:40px 24px;text-align:center;">
                <div style="font-size:11px;letter-spacing:1.5px;text-transform:uppercase;
                            color:var(--rep-muted);margin-bottom:20px;font-weight:600;">
                    Como Líder
                </div>
                <?php if ($totalRecLider > 0): ?>
                <div style="font-size:56px;font-weight:800;color:#0058af;
                            font-family:'DM Mono',monospace;line-height:1;margin-bottom:8px;">
                    <?= $totalRecLider ?>
                </div>
                <div style="font-size:.85rem;color:#475569;margin-bottom:6px;">
                    evaluación<?= $totalRecLider > 1 ? 'es' : '' ?> de liderazgo recibida<?= $totalRecLider > 1 ? 's' : '' ?>
                </div>
                <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 14px;
                            border-radius:20px;background:#dbeafe;color:#1e40af;font-size:.78rem;font-weight:600;">
                    <?= icon('check-circle', 13) ?> Tu liderazgo fue evaluado
                </div>
                <?php else: ?>
                <div style="font-size:40px;font-weight:800;color:#cbd5e1;font-family:'DM Mono',monospace;
                            line-height:1;margin-bottom:8px;">—</div>
                <div style="font-size:.85rem;color:var(--rep-muted);">
                    Aún no has recibido evaluaciones de liderazgo.
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="rp-card" style="display:flex;align-items:center;justify-content:center;padding:32px;">
                <div style="text-align:center;color:var(--rep-muted);">
                    <div style="margin-bottom:10px;"><?= icon('user', 32) ?></div>
                    <div style="font-size:.85rem;">No aplica evaluación de liderazgo para tu rol.</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════
         PANEL 3 — REALIZADAS
    ══════════════════════════════════ -->
    <div id="rp-realizadas" class="rp-panel <?= $activeTab === 'realizadas' ? 'active' : '' ?>">
        <div class="rp-grid-2">
            <div class="rp-card">
                <div class="rp-section-head">
                    <div class="rp-section-dot" style="background:#10b981;"></div>
                    <div>
                        <div class="rp-section-title">A Colaboradores</div>
                        <div class="rp-section-sub"><?= count($realizadasColab) ?> evaluaciones realizadas</div>
                    </div>
                </div>
                <?php if (!empty($realizadasColab)): ?>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <?php foreach ($realizadasColab as $r): ?>
                        <div class="rp-ev-card" style="cursor:pointer;"
                             onclick="verCalificaciones(<?= $r['ID_EVALUADO'] ?>, '<?= addslashes(htmlspecialchars($r['EVALUADO'] ?? '')) ?>', 'LIDER_A_COLAB')">
                            <div class="rp-ev-avatar" style="background:linear-gradient(135deg,#10b981,#059669);">
                                <?= mb_strtoupper(mb_substr($r['EVALUADO'] ?? 'E', 0, 1)) ?>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="rp-ev-name"><?= htmlspecialchars($r['EVALUADO'] ?? '—') ?></div>
                                <div class="rp-ev-meta"><?= htmlspecialchars($r['CARGO_EVALUADO'] ?? '') ?></div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="rp-date"><?= $r['FECHACONFIRMA'] ?? '—' ?></span>
                                <span style="font-size:.72rem;color:#0058af;font-weight:600;white-space:nowrap;"><?= icon('eye', 13) ?> Ver</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="rp-empty"><div class="rp-empty-icon"><?= icon('pen', 32) ?></div><div class="rp-empty-text">No has realizado evaluaciones a colaboradores.</div></div>
                <?php endif; ?>
            </div>

            <div class="rp-card">
                <div class="rp-section-head">
                    <div class="rp-section-dot" style="background:#f59e0b;"></div>
                    <div>
                        <div class="rp-section-title">De Liderazgo</div>
                        <div class="rp-section-sub"><?= count($realizadasLider) ?> evaluaciones realizadas</div>
                    </div>
                </div>
                <?php if (!empty($realizadasLider)): ?>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <?php foreach ($realizadasLider as $r): ?>
                        <div class="rp-ev-card" style="cursor:pointer;"
                             onclick="verCalificaciones(<?= $r['ID_EVALUADO'] ?>, '<?= addslashes(htmlspecialchars($r['EVALUADO'] ?? '')) ?>', 'COLAB_A_LIDER')">
                            <div class="rp-ev-avatar" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                                <?= mb_strtoupper(mb_substr($r['EVALUADO'] ?? 'E', 0, 1)) ?>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="rp-ev-name"><?= htmlspecialchars($r['EVALUADO'] ?? '—') ?></div>
                                <div class="rp-ev-meta"><?= htmlspecialchars($r['CARGO_EVALUADO'] ?? '') ?></div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="rp-date"><?= $r['FECHACONFIRMA'] ?? '—' ?></span>
                                <span style="font-size:.72rem;color:#d97706;font-weight:600;white-space:nowrap;"><?= icon('eye', 13) ?> Ver</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="rp-empty"><div class="rp-empty-icon"><?= icon('pen', 32) ?></div><div class="rp-empty-text">No has realizado evaluaciones de liderazgo.</div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════
         PANEL 5 — SEGUIMIENTO (rol admin)
    ══════════════════════════════════ -->

    <div id="rp-equipo" class="rp-panel <?= $activeTab === 'equipo' ? 'active' : '' ?>">

        <!-- Tarjetas resumen -->
        <div class="rp-grid-3 rp-animate" style="margin-bottom:20px;">
            <div class="rp-stat" style="background:linear-gradient(135deg,#6366f122,#6366f111);border:1.5px solid #6366f144;">
                <div class="rp-stat-label" style="color:#6366f1;">Total equipo</div>
                <div class="rp-stat-value" style="color:#6366f1;"><?= $resumenEquipo['total'] ?></div>
                <div class="rp-stat-sub">colaboradores a cargo</div>
            </div>
            <div class="rp-stat" style="background:linear-gradient(135deg,#10b98122,#10b98111);border:1.5px solid #10b98144;">
                <div class="rp-stat-label" style="color:#059669;">Completos</div>
                <div class="rp-stat-value" style="color:#059669;"><?= $resumenEquipo['completo'] ?></div>
                <div class="rp-stat-sub">evaluación finalizada</div>
            </div>
            <div class="rp-stat" style="background:linear-gradient(135deg,#ef444422,#ef444411);border:1.5px solid #ef444444;">
                <div class="rp-stat-label" style="color:#dc2626;">Pendientes</div>
                <div class="rp-stat-value" style="color:#dc2626;"><?= $resumenEquipo['sin_iniciar'] + $resumenEquipo['en_progreso'] ?></div>
                <div class="rp-stat-sub">sin iniciar o en progreso</div>
            </div>
        </div>

        <?php if (!empty($estadoEquipo)): ?>
        <div class="rp-card">
            <!-- Buscador -->
            <div style="margin-bottom:14px;">
                <input type="text" id="equipoBuscador"
                       placeholder="Buscar por nombre o cargo..."
                       oninput="equipoBuscar(this.value)"
                       style="width:100%;padding:9px 14px;border:1.5px solid var(--rep-border);
                              border-radius:10px;font-size:.88rem;font-family:inherit;
                              outline:none;transition:border-color .2s;">
            </div>
            <!-- Filtros -->
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;">
                <button class="equipo-filter"
                        style="padding:5px 14px;border-radius:8px;font-size:.78rem;
                               font-weight:600;cursor:pointer;border:none;
                               background:#f1f5f9;color:#475569;"
                        onclick="equipoFiltrar('todos',this)">Todos</button>
                <button class="equipo-filter"
                        style="padding:5px 14px;border-radius:8px;font-size:.78rem;
                               font-weight:600;cursor:pointer;border:none;
                               background:#fee2e2;color:#991b1b;"
                        onclick="equipoFiltrar('sin_iniciar',this)"><?= icon('x-circle', 13) ?> Sin iniciar</button>
                <button class="equipo-filter"
                        style="padding:5px 14px;border-radius:8px;font-size:.78rem;
                               font-weight:600;cursor:pointer;border:none;
                               background:#fef3c7;color:#92400e;"
                        onclick="equipoFiltrar('en_progreso',this)"><?= icon('clock', 13) ?> En progreso</button>
                <button class="equipo-filter"
                        style="padding:5px 14px;border-radius:8px;font-size:.78rem;
                               font-weight:600;cursor:pointer;border:none;
                               background:#d1fae5;color:#065f46;"
                        onclick="equipoFiltrar('completo',this)"><?= icon('check-circle', 13) ?> Completo</button>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <div id="equipoContador" style="font-size:.78rem;color:var(--rep-muted);"></div>
                <button onclick="exportarEquipoCSV(this)"
                        style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;
                               background:linear-gradient(135deg,#0058af,#2563eb);color:#fff;
                               border-radius:8px;font-size:.8rem;font-weight:600;
                               border:none;cursor:pointer;font-family:inherit;flex-shrink:0;">
                    <i class="ti ti-file-spreadsheet"></i> Exportar Excel
                </button>
            </div>
            <div style="overflow-x:auto;">
                <table class="rp-table" id="equipoTable">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th>Proceso</th>
                            <th style="text-align:center;">Autoevaluación</th>
                            <th style="text-align:center;">Fue evaluado</th>
                            <th>Estado</th>
                            <th style="text-align:center;">Compromisos</th>
                            <?php if ($puedeVerDetalle): ?>
                            <th style="text-align:center;">Prom. Auto</th>
                            <?php endif; ?>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estadoEquipo as $col):
                            switch ($col['ESTADO']) {
                                case 'completo':
                                    $badgeClass = 'background:#d1fae5;color:#065f46;';
                                    $badgeLabel = icon('check-circle', 13) . ' Completo';
                                    break;
                                case 'en_progreso':
                                    $badgeClass = 'background:#fef3c7;color:#92400e;';
                                    $badgeLabel = icon('clock', 13) . ' En progreso';
                                    break;
                                default:
                                    $badgeClass = 'background:#fee2e2;color:#991b1b;';
                                    $badgeLabel = icon('x-circle', 13) . ' Sin iniciar';
                                    break;
                            }
                        ?>
                        <tr class="equipo-row"
                            data-estado="<?= $col['ESTADO'] ?>"
                            data-nombre="<?= strtolower(htmlspecialchars($col['EMPLEADO'])) ?>"
                            data-cargo="<?= strtolower(htmlspecialchars($col['CARGO'])) ?>">
                            <td>
                                <div style="font-weight:600;"><?= htmlspecialchars($col['EMPLEADO']) ?></div>
                                <div style="font-size:.74rem;color:var(--rep-muted);"><?= htmlspecialchars($col['CARGO']) ?></div>
                            </td>
                            <td style="font-size:.82rem;color:var(--rep-muted);"><?= htmlspecialchars($col['DEPENDENCIA']) ?></td>
                            <td style="text-align:center;">
                                <?= $col['TIENE_AUTOEVAL'] ? icon('check-circle', 16) : icon('x-circle', 16) ?>
                            </td>
                            <td style="text-align:center;">
                                <?= $col['TIENE_EVAL_COLAB'] ? icon('check-circle', 16) : icon('x-circle', 16) ?>
                            </td>
                            <td>
                                <span style="display:inline-flex;align-items:center;gap:5px;
                                             padding:3px 10px;border-radius:20px;
                                             font-size:.72rem;font-weight:700;
                                             <?= $badgeClass ?>">
                                    <?= $badgeLabel ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <?php
                                // Siempre 3 compromisos por colaborador (regla SMART)
                                $planInfo  = $estadoPlanEquipo[$col['IDEMPLEADO']] ?? null;
                                $tieneEval = $col['TIENE_EVAL_COLAB'];
                                $totalComp = $planInfo ? (int)$planInfo['total']     : 0;
                                $aprobComp = $planInfo ? (int)$planInfo['aprobados'] : 0;

                                if (!$tieneEval):
                                ?>
                                    <span style="color:#94a3b8;font-size:.8rem;">—</span>
                                <?php elseif ($totalComp === 0): ?>
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;
                                                 border-radius:20px;font-size:.72rem;font-weight:700;
                                                 background:#fef3c7;color:#92400e;">
                                        <?= icon('clock', 12) ?> Pendiente asignar
                                    </span>
                                <?php elseif ($aprobComp === $totalComp && $totalComp >= 3): ?>
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;
                                                 border-radius:20px;font-size:.72rem;font-weight:700;
                                                 background:#d1fae5;color:#065f46;">
                                        <?= icon('check-circle', 12) ?> Completado
                                    </span>
                                <?php else: ?>
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;
                                                 border-radius:20px;font-size:.72rem;font-weight:700;
                                                 background:#dbeafe;color:#1e40af;">
                                        <?= icon('activity', 12) ?> <?= $aprobComp ?>/<?= $totalComp ?> · En curso
                                    </span>
                                <?php endif; ?>
                            </td>
                            <?php if ($puedeVerDetalle): ?>
                            <td style="text-align:center;">
                                <?php
                                // Promedio autoevaluación del colaborador
                                $promAuto = $agregados['promedios'][$col['IDEMPLEADO']] ?? null;
                                if ($promAuto !== null):
                                    $colProm = colorScore($promAuto);
                                ?>
                                <span style="font-family:'DM Mono',monospace;font-weight:700;color:<?= $colProm ?>;font-size:.85rem;">
                                    <?= number_format((float)$promAuto, 2) ?>
                                </span>
                                <?php else: ?>
                                <span style="color:#94a3b8;font-size:.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="rp-empty">
            <div class="rp-empty-icon"><?= icon('users', 32) ?></div>
            <div class="rp-empty-text">No se encontraron colaboradores a cargo en el período activo.</div>
        </div>
        <?php endif; ?>
    </div>
    <!-- ══════════════════════════════════
    <!-- PANEL — MI PLAN DE MEJORA -->
    <div id="rp-plan" class="rp-panel <?= $activeTab === 'plan' ? 'active' : '' ?>">
        <?php
        // Solo evaluar firma sobre acuerdos del Proceso 1 (P1-P11).
        $acuerdosProceso1 = array_filter(
            $acuerdosColaborador,
            fn($ac) => (int)($ac['NUM_COMPETENCIA'] ?? 0) >= 1
                    && (int)($ac['NUM_COMPETENCIA'] ?? 0) <= 11
        );
        $firmado = true;
        foreach ($acuerdosProceso1 as $ac) {
            if ((int)($ac['FIRMADO_COLAB'] ?? 0) === 0) { $firmado = false; break; }
        }
        $mostrarTabLider = ($esLiderFuncional ?? false) && !empty($acuerdosLider);
        ?>

        <?php if ($mostrarTabLider): ?>
        <!-- ── Barra de subpestañas ── -->
        <div style="display:flex;gap:4px;background:#f8fafc;padding:5px;
                    border-radius:12px;border:1px solid var(--rep-border);
                    margin-bottom:24px;width:fit-content;">
            <button id="plan-subtab-colab" onclick="planSubSwitch('colab')"
                    style="padding:7px 20px;border-radius:9px;font-size:.84rem;font-weight:600;
                           cursor:pointer;border:none;font-family:inherit;transition:all .2s;
                           background:linear-gradient(135deg,#0058af,#2563eb);color:#fff;
                           box-shadow:0 2px 8px rgba(0,88,175,.25);">
                <?= icon('user', 14) ?> Como Colaborador
            </button>
            <button id="plan-subtab-lider" onclick="planSubSwitch('lider')"
                    style="padding:7px 20px;border-radius:9px;font-size:.84rem;font-weight:600;
                           cursor:pointer;border:none;font-family:inherit;transition:all .2s;
                           background:transparent;color:#64748b;box-shadow:none;">
                <?= icon('target', 14) ?> Como Líder
            </button>
        </div>
        <?php endif; ?>

        <!-- ══ SUBPANEL: COMO COLABORADOR ══ -->
        <div id="plan-sub-colab">

        <?php if (!empty($acuerdosProceso1) && !$firmado): ?>
        <div style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:14px;
                    padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;gap:14px;">
            <div style="flex-shrink:0;"><i class="ti ti-lock" style="font-size:1.6rem;color:#d97706;"></i></div>
            <div>
                <div style="font-weight:700;color:#92400e;font-size:.95rem;margin-bottom:4px;">
                    Compromisos pendientes por recibir
                </div>
                <div style="color:#b45309;font-size:.84rem;line-height:1.5;">
                    Tu líder te ha asignado objetivos de mejora. Para visualizarlos debes
                    <strong>firmar el recibido del feedback</strong> en la reunión con tu líder.
                </div>
            </div>
        </div>
        <?php elseif (!empty($acuerdosColaborador)):
            $dictColabPlan = \app\models\reportModel::getDictColaborador();
            $calLabelsPlan = [
                1=>'Insuficiente',2=>'Necesita Mejorar',3=>'Aceptable',
                4=>'Acorde',5=>'Sobresaliente'
            ];
            $calTextMap = [
                'Insuficiente'=>1,'Necesita Mejorar'=>2,'Aceptable'=>3,'Acorde'=>4,'Sobresaliente'=>5
            ];
            $calColorsPlan = [
                1=>['bg'=>'#fee2e2','color'=>'#991b1b'],
                2=>['bg'=>'#fef3c7','color'=>'#92400e'],
                3=>['bg'=>'#e0f2fe','color'=>'#0369a1'],
                4=>['bg'=>'#dcfce7','color'=>'#15803d'],
                5=>['bg'=>'#f3e8ff','color'=>'#6b21a8'],
            ];
            $porComp = [];
            foreach ($acuerdosColaborador as $ac) { $porComp[$ac['NUM_COMPETENCIA']][] = $ac; }
        ?>
        <!-- Sub-encabezado sección colaborador -->
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;
                    padding-bottom:14px;border-bottom:2px solid var(--rep-border);">
            <div style="width:36px;height:36px;border-radius:10px;
                        background:linear-gradient(135deg,#0058af,#2563eb);
                        display:flex;align-items:center;justify-content:center;
                        flex-shrink:0;"><?= icon('user', 18, '#fff') ?></div>
            <div>
                <div style="font-size:1rem;font-weight:700;color:var(--rep-text);">Como Colaborador</div>
                <div style="font-size:.82rem;color:var(--rep-muted);margin-top:2px;">
                    Objetivos asignados por tu líder directo
                </div>
            </div>
        </div>
        <?php foreach ($porComp as $numComp => $acuerdos):
            $nombreComp = $dictColabPlan[$numComp] ?? "Competencia $numComp";
            $calRaw = $acuerdos[0]['CALIFICACION'] ?? '';
            $cal = is_numeric($calRaw) ? (int)$calRaw : ($calTextMap[$calRaw] ?? 0);
            $cc  = $calColorsPlan[$cal] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
        ?>
        <div class="rp-card" style="margin-bottom:18px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:8px;height:8px;border-radius:50%;background:var(--rep-accent);"></div>
                <div>
                    <div style="font-weight:700;"><?= htmlspecialchars($nombreComp) ?></div>
                    <span style="font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:20px;
                                 background:<?= $cc['bg'] ?>;color:<?= $cc['color'] ?>;">
                        <?= $calLabelsPlan[$cal] ?? '' ?>
                    </span>
                </div>
            </div>
            <?php foreach ($acuerdos as $ac): ?>
            <div style="border:1.5px solid var(--rep-border);border-radius:10px;padding:14px 16px;margin-bottom:12px;">

                <!-- Objetivo -->
                <div style="font-size:.85rem;font-weight:600;margin-bottom:8px;">
                    <?= icon('target', 14) ?> <?= htmlspecialchars($ac['OBJETIVO']) ?>
                </div>

                <!-- Meta, asignación, estado -->
                <div style="font-size:.75rem;color:var(--rep-muted);margin-bottom:10px;">
                    Asignado por: <strong><?= htmlspecialchars($ac['NOMBRE_LIDER']) ?></strong>
                    &nbsp;·&nbsp; <?= date('d/m/Y', strtotime($ac['FECHA_ASIGNACION'])) ?>
                    &nbsp;·&nbsp;
                    <?php
                    if ($ac['ESTADO'] === 'APROBADO'):
                        echo '<span style="font-weight:700;color:#005EB8;">' . icon('check-circle', 14) . ' Aprobado por tu líder</span>';
                    elseif ($ac['ESTADO'] === 'RESPONDIDO'):
                        echo '<span style="font-weight:700;color:#059669;">' . icon('check-circle', 14) . ' Respondido</span>';
                    else:
                        echo '<span style="font-weight:700;color:#d97706;">' . icon('clock', 14) . ' Pendiente</span>';
                    endif;
                    ?>
                </div>

                <!-- Cita de reunión con el líder -->
                <?php if (!empty($ac['FECHA_FEEDBACK'])): ?>
                <?php
                    $fbPartesPlan = explode(' ', $ac['FECHA_FEEDBACK']);
                    $fbFechaPlan  = $fbPartesPlan[0] ?? '';
                    $fbHoraPlan   = $fbPartesPlan[1] ?? '';
                ?>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;
                            padding:10px 14px;margin-bottom:12px;font-size:.82rem;color:#1e40af;
                            display:flex;align-items:center;gap:8px;">
                    <?= icon('calendar', 14) ?> <strong>Reunión agendada:</strong>
                    &nbsp;<?= $fbFechaPlan ?>
                    <?php if ($fbHoraPlan): ?>
                    &nbsp;<?= icon('clock', 14) ?> <?= $fbHoraPlan ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Campos SMART -->
                <?php
                $smartItems = [
                    [icon('ruler',      13), 'Indicador',             $ac['INDICADOR']          ?? ''],
                    [icon('target',     13), 'Meta',                  $ac['META']               ?? ''],
                    [icon('calendar',   13), 'Plazo',                 $ac['PLAZO']              ?? ''],
                    [icon('search',     13), 'Evidencia',             $ac['EVIDENCIA']          ?? ''],
                    [icon('handshake',  13), 'Apoyo del líder',       $ac['APOYO_LIDER']        ?? ''],
                    [icon('list',       13), 'Seguimiento sugerido',  $ac['SEGUIMIENTO']        ?? ''],
                    [icon('check',      13), 'Compromiso acordado',   $ac['COMPROMISO_AJUSTADO']?? ''],
                ];
                $haySmartData = array_filter(array_column($smartItems, 2));
                ?>
                <?php if ($haySmartData): ?>
                <div style="background:#f8fafc;border:1px solid var(--rep-border);border-radius:8px;
                            padding:10px 14px;margin-bottom:12px;">
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;
                                color:var(--rep-muted);margin-bottom:8px;">Detalle del objetivo SMART</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                    <?php foreach ($smartItems as [$ico, $label, $val]): ?>
                        <?php if (!empty(trim($val ?? ''))): ?>
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:7px 10px;">
                            <div style="font-size:.68rem;font-weight:700;color:var(--rep-muted);margin-bottom:2px;">
                                <?= $ico ?> <?= $label ?>
                            </div>
                            <div style="font-size:.8rem;color:var(--rep-text);">
                                <?= htmlspecialchars($val) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Plan de acción -->
                <?php if ($ac['ESTADO'] === 'APROBADO'): ?>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;font-size:.84rem;color:#166534;margin-bottom:6px;">
                    <strong>Tu plan de acción:</strong><br>
                    <?= nl2br(htmlspecialchars($ac['PLAN_ACCION'])) ?>
                </div>
                <?php if (!empty($ac['COMENTARIO_LIDER'])): ?>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:8px 12px;font-size:.82rem;color:#1e40af;">
                    <?= icon('message-circle', 14) ?> <strong>Comentario del líder:</strong> <?= htmlspecialchars($ac['COMENTARIO_LIDER']) ?>
                </div>
                <?php endif; ?>
                <?php elseif ($ac['ESTADO'] === 'RESPONDIDO'): ?>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;font-size:.84rem;color:#166534;margin-bottom:6px;">
                    <strong>Tu plan de acción:</strong><br>
                    <?= nl2br(htmlspecialchars($ac['PLAN_ACCION'])) ?>
                </div>
                <?php if (!empty($ac['COMENTARIO_LIDER'])): ?>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:8px 12px;font-size:.82rem;color:#1e40af;">
                    <?= icon('message-circle', 14) ?> <strong>Comentario del líder:</strong> <?= htmlspecialchars($ac['COMENTARIO_LIDER']) ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div id="plan-form-<?= $ac['IDACUERDO'] ?>">
                    <textarea id="plan-text-<?= $ac['IDACUERDO'] ?>" rows="3" maxlength="1000"
                              placeholder="Describe cómo vas a mejorar en esta competencia..."
                              style="width:100%;padding:9px 12px;border:1.5px solid var(--rep-border);
                                     border-radius:8px;font-size:.84rem;font-family:inherit;
                                     resize:vertical;outline:none;"></textarea>
                    <button type="button"
                            onclick="guardarPlanAjax(<?= $ac['IDACUERDO'] ?>, this)"
                            style="margin-top:8px;padding:7px 18px;background:var(--rep-accent);
                                   color:#fff;border:none;border-radius:8px;font-weight:600;
                                   font-size:.84rem;cursor:pointer;font-family:inherit;">
                        <?= icon('save', 14) ?> Guardar plan de acción
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="rp-empty">
            <div class="rp-empty-icon"><?= icon('clipboard', 32) ?></div>
            <div class="rp-empty-text" style="font-weight:600;color:#1e293b;margin-bottom:8px;">
                Sin plan de mejora asignado
            </div>
            <div style="font-size:.85rem;color:var(--rep-muted);max-width:400px;margin:0 auto;">
                Tu líder aún no ha asignado objetivos de mejora para este período. 
                Si tienes dudas, consulta con tu líder o con Gestión Humana.
            </div>
        </div>
        <?php endif; ?>

        </div><!-- /plan-sub-colab -->

        <?php if ($mostrarTabLider): ?>
        <!-- ══ SUBPANEL: COMO LÍDER ══ -->
        <div id="plan-sub-lider" style="display:none;">

        <!-- Sub-encabezado sección líder -->
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;
                    padding-bottom:14px;border-bottom:2px solid var(--rep-border);">
            <div style="width:36px;height:36px;border-radius:10px;
                        background:linear-gradient(135deg,#0058af,#2563eb);
                        display:flex;align-items:center;justify-content:center;
                        flex-shrink:0;"><?= icon('target', 18, '#fff') ?></div>
            <div>
                <div style="font-size:1rem;font-weight:700;color:var(--rep-text);">Como Líder</div>
                <div style="font-size:.82rem;color:var(--rep-muted);margin-top:2px;">
                    Objetivos asignados por tu director · Competencias de liderazgo
                </div>
            </div>
        </div>

            <?php
            // Verificar si el líder ya firmó el recibido del feedback
            $firmadoL2 = (int)(($acuerdosLider[0]['FIRMADO_COLAB'] ?? 0));
            if (!$firmadoL2):
            ?>
            <div style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:14px;
                        padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;gap:14px;">
                <div style="flex-shrink:0;"><i class="ti ti-lock" style="font-size:1.6rem;color:#d97706;"></i></div>
                <div>
                    <div style="font-weight:700;color:#92400e;font-size:.95rem;margin-bottom:4px;">
                        Compromisos de liderazgo pendientes por recibir
                    </div>
                    <div style="color:#b45309;font-size:.84rem;line-height:1.5;">
                        Tu director te ha asignado objetivos de liderazgo. Para visualizarlos debes
                        <strong>firmar el recibido del feedback</strong> en la reunión con tu director.
                    </div>
                </div>
            </div>
            <?php else:
                $dictLiderPlan = app\models\ReportModel::getDictLiderazgo();
                $calLabelsPlanL2 = [
                    1=>'Insuficiente', 2=>'Necesita Mejorar', 3=>'Aceptable',
                    4=>'Acorde', 5=>'Sobresaliente'
                ];
                $calColorsPlanL2 = [
                    1=>['bg'=>'#fee2e2','color'=>'#991b1b'],
                    2=>['bg'=>'#fef3c7','color'=>'#92400e'],
                    3=>['bg'=>'#e0f2fe','color'=>'#0369a1'],
                    4=>['bg'=>'#dcfce7','color'=>'#15803d'],
                    5=>['bg'=>'#f3e8ff','color'=>'#6b21a8'],
                ];
                $porCompL2 = [];
                foreach ($acuerdosLider as $acL) {
                    $porCompL2[$acL['NUM_COMPETENCIA']][] = $acL;
                }
            ?>
            <?php foreach ($porCompL2 as $numCompL2 => $acuerdosL2):
                $nombreCompL2 = $dictLiderPlan[$numCompL2] ?? "Competencia $numCompL2";
                $calRawL2     = $acuerdosL2[0]['CALIFICACION'] ?? 0;
                $calL2        = is_numeric($calRawL2) ? (int)$calRawL2 : 0;
                $ccL2         = $calColorsPlanL2[$calL2] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
            ?>
            <div class="rp-card" style="margin-bottom:18px;border-left:3px solid #0058af;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:#0058af;"></div>
                    <div>
                        <div style="font-weight:700;"><?= htmlspecialchars($nombreCompL2) ?></div>
                        <span style="font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:20px;
                                     background:<?= $ccL2['bg'] ?>;color:<?= $ccL2['color'] ?>;">
                            <?= $calLabelsPlanL2[$calL2] ?? '' ?>
                        </span>
                    </div>
                </div>

                <?php foreach ($acuerdosL2 as $acL): ?>
                <div style="border:1.5px solid var(--rep-border);border-radius:10px;padding:14px 16px;margin-bottom:12px;">

                    <!-- Objetivo -->
                    <div style="font-size:.85rem;font-weight:600;margin-bottom:8px;">
                        <?= icon('target', 14) ?> <?= htmlspecialchars($acL['OBJETIVO']) ?>
                    </div>

                    <!-- Asignado por / estado -->
                    <div style="font-size:.75rem;color:var(--rep-muted);margin-bottom:10px;">
                        Asignado por: <strong><?= htmlspecialchars($acL['NOMBRE_LIDER']) ?></strong>
                        &nbsp;·&nbsp;
                        <?php
                        if ($acL['ESTADO'] === 'APROBADO'):
                            echo '<span style="font-weight:700;color:#005EB8;">' . icon('check-circle', 14) . ' Aprobado por tu director</span>';
                        elseif ($acL['ESTADO'] === 'RESPONDIDO'):
                            echo '<span style="font-weight:700;color:#059669;">' . icon('check-circle', 14) . ' Respondido</span>';
                        else:
                            echo '<span style="font-weight:700;color:#d97706;">' . icon('clock', 14) . ' Pendiente</span>';
                        endif;
                        ?>
                    </div>

                    <!-- Campos SMART -->
                    <?php
                    $smartL2 = [
                        [icon('ruler',     13), 'Indicador',            $acL['INDICADOR']           ?? ''],
                        [icon('target',    13), 'Meta',                 $acL['META']                ?? ''],
                        [icon('calendar',  13), 'Plazo',                $acL['PLAZO']               ?? ''],
                        [icon('search',    13), 'Evidencia',            $acL['EVIDENCIA']           ?? ''],
                        [icon('handshake', 13), 'Apoyo del director',   $acL['APOYO_LIDER']         ?? ''],
                        [icon('list',      13), 'Seguimiento sugerido', $acL['SEGUIMIENTO']         ?? ''],
                        [icon('check',     13), 'Compromiso acordado',  $acL['COMPROMISO_AJUSTADO'] ?? ''],
                    ];
                    $haySmartL2 = array_filter(array_column($smartL2, 2), fn($v) => !empty(trim($v ?? '')));
                    ?>
                    <?php if (!empty($haySmartL2)): ?>
                    <div style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:8px;
                                padding:10px 14px;margin-bottom:12px;">
                        <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;
                                    color:#0058af;margin-bottom:8px;">Detalle del objetivo</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                        <?php foreach ($smartL2 as [$ico, $label, $val]): ?>
                            <?php if (!empty(trim($val ?? ''))): ?>
                            <div style="background:#fff;border:1px solid #bfdbfe;border-radius:6px;padding:7px 10px;">
                                <div style="font-size:.68rem;font-weight:700;color:#0058af;margin-bottom:2px;">
                                    <?= $ico ?> <?= $label ?>
                                </div>
                                <div style="font-size:.8rem;color:var(--rep-text);">
                                    <?= htmlspecialchars($val) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Plan de acción -->
                    <?php if ($acL['ESTADO'] === 'APROBADO'): ?>
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;
                                padding:10px 12px;font-size:.84rem;color:#166534;margin-bottom:6px;">
                        <strong>Tu plan de acción:</strong><br>
                        <?= nl2br(htmlspecialchars($acL['PLAN_ACCION'])) ?>
                    </div>
                    <?php if (!empty($acL['COMENTARIO_LIDER'])): ?>
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;
                                padding:8px 12px;font-size:.82rem;color:#1e40af;">
                        <?= icon('message-circle', 14) ?>
                        <strong>Comentario del director:</strong>
                        <?= htmlspecialchars($acL['COMENTARIO_LIDER']) ?>
                    </div>
                    <?php endif; ?>
                    <?php elseif ($acL['ESTADO'] === 'RESPONDIDO'): ?>
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;
                                padding:10px 12px;font-size:.84rem;color:#166534;margin-bottom:6px;">
                        <strong>Tu plan de acción:</strong><br>
                        <?= nl2br(htmlspecialchars($acL['PLAN_ACCION'])) ?>
                    </div>
                    <?php if (!empty($acL['COMENTARIO_LIDER'])): ?>
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;
                                padding:8px 12px;font-size:.82rem;color:#1e40af;">
                        <?= icon('message-circle', 14) ?>
                        <strong>Comentario del director:</strong>
                        <?= htmlspecialchars($acL['COMENTARIO_LIDER']) ?>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div id="plan-form-l2-<?= $acL['IDACUERDO'] ?>">
                        <textarea id="plan-text-l2-<?= $acL['IDACUERDO'] ?>" rows="3" maxlength="1000"
                                  placeholder="Describe cómo vas a trabajar en esta competencia de liderazgo..."
                                  style="width:100%;padding:9px 12px;border:1.5px solid var(--rep-border);
                                         border-radius:8px;font-size:.84rem;font-family:inherit;
                                         resize:vertical;outline:none;"></textarea>
                        <button type="button"
                                onclick="guardarPlanL2Ajax(<?= $acL['IDACUERDO'] ?>, this)"
                                style="margin-top:8px;padding:7px 18px;background:linear-gradient(135deg,#0058af,#2563eb);
                                       color:#fff;border:none;border-radius:8px;font-weight:600;
                                       font-size:.84rem;cursor:pointer;font-family:inherit;">
                            <?= icon('save', 14) ?> Guardar plan de acción
                        </button>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

        </div><!-- /plan-sub-lider -->
        <?php endif; ?>

    </div><!-- /rp-plan -->

</div><!-- /rp-wrap -->
</div><!-- /rp-root -->

<!-- ══════════════════════════════════════════════
     SCRIPTS — Chart.js + lógica de tabs
══════════════════════════════════════════════ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<!-- Modal calificaciones realizadas -->
<div id="modalCalif" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
     z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:24px;width:min(520px,95vw);
                max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3);position:relative;">
        <button onclick="cerrarModalCalif()"
                style="position:absolute;top:12px;right:14px;background:none;border:none;
                       font-size:1.3rem;cursor:pointer;color:#94a3b8;">✕</button>
        <div id="modalCalifTitle" style="font-weight:700;font-size:.95rem;color:#1e3a5f;margin-bottom:4px;"></div>
        <div id="modalCalifSub"   style="font-size:.78rem;color:#64748b;margin-bottom:16px;"></div>
        <div id="modalCalifBody">
            <div style="text-align:center;padding:20px;color:#94a3b8;">Cargando...</div>
        </div>
    </div>
</div>


<script>
(function () {
    // ── Datos desde PHP ──────────────────────────────────────────────────────
    const appUrl = '<?= APP_URL ?>';
    // ── Modal calificaciones realizadas ──────────────────────────────────────
    const calLabels = { 1:'Insuficiente', 2:'Requiere mejora', 3:'Aceptable', 4:'Acorde', 5:'Sobresaliente' };

    function getPromLabel(prom) {
        if (prom < 2) return 'Insuficiente';
        if (prom < 3) return 'Requiere mejora';
        if (prom < 4) return 'Aceptable';
        if (prom < 5) return 'Acorde';
        return 'Sobresaliente';
    }

    window.verCalificaciones = function(idEvaluado, nombre, tipoEval) {
        document.getElementById('modalCalifTitle').textContent = nombre;
        document.getElementById('modalCalifSub').textContent   =
            tipoEval === 'LIDER_A_COLAB' ? 'Evaluación de desempeño' : 'Evaluación de liderazgo';
        document.getElementById('modalCalifBody').innerHTML =
            '<div style="text-align:center;padding:20px;color:#94a3b8;">Cargando...</div>';
        document.getElementById('modalCalif').style.display = 'flex';

        fetch(appUrl + 'reportes/?action=getCalificacionesRealizadas&idEvaluado=' + idEvaluado + '&tipoEval=' + tipoEval, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                document.getElementById('modalCalifBody').innerHTML =
                    '<div style="text-align:center;color:#94a3b8;padding:16px;">Sin datos disponibles.</div>';
                return;
            }
            let total = 0;
            let html  = '<table style="width:100%;border-collapse:collapse;font-size:.82rem;">';
            data.forEach((c, i) => {
                const val = parseInt(c.VALOR) || 0;
                const lbl = calLabels[val] || val;
                total    += val;
                const bg  = i % 2 === 0 ? '#f8fafc' : '#fff';
                html += `<tr style="background:${bg};border-bottom:1px solid #f1f5f9;">
                    <td style="padding:8px 10px;color:#475569;">${c.NOMBRE_COMP}</td>
                    <td style="padding:8px 10px;text-align:center;width:120px;color:#1e3a5f;font-size:.78rem;">${lbl}</td>
                    <td style="padding:8px 10px;text-align:center;width:32px;font-weight:700;color:#1e3a5f;">${val}</td>
                </tr>`;
            });
            html += '</table>';
            const prom    = (total / data.length).toFixed(2);
            const promLbl = getPromLabel(parseFloat(prom));
            html += `<div style="margin-top:14px;padding:10px 14px;background:#f8fafc;
                                 border-radius:10px;border:1.5px solid #e2e8f0;
                                 display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:.82rem;font-weight:700;color:#1e3a5f;">Promedio obtenido</span>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:1.1rem;font-weight:800;color:#1e3a5f;font-family:'DM Mono',monospace;">${prom}</span>
                    <span style="font-size:.78rem;color:#64748b;">${promLbl}</span>
                </div>
            </div>`;
            document.getElementById('modalCalifBody').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('modalCalifBody').innerHTML =
                '<div style="text-align:center;color:#ef4444;padding:16px;">Error al cargar datos.</div>';
        });
    };

    window.cerrarModalCalif = function() {
        document.getElementById('modalCalif').style.display = 'none';
    };


    const autoLabels  = <?= json_encode(array_values($autoLabels)) ?>;
    const autoValues  = <?= json_encode(array_values($autoValues)) ?>;
    const indLabels   = <?= json_encode(array_values($indLabels)) ?>;
    const indAutoVals = <?= json_encode(array_values($indAutoVals)) ?>;
    const indLiderVals= <?= json_encode(array_values($indLiderVals)) ?>;
    const radarLabels = <?= json_encode(array_values($radarLabels)) ?>;
    const radarDesemp = <?= json_encode(array_values($radarDesempeno)) ?>;

    // ── Paleta ───────────────────────────────────────────────────────────────
    const ACCENT  = '#4f46e5';
    const ACCENT2 = '#0058af';
    const BLUE    = '#3b82f6';
    const GREEN   = '#10b981';
    const AMBER   = '#f59e0b';

    // ── Defaults globales ────────────────────────────────────────────────────
    Chart.defaults.font.family = "'DM Sans', sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#64748b';
    Chart.defaults.plugins.legend.display = false;

    // ── Función: color por valor ─────────────────────────────────────────────
    function scoreColor(v, alpha) {
        if (v >= 4.5) return `rgba(16,185,129,${alpha||1})`;
        if (v >= 3.5) return `rgba(59,130,246,${alpha||1})`;
        if (v >= 2.5) return `rgba(245,158,11,${alpha||1})`;
        if (v >  0)   return `rgba(239,68,68,${alpha||1})`;
        return `rgba(203,213,225,${alpha||1})`;
    }

    // ── AUTOEVALUACIÓN: Radar ────────────────────────────────────────────────
    const canvasAutoRadar = document.getElementById('chartAutoRadar');
    if (canvasAutoRadar && autoValues.some(v => v > 0)) {
        new Chart(canvasAutoRadar, {
            type: 'radar',
            data: {
                labels: autoLabels,
                datasets: [{
                    data: autoValues,
                    backgroundColor: `rgba(79,70,229,.15)`,
                    borderColor: ACCENT,
                    borderWidth: 2,
                    pointBackgroundColor: ACCENT,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    r: {
                        min: 0, max: 5,
                        ticks: { stepSize: 1, font: { size: 10 } },
                        pointLabels: { font: { size: 10 }, color: '#475569' },
                        grid: { color: '#e2e8f0' },
                    }
                }
            }
        });
    }

    // ── AUTOEVALUACIÓN: Barras horizontales ───────────────────────────────────
    const canvasAutoBars = document.getElementById('chartAutoBars');
    if (canvasAutoBars && autoValues.some(v => v > 0)) {
        new Chart(canvasAutoBars, {
            type: 'bar',
            data: {
                labels: autoLabels,
                datasets: [{
                    data: autoValues,
                    backgroundColor: autoValues.map(v => scoreColor(v, .75)),
                    borderColor:     autoValues.map(v => scoreColor(v)),
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { min: 0, max: 5, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                    y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    // ── INDICADORES: Barras agrupadas Auto vs Líder ───────────────────────────
    const canvasIndBars = document.getElementById('chartIndBars');
    if (canvasIndBars && (indAutoVals.some(v=>v>0) || indLiderVals.some(v=>v>0))) {
        new Chart(canvasIndBars, {
            type: 'bar',
            data: {
                labels: indLabels,
                datasets: [
                    {
                        label: 'Autoevaluación',
                        data: indAutoVals,
                        backgroundColor: `rgba(59,130,246,.7)`,
                        borderColor: BLUE,
                        borderWidth: 1.5,
                        borderRadius: 4,
                    },
                    {
                        label: 'Líder',
                        data: indLiderVals,
                        backgroundColor: `rgba(124,58,237,.7)`,
                        borderColor: ACCENT2,
                        borderWidth: 1.5,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                    y: { min: 0, max: 5, stepSize: 1, grid: { color: '#f1f5f9' } }
                }
            }
        });
    }

    // ── INDICADORES: Radar Auto vs Líder ─────────────────────────────────────
    const canvasIndRadar = document.getElementById('chartIndRadar');
    if (canvasIndRadar && (indAutoVals.some(v=>v>0) || indLiderVals.some(v=>v>0))) {
        new Chart(canvasIndRadar, {
            type: 'radar',
            data: {
                labels: indLabels,
                datasets: [
                    {
                        label: 'Autoevaluación',
                        data: indAutoVals,
                        backgroundColor: 'rgba(59,130,246,.12)',
                        borderColor: BLUE,
                        borderWidth: 2,
                        pointBackgroundColor: BLUE,
                        pointRadius: 3,
                    },
                    {
                        label: 'Líder',
                        data: indLiderVals,
                        backgroundColor: 'rgba(124,58,237,.12)',
                        borderColor: ACCENT2,
                        borderWidth: 2,
                        pointBackgroundColor: ACCENT2,
                        pointRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                    }
                },
                scales: {
                    r: {
                        min: 0, max: 5,
                        ticks: { stepSize: 1, font: { size: 9 } },
                        pointLabels: { font: { size: 9 }, color: '#64748b' },
                        grid: { color: '#e2e8f0' },
                    }
                }
            }
        });
    }

    // ── Cambio de pestañas ───────────────────────────────────────────────────
    window.rpSwitch = function(id, btn) {
        document.querySelectorAll('.rp-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.rp-tab').forEach(b => b.classList.remove('active'));
        const panel = document.getElementById('rp-' + id);
        if (panel) panel.classList.add('active');
        btn.classList.add('active');
    };

    // Restaurar tab guardado en sessionStorage tras recarga
    (function() {
        const savedTab = sessionStorage.getItem('rp_active_tab');
        if (savedTab) {
            sessionStorage.removeItem('rp_active_tab');
            const btn = document.querySelector('.rp-tab[onclick*="' + savedTab + '"]');
            if (btn) btn.click();
        }
    })();

    // Restaurar tab guardado en sessionStorage tras recarga
    (function() {
        const savedTab = sessionStorage.getItem('rp_active_tab');
        if (savedTab) {
            sessionStorage.removeItem('rp_active_tab');
            const btn = document.querySelector('.rp-tab[onclick*="' + savedTab + '"]');
            if (btn) btn.click();
        }
    })();

    // ── Compatibilidad con switchTab del layout global ─────────────────────
    window.switchTab = function(tabName, element) {
        rpSwitch(tabName, element);
    };

    // ── Animar barras de progreso al cargar ─────────────────────────────────
    document.querySelectorAll('.rp-bar-fill').forEach(el => {
        const target = el.style.width;
        el.style.width = '0%';
        setTimeout(() => { el.style.width = target; }, 200);
    });

    // ── Mi Equipo: buscador y filtros ─────────────────────────────────────────
    let equipoEstadoActivo = 'todos';

    window.equipoFiltrar = function(estado, btn) {
        equipoEstadoActivo = estado;
        document.querySelectorAll('.equipo-filter').forEach(b => {
            b.style.opacity = '.6';
            b.style.fontWeight = '600';
        });
        btn.style.opacity = '1';
        btn.style.fontWeight = '700';
        equipoAplicarFiltros();
    };

    window.equipoBuscar = function(term) {
        equipoAplicarFiltros(term);
    };

    function equipoAplicarFiltros(term) {
        term = (term ?? document.getElementById('equipoBuscador')?.value ?? '').toLowerCase().trim();
        const rows = document.querySelectorAll('.equipo-row');
        let visible = 0;
        rows.forEach(row => {
            const nombre = row.dataset.nombre || '';
            const cargo  = row.dataset.cargo  || '';
            const estado = row.dataset.estado || '';
            const matchEstado = equipoEstadoActivo === 'todos' || estado === equipoEstadoActivo;
            const matchTerm   = !term || nombre.includes(term) || cargo.includes(term);
            const show = matchEstado && matchTerm;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const contador = document.getElementById('equipoContador');
        if (contador) {
            contador.textContent = visible === rows.length
                ? `Mostrando ${rows.length} colaboradores`
                : `Mostrando ${visible} de ${rows.length} colaboradores`;
        }
    }

    setTimeout(() => {
        equipoAplicarFiltros();
        const btnTodos = document.querySelector('.equipo-filter');
        if (btnTodos) { btnTodos.style.opacity = '1'; btnTodos.style.fontWeight = '700'; }
    }, 200);

    // ── Modal asignación de acuerdos ──────────────────────────────────────────
    
        // ── Guardar plan de acción del colaborador via AJAX ───────────────────────
    // ── Helper: reemplaza el formulario con el plan guardado ─────────────────
    function _reemplazarFormPlan(prefijo, idAcuerdo, texto) {
        const formDiv = document.getElementById(prefijo + idAcuerdo);
        if (formDiv) {
            formDiv.outerHTML =
                '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;' +
                'padding:10px 12px;font-size:.84rem;color:#166534;">' +
                '<strong>Tu plan de acción:</strong><br>' +
                texto.replace(/\n/g, '<br>') + '</div>';
        }
    }

    // ── Helper: fetch + SweetAlert compartido ─────────────────────────────────
    function _fetchGuardarPlan(idAcuerdo, texto, btn, textoOrigBtn, prefijoForm) {
        const formData = new FormData();
        formData.append('action',      'guardarPlanAccion');
        formData.append('idAcuerdo',   idAcuerdo);
        formData.append('plan_accion', texto);

        fetch(appUrl + 'reportes/', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled    = false;
            btn.textContent = textoOrigBtn;
            if (data.ok) {
                _reemplazarFormPlan(prefijoForm, idAcuerdo, texto);
                Swal.fire({
                    icon: 'success',
                    title: '¡Plan guardado!',
                    text: 'Tu plan de acción fue registrado correctamente.',
                    confirmButtonColor: '#0058af',
                    confirmButtonText: 'Entendido',
                    timer: 3000,
                    timerProgressBar: true,
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: 'No fue posible guardar tu plan. Intenta de nuevo.',
                    confirmButtonColor: '#0058af',
                    confirmButtonText: 'Cerrar',
                });
            }
        })
        .catch(err => {
            btn.disabled    = false;
            btn.textContent = textoOrigBtn;
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: err.message,
                confirmButtonColor: '#0058af',
                confirmButtonText: 'Cerrar',
            });
        });
    }

    // ── Guardar plan colaborador (P1-P11) ─────────────────────────────────────
    window.guardarPlanAjax = function(idAcuerdo, btn) {
        const textarea = document.getElementById('plan-text-' + idAcuerdo);
        if (!textarea || !textarea.value.trim()) {
            Swal.fire({ icon: 'warning', title: 'Campo vacío',
                text: 'Por favor escribe tu plan de acción antes de guardar.',
                confirmButtonColor: '#0058af', confirmButtonText: 'Entendido' });
            return;
        }
        const textoOrigBtn = btn.textContent;
        btn.disabled    = true;
        btn.textContent = 'Guardando...';
        _fetchGuardarPlan(idAcuerdo, textarea.value.trim(), btn, textoOrigBtn, 'plan-form-');
    };

    // ── Guardar plan líder (P12-P16) ──────────────────────────────────────────
    window.guardarPlanL2Ajax = function(idAcuerdo, btn) {
        const textarea = document.getElementById('plan-text-l2-' + idAcuerdo);
        if (!textarea || !textarea.value.trim()) {
            Swal.fire({ icon: 'warning', title: 'Campo vacío',
                text: 'Por favor escribe tu plan de acción antes de guardar.',
                confirmButtonColor: '#0058af', confirmButtonText: 'Entendido' });
            return;
        }
        const textoOrigBtn = btn.textContent;
        btn.disabled    = true;
        btn.textContent = 'Guardando...';
        _fetchGuardarPlan(idAcuerdo, textarea.value.trim(), btn, textoOrigBtn, 'plan-form-l2-');
    };

    // ── Subpestañas Mi Plan de Mejora ────────────────────────────────────────
    // Activar subpestaña por parámetro URL (?sub=lider o ?sub=colab)
    (function() {
        const urlSub = new URLSearchParams(window.location.search).get('sub');
        if (urlSub === 'lider' || urlSub === 'colab') {
            // Esperar a que el DOM esté listo y el tab plan esté activo
            setTimeout(function() {
                if (typeof planSubSwitch === 'function') planSubSwitch(urlSub);
            }, 100);
        }
    })();

    window.planSubSwitch = function(tab) {
        const isColab = tab === 'colab';
        const pColab  = document.getElementById('plan-sub-colab');
        const pLider  = document.getElementById('plan-sub-lider');
        if (pColab) pColab.style.display = isColab ? '' : 'none';
        if (pLider) pLider.style.display = isColab ? 'none' : '';
        const btns = {
            colab: document.getElementById('plan-subtab-colab'),
            lider: document.getElementById('plan-subtab-lider')
        };
        Object.entries(btns).forEach(([key, btn]) => {
            if (!btn) return;
            const active = key === tab;
            btn.style.background = active ? 'linear-gradient(135deg,#0058af,#2563eb)' : 'transparent';
            btn.style.color      = active ? '#fff' : '#64748b';
            btn.style.boxShadow  = active ? '0 2px 8px rgba(0,88,175,.25)' : 'none';
        });
    };

})();

// ── Exportar Mi Equipo (líder) ─────────────────────────────────────────────
const EQUIPO_EXPORT_URL = '<?= rtrim(str_replace("GestionHumana/", "GestionHumana/exportar.php", APP_URL), "/") ?>';

function exportarEquipoCSV(btn) {
    const origHtml = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .8s linear infinite;display:inline-block;vertical-align:middle;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Generando...';

    fetch(EQUIPO_EXPORT_URL + '?tipo=equipo_lider')
        .then(res => {
            if (!res.ok) throw new Error('Error del servidor: ' + res.status);
            return res.blob();
        })
        .then(blob => {
            const url  = URL.createObjectURL(blob);
            const link = document.createElement('a');
            const fecha = new Date().toISOString().slice(0,10).replace(/-/g,'');
            link.href     = url;
            link.download = 'equipo_lider_' + fecha + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            btn.disabled  = false;
            btn.innerHTML = origHtml;

            Swal.fire({
                icon:             'success',
                title:            '¡Exportación exitosa!',
                html:             '<b>Avance del equipo</b><br><span style="font-size:.88rem;color:#64748b;">El archivo CSV fue descargado correctamente.<br>Ábrelo con Excel o Google Sheets.</span>',
                confirmButtonColor: '#0058af',
                confirmButtonText:  'Entendido',
                timer:            4000,
                timerProgressBar: true,
            });
        })
        .catch(err => {
            btn.disabled  = false;
            btn.innerHTML = origHtml;
            Swal.fire({
                icon:             'error',
                title:            'Error al exportar',
                text:             err.message || 'No fue posible generar el archivo.',
                confirmButtonColor: '#0058af',
                confirmButtonText:  'Cerrar',
            });
        });
}
</script>

<!-- ══════════════════════════════════════════════════════════
     TOUR DE AYUDA — REPORTES
══════════════════════════════════════════════════════════ -->
<style>
#rp-tour-btn {
    position: fixed;
    bottom: 24px; right: 24px;
    width: 44px; height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0058af, #0074e0);
    color: #fff;
    font-size: 20px; font-weight: 900;
    border: none; cursor: pointer;
    box-shadow: 0 4px 16px rgba(0,88,175,0.4);
    display: flex; align-items: center; justify-content: center;
    z-index: 8990;
    transition: transform 0.2s, box-shadow 0.2s;
    line-height: 1;
}
#rp-tour-btn:hover {
    transform: scale(1.12);
    box-shadow: 0 6px 22px rgba(0,88,175,0.55);
}
</style>

<button id="rp-tour-btn" title="Tour de ayuda" onclick="iniciarTourReportes()">?</button>

<script>
(function () {
    var activeTab   = '<?= htmlspecialchars($activeTab ?? 'autoeval', ENT_QUOTES) ?>';
    var tieneAuto   = <?= !empty($autoeval)       ? 'true' : 'false' ?>;
    var tieneRecib  = <?= (!empty($recibidasColab) || !empty($recibidasLider)) ? 'true' : 'false' ?>;
    var tieneRealiz = <?= (!empty($realizadasColab) || !empty($realizadasLider)) ? 'true' : 'false' ?>;
    var tieneAcuerd = <?= !empty($acuerdosColaborador) ? 'true' : 'false' ?>;
    var esLider     = <?= ($esLiderFuncionalSb ?? false) ? 'true' : 'false' ?>;

    // ── Loader CDN (compartido con el resto de módulos) ──────────────────────
    function _rpLoadDriver(cb) {
        if (window._fbDriverReady) { cb(); return; }
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css';
        document.head.appendChild(link);

        var style = document.createElement('style');
        style.id  = 'gh-driver-overrides';
        style.textContent =
            '.driver-popover{border-radius:16px!important;padding:0!important;' +
                'box-shadow:0 20px 60px rgba(5,15,45,0.35)!important;' +
                'border:1px solid rgba(255,255,255,0.15)!important;' +
                'font-family:"Plus Jakarta Sans",sans-serif!important;overflow:hidden!important;}' +
            '.driver-popover *{box-sizing:border-box!important;}' +
            '.driver-popover-title{font-size:15px!important;font-weight:700!important;' +
                'color:#0f172a!important;padding:18px 20px 6px!important;margin:0!important;' +
                'font-family:"Plus Jakarta Sans",sans-serif!important;' +
                'display:flex!important;align-items:center!important;gap:8px!important;' +
                'border-bottom:1px solid #f1f5f9!important;}' +
            '.driver-popover-description{font-size:13px!important;color:#475569!important;' +
                'line-height:1.65!important;padding:12px 20px 16px!important;margin:0!important;}' +
            '.driver-popover-footer{padding:10px 20px 16px!important;border-top:1px solid #f8fafc!important;' +
                'display:flex!important;align-items:center!important;justify-content:space-between!important;' +
                'background:#fafbfd!important;}' +
            '.driver-popover-progress-text{font-size:11px!important;color:#94a3b8!important;font-weight:600!important;}' +
            '.driver-popover-prev-btn,.driver-popover-next-btn,.driver-popover-close-btn{' +
                'border-radius:8px!important;font-size:12px!important;font-weight:600!important;' +
                'padding:7px 16px!important;border:none!important;cursor:pointer!important;transition:all .15s!important;}' +
            '.driver-popover-next-btn{background:#0058af!important;color:#fff!important;' +
                'border-radius:8px!important;padding:9px 22px!important;font-size:13px!important;' +
                'font-weight:700!important;letter-spacing:0!important;' +
                '-webkit-font-smoothing:antialiased!important;text-shadow:none!important;}' +
            '.driver-popover-next-btn:hover{background:#004a9a!important;}' +
            '.driver-popover-prev-btn{background:#f1f5f9!important;color:#475569!important;}' +
            '.driver-popover-prev-btn:hover{background:#e2e8f0!important;}' +
            '.driver-popover-close-btn{background:transparent!important;color:#94a3b8!important;' +
                'font-size:16px!important;padding:4px 8px!important;}' +
            '.driver-popover-close-btn:hover{color:#ef4444!important;}' +
            '.driver-popover-arrow{filter:drop-shadow(0 2px 4px rgba(0,0,0,0.08))!important;}' +
            '.driver-overlay{backdrop-filter:none!important;}';
        document.head.appendChild(style);

        var scr   = document.createElement('script');
        scr.src   = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js';
        scr.onload = function () { window._fbDriverReady = true; cb(); };
        document.head.appendChild(scr);
    }

    // ── Icono helper ─────────────────────────────────────────────────────────
    function ic(name, color) {
        color = color || '#0058af';
        return '<i class="ti ti-' + name + '" style="color:' + color +
               ';font-size:16px;flex-shrink:0;"></i>';
    }

    // ── Botón "?" para el paso final ─────────────────────────────────────────
    var helpBtn =
        '<span style="display:inline-flex;align-items:center;justify-content:center;' +
        'width:20px;height:20px;border-radius:50%;vertical-align:middle;margin:0 2px;' +
        'background:linear-gradient(135deg,#0058af,#0074e0);' +
        'color:#fff;font-size:11px;font-weight:700;line-height:1;">?</span>';

    // ── Descripción del tab activo ────────────────────────────────────────────
    function tabDesc() {
        var map = {
            autoeval : 'Estás en <strong>Autoevaluación</strong> — tus calificaciones por competencia con gráficas de radar y barras.',
            recibidas: 'Estás en <strong>Recibidas</strong> — cómo te calificaron tu líder y los evaluadores externos.',
            realizadas:'Estás en <strong>Realizadas</strong> — las evaluaciones que tú completaste a colaboradores y liderazgo.',
            equipo   : 'Estás en <strong>Mi Equipo</strong> — resumen del avance de cada integrante de tu equipo.',
            plan     : 'Estás en <strong>Mi Plan de Mejora</strong> — objetivos SMART asignados en el proceso de feedback.',
        };
        return map[activeTab] || 'Navega por las pestañas para consultar tus resultados.';
    }

    // ── Elemento adaptativo según tab ────────────────────────────────────────
    function tabElement() {
        if (activeTab === 'autoeval')  return tieneAuto   ? '.rp-stat'  : '#rp-autoeval';
        if (activeTab === 'recibidas') return '#rp-recibidas';
        if (activeTab === 'realizadas')return '#rp-realizadas';
        if (activeTab === 'equipo')    return '#rp-equipo';
        if (activeTab === 'plan')      return '#rp-plan';
        return '.rp-panel.active';
    }

    // ── Pasos del tour ────────────────────────────────────────────────────────
    window.iniciarTourReportes = function () {
        _rpLoadDriver(function () {
            var driverFn = (window['driver'] && window['driver']['js'] && window['driver']['js']['driver'])
                ? window['driver']['js']['driver']
                : window['driver'];

            var pasos = [
                // 0 — Bienvenida
                {
                    popover: {
                        title: ic('chart-bar') + ' Mis Reportes',
                        description:
                            'Aquí encuentras <strong>todos los resultados de tu evaluación de desempeño</strong>: ' +
                            'autoevaluación, calificaciones recibidas, evaluaciones realizadas y tu plan de mejora. ' +
                            'Hagamos un recorrido rápido.',
                        side: 'over', align: 'center',
                    }
                },
                // 1 — Hero
                {
                    element: '#rp-hero',
                    popover: {
                        title: ic('layout-dashboard') + ' Panel principal',
                        description:
                            'En el encabezado ves el <strong>nombre del período activo</strong> y cuántos días restan ' +
                            'para el cierre de la evaluación.',
                        side: 'bottom', align: 'start',
                    }
                },
                // 2 — Tabs
                {
                    element: '#rpTabs',
                    popover: {
                        title: ic('layout-navbar') + ' Pestañas de contenido',
                        description:
                            '<strong>Autoevaluación</strong> — tus propias calificaciones.<br>' +
                            '<strong>Recibidas</strong> — cómo te evaluaron.<br>' +
                            '<strong>Realizadas</strong> — evaluaciones que tú hiciste.<br>' +
                            (esLider ? '<strong>Mi Equipo</strong> — avance de tu equipo.<br>' : '') +
                            '<strong>Mi Plan de Mejora</strong> — objetivos SMART del feedback.',
                        side: 'bottom', align: 'center',
                    }
                },
                // 3 — Contenido adaptativo
                {
                    element: tabElement(),
                    popover: {
                        title: ic('eye') + ' Contenido activo',
                        description: tabDesc() +
                            '<br><br><span style="font-size:11.5px;color:#94a3b8;">' +
                            'Cambia de pestaña en cualquier momento para explorar las otras secciones.</span>',
                        side: 'top', align: 'start',
                    }
                },
                // 4 — Final
                {
                    popover: {
                        title: ic('circle-check', '#16a34a') + ' ¡Todo listo!',
                        description:
                            'Ya conoces el módulo de <strong>Reportes</strong>. Explora cada pestaña para revisar ' +
                            'en detalle tu desempeño y el avance de tu plan de mejora. ' +
                            'Puedes repetir este recorrido pulsando el botón ' + helpBtn + ' en la esquina inferior derecha.',
                        side: 'over', align: 'center',
                    }
                },
            ];

            var tour;
            tour = driverFn({
                animate: true,
                overlayColor: 'rgba(5,15,45,0.60)',
                smoothScroll: true,
                showProgress: true,
                progressText: 'Paso {{current}} de {{total}}',
                nextBtnText: 'Siguiente',
                prevBtnText: 'Anterior',
                doneBtnText: 'Entendido',
                steps: pasos,
                onNextClick:  function () { if (tour.hasNextStep()) { tour.moveNext(); } else { tour.destroy(); } },
                onCloseClick: function () { tour.destroy(); },
            });
            tour.drive();
        });
    };
})();
</script>
