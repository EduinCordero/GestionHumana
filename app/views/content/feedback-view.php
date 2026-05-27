<?php
// Variables de control de acceso
$feedbackActivo = $feedbackActivo ?? true;  // true por defecto para no romper si viene de ruta directa
$esAdmin        = $esAdmin        ?? false;
$periodoActivo  = $periodoActivo  ?? null;

// ── PANTALLA DE BLOQUEO — solo líderes cuando feedback está deshabilitado ──
if (!$feedbackActivo && !$esAdmin): ?>
<style>
.fb-lock-root {
    font-family:'Plus Jakarta Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    padding:68px 32px 56px; max-width:680px; margin:0 auto;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    min-height:70vh;
}
</style>
<div class="fb-lock-root">
    <div style="width:100%;background:#fff;border-radius:20px;
                box-shadow:0 1px 3px rgba(0,0,0,.06),0 8px 32px rgba(0,0,0,.08);
                padding:48px 40px;text-align:center;">

        <!-- Ícono -->
        <div style="width:72px;height:72px;border-radius:20px;
                    background:linear-gradient(135deg,#f1f5f9,#e2e8f0);
                    display:flex;align-items:center;justify-content:center;
                    margin:0 auto 24px;">
            <i class="ti ti-lock" style="font-size:34px;color:#94a3b8;"></i>
        </div>

        <!-- Título -->
        <div style="font-size:1.25rem;font-weight:700;color:#1e293b;margin-bottom:10px;">
            Proceso de Feedback no disponible
        </div>

        <!-- Mensaje -->
        <div style="font-size:.9rem;color:#64748b;line-height:1.75;max-width:440px;margin:0 auto 28px;">
            El proceso de Feedback aún no ha sido habilitado por el equipo de
            <strong>Gestión Humana</strong>. Una vez que las evaluaciones de desempeño
            estén completas, recibirás una comunicación para iniciar esta etapa
            con tu equipo.
        </div>

        <!-- Período activo -->
        <?php if ($periodoActivo): ?>
        <div style="display:inline-flex;align-items:center;gap:8px;
                    background:#eff6ff;border:1.5px solid #bfdbfe;
                    border-radius:10px;padding:10px 20px;
                    font-size:.84rem;color:#1d4ed8;font-weight:500;">
            <i class="ti ti-calendar-event" style="font-size:16px;"></i>
            Período activo: <strong><?= htmlspecialchars($periodoActivo['NOMBRE'] ?? '', ENT_QUOTES) ?></strong>
        </div>
        <?php endif; ?>

        <!-- Separador -->
        <div style="border-top:1px solid #f1f5f9;margin:32px 0 24px;"></div>

        <!-- Volver -->
        <a href="<?= APP_URL ?>home/"
           style="display:inline-flex;align-items:center;gap:7px;
                  padding:10px 22px;border-radius:10px;
                  background:linear-gradient(135deg,#0058af,#2563eb);
                  color:#fff;font-size:.86rem;font-weight:600;
                  text-decoration:none;box-shadow:0 3px 10px rgba(0,88,175,.25);">
            <i class="ti ti-home"></i> Volver al inicio
        </a>
    </div>
</div>
<?php return; // No renderizar nada más del módulo
endif;
// ── FIN PANTALLA DE BLOQUEO ────────────────────────────────────────────────

// Nombres de competencias — desde $dictComp que viene del controller
// Limpiar encoding para JSON válido
$nombresComp = [];
foreach (($dictComp ?? []) as $k => $v) {
    $nombresComp[(int)$k] = (string)$v; // Ya viene convertido de competenciaModel
}
// Si dictComp no tiene datos, usar fallback
if (empty($nombresComp)) {
    $nombresComp = [
        1=>'Calidez Humana',2=>'Integridad',3=>'Innovacion',4=>'Comunicacion',
        5=>'Calidad',6=>'Disciplina',7=>'Formacion',8=>'SST',
        9=>'Relaciones',10=>'Eficacia',11=>'Tiempo y Recursos',
        12=>'Proposito',13=>'Colaboracion',14=>'Consistencia',15=>'Adaptabilidad',16=>'Amor',
    ];
}

// Variables por defecto para evitar errores
$periodoActivo = $periodoActivo ?? null;
$equipo = $equipo ?? [];
$maxObjetivos = $maxObjetivos ?? 3;

$calLabels = [
    1 => 'Insuficiente',
    2 => 'Requiere mejora',
    3 => 'Aceptable',
    4 => 'Acorde',
    5 => 'Sobresaliente',
];

$calColors = [
    1 => ['bg'=>'#fee2e2','color'=>'#991b1b'],
    2 => ['bg'=>'#fef3c7','color'=>'#92400e'],
    3 => ['bg'=>'#e0f2fe','color'=>'#0369a1'],
    4 => ['bg'=>'#dcfce7','color'=>'#15803d'],
    5 => ['bg'=>'#f3e8ff','color'=>'#6b21a8'],
];
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

:root {
    --fb-accent:  #0058af;
    --fb-accent2: #2563eb;
    --fb-bg:      #f8fafc;
    --fb-card:    #ffffff;
    --fb-border:  #e2e8f0;
    --fb-muted:   #64748b;
    --fb-text:    #1e293b;
    --fb-radius:  14px;
}
.fb-root { font-family:'Plus Jakarta Sans',sans-serif; padding:68px 32px 56px; max-width:1200px; margin:0 auto; }

/* ── HERO ── */
.fb-hero {
    background:linear-gradient(135deg,#0058af 0%,#1a73e8 60%,#2563eb 100%);
    border-radius:20px; padding:28px 36px;
    display:flex; align-items:center; justify-content:space-between;
    gap:20px; margin-bottom:24px; position:relative; overflow:hidden;
    box-shadow:0 4px 24px rgba(0,88,175,0.25);
}
.fb-hero::before { content:''; position:absolute; width:280px; height:280px;
    border-radius:50%; background:rgba(255,255,255,0.06); top:-80px; right:-60px; }
.fb-hero-title { font-size:22px; font-weight:600; color:#fff; margin-bottom:4px; }
.fb-hero-sub   { font-size:13px; color:rgba(255,255,255,0.6); }
.fb-hero-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25);
    border-radius:99px; padding:6px 14px; font-size:12px; color:#fff;
    font-family:'DM Mono',monospace;
}
.fb-hero-dot { width:7px; height:7px; border-radius:50%; background:#4ade80; animation:fbPulse 2s infinite; }
@keyframes fbPulse { 0%,100%{opacity:1;} 50%{opacity:.4;} }

/* ── STATS ── */
.fb-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
.fb-stat {
    background:var(--fb-card); border-radius:var(--fb-radius);
    border:1px solid var(--fb-border); padding:16px 20px;
    box-shadow:0 1px 4px rgba(0,0,0,0.04);
}
.fb-stat-n  { font-size:28px; font-weight:600; color:var(--fb-text); font-family:'DM Mono',monospace; display:block; line-height:1; }
.fb-stat-l  { font-size:11px; color:var(--fb-muted); margin-top:4px; display:block; letter-spacing:.5px; }

/* ── TABLA EQUIPO ── */
.fb-card { background:var(--fb-card); border-radius:var(--fb-radius); border:1px solid var(--fb-border); overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.04); margin-bottom:20px; }
.fb-card-head { padding:16px 22px; border-bottom:1px solid var(--fb-border); display:flex; align-items:center; justify-content:space-between; }
.fb-card-title { font-size:14px; font-weight:600; color:var(--fb-text); }
.fb-table { width:100%; border-collapse:collapse; }
.fb-table thead th { background:#f8fafc; padding:10px 18px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--fb-muted); text-align:left; border-bottom:1px solid var(--fb-border); }
.fb-table tbody tr { border-bottom:1px solid var(--fb-border); transition:background .15s; }
.fb-table tbody tr:last-child { border-bottom:none; }
.fb-table tbody tr:hover { background:#f8fafc; }
.fb-table td { padding:13px 18px; vertical-align:middle; }
.fb-emp-name { font-weight:600; font-size:.88rem; color:var(--fb-text); }
.fb-emp-cargo { font-size:.73rem; color:var(--fb-muted); margin-top:2px; }

/* ── BADGES ── */
.fb-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:99px; font-size:.72rem; font-weight:600; white-space:nowrap; }
.fb-badge-done    { background:#dcfce7; color:#15803d; }
.fb-badge-pending { background:#fef9c3; color:#854d0e; }
.fb-badge-none    { background:#f1f5f9; color:#64748b; }
.fb-badge-info    { background:#dbeafe; color:#1d4ed8; }

/* ── BOTONES ── */
.fb-btn { padding:7px 16px; border-radius:8px; font-size:.8rem; font-weight:600; cursor:pointer; border:none; font-family:inherit; transition:all .15s; white-space:nowrap; }
.fb-btn-primary  { background:var(--fb-accent); color:#fff; }
.fb-btn-primary:hover  { background:#0074e0; }
.fb-btn-outline  { background:#fff; color:var(--fb-accent); border:1px solid var(--fb-accent); }
.fb-btn-outline:hover  { background:#eff6ff; }
.fb-btn-done     { background:#f1f5f9; color:#94a3b8; cursor:default; }
.fb-btn-sm       { padding:5px 12px; font-size:.75rem; }

/* ── MODAL ── */
.fb-modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(15,23,42,.5);
    z-index:1000; align-items:center; justify-content:center; padding:20px;
}
.fb-modal-overlay.active { display:flex; }
.fb-modal {
    background:#fff; border-radius:20px; width:100%; max-width:780px;
    max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.2);
    animation:fbSlideIn .3s ease;
}
@keyframes fbSlideIn { from{opacity:0;transform:translateY(20px);} to{opacity:1;transform:translateY(0);} }
.fb-modal-header {
    background:linear-gradient(135deg,#003d82 0%,#0058af 50%,#0074e0 100%);
    padding:24px 28px 20px; border-radius:20px 20px 0 0;
    position:relative; overflow:hidden; flex-shrink:0;
}
.fb-modal-header::before {
    content:''; position:absolute; width:260px; height:260px; border-radius:50%;
    background:rgba(255,255,255,.06); top:-80px; right:-50px; pointer-events:none;
}
.fb-modal-header::after {
    content:''; position:absolute; width:140px; height:140px; border-radius:50%;
    background:rgba(255,255,255,.04); bottom:-40px; left:30px; pointer-events:none;
}
.fb-modal-header-top {
    display:flex; align-items:flex-start; justify-content:space-between;
    gap:12px; position:relative; margin-bottom:18px;
}
.fb-modal-eyebrow { font-size:.65rem; letter-spacing:2px; text-transform:uppercase;
    color:rgba(255,255,255,.55); margin-bottom:6px; }
.fb-modal-title  { font-size:1.1rem; font-weight:800; color:#fff; line-height:1.2; }
.fb-modal-sub    { font-size:.78rem; color:rgba(255,255,255,.6); margin-top:3px; }
.fb-modal-close  { background:rgba(255,255,255,.15); border:none; border-radius:50%;
    width:32px; height:32px; cursor:pointer; color:#fff; font-size:1rem;
    display:flex; align-items:center; justify-content:center;
    transition:background .15s; flex-shrink:0; position:relative; }
.fb-modal-close:hover { background:rgba(255,255,255,.25); }
.fb-modal-body   { padding:24px 28px; }
.fb-modal-footer { padding:16px 28px; border-top:1px solid var(--fb-border); display:flex; justify-content:flex-end; gap:10px; }
/* Stepper sobre fondo azul — labels blancos (Proceso 1 y Flujo 2) */
.fb-modal-header .fb-step-label,
#fl2ModalOverlay .fb-stepper .fb-step-label        { color:rgba(255,255,255,.45); }
.fb-modal-header .fb-step-label.active,
#fl2ModalOverlay .fb-stepper .fb-step-label.active { color:#fff; }
.fb-modal-header .fb-step-circle.pending,
#fl2ModalOverlay .fb-stepper .fb-step-circle.pending { background:rgba(255,255,255,.15); color:rgba(255,255,255,.6); }
.fb-modal-header .fb-step-circle.active,
#fl2ModalOverlay .fb-stepper .fb-step-circle.active  { background:#fff; color:#0058af; }
.fb-modal-header .fb-step-circle.done,
#fl2ModalOverlay .fb-stepper .fb-step-circle.done    { background:#4ade80; color:#fff; }
.fb-modal-header .fb-step-connector,
#fl2ModalOverlay .fb-stepper .fb-step-connector      { background:rgba(255,255,255,.2); }
.fb-modal-header .fb-step-connector.done,
#fl2ModalOverlay .fb-stepper .fb-step-connector.done { background:#4ade80; }

/* ── SECCIONES DEL MODAL ── */
.fb-section-label { font-size:10px; letter-spacing:1.5px; text-transform:uppercase; color:var(--fb-muted); margin-bottom:10px; font-weight:600; }
.fb-input-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px; }
.fb-input-full { margin-bottom:14px; }
.fb-label { font-size:12px; font-weight:600; color:var(--fb-text); margin-bottom:5px; display:block; }
.fb-input, .fb-textarea, .fb-select {
    width:100%; padding:9px 12px; border:1px solid var(--fb-border);
    border-radius:8px; font-size:13px; font-family:inherit;
    color:var(--fb-text); outline:none; transition:border-color .15s;
    box-sizing:border-box;
}
.fb-input:focus, .fb-textarea:focus, .fb-select:focus { border-color:var(--fb-accent); }
.fb-textarea { resize:vertical; min-height:80px; }

/* ── COMPETENCIAS GRID ── */
.fb-comp-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px; }
.fb-comp-item.fb-comp-asignada {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
}
.fb-comp-item {
    padding:10px 12px; border-radius:10px; border:1px solid var(--fb-border);
    background:#f8fafc; cursor:pointer; transition:all .15s;
}
.fb-comp-item:hover { border-color:var(--fb-accent); background:#eff6ff; }
.fb-comp-item.selected { border-color:var(--fb-accent); background:#dbeafe; }
.fb-comp-name { font-size:12px; font-weight:600; color:var(--fb-text); }
.fb-comp-cal  { font-size:11px; margin-top:3px; display:inline-block; padding:2px 8px; border-radius:99px; font-weight:600; }

/* ── OBJETIVOS LISTA ── */
.fb-obj-list { display:flex; flex-direction:column; gap:8px; margin-top:10px; max-height:280px; overflow-y:auto; }
.fb-obj-item {
    padding:12px 14px; border-radius:10px; border:1px solid var(--fb-border);
    background:#f8fafc; cursor:pointer; transition:all .15s;
}
.fb-obj-item:hover { border-color:var(--fb-accent); background:#eff6ff; }
.fb-obj-item.selected { border-color:var(--fb-accent); background:#dbeafe; }
.fb-obj-modelo { font-size:10px; font-weight:600; letter-spacing:.5px; text-transform:uppercase; color:var(--fb-accent); margin-bottom:4px; }
.fb-obj-texto  { font-size:12px; color:var(--fb-text); line-height:1.5; }

/* ── ACUERDOS ASIGNADOS ── */
.fb-acuerdo-item {
    background:#f8fafc; border:1px solid var(--fb-border);
    border-radius:10px; padding:12px 14px; margin-bottom:8px;
}
.fb-acuerdo-comp  { font-size:11px; font-weight:600; color:var(--fb-accent); margin-bottom:4px; }
.fb-acuerdo-obj   { font-size:12px; color:var(--fb-text); line-height:1.5; }

/* ── STEPPER ── */
.fb-stepper { display:flex; align-items:center; gap:0; margin-bottom:20px; }
.fb-step-item { display:flex; align-items:center; flex:1; }
.fb-step-item:last-child { flex:0; }
.fb-step-circle {
    width:32px; height:32px; border-radius:50%; display:flex; align-items:center;
    justify-content:center; font-size:13px; font-weight:600; flex-shrink:0;
    transition:all .3s;
}
.fb-step-circle.done    { background:#dcfce7; color:#15803d; }
.fb-step-circle.active  { background:var(--fb-accent); color:#fff; }
.fb-step-circle.pending { background:#f1f5f9; color:#94a3b8; }
.fb-step-label { font-size:11px; font-weight:500; margin-top:4px; text-align:center; }
.fb-step-label.active  { color:var(--fb-accent); }
.fb-step-label.pending { color:#94a3b8; }
.fb-step-connector { flex:1; height:2px; background:#e2e8f0; margin:0 6px; margin-bottom:16px; }
.fb-step-connector.done { background:#4ade80; }
.fb-step-wrap { display:flex; flex-direction:column; align-items:center; }

/* ── ANIMACIONES ── */
.fb-fade { opacity:0; transform:translateY(10px); animation:fbFade .4s ease forwards; }
@keyframes fbFade { to{opacity:1;transform:translateY(0);} }
.fb-d1{animation-delay:.05s} .fb-d2{animation-delay:.1s} .fb-d3{animation-delay:.15s}

/* ── LOADING ── */
.fb-loading { text-align:center; padding:30px; color:var(--fb-muted); font-size:13px; }
.fb-loading-spin { width:28px; height:28px; border:3px solid #e2e8f0; border-top-color:var(--fb-accent); border-radius:50%; animation:spin .8s linear infinite; margin:0 auto 10px; }
@keyframes spin { to{transform:rotate(360deg);} }

@media(max-width:768px){
    .fb-stats { grid-template-columns:1fr 1fr; }
    .fb-comp-grid { grid-template-columns:1fr; }
    .fb-input-row { grid-template-columns:1fr; }
}
</style>

<div class="fb-root">

<?php if (!$feedbackActivo && $esAdmin): ?>
<!-- ── Banner admin: módulo deshabilitado ── -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;
            flex-wrap:wrap;padding:14px 20px;margin-bottom:20px;
            background:#fef3c7;border:1.5px solid #fbbf24;border-radius:14px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <i class="ti ti-eye-off" style="font-size:20px;color:#d97706;flex-shrink:0;"></i>
        <div>
            <div style="font-weight:700;font-size:.9rem;color:#92400e;">
                Módulo de Feedback deshabilitado
            </div>
            <div style="font-size:.8rem;color:#b45309;margin-top:2px;">
                Los líderes ven el mensaje "proceso no disponible". Estás viendo el panel en modo administrador.
            </div>
        </div>
    </div>
    <a href="<?= APP_URL ?>admin/?tab=periodos"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
              background:#d97706;color:#fff;border-radius:9px;font-size:.8rem;
              font-weight:600;text-decoration:none;white-space:nowrap;">
        <i class="ti ti-settings"></i> Ir a configuración
    </a>
</div>
<?php endif; ?>

<!-- ── HERO ── -->
<div class="fb-hero fb-fade fb-d1">
    <div>
        <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;
                        color:rgba(255,255,255,0.55);margin-bottom:6px;
                        font-family:'Plus Jakarta Sans',sans-serif;">
                Clínica Zayma · Evaluación de Desempeño
            </div>
        <div class="fb-hero-title">Feedback</div>
        <div class="fb-hero-sub">Registra la reunión de retroalimentación y asigna objetivos SMART a tu equipo</div>
    </div>
    <?php if ($periodoActivo): ?>
    <div class="fb-hero-badge">
        <div class="fb-hero-dot"></div>
        Período activo · Cierre <?= htmlspecialchars($periodoActivo['FECHACIERRE']) ?>
    </div>
    <?php else: ?>
    <div class="fb-hero-badge" style="background:rgba(239,68,68,.2);border-color:rgba(239,68,68,.4);">
        Sin período activo
    </div>
    <?php endif; ?>
</div>

<?php if (!$periodoActivo): ?>
<div style="text-align:center;padding:60px 20px;color:var(--fb-muted);">
    <div style="font-size:40px;margin-bottom:12px;">·</div>
    <div style="font-size:15px;font-weight:600;">No hay un período de evaluación activo</div>
    <div style="font-size:13px;margin-top:6px;">Contacta al área de Gestión Humana para activar un período.</div>
</div>
<?php else: ?>

<?php
// Estadísticas del equipo
$totalEq      = count($equipo);
$conFeedback  = count(array_filter($equipo, fn($c) => $c['TIENE_FEEDBACK'] == 1));
$conAcuerdos  = count(array_filter($equipo, fn($c) => $c['TOTAL_ACUERDOS'] > 0));
$completos    = count(array_filter($equipo, fn($c) => $c['TIENE_FEEDBACK'] == 1 && $c['TOTAL_ACUERDOS'] >= $maxObjetivos));
?>

<!-- ── STATS ── -->
<div class="fb-stats fb-fade fb-d2">
    <div class="fb-stat">
        <span class="fb-stat-n"><?= $totalEq ?></span>
        <span class="fb-stat-l">Colaboradores</span>
    </div>
    <div class="fb-stat">
        <span class="fb-stat-n"><?= $conFeedback ?></span>
        <span class="fb-stat-l">Con feedback</span>
    </div>
    <div class="fb-stat">
        <span class="fb-stat-n"><?= $conAcuerdos ?></span>
        <span class="fb-stat-l">Con objetivos SMART</span>
    </div>
    <div class="fb-stat">
        <span class="fb-stat-n"><?= $completos ?></span>
        <span class="fb-stat-l">Proceso completo</span>
    </div>
</div>

<!-- ── TABLA EQUIPO ── -->
<div class="fb-card fb-fade fb-d3">
    <div class="fb-card-head">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:10px;
                        background:linear-gradient(135deg,#0058af,#0074e0);
                        display:flex;align-items:center;justify-content:center;">
                <span style="color:#fff;"><?= icon('users', 18) ?></span>
            </div>
            <div>
                <div class="fb-card-title">Mi equipo — Retroalimentación del período</div>
                <div style="font-size:11px;color:var(--fb-muted);">
                    Proceso 1 · Competencias P1-P11 · Máx. <?= $maxObjetivos ?> objetivos SMART por colaborador
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($equipo)): ?>
    <div style="text-align:center;padding:48px;color:var(--fb-muted);">
        <div style="font-size:32px;margin-bottom:10px;">·</div>
        <div style="font-size:13px;">No se encontraron colaboradores a cargo en el período activo.</div>
    </div>
    <?php else: ?>
    <table class="fb-table">
        <thead>
            <tr>
                <th>Colaborador</th>
                <th style="text-align:center;">Autoevaluó</th>
                <th style="text-align:center;">Fue evaluado</th>
                <th style="text-align:center;">Feedback</th>
                <th style="text-align:center;">Objetivos SMART</th>
                <th style="text-align:center;">Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($equipo as $col): ?>
        <tr>
            <td>
                <div class="fb-emp-name"><?= htmlspecialchars($col['EMPLEADO']) ?></div>
                <div class="fb-emp-cargo"><?= htmlspecialchars($col['CARGO']) ?></div>
            </td>
            <td style="text-align:center;">
                <span class="fb-badge <?= $col['TIENE_AUTO'] ? 'fb-badge-done' : 'fb-badge-none' ?>">
                    <?= $col['TIENE_AUTO'] ? (icon('check-circle', 14) . ' Sí') : (icon('clock', 14) . ' No') ?>
                </span>
            </td>
            <td style="text-align:center;">
                <span class="fb-badge <?= $col['TIENE_EVAL_COLAB'] ? 'fb-badge-done' : 'fb-badge-none' ?>">
                    <?= $col['TIENE_EVAL_COLAB'] ? (icon('check-circle', 14) . ' Sí') : (icon('clock', 14) . ' No') ?>
                </span>
            </td>
            <td style="text-align:center;">
                <?php if ($col['TIENE_FEEDBACK']): ?>
                <?php
                    // Separar fecha y hora para mostrar en dos líneas
                    $fbPartes = explode(' ', $col['FECHA_FEEDBACK'] ?? '');
                    $fbFechaSolo = $fbPartes[0] ?? '';
                    $fbHoraSolo  = $fbPartes[1] ?? '';
                ?>
                <div>
                    <span class="fb-badge fb-badge-done">✓ Registrado</span>
                    <div style="font-size:10px;color:var(--fb-muted);margin-top:3px;">
                        · <?= $fbFechaSolo ?>
                        <?php if ($fbHoraSolo): ?>
                        &nbsp;· <?= $fbHoraSolo ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <span class="fb-badge fb-badge-pending">· Pendiente</span>
                <?php endif; ?>
            </td>
            <td style="text-align:center;">
                <?php
                $total   = (int)$col['TOTAL_ACUERDOS'];
                $aprobados = (int)$col['ACUERDOS_APROBADOS'];
                ?>
                <?php if ($total > 0): ?>
                <span class="fb-badge fb-badge-info"><?= $total ?>/<?= $maxObjetivos ?> asignados</span>
                <?php else: ?>
                <span class="fb-badge fb-badge-none">Sin asignar</span>
                <?php endif; ?>
            </td>
            <td style="text-align:center;">
                <?php
                $puedeIniciarFeedback = $col['TIENE_AUTO'] && $col['TIENE_EVAL_COLAB'];
                $yaTieneFeedback      = $col['TIENE_FEEDBACK'];
                // Si ya tiene feedback registrado, siempre puede editar
                // Si no, solo puede iniciar si ambas evaluaciones están completas
                $btnHabilitado = $yaTieneFeedback || $puedeIniciarFeedback;
                ?>
                <?php if ($btnHabilitado): ?>
                <?php $firmaColab = (int)($col['FIRMADO_COLAB'] ?? 0); ?>
                <button class="fb-btn <?= $firmaColab ? 'fb-btn-outline' : 'fb-btn-primary' ?> fb-btn-sm"
                    onclick="abrirModalFeedback(
                        <?= $col['IDEMPLEADO'] ?>,
                        '<?= addslashes(htmlspecialchars($col['EMPLEADO'])) ?>',
                        '<?= addslashes(htmlspecialchars($col['CARGO'])) ?>',
                        <?= $col['TIENE_FEEDBACK'] ? 1 : 0 ?>,
                        <?= $col['IDFEEDBACK'] ?: 'null' ?>,
                        '<?= $col['FECHA_FEEDBACK'] ?: '' ?>',
                        '<?= addslashes($col['OBSERVACION'] ?: '') ?>',
                        <?= $col['TOTAL_ACUERDOS'] ?>,
                        <?= $firmaColab ?>
                    )">
                    <?php if ($firmaColab): ?>
                        <?= icon('check-circle', 14) ?> Firmado
                    <?php elseif ($yaTieneFeedback): ?>
                        <?= icon('pen', 14) ?> Editar
                    <?php else: ?>
                        <?= icon('message-circle', 14) ?> Registrar
                    <?php endif; ?>
                </button>
                <?php else: ?>
                <?php
                // Construir mensaje indicando qué falta
                $faltaMsg = [];
                if (!$col['TIENE_AUTO'])       $faltaMsg[] = 'autoevaluación';
                if (!$col['TIENE_EVAL_COLAB']) $faltaMsg[] = 'evaluación del líder';
                $tooltip = 'Falta: ' . implode(' y ', $faltaMsg);
                ?>
                <div title="<?= htmlspecialchars($tooltip) ?>"
                     style="display:inline-block;">
                    <button class="fb-btn fb-btn-sm"
                            disabled
                            style="background:#f1f5f9;color:#94a3b8;cursor:not-allowed;border:1px dashed #cbd5e1;">
                        · Pendiente
                    </button>
                </div>
                <div style="font-size:10px;color:#f59e0b;margin-top:3px;max-width:110px;line-height:1.3;">
                    <?= htmlspecialchars($tooltip) ?>
                </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php endif; // fin periodoActivo ?>



<?php if (!empty($lideresACargo)): ?>
<!-- ── TABLA LÍDERES A CARGO (Flujo 2 — solo directores) ── -->
<div class="fb-card fb-fade fb-d3" style="margin-top:24px;">
    <div class="fb-card-head">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:10px;
                        background:linear-gradient(135deg,#0058af,#0074e0);
                        display:flex;align-items:center;justify-content:center;">
                <span style="color:#fff;"><!-- forzamos icono blanco aunque el tema sea oscuro -->
                    <?= icon('users', 18) ?>
                 </span>
            </div>
            <div>
                <div class="fb-card-title">Feedback a Líderes — Competencias de Liderazgo</div>
                <div style="font-size:11px;color:var(--fb-muted);">
                    Basado en calificaciones promediadas P12-P16 recibidas de sus colaboradores
                </div>
            </div>
        </div>
    </div>

    <table class="fb-table">
        <thead>
            <tr>
                <th>Líder</th>
                <th style="text-align:center;">Evaluaciones recibidas</th>
                <th style="text-align:center;">Feedback</th>
                <th style="text-align:center;">Objetivos SMART</th>
                <th style="text-align:center;">Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lideresACargo as $lid): ?>
        <?php
        $tieneFbL2   = ($lid['IDFEEDBACK'] ?? 0) > 0;
        $firmadoL2   = ($lid['FIRMADO_COLAB'] ?? 0) > 0;
        $objL2       = (int)($lid['TOTAL_OBJETIVOS_L2'] ?? 0);
        $tieneEvalL2 = $lid['TIENE_EVAL'] > 0;
        $maxObj      = $maxObjetivos ?? 3;
        ?>
        <tr>
            <td>
                <div class="fb-emp-name"><?= htmlspecialchars($lid['NOMBRE']) ?></div>
                <div class="fb-emp-cargo"><?= htmlspecialchars($lid['CARGO']) ?></div>
            </td>
            <td style="text-align:center;">
                <span class="fb-badge <?= $tieneEvalL2 ? 'fb-badge-done' : 'fb-badge-none' ?>">
                    <?= $tieneEvalL2
                        ? (icon('check-circle', 14) . ' ' . $lid['TIENE_EVAL'] . '/' . $lid['TOTAL_COLAB'])
                        : (icon('clock', 14) . ' 0/' . $lid['TOTAL_COLAB']) ?>
                </span>
            </td>
            <td style="text-align:center;">
                <?php if ($tieneFbL2): ?>
                    <span class="fb-badge fb-badge-done"><?= icon('check-circle', 14) ?> Registrado</span>
                    <?php if (!empty($lid['FECHA_FEEDBACK_L2'])): ?>
                    <div style="font-size:10px;color:var(--fb-muted);margin-top:3px;">
                        · <?= htmlspecialchars(explode(' ', $lid['FECHA_FEEDBACK_L2'])[0] ?? '') ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="fb-badge fb-badge-pending"><?= icon('clock', 14) ?> Pendiente</span>
                <?php endif; ?>
            </td>
            <td style="text-align:center;">
                <?php if ($objL2 > 0): ?>
                    <span class="fb-badge fb-badge-info"><?= $objL2 ?>/<?= $maxObj ?> asignados</span>
                <?php else: ?>
                    <span class="fb-badge fb-badge-none">Sin asignar</span>
                <?php endif; ?>
            </td>
            <td style="text-align:center;">
                <?php if (!$tieneEvalL2): ?>
                    <button class="fb-btn fb-btn-sm" disabled
                            style="background:#f1f5f9;color:#94a3b8;border:1px dashed #cbd5e1;cursor:not-allowed;">
                        Sin evaluaciones
                    </button>
                <?php elseif ($firmadoL2): ?>
                    <button class="fb-btn fb-btn-outline fb-btn-sm"
                            onclick="abrirFeedbackLider(<?= $lid['IDEMPLEADO'] ?>, '<?= addslashes($lid['NOMBRE']) ?>', '<?= addslashes($lid['CARGO']) ?>', <?= $lid['IDFEEDBACK'] ?>, true)">
                        <?= icon('check-circle', 14) ?> Firmado
                    </button>
                <?php elseif ($tieneFbL2 && $objL2 >= $maxObj): ?>
                    <button class="fb-btn fb-btn-sm"
                            onclick="abrirFeedbackLider(<?= $lid['IDEMPLEADO'] ?>, '<?= addslashes($lid['NOMBRE']) ?>', '<?= addslashes($lid['CARGO']) ?>', <?= $lid['IDFEEDBACK'] ?>, true)"
                            style="background:#fff7ed;color:#d97706;border:1.5px solid #fed7aa;">
                        <?= icon('pen', 14) ?> Pendiente firmar
                    </button>
                <?php elseif ($tieneFbL2): ?>
                    <button class="fb-btn fb-btn-primary fb-btn-sm"
                            onclick="abrirFeedbackLider(<?= $lid['IDEMPLEADO'] ?>, '<?= addslashes($lid['NOMBRE']) ?>', '<?= addslashes($lid['CARGO']) ?>', <?= $lid['IDFEEDBACK'] ?>)">
                        <?= icon('plus', 14) ?> Asignar objetivos
                    </button>
                <?php else: ?>
                    <button class="fb-btn fb-btn-primary fb-btn-sm"
                            onclick="abrirFeedbackLider(<?= $lid['IDEMPLEADO'] ?>, '<?= addslashes($lid['NOMBRE']) ?>', '<?= addslashes($lid['CARGO']) ?>', 0)"
                            style="background:linear-gradient(135deg,#0058af,#0074e0);">
                        <?= icon('message-circle', 14) ?> Registrar Feedback
                    </button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

</div><!-- fb-root -->


<!-- ══════════════════════════════════
     MODAL FLUJO 2 — Feedback a Líderes
══════════════════════════════════ -->
<style>
.fl2-comp-row {
    border:1.5px solid #e2e8f0; border-radius:12px; padding:14px 18px;
    cursor:pointer; transition:all .18s; background:#fff; margin-bottom:8px;
    display:flex; align-items:center; justify-content:space-between; gap:12px;
}
.fl2-comp-row:hover { border-color:#0058af; background:#eff6ff; transform:translateX(3px); }
.fl2-comp-row.asignada { background:#f0fdf4; border-color:#86efac; cursor:default; }
.fl2-obj-wrap { border:1.5px solid #e2e8f0; border-radius:12px; margin-bottom:10px; overflow:hidden; transition:border-color .15s; }
.fl2-obj-wrap.selected { border-color:#0058af; }
.fl2-obj-head {
    padding:13px 16px; display:flex; align-items:center; gap:12px;
    cursor:pointer; background:#f8fafc; transition:background .15s;
}
.fl2-obj-head:hover { background:#eff6ff; }
.fl2-obj-wrap.selected .fl2-obj-head { background:#dbeafe; }
.fl2-obj-toggle { width:20px; height:20px; border-radius:50%; border:2px solid #cbd5e1;
    flex-shrink:0; transition:all .15s; display:flex; align-items:center; justify-content:center; }
.fl2-obj-wrap.selected .fl2-obj-toggle { background:#0058af; border-color:#0058af; color:#fff; }
.fl2-obj-body { display:none; padding:16px; background:#fff; border-top:1px solid #e2e8f0; }
.fl2-obj-body.open { display:block; }
.fl2-field-label { font-size:.7rem; font-weight:700; color:#0058af; text-transform:uppercase;
    letter-spacing:.06em; display:block; margin-bottom:4px; }
.fl2-input { width:100%; padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:8px;
    font-size:.83rem; font-family:inherit; outline:none; transition:border-color .15s; box-sizing:border-box; }
.fl2-input:focus { border-color:#0058af; }
.fl2-counter-pill {
    display:inline-flex; align-items:center; gap:5px; padding:3px 12px;
    border-radius:20px; font-size:.72rem; font-weight:700;
    background:#dbeafe; color:#1e40af;
}
.fl2-counter-pill.lleno { background:#dcfce7; color:#15803d; }
</style>

<div id="fl2ModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(10,20,40,.75);
     z-index:9000;align-items:center;justify-content:center;padding:16px;">
<div style="background:#fff;border-radius:22px;width:min(740px,98vw);max-height:92vh;
            overflow:hidden;display:flex;flex-direction:column;
            box-shadow:0 32px 80px rgba(0,88,175,.3);">

    <!-- Header degradado -->
    <div style="background:linear-gradient(135deg,#003d82 0%,#0058af 50%,#0074e0 100%);
                padding:24px 28px 18px;flex-shrink:0;position:relative;overflow:hidden;">
        <div style="position:absolute;width:260px;height:260px;border-radius:50%;
                    background:rgba(255,255,255,.06);top:-80px;right:-50px;pointer-events:none;"></div>
        <div style="position:absolute;width:140px;height:140px;border-radius:50%;
                    background:rgba(255,255,255,.04);bottom:-40px;left:30px;pointer-events:none;"></div>

        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;position:relative;">
            <div>
                <div style="font-size:.65rem;letter-spacing:2px;text-transform:uppercase;
                            color:rgba(255,255,255,.55);margin-bottom:6px;">
                    Feedback de Liderazgo &mdash; Competencias P12 a P16
                </div>
                <div id="fl2NombreLider" style="font-size:1.1rem;font-weight:800;color:#fff;line-height:1.2;"></div>
                <div id="fl2CargoLider" style="font-size:.78rem;color:rgba(255,255,255,.6);margin-top:3px;"></div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <span id="fl2CounterPill" class="fl2-counter-pill">0 / 3 obj.</span>
                <button onclick="cerrarFl2Modal()" style="background:rgba(255,255,255,.15);border:none;
                        color:#fff;width:32px;height:32px;border-radius:50%;font-size:1rem;
                        cursor:pointer;line-height:1;">&#215;</button>
            </div>
        </div>

        <!-- Stepper 4 pasos -->
        <div class="fb-stepper" id="fl2Stepper" style="margin-top:18px;margin-bottom:0;">
            <div class="fb-step-wrap">
                <div class="fb-step-circle active" id="fl2StepCircle1">1</div>
                <div class="fb-step-label active" id="fl2StepLabel1" style="color:#fff;">Feedback</div>
            </div>
            <div class="fb-step-connector" id="fl2StepConn1"></div>
            <div class="fb-step-wrap">
                <div class="fb-step-circle pending" id="fl2StepCircle2">2</div>
                <div class="fb-step-label pending" id="fl2StepLabel2" style="color:rgba(255,255,255,.45);">Competencias</div>
            </div>
            <div class="fb-step-connector" id="fl2StepConn2"></div>
            <div class="fb-step-wrap">
                <div class="fb-step-circle pending" id="fl2StepCircle3">3</div>
                <div class="fb-step-label pending" id="fl2StepLabel3" style="color:rgba(255,255,255,.45);">Objetivos</div>
            </div>
            <div class="fb-step-connector" id="fl2StepConn3"></div>
            <div class="fb-step-wrap">
                <div class="fb-step-circle pending" id="fl2StepCircle4">4</div>
                <div class="fb-step-label pending" id="fl2StepLabel4" style="color:rgba(255,255,255,.45);">Firma</div>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div style="overflow-y:auto;flex:1;padding:22px 28px;">

        <!-- PASO 0: Registro de feedback (antes de objetivos) -->
        <div id="fl2PasoFeedback" style="display:none;">
            <div style="font-size:.82rem;color:var(--fb-muted);margin:0 0 16px;line-height:1.6;">
                Registra la reuni&oacute;n de feedback con el l&iacute;der antes de asignar objetivos.
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label class="fl2-field-label">Fecha de la reuni&oacute;n</label>
                    <input class="fl2-input" type="date" id="fl2FbFecha">
                </div>
                <div>
                    <label class="fl2-field-label">Hora</label>
                    <input class="fl2-input" type="time" id="fl2FbHora">
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label class="fl2-field-label">Observaciones de la reuni&oacute;n</label>
                <textarea class="fl2-input" id="fl2FbObs" rows="3" style="resize:vertical;"
                    placeholder="Temas tratados, compromisos verbales, contexto..."></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button onclick="cerrarFl2Modal()" style="padding:10px 18px;border:1.5px solid #e2e8f0;
                        border-radius:10px;background:#fff;color:#64748b;font-size:.85rem;
                        font-weight:600;cursor:pointer;">Cancelar</button>
                <button id="fl2BtnGuardarFb" onclick="fl2GuardarFeedback()"
                        style="background:linear-gradient(135deg,#0058af,#0074e0);color:#fff;border:none;
                               border-radius:10px;padding:10px 24px;font-weight:700;font-size:.88rem;
                               cursor:pointer;box-shadow:0 4px 12px rgba(0,88,175,.3);">
                    Guardar y continuar &rarr;
                </button>
            </div>
        </div>

        <!-- PASO 1: Competencias con calificaciones -->
        <div id="fl2Paso1">
            <p style="font-size:.82rem;color:var(--fb-muted);margin:0 0 16px;line-height:1.6;">
                Selecciona la competencia de liderazgo a trabajar.
                Las calificaciones son el <strong>promedio</strong> de las evaluaciones
                recibidas de los colaboradores del l&iacute;der.
            </p>
            <div id="fl2GridComps">
                <div style="padding:32px;text-align:center;color:var(--fb-muted);">
                    <div style="width:28px;height:28px;border:3px solid #e2e8f0;border-top-color:#0058af;
                                border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 10px;"></div>
                    Cargando calificaciones...
                </div>
            </div>
        </div>

        <!-- PASO FIRMA: firma del líder -->
        <div id="fl2PasoFirma" style="display:none;">
            <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;
                        padding:16px 18px;margin-bottom:16px;">
                <div style="font-size:.82rem;font-weight:700;color:#15803d;margin-bottom:4px;">
                    &#10003; Objetivos de liderazgo asignados correctamente
                </div>
                <div style="font-size:.78rem;color:#166534;line-height:1.5;">
                    Para completar el proceso, el l&iacute;der debe firmar el recibido
                    ingresando sus credenciales del sistema.
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;
                            padding:14px;text-align:center;">
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;
                                text-transform:uppercase;color:var(--fb-muted);margin-bottom:8px;">Director</div>
                    <div style="font-size:1.2rem;margin-bottom:4px;">&#10003;</div>
                    <div style="font-size:.82rem;font-weight:600;color:var(--fb-text);" id="fl2NombreDirectorFirma"></div>
                    <div style="font-size:.72rem;color:#15803d;margin-top:4px;">&#10003; Feedback registrado</div>
                </div>
                <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;
                            padding:14px;text-align:center;">
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;
                                text-transform:uppercase;color:var(--fb-muted);margin-bottom:8px;">L&iacute;der</div>
                    <div style="font-size:1.2rem;margin-bottom:4px;">&#183;</div>
                    <div id="fl2NombreLiderFirmaPanel" style="font-size:.82rem;font-weight:600;color:var(--fb-text);"></div>
                    <div id="fl2EstadoFirmaLider" style="font-size:.72rem;color:#d97706;margin-top:4px;">&#183; Pendiente de firma</div>
                </div>
            </div>
            <div id="fl2FormFirma">
                <div style="font-size:.78rem;font-weight:700;color:#374151;
                            text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">
                    El l&iacute;der ingresa sus credenciales
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:8px;">
                    <div>
                        <label class="fl2-field-label">Identificaci&oacute;n</label>
                        <input class="fl2-input" type="text" id="fl2FirmaCedula"
                               placeholder="N&uacute;mero de identificaci&oacute;n" autocomplete="off">
                    </div>
                    <div>
                        <label class="fl2-field-label">Contrase&ntilde;a</label>
                        <input class="fl2-input" type="password" id="fl2FirmaPassword"
                               placeholder="Contrase&ntilde;a del sistema" autocomplete="new-password">
                    </div>
                </div>
                <div style="font-size:.73rem;color:var(--fb-muted);margin-bottom:14px;">
                    El l&iacute;der debe ingresar sus propias credenciales para confirmar recibido.
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;">
                    <button onclick="cerrarFl2Modal(true)"
                            style="padding:10px 18px;border:1.5px solid #e2e8f0;border-radius:10px;
                                   background:#fff;color:#64748b;font-size:.85rem;font-weight:600;cursor:pointer;">
                        Firmar despu&eacute;s
                    </button>
                    <button id="fl2BtnFirmar" onclick="fl2Firmar()"
                            style="background:linear-gradient(135deg,#0058af,#0074e0);color:#fff;border:none;
                                   border-radius:10px;padding:10px 24px;font-weight:700;font-size:.88rem;
                                   cursor:pointer;box-shadow:0 4px 12px rgba(0,88,175,.3);">
                        &#10002; Confirmar firma
                    </button>
                </div>
            </div>
            <div id="fl2FirmaOk" style="display:none;text-align:center;padding:12px;">
                <div style="font-size:1.6rem;margin-bottom:6px;">&#10003;</div>
                <div style="font-size:.95rem;font-weight:700;color:#15803d;">Proceso completado</div>
                <div style="font-size:.82rem;color:var(--fb-muted);margin-top:4px;">
                    El l&iacute;der firm&oacute; el recibido del feedback de liderazgo.
                </div>
            </div>

            <!-- Panel seguimiento Flujo 2 -->
            <div id="fl2PanelSeguimiento" style="display:none;margin-top:16px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                    <div style="width:4px;height:18px;background:#0058af;border-radius:2px;"></div>
                    <div style="font-weight:700;font-size:.85rem;color:#1e3a5f;">
                        Seguimiento de compromisos de liderazgo
                    </div>
                </div>
                <div id="fl2ListaSeguimiento">
                    <div style="text-align:center;padding:20px;color:var(--fb-muted);font-size:.82rem;">
                        Cargando compromisos...
                    </div>
                </div>
            </div>
        </div>

        <!-- PASO 2: Objetivos expandibles con formulario inline -->
        <div id="fl2Paso2" style="display:none;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
                <button onclick="fl2IrPaso(1)" style="display:flex;align-items:center;gap:5px;
                        background:#f1f5f9;border:none;border-radius:8px;padding:7px 14px;
                        font-size:.8rem;cursor:pointer;color:#475569;font-weight:600;">
                    &larr; Volver
                </button>
                <div>
                    <div id="fl2CompLabel" style="font-size:.95rem;font-weight:800;color:#0058af;"></div>
                    <div style="font-size:.72rem;color:var(--fb-muted);">
                        Selecciona un objetivo, completa los detalles y haz clic en Asignar
                    </div>
                </div>
            </div>
            <div id="fl2GridObjs">
                <div style="padding:24px;text-align:center;color:var(--fb-muted);">Cargando objetivos...</div>
            </div>
            <div style="margin-top:18px;display:flex;justify-content:flex-end;gap:10px;">
                <button onclick="cerrarFl2Modal(true)" style="padding:10px 20px;border:1.5px solid #e2e8f0;
                        border-radius:10px;background:#fff;color:#64748b;font-size:.85rem;
                        font-weight:600;cursor:pointer;">Cerrar</button>
                <button id="fl2BtnAsignar" onclick="fl2Asignar()"
                        style="background:linear-gradient(135deg,#0058af,#0074e0);color:#fff;border:none;
                               border-radius:10px;padding:10px 28px;font-weight:700;font-size:.88rem;
                               cursor:pointer;box-shadow:0 4px 14px rgba(0,88,175,.35);
                               display:flex;align-items:center;gap:6px;">
                    &#10003; Asignar Objetivo
                </button>
            </div>
        </div>

    </div>
</div>
</div>

<!-- Stepper FL2 usa clases fb-step-* unificadas -->

<!-- ══════════════════════════════════
     MODAL DE FEEDBACK + SMART
══════════════════════════════════ -->
<div id="fbModalOverlay" class="fb-modal-overlay">
<div class="fb-modal">
    <div class="fb-modal-header">
        <!-- Fila superior: info + pill + cerrar -->
        <div class="fb-modal-header-top">
            <div style="position:relative;">
                <div class="fb-modal-eyebrow">Proceso 1 &mdash; Retroalimentación P1-P11</div>
                <div class="fb-modal-title" id="fbModalTitle">Colaborador</div>
                <div class="fb-modal-sub"   id="fbModalSub">Cargo</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <span id="fbObjPill" style="display:inline-flex;align-items:center;gap:5px;
                    padding:3px 12px;border-radius:20px;font-size:.72rem;font-weight:700;
                    background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25);">
                    0 / <?= $maxObjetivos ?> obj.
                </span>
                <button class="fb-modal-close" onclick="cerrarModal()">&#215;</button>
            </div>
        </div>
        <!-- Stepper integrado en el header -->
        <div class="fb-stepper" id="fbStepper" style="margin-bottom:0;">
            <div class="fb-step-wrap">
                <div class="fb-step-circle active" id="stepCircle1">1</div>
                <div class="fb-step-label active" id="stepLabel1">Feedback</div>
            </div>
            <div class="fb-step-connector" id="stepConn1"></div>
            <div class="fb-step-wrap">
                <div class="fb-step-circle pending" id="stepCircle2">2</div>
                <div class="fb-step-label pending" id="stepLabel2">Competencias</div>
            </div>
            <div class="fb-step-connector" id="stepConn2"></div>
            <div class="fb-step-wrap">
                <div class="fb-step-circle pending" id="stepCircle3">3</div>
                <div class="fb-step-label pending" id="stepLabel3">Objetivos SMART</div>
            </div>
            <div class="fb-step-connector" id="stepConn3"></div>
            <div class="fb-step-wrap">
                <div class="fb-step-circle pending" id="stepCircle4">4</div>
                <div class="fb-step-label pending" id="stepLabel4">Firma</div>
            </div>
        </div>
    </div>
    <div class="fb-modal-body">

        <!-- PASO 1: Registrar feedback -->
        <div id="fbPaso1">
            <div class="fb-section-label">Información de la reunión</div>
            <div class="fb-input-row">
                <div>
                    <label class="fb-label">· Fecha de la reunión</label>
                    <input type="date" id="fbFecha" class="fb-input"
                           value="<?= date('Y-m-d') ?>"
                           style="cursor:pointer;"
                           onclick="this.showPicker ? this.showPicker() : null">
                </div>
                <div>
                    <label class="fb-label">· Hora de la reunión</label>
                    <input type="time" id="fbHora" class="fb-input"
                           value="<?= date('H:i') ?>"
                           style="cursor:pointer;"
                           onclick="this.showPicker ? this.showPicker() : null">
                </div>
            </div>
            <div class="fb-input-full">
                <label class="fb-label">Colaborador</label>
                <input type="text" id="fbNombreColab" class="fb-input" readonly
                       style="background:#f8fafc;">
            </div>
            <div class="fb-input-full">
                <label class="fb-label">Observaciones de la reunión de feedback</label>
                <textarea id="fbObservacion" class="fb-textarea" rows="4"
                    placeholder="Describe los temas tratados, compromisos verbales, contexto de la reunión..."></textarea>
            </div>
            <!-- Acuerdos existentes -->
            <div id="fbAcuerdosExistentes" style="display:none;">
                <div class="fb-section-label" style="margin-top:8px;">Objetivos SMART ya asignados</div>
                <div id="fbListaAcuerdos"></div>
            </div>
        </div>

        <!-- PASO 2: Seleccionar competencia -->
        <div id="fbPaso2" style="display:none;">
            <div class="fb-section-label">Selecciona la competencia a trabajar</div>
            <div style="font-size:12px;color:var(--fb-muted);margin-bottom:12px;">
                Basado en las calificaciones de la evaluación del período actual.
            </div>
            <div id="fbCompGrid" class="fb-comp-grid">
                <div class="fb-loading">
                    <div class="fb-loading-spin"></div>
                    Cargando calificaciones...
                </div>
            </div>
        </div>

        <!-- PASO 3: Seleccionar objetivo -->
        <div id="fbPaso3" style="display:none;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                <button class="fb-btn fb-btn-outline fb-btn-sm" onclick="irPaso(2)">← Volver</button>
                <div>
                    <div class="fb-section-label" style="margin:0;">Objetivos SMART sugeridos</div>
                    <div style="font-size:11px;color:var(--fb-muted);" id="fbCompSelLabel"></div>
                </div>
            </div>
            <div id="fbObjList" class="fb-obj-list">
                <div class="fb-loading"><div class="fb-loading-spin"></div>Cargando...</div>
            </div>
            <!-- Campos del acuerdo SMART -->
            <div id="fbAjusteLider" style="display:none;margin-top:14px;padding:14px;background:#f0f9ff;border-radius:10px;border:1px solid #bae6fd;">
                <div class="fb-section-label" style="margin-bottom:10px;">Completa los campos del acuerdo</div>
                <div class="fb-input-full">
                    <label class="fb-label">Indicador</label>
                    <input type="text" id="fbIndicadorAjuste" class="fb-input" placeholder="">
                </div>
                <div class="fb-input-row">
                    <div>
                        <label class="fb-label">Meta</label>
                        <input type="text" id="fbMetaAjuste" class="fb-input" placeholder="">
                    </div>
                    <div>
                        <label class="fb-label">Plazo</label>
                        <input type="text" id="fbPlazoAjuste" class="fb-input" placeholder="">
                    </div>
                </div>
                <div class="fb-input-full">
                    <label class="fb-label">Evidencia</label>
                    <input type="text" id="fbEvidencia" class="fb-input" placeholder="">
                </div>
                <div class="fb-input-full">
                    <label class="fb-label">Apoyo del líder</label>
                    <textarea id="fbApoyo" class="fb-textarea" rows="2"
                        placeholder="¿Qué apoyo o recursos proveerás al colaborador?"></textarea>
                </div>
                <div class="fb-input-full">
                    <label class="fb-label">Seguimiento sugerido</label>
                    <input type="text" id="fbSeguimiento" class="fb-input" placeholder="">
                </div>
                <div class="fb-input-full">
                    <label class="fb-label">Compromiso final ajustado</label>
                    <textarea id="fbCompromisoFinal" class="fb-textarea" rows="2"
                        placeholder="Compromiso acordado en la sesión"></textarea>
                </div>
            </div>
        </div>

        <!-- PASO 4: Firma del colaborador -->
        <div id="fbPaso4" style="display:none;">
            <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;
                        padding:16px 18px;margin-bottom:16px;">
                <div style="font-size:.8rem;font-weight:700;color:#15803d;margin-bottom:4px;">
                    ✓ Objetivos SMART asignados correctamente
                </div>
                <div style="font-size:.78rem;color:#166534;">
                    Para completar el proceso, el colaborador debe firmar
                    el recibido del feedback con su usuario y contraseña.
                </div>
            </div>

            <!-- Firmas -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <!-- Firma del líder (ya registrada) -->
                <div style="background:#f8fafc;border:1.5px solid var(--fb-border);
                            border-radius:10px;padding:14px;text-align:center;">
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;
                                text-transform:uppercase;color:var(--fb-muted);margin-bottom:8px;">
                        Líder
                    </div>
                    <div style="font-size:1.4rem;margin-bottom:4px;">✎</div>
                    <div id="fbNombreLiderFirma" style="font-size:.82rem;font-weight:600;
                                                         color:var(--fb-text);"></div>
                    <div style="font-size:.72rem;color:#15803d;margin-top:4px;">
                        ✓ Feedback registrado
                    </div>
                </div>
                <!-- Firma del colaborador -->
                <div style="background:#f8fafc;border:1.5px solid var(--fb-border);
                            border-radius:10px;padding:14px;text-align:center;">
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;
                                text-transform:uppercase;color:var(--fb-muted);margin-bottom:8px;">
                        Colaborador
                    </div>
                    <div style="font-size:1.4rem;margin-bottom:4px;">·</div>
                    <div id="fbNombreColabFirma" style="font-size:.82rem;font-weight:600;
                                                          color:var(--fb-text);"></div>
                    <div id="fbEstadoFirma" style="font-size:.72rem;color:#d97706;margin-top:4px;">
                        · Pendiente de firma
                    </div>
                </div>
            </div>

            <!-- Formulario de firma -->
            <div id="fbFormFirma">
                <div class="fb-section-label">El colaborador ingresa sus credenciales</div>
                <div class="fb-input-row">
                    <div>
                        <label class="fb-label">🪪 Identificación</label>
                        <input type="text" id="fbFirmaCedula" class="fb-input"
                               placeholder="Número de identificación"
                               autocomplete="off">
                    </div>
                    <div>
                        <label class="fb-label">· Contraseña</label>
                        <input type="password" id="fbFirmaPassword" class="fb-input"
                               placeholder="Contraseña del sistema"
                               autocomplete="new-password">
                    </div>
                </div>
                <div style="font-size:.75rem;color:var(--fb-muted);margin-top:-6px;margin-bottom:12px;">
                    · El colaborador debe ingresar sus propias credenciales del sistema para confirmar recibido.
                </div>
            </div>

            <!-- Confirmación tras firma exitosa -->
            <div id="fbFirmaOk" style="display:none;text-align:center;padding:12px;">
                <div style="font-size:1.6rem;margin-bottom:6px;">✓</div>
                <div style="font-size:.95rem;font-weight:700;color:#15803d;">Proceso completado</div>
                <div style="font-size:.82rem;color:var(--fb-muted);margin-top:4px;">
                    El colaborador firmó el recibido del feedback exitosamente.
                </div>
            </div>

            <!-- Panel seguimiento -->
            <div id="fbPanelSeguimiento" style="display:none;margin-top:16px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                    <div style="width:4px;height:18px;background:#0058af;border-radius:2px;"></div>
                    <div style="font-weight:700;font-size:.85rem;color:#1e3a5f;">Seguimiento de compromisos</div>
                </div>
                <div id="fbListaSeguimiento">
                    <div class="fb-loading"><div class="fb-loading-spin"></div>Cargando...</div>
                </div>
            </div>
        </div>

    </div>
    <div class="fb-modal-footer" id="fbModalFooter">
        <button class="fb-btn fb-btn-outline" onclick="fbState.paso === 4 ? location.reload() : cerrarModal()">Cancelar</button>
        <button class="fb-btn fb-btn-primary" id="fbBtnAccion" onclick="accionModal()">
            Guardar feedback →
        </button>
    </div>
</div>
</div>



<!-- Mini-modal seguimiento -->
<div id="fbSeguimientoModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
     z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:24px;width:min(480px,95vw);
                box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <div style="font-weight:700;font-size:.95rem;color:#1e3a5f;margin-bottom:4px;" id="fbSegCompNombre"></div>
        <div style="font-size:.78rem;color:#64748b;margin-bottom:14px;" id="fbSegObjTexto"></div>
        <label style="font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Estado</label>
        <select id="fbSegEstado" style="width:100%;padding:8px 10px;border:1.5px solid #cbd5e1;
                border-radius:8px;font-size:.85rem;margin-bottom:12px;outline:none;">
            <option value="RESPONDIDO">Respondido — dejar como está</option>
            <option value="APROBADO">Aprobado — confirmar cumplimiento</option>
        </select>
        <label style="font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Comentario del líder</label>
        <textarea id="fbSegComentario" rows="3" style="width:100%;padding:8px 10px;border:1.5px solid #cbd5e1;
                  border-radius:8px;font-size:.85rem;resize:vertical;outline:none;box-sizing:border-box;"
                  placeholder="Observación, avance o retroalimentación..."></textarea>
        <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end;">
            <button onclick="cerrarSegModal()"
                    style="padding:8px 18px;border:1.5px solid #cbd5e1;border-radius:8px;
                           background:#fff;color:#64748b;font-size:.82rem;cursor:pointer;">Cancelar</button>
            <button onclick="guardarSeguimiento()"
                    style="padding:8px 18px;border:none;border-radius:8px;
                           background:#0058af;color:#fff;font-weight:600;font-size:.82rem;cursor:pointer;">Guardar</button>
        </div>
    </div>
</div>


<script>
const FB_APP_URL    = '<?= APP_URL ?>';
const FB_PERIODO    = <?= $periodoActivo ? json_encode(['IDPERIODO' => (int)$periodoActivo['IDPERIODO']]) : 'null' ?>;
const FB_MAX_OBJ    = <?= $maxObjetivos ?>;
const FB_COMP_NAMES = <?= json_encode($nombresComp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const FB_CAL_LABELS = <?= json_encode($calLabels) ?>;
const FB_CAL_COLORS = <?= json_encode($calColors) ?>;

let fbState = {
    paso:          1,
    idEmpleado:    null,
    nombreColab:   '',
    cargoColab:    '',
    tieneFeedback: false,
    idFeedback:    null,
    calificaciones: {},
    compSeleccionada: null,
    calSeleccionada:  null,
    objSeleccionado:  null,
    acuerdosCount:  0,
    acuerdos:       [],
    firmado:        0,
};

function abrirModalFeedback(idEmp, nombre, cargo, tieneFeedback, idFeedback, fecha, obs, totalAcuerdos, firmado) {
    fbState.idEmpleado    = idEmp;
    fbState.nombreColab   = nombre;
    fbState.cargoColab    = cargo;
    fbState.tieneFeedback = tieneFeedback == 1;
    fbState.idFeedback    = idFeedback;
    fbState.acuerdosCount = parseInt(totalAcuerdos) || 0;
    fbState.firmado       = parseInt(firmado) || 0;

    document.getElementById('fbModalTitle').textContent = nombre;
    document.getElementById('fbModalSub').textContent   = cargo;
    // Actualizar pill de objetivos
    const pill = document.getElementById('fbObjPill');
    if (pill) pill.textContent = fbState.acuerdosCount + ' / ' + FB_MAX_OBJ + ' obj.';
    document.getElementById('fbNombreColab').value      = nombre;

    if (fecha) {
        // fecha viene como "DD/MM/YYYY HH:MI" desde el modelo
        const [fechaPart, horaPart] = fecha.split(' ');
        const parts = fechaPart ? fechaPart.split('/') : [];
        if (parts.length === 3)
            document.getElementById('fbFecha').value = parts[2] + '-' + parts[1] + '-' + parts[0];
        if (horaPart)
            document.getElementById('fbHora').value = horaPart;
    }
    document.getElementById('fbObservacion').value = obs || '';

    // Cargar acuerdos existentes
    if (totalAcuerdos > 0) {
        cargarAcuerdosExistentes(idEmp);
    } else {
        document.getElementById('fbAcuerdosExistentes').style.display = 'none';
    }

    // Si ya está firmado → mostrar paso 4 completado
    if (fbState.firmado) {
        cargarAcuerdosExistentes(idEmp);
        const colabEl = document.getElementById('fbNombreColabFirma');
        if (colabEl) colabEl.textContent = nombre;
        document.getElementById('fbFormFirma').style.display  = 'none';
        document.getElementById('fbFirmaOk').style.display    = '';
        document.getElementById('fbEstadoFirma').textContent  = '✓ Firmado';
        document.getElementById('fbEstadoFirma').style.color  = '#15803d';
        document.getElementById('fbBtnAccion').textContent    = 'Cerrar';
        cargarPanelSeguimiento(idEmp);
        irPaso(4);
        // Marcar todos los círculos como completados (verde)
        for (let s = 1; s <= 4; s++) {
            const sc = document.getElementById('stepCircle' + s);
            const sl = document.getElementById('stepLabel'  + s);
            if (sc) { sc.className = 'fb-step-circle done'; sc.textContent = '✓'; }
            if (sl) { sl.className = 'fb-step-label'; sl.style.color = ''; }
            const conn = document.getElementById('stepConn' + s);
            if (conn) conn.className = 'fb-step-connector done';
        }
    } else if (fbState.tieneFeedback && fbState.acuerdosCount >= FB_MAX_OBJ) {
        // Tiene objetivos completos → ir a firma
        cargarAcuerdosExistentes(idEmp);
        const colabEl = document.getElementById('fbNombreColabFirma');
        if (colabEl) colabEl.textContent = nombre;
        document.getElementById('fbFirmaCedula').value   = '';
        document.getElementById('fbFirmaPassword').value = '';
        document.getElementById('fbFormFirma').style.display = '';
        document.getElementById('fbFirmaOk').style.display   = 'none';
        document.getElementById('fbEstadoFirma').textContent = '· Pendiente de firma';
        document.getElementById('fbEstadoFirma').style.color = '#d97706';
        irPaso(4);
    } else {
        irPaso(1);
    }
    document.getElementById('fbModalOverlay').classList.add('active');
}

function cerrarModal() {
    document.getElementById('fbModalOverlay').classList.remove('active');
    fbState = { paso:1, idEmpleado:null, nombreColab:'', cargoColab:'', tieneFeedback:false,
                idFeedback:null, calificaciones:{}, compSeleccionada:null,
                calSeleccionada:null, objSeleccionado:null, acuerdosCount:0, firmado:0 };
}

function irPaso(paso) {
    fbState.paso = paso;

    // Stepper
    for (let i = 1; i <= 4; i++) {
        const circle = document.getElementById('stepCircle' + i);
        const label  = document.getElementById('stepLabel'  + i);
        if (!circle) continue;
        if (i < paso) {
            circle.className = 'fb-step-circle done';
            circle.textContent = '✓';
            label.className = 'fb-step-label';
            label.style.color = '#15803d';
        } else if (i === paso) {
            circle.className = 'fb-step-circle active';
            circle.textContent = i;
            label.className = 'fb-step-label active';
            label.style.color = '';
        } else {
            circle.className = 'fb-step-circle pending';
            circle.textContent = i;
            label.className = 'fb-step-label pending';
            label.style.color = '';
        }
        if (i <= 3) {
            const conn = document.getElementById('stepConn' + i);
            if (conn) conn.className = 'fb-step-connector' + (i < paso ? ' done' : '');
        }
    }

    // Mostrar paso
    document.getElementById('fbPaso1').style.display = paso === 1 ? '' : 'none';
    document.getElementById('fbPaso2').style.display = paso === 2 ? '' : 'none';
    document.getElementById('fbPaso3').style.display = paso === 3 ? '' : 'none';
    document.getElementById('fbPaso4').style.display = paso === 4 ? '' : 'none';

    // Botón acción
    const btn = document.getElementById('fbBtnAccion');
    if (paso === 1) {
        btn.textContent = 'Guardar feedback →';
        btn.style.display = '';
    } else if (paso === 2) {
        btn.textContent = 'Seleccionar objetivo →';
        btn.style.display = '';
    } else if (paso === 3) {
        btn.textContent = '✓ Asignar objetivo';
        btn.style.display = '';
    } else {
        btn.textContent = fbState.firmado ? 'Cerrar' : '✎ Confirmar firma';
        btn.style.display = '';
    }

    // Cargar competencias en paso 2
    if (paso === 2 && Object.keys(fbState.calificaciones).length === 0) {
        cargarCalificaciones();
    }
}

function accionModal() {
    if (fbState.paso === 1) guardarFeedback();
    else if (fbState.paso === 2) {
        if (!fbState.compSeleccionada) {
            Swal.fire({ icon:'warning', title:'Selecciona una competencia', showConfirmButton:false, timer:1800 });
            return;
        }
        cargarObjetivos(fbState.compSeleccionada, fbState.calSeleccionada);
        irPaso(3);
    } else if (fbState.paso === 3) {
        asignarObjetivo();
    } else if (fbState.paso === 4) {
        if (fbState.firmado) {
            cerrarModal();
            location.reload();
        } else {
            firmarColaborador();
        }
    }
}

function firmarColaborador() {
    const cedula   = document.getElementById('fbFirmaCedula').value.trim();
    const password = document.getElementById('fbFirmaPassword').value.trim();
    if (!cedula || !password) {
        Swal.fire({ icon:'warning', title:'Ingresa cédula y contraseña', showConfirmButton:false, timer:1800 });
        return;
    }
    const btn = document.getElementById('fbBtnAccion');
    btn.disabled = true;
    btn.textContent = 'Verificando...';

    const body = new URLSearchParams({
        action:      'firmarFeedback',
        idEmpleado:  fbState.idEmpleado,
        cedula:      cedula,
        password:    password,
    });

    fetch(FB_APP_URL + 'feedback/', {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
        body: body.toString()
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        if (res.ok) {
            // SweetAlert de éxito — al confirmar cierra modal y recarga
            Swal.fire({
                icon: 'success',
                title: '¡Firma registrada!',
                html: 'El proceso de feedback ha sido completado exitosamente.<br>' +
                      '<span style="font-size:.85rem;color:#475569;">Tus compromisos han sido cargados en <strong>Mi Plan de Mejora</strong> del Colaborador.</span>',
                confirmButtonText: 'Continuar',
                confirmButtonColor: '#0058af',
                allowOutsideClick: false,
            }).then(() => {
                cerrarModal();
                location.reload();
            });
            // Actualizar estado visual del modal mientras aparece el alert
            document.getElementById('fbFormFirma').style.display = 'none';
            document.getElementById('fbFirmaOk').style.display = '';
            document.getElementById('fbEstadoFirma').textContent = '✓ Firmado';
            document.getElementById('fbEstadoFirma').style.color = '#15803d';
            // Marcar todos los pasos como completados
            for (let s = 1; s <= 4; s++) {
                const sc = document.getElementById('stepCircle' + s);
                const sl = document.getElementById('stepLabel'  + s);
                if (sc) { sc.className = 'fb-step-circle done'; sc.textContent = '✓'; }
                if (sl) { sl.className = 'fb-step-label'; sl.style.color = '#15803d'; }
            }
        } else {
            btn.textContent = '✎ Confirmar firma';
            Swal.fire({ icon:'error', title:'Firma no válida', text: res.msg || 'Credenciales incorrectas.', confirmButtonColor:'#0058af' });
            document.getElementById('fbFirmaPassword').value = '';
            document.getElementById('fbFirmaPassword').focus();
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = '✎ Confirmar firma';
        Swal.fire({ icon:'error', title:'Error de conexión', text:'Intenta nuevamente.', confirmButtonColor:'#0058af' });
    });
}

function guardarFeedback() {
    const fecha = document.getElementById('fbFecha').value;
    const hora  = document.getElementById('fbHora').value || '00:00';
    const obs   = document.getElementById('fbObservacion').value.trim();
    if (!fecha) {
        Swal.fire({ icon:'warning', title:'Ingresa la fecha de la reunión', showConfirmButton:false, timer:1800 });
        return;
    }

    // Convertir fecha+hora a DD/MM/YYYY HH:MI (formato esperado por TO_DATE Oracle)
    const parts = fecha.split('-');
    const fechaFmt = parts[2] + '/' + parts[1] + '/' + parts[0] + ' ' + hora;

    const btn = document.getElementById('fbBtnAccion');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch(FB_APP_URL + 'feedback/', {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
        body: 'action=registrarFeedback&idEmpleado=' + fbState.idEmpleado +
              '&fechaFeedback=' + encodeURIComponent(fechaFmt) +
              '&observacion='   + encodeURIComponent(obs)
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        if (res.ok) {
            fbState.tieneFeedback = true;
            if (res.idFeedback) fbState.idFeedback = res.idFeedback;

            if (fbState.acuerdosCount >= FB_MAX_OBJ) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Cita actualizada!',
                    text: 'Los cambios fueron guardados correctamente.',
                    confirmButtonColor: '#0058af',
                    confirmButtonText: 'Cerrar'
                }).then(() => { cerrarModal(); location.reload(); });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: '¡Cita registrada!',
                    html: 'La cita fue guardada correctamente.',
                    confirmButtonColor: '#0058af',
                    confirmButtonText: '◎ Asignar objetivos ahora',
                    showDenyButton: true,
                    denyButtonColor: '#64748b',
                    denyButtonText: '· Asignar después',
                    allowOutsideClick: false
                }).then(r => {
                    if (r.isConfirmed) {
                        irPaso(2);
                    } else {
                        cerrarModal();
                        location.reload();
                    }
                });
            }
        } else {
            btn.textContent = 'Guardar feedback →';
            Swal.fire({ icon:'error', title:'Error al guardar', text:'Intenta nuevamente.', confirmButtonColor:'#0058af' });
        }
    });
}

function cargarCalificaciones() {
    const grid = document.getElementById('fbCompGrid');
    grid.innerHTML = '<div class="fb-loading"><div class="fb-loading-spin"></div>Cargando calificaciones...</div>';

    fetch(FB_APP_URL + 'feedback/?action=getCalificaciones&idEmpleado=' + fbState.idEmpleado, {
        headers: { 'X-Requested-With':'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        fbState.calificaciones = data;
        renderCompetencias(data);
    });
}

function renderCompetencias(cals) {
    const grid = document.getElementById('fbCompGrid');
    const auto  = cals.auto  || {};
    const lider = cals.lider || {};

    // Mapa de texto a número
    const calTextToNum = {
        'Insuficiente': 1, 'Necesita Mejorar': 2, 'Requiere mejora': 2,
        'Aceptable': 3, 'Acorde': 4, 'Sobresaliente': 5
    };

    function getCalNum(val) {
        if (!val) return 0;
        if (!isNaN(parseInt(val))) return parseInt(val);
        return calTextToNum[val] || 0;
    }

    // Usar calificaciones del lider al colaborador (HUMEVALUACIONCOLABO)
    let html = '';
    // Competencias ya asignadas
    const yaAsignadas = new Set((fbState.acuerdos || []).map(a => parseInt(a.NUM_COMPETENCIA)));

    for (let i = 1; i <= 11; i++) {
        const calNum   = getCalNum(lider['PREGUNTA' + i]);
        if (!calNum) continue;
        const calLabel  = FB_CAL_LABELS[calNum] || String(lider['PREGUNTA' + i]);
        const colors    = FB_CAL_COLORS[calNum] || {bg:'#f1f5f9',color:'#64748b'};
        const nombre    = FB_COMP_NAMES[i] || 'Competencia ' + i;
        const asignada  = yaAsignadas.has(i);
        const badgeHTML = asignada
            ? '<span style="margin-left:auto;font-size:.7rem;padding:2px 8px;border-radius:20px;background:#dcfce7;color:#166534;font-weight:700;flex-shrink:0;">✓ Asignado</span>'
            : '';
        html += '<div class="fb-comp-item' + (asignada ? ' fb-comp-asignada' : '') + '" onclick="selCompetencia(' + i + ', \'' + calLabel + '\')" data-comp="' + i + '">' +
            '<div style="display:flex;align-items:center;gap:8px;width:100%;">' +
            '<div class="fb-comp-name">' + nombre + '</div>' +
            badgeHTML +
            '</div>' +
            '<span class="fb-comp-cal" style="background:' + colors.bg + ';color:' + colors.color + ';">' + calLabel + '</span>' +
            '</div>';
    }

    // P12-P16 van en pestaña separada "Feedback a Líderes" — pendiente Flujo 2

    if (!html) {
        html = '<div style="grid-column:1/-1;text-align:center;padding:24px;color:var(--fb-muted);font-size:13px;">No hay calificaciones disponibles para este colaborador en el período actual.</div>';
    }
    grid.innerHTML = html;
}

function selCompetencia(numComp, calLabel) {
    fbState.compSeleccionada = parseInt(numComp);
    fbState.calSeleccionada  = calLabel;
    document.querySelectorAll('.fb-comp-item').forEach(el => el.classList.remove('selected'));
    document.querySelector('[data-comp="' + numComp + '"]')?.classList.add('selected');
    // Actualizar botón para indicar selección
    const btn = document.getElementById('fbBtnAccion');
    btn.textContent = 'Ver objetivos de ' + (FB_COMP_NAMES[numComp] || 'competencia') + ' →';
}

function cargarObjetivos(numComp, calLabel) {
    const label = document.getElementById('fbCompSelLabel');
    label.textContent = (FB_COMP_NAMES[numComp] || '') + ' · ' + calLabel;

    const list = document.getElementById('fbObjList');
    list.innerHTML = '<div class="fb-loading"><div class="fb-loading-spin"></div>Cargando objetivos...</div>';
    document.getElementById('fbAjusteLider').style.display = 'none';
    fbState.objSeleccionado = null;

    fetch(FB_APP_URL + 'feedback/?action=getObjetivos&numComp=' + numComp + '&calificacion=' + encodeURIComponent(calLabel), {
        headers: { 'X-Requested-With':'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(objs => {
        if (!objs.length) {
            list.innerHTML = '<div style="text-align:center;padding:24px;color:var(--fb-muted);font-size:13px;">No hay objetivos SMART disponibles para esta competencia.</div>';
            return;
        }
        let html = '';
        objs.forEach(obj => {
            const ind   = (obj.INDICADOR  || '').replace(/'/g, "\'");
            const meta  = (obj.META       || '').replace(/'/g, "\'");
            const plazo = (obj.PLAZO      || '').replace(/'/g, "\'");
            const evid = (obj.EVIDENCIA   || '').replace(/'/g, "\'");
            const seg  = (obj.SEGUIMIENTO || '').replace(/'/g, "\'");
            html += `<div class="fb-obj-item" onclick="selObjetivo(${obj.IDOBJETIVO}, this, '${ind}', '${meta}', '${plazo}', '${evid}', '${seg}')" data-id="${obj.IDOBJETIVO}">
                <div class="fb-obj-modelo">${obj.MODELO}</div>
                <div class="fb-obj-texto">${obj.OBJETIVO}</div>
            </div>`;
        });
        list.innerHTML = html;
    });
}

function selObjetivo(idObj, el, indicador, meta, plazo, evidencia, seguimiento) {
    fbState.objSeleccionado = idObj;
    document.querySelectorAll('.fb-obj-item').forEach(e => e.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('fbIndicadorAjuste').value = indicador   || '';
    document.getElementById('fbMetaAjuste').value      = meta        || '';
    document.getElementById('fbPlazoAjuste').value     = plazo       || '';
    document.getElementById('fbEvidencia').value       = evidencia   || '';
    document.getElementById('fbSeguimiento').value     = seguimiento || '';
    document.getElementById('fbCompromisoFinal').value = '';
    document.getElementById('fbApoyo').value           = '';
    document.getElementById('fbAjusteLider').style.display = '';
}

function asignarObjetivo() {
    if (!fbState.objSeleccionado) {
        Swal.fire({ icon:'warning', title:'Selecciona un objetivo SMART', showConfirmButton:false, timer:1800 });
        return;
    }

    const apoyo = document.getElementById('fbApoyo').value.trim();
    if (!apoyo) {
        Swal.fire({ icon:'warning', title:'Campo requerido', text:'Indica el apoyo que brindarás al colaborador.', confirmButtonColor:'#0058af' });
        document.getElementById('fbApoyo').focus();
        return;
    }

    const btn = document.getElementById('fbBtnAccion');
    btn.disabled = true;
    btn.textContent = 'Asignando...';

    const body = new URLSearchParams({
        action:       'asignarObjetivo',
        idEmpleado:   fbState.idEmpleado,
        idObjetivo:   fbState.objSeleccionado,
        numComp:      fbState.compSeleccionada,
        calificacion: fbState.calSeleccionada,
        idFeedback:   fbState.idFeedback || 0,
        apoyo:        document.getElementById('fbApoyo').value,
        indicador:    document.getElementById('fbIndicadorAjuste').value,
        meta:         document.getElementById('fbMetaAjuste').value,
        plazo:        document.getElementById('fbPlazoAjuste').value,
        evidencia:    document.getElementById('fbEvidencia').value,
        seguimiento:  document.getElementById('fbSeguimiento').value,
        compromiso:   document.getElementById('fbCompromisoFinal').value,
    });

    fetch(FB_APP_URL + 'feedback/', {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
        body: body.toString()
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        if (res.ok) {
            fbState.acuerdosCount++;
            // Actualizar pill de objetivos en el header
            const pill = document.getElementById('fbObjPill');
            if (pill) pill.textContent = fbState.acuerdosCount + ' / ' + FB_MAX_OBJ + ' obj.';
            const quedan = FB_MAX_OBJ - fbState.acuerdosCount;
            if (quedan > 0) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Objetivo asignado!',
                    text: `Puedes asignar ${quedan} objetivo(s) más.`,
                    confirmButtonColor: '#0058af',
                    confirmButtonText: 'Asignar otro'
                }).then(() => {
                    fbState.compSeleccionada = null;
                    fbState.objSeleccionado  = null;
                    cargarAcuerdosExistentes(fbState.idEmpleado);
                    irPaso(2);
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: '¡Objetivos completados!',
                    html: 'Se asignaron los <strong>' + FB_MAX_OBJ + '</strong> objetivos SMART.<br>Ahora el colaborador debe firmar el recibido.',
                    confirmButtonColor: '#0058af',
                    confirmButtonText: '✎ Ir a firma',
                    allowOutsideClick: false
                }).then(() => {
                    const colabEl = document.getElementById('fbNombreColabFirma');
                    if (colabEl) colabEl.textContent = fbState.nombreColab;
                    document.getElementById('fbFirmaCedula').value = '';
                    document.getElementById('fbFirmaPassword').value = '';
                    document.getElementById('fbFormFirma').style.display = '';
                    document.getElementById('fbFirmaOk').style.display = 'none';
                    document.getElementById('fbEstadoFirma').textContent = '· Pendiente de firma';
                    document.getElementById('fbEstadoFirma').style.color = '#d97706';
                    irPaso(4);
                });
            }
        } else {
            btn.textContent = '✓ Asignar objetivo';
            Swal.fire({ icon:'error', title:'Error', text: res.msg || 'No se pudo asignar.', confirmButtonColor:'#0058af',
                didOpen: () => { document.querySelector('.swal2-container').style.zIndex = '99999'; } });
        }
    });
}

function cargarAcuerdosExistentes(idEmp) {
    fetch(FB_APP_URL + 'feedback/?action=getAcuerdos&idEmpleado=' + idEmp, {
        headers: { 'X-Requested-With':'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(acuerdos => {
        fbState.acuerdos = acuerdos; // Para marcar competencias ya asignadas
        // Re-renderizar competencias para actualizar badges de asignados
        if (fbState.calificaciones && Object.keys(fbState.calificaciones).length) {
            renderCompetencias(fbState.calificaciones);
        }
        if (!acuerdos.length) return;
        const lista = document.getElementById('fbListaAcuerdos');
        let html = '';
        acuerdos.forEach(a => {
            const comp  = FB_COMP_NAMES[a.NUM_COMPETENCIA] || 'Competencia ' + a.NUM_COMPETENCIA;
            const badge = a.ESTADO === 'APROBADO' ? 'fb-badge-done' : (a.ESTADO === 'RESPONDIDO' ? 'fb-badge-info' : 'fb-badge-pending');
            html += `<div class="fb-acuerdo-item">
                <div class="fb-acuerdo-comp">${comp}</div>
                <div class="fb-acuerdo-obj">${a.OBJETIVO}</div>
                <span class="fb-badge ${badge}" style="margin-top:6px;display:inline-flex;">${a.ESTADO}</span>
            </div>`;
        });
        lista.innerHTML = html;
        document.getElementById('fbAcuerdosExistentes').style.display = '';
    });
}

// ── Seguimiento de compromisos ────────────────────────────────────────────────
let fbSegState = { idAcuerdo: null };

function cargarPanelSeguimiento(idEmp) {
    document.getElementById('fbPanelSeguimiento').style.display = '';
    fetch(FB_APP_URL + 'feedback/?action=getAcuerdos&idEmpleado=' + idEmp, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(acuerdos => {
        const cont = document.getElementById('fbListaSeguimiento');
        if (!acuerdos.length) {
            cont.innerHTML = '<div style="text-align:center;color:#94a3b8;font-size:.8rem;padding:12px;">Sin compromisos registrados.</div>';
            return;
        }
        const estadoColors = {
            'PENDIENTE':  { bg:'#fef3c7', color:'#92400e', label:'Pendiente' },
            'RESPONDIDO': { bg:'#dbeafe', color:'#1e40af', label:'Respondido' },
            'APROBADO':   { bg:'#dcfce7', color:'#15803d', label:'Aprobado' },
        };
        let html = '';
        acuerdos.forEach(a => {
            const comp  = FB_COMP_NAMES[a.NUM_COMPETENCIA] || 'Competencia ' + a.NUM_COMPETENCIA;
            const ec    = estadoColors[a.ESTADO] || estadoColors['PENDIENTE'];
            const obj   = (a.OBJETIVO || '');
            const compE = comp.replace(/'/g, "\'");
            const objE  = obj.substring(0, 50).replace(/'/g, "\'");
            const comE  = (a.COMENTARIO_LIDER || '').replace(/'/g, "\'");
            const planAc= a.PLAN_ACCION || '';

            // Botón de acción — solo si está RESPONDIDO (lider puede aprobar)
            // Si PENDIENTE o APROBADO → sin acción del líder
            let accionHtml = '';
            if (a.ESTADO === 'RESPONDIDO') {
                accionHtml = `<button onclick="abrirSegModal(${a.IDACUERDO},'${compE}','${objE}','${a.ESTADO}','${comE}')"
                    style="padding:5px 12px;border:1.5px solid #0058af;border-radius:6px;background:#fff;
                           color:#0058af;font-size:.72rem;font-weight:600;cursor:pointer;margin-top:6px;">
                    ✓ Aprobar / Comentar
                </button>`;
            } else if (a.ESTADO === 'APROBADO') {
                accionHtml = '<span style="color:#15803d;font-size:.72rem;font-weight:600;">✓ Aprobado</span>';
            } else {
                accionHtml = '<span style="color:#94a3b8;font-size:.72rem;">Esperando respuesta</span>';
            }

            const ind    = a.INDICADOR         || '';
            const meta   = a.META              || '';
            const plaz   = a.PLAZO             || '';
            const evid   = a.EVIDENCIA         || '';
            const apoy   = a.APOYO_LIDER       || '';
            const seg    = a.SEGUIMIENTO       || '';
            const compAj = a.COMPROMISO_AJUSTADO || '';
            html += `<div style="border:1.5px solid #e2e8f0;border-radius:10px;padding:12px 14px;margin-bottom:10px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:.8rem;color:#1e3a5f;">${comp}</div>
                        <div style="font-size:.78rem;color:#475569;margin-top:2px;">${obj}</div>
                    </div>
                    <span style="background:${ec.bg};color:${ec.color};padding:3px 10px;border-radius:20px;
                                 font-weight:600;font-size:.72rem;white-space:nowrap;">${ec.label}</span>
                </div>
                ${(ind||meta||plaz||evid||apoy||seg) ? `<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:7px;padding:8px 12px;margin-top:8px;font-size:.77rem;color:#0369a1;line-height:1.6;">
                    ${ind  ? '<strong>Indicador:</strong> '           + ind  + '<br>' : ''}
                    ${meta ? '<strong>Meta:</strong> '                + meta + '<br>' : ''}
                    ${plaz ? '<strong>Plazo:</strong> '               + plaz + '<br>' : ''}
                    ${evid ? '<strong>Evidencia:</strong> '           + evid + '<br>' : ''}
                    ${apoy ? '<strong>Apoyo líder:</strong> '         + apoy + '<br>' : ''}
                    ${seg  ? '<strong>Seguimiento sugerido:</strong> ' + seg          : ''}
                </div>` : ''}
                ${compAj ? `<div style="background:#fefce8;border:1px solid #fde68a;border-radius:7px;padding:8px 12px;margin-top:6px;font-size:.77rem;color:#92400e;line-height:1.5;">
                    <strong>Compromiso ajustado:</strong> ${compAj}
                </div>` : ''}
                ${planAc ? `<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;
                                        padding:8px 12px;margin-top:8px;font-size:.78rem;color:#166534;">
                    <strong>Respuesta del colaborador:</strong><br>${planAc}
                </div>` : ''}
                ${a.COMENTARIO_LIDER ? `<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:7px;
                                              padding:7px 12px;margin-top:6px;font-size:.75rem;color:#1e40af;">
                    💬 <strong>Tu comentario:</strong> ${a.COMENTARIO_LIDER}
                </div>` : ''}
                <div style="margin-top:6px;">${accionHtml}</div>
            </div>`;
        });
        cont.innerHTML = html;
    });
}

function abrirSegModal(idAcuerdo, comp, obj, estado, comentario) {
    fbSegState.idAcuerdo = idAcuerdo;
    document.getElementById('fbSegCompNombre').textContent = comp;
    document.getElementById('fbSegObjTexto').textContent   = obj;
    document.getElementById('fbSegEstado').value           = estado;
    document.getElementById('fbSegComentario').value       = comentario;
    document.getElementById('fbSeguimientoModal').dataset.flujo = '1'; // Proceso 1
    document.getElementById('fbSeguimientoModal').style.display = 'flex';
}

function cerrarSegModal() {
    document.getElementById('fbSeguimientoModal').style.display = 'none';
    document.getElementById('fbSeguimientoModal').dataset.flujo = '';
    fbSegState.idAcuerdo = null;
}

function guardarSeguimiento() {
    const esFlujo2  = document.getElementById('fbSeguimientoModal').dataset.flujo === '2';
    // Leer idAcuerdo de la variable correcta según el flujo activo
    const idAcuerdo = esFlujo2 ? fl2SegState.idAcuerdo : fbSegState.idAcuerdo;
    if (!idAcuerdo) return;
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    const body = new URLSearchParams({
        action:     'actualizarSeguimiento',
        idAcuerdo:  idAcuerdo,
        estado:     document.getElementById('fbSegEstado').value,
        comentario: document.getElementById('fbSegComentario').value,
    });
    fetch(FB_APP_URL + 'feedback/', {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
        body: body.toString()
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.textContent = 'Guardar';
        if (res.ok) {
            cerrarSegModal();
            if (esFlujo2) {
                fl2CargarSeguimiento(); // Recarga panel Flujo 2 (director → líder)
            } else {
                cargarPanelSeguimiento(fbState.idEmpleado); // Recarga panel Proceso 1
            }
            Swal.fire({ icon:'success', title:'Seguimiento guardado', showConfirmButton:false, timer:1600 });
        } else {
            Swal.fire({ icon:'error', title:'Error al guardar', showConfirmButton:false, timer:1800 });
        }
    });
}


// ══════════════════════════════════════════════════════
// FLUJO 2 — Feedback director a líderes (P12-P16)
// ══════════════════════════════════════════════════════
const FL2_COMP_NAMES = {
    12: 'Propósito', 13: 'Colaboración', 14: 'Consistencia',
    15: 'Adaptabilidad', 16: 'Amor'
};
const FL2_MAX_OBJ = 3;

let fl2State = {
    idLider: null, nombre: '', cargo: '', idFeedback: 0,
    cals: {}, compSel: null, calSel: '',
    objSel: null, tieneFeedback: false,
    acuerdosCount: 0, acuerdosComp: new Set(),
};

// ── Abrir modal ──────────────────────────────────────────────────────────
function abrirFeedbackLider(idLider, nombre, cargo, idFeedback, irAFirma) {
    idFeedback = idFeedback || 0;
    fl2State = { idLider, nombre, cargo, idFeedback,
                 cals: {}, compSel: null, calSel: '',
                 objSel: null, acuerdosCount: 0, acuerdosComp: new Set(),
                 tieneFeedback: idFeedback > 0 };
    document.getElementById('fl2NombreLider').textContent = nombre;
    document.getElementById('fl2CargoLider').textContent  = cargo;
    document.getElementById('fl2ModalOverlay').style.display = 'flex';
    fl2ActualizarContador(0);

    if (idFeedback <= 0) {
        // Sin feedback → paso registro
        fl2IrPasoFeedback();
    } else if (irAFirma) {
        // Objetivos completos → ir directo a firma
        fl2CargarDatosYFirmar();
    } else {
        // Tiene feedback → cargar datos (competencias/objetivos)
        fl2CargarDatos();
    }
}

function fl2CargarDatosYFirmar() {
    fetch(FB_APP_URL + 'feedback/?action=getAcuerdos&idEmpleado=' + fl2State.idLider + '&flujo=2', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(acuerdos => {
        fl2State.acuerdosCount = acuerdos.length;
        fl2ActualizarContador(acuerdos.length);
        document.getElementById('fl2NombreDirectorFirma').textContent = 'Director';
        document.getElementById('fl2NombreLiderFirmaPanel').textContent = fl2State.nombre;
        document.getElementById('fl2FirmaCedula').value   = '';
        document.getElementById('fl2FirmaPassword').value = '';
        const yaFirmado = acuerdos.length > 0 &&
            acuerdos.some(a => ['PENDIENTE','RESPONDIDO','APROBADO'].includes(a.ESTADO));
        if (yaFirmado) {
            document.getElementById('fl2FormFirma').style.display = 'none';
            document.getElementById('fl2FirmaOk').style.display   = '';
            document.getElementById('fl2EstadoFirmaLider').textContent = '\u2713 Firmado';
            document.getElementById('fl2EstadoFirmaLider').style.color = '#15803d';
            fl2CargarSeguimiento();
        } else {
            document.getElementById('fl2FormFirma').style.display  = '';
            document.getElementById('fl2FirmaOk').style.display    = 'none';
            document.getElementById('fl2EstadoFirmaLider').textContent = '\u00b7 Pendiente de firma';
            document.getElementById('fl2EstadoFirmaLider').style.color = '#d97706';
        }
        fl2IrPaso('firma');
        if (yaFirmado) {
            for (let s = 1; s <= 4; s++) {
                const sc = document.getElementById('fl2StepCircle' + s);
                const sl = document.getElementById('fl2StepLabel'  + s);
                if (sc) { sc.className = 'fb-step-circle done'; sc.textContent = '\u2713'; }
                if (sl) { sl.style.color = ''; }
                const conn = document.getElementById('fl2StepConn' + s);
                if (conn) conn.className = 'fb-step-connector done';
            }
        }
    });
}

function fl2CargarSeguimiento() {
    const panel = document.getElementById('fl2PanelSeguimiento');
    const lista  = document.getElementById('fl2ListaSeguimiento');
    if (!panel || !lista) return;
    panel.style.display = '';
    lista.innerHTML = '<div style="padding:16px;text-align:center;color:var(--fb-muted);font-size:.82rem;">Cargando...</div>';
    fetch(FB_APP_URL + 'feedback/?action=getAcuerdos&idEmpleado=' + fl2State.idLider + '&flujo=2', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(acuerdos => {
        if (!acuerdos.length) {
            lista.innerHTML = '<div style="text-align:center;color:#94a3b8;font-size:.8rem;padding:12px;">Sin compromisos.</div>';
            return;
        }
        const EC = {
            'PENDIENTE_FIRMA': {bg:'#f1f5f9',color:'#64748b',label:'Por firmar'},
            'PENDIENTE':  {bg:'#fef3c7',color:'#92400e',label:'Pendiente'},
            'RESPONDIDO': {bg:'#dbeafe',color:'#1e40af',label:'Respondido'},
            'APROBADO':   {bg:'#dcfce7',color:'#15803d',label:'Aprobado'},
        };
        const COMP = {12:'Prop\u00f3sito',13:'Colaboraci\u00f3n',14:'Consistencia',15:'Adaptabilidad',16:'Amor'};
        let html = '';
        acuerdos.forEach(a => {
            const comp = COMP[parseInt(a.NUM_COMPETENCIA)] || 'P'+a.NUM_COMPETENCIA;
            const ec   = EC[a.ESTADO] || EC['PENDIENTE'];
            const obj    = a.OBJETIVO           || '';
            const plan   = a.PLAN_ACCION        || '';
            const com    = a.COMENTARIO_LIDER   || '';
            const ind    = a.INDICADOR          || '';
            const meta   = a.META               || '';
            const plaz   = a.PLAZO              || '';
            const evid   = a.EVIDENCIA          || '';
            const apoy   = a.APOYO_LIDER        || '';
            const seg    = a.SEGUIMIENTO        || '';
            const compAj = a.COMPROMISO_AJUSTADO || '';
            const compE = comp.replace(/'/g,"\\'");
            const objE  = obj.substring(0,50).replace(/'/g,"\\'");
            const comE  = com.replace(/'/g,"\\'");
            let accionHtml = '';
            if (a.ESTADO === 'RESPONDIDO') {
                accionHtml = `<button onclick="fl2AbrirSegModal(${a.IDACUERDO},'${compE}','${objE}','${a.ESTADO}','${comE}')"
                    style="padding:5px 12px;border:1.5px solid #0058af;border-radius:6px;background:#fff;
                           color:#0058af;font-size:.72rem;font-weight:600;cursor:pointer;margin-top:6px;">
                    \u2713 Aprobar / Comentar
                </button>`;
            } else if (a.ESTADO === 'APROBADO') {
                accionHtml = '<span style="color:#15803d;font-size:.72rem;font-weight:600;">\u2713 Aprobado</span>';
            } else {
                accionHtml = '<span style="color:#94a3b8;font-size:.72rem;">Esperando respuesta</span>';
            }
            html += `<div style="border:1.5px solid #e2e8f0;border-radius:10px;padding:12px 14px;margin-bottom:10px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:.78rem;color:#0058af;">${comp}</div>
                        <div style="font-size:.78rem;color:#475569;margin-top:2px;">${obj}</div>
                    </div>
                    <span style="background:${ec.bg};color:${ec.color};padding:3px 10px;
                                 border-radius:20px;font-weight:600;font-size:.72rem;">${ec.label}</span>
                </div>
                ${(ind||meta||plaz||evid||apoy||seg) ? `<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:7px;
                    padding:7px 12px;margin-top:8px;font-size:.77rem;color:#0369a1;line-height:1.6;">
                    ${ind  ? '<strong>Indicador:</strong> '            + ind  + '<br>' : ''}
                    ${meta ? '<strong>Meta:</strong> '                 + meta + '<br>' : ''}
                    ${plaz ? '<strong>Plazo:</strong> '                + plaz + '<br>' : ''}
                    ${evid ? '<strong>Evidencia:</strong> '            + evid + '<br>' : ''}
                    ${apoy ? '<strong>Apoyo director:</strong> '       + apoy + '<br>' : ''}
                    ${seg  ? '<strong>Seguimiento sugerido:</strong> ' + seg           : ''}
                </div>` : ''}
                ${compAj ? `<div style="background:#fefce8;border:1px solid #fde68a;border-radius:7px;
                    padding:7px 12px;margin-top:6px;font-size:.77rem;color:#92400e;line-height:1.5;">
                    <strong>Compromiso ajustado:</strong> ${compAj}
                </div>` : ''}
                ${plan ? `<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;
                    padding:8px 12px;margin-top:8px;font-size:.78rem;color:#166534;">
                    <strong>Respuesta del l\u00edder:</strong><br>${plan}
                </div>` : ''}
                ${com ? `<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:7px;
                    padding:7px 12px;margin-top:6px;font-size:.75rem;color:#1e40af;">
                    \ud83d\udcac <strong>Tu comentario:</strong> ${com}
                </div>` : ''}
                <div style="margin-top:6px;">${accionHtml}</div>
            </div>`;
        });
        lista.innerHTML = html;
    });
}

let fl2SegState = { idAcuerdo: null };

function fl2AbrirSegModal(idAcuerdo, comp, obj, estado, comentario) {
    fl2SegState.idAcuerdo = idAcuerdo;
    document.getElementById('fbSegCompNombre').textContent = comp;
    document.getElementById('fbSegObjTexto').textContent   = obj;
    // Respetar el estado actual, no forzar APROBADO
    const sel = document.getElementById('fbSegEstado');
    sel.value = (estado === 'RESPONDIDO' || estado === 'APROBADO') ? estado : 'RESPONDIDO';
    document.getElementById('fbSegComentario').value = comentario || '';
    // Marcar flujo 2 para que guardarSeguimiento recargue el panel correcto
    document.getElementById('fbSeguimientoModal').dataset.flujo = '2';
    document.getElementById('fbSeguimientoModal').style.display = 'flex';
}

function fl2IrPasoFeedback() {
    fl2IrPaso('feedback'); // muestra fl2PasoFeedback, oculta el resto, actualiza stepper
    // Limpiar campos
    const hoy = new Date();
    document.getElementById('fl2FbFecha').value = hoy.toISOString().split('T')[0];
    document.getElementById('fl2FbHora').value  = hoy.toTimeString().slice(0,5);
    document.getElementById('fl2FbObs').value   = '';
}

function fl2GuardarFeedback() {
    const fecha   = document.getElementById('fl2FbFecha').value;
    const hora    = document.getElementById('fl2FbHora').value || '00:00';
    if (!fecha) {
        Swal.fire({ icon:'warning', title:'Ingresa la fecha', showConfirmButton:false, timer:1600 });
        return;
    }
    const parts   = fecha.split('-');
    const fechaFmt = parts[2] + '/' + parts[1] + '/' + parts[0] + ' ' + hora;
    const btn = document.getElementById('fl2BtnGuardarFb');
    btn.disabled = true; btn.textContent = 'Guardando...';

    fetch(FB_APP_URL + 'feedback/', {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
        body: new URLSearchParams({
            action: 'registrarFeedbackLider',
            idLider: fl2State.idLider,
            fechaFeedback: fechaFmt,
            observacion: document.getElementById('fl2FbObs').value
        }).toString()
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false; btn.textContent = 'Guardar y continuar →';
        if (res.ok) {
            fl2State.tieneFeedback = true;
            if (res.idFeedback) fl2State.idFeedback = res.idFeedback;
            Swal.fire({
                icon: 'success', title: '¡Feedback registrado!',
                html: 'La reunión fue registrada.<br>Ahora asigna los objetivos de liderazgo.',
                confirmButtonColor: '#0058af', confirmButtonText: 'Asignar objetivos',
                showDenyButton: true, denyButtonText: 'Después', denyButtonColor: '#64748b',
                allowOutsideClick: false,
                didOpen: () => { document.querySelector('.swal2-container').style.zIndex = '99999'; }
            }).then(r => {
                if (r.isConfirmed) fl2CargarDatos();
                else cerrarFl2Modal();
            });
        } else {
            Swal.fire({ icon:'error', title:'Error al guardar', confirmButtonColor:'#0058af',
                didOpen: () => { document.querySelector('.swal2-container').style.zIndex = '99999'; } });
        }
    });
}

function fl2CargarDatos() {
    document.getElementById('fl2PasoFeedback').style.display = 'none';
    fl2IrPaso(1);
    document.getElementById('fl2GridComps').innerHTML =
        '<div style="padding:32px;text-align:center;color:var(--fb-muted);">' +
        '<div style="width:28px;height:28px;border:3px solid #e2e8f0;border-top-color:#0058af;' +
        'border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 10px;"></div>' +
        'Cargando calificaciones...</div>';

    Promise.all([
        fetch(FB_APP_URL + 'feedback/?action=getCalificacionesLider&idLider=' + fl2State.idLider, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()),
        fetch(FB_APP_URL + 'feedback/?action=getAcuerdos&idEmpleado=' + fl2State.idLider + '&flujo=2', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json())
    ]).then(([calsData, acuerdos]) => {
        fl2State.cals = calsData.detalle || {};
        fl2State.acuerdosCount = acuerdos.length;
        fl2State.acuerdosComp  = new Set(acuerdos.map(a => parseInt(a.NUM_COMPETENCIA)));
        fl2ActualizarContador(acuerdos.length);
        if (!Object.keys(fl2State.cals).length) {
            document.getElementById('fl2GridComps').innerHTML =
                '<div style="text-align:center;padding:36px;color:var(--fb-muted);">' +
                '<div style="font-size:2rem;margin-bottom:10px;">&#9888;</div>' +
                '<div style="font-weight:700;font-size:.92rem;color:#1e3a5f;">Sin calificaciones de liderazgo</div>' +
                '<div style="font-size:.82rem;margin-top:8px;max-width:320px;margin:8px auto 0;line-height:1.5;color:#64748b;">' +
                'Los colaboradores de este líder aún no han completado la evaluación de competencias P12-P16.</div>' +
                '</div>';
            return;
        }
        fl2RenderCompetencias();
    });
}

function fl2Firmar() {
    const cedula   = document.getElementById('fl2FirmaCedula').value.trim();
    const password = document.getElementById('fl2FirmaPassword').value.trim();
    if (!cedula || !password) {
        Swal.fire({ icon:'warning', title:'Ingresa cédula y contraseña',
            showConfirmButton:false, timer:1800,
            didOpen: () => { document.querySelector('.swal2-container').style.zIndex = '99999'; } });
        return;
    }
    const btn = document.getElementById('fl2BtnFirmar');
    btn.disabled = true; btn.textContent = 'Verificando...';

    fetch(FB_APP_URL + 'feedback/', {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
        body: new URLSearchParams({
            action:   'firmarFeedbackLider',
            idLider:  fl2State.idLider,
            cedula:   cedula,
            password: password
        }).toString()
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        if (res.ok) {
            Swal.fire({
                icon:'success', title:'¡Firma registrada!',
                html: 'El proceso de feedback de liderazgo ha sido completado.<br>' +
                      '<span style="font-size:.85rem;color:#475569;">Los objetivos aparecerán en el Plan de Mejora del líder.</span>',
                confirmButtonText:'Continuar', confirmButtonColor:'#0058af',
                allowOutsideClick: false,
                didOpen: () => { document.querySelector('.swal2-container').style.zIndex = '99999'; }
            }).then(() => { cerrarFl2Modal(true); });
            document.getElementById('fl2FormFirma').style.display = 'none';
            document.getElementById('fl2FirmaOk').style.display = '';
            document.getElementById('fl2EstadoFirmaLider').textContent = '✓ Firmado';
            document.getElementById('fl2EstadoFirmaLider').style.color = '#15803d';
            fl2CargarSeguimiento();
            for (let s = 1; s <= 4; s++) {
                const sc = document.getElementById('fl2StepCircle' + s);
                const sl = document.getElementById('fl2StepLabel'  + s);
                if (sc) { sc.className = 'fb-step-circle done'; sc.textContent = '\u2713'; }
                if (sl) { sl.style.color = ''; }
                const conn = document.getElementById('fl2StepConn' + s);
                if (conn) conn.className = 'fb-step-connector done';
            }
        } else {
            btn.textContent = '✂ Confirmar firma';
            Swal.fire({ icon:'error', title:'Firma no válida', text: res.msg || 'Credenciales incorrectas.',
                confirmButtonColor:'#0058af',
                didOpen: () => { document.querySelector('.swal2-container').style.zIndex = '99999'; } });
            document.getElementById('fl2FirmaPassword').value = '';
        }
    });
}

function cerrarFl2Modal(recargar) {
    document.getElementById('fl2ModalOverlay').style.display = 'none';
    if (recargar || fl2State.tieneFeedback) location.reload();
}

function fbEscapeJs(text) {
    return String(text).replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"');
}

// ── Stepper ──────────────────────────────────────────────────────────────
function fl2IrPaso(p) {
    fl2State.paso = p;
    // p: 'feedback'=paso1, 1=Competencias, 2=Objetivos, 'firma'=paso4
    document.getElementById('fl2PasoFeedback').style.display = p === 'feedback' ? 'block' : 'none';
    document.getElementById('fl2Paso1').style.display        = p === 1           ? 'block' : 'none';
    document.getElementById('fl2Paso2').style.display        = p === 2           ? 'block' : 'none';
    document.getElementById('fl2PasoFirma').style.display    = p === 'firma'     ? 'block' : 'none';

    // Mapeo de paso lógico a número visual del stepper
    // feedback(antes de abrir) = 1, p=1(Competencias) = 2, p=2(Objetivos) = 3, firma = 4
    const stepActual = p === 'feedback' ? 1 : p === 1 ? 2 : p === 2 ? 3 : 4;

    for (let i = 1; i <= 4; i++) {
        const circle = document.getElementById('fl2StepCircle' + i);
        const label  = document.getElementById('fl2StepLabel'  + i);
        if (!circle) continue;
        if (i < stepActual) {
            circle.className   = 'fb-step-circle done';
            circle.textContent = '✓';
            label.className    = 'fb-step-label';
            label.style.color  = 'rgba(255,255,255,0.8)';
        } else if (i === stepActual) {
            circle.className   = 'fb-step-circle active';
            circle.textContent = i;
            label.className    = 'fb-step-label active';
            label.style.color  = '#fff';
        } else {
            circle.className   = 'fb-step-circle pending';
            circle.textContent = i;
            label.className    = 'fb-step-label pending';
            label.style.color  = 'rgba(255,255,255,0.4)';
        }
        if (i <= 3) {
            const conn = document.getElementById('fl2StepConn' + i);
            if (conn) conn.className = 'fb-step-connector' + (i < stepActual ? ' done' : '');
        }
    }
}

// ── Contador ─────────────────────────────────────────────────────────────
function fl2ActualizarContador(n) {
    const pill = document.getElementById('fl2CounterPill');
    if (!pill) return;
    pill.textContent = n + ' / ' + FL2_MAX_OBJ + ' obj.';
    pill.className   = 'fl2-counter-pill' + (n >= FL2_MAX_OBJ ? ' lleno' : '');
}

// ── Render competencias ───────────────────────────────────────────────────
function fl2RenderCompetencias() {
    const grid = document.getElementById('fl2GridComps');
    if (!grid) return;
    const cals = fl2State.cals;
    const CAL_COLORS = {
        1:{bg:'#fee2e2',color:'#991b1b'}, 2:{bg:'#fef3c7',color:'#92400e'},
        3:{bg:'#dbeafe',color:'#1e40af'}, 4:{bg:'#d1fae5',color:'#065f46'},
        5:{bg:'#ede9fe',color:'#5b21b6'}
    };
    const CAL_LABELS = {1:'Insuficiente',2:'Requiere mejora',3:'Aceptable',4:'Acorde',5:'Sobresaliente'};
    let html = '';
    const maximo = fl2State.acuerdosCount >= FL2_MAX_OBJ;

    for (let p = 12; p <= 16; p++) {
        if (!cals[p]) continue;
        const val      = cals[p].VALOR;
        const calLabel = CAL_LABELS[val] || 'Valor ' + val;
        const colors   = CAL_COLORS[val] || {bg:'#f1f5f9',color:'#64748b'};
        const nombre   = FL2_COMP_NAMES[p] || 'P' + p;
        const asignada = fl2State.acuerdosComp.has(p);
        const bloqueada = maximo && !asignada;

        html += `<div class="fl2-comp-row${asignada ? ' asignada' : ''}"
            ${!asignada && !bloqueada ? `data-comp="${p}" data-cal="${fbEscapeJs(calLabel)}"` : ''}
            style="${bloqueada ? 'opacity:.45;cursor:not-allowed;' : ''}">
            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                <div style="width:36px;height:36px;border-radius:10px;background:${asignada ? '#dcfce7' : '#dbeafe'};
                            display:flex;align-items:center;justify-content:center;font-size:.72rem;
                            font-weight:800;color:${asignada ? '#15803d' : '#1e40af'};flex-shrink:0;">
                    P${p}
                </div>
                <div>
                    <div style="font-weight:700;font-size:.88rem;color:#1e293b;">${nombre}</div>
                    <div style="font-size:.72rem;color:var(--fb-muted);">Competencia de liderazgo</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <span style="background:${colors.bg};color:${colors.color};padding:3px 12px;
                             border-radius:20px;font-size:.72rem;font-weight:700;">${calLabel}</span>
                ${asignada
                    ? '<span style="background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;">&#10003; Asignado</span>'
                    : bloqueada
                        ? '<span style="font-size:.72rem;color:#94a3b8;">Máx. alcanzado</span>'
                        : '<span style="color:#0058af;font-size:.78rem;font-weight:600;">Ver objetivos &rarr;</span>'
                }
            </div>
        </div>`;
    }
    grid.innerHTML = html || '<div style="padding:24px;text-align:center;color:var(--fb-muted);">Sin competencias evaluadas.</div>';
    // Eventos click solo en las no asignadas
    grid.querySelectorAll('.fl2-comp-row[data-comp]').forEach(row => {
        row.addEventListener('click', () => {
            fl2SelCompetencia(parseInt(row.dataset.comp), row.dataset.cal);
        });
    });
}

// ── Seleccionar competencia ───────────────────────────────────────────────
function fl2SelCompetencia(numComp, calLabel) {
    fl2State.compSel = numComp;
    fl2State.calSel  = calLabel;
    fl2State.objSel  = null;
    fl2State.objData = null;
    document.getElementById('fl2CompLabel').textContent =
        (FL2_COMP_NAMES[numComp] || 'P' + numComp) + ' · ' + calLabel;
    fl2IrPaso(2);
    const grid = document.getElementById('fl2GridObjs');
    grid.innerHTML = '<div style="padding:24px;text-align:center;color:var(--fb-muted);">Cargando objetivos...</div>';
    fetch(FB_APP_URL + 'feedback/?action=getObjetivos&numComp=' + numComp + '&calificacion=' + encodeURIComponent(calLabel), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(objs => {
        if (!objs.length) {
            grid.innerHTML = '<div style="padding:24px;text-align:center;color:var(--fb-muted);">No hay objetivos disponibles para esta calificación.</div>';
            return;
        }
        let h = '';
        objs.forEach((obj, idx) => {
            h += `<div class="fl2-obj-wrap" id="fl2Obj_${obj.IDOBJETIVO}">
                <div class="fl2-obj-head" onclick="fl2ToggleObj(${obj.IDOBJETIVO}, this)">
                    <div class="fl2-obj-toggle" id="fl2ObjDot_${obj.IDOBJETIVO}"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.7rem;font-weight:700;color:#0058af;text-transform:uppercase;
                                    letter-spacing:.06em;margin-bottom:3px;">${obj.MODELO}</div>
                        <div style="font-size:.83rem;color:#1e293b;line-height:1.5;">${obj.OBJETIVO}</div>
                    </div>
                    <div style="font-size:.8rem;color:#94a3b8;flex-shrink:0;">&#9660;</div>
                </div>
                <div class="fl2-obj-body" id="fl2ObjBody_${obj.IDOBJETIVO}">
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;
                                padding:10px 14px;margin-bottom:14px;font-size:.78rem;color:#0369a1;line-height:1.6;">
                        ${obj.INDICADOR ? '<strong>Indicador sugerido:</strong> ' + obj.INDICADOR + '<br>' : ''}
                        ${obj.META      ? '<strong>Meta sugerida:</strong> '      + obj.META      + '<br>' : ''}
                        ${obj.PLAZO     ? '<strong>Plazo sugerido:</strong> '     + obj.PLAZO             : ''}
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div>
                            <label class="fl2-field-label">Indicador</label>
                            <input class="fl2-input" id="fl2Ind_${obj.IDOBJETIVO}" type="text"
                                   value="${fbEscapeJs(obj.INDICADOR||'')}" placeholder="¿Cómo se medirá el avance?">
                        </div>
                        <div>
                            <label class="fl2-field-label">Meta</label>
                            <input class="fl2-input" id="fl2Met_${obj.IDOBJETIVO}" type="text"
                                   value="${fbEscapeJs(obj.META||'')}" placeholder="Resultado esperado">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div>
                            <label class="fl2-field-label">Plazo</label>
                            <input class="fl2-input" id="fl2Plz_${obj.IDOBJETIVO}" type="text"
                                   value="${fbEscapeJs(obj.PLAZO||'')}" placeholder="Ej: 12 meses">
                        </div>
                        <div>
                            <label class="fl2-field-label">Evidencia</label>
                            <input class="fl2-input" id="fl2Evi_${obj.IDOBJETIVO}" type="text"
                                   value="${fbEscapeJs(obj.EVIDENCIA||'')}" placeholder="¿Cómo se demostrará?">
                        </div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label class="fl2-field-label">Apoyo del director</label>
                        <textarea class="fl2-input" id="fl2Apo_${obj.IDOBJETIVO}" rows="2"
                                  style="resize:vertical;"
                                  placeholder="¿Qué recursos o apoyo brindarás al líder?"></textarea>
                    </div>
                    <div style="margin-bottom:4px;">
                        <label class="fl2-field-label">Compromiso ajustado</label>
                        <textarea class="fl2-input" id="fl2Com_${obj.IDOBJETIVO}" rows="2"
                                  style="resize:vertical;"
                                  placeholder="Compromiso final acordado en la reunión de feedback..."></textarea>
                    </div>
                </div>
            </div>`;
        });
        grid.innerHTML = h;
    });
}

// ── Toggle objetivo expandible ────────────────────────────────────────────
function fl2ToggleObj(idObj, headEl) {
    // Seleccionar este objetivo
    document.querySelectorAll('.fl2-obj-wrap').forEach(w => w.classList.remove('selected'));
    document.querySelectorAll('.fl2-obj-body').forEach(b => b.classList.remove('open'));
    document.querySelectorAll('.fl2-obj-toggle').forEach(d => { d.innerHTML = ''; });

    const wrap = document.getElementById('fl2Obj_' + idObj);
    const body = document.getElementById('fl2ObjBody_' + idObj);
    const dot  = document.getElementById('fl2ObjDot_' + idObj);

    wrap.classList.add('selected');
    body.classList.add('open');
    dot.innerHTML = '&#10003;';
    fl2State.objSel = idObj;
}

// ── Asignar objetivo ──────────────────────────────────────────────────────
// ── Bloqueo ESC en ambos modales ─────────────────────────────────────────
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    const fl2Open = document.getElementById('fl2ModalOverlay')?.style.display === 'flex';
    const fb1Open = document.getElementById('fbModalOverlay')?.classList.contains('active');
    // Bloquear ESC en ambos modales — el usuario debe usar el botón explícito de cierre
    if (fl2Open || fb1Open) e.preventDefault();
});

function fl2Asignar() {
    if (!fl2State.idLider || !fl2State.compSel || !fl2State.objSel) {
        Swal.fire({ icon:'warning', title:'Selecciona un objetivo', showConfirmButton:false, timer:1800 });
        return;
    }
    if (fl2State.acuerdosCount >= FL2_MAX_OBJ) {
        Swal.fire({ icon:'warning', title:'Límite alcanzado',
            text: 'Ya se asignaron ' + FL2_MAX_OBJ + ' objetivos a este líder.',
            confirmButtonColor:'#0058af' });
        return;
    }
    const btn = document.getElementById('fl2BtnAsignar');
    btn.disabled = true; btn.textContent = 'Guardando...';

    const id = fl2State.objSel;
    const body = new URLSearchParams({
        action:       'asignarObjetivo',
        idEmpleado:   fl2State.idLider,
        idObjetivo:   id,
        numComp:      fl2State.compSel,
        calificacion: fl2State.calSel,
        apoyo:        document.getElementById('fl2Apo_' + id)?.value || '',
        indicador:    document.getElementById('fl2Ind_' + id)?.value || '',
        meta:         document.getElementById('fl2Met_' + id)?.value || '',
        plazo:        document.getElementById('fl2Plz_' + id)?.value || '',
        evidencia:    document.getElementById('fl2Evi_' + id)?.value || '',
        compromiso:   document.getElementById('fl2Com_' + id)?.value || '',
        idFeedback:   fl2State.idFeedback || 0,
    });
    fetch(FB_APP_URL + 'feedback/', {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
        body: body.toString()
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false; btn.textContent = '\u2713 Asignar Objetivo';
        if (res.ok) {
            fl2State.acuerdosCount++;
            fl2State.acuerdosComp.add(fl2State.compSel);
            fl2ActualizarContador(fl2State.acuerdosCount);
            const quedan = FL2_MAX_OBJ - fl2State.acuerdosCount;
            if (quedan > 0) {
                Swal.fire({
                    icon:'success', title:'¡Objetivo asignado!',
                    text: 'Puedes asignar ' + quedan + ' objetivo(s) más a este líder.',
                    confirmButtonColor:'#0058af', confirmButtonText:'Asignar otro',
                    didOpen: () => { document.querySelector('.swal2-container').style.zIndex = '99999'; }
                }).then(() => { fl2IrPaso(1); fl2RenderCompetencias(); });
            } else {
                Swal.fire({
                    icon:'success', title:'¡Objetivos completados!',
                    html: 'Se asignaron los <strong>' + FL2_MAX_OBJ + '</strong> objetivos de liderazgo.<br>' +
                          '<span style="font-size:.85rem;color:#475569;">Ahora el líder debe firmar el recibido.</span>',
                    confirmButtonColor:'#0058af', confirmButtonText:'✎ Ir a firma',
                    allowOutsideClick: false,
                    didOpen: () => { document.querySelector('.swal2-container').style.zIndex = '99999'; }
                }).then(() => {
                    document.getElementById('fl2NombreDirectorFirma').textContent = 'Director';
                    document.getElementById('fl2NombreLiderFirmaPanel').textContent = fl2State.nombre;
                    document.getElementById('fl2FirmaCedula').value = '';
                    document.getElementById('fl2FirmaPassword').value = '';
                    document.getElementById('fl2FormFirma').style.display = '';
                    document.getElementById('fl2FirmaOk').style.display = 'none';
                    document.getElementById('fl2EstadoFirmaLider').textContent = '· Pendiente de firma';
                    document.getElementById('fl2EstadoFirmaLider').style.color = '#d97706';
                    fl2IrPaso('firma');
                });
            }
            fl2IrPaso(1);
            fl2RenderCompetencias();
        } else {
            Swal.fire({ icon:'error', title:'Error', text: res.msg || 'No se pudo asignar.', confirmButtonColor:'#0058af',
                didOpen: () => { document.querySelector('.swal2-container').style.zIndex = '99999'; } });
        }
    });
}


</script>

<!-- ══════════════════════════════════════════════════════════
     TOUR INTERACTIVO — Fase 1 · Módulo Feedback
     Driver.js 1.3.1 · Carga diferida · Reversible sin impacto
══════════════════════════════════════════════════════════ -->

<!-- Botón flotante ayuda -->
<button id="fb-tour-btn"
        onclick="iniciarTourFeedback()"
        title="Tour guiado — cómo usar este módulo"
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
<div id="fb-tour-tip"
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
    var btn = document.getElementById('fb-tour-btn');
    var tip = document.getElementById('fb-tour-tip');
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

// ── Carga diferida de Driver.js + CSS personalizado ───────────────────────────
function _fbLoadDriver(cb) {
    if (window._fbDriverReady) { cb(); return; }

    // 1. CSS de Driver.js
    var cssLink  = document.createElement('link');
    cssLink.rel  = 'stylesheet';
    cssLink.href = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css';
    document.head.appendChild(cssLink);

    // 2. Overrides de estilo — GestionHumana design system
    var style = document.createElement('style');
    style.id  = 'gh-driver-overrides';
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
        'border-top:1px solid #f1f5f9!important;' +
        'display:flex!important;align-items:center!important;' +
        'justify-content:space-between!important;gap:8px!important;' +
        'background:#f8fafc!important;border-radius:0 0 16px 16px!important;}' +

        '.driver-popover-progress-text{font-size:10.5px!important;color:#94a3b8!important;' +
        'font-family:"DM Mono",monospace!important;letter-spacing:.04em!important;flex:1!important;}' +

        '.driver-popover-prev-btn{border:1.5px solid #e2e8f0!important;border-radius:8px!important;' +
        'padding:6px 15px!important;font-size:12px!important;font-weight:600!important;' +
        'cursor:pointer!important;background:#fff!important;color:#64748b!important;' +
        'transition:background .15s!important;}' +
        '.driver-popover-prev-btn:hover{background:#f1f5f9!important;}' +

        '.driver-popover-next-btn{border:none!important;border-radius:8px!important;' +
        'padding:9px 22px!important;font-size:13px!important;font-weight:700!important;' +
        'letter-spacing:0!important;cursor:pointer!important;' +
        '-webkit-font-smoothing:antialiased!important;text-shadow:none!important;' +
        'background:#0058af!important;color:#fff!important;}' +
        '.driver-popover-next-btn:hover{background:#004a9a!important;}' +

        '.driver-popover-close-btn{color:#94a3b8!important;background:none!important;' +
        'border:none!important;cursor:pointer!important;font-size:17px!important;' +
        'line-height:1!important;opacity:.7!important;transition:opacity .15s!important;}' +
        '.driver-popover-close-btn:hover{opacity:1!important;}' +

        '.driver-popover-arrow-side-left .driver-popover-arrow{border-left-color:#fff!important;}' +
        '.driver-popover-arrow-side-right .driver-popover-arrow{border-right-color:#fff!important;}' +
        '.driver-popover-arrow-side-top .driver-popover-arrow{border-top-color:#fff!important;}' +
        '.driver-popover-arrow-side-bottom .driver-popover-arrow{border-bottom-color:#fff!important;}';
    document.head.appendChild(style);

    // 3. Script de Driver.js
    var scr    = document.createElement('script');
    scr.src    = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js';
    scr.onload = function() { window._fbDriverReady = true; cb(); };
    document.head.appendChild(scr);
}

// ── Tour principal ────────────────────────────────────────────────────────────
function iniciarTourFeedback() {
    _fbLoadDriver(function () {
        var driverFn     = window.driver.js.driver;
        var tienePeriodo = <?= $periodoActivo    ? 'true' : 'false' ?>;
        var tieneEquipo  = <?= !empty($equipo)        ? 'true' : 'false' ?>;
        var tieneDir     = <?= !empty($lideresACargo) ? 'true' : 'false' ?>;

        // Helper — icon HTML (Tabler)
        function ic(name, color) {
            color = color || '#0058af';
            return '<i class="ti ti-' + name + '" style="color:' + color +
                   ';font-size:16px;flex-shrink:0;"></i>';
        }

        var pasos = [];

        // ── 0. Bienvenida ───────────────────────────────────────────────────
        pasos.push({
            popover: {
                title:       ic('messages') + ' Módulo de Feedback',
                description: 'Aquí registras las <strong>reuniones de retroalimentación</strong> con ' +
                             'tu equipo y asignas <strong>Objetivos SMART</strong> de mejora para el ' +
                             'período actual.<br><br>El tour te mostrará cada sección del panel paso a paso.',
                side: 'over', align: 'center'
            }
        });

        // ── 1. Hero ─────────────────────────────────────────────────────────
        if (document.querySelector('.fb-hero')) {
            pasos.push({
                element: '.fb-hero',
                popover: {
                    title:       ic('calendar-event') + ' Período activo',
                    description: 'Muestra el período de evaluación vigente y su <strong>fecha de cierre</strong>. ' +
                                 'Todo el proceso de feedback debe completarse dentro de este plazo.',
                    side: 'bottom', align: 'start'
                }
            });
        }

        if (tienePeriodo) {

            // ── 2. Stats ────────────────────────────────────────────────────
            if (document.querySelector('.fb-stats')) {
                pasos.push({
                    element: '.fb-stats',
                    popover: {
                        title:       ic('chart-bar') + ' Resumen del equipo',
                        description: 'Indicadores de avance del período:<br><br>' +
                                     '<strong>Colaboradores</strong> — total a tu cargo<br>' +
                                     '<strong>Con feedback</strong> — reunión ya registrada<br>' +
                                     '<strong>Con objetivos SMART</strong> — compromisos asignados<br>' +
                                     '<strong>Proceso completo</strong> — feedback + objetivos + firma',
                        side: 'bottom', align: 'start'
                    }
                });
            }

            // ── 3. Tabla equipo ─────────────────────────────────────────────
            if (document.querySelector('.fb-card')) {
                pasos.push({
                    element: '.fb-card',
                    popover: {
                        title:       ic('users') + ' Tu equipo — Proceso 1',
                        description: 'Lista de colaboradores a cargo. Cada fila muestra el estado del ' +
                                     'proceso para el período activo. Los botones cambian según el avance de cada persona.',
                        side: 'top', align: 'start'
                    }
                });
            }

            // ── 4. Columnas ─────────────────────────────────────────────────
            if (document.querySelector('.fb-table thead')) {
                pasos.push({
                    element: '.fb-table thead',
                    popover: {
                        title:       ic('layout-columns') + ' Columnas de estado',
                        description: '<strong>Autoevaluó</strong> — el colaborador completó su autoevaluación<br>' +
                                     '<strong>Fue evaluado</strong> — tú lo evaluaste en el período<br>' +
                                     '<strong>Feedback</strong> — reunión registrada en el sistema<br>' +
                                     '<strong>Objetivos SMART</strong> — compromisos de mejora ' +
                                     '(máx. <strong>' + FB_MAX_OBJ + '</strong> por colaborador)',
                        side: 'bottom', align: 'start'
                    }
                });
            }

            // ── 5. Botón acción ─────────────────────────────────────────────
            if (tieneEquipo && document.querySelector('.fb-table tbody tr td:last-child')) {
                pasos.push({
                    element: '.fb-table tbody tr:first-child td:last-child',
                    popover: {
                        title:       ic('player-play') + ' Iniciar el proceso',
                        description: 'Haz clic en <strong>Registrar</strong> para abrir el asistente de 4 pasos:<br><br>' +
                                     ic('circle-number-1','#64748b') + ' Fecha y observaciones de la reunión<br>' +
                                     ic('circle-number-2','#64748b') + ' Competencia a trabajar según calificaciones<br>' +
                                     ic('circle-number-3','#64748b') + ' Objetivo SMART sugerido por el sistema<br>' +
                                     ic('circle-number-4','#64748b') + ' Firma digital del colaborador',
                        side: 'left', align: 'center'
                    }
                });
            }

            // ── 6. Tabla líderes — solo directores ──────────────────────────
            if (tieneDir && document.querySelector('.fb-card:nth-of-type(2)')) {
                pasos.push({
                    element: '.fb-card:nth-of-type(2)',
                    popover: {
                        title:       ic('crown') + ' Feedback a Líderes — Proceso 2',
                        description: 'Como director, también das feedback a los líderes de tu área basado en ' +
                                     'las <strong>competencias de liderazgo P12-P16</strong> evaluadas ' +
                                     'por sus propios colaboradores.',
                        side: 'top', align: 'start'
                    }
                });
            }

        } // fin tienePeriodo

        // ── Final ───────────────────────────────────────────────────────────
        pasos.push({
            popover: {
                title:       ic('circle-check','#15803d') + ' Listo para comenzar',
                description: 'Ya conoces el panel de Feedback. Puedes repetir este recorrido ' +
                             'cuando quieras pulsando el botón ' +
                             '<span style="display:inline-flex;align-items:center;justify-content:center;' +
                             'width:20px;height:20px;border-radius:50%;vertical-align:middle;margin:0 2px;' +
                             'background:linear-gradient(135deg,#0058af,#0074e0);' +
                             'color:#fff;font-size:11px;font-weight:700;line-height:1;">?</span>' +
                             ' en la esquina inferior derecha.',
                side: 'over', align: 'center'
            }
        });

        // ── Iniciar Driver ──────────────────────────────────────────────────
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
            // Fix: manejar el cierre manualmente para garantizar que siempre cierre
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