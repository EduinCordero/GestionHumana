<?php
// ── Inicialización de variables ────────────────────────────────────────────
$activeTab          = $activeTab          ?? 'periodos';
$mensajeOk          = $mensajeOk          ?? '';
$mensajeErr         = $mensajeErr         ?? '';
$periodoActivo      = $periodoActivo      ?? null;
$periodos           = $periodos           ?? [];
$avanceProceso      = $avanceProceso      ?? [];
$promedioComp       = $promedioComp       ?? [];
$estadoEquipo       = $estadoEquipo       ?? [];
$resumenEquipo      = $resumenEquipo      ?? ['total'=>0,'completo'=>0,'en_progreso'=>0,'sin_iniciar'=>0];
$resultadosUsuarios = $resultadosUsuarios ?? [];
$searchTermUsuarios = $searchTermUsuarios ?? '';
$objetivos          = $objetivos          ?? [];
$dictColab          = $dictColab          ?? [];
$dictColab          = $dictColab          ?? [];
$notifMasiva_ok     = $notifMasiva_ok     ?? false;
$notifMasiva_total  = $notifMasiva_total  ?? 0;
$notifSinCorreo     = $notifSinCorreo     ?? [];
$feedbackActivo     = $feedbackActivo     ?? false;
$dictColab          = $dictColab          ?? [];

// Tabs disponibles
$tabs = [
    'periodos'     => ['label' => 'Períodos',     'icon' => '◈'],
    'seguimiento'  => ['label' => 'Seguimiento',  'icon' => '◉'],
    'avance'       => ['label' => 'Avance',        'icon' => '↗'],
    'promedios'    => ['label' => 'Promedios',     'icon' => '◎'],
    'usuarios'     => ['label' => 'Usuarios',      'icon' => '◈'],
    'objetivos'    => ['label' => 'Objetivos',     'icon' => '◎'],
    'competencias'  => ['label' => 'Competencias',  'icon' => '◇'],
    'colaboradores' => ['label' => 'Colaboradores', 'icon' => '◉'],
];
$empleadosEval = $empleadosEval ?? [];
$lideresEval   = $lideresEval   ?? [];
$buscarEval    = $buscarEval    ?? '';
$soloActivos   = $soloActivos   ?? 1;
$competencias  = $competencias  ?? [];
$opcionesComp  = $opcionesComp  ?? [];
$promedioLider = $promedioLider ?? [];
$dictLider     = $dictLider     ?? [];

// Prepara datos para la gráfica de avance (Chart.js)
$chartLabels  = [];
$chartPctAuto = [];
$chartPctComp = [];
foreach ($avanceProceso as $row) {
    $chartLabels[]  = mb_substr($row['DEPENDENCIA'], 0, 20) . '…';
    $chartPctAuto[] = (float)($row['PCT_AUTOEVAL'] ?? 0);
    $chartPctComp[] = (float)($row['PCT_COMPLETO'] ?? 0);
}
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap');
:root {
    --adm-accent:  #0058af;
    --adm-accent2: #0074e0;
    --adm-green:   #10b981;
    --adm-amber:   #f59e0b;
    --adm-red:     #ef4444;
    --adm-blue:    #0058af;
    --adm-border:  #e2e8f0;
    --adm-muted:   #64748b;
    --adm-card:    #ffffff;
    --adm-bg:      #f8fafc;
    --adm-radius:  14px;
    --adm-shadow:  0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,88,175,.08);
}
.adm-root * { box-sizing: border-box; }
.adm-root   { font-family:'DM Sans',sans-serif; color:#1e293b; }
.adm-wrap   { padding:68px 32px 56px; max-width:1200px; margin:0 auto; }

/* Header */
.adm-hero   { display:flex; align-items:center; gap:14px; margin-bottom:24px; }
.adm-icon   { width:46px; height:46px; border-radius:13px;
              background:linear-gradient(135deg,#0058af,#0074e0);
              display:flex; align-items:center; justify-content:center;
              font-size:20px; box-shadow:0 4px 12px rgba(0,88,175,.35); }
.adm-title  { font-size:1.6rem; font-weight:700; letter-spacing:-.4px; }
.adm-sub    { font-size:.85rem; color:var(--adm-muted); margin-top:2px; }

/* Período activo banner */
.adm-period-banner { display:flex; align-items:center; gap:12px; padding:12px 18px;
                     border-radius:12px; margin-bottom:20px; font-size:.88rem; }
.adm-period-active { background:#ecfdf5; border:1.5px solid #6ee7b7; color:#065f46; }
.adm-period-none   { background:#fff7ed; border:1.5px solid #fcd34d; color:#92400e; }

/* Tabs */
.adm-tabs  { display:flex; gap:4px; background:#fff; padding:6px;
             border-radius:14px; border:1px solid var(--adm-border);
             box-shadow:var(--adm-shadow); overflow-x:auto;
             margin-bottom:24px; scrollbar-width:none; }
.adm-tabs::-webkit-scrollbar { display:none; }
.adm-tab   { flex-shrink:0; padding:8px 18px; border-radius:10px;
             font-size:.85rem; font-weight:600; cursor:pointer;
             border:none; background:transparent; color:var(--adm-muted);
             transition:all .2s; display:flex; align-items:center; gap:6px;
             text-decoration:none; }
.adm-tab:hover  { background:#f1f5f9; color:#1e293b; }
.adm-tab.active { background:linear-gradient(135deg,#0058af,#0074e0);
                  color:#fff; box-shadow:0 2px 8px rgba(0,88,175,.3); }

/* Paneles */
.adm-panel { display:none; animation:admFade .3s ease; }
.adm-panel.active { display:block; }
@keyframes admFade { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
@keyframes spin    { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

/* Cards */
.adm-card  { background:var(--adm-card); border-radius:var(--adm-radius);
             border:1px solid var(--adm-border); box-shadow:var(--adm-shadow); padding:24px; }
.adm-grid2 { display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:18px; }
.adm-grid4 { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; }

/* Stat cards */
.adm-stat  { border-radius:14px; padding:20px; position:relative; overflow:hidden; }
.adm-stat-label { font-size:.7rem; font-weight:700; letter-spacing:.08em;
                  text-transform:uppercase; margin-bottom:6px; }
.adm-stat-value { font-size:2.4rem; font-weight:700; line-height:1;
                  font-family:'DM Mono',monospace; }
.adm-stat-sub   { font-size:.75rem; margin-top:4px; opacity:.75; }

/* Tabla */
.adm-table { width:100%; border-collapse:collapse; font-size:.84rem; }
.adm-table thead th { background:#f8fafc; color:var(--adm-muted);
                      font-size:.7rem; font-weight:700; letter-spacing:.06em;
                      text-transform:uppercase; padding:10px 12px;
                      border-bottom:2px solid var(--adm-border); text-align:left; }
.adm-table tbody tr { border-bottom:1px solid #f1f5f9; transition:background .15s; }
.adm-table tbody tr:hover { background:#f8fafc; }
.adm-table tbody td { padding:10px 12px; vertical-align:middle; }

/* Badges estado */
.adm-badge { display:inline-flex; align-items:center; gap:5px;
             padding:3px 10px; border-radius:20px;
             font-size:.72rem; font-weight:700; white-space:nowrap; }
.adm-badge-ok  { background:#d1fae5; color:#065f46; }
.adm-badge-mid { background:#fef3c7; color:#92400e; }
.adm-badge-no  { background:#fee2e2; color:#991b1b; }
.adm-badge-off { background:#f1f5f9; color:#64748b; }

/* Progress bar */
.adm-bar  { height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden;
            min-width:80px; }
.adm-fill { height:100%; border-radius:4px; transition:width 1s ease; }

/* Botones */
.adm-btn  { display:inline-flex; align-items:center; gap:6px;
            padding:8px 18px; border-radius:10px; font-weight:600;
            font-size:.85rem; cursor:pointer; border:none;
            font-family:inherit; transition:opacity .2s; }
.adm-btn:hover { opacity:.88; }
.adm-btn-primary { background:linear-gradient(135deg,#0058af,#0074e0); color:#fff;
                   box-shadow:0 2px 8px rgba(0,88,175,.3); }
.adm-btn-primary:hover { background:linear-gradient(135deg,#004a94,#005ec7); }
.adm-btn-green   { background:var(--adm-green); color:#fff; }
.adm-btn-amber   { background:var(--adm-amber); color:#fff; }
.adm-btn-red     { background:var(--adm-red);   color:#fff; }
.adm-btn-ghost   { background:#f1f5f9; color:#475569; }
.adm-btn-sm      { padding:5px 12px; font-size:.78rem; }

/* Form */
.adm-form-group { margin-bottom:16px; }
.adm-label  { display:block; font-size:.82rem; font-weight:600;
              color:#475569; margin-bottom:6px; }
.adm-input  { width:100%; padding:9px 14px; border:1.5px solid var(--adm-border);
              border-radius:10px; font-size:.88rem; font-family:inherit;
              outline:none; transition:border-color .2s; background:#fff; }
.adm-input:focus { border-color:var(--adm-accent); }
.adm-toggle { display:flex; align-items:center; gap:10px; cursor:pointer; }
.adm-toggle input[type=checkbox] { width:18px; height:18px; accent-color:var(--adm-accent); cursor:pointer; }

/* Alertas */
.adm-alert     { padding:12px 16px; border-radius:10px; font-size:.87rem;
                 margin-bottom:18px; display:flex; align-items:center; gap:10px; }
.adm-alert-ok  { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
.adm-alert-err { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }

/* Buscador */
.adm-search { display:flex; gap:10px; margin-bottom:20px; }
.adm-search input { flex:1; padding:9px 14px; border:1.5px solid var(--adm-border);
                    border-radius:10px; font-size:.88rem; font-family:inherit;
                    outline:none; transition:border-color .2s; }
.adm-search input:focus { border-color:var(--adm-accent); }

/* Período card */
.adm-period-card { border:1.5px solid var(--adm-border); border-radius:12px;
                   padding:16px 18px; display:flex; align-items:center;
                   gap:14px; transition:border-color .2s; }
.adm-period-card:hover { border-color:var(--adm-accent); }
.adm-period-dot  { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

/* Tabla de promedios — colores semafóricos */
.adm-prom-5 { background:#d1fae5; color:#065f46; font-weight:700;
              border-radius:6px; padding:2px 6px; font-size:.78rem; }
.adm-prom-4 { background:#dbeafe; color:#1e40af; font-weight:700;
              border-radius:6px; padding:2px 6px; font-size:.78rem; }
.adm-prom-3 { background:#fef3c7; color:#92400e; font-weight:700;
              border-radius:6px; padding:2px 6px; font-size:.78rem; }
.adm-prom-2 { background:#fee2e2; color:#991b1b; font-weight:700;
              border-radius:6px; padding:2px 6px; font-size:.78rem; }
.adm-prom-0 { color:#94a3b8; font-size:.78rem; }

/* Fecha mono */
.adm-date { font-family:'DM Mono',monospace; font-size:.75rem;
            background:#f1f5f9; padding:2px 8px; border-radius:6px;
            color:var(--adm-muted); }

/* Empty */
.adm-empty { text-align:center; padding:40px 20px; color:var(--adm-muted); }
.adm-empty-icon { font-size:2rem; margin-bottom:10px; opacity:.4; }

@media (max-width:640px) {
    .adm-wrap { padding:68px 16px 56px; }
    .adm-stat-value { font-size:1.8rem; }
    .adm-grid2 { grid-template-columns:1fr; }
}
</style>

<div class="adm-root">
<div class="adm-wrap">

    <!-- Cabecera -->
    <!-- Header estilo Zayma -->
    <div id="adm-hero" style="background:linear-gradient(135deg,#0058af 0%,#1a73e8 60%,#2563eb 100%);
                border-radius:20px;padding:28px 36px;
                display:flex;align-items:center;justify-content:space-between;gap:24px;
                margin-bottom:20px;position:relative;overflow:hidden;
                box-shadow:0 4px 24px rgba(0,88,175,0.25);">
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
                Panel de Administración
            </div>
            <div style="font-size:13px;color:rgba(255,255,255,0.6);">
                Gestión de períodos, seguimiento y reportes globales
            </div>
        </div>
        <div style="z-index:1;flex-shrink:0;">
            <div style="width:56px;height:56px;border-radius:16px;
                        background:rgba(255,255,255,0.15);
                        display:flex;align-items:center;justify-content:center;">
                <?= icon('settings', 28) ?>
            </div>
        </div>
    </div>

    <!-- Alertas de sesión -->
    <?php if ($mensajeOk): ?>
    <div class="adm-alert adm-alert-ok"><?= icon('check-circle', 14) ?> <?= htmlspecialchars($mensajeOk) ?></div>
    <?php endif; ?>
    <?php if ($mensajeErr): ?>
    <div class="adm-alert adm-alert-err"><?= icon('x-circle', 14) ?> <?= htmlspecialchars($mensajeErr) ?></div>
    <?php endif; ?>

    <!-- Banner período activo -->
    <?php if ($periodoActivo): ?>
    <div class="adm-period-banner adm-period-active">
        <span><?= icon('check-circle', 14) ?></span>
        <div>
            <strong><?= htmlspecialchars($periodoActivo['NOMBRE']) ?></strong>
            &nbsp;·&nbsp;
            <?= $periodoActivo['FECHAAPERTURA'] ?>
            al
            <?= $periodoActivo['FECHACIERRE'] ?>
            <?php
            $hoy     = new DateTime();
            $cierre  = DateTime::createFromFormat('d/m/Y', $periodoActivo['FECHACIERRE']);
            $diff    = $hoy->diff($cierre);
            $diasRest = (int)$cierre->format('U') - (int)$hoy->format('U');
            if ($diasRest > 0):
                $diasNum = (int)$diff->days;
            ?>
            &nbsp;·&nbsp;
            <span style="font-weight:700;">
                <?= $diasNum ?> día<?= $diasNum != 1 ? 's' : '' ?> restante<?= $diasNum != 1 ? 's' : '' ?>
            </span>
            <?php else: ?>
            &nbsp;·&nbsp; <span style="color:#dc2626;font-weight:700;"><?= icon('alert-circle', 13) ?> Período vencido</span>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="adm-period-banner adm-period-none">
        <?= icon('alert-circle', 14) ?> <strong>No hay período activo.</strong> Activa uno en la pestaña Períodos para que los colaboradores puedan ser evaluados.
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="adm-tabs">
        <?php foreach ($tabs as $id => $info): ?>
        <a class="adm-tab <?= $id === $activeTab ? 'active' : '' ?>"
           href="<?= APP_URL ?>admin/?tab=<?= $id ?>">
            <?= $info['icon'] ?> <?= $info['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ══════════════════════════════════
         PANEL — PERÍODOS
    ══════════════════════════════════ -->
    <?php if ($activeTab === 'periodos'): ?>
    <div id="adm-periodos" class="adm-panel active">
        <div class="adm-grid2">

            <!-- Crear período -->
            <div class="adm-card">
                <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.06em;color:var(--adm-muted);margin-bottom:18px;">
                    Crear nuevo período
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="crearPeriodo">
                    <input type="hidden" name="activeTab" value="periodos">
                    <div class="adm-form-group">
                        <label class="adm-label">Nombre del período *</label>
                        <input type="text" name="nombre" class="adm-input"
                               placeholder="Ej: Evaluación 2025 — II Semestre" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="adm-form-group">
                            <label class="adm-label">Fecha apertura *</label>
                            <input type="date" name="apertura" class="adm-input" required>
                        </div>
                        <div class="adm-form-group">
                            <label class="adm-label">Fecha cierre *</label>
                            <input type="date" name="cierre" class="adm-input" required>
                        </div>
                    </div>
                    <div class="adm-form-group">
                        <label class="adm-label">Observación</label>
                        <input type="text" name="observacion" class="adm-input"
                               placeholder="Opcional — notas internas">
                    </div>
                    <button type="submit" class="adm-btn adm-btn-primary">
                        <?= icon('check', 13) ?> Crear período
                    </button>
                </form>
            </div>

            <!-- Lista de períodos -->
            <div class="adm-card">
                <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.06em;color:var(--adm-muted);margin-bottom:18px;">
                    Períodos registrados
                </div>
                <?php if (!empty($periodos)): ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php foreach ($periodos as $p):
                        $esActivo = $p['ESTADO'] == 1;
                        $dotColor = $esActivo ? '#10b981' : '#94a3b8';
                    ?>
                    <div class="adm-period-card">
                        <div class="adm-period-dot" style="background:<?= $dotColor ?>;"></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;font-size:.88rem;margin-bottom:3px;">
                                <?= htmlspecialchars($p['NOMBRE']) ?>
                                <?php if ($esActivo): ?>
                                <span class="adm-badge adm-badge-ok" style="margin-left:6px;">Activo</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:.75rem;color:var(--adm-muted);">
                                <?= $p['FECHAAPERTURA'] ?>
                                →
                                <?= $p['FECHACIERRE'] ?>
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;align-items:center;">
                            <!-- Editar fecha cierre -->
                            <button type="button" onclick="toggleEditCierre(<?= $p['IDPERIODO'] ?>)"
                                    class="adm-btn adm-btn-ghost adm-btn-sm" title="Editar fecha de cierre"><?= icon('pen', 13) ?>️</button>
                            <?php if (!$esActivo): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="activarPeriodo">
                                <input type="hidden" name="idPeriodo" value="<?= $p['IDPERIODO'] ?>">
                                <button type="submit" class="adm-btn adm-btn-green adm-btn-sm"
                                        onclick="return confirm('¿Activar este período? El período actual se desactivará automáticamente.')">
                                    ▶ Activar
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="cerrarPeriodo">
                                <input type="hidden" name="idPeriodo" value="<?= $p['IDPERIODO'] ?>">
                                <button type="submit" class="adm-btn adm-btn-red adm-btn-sm"
                                        onclick="return confirm('¿Cerrar este período?')">
                                    <?= icon('x-circle', 13) ?> Cerrar
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <!-- Formulario inline editar fecha cierre -->
                        <div id="edit-cierre-<?= $p['IDPERIODO'] ?>" style="display:none;margin-top:10px;
                             padding:10px 12px;background:#f8fafc;border-radius:8px;
                             border:1.5px solid var(--adm-border);">
                            <form method="POST" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <input type="hidden" name="action" value="editarCierrePeriodo">
                                <input type="hidden" name="idPeriodo" value="<?= $p['IDPERIODO'] ?>">
                                <label style="font-size:.82rem;font-weight:600;">Nueva fecha de cierre:</label>
                                <input type="date" name="nuevoCierre" class="adm-input"
                                       value="<?php
    $dtC = DateTime::createFromFormat('d/m/Y', $p['FECHACIERRE']);
    echo $dtC ? $dtC->format('Y-m-d') : '';
?>"
                                       style="width:auto;" required>
                                <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm"><?= icon('save', 13) ?> Guardar</button>
                                <button type="button" onclick="toggleEditCierre(<?= $p['IDPERIODO'] ?>)"
                                        class="adm-btn adm-btn-ghost adm-btn-sm">Cancelar</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="adm-empty">
                    <div class="adm-empty-icon"><?= icon('calendar', 36) ?></div>
                    <div>No hay períodos registrados aún.</div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Control Módulo Feedback ────────────────────────────────────── -->
        <div class="adm-card" style="margin-top:20px;">
            <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                        letter-spacing:.06em;color:var(--adm-muted);margin-bottom:16px;">
                <?= icon('message-dots', 15) ?> Módulo de Feedback
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;
                        gap:20px;flex-wrap:wrap;">
                <div>
                    <div style="font-weight:600;font-size:.92rem;margin-bottom:5px;">
                        Control de acceso al proceso de Feedback
                    </div>
                    <div style="font-size:.82rem;color:var(--adm-muted);line-height:1.6;max-width:520px;">
                        Cuando está <strong>deshabilitado</strong>, los líderes verán el mensaje
                        <em>"El proceso de Feedback aún no está disponible"</em> en lugar del panel.
                        Los administradores siempre tienen acceso completo.
                    </div>
                    <div style="margin-top:12px;">
                        <span class="adm-badge <?= $feedbackActivo ? 'adm-badge-ok' : 'adm-badge-no' ?>"
                              style="font-size:.82rem;padding:5px 14px;border-radius:20px;">
                            <?php if ($feedbackActivo): ?>
                            <?= icon('check-circle', 13) ?> Habilitado — líderes pueden acceder
                            <?php else: ?>
                            <?= icon('x-circle', 13) ?> Deshabilitado — líderes ven mensaje de espera
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <form method="POST" id="formToggleFeedback">
                    <input type="hidden" name="action"    value="toggleFeedbackActivo">
                    <input type="hidden" name="activeTab" value="periodos">
                    <input type="hidden" name="valor"     value="<?= $feedbackActivo ? 0 : 1 ?>">
                    <button type="button"
                            onclick="admToggleFeedback(<?= $feedbackActivo ? 0 : 1 ?>)"
                            class="adm-btn <?= $feedbackActivo ? 'adm-btn-red' : 'adm-btn-green' ?>"
                            style="min-width:190px;">
                        <?php if ($feedbackActivo): ?>
                        <?= icon('x-circle', 14) ?> Deshabilitar Feedback
                        <?php else: ?>
                        <?= icon('check-circle', 14) ?> Habilitar Feedback
                        <?php endif; ?>
                    </button>
                </form>
            </div>
        </div>

    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════
         PANEL — SEGUIMIENTO
    ══════════════════════════════════ -->
    <?php if ($activeTab === 'seguimiento'): ?>
    <div id="adm-seguimiento" class="adm-panel active">

        <?php if ($notifMasiva_ok): ?>
        <div class="adm-alert adm-alert-ok">
            <?= icon('check-circle', 14) ?> Recordatorios enviados a <strong><?= $notifMasiva_total ?></strong> colaborador(es) con evaluación pendiente.
            <?php if (!empty($notifSinCorreo)): ?>
            <div style="margin-top:8px;padding-top:8px;border-top:1px solid #a7f3d0;">
                <?= icon('alert-triangle', 13) ?>
                <strong><?= count($notifSinCorreo) ?></strong> colaborador(es) omitido(s) por no tener correo registrado en HUMEMPLEADOEVAL ni GHEMPEMPLEADOS:
                <div style="margin-top:5px;font-size:.8rem;line-height:1.7;color:#065f46;">
                    <?php foreach ($notifSinCorreo as $nombre): ?>
                    <span style="display:inline-block;background:#d1fae5;border:1px solid #6ee7b7;
                                 border-radius:6px;padding:1px 8px;margin:2px 4px 2px 0;">
                        <?= htmlspecialchars($nombre) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Tarjetas resumen -->
        <div class="adm-grid4" style="margin-bottom:20px;">
            <div class="adm-stat" style="background:linear-gradient(135deg,#6366f122,#6366f111);border:1.5px solid #6366f144;">
                <div class="adm-stat-label" style="color:#6366f1;">Total colaboradores</div>
                <div class="adm-stat-value" style="color:#6366f1;"><?= $resumenEquipo['total'] ?></div>
                <div class="adm-stat-sub">con más de 90 días</div>
            </div>
            <div class="adm-stat" style="background:linear-gradient(135deg,#10b98122,#10b98111);border:1.5px solid #10b98144;">
                <div class="adm-stat-label" style="color:#059669;">Completos</div>
                <div class="adm-stat-value" style="color:#059669;"><?= $resumenEquipo['completo'] ?></div>
                <div class="adm-stat-sub">autoevaluación + evaluación recibida</div>
            </div>
            <div class="adm-stat" style="background:linear-gradient(135deg,#f59e0b22,#f59e0b11);border:1.5px solid #f59e0b44;">
                <div class="adm-stat-label" style="color:#d97706;">En progreso</div>
                <div class="adm-stat-value" style="color:#d97706;"><?= $resumenEquipo['en_progreso'] ?></div>
                <div class="adm-stat-sub">solo autoevaluación</div>
            </div>
            <div class="adm-stat" style="background:linear-gradient(135deg,#ef444422,#ef444411);border:1.5px solid #ef444444;">
                <div class="adm-stat-label" style="color:#dc2626;">Sin iniciar</div>
                <div class="adm-stat-value" style="color:#dc2626;"><?= $resumenEquipo['sin_iniciar'] ?></div>
                <div class="adm-stat-sub">sin autoevaluación</div>
            </div>
        </div>

        <!-- Botón notificación masiva -->
        <?php
        $pendientes = $resumenEquipo['en_progreso'] + $resumenEquipo['sin_iniciar'];
        if ($pendientes > 0 && $periodoActivo):
        ?>
        <div class="adm-card" style="margin-bottom:20px;display:flex;align-items:center;
                                      justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <div style="font-weight:700;font-size:.95rem;">Notificación masiva</div>
                <div style="font-size:.82rem;color:var(--adm-muted);margin-top:3px;">
                    Enviar recordatorio a los <strong><?= $pendientes ?></strong> colaboradores con evaluación pendiente
                    e informar a sus líderes.
                    <?php if ($periodoActivo): ?>
                    Fecha de cierre: <strong><?= $periodoActivo['FECHACIERRE'] ?></strong>
                    <?php endif; ?>
                </div>
            </div>
            <form method="POST" id="formNotifMasiva">
                <input type="hidden" name="action" value="enviarNotifMasiva">
                <input type="hidden" name="activeTab" value="seguimiento">
                <button type="button" class="adm-btn adm-btn-amber"
                        onclick="admConfirmarMasiva(<?= $pendientes ?>)">
                    <?= icon('bell', 13) ?> Enviar recordatorios
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Tabla de estado -->
        <div class="adm-card">
            <div style="display:flex;align-items:center;justify-content:space-between;
                        flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.06em;color:var(--adm-muted);">
                    Estado individual de colaboradores
                </div>
                <!-- Filtros de estado -->
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="adm-btn adm-btn-ghost adm-btn-sm seg-filter active"
                            onclick="segFiltrar('todos',this)">Todos</button>
                    <button class="adm-btn adm-btn-sm seg-filter"
                            style="background:#fee2e2;color:#991b1b;"
                            onclick="segFiltrar('sin_iniciar',this)"><?= icon('x-circle', 13) ?> Sin iniciar</button>
                    <button class="adm-btn adm-btn-sm seg-filter"
                            style="background:#fef3c7;color:#92400e;"
                            onclick="segFiltrar('en_progreso',this)"><?= icon('clock', 13) ?> En progreso</button>
                    <button class="adm-btn adm-btn-sm seg-filter"
                            style="background:#d1fae5;color:#065f46;"
                            onclick="segFiltrar('completo',this)"><?= icon('check-circle', 13) ?> Completo</button>
                </div>
            </div>
            <!-- Buscador en tiempo real -->
            <div style="margin-bottom:14px;">
                <input type="text" id="segBuscador"
                       placeholder="Buscar por nombre, cargo o área..."
                       oninput="segBuscar(this.value)"
                       style="width:100%;padding:9px 14px;border:1.5px solid var(--adm-border);
                              border-radius:10px;font-size:.88rem;font-family:inherit;
                              outline:none;transition:border-color .2s;">
            </div>
            <!-- Contador de resultados -->
            <div id="segContador" style="font-size:.78rem;color:var(--adm-muted);margin-bottom:10px;"></div>
            <?php if (!empty($estadoEquipo)): ?>
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
                <button onclick="exportarCSV('seguimiento', this)"
                   style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;
                      background:linear-gradient(135deg,#0058af,#2563eb);color:#fff;
                      border-radius:8px;font-size:.8rem;font-weight:600;
                      border:none;cursor:pointer;font-family:inherit;">
                <?= icon('download', 14, '#fff') ?> Exportar Excel
            </button>
            </div>
            <div style="overflow-x:auto;">
                <table class="adm-table" id="segTable">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th>Área / Proceso</th>
                            <th style="text-align:center;">Autoevaluación</th>
                            <th style="text-align:center;">Fue evaluado</th>
                            <th style="text-align:center;">Evaluó a otros</th>
                            <th>Estado</th>
                            <th style="text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estadoEquipo as $col):
                            $badgeClass = match($col['ESTADO']) {
                                'completo'    => 'adm-badge-ok',
                                'en_progreso' => 'adm-badge-mid',
                                default       => 'adm-badge-no',
                            };
                            $badgeLabel = match($col['ESTADO']) {
                                'completo'    => icon('check-circle', 13) . ' Completo',
                                'en_progreso' => icon('clock', 13) . ' En progreso',
                                default       => icon('x-circle', 13) . ' Sin iniciar',
                            };
                        ?>
                        <tr class="seg-row"
                            data-estado="<?= $col['ESTADO'] ?>"
                            data-nombre="<?= strtolower(htmlspecialchars($col['EMPLEADO'])) ?>"
                            data-cargo="<?= strtolower(htmlspecialchars($col['CARGO'])) ?>"
                            data-area="<?= strtolower(htmlspecialchars($col['AREAFUNCIONAL'])) ?>">
                            <td>
                                <div style="font-weight:600;"><?= htmlspecialchars($col['EMPLEADO']) ?></div>
                                <div style="font-size:.74rem;color:var(--adm-muted);"><?= htmlspecialchars($col['CARGO']) ?></div>
                            </td>
                            <td style="font-size:.82rem;color:var(--adm-muted);"><?= htmlspecialchars($col['AREAFUNCIONAL']) ?></td>
                            <td style="text-align:center;">
                                <?php if ($col['TIENE_AUTOEVAL']): ?>
                                    <?= icon('check-circle', 16) ?>
                                <?php else: ?>
                                    <?= icon('x-circle', 16) ?>
                                <?php endif; ?>
                            </td>
                            <!-- Fue evaluado: X/1 (cuántos lo evaluaron vs 1 esperado) -->
                            <td style="text-align:center;">
                                <?php
                                $recOk  = (int)($col['EVAL_RECIBIDAS'] ?? 0);
                                $recEsp = (int)($col['EVAL_ESPERADAS'] ?? 1);
                                $recColor = $recOk >= $recEsp ? '#10b981' : '#f59e0b';
                                ?>
                                <span style="font-family:'DM Mono',monospace;font-size:.82rem;
                                             font-weight:700;color:<?= $recColor ?>;">
                                    <?= $recOk ?>/<?= $recEsp ?>
                                </span>
                            </td>
                            <!-- Evaluó a otros: X/Y subordinados directos -->
                            <td style="text-align:center;">
                                <?php
                                $realiz = (int)($col['EVAL_REALIZADAS']    ?? 0);
                                $subord = (int)($col['TOTAL_SUBORDINADOS'] ?? 0);
                                ?>
                                <?php if ($subord > 0): ?>
                                    <span style="font-family:'DM Mono',monospace;font-size:.82rem;
                                                 font-weight:700;
                                                 color:<?= $realiz >= $subord ? '#10b981' : '#f59e0b' ?>;">
                                        <?= $realiz ?>/<?= $subord ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:.8rem;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="adm-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span></td>
                            <td style="text-align:center;">
                                <?php if ($col['ESTADO'] !== 'completo'): ?>
                                <?php
                                $yaNotif = !empty($col['ULTIMA_NOTIF']);
                                $tsNotif = $yaNotif ? strtotime($col['ULTIMA_NOTIF']) : false;
                                ?>
                                <button type="button"
                                        data-id="<?= (int)$col['IDEMPLEADO'] ?>"
                                        data-nombre="<?= htmlspecialchars($col['EMPLEADO'] ?? '', ENT_QUOTES) ?>"
                                        onclick="admNotifIndividual(this)"
                                        title="<?= $yaNotif && $tsNotif ? 'Último envío: ' . date('d/m/Y H:i', $tsNotif) : 'Enviar recordatorio por correo' ?>"
                                        style="display:inline-flex;flex-direction:column;align-items:center;
                                               gap:2px;padding:6px 12px;border-radius:8px;border:none;
                                               cursor:pointer;font-family:inherit;font-size:.78rem;
                                               font-weight:600;line-height:1.3;
                                               <?= $yaNotif
                                                   ? 'background:#d1fae5;color:#065f46;box-shadow:0 0 0 1.5px #6ee7b7;min-width:118px;'
                                                   : 'background:linear-gradient(135deg,#0058af,#2563eb);color:#fff;min-width:118px;' ?>">
                                    <span style="display:flex;align-items:center;gap:5px;">
                                        <?= icon($yaNotif ? 'bell-ringing' : 'bell', 13, $yaNotif ? '#065f46' : '#fff') ?>
                                        <?= $yaNotif ? 'Enviar de nuevo' : 'Enviar notificación' ?>
                                    </span>
                                    <?php if ($yaNotif && $tsNotif): ?>
                                    <span style="font-size:.67rem;font-weight:400;opacity:.78;">
                                        Último: <?= date('d/m/Y H:i', $tsNotif) ?>
                                    </span>
                                    <?php endif; ?>
                                </button>
                                <?php else: ?>
                                <span style="color:#94a3b8;font-size:.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="adm-empty">
                <div class="adm-empty-icon"><?= icon('users', 36) ?></div>
                <div>No hay colaboradores activos con más de 90 días registrados.</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

<script>
// ── Notificación individual — AJAX + SweetAlert ───────────────────────────
function admNotifIndividual(btn) {
    var id     = btn.dataset.id;
    var nombre = btn.dataset.nombre;

    Swal.fire({
        title: '¿Enviar recordatorio?',
        html:  'Se notificará a <strong>' + nombre + '</strong> '
             + 'y a su líder por correo electrónico.',
        icon: 'question',
        showCancelButton:   true,
        confirmButtonText:  '<i class="ti ti-send"></i> Sí, enviar',
        cancelButtonText:   'Cancelar',
        confirmButtonColor: '#0058af',
        cancelButtonColor:  '#94a3b8',
        reverseButtons:     true,
    }).then(function(result) {
        if (!result.isConfirmed) return;

        // Pantalla de carga
        Swal.fire({
            title: 'Enviando notificación…',
            html:  'Notificando a <strong>' + nombre + '</strong>',
            allowOutsideClick: false,
            allowEscapeKey:    false,
            didOpen: function() { Swal.showLoading(); }
        });

        var fd = new FormData();
        fd.append('action',        'enviarNotifIndividualAjax');
        fd.append('idColaborador', id);
        fd.append('activeTab',     'seguimiento');

        fetch(window.location.pathname, {
            method:  'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body:    fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                Swal.fire({
                    title: '¡Recordatorio enviado!',
                    html:  'Se notificó a <strong>' + data.nombre + '</strong> por correo.'
                         + (data.lider
                             ? '<br>También se informó a su líder <strong>' + data.lider + '</strong>.'
                             : '<br><small style="color:#94a3b8">El líder no tiene correo registrado.</small>'),
                    icon:               'success',
                    confirmButtonColor: '#0058af',
                    confirmButtonText:  'Aceptar',
                    timer:              4500,
                    timerProgressBar:   true,
                }).then(function() { location.reload(); });
            } else {
                Swal.fire({
                    title:              'Sin correo registrado',
                    html:               data.msg,
                    icon:               'warning',
                    confirmButtonColor: '#0058af',
                    confirmButtonText:  'Entendido',
                });
            }
        })
        .catch(function() {
            Swal.fire({
                title:              'Error de conexión',
                text:               'No se pudo conectar con el servidor. Intenta de nuevo.',
                icon:               'error',
                confirmButtonColor: '#dc2626',
            });
        });
    });
}

// ── Notificación masiva — confirmación Swal + loading durante POST ────────
function admConfirmarMasiva(total) {
    Swal.fire({
        title: '¿Enviar recordatorios masivos?',
        html:  'Se notificará a <strong>' + total + '</strong> colaborador(es) con evaluación pendiente '
             + 'y a sus respectivos líderes.'
             + '<br><br><small style="color:#64748b;">Este proceso puede tardar algunos segundos.</small>',
        icon:               'question',
        showCancelButton:   true,
        confirmButtonText:  '<i class="ti ti-bell-ringing"></i> Sí, enviar a todos',
        cancelButtonText:   'Cancelar',
        confirmButtonColor: '#d97706',
        cancelButtonColor:  '#94a3b8',
        reverseButtons:     true,
    }).then(function(result) {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Enviando recordatorios…',
            html:  'Notificando a <strong>' + total + '</strong> colaboradores. Por favor espera.',
            allowOutsideClick: false,
            allowEscapeKey:    false,
            didOpen: function() { Swal.showLoading(); }
        });

        document.getElementById('formNotifMasiva').submit();
    });
}

// ── Toggle Módulo Feedback ────────────────────────────────────────────────
function admToggleFeedback(nuevoValor) {
    var habilitar = nuevoValor === 1;
    Swal.fire({
        title: habilitar ? '¿Habilitar Feedback?' : '¿Deshabilitar Feedback?',
        html:  habilitar
            ? 'Los líderes funcionales podrán acceder al módulo de Feedback y registrar el plan de mejora de su equipo.'
            : 'Los líderes verán un mensaje de <em>"proceso no disponible"</em> al intentar acceder al módulo de Feedback.<br><br><small style="color:#64748b;">Los administradores siempre tienen acceso completo.</small>',
        icon:               habilitar ? 'question' : 'warning',
        showCancelButton:   true,
        confirmButtonText:  habilitar ? '✅ Sí, habilitar' : '🔒 Sí, deshabilitar',
        cancelButtonText:   'Cancelar',
        confirmButtonColor: habilitar ? '#10b981' : '#dc2626',
        cancelButtonColor:  '#94a3b8',
        reverseButtons:     true,
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('formToggleFeedback').submit();
        }
    });
}
</script>

    <!-- ══════════════════════════════════
         PANEL — AVANCE POR PROCESO
    ══════════════════════════════════ -->
    <?php if ($activeTab === 'avance'): ?>
    <div id="adm-avance" class="adm-panel active">

        <?php if (!empty($avanceProceso)):
            // Totales globales
            $totales = array_reduce($avanceProceso, fn($c, $r) => [
                'TOTAL'         => $c['TOTAL'] + $r['TOTAL'],
                'CON_AUTOEVAL'  => $c['CON_AUTOEVAL'] + $r['CON_AUTOEVAL'],
                'COMPLETOS'     => $c['COMPLETOS'] + $r['COMPLETOS'],
            ], ['TOTAL' => 0, 'CON_AUTOEVAL' => 0, 'COMPLETOS' => 0]);
            $pctGlobalAuto = $totales['TOTAL'] ? round($totales['CON_AUTOEVAL'] * 100 / $totales['TOTAL'], 1) : 0;
            $pctGlobalComp = $totales['TOTAL'] ? round($totales['COMPLETOS']    * 100 / $totales['TOTAL'], 1) : 0;
        ?>

        <!-- Tarjetas globales -->
        <div class="adm-grid4" style="margin-bottom:20px;">
            <div class="adm-stat" style="background:linear-gradient(135deg,#6366f122,#6366f111);border:1.5px solid #6366f144;">
                <div class="adm-stat-label" style="color:#6366f1;">Procesos</div>
                <div class="adm-stat-value" style="color:#6366f1;"><?= count($avanceProceso) ?></div>
                <div class="adm-stat-sub">áreas con colaboradores</div>
            </div>
            <div class="adm-stat" style="background:linear-gradient(135deg,#3b82f622,#3b82f611);border:1.5px solid #3b82f644;">
                <div class="adm-stat-label" style="color:#1d4ed8;">Total colaboradores</div>
                <div class="adm-stat-value" style="color:#1d4ed8;"><?= $totales['TOTAL'] ?></div>
                <div class="adm-stat-sub">en todos los procesos</div>
            </div>
            <div class="adm-stat" style="background:linear-gradient(135deg,#f59e0b22,#f59e0b11);border:1.5px solid #f59e0b44;">
                <div class="adm-stat-label" style="color:#d97706;">% Autoevaluación</div>
                <div class="adm-stat-value" style="color:#d97706;"><?= $pctGlobalAuto ?>%</div>
                <div class="adm-stat-sub"><?= $totales['CON_AUTOEVAL'] ?> de <?= $totales['TOTAL'] ?></div>
            </div>
            <div class="adm-stat" style="background:linear-gradient(135deg,#10b98122,#10b98111);border:1.5px solid #10b98144;">
                <div class="adm-stat-label" style="color:#059669;">% Completo</div>
                <div class="adm-stat-value" style="color:#059669;"><?= $pctGlobalComp ?>%</div>
                <div class="adm-stat-sub"><?= $totales['COMPLETOS'] ?> de <?= $totales['TOTAL'] ?></div>
            </div>
        </div>

        <!-- Gráfica de barras por proceso -->
        <div class="adm-card" style="margin-bottom:20px;">
            <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                        letter-spacing:.06em;color:var(--adm-muted);margin-bottom:16px;">
                Avance por área / proceso
            </div>
            <div style="position:relative;height:320px;">
                <canvas id="chartAvance"></canvas>
            </div>
        </div>

        <!-- Tabla detalle -->
        <div class="adm-card">
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
                <button onclick="exportarCSV('avance', this)"
                   style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;
                      background:linear-gradient(135deg,#0058af,#2563eb);color:#fff;
                      border-radius:8px;font-size:.8rem;font-weight:600;
                      border:none;cursor:pointer;font-family:inherit;">
                <?= icon('download', 14, '#fff') ?> Exportar Excel
            </button>
            </div>
            <div style="overflow-x:auto;">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Área / Proceso</th>
                            <th style="text-align:center;">Total</th>
                            <th style="text-align:center;">Autoevaluaron</th>
                            <th style="text-align:center;">Completos</th>
                            <th style="text-align:center;">% Autoevaluación</th>
                            <th style="text-align:center;">% Completo</th>
                            <th style="min-width:140px;">Progreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($avanceProceso as $row):
                            $pct  = (float)($row['PCT_COMPLETO'] ?? 0);
                            $color = $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                        ?>
                        <tr>
                            <td style="font-weight:600;"><?= htmlspecialchars($row['DEPENDENCIA']) ?></td>
                            <td style="text-align:center;"><?= $row['TOTAL'] ?></td>
                            <td style="text-align:center;"><?= $row['CON_AUTOEVAL'] ?></td>
                            <td style="text-align:center;"><?= $row['COMPLETOS'] ?></td>
                            <td style="text-align:center;">
                                <span style="font-family:'DM Mono',monospace;font-weight:700;color:#d97706;">
                                    <?= $row['PCT_AUTOEVAL'] ?>%
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <span style="font-family:'DM Mono',monospace;font-weight:700;color:<?= $color ?>;">
                                    <?= $row['PCT_COMPLETO'] ?>%
                                </span>
                            </td>
                            <td>
                                <div class="adm-bar">
                                    <div class="adm-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php else: ?>
        <div class="adm-card">
            <div class="adm-empty">
                <div class="adm-empty-icon"><?= icon('trending-up', 36) ?></div>
                <div>No hay datos de avance disponibles aún.</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════
         PANEL — PROMEDIOS POR COMPETENCIA
    ══════════════════════════════════ -->
    <?php if ($activeTab === 'promedios'): ?>
    <div id="adm-promedios" class="adm-panel active">

        <!-- Sub-tabs: Colaboradores / Líderes -->
        <div style="display:flex;gap:6px;margin-bottom:16px;border-bottom:2px solid var(--adm-border);padding-bottom:0;">
            <button onclick="admSubTab('prom-colab', this)"
                    id="btn-prom-colab"
                    style="padding:8px 20px;border:none;border-radius:10px 10px 0 0;font-size:.84rem;
                           font-weight:600;cursor:pointer;background:var(--adm-accent);color:#fff;">
                Como Colaborador
            </button>
            <button onclick="admSubTab('prom-lider', this)"
                    id="btn-prom-lider"
                    style="padding:8px 20px;border:none;border-radius:10px 10px 0 0;font-size:.84rem;
                           font-weight:600;cursor:pointer;background:#f1f5f9;color:var(--adm-muted);">
                Como Líder
            </button>
        </div>

        <!-- Promedios Colaboradores -->
        <div id="prom-colab">
        <?php if (!empty($promedioComp) && !empty($dictColab)): ?>
        <div class="adm-card">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px;">
                <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.06em;color:var(--adm-muted);">
                    Calificación promedio por área — Evaluación de desempeño
                </div>
                <button onclick="exportarCSV('promedios_colab', this)"
                   style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;
                      background:linear-gradient(135deg,#0058af,#2563eb);color:#fff;
                      border-radius:8px;font-size:.8rem;font-weight:600;
                      border:none;cursor:pointer;font-family:inherit;">
                <?= icon('download', 14, '#fff') ?> Exportar Excel
            </button>
            </div>
            <div style="font-size:.78rem;color:var(--adm-muted);margin-bottom:16px;">
                Promedio de las calificaciones que los líderes asignaron a sus colaboradores, agrupado por área funcional.
            </div>
            <div style="overflow-x:auto;">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Área / Proceso</th>
                            <th style="text-align:center;">Eval.</th>
                            <?php foreach ($dictColab as $i => $nombre): ?>
                            <th style="text-align:center;min-width:46px;font-size:.68rem;"
                                title="<?= htmlspecialchars($nombre) ?>">
                                P<?= $i ?>
                            </th>
                            <?php endforeach; ?>
                            <th style="text-align:center;min-width:70px;background:#eff6ff;color:#0058af;">
                                Promedio
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Acumuladores para fila de totales
                        $totalesComp = array_fill(1, 11, ['suma' => 0, 'cnt' => 0]);
                        foreach ($promedioComp as $row):
                            // Calcular promedio general de la fila
                            $sumaFila = 0; $cntFila = 0;
                            for ($i = 1; $i <= 11; $i++) {
                                $v = (float)($row["PROM_P$i"] ?? 0);
                                if ($v > 0) { $sumaFila += $v; $cntFila++; $totalesComp[$i]['suma'] += $v; $totalesComp[$i]['cnt']++; }
                            }
                            $promFila = $cntFila > 0 ? round($sumaFila / $cntFila, 2) : 0;
                            $clsFila  = $promFila >= 4.5 ? 'adm-prom-5' : ($promFila >= 3.5 ? 'adm-prom-4' : ($promFila >= 2.5 ? 'adm-prom-3' : ($promFila > 0 ? 'adm-prom-2' : 'adm-prom-0')));
                        ?>
                        <tr>
                            <td style="font-weight:600;"><?= htmlspecialchars($row['DEPENDENCIA']) ?></td>
                            <td style="text-align:center;font-family:'DM Mono',monospace;font-size:.8rem;"><?= $row['EVALUADOS'] ?></td>
                            <?php for ($i = 1; $i <= 11; $i++):
                                $val = (float)($row["PROM_P$i"] ?? 0);
                                if ($val == 0): ?>
                                <td style="text-align:center;"><span class="adm-prom-0">—</span></td>
                                <?php else:
                                    $cls = $val >= 4.5 ? 'adm-prom-5' : ($val >= 3.5 ? 'adm-prom-4' : ($val >= 2.5 ? 'adm-prom-3' : 'adm-prom-2'));
                                ?>
                                <td style="text-align:center;"><span class="<?= $cls ?>"><?= $val ?></span></td>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <td style="text-align:center;background:#f8faff;">
                                <?php if ($promFila > 0): ?>
                                <span class="<?= $clsFila ?>" style="font-size:.82rem;"><?= $promFila ?></span>
                                <?php else: ?>
                                <span class="adm-prom-0">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#eff6ff;border-top:2px solid #bfdbfe;">
                            <td style="font-weight:700;font-size:.78rem;color:#0058af;">Promedio general</td>
                            <td></td>
                            <?php
                            $sumaGlobal = 0; $cntGlobal = 0;
                            for ($i = 1; $i <= 11; $i++):
                                $promComp = $totalesComp[$i]['cnt'] > 0
                                    ? round($totalesComp[$i]['suma'] / $totalesComp[$i]['cnt'], 2)
                                    : 0;
                                $clsComp = $promComp >= 4.5 ? 'adm-prom-5' : ($promComp >= 3.5 ? 'adm-prom-4' : ($promComp >= 2.5 ? 'adm-prom-3' : ($promComp > 0 ? 'adm-prom-2' : 'adm-prom-0')));
                                if ($promComp > 0) { $sumaGlobal += $promComp; $cntGlobal++; }
                            ?>
                            <td style="text-align:center;">
                                <?php if ($promComp > 0): ?>
                                <span class="<?= $clsComp ?>" style="font-size:.78rem;"><?= $promComp ?></span>
                                <?php else: ?><span class="adm-prom-0">—</span><?php endif; ?>
                            </td>
                            <?php endfor; ?>
                            <td style="text-align:center;background:#dbeafe;">
                                <?php $totalGlobalProm = $cntGlobal > 0 ? round($sumaGlobal / $cntGlobal, 2) : 0;
                                $clsTotal = $totalGlobalProm >= 4.5 ? 'adm-prom-5' : ($totalGlobalProm >= 3.5 ? 'adm-prom-4' : ($totalGlobalProm >= 2.5 ? 'adm-prom-3' : ($totalGlobalProm > 0 ? 'adm-prom-2' : 'adm-prom-0'))); ?>
                                <span class="<?= $clsTotal ?>"><?= $totalGlobalProm ?: '—' ?></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- Leyenda de competencias -->
            <div style="margin-top:16px;display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($dictColab as $i => $nombre): ?>
                <span style="font-size:.72rem;background:#f1f5f9;padding:3px 8px;border-radius:6px;color:#475569;">
                    <strong>P<?= $i ?></strong> <?= htmlspecialchars(mb_substr($nombre, 0, 35)) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="adm-card">
            <div class="adm-empty">
                <div class="adm-empty-icon"><?= icon('award', 36) ?></div>
                <div>No hay evaluaciones registradas para calcular promedios.</div>
            </div>
        </div>
        <?php endif; ?>
        </div><!-- /prom-colab -->

        <!-- Promedios Líderes -->
        <div id="prom-lider" style="display:none;">
        <?php if (!empty($promedioLider) && !empty($dictLider)): ?>
        <div class="adm-card">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px;">
                <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.06em;color:var(--adm-muted);">
                    Calificación promedio por área — Evaluación de liderazgo
                </div>
                <button onclick="exportarCSV('promedios_lider', this)"
                       style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;
                              background:linear-gradient(135deg,#0058af,#2563eb);color:#fff;
                              border-radius:8px;font-size:.8rem;font-weight:600;
                              border:none;cursor:pointer;font-family:inherit;
                              flex-shrink:0;margin-left:12px;">
                    <?= icon('download', 14, '#fff') ?> Exportar Excel
                </button>
            </div>
            <div style="font-size:.78rem;color:var(--adm-muted);margin-bottom:16px;">
                Promedio de las calificaciones que los colaboradores asignaron a sus líderes, agrupado por área funcional.
            </div>
            <div style="overflow-x:auto;">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Área / Proceso</th>
                            <th style="text-align:center;">Eval.</th>
                            <?php foreach ($dictLider as $i => $nombre): ?>
                            <th style="text-align:center;min-width:46px;font-size:.68rem;"
                                title="<?= htmlspecialchars($nombre) ?>">
                                P<?= $i ?>
                            </th>
                            <?php endforeach; ?>
                            <th style="text-align:center;min-width:70px;background:#f0f4ff;color:#0058af;">
                                Promedio
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalesLider = array_fill(12, 5, ['suma' => 0, 'cnt' => 0]);
                        foreach ($promedioLider as $row):
                            $sumaFila = 0; $cntFila = 0;
                            for ($i = 12; $i <= 16; $i++) {
                                $v = (float)($row["PROM_P$i"] ?? 0);
                                if ($v > 0) {
                                    $sumaFila += $v; $cntFila++;
                                    $totalesLider[$i]['suma'] += $v;
                                    $totalesLider[$i]['cnt']++;
                                }
                            }
                            $promFila = $cntFila > 0 ? round($sumaFila / $cntFila, 2) : 0;
                            $clsFila  = $promFila >= 4.5 ? 'adm-prom-5' : ($promFila >= 3.5 ? 'adm-prom-4' : ($promFila >= 2.5 ? 'adm-prom-3' : ($promFila > 0 ? 'adm-prom-2' : 'adm-prom-0')));
                        ?>
                        <tr>
                            <td style="font-weight:600;"><?= htmlspecialchars($row['DEPENDENCIA']) ?></td>
                            <td style="text-align:center;font-family:'DM Mono',monospace;font-size:.8rem;"><?= $row['EVALUADOS'] ?></td>
                            <?php for ($i = 12; $i <= 16; $i++):
                                $val = (float)($row["PROM_P$i"] ?? 0);
                                if ($val == 0): ?>
                                <td style="text-align:center;"><span class="adm-prom-0">—</span></td>
                                <?php else:
                                    $cls = $val >= 4.5 ? 'adm-prom-5' : ($val >= 3.5 ? 'adm-prom-4' : ($val >= 2.5 ? 'adm-prom-3' : 'adm-prom-2'));
                                ?>
                                <td style="text-align:center;"><span class="<?= $cls ?>"><?= $val ?></span></td>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <td style="text-align:center;background:#f8faff;">
                                <?php if ($promFila > 0): ?>
                                <span class="<?= $clsFila ?>" style="font-size:.82rem;"><?= $promFila ?></span>
                                <?php else: ?>
                                <span class="adm-prom-0">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#f0f4ff;border-top:2px solid #bfdbfe;">
                            <td style="font-weight:700;font-size:.78rem;color:#0058af;">Promedio general</td>
                            <td></td>
                            <?php
                            $sumaGlobalL = 0; $cntGlobalL = 0;
                            for ($i = 12; $i <= 16; $i++):
                                $promComp = $totalesLider[$i]['cnt'] > 0
                                    ? round($totalesLider[$i]['suma'] / $totalesLider[$i]['cnt'], 2) : 0;
                                $clsComp = $promComp >= 4.5 ? 'adm-prom-5' : ($promComp >= 3.5 ? 'adm-prom-4' : ($promComp >= 2.5 ? 'adm-prom-3' : ($promComp > 0 ? 'adm-prom-2' : 'adm-prom-0')));
                                if ($promComp > 0) { $sumaGlobalL += $promComp; $cntGlobalL++; }
                            ?>
                            <td style="text-align:center;">
                                <?php if ($promComp > 0): ?>
                                <span class="<?= $clsComp ?>" style="font-size:.78rem;"><?= $promComp ?></span>
                                <?php else: ?><span class="adm-prom-0">—</span><?php endif; ?>
                            </td>
                            <?php endfor; ?>
                            <td style="text-align:center;background:#e0e7ff;">
                                <?php $totalGlobalL = $cntGlobalL > 0 ? round($sumaGlobalL / $cntGlobalL, 2) : 0;
                                $clsTotalL = $totalGlobalL >= 4.5 ? 'adm-prom-5' : ($totalGlobalL >= 3.5 ? 'adm-prom-4' : ($totalGlobalL >= 2.5 ? 'adm-prom-3' : ($totalGlobalL > 0 ? 'adm-prom-2' : 'adm-prom-0'))); ?>
                                <span class="<?= $clsTotalL ?>"><?= $totalGlobalL ?: '—' ?></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- Leyenda competencias liderazgo -->
            <div style="margin-top:16px;display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($dictLider as $i => $nombre): ?>
                <span style="font-size:.72rem;background:#f1f5f9;padding:3px 8px;border-radius:6px;color:#475569;">
                    <strong>P<?= $i ?></strong> <?= htmlspecialchars(mb_substr($nombre, 0, 35)) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="adm-card">
            <div class="adm-empty">
                <div class="adm-empty-icon"><?= icon('award', 36) ?></div>
                <div>No hay evaluaciones de liderazgo registradas.</div>
            </div>
        </div>
        <?php endif; ?>
        </div><!-- /prom-lider -->

    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════
         PANEL — USUARIOS Y PERMISOS
    ══════════════════════════════════ -->
    <?php if ($activeTab === 'usuarios'): ?>
    <div id="adm-usuarios" class="adm-panel active">
        <div class="adm-card">
            <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                        letter-spacing:.06em;color:var(--adm-muted);margin-bottom:16px;">
                Buscar y gestionar permisos de usuario
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="buscarUsuarios">
                <input type="hidden" name="activeTab" value="usuarios">
                <div class="adm-search">
                    <input type="text" name="searchTerm"
                           value="<?= htmlspecialchars($searchTermUsuarios) ?>"
                           placeholder="Nombre o número de cédula…">
                    <button type="submit" class="adm-btn adm-btn-primary"><?= icon('search', 13) ?> Buscar</button>
                </div>
            </form>

            <?php if (!empty($resultadosUsuarios)): ?>
            <div style="overflow-x:auto;">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th style="text-align:center;">Es Admin</th>
                            <th style="text-align:center;">Ver detalle reportes</th>
                            <th style="text-align:center;">Estado cuenta</th>
                            <th style="text-align:center;">Guardar</th>
                            <th style="text-align:center;">Reset clave</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultadosUsuarios as $u): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;"><?= htmlspecialchars($u['NOMBRE']) ?></div>
                                <div style="font-size:.74rem;color:var(--adm-muted);font-family:'DM Mono',monospace;">
                                    <?= $u['IDENTIFICACION'] ?>
                                </div>
                                <div style="font-size:.74rem;color:var(--adm-muted);"><?= htmlspecialchars($u['CARGO']) ?></div>
                            </td>
                            <td style="text-align:center;">
                                <form method="POST" id="form-u-<?= $u['IDUSUARIO'] ?>">
                                    <input type="hidden" name="action" value="actualizarPermisos">
                                    <input type="hidden" name="idUsuario" value="<?= $u['IDUSUARIO'] ?>">
                                    <input type="hidden" name="searchTerm" value="<?= htmlspecialchars($searchTermUsuarios) ?>">
                                    <input type="hidden" name="activeTab" value="usuarios">
                                    <label class="adm-toggle" style="justify-content:center;">
                                        <input type="checkbox" name="esadmin"
                                               <?= $u['ESADMIN'] == 1 ? 'checked' : '' ?>>
                                    </label>
                            </td>
                            <td style="text-align:center;">
                                    <label class="adm-toggle" style="justify-content:center;">
                                        <input type="checkbox" name="ver_detalle"
                                               <?= $u['VER_DETALLE_REP'] == 1 ? 'checked' : '' ?>>
                                    </label>
                            </td>
                            <td style="text-align:center;">
                                <span class="adm-badge <?= $u['CUENTA_ACTIVA'] == 1 ? 'adm-badge-ok' : 'adm-badge-no' ?>">
                                    <?= $u['CUENTA_ACTIVA'] == 1 ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                    <button type="submit" form="form-u-<?= $u['IDUSUARIO'] ?>"
                                            class="adm-btn adm-btn-primary adm-btn-sm">
                                        <?= icon('save', 15) ?>
                                    </button>
                                </form>
                            </td>
                            <td style="text-align:center;">
                                <button type="button"
                                        onclick="confirmarReset(<?= $u['IDUSUARIO'] ?>, '<?= htmlspecialchars($u['IDENTIFICACION']) ?>', '<?= htmlspecialchars($u['NOMBRE'], ENT_QUOTES) ?>')"
                                        class="adm-btn adm-btn-ghost adm-btn-sm"
                                        title="Resetear contraseña a número de identificación"
                                        <?= $u['CUENTA_ACTIVA'] != 1 ? 'disabled style="opacity:.4;cursor:not-allowed;"' : '' ?>>
                                    <?= icon('key', 15) ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($searchTermUsuarios): ?>
            <div class="adm-empty">
                <div class="adm-empty-icon"><?= icon('search', 36) ?></div>
                <div>No se encontraron usuarios para "<?= htmlspecialchars($searchTermUsuarios) ?>"</div>
            </div>
            <?php else: ?>
            <div class="adm-empty">
                <div class="adm-empty-icon"><?= icon('users', 36) ?></div>
                <div>Busca un colaborador por nombre o cédula para gestionar sus permisos.</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>


    <!-- ══════════════════════════════════
         PANEL — OBJETIVOS DE MEJORA
    ══════════════════════════════════ -->
    <?php if ($activeTab === 'objetivos'): ?>
    <div id="adm-objetivos" class="adm-panel active">
        <div class="adm-grid2">

            <!-- Formulario crear/editar objetivo -->
            <div class="adm-card">
                <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.06em;color:var(--adm-muted);margin-bottom:16px;"
                     id="objFormTitle"><?= icon('target', 13) ?> Nuevo objetivo SMART
                </div>
                <form method="POST" id="formObjetivo">
                    <input type="hidden" name="action"    id="objAction"    value="crearObjetivo">
                    <input type="hidden" name="idObjetivo" id="objIdObjeto"  value="">
                    <input type="hidden" name="activeTab" value="objetivos">

                    <div class="adm-form-group">
                        <label class="adm-label">Competencia *</label>
                        <select name="num_competencia" id="objCompetencia" class="adm-input" required>
                            <option value="">Selecciona una competencia...</option>
                            <?php foreach ($dictColab as $i => $nombre): ?>
                            <option value="<?= $i ?>">P<?= $i ?> — <?= htmlspecialchars($nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="adm-form-group">
                            <label class="adm-label">Calificación *</label>
                            <select name="calificacion" id="objCalificacion" class="adm-input" required>
                                <option value="">Selecciona...</option>
                                <option value="1">1 — Insuficiente</option>
                                <option value="2">2 — Requiere mejora</option>
                                <option value="3">3 — Aceptable</option>
                                <option value="4">4 — Acorde</option>
                                <option value="5">5 — Sobresaliente</option>
                            </select>
                        </div>
                        <div class="adm-form-group">
                            <label class="adm-label">Modelo</label>
                            <select name="modelo" id="objModelo" class="adm-input">
                                <option value="">Auto (por calificación)</option>
                                <option value="Cierre de brecha">Cierre de brecha</option>
                                <option value="Consolidación">Consolidación</option>
                                <option value="Expansión / referente">Expansión / referente</option>
                            </select>
                        </div>
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-label">Objetivo de mejora *</label>
                        <textarea name="objetivo" id="objObjetivo" class="adm-input" rows="2"
                                  placeholder="Describe el objetivo..." style="resize:vertical;"
                                  required maxlength="500"></textarea>
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-label">Indicador</label>
                        <textarea name="indicador" id="objIndicador" class="adm-input" rows="2"
                                  placeholder="¿Cómo se mide el logro?" style="resize:vertical;"
                                  maxlength="500"></textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="adm-form-group">
                            <label class="adm-label">Meta</label>
                            <input type="text" name="meta" id="objMeta" class="adm-input"
                                   placeholder="Ej: ≥ 90%" maxlength="300">
                        </div>
                        <div class="adm-form-group">
                            <label class="adm-label">Plazo</label>
                            <input type="text" name="plazo" id="objPlazo" class="adm-input"
                                   placeholder="Ej: 3 meses" maxlength="200">
                        </div>
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-label">Evidencia</label>
                        <input type="text" name="evidencia" id="objEvidencia" class="adm-input"
                               placeholder="¿Qué evidencia demuestra el logro?" maxlength="500">
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-label">Seguimiento sugerido</label>
                        <input type="text" name="seguimiento" id="objSeguimiento" class="adm-input"
                               placeholder="Ej: Mensual, Trimestral..." maxlength="300">
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-label">Uso recomendado</label>
                        <input type="text" name="uso_recomendado" id="objUsoRecomendado" class="adm-input"
                               placeholder="Ej: Para líderes de área" maxlength="100">
                    </div>

                    <div style="display:flex;gap:10px;margin-top:4px;">
                        <button type="submit" class="adm-btn adm-btn-primary" style="flex:1;">
                            <?= icon('check', 13) ?> <span id="objBtnLabel">Crear objetivo</span>
                        </button>
                        <button type="button" onclick="resetFormObjetivo()"
                                class="adm-btn adm-btn-ghost" style="min-width:80px;">
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de objetivos -->
            <div class="adm-card">
                <div style="font-size:.82rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.06em;color:var(--adm-muted);margin-bottom:10px;">
                    Objetivos registrados
                </div>
                <div style="display:flex;gap:8px;margin-bottom:12px;">
                    <select id="filtroCompObj" onchange="filtrarObjetivos()"
                            style="flex:1;padding:8px 12px;border:1.5px solid var(--adm-border);
                                   border-radius:10px;font-size:.82rem;font-family:inherit;outline:none;">
                        <option value="">Todas las competencias</option>
                        <?php foreach ($dictColab as $i => $nombre): ?>
                        <option value="<?= $i ?>">P<?= $i ?> — <?= htmlspecialchars(mb_substr($nombre, 0, 35)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filtroCalObj" onchange="filtrarObjetivos()"
                            style="width:120px;padding:8px 10px;border:1.5px solid var(--adm-border);
                                   border-radius:10px;font-size:.82rem;font-family:inherit;outline:none;">
                        <option value="">Todas</option>
                        <option value="1">1 — Insuficiente</option>
                        <option value="2">2 — Requiere mejora</option>
                        <option value="3">3 — Aceptable</option>
                        <option value="4">4 — Acorde</option>
                        <option value="5">5 — Sobresaliente</option>
                    </select>
                </div>
                <?php if (!empty($objetivos)): ?>
                <div style="max-height:560px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;"
                     id="listaObjetivos">
                    <?php
                    $calColors = [
                        1 => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'Insuficiente'],
                        2 => ['bg'=>'#fef3c7','color'=>'#92400e','label'=>'Requiere mejora'],
                        3 => ['bg'=>'#fef9c3','color'=>'#713f12','label'=>'Aceptable'],
                        4 => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'Acorde'],
                        5 => ['bg'=>'#dbeafe','color'=>'#1e40af','label'=>'Sobresaliente'],
                    ];
                    foreach ($objetivos as $obj):
                        $cc       = $calColors[(int)$obj['CALIFICACION']] ?? ['bg'=>'#f1f5f9','color'=>'#475569','label'=>''];
                        $esActivo = $obj['ACTIVO'] == 1;
                        $modelo   = $obj['MODELO'] ?? '';
                    ?>
                    <div class="obj-item"
                         data-comp="<?= $obj['NUM_COMPETENCIA'] ?>"
                         data-cal="<?= $obj['CALIFICACION'] ?>"
                         style="border:1.5px solid var(--adm-border);border-radius:10px;
                                padding:12px 14px;<?= $esActivo ? '' : 'opacity:.5;' ?>">
                        <!-- Badges -->
                        <div style="display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap;align-items:center;">
                            <span style="font-size:.7rem;font-weight:700;padding:2px 8px;
                                         border-radius:20px;background:#dbeafe;color:#0058af;">
                                P<?= $obj['NUM_COMPETENCIA'] ?>
                            </span>
                            <span style="font-size:.7rem;font-weight:700;padding:2px 8px;
                                         border-radius:20px;background:<?= $cc['bg'] ?>;color:<?= $cc['color'] ?>;">
                                <?= $obj['CALIFICACION'] ?> · <?= $cc['label'] ?>
                            </span>
                            <?php if ($modelo): ?>
                            <span style="font-size:.7rem;padding:2px 8px;border-radius:20px;
                                         background:#f0fdf4;color:#166534;"><?= htmlspecialchars($modelo) ?></span>
                            <?php endif; ?>
                            <?php if (!$esActivo): ?>
                            <span style="font-size:.7rem;padding:2px 8px;border-radius:20px;
                                         background:#f1f5f9;color:#94a3b8;">Inactivo</span>
                            <?php endif; ?>
                        </div>
                        <!-- Objetivo -->
                        <div style="font-size:.83rem;color:#1e293b;font-weight:500;margin-bottom:6px;">
                            <?= htmlspecialchars($obj['OBJETIVO']) ?>
                        </div>
                        <!-- Campos SMART resumidos -->
                        <?php if (!empty($obj['INDICADOR']) || !empty($obj['META']) || !empty($obj['PLAZO'])): ?>
                        <div style="font-size:.75rem;color:#64748b;display:flex;gap:12px;flex-wrap:wrap;margin-bottom:6px;">
                            <?php if (!empty($obj['INDICADOR'])): ?>
                            <span><strong>Indicador:</strong> <?= htmlspecialchars(mb_substr($obj['INDICADOR'],0,50)) ?>...</span>
                            <?php endif; ?>
                            <?php if (!empty($obj['META'])): ?>
                            <span><strong>Meta:</strong> <?= htmlspecialchars($obj['META']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($obj['PLAZO'])): ?>
                            <span><strong>Plazo:</strong> <?= htmlspecialchars($obj['PLAZO']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <!-- Acciones -->
                        <div style="display:flex;gap:6px;margin-top:6px;">
                            <button type="button"
                                onclick="editarObjetivo(<?= htmlspecialchars(json_encode($obj), ENT_QUOTES) ?>)"
                                class="adm-btn adm-btn-ghost adm-btn-sm">
                                <?= icon('pen', 12) ?> Editar
                            </button>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action"    value="toggleObjetivo">
                                <input type="hidden" name="idObjetivo" value="<?= $obj['IDOBJETIVO'] ?>">
                                <input type="hidden" name="activo"    value="<?= $esActivo ? 0 : 1 ?>">
                                <input type="hidden" name="activeTab" value="objetivos">
                                <button type="submit" class="adm-btn adm-btn-ghost adm-btn-sm"
                                        title="<?= $esActivo ? 'Desactivar' : 'Activar' ?>">
                                    <?= $esActivo ? icon('x-circle', 12).'  Desactivar' : icon('check-circle', 12).' Activar' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="adm-empty">
                    <div class="adm-empty-icon"><?= icon('target', 36) ?></div>
                    <div>No hay objetivos registrados aún.</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════
         PANEL — COMPETENCIAS
    ══════════════════════════════════ -->
    <?php if ($activeTab === 'competencias'): ?>
    <div id="adm-competencias" class="adm-panel active">

        <?php if (empty($competencias)): ?>
        <div class="adm-card">
            <div class="adm-empty">
                <div class="adm-empty-icon"><?= icon('list', 36) ?></div>
                <div>No hay competencias registradas.</div>
            </div>
        </div>
        <?php else:
            // Agrupar por dimensión
            $porDimension = [];
            foreach ($competencias as $comp) {
                $porDimension[$comp['NOMBRE_DIMENSION']][] = $comp;
            }
            foreach ($porDimension as $dimNombre => $comps):
        ?>
        <div class="adm-card" style="margin-bottom:16px;">
            <!-- Encabezado dimensión -->
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;
                        padding-bottom:12px;border-bottom:2px solid var(--adm-border);">
                <div style="width:4px;height:22px;background:var(--adm-accent);border-radius:2px;"></div>
                <div style="font-size:.88rem;font-weight:700;color:#1e3a5f;">
                    <?= htmlspecialchars($dimNombre) ?>
                </div>
                <div style="font-size:.75rem;color:var(--adm-muted);margin-left:auto;">
                    <?= count($comps) ?> competencias
                </div>
            </div>

            <!-- Lista de competencias -->
            <?php foreach ($comps as $comp):
                $esActivo = $comp['ACTIVO'] == 1;
                $opciones = $opcionesComp[(int)$comp['IDCOMPETENCIA']] ?? [];
            ?>
            <div style="border:1.5px solid var(--adm-border);border-radius:12px;
                        margin-bottom:10px;overflow:hidden;
                        <?= $esActivo ? '' : 'opacity:.55;' ?>">

                <!-- Header competencia -->
                <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;
                            background:#f8fafc;cursor:pointer;user-select:none;"
                     onclick="toggleCompPanel(<?= $comp['IDCOMPETENCIA'] ?>)">
                    <span style="background:#dbeafe;color:#0058af;font-size:.72rem;
                                 font-weight:700;padding:3px 8px;border-radius:20px;
                                 min-width:32px;text-align:center;">
                        P<?= $comp['NUM_PREGUNTA'] ?>
                    </span>
                    <div style="flex:1;font-size:.85rem;font-weight:600;color:#1e293b;">
                        <?= htmlspecialchars($comp['NOMBRE']) ?>
                    </div>
                    <?php if (!$esActivo): ?>
                    <span style="font-size:.7rem;padding:2px 8px;border-radius:20px;
                                 background:#f1f5f9;color:#94a3b8;">Inactiva</span>
                    <?php endif; ?>
                    <span style="font-size:.75rem;color:var(--adm-muted);" id="arr-<?= $comp['IDCOMPETENCIA'] ?>">▼</span>
                </div>

                <!-- Cuerpo editable (colapsable) -->
                <div id="comp-panel-<?= $comp['IDCOMPETENCIA'] ?>"
                     style="display:none;padding:16px;">

                    <!-- Editar nombre y pregunta -->
                    <form method="POST" style="margin-bottom:10px;">
                        <input type="hidden" name="action" value="editarCompetencia">
                        <input type="hidden" name="activeTab" value="competencias">
                        <input type="hidden" name="idCompetencia" value="<?= $comp['IDCOMPETENCIA'] ?>">
                        <div class="adm-form-group">
                            <label class="adm-label">Nombre de la competencia</label>
                            <input type="text" name="nombre" class="adm-input"
                                   value="<?= htmlspecialchars($comp['NOMBRE']) ?>"
                                   required maxlength="200">
                        </div>
                        <div class="adm-form-group">
                            <label class="adm-label">Pregunta orientadora</label>
                            <textarea name="pregunta" class="adm-input" rows="2"
                                      style="resize:vertical;" maxlength="500"><?= htmlspecialchars($comp['PREGUNTA'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm">
                            <?= icon('save', 13) ?> Guardar cambios
                        </button>
                    </form>

                    <!-- Activar / Inactivar — form separado -->
                    <form method="POST" style="margin-bottom:14px;">
                        <input type="hidden" name="action" value="toggleCompetencia">
                        <input type="hidden" name="activeTab" value="competencias">
                        <input type="hidden" name="idCompetencia" value="<?= $comp['IDCOMPETENCIA'] ?>">
                        <input type="hidden" name="activo" value="<?= $esActivo ? 0 : 1 ?>">
                        <button type="submit" class="adm-btn adm-btn-ghost adm-btn-sm">
                            <?= $esActivo ? icon('x-circle',13).' Inactivar' : icon('check-circle',13).' Activar' ?>
                        </button>
                    </form>

                    <!-- Editar descripciones de opciones -->
                    <?php if (!empty($opciones)): ?>
                    <div style="border-top:1px solid var(--adm-border);padding-top:14px;margin-top:4px;">
                        <div style="font-size:.78rem;font-weight:700;color:var(--adm-muted);
                                    text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">
                            Descripciones por nivel (tooltips)
                        </div>
                        <?php foreach ($opciones as $op): ?>
                        <form method="POST" style="margin-bottom:10px;">
                            <input type="hidden" name="action" value="editarOpcionComp">
                            <input type="hidden" name="activeTab" value="competencias">
                            <input type="hidden" name="idOpcionComp" value="<?= $op['IDOPCIONCOMP'] ?>">
                            <div style="display:flex;gap:8px;align-items:flex-start;">
                                <span style="background:#f0fdf4;color:#166534;font-size:.7rem;
                                             font-weight:700;padding:3px 8px;border-radius:6px;
                                             margin-top:6px;white-space:nowrap;min-width:90px;text-align:center;">
                                    <?= htmlspecialchars($op['ETIQUETA']) ?>
                                </span>
                                <textarea name="descripcion" class="adm-input" rows="2"
                                          style="resize:vertical;flex:1;font-size:.8rem;"
                                          maxlength="1000"><?= htmlspecialchars($op['DESCRIPCION'] ?? '') ?></textarea>
                                <button type="submit" class="adm-btn adm-btn-ghost adm-btn-sm"
                                        style="margin-top:4px;" title="Guardar">
                                    <?= icon('save', 13) ?>
                                </button>
                            </div>
                        </form>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; endif; ?>
    </div>
    <?php endif; ?>


    <!-- ══════════════════════════════════
         PANEL — COLABORADORES (HUMEMPLEADOEVAL)
    ══════════════════════════════════ -->
    <?php if ($activeTab === 'colaboradores'): ?>
    <div id="adm-colaboradores" class="adm-panel active">

        <!-- Barra de búsqueda y filtros -->
        <div class="adm-card" style="margin-bottom:16px;">
            <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <input type="hidden" name="views" value="admin">
                <input type="hidden" name="tab"   value="colaboradores">
                <div style="flex:1;min-width:200px;">
                    <label class="adm-label">Buscar por nombre, cédula o cargo</label>
                    <input type="text" name="buscarEval" class="adm-input"
                           value="<?= htmlspecialchars($buscarEval) ?>"
                           placeholder="Ej: PEREZ, 1067895156, AUXILIAR...">
                </div>
                <div>
                    <label class="adm-label">Estado</label>
                    <select name="soloActivos" class="adm-input" style="width:130px;">
                        <option value="1" <?= $soloActivos ? 'selected' : '' ?>>Solo activos</option>
                        <option value="0" <?= !$soloActivos ? 'selected' : '' ?>>Todos</option>
                    </select>
                </div>
                <button type="submit" class="adm-btn adm-btn-primary">
                    <?= icon('search', 13) ?> Buscar
                </button>
                <?php if ($buscarEval): ?>
                <a href="<?= APP_URL ?>admin/?tab=colaboradores"
                   class="adm-btn adm-btn-ghost">Limpiar</a>
                <?php endif; ?>
                <button type="button" onclick="abrirCrearEval()"
                        class="adm-btn adm-btn-primary" style="margin-left:auto;">
                    <?= icon('user-plus', 13) ?> Agregar colaborador
                </button>
            </form>
            <div style="margin-top:10px;font-size:.78rem;color:var(--adm-muted);">
                <?= count($empleadosEval) ?> registros encontrados &nbsp;·&nbsp;
                <span style="color:#0058af;font-weight:600;">
                    <?= array_sum(array_column($empleadosEval, 'APLICA_EXP_AZUL')) ?> con Experiencia Azul
                </span>
            </div>
        </div>

        <!-- Tabla de colaboradores -->
        <div class="adm-card" style="padding:0;overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="adm-table" style="font-size:.78rem;">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Cargo</th>
                            <th style="text-align:center;">Rol</th>
                            <th style="text-align:center;">Exp. Azul</th>
                            <th style="text-align:center;">Líder Func.</th>
                            <th style="text-align:center;">Director</th>
                            <th>Proceso</th>
                            <th>Jefe</th>
                            <th style="text-align:center;">Estado</th>
                            <th style="text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($empleadosEval)): ?>
                        <tr><td colspan="9" style="text-align:center;padding:24px;color:var(--adm-muted);">
                            Sin resultados.
                        </td></tr>
                    <?php else: ?>
                        <?php
                        $rolColors = [
                            1 => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                            2 => ['bg'=>'#f3e8ff','color'=>'#6b21a8'],
                            3 => ['bg'=>'#dcfce7','color'=>'#166534'],
                        ];
                        foreach ($empleadosEval as $ev):
                            $rc       = $rolColors[(int)$ev['IDROL']] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
                            $esActivo = $ev['ACTIVO'] == 1;
                        ?>
                        <tr id="ev-row-<?= $ev['IDASIGNACION'] ?>"
                            style="<?= $esActivo ? '' : 'opacity:.5;' ?>">
                            <td style="font-weight:600;">
                                <?= htmlspecialchars($ev['NOMBRE'] ?? '—') ?>
                            </td>
                            <td style="font-family:'DM Mono',monospace;">
                                <?= htmlspecialchars($ev['IDENTIFICACION'] ?? '') ?>
                            </td>
                            <td><?= htmlspecialchars($ev['CARGO'] ?? '—') ?></td>
                            <td style="text-align:center;">
                                <span style="padding:2px 8px;border-radius:20px;font-size:.7rem;
                                             font-weight:700;background:<?= $rc['bg'] ?>;color:<?= $rc['color'] ?>;">
                                    <?= htmlspecialchars($ev['ROL_NOMBRE'] ?? '') ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($ev['APLICA_EXP_AZUL'] == 1): ?>
                                <span style="color:#0058af;font-weight:700;">✓</span>
                                <?php else: ?>
                                <span style="color:#cbd5e1;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($ev['ES_LIDER_FUNCIONAL'] == 1): ?>
                                <span style="color:#059669;font-weight:700;" title="Líder funcional — tiene subordinados">✓</span>
                                <?php else: ?>
                                <span style="color:#cbd5e1;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($ev['ES_DIRECTOR'] == 1): ?>
                                <span style="color:#7c3aed;font-weight:700;" title="Director — puede dar feedback a líderes">✓</span>
                                <?php else: ?>
                                <span style="color:#cbd5e1;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?= htmlspecialchars($ev['PROCESO'] ?? '') ?>
                            </td>
                            <td style="font-size:.74rem;color:var(--adm-muted);">
                                <?= htmlspecialchars(mb_substr($ev['NOMBRE_JEFE'] ?? '', 0, 25)) ?>
                                <?= strlen($ev['NOMBRE_JEFE'] ?? '') > 25 ? '...' : '' ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($esActivo): ?>
                                <span style="background:#dcfce7;color:#166534;padding:2px 8px;
                                             border-radius:20px;font-size:.7rem;font-weight:700;">Activo</span>
                                <?php else: ?>
                                <span style="background:#f1f5f9;color:#94a3b8;padding:2px 8px;
                                             border-radius:20px;font-size:.7rem;">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <button onclick="abrirEditarEval(<?= htmlspecialchars(json_encode($ev), ENT_QUOTES) ?>)"
                                        class="adm-btn adm-btn-ghost adm-btn-sm">
                                    <?= icon('pen', 12) ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal crear colaborador eval -->
    <div id="modalCrearEval" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
         z-index:10000;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:min(500px,95vw);
                    box-shadow:0 20px 60px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto;">
            <div style="font-weight:700;font-size:.95rem;color:#1e3a5f;margin-bottom:16px;">
                <?= icon('user-plus', 16) ?> Agregar colaborador
            </div>

            <!-- Paso 1: buscar por cédula -->
            <div id="crearEvalPaso1">
                <div class="adm-form-group">
                    <label class="adm-label">Cédula del colaborador *</label>
                    <div style="display:flex;gap:8px;">
                        <input type="text" id="crearEvalCedula" class="adm-input"
                               placeholder="Ingresa el número de cédula"
                               style="flex:1;" maxlength="20">
                        <button type="button" onclick="buscarEmpleadoEval()"
                                class="adm-btn adm-btn-primary">
                            <?= icon('search', 13) ?> Buscar
                        </button>
                    </div>
                    <div id="crearEvalMsg" style="font-size:.78rem;margin-top:6px;"></div>
                </div>
            </div>

            <!-- Paso 2: formulario completo -->
            <form method="POST" id="formCrearEval" style="display:none;"
                  onsubmit="document.getElementById('crearEvalExpAzulH').value = document.getElementById('crearEvalExpAzul').checked ? 1 : 0;">
                <input type="hidden" name="action"        value="crearEmpleadoEval">
                <input type="hidden" name="activeTab"     value="colaboradores">
                <input type="hidden" name="idempleado"    id="crearEvalIdEmp">
                <input type="hidden" name="identificacion" id="crearEvalIdentH">
                <input type="hidden" name="nombre"        id="crearEvalNombreH">
                <input type="hidden" name="aplicaExpAzul" id="crearEvalExpAzulH" value="0">

                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;
                            padding:10px 14px;margin-bottom:14px;font-size:.82rem;color:#166534;">
                    <?= icon('check-circle', 14) ?>
                    <strong id="crearEvalNombreShow"></strong>
                    <span style="color:#64748b;font-size:.75rem;" id="crearEvalCedulaShow"></span>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="adm-form-group">
                        <label class="adm-label">Rol *</label>
                        <select name="idRol" id="crearEvalRol" class="adm-input" required>
                            <option value="1">Asistencial</option>
                            <option value="2">Administrativo</option>
                            <option value="3">Líder</option>
                        </select>
                    </div>
                    <div class="adm-form-group">
                        <label class="adm-label">Proceso</label>
                        <input type="text" name="proceso" class="adm-input" maxlength="10">
                    </div>
                </div>

                <div class="adm-form-group">
                    <label class="adm-label">Cargo</label>
                    <input type="text" name="cargo" class="adm-input" maxlength="200">
                </div>

                <div class="adm-form-group">
                    <label class="adm-label">Nombre del jefe directo</label>
                    <input type="text" name="nombreJefe" id="crearEvalNombreJefe"
                           class="adm-input" maxlength="200"
                           placeholder="Nombre completo del jefe">
                </div>

                <div class="adm-form-group">
                    <label class="adm-label" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="aplicaExpAzulCheck" id="crearEvalExpAzul"
                               style="width:16px;height:16px;">
                        Aplica Experiencia Azul
                    </label>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="adm-form-group">
                        <label class="adm-label">Email</label>
                        <input type="email" name="email" class="adm-input" maxlength="100">
                    </div>
                    <div class="adm-form-group">
                        <label class="adm-label">Celular</label>
                        <input type="text" name="celular" class="adm-input" maxlength="20">
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" class="adm-btn adm-btn-primary" style="flex:1;">
                        <?= icon('save', 13) ?> Guardar
                    </button>
                    <button type="button" onclick="cerrarCrearEval()"
                            class="adm-btn adm-btn-ghost">Cancelar</button>
                </div>
            </form>

            <?php if (!isset($formCrearEval)): ?>
            <div style="text-align:right;margin-top:8px;">
                <button type="button" onclick="cerrarCrearEval()"
                        style="background:none;border:none;color:var(--adm-muted);
                               font-size:.82rem;cursor:pointer;">✕ Cerrar</button>
            </div>
            <?php endif; ?>
        </div>
    </div>


    <!-- Modal editar colaborador eval -->
    <div id="modalEditarEval" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
         z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:min(500px,95vw);
                    box-shadow:0 20px 60px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto;">
            <div style="font-weight:700;font-size:.95rem;color:#1e3a5f;margin-bottom:4px;"
                 id="evalModalNombre"></div>
            <div style="font-size:.78rem;color:var(--adm-muted);margin-bottom:18px;"
                 id="evalModalCedula"></div>
            <form method="POST" id="formEditarEval"
                      onsubmit="
                          document.getElementById('evalExpAzulHidden').value = document.getElementById('evalExpAzul').checked ? 1 : 0;
                          document.getElementById('evalEsDirectorHidden').value = document.getElementById('evalEsDirector').checked ? 1 : 0;
                          return true;
                      ">
                <input type="hidden" name="aplicaExpAzul" id="evalExpAzulHidden" value="0">
                <input type="hidden" name="esDirector"    id="evalEsDirectorHidden" value="0">
                <input type="hidden" name="action"       value="editarEmpleadoEval">
                <input type="hidden" name="activeTab"    value="colaboradores">
                <input type="hidden" name="idAsignacion" id="evalIdAsignacion">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="adm-form-group">
                        <label class="adm-label">Rol *</label>
                        <select name="idRol" id="evalIdRol" class="adm-input" required>
                            <option value="1">Asistencial</option>
                            <option value="2">Administrativo</option>
                            <option value="3">Líder</option>
                        </select>
                    </div>
                    <div class="adm-form-group">
                        <label class="adm-label">Proceso</label>
                        <input type="text" name="proceso" id="evalProceso"
                               class="adm-input" maxlength="10">
                    </div>
                </div>

                <div class="adm-form-group">
                    <label class="adm-label">Cargo</label>
                    <input type="text" name="cargo" id="evalCargo"
                           class="adm-input" maxlength="200">
                </div>

                <div class="adm-form-group">
                    <label class="adm-label">Jefe directo</label>
                    <select name="idJefe" id="evalIdJefe" class="adm-input"
                            onchange="actualizarNombreJefe(this)">
                        <option value="0">— Sin jefe asignado —</option>
                        <?php foreach ($lideresEval as $lider): ?>
                        <option value="<?= $lider['IDEMPLEADO'] ?>"
                                data-nombre="<?= htmlspecialchars($lider['NOMBRE'], ENT_QUOTES) ?>">
                            <?= htmlspecialchars($lider['NOMBRE']) ?>
                            <?php if ($lider['PROCESO']): ?>· <?= htmlspecialchars($lider['PROCESO']) ?><?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="nombreJefe" id="evalNombreJefe">
                </div>

                <div class="adm-form-group">
                    <label class="adm-label" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="aplicaExpAzul" id="evalExpAzul" value="1"
                               style="width:16px;height:16px;">
                        Aplica Experiencia Azul
                    </label>
                    <div style="font-size:.74rem;color:var(--adm-muted);margin-top:4px;">
                        Marca si este colaborador participa en la evaluación de Experiencia Azul.
                    </div>
                </div>

                <div class="adm-form-group">
                    <label class="adm-label" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="esDirector" id="evalEsDirector" value="1"
                               style="width:16px;height:16px;">
                        Es Director
                    </label>
                    <div style="font-size:.74rem;color:var(--adm-muted);margin-top:4px;">
                        Puede dar feedback a los líderes bajo su cargo.
                    </div>
                </div>

                <div id="evalLiderFuncionalBadge" style="display:none;margin-bottom:10px;">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;
                                padding:8px 12px;font-size:.78rem;color:#166534;">
                        ✓ <strong>Líder funcional</strong> — tiene colaboradores a cargo
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div class="adm-form-group">
                        <label class="adm-label">Email</label>
                        <input type="email" name="email" id="evalEmail"
                               class="adm-input" maxlength="100">
                    </div>
                    <div class="adm-form-group">
                        <label class="adm-label">Celular</label>
                        <input type="text" name="celular" id="evalCelular"
                               class="adm-input" maxlength="20">
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button type="submit" class="adm-btn adm-btn-primary" style="flex:1;">
                        <?= icon('save', 13) ?> Guardar cambios
                    </button>
                    <button type="button" id="toggleEvalBtn"
                            onclick="submitToggleEval()"
                            class="adm-btn adm-btn-ghost">
                        Inactivar
                    </button>
                    <button type="button" onclick="cerrarEditarEval()"
                            class="adm-btn adm-btn-ghost">
                        Cancelar
                    </button>
                </div>
            </form>

            <!-- Form toggle FUERA del form editar -->
            <form method="POST" style="display:none;" id="formToggleEval">
                <input type="hidden" name="action"       value="toggleEmpleadoEval">
                <input type="hidden" name="activeTab"    value="colaboradores">
                <input type="hidden" name="idAsignacion" id="toggleEvalId">
                <input type="hidden" name="activo"       id="toggleEvalActivo">
            </form>
        </div>
    </div>
    <?php endif; ?>


</div><!-- /adm-wrap -->
</div><!-- /adm-root -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const appUrl = '<?= APP_URL ?>';
(function(){
    Chart.defaults.font.family = "'DM Sans', sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#64748b';

    // Gráfica de avance por proceso
    const cvAvance = document.getElementById('chartAvance');
    if (cvAvance) {
        const labels  = <?= json_encode(array_values($chartLabels)) ?>;
        const pctAuto = <?= json_encode(array_values($chartPctAuto)) ?>;
        const pctComp = <?= json_encode(array_values($chartPctComp)) ?>;

        new Chart(cvAvance, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: '% Autoevaluación',
                        data: pctAuto,
                        backgroundColor: 'rgba(245,158,11,.7)',
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        borderRadius: 5,
                    },
                    {
                        label: '% Completo',
                        data: pctComp,
                        backgroundColor: 'rgba(16,185,129,.7)',
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                        borderRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top',
                              labels: { boxWidth: 12, padding: 14 } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { min: 0, max: 100, ticks: { callback: v => v + '%' },
                         grid: { color: '#f1f5f9' } }
                }
            }
        });
    }

    // Cambio de pestañas
    // ── Subtabs promedios ────────────────────────────────────────────────────
    window.admSubTab = function(id, btn) {
        ['prom-colab','prom-lider'].forEach(function(tab) {
            const el = document.getElementById(tab);
            if (el) el.style.display = tab === id ? '' : 'none';
        });
        document.querySelectorAll('[id^="btn-prom-"]').forEach(function(b) {
            b.style.background = '#f1f5f9';
            b.style.color      = 'var(--adm-muted)';
        });
        btn.style.background = 'var(--adm-accent)';
        btn.style.color      = '#fff';
    };


    // ── Crear colaborador HUMEMPLEADOEVAL ───────────────────────────────────────
    window.abrirCrearEval = function() {
        document.getElementById('crearEvalPaso1').style.display   = '';
        document.getElementById('formCrearEval').style.display    = 'none';
        document.getElementById('crearEvalCedula').value          = '';
        document.getElementById('crearEvalMsg').textContent       = '';
        document.getElementById('modalCrearEval').style.display   = 'flex';
    };

    window.cerrarCrearEval = function() {
        document.getElementById('modalCrearEval').style.display = 'none';
    };

    window.buscarEmpleadoEval = function() {
        const cedula = document.getElementById('crearEvalCedula').value.trim();
        const msg    = document.getElementById('crearEvalMsg');
        if (!cedula) { msg.textContent = 'Ingresa una cédula.'; msg.style.color = '#ef4444'; return; }
        msg.textContent = 'Buscando...'; msg.style.color = '#64748b';

        fetch(appUrl + '?views=admin&action=buscarEmpleadoCedula&cedula=' + encodeURIComponent(cedula), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                msg.textContent = '⚠️ ' + data.error + '. El empleado debe crearse primero en nómina.';
                msg.style.color = '#ef4444';
                return;
            }
            // Encontrado — llenar formulario
            document.getElementById('crearEvalIdEmp').value      = data.IDEMPLEADO;
            document.getElementById('crearEvalIdentH').value     = data.IDENTIFICACION;
            document.getElementById('crearEvalNombreH').value    = data.NOMBRE;
            document.getElementById('crearEvalNombreShow').textContent = data.NOMBRE;
            document.getElementById('crearEvalCedulaShow').textContent = ' · C.C. ' + data.IDENTIFICACION;
            document.getElementById('crearEvalPaso1').style.display = 'none';
            document.getElementById('formCrearEval').style.display  = '';
            msg.textContent = '';
        })
        .catch(() => { msg.textContent = 'Error de conexión.'; msg.style.color = '#ef4444'; });
    };

    // Enter en campo cédula dispara búsqueda
    document.getElementById('crearEvalCedula')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarEmpleadoEval(); }
    });

    // ── Gestión HUMEMPLEADOEVAL ───────────────────────────────────────────────
    window.abrirEditarEval = function(ev) {
        document.getElementById('evalModalNombre').textContent   = ev.NOMBRE   || '—';
        document.getElementById('evalModalCedula').textContent   = 'C.C. ' + (ev.IDENTIFICACION || '');
        document.getElementById('evalIdAsignacion').value        = ev.IDASIGNACION || '';
        document.getElementById('evalIdRol').value               = ev.IDROL    || 2;
        document.getElementById('evalProceso').value             = ev.PROCESO  || '';
        document.getElementById('evalCargo').value               = ev.CARGO    || '';
        document.getElementById('evalEmail').value               = ev.EMAIL    || '';
        document.getElementById('evalCelular').value             = ev.CELULAR  || '';
        document.getElementById('evalExpAzul').checked           = ev.APLICA_EXP_AZUL == 1;
        document.getElementById('evalEsDirector').checked         = ev.ES_DIRECTOR == 1;
        // Mostrar badge líder funcional si aplica
        const badge = document.getElementById('evalLiderFuncionalBadge');
        if (badge) badge.style.display = ev.ES_LIDER_FUNCIONAL == 1 ? 'block' : 'none';

        // Preseleccionar jefe
        const jefeSelect = document.getElementById('evalIdJefe');
        if (jefeSelect) {
            jefeSelect.value = ev.IDEMPLEADO_EVAL || 0;
            document.getElementById('evalNombreJefe').value = ev.NOMBRE_JEFE || '';
        }

        // Toggle btn
        document.getElementById('toggleEvalId').value     = ev.IDASIGNACION || '';
        document.getElementById('toggleEvalActivo').value = ev.ACTIVO == 1 ? 0 : 1;
        document.getElementById('toggleEvalBtn').textContent =
            ev.ACTIVO == 1 ? 'Inactivar' : 'Activar';

        document.getElementById('modalEditarEval').style.display = 'flex';
    };

    window.actualizarNombreJefe = function(select) {
        const opt = select.options[select.selectedIndex];
        document.getElementById('evalNombreJefe').value = opt.dataset.nombre || '';
    };

    window.submitToggleEval = function() {
        document.getElementById('formToggleEval').submit();
    };

    window.cerrarEditarEval = function() {
        document.getElementById('modalEditarEval').style.display = 'none';
    };

    // Cerrar modal al hacer clic fuera
    document.getElementById('modalEditarEval')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarEditarEval();
    });

    window.toggleCompPanel = function(id) {
        const panel = document.getElementById('comp-panel-' + id);
        const arr   = document.getElementById('arr-' + id);
        if (!panel) return;
        const open = panel.style.display === '';
        panel.style.display = open ? 'none' : '';
        if (arr) arr.textContent = open ? '▼' : '▲';
    };

    // Animar barras de progreso
    document.querySelectorAll('.adm-fill').forEach(el => {
        const w = el.style.width;
        el.style.width = '0%';
        setTimeout(() => { el.style.width = w; }, 300);
    });

    // ── Seguimiento: filtro por estado ────────────────────────────────────────
    let segEstadoActivo = 'todos';

    window.segFiltrar = function(estado, btn) {
        segEstadoActivo = estado;
        // Resaltar botón activo
        document.querySelectorAll('.seg-filter').forEach(b => {
            b.style.fontWeight = '600';
            b.style.opacity = '.6';
        });
        btn.style.opacity = '1';
        btn.style.fontWeight = '700';
        segAplicarFiltros();
    };

    // ── Seguimiento: búsqueda en tiempo real ──────────────────────────────────
    window.segBuscar = function(term) {
        segAplicarFiltros(term);
    };

    function segAplicarFiltros(term) {
        term = (term ?? document.getElementById('segBuscador')?.value ?? '').toLowerCase().trim();
        const rows = document.querySelectorAll('.seg-row');
        let visible = 0;

        rows.forEach(row => {
            const nombre = row.dataset.nombre || '';
            const cargo  = row.dataset.cargo  || '';
            const area   = row.dataset.area   || '';
            const estado = row.dataset.estado || '';

            const matchEstado = segEstadoActivo === 'todos' || estado === segEstadoActivo;
            const matchTerm   = !term || nombre.includes(term) || cargo.includes(term) || area.includes(term);

            const show = matchEstado && matchTerm;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Actualizar contador
        const contador = document.getElementById('segContador');
        if (contador) {
            const total = rows.length;
            contador.textContent = visible === total
                ? `Mostrando ${total} colaboradores`
                : `Mostrando ${visible} de ${total} colaboradores`;
        }
    }

    // Inicializar contador al cargar
    setTimeout(() => segAplicarFiltros(), 100);

    // Estilo botón activo inicial
    const btnTodos = document.querySelector('.seg-filter.active');
    if (btnTodos) { btnTodos.style.opacity = '1'; btnTodos.style.fontWeight = '700'; }

    // ── Filtro de objetivos por competencia ───────────────────────────────────
    window.filtrarObjetivos = function() {
        const comp = document.getElementById('filtroCompObj')?.value || '';
        const cal  = document.getElementById('filtroCalObj')?.value  || '';
        document.querySelectorAll('.obj-item').forEach(el => {
            const matchComp = !comp || el.dataset.comp === comp;
            const matchCal  = !cal  || el.dataset.cal  === cal;
            el.style.display = (matchComp && matchCal) ? '' : 'none';
        });
    };

    window.editarObjetivo = function(obj) {
        document.getElementById('objFormTitle').textContent = '✏️ Editando objetivo';
        document.getElementById('objAction').value          = 'editarObjetivo';
        document.getElementById('objIdObjeto').value        = obj.IDOBJETIVO     || '';
        document.getElementById('objBtnLabel').textContent  = 'Guardar cambios';
        document.getElementById('objCompetencia').value     = obj.NUM_COMPETENCIA || '';
        document.getElementById('objCalificacion').value    = obj.CALIFICACION    || '';
        document.getElementById('objModelo').value          = obj.MODELO          || '';
        document.getElementById('objObjetivo').value        = obj.OBJETIVO        || '';
        document.getElementById('objIndicador').value       = obj.INDICADOR       || '';
        document.getElementById('objMeta').value            = obj.META            || '';
        document.getElementById('objPlazo').value           = obj.PLAZO           || '';
        document.getElementById('objEvidencia').value       = obj.EVIDENCIA       || '';
        document.getElementById('objSeguimiento').value     = obj.SEGUIMIENTO     || '';
        document.getElementById('objUsoRecomendado').value  = obj.USO_RECOMENDADO || '';
        document.getElementById('formObjetivo').scrollIntoView({ behavior:'smooth', block:'start' });
    };

    window.resetFormObjetivo = function() {
        document.getElementById('objFormTitle').textContent = '🎯 Nuevo objetivo SMART';
        document.getElementById('objAction').value          = 'crearObjetivo';
        document.getElementById('objIdObjeto').value        = '';
        document.getElementById('objBtnLabel').textContent  = 'Crear objetivo';
        document.getElementById('formObjetivo').reset();
    };


    // Editar fecha de cierre del período
    window.toggleEditCierre = function(idPeriodo) {
        const div = document.getElementById('edit-cierre-' + idPeriodo);
        if (div) div.style.display = div.style.display === 'none' ? 'block' : 'none';
    };


    // ── Reset contraseña ──────────────────────────────────────────────────────
    window.confirmarReset = function(idUsuario, identificacion, nombre) {
        Swal.fire({
            icon: 'warning',
            title: '¿Resetear contraseña?',
            html: 'La contraseña de <strong>' + nombre + '</strong> quedará como su número de identificación.<br><br>El colaborador deberá cambiarla al ingresar.',
            showCancelButton: true,
            confirmButtonText: 'Sí, resetear',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0058af',
            cancelButtonColor: '#94a3b8'
        }).then(res => {
            if (res.isConfirmed) {
                fetch('<?= APP_URL ?>admin/', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded',
                               'X-Requested-With': 'XMLHttpRequest' },
                    body: 'action=resetearPassword&idUsuario=' + idUsuario + '&identificacion=' + encodeURIComponent(identificacion)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        Swal.fire({ icon:'success', title:'Contraseña reseteada',
                            text:'La nueva contraseña es el número de identificación del colaborador.',
                            confirmButtonColor:'#0058af' });
                    } else {
                        Swal.fire({ icon:'error', title:'Error', text: data.msg || 'No se pudo resetear.',
                            confirmButtonColor:'#0058af' });
                    }
                });
            }
        });
    };

})();

// ── Exportar CSV con fetch + Blob + SweetAlert ────────────────────────────────
const EXPORT_BASE_URL = '<?= rtrim(str_replace("GestionHumana/", "GestionHumana/exportar.php", APP_URL), "/") ?>';

const EXPORT_LABELS = {
    seguimiento:     'Seguimiento de evaluaciones',
    avance:          'Avance por proceso',
    promedios_colab: 'Promedios — Como Colaborador',
    promedios_lider: 'Promedios — Como Líder',
};

function exportarCSV(tipo, btn) {
    const label = EXPORT_LABELS[tipo] || tipo;
    const origHtml = btn.innerHTML;

    // Estado de carga en el botón
    btn.disabled = true;
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin .8s linear infinite;display:inline-block;vertical-align:middle;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Generando...';

    fetch(EXPORT_BASE_URL + '?tipo=' + encodeURIComponent(tipo))
        .then(res => {
            if (!res.ok) throw new Error('Error del servidor: ' + res.status);
            return res.blob();
        })
        .then(blob => {
            // Crear enlace temporal y forzar descarga
            const url  = URL.createObjectURL(blob);
            const link = document.createElement('a');
            const fecha = new Date().toISOString().slice(0,10).replace(/-/g,'');
            link.href     = url;
            link.download = tipo + '_' + fecha + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            // Restaurar botón
            btn.disabled  = false;
            btn.innerHTML = origHtml;

            // SweetAlert de éxito
            Swal.fire({
                icon:             'success',
                title:            '¡Exportación exitosa!',
                html:             '<b>' + label + '</b><br><span style="font-size:.88rem;color:#64748b;">El archivo CSV fue descargado correctamente.<br>Ábrelo con Excel o Google Sheets.</span>',
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
     TOUR DE AYUDA — ADMIN
══════════════════════════════════════════════════════════ -->
<style>
#adm-tour-btn {
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
#adm-tour-btn:hover {
    transform: scale(1.12);
    box-shadow: 0 6px 22px rgba(0,88,175,0.55);
}
</style>

<button id="adm-tour-btn" title="Tour de ayuda" onclick="iniciarTourAdmin()">?</button>

<script>
(function () {
    var activeTab      = '<?= htmlspecialchars($activeTab ?? 'periodos', ENT_QUOTES) ?>';
    var hayPeriodo     = <?= !empty($periodoActivo) ? 'true' : 'false' ?>;
    var feedbackActivo = <?= !empty($feedbackActivo) ? 'true' : 'false' ?>;

    // ── Loader CDN ────────────────────────────────────────────────────────────
    function _admLoadDriver(cb) {
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

    function ic(name, color) {
        color = color || '#0058af';
        return '<i class="ti ti-' + name + '" style="color:' + color +
               ';font-size:16px;flex-shrink:0;"></i>';
    }

    var helpBtn =
        '<span style="display:inline-flex;align-items:center;justify-content:center;' +
        'width:20px;height:20px;border-radius:50%;vertical-align:middle;margin:0 2px;' +
        'background:linear-gradient(135deg,#0058af,#0074e0);' +
        'color:#fff;font-size:11px;font-weight:700;line-height:1;">?</span>';

    // ── Descripción y elemento según pestaña activa ───────────────────────────
    var tabInfo = {
        periodos    : { el:'#adm-periodos',
            title: ic('calendar') + ' Gestión de períodos',
            desc : 'Crea, activa y cierra <strong>períodos de evaluación</strong>. Solo puede haber un período activo a la vez. ' +
                   'También controlas aquí el <strong>interruptor de Feedback</strong> para habilitar ese módulo a los líderes.' },
        seguimiento : { el:'#adm-seguimiento',
            title: ic('eye') + ' Seguimiento global',
            desc : 'Tabla de todos los colaboradores con su estado: <strong>sin iniciar / en progreso / completo</strong>. ' +
                   'Envía notificaciones masivas por correo a quienes tengan pendientes.' },
        avance      : { el:'#adm-avance',
            title: ic('trending-up') + ' Avance por dependencia',
            desc : 'Gráfica de barras con el <strong>porcentaje de avance</strong> de autoevaluación y evaluación completa, ' +
                   'desglosado por área o dependencia.' },
        promedios   : { el:'#adm-promedios',
            title: ic('chart-bar') + ' Promedios organizacionales',
            desc : 'Calificaciones promedio <strong>globales por competencia</strong>, separadas en desempeño (P1–P11) ' +
                   'y liderazgo (P12–P16). Útil para identificar brechas a nivel institucional.' },
        usuarios    : { el:'#adm-usuarios',
            title: ic('users') + ' Gestión de usuarios',
            desc : 'Busca colaboradores por cédula para <strong>asignar roles</strong>: líder funcional, director o administrador. ' +
                   'También puedes resetear contraseñas desde aquí.' },
        objetivos   : { el:'#adm-objetivos',
            title: ic('target') + ' Catálogo de objetivos SMART',
            desc : 'Banco de <strong>objetivos predefinidos</strong> organizados por competencia y calificación. ' +
                   'Los líderes los seleccionan durante el feedback para asignarlos a sus colaboradores.' },
        competencias: { el:'#adm-competencias',
            title: ic('award') + ' Definición de competencias',
            desc : 'Gestiona las <strong>competencias evaluadas</strong> (nombre, descripción, opciones de respuesta). ' +
                   'Cambios aquí afectan directamente los formularios de evaluación.' },
        colaboradores:{el:'#adm-colaboradores',
            title: ic('id-badge') + ' Asignación de colaboradores',
            desc : 'Define la <strong>relación evaluado → evaluador</strong> para cada período: qué colaborador es evaluado ' +
                   'por qué líder funcional, y qué líderes están a cargo de qué director.' },
    };

    var current = tabInfo[activeTab] || tabInfo['periodos'];

    // ── Tour ─────────────────────────────────────────────────────────────────
    window.iniciarTourAdmin = function () {
        _admLoadDriver(function () {
            var driverFn = (window['driver'] && window['driver']['js'] && window['driver']['js']['driver'])
                ? window['driver']['js']['driver']
                : window['driver'];

            var pasos = [
                // 0 — Bienvenida
                {
                    popover: {
                        title: ic('settings') + ' Panel de Administración',
                        description:
                            'Desde aquí controlas <strong>todo el proceso de evaluación</strong>: períodos, ' +
                            'seguimiento de avance, usuarios y catálogos de contenido. ' +
                            'Este módulo es solo para administradores. Te mostramos lo más importante.',
                        side: 'over', align: 'center',
                    }
                },
                // 1 — Hero
                {
                    element: '#adm-hero',
                    popover: {
                        title: ic('layout-dashboard') + ' Panel de control',
                        description: 'El encabezado muestra el <strong>nombre del módulo</strong>. ' +
                            (hayPeriodo
                                ? 'El banner verde debajo indica el período activo y los días restantes.'
                                : '<strong style="color:#dc2626;">No hay período activo.</strong> Dirígete a la pestaña Períodos para crear y activar uno.'),
                        side: 'bottom', align: 'start',
                    }
                },
                // 2 — Banner período
                {
                    element: hayPeriodo ? '.adm-period-banner.adm-period-active' : '.adm-period-banner.adm-period-none',
                    popover: {
                        title: ic('calendar-event') + ' Estado del período',
                        description: hayPeriodo
                            ? 'Período activo visible para todos. Mientras esté abierto los colaboradores pueden evaluar. ' +
                              'El cierre automático no existe — debes cerrarlo manualmente desde <strong>Períodos</strong>.'
                            : '<strong>Sin período activo.</strong> Los colaboradores no pueden acceder al formulario de evaluación hasta que actives uno.',
                        side: 'bottom', align: 'start',
                    }
                },
                // 3 — Tabs
                {
                    element: '.adm-tabs',
                    popover: {
                        title: ic('layout-navbar') + ' Secciones del panel',
                        description:
                            '<strong>Períodos</strong> — crear/activar/cerrar períodos + habilitar feedback.<br>' +
                            '<strong>Seguimiento</strong> — estado de cada colaborador + notificaciones.<br>' +
                            '<strong>Avance</strong> — % completado por dependencia.<br>' +
                            '<strong>Promedios</strong> — calificaciones medias por competencia.<br>' +
                            '<strong>Usuarios</strong> — roles y contraseñas.<br>' +
                            '<strong>Objetivos</strong> — catálogo SMART.<br>' +
                            '<strong>Competencias</strong> — definiciones y opciones.<br>' +
                            '<strong>Colaboradores</strong> — asignación evaluado/evaluador.',
                        side: 'bottom', align: 'center',
                    }
                },
                // 4 — Contenido de la pestaña activa
                {
                    element: current.el,
                    popover: {
                        title: current.title,
                        description: current.desc,
                        side: 'top', align: 'start',
                    }
                },
                // 5 — Final
                {
                    popover: {
                        title: ic('circle-check', '#16a34a') + ' ¡Todo listo!',
                        description:
                            'Ya conoces el <strong>Panel de Administración</strong>. Recuerda: siempre debe ' +
                            'existir un período activo para que el proceso de evaluación funcione. ' +
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
