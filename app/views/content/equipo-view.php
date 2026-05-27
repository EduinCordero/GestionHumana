<?php
// Defaults de seguridad
$kpis          = $kpis          ?? [];
$equipo        = $equipo        ?? [];
$acuerdos      = $acuerdos      ?? [];
$totalAcuerdos = $totalAcuerdos ?? 0;
$feedbacks        = $feedbacks        ?? [];
$feedbacksLideres = $feedbacksLideres ?? [];
$esDirector       = $esDirector       ?? false;
$alertas          = $alertas          ?? ['sin_auto'=>[],'sin_eval'=>[],'sin_fb'=>[],'ac_pendientes'=>[],'sin_fb_2'=>[],'ac_pendientes_2'=>[]];
$periodoActivo = $periodoActivo ?? null;
$periodos      = $periodos      ?? [];
$activeTab     = $activeTab     ?? 'dashboard';
$estadoAc      = $estadoAc      ?? '';
$pageAc        = $pageAc        ?? 1;
$perPageAc     = 15;
$totalPagesAc  = $totalAcuerdos > 0 ? (int)ceil($totalAcuerdos / $perPageAc) : 1;

$urlBase = APP_URL . 'liderPanel/';
$urlPeriodo = $periodoActivo ? '&idperiodo=' . (int)$periodoActivo['IDPERIODO'] : '';
?>
<style>
/* ── Panel de Liderazgo ────────────────────────────────────────── */
.lp-root { padding:68px 32px 56px; max-width:1200px; margin:0 auto; color:#1e293b; }

/* Hero */
.lp-hero {
    background:linear-gradient(135deg,#0058af 0%,#1a73e8 60%,#2563eb 100%);
    border-radius:20px; padding:28px 36px;
    display:flex; align-items:center; justify-content:space-between; gap:20px;
    margin-bottom:24px; position:relative; overflow:hidden;
    box-shadow:0 4px 24px rgba(0,88,175,0.25);
}
.lp-hero::before {
    content:''; position:absolute; width:280px; height:280px; border-radius:50%;
    background:rgba(255,255,255,0.06); top:-80px; right:-50px; pointer-events:none;
}
.lp-hero-title  { font-size:22px; font-weight:700; color:#fff; margin-bottom:3px; }
.lp-hero-sub    { font-size:13px; color:rgba(255,255,255,0.65); }
.lp-period-badge {
    display:inline-flex; align-items:center; gap:7px;
    border-radius:99px; padding:6px 14px;
    font-size:12px; font-weight:500;
    background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25); color:#fff;
}
.lp-period-dot { width:7px; height:7px; border-radius:50%; animation:lpPulse 2s infinite; }
@keyframes lpPulse{0%,100%{opacity:1;}50%{opacity:.35;}}

/* Tabs */
.lp-tabs {
    display:flex; gap:4px; margin-bottom:24px;
    background:#f1f5f9; border-radius:12px; padding:4px;
}
.lp-tab {
    flex:1; text-align:center; padding:9px 14px;
    border-radius:9px; font-size:13px; font-weight:500;
    color:#64748b; text-decoration:none; transition:all .2s;
    display:flex; align-items:center; justify-content:center; gap:6px;
    white-space:nowrap;
}
.lp-tab:hover { color:#1e293b; background:#e2e8f0; text-decoration:none; }
.lp-tab.active { background:#fff; color:#0058af; font-weight:600; box-shadow:0 1px 4px rgba(0,0,0,.08); }
.lp-tab .lp-badge {
    font-size:10px; font-weight:700; padding:2px 7px; border-radius:99px;
    background:#fee2e2; color:#dc2626; line-height:1.4;
}
.lp-tab.active .lp-badge { background:#dbeafe; color:#1d4ed8; }

/* Cards grid */
.lp-kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px; }
.lp-kpi {
    background:#fff; border-radius:14px; border:1px solid #e2e8f0;
    padding:18px 20px; box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.lp-kpi-icon { width:38px; height:38px; border-radius:10px;
    display:flex; align-items:center; justify-content:center; margin-bottom:12px; font-size:18px; }
.lp-kpi-val  { font-size:30px; font-weight:700; color:#0f172a; line-height:1; }
.lp-kpi-lbl  { font-size:11px; color:#64748b; margin-top:4px; font-weight:500; }
.lp-kpi-bar  { height:4px; border-radius:99px; background:#f1f5f9; margin-top:10px; overflow:hidden; }
.lp-kpi-fill { height:100%; border-radius:99px; transition:width 1s ease; }

/* Panel sections */
.lp-section { background:#fff; border-radius:14px; border:1px solid #e2e8f0; padding:22px 24px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
.lp-section-title { font-size:11px; font-weight:700; letter-spacing:1.2px; text-transform:uppercase; color:#94a3b8; margin-bottom:16px; }

/* Table */
.lp-table { width:100%; border-collapse:collapse; font-size:13px; }
.lp-table th { font-size:10.5px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:#94a3b8; padding:8px 12px; border-bottom:1px solid #f1f5f9; text-align:left; white-space:nowrap; }
.lp-table td { padding:11px 12px; border-bottom:1px solid #f8fafc; color:#334155; vertical-align:middle; }
.lp-table tr:last-child td { border-bottom:none; }
.lp-table tr.clickable { cursor:pointer; transition:background .15s; }
.lp-table tr.clickable:hover td { background:#f8fafc; }

/* Badges de estado */
.lp-st { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600; white-space:nowrap; }
.lp-st-ok  { background:#dcfce7; color:#15803d; }
.lp-st-pend{ background:#fef9c3; color:#854d0e; }
.lp-st-no  { background:#f1f5f9; color:#64748b; }
.lp-st-err { background:#fee2e2; color:#dc2626; }
.lp-st-info{ background:#dbeafe; color:#1d4ed8; }

/* Alertas */
.lp-alert-section { margin-bottom:18px; }
.lp-alert-head { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; margin-bottom:10px; text-transform:uppercase; letter-spacing:.8px; }
.lp-alert-row { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:10px; background:#f8fafc; border:1px solid #e2e8f0; margin-bottom:6px; font-size:13px; }
.lp-alert-name { font-weight:500; color:#1e293b; flex:1; }
.lp-alert-cargo { font-size:11px; color:#64748b; }
.lp-empty { text-align:center; padding:32px; color:#94a3b8; font-size:13px; }

/* Feedback cards */
.lp-fb-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:14px; }
.lp-fb-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:18px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
.lp-fb-name { font-size:14px; font-weight:600; color:#1e293b; margin-bottom:2px; }
.lp-fb-cargo { font-size:11px; color:#64748b; margin-bottom:10px; }
.lp-fb-meta { font-size:11px; color:#94a3b8; margin-top:8px; }

/* Filtros estado */
.lp-filter-row { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.lp-filter-btn {
    padding:6px 14px; border-radius:99px; font-size:12px; font-weight:600;
    border:1.5px solid #e2e8f0; background:#fff; color:#64748b;
    cursor:pointer; text-decoration:none; transition:all .15s;
}
.lp-filter-btn:hover { border-color:#0058af; color:#0058af; text-decoration:none; }
.lp-filter-btn.active { border-color:#0058af; background:#0058af; color:#fff; }

/* Pagination */
.lp-pages { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:16px; }
.lp-page-btn { padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #e2e8f0; background:#fff; color:#64748b; cursor:pointer; text-decoration:none; transition:all .15s; }
.lp-page-btn:hover { background:#f1f5f9; text-decoration:none; }
.lp-page-btn.active { background:#0058af; color:#fff; border-color:#0058af; }
.lp-page-btn.disabled { pointer-events:none; opacity:.4; }

/* Period selector */
.lp-period-sel { display:flex; align-items:center; gap:8px; }
.lp-period-sel select { padding:6px 10px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:#1e293b; background:#fff; cursor:pointer; }

/* Modal */
#lpModalOverlay {
    position:fixed; inset:0; background:rgba(0,0,0,.45);
    display:none; align-items:center; justify-content:center;
    z-index:9000; backdrop-filter:blur(3px);
}
#lpModalOverlay.open { display:flex; }
#lpModal {
    background:#fff; border-radius:20px; width:min(640px,96vw); max-height:88vh;
    overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.2);
    animation:lpModalIn .2s ease;
}
@keyframes lpModalIn { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:none} }
.lp-modal-head {
    background:linear-gradient(135deg,#0058af 0%,#1a73e8 60%,#2563eb 100%);
    padding:22px 26px; border-radius:20px 20px 0 0;
    display:flex; align-items:center; justify-content:space-between;
    position:relative; overflow:hidden;
}
.lp-modal-head::before {
    content:''; position:absolute; width:220px; height:220px; border-radius:50%;
    background:rgba(255,255,255,0.06); top:-70px; right:-40px; pointer-events:none;
}
.lp-modal-avatar {
    width:48px; height:48px; border-radius:50%;
    background:rgba(255,255,255,.2); border:2px solid rgba(255,255,255,.35);
    display:flex; align-items:center; justify-content:center;
    font-size:18px; font-weight:800; color:#fff; flex-shrink:0;
}
.lp-modal-close {
    width:32px; height:32px; border-radius:8px; border:none; cursor:pointer;
    background:rgba(255,255,255,.15); color:#fff; font-size:18px;
    display:flex; align-items:center; justify-content:center;
    transition:background .15s;
}
.lp-modal-close:hover { background:rgba(255,255,255,.25); }
.lp-modal-body { padding:22px 26px; }
.lp-modal-section { margin-bottom:20px; }
.lp-modal-section-title { font-size:10.5px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#94a3b8; margin-bottom:12px; }
.lp-cal-row { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:13px; }
.lp-cal-row:last-child { border-bottom:none; }
.lp-cal-name { color:#334155; flex:1; }
.lp-cal-val { font-weight:700; padding:3px 10px; border-radius:8px; font-size:12px; }
.lp-ac-item { padding:10px 12px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:8px; font-size:12px; }
.lp-ac-item-obj { font-weight:500; color:#1e293b; margin-bottom:4px; }
.lp-ac-item-meta { color:#64748b; display:flex; gap:12px; flex-wrap:wrap; }

/* Fade */
.lp-fade { opacity:0; transform:translateY(10px); animation:lpUp .4s ease forwards; }
@keyframes lpUp { to { opacity:1; transform:none; } }
.ld1{animation-delay:.05s}.ld2{animation-delay:.12s}.ld3{animation-delay:.19s}

@media(max-width:900px) {
    .lp-root { padding:20px 16px; }
    .lp-kpi-grid { grid-template-columns:repeat(2,1fr); }
    .lp-hero { flex-direction:column; align-items:flex-start; padding:22px 20px; }
    .lp-tabs { flex-wrap:wrap; }
    .lp-tab { flex:none; font-size:12px; }
}
</style>

<div class="lp-root">

<!-- ── HERO ── -->
<div class="lp-hero lp-fade ld1">
    <div>
        <div class="lp-hero-title">Panel de Liderazgo</div>
        <div class="lp-hero-sub">
            Consolidado de tu equipo ·
            <?= $periodoActivo ? htmlspecialchars($periodoActivo['NOMBRE']) : 'Sin período activo' ?>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;">
        <!-- Selector de período -->
        <?php if (count($periodos) > 1): ?>
        <div class="lp-period-sel">
            <form method="GET" action="<?= $urlBase ?>">
                <input type="hidden" name="views" value="liderPanel">
                <input type="hidden" name="tab"   value="<?= htmlspecialchars($activeTab) ?>">
                <select name="idperiodo" onchange="this.form.submit()">
                    <?php foreach ($periodos as $p): ?>
                    <option value="<?= (int)$p['IDPERIODO'] ?>"
                        <?= $periodoActivo && (int)$p['IDPERIODO'] === (int)$periodoActivo['IDPERIODO'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['NOMBRE']) ?>
                        <?= (int)$p['ESTADO'] ? '(Activo)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php endif; ?>
        <?php if ($periodoActivo): ?>
        <div class="lp-period-badge">
            <div class="lp-period-dot" style="background:#4ade80;"></div>
            Período activo
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── TABS ── -->
<?php
$totalAlertas = count($alertas['sin_auto'] ?? []) + count($alertas['sin_eval'] ?? []) + count($alertas['sin_fb'] ?? []) + count($alertas['ac_pendientes'] ?? []);
$tabs = [
    'dashboard'      => ['icon'=>'ti-speedometer',    'label'=>'Dashboard'],
    'acuerdos'       => ['icon'=>'ti-clipboard-list', 'label'=>'Acuerdos'],
    'feedback'       => ['icon'=>'ti-message-circle', 'label'=>'Feedback'],
    'alertas'        => ['icon'=>'ti-alert-triangle', 'label'=>'Alertas', 'badge'=>$totalAlertas],
];
?>
<div class="lp-tabs lp-fade ld2">
    <?php foreach ($tabs as $tabKey => $tabInfo): ?>
    <a href="<?= $urlBase ?>?tab=<?= $tabKey ?><?= $urlPeriodo ?>"
       class="lp-tab <?= $activeTab === $tabKey ? 'active' : '' ?>">
        <i class="ti <?= $tabInfo['icon'] ?>" style="font-size:15px;"></i>
        <?= $tabInfo['label'] ?>
        <?php if (!empty($tabInfo['badge']) && (int)$tabInfo['badge'] > 0): ?>
        <span class="lp-badge"><?= (int)$tabInfo['badge'] ?></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════════════════════
     TAB: DASHBOARD
════════════════════════════════════════════════════════════ -->
<?php if ($activeTab === 'dashboard'): ?>

<?php
$total   = (int)($kpis['TOTAL_COLAB'] ?? 0);
$conAuto = (int)($kpis['CON_AUTO']    ?? 0);
$conEval = (int)($kpis['CON_EVAL']    ?? 0);
$totalFb = (int)($kpis['TOTAL_FB']    ?? 0);
$fbOk    = (int)($kpis['FB_COMPLETO'] ?? 0);
$totalAc = (int)($kpis['TOTAL_AC']    ?? 0);
$acPend  = (int)($kpis['AC_PEND']     ?? 0);
$acApro  = (int)($kpis['AC_APRO']     ?? 0);
$pctAuto = $total > 0 ? round($conAuto / $total * 100) : 0;
$pctEval = $total > 0 ? round($conEval / $total * 100) : 0;
$pctFb   = $total > 0 ? round($fbOk   / $total * 100) : 0;
$pctAc   = $totalAc > 0 ? round($acApro / $totalAc * 100) : 0;
?>

<!-- KPIs -->
<div class="lp-kpi-grid lp-fade ld2">

    <div class="lp-kpi">
        <div class="lp-kpi-icon" style="background:#dbeafe;color:#1d4ed8;">
            <i class="ti ti-users"></i>
        </div>
        <div class="lp-kpi-val"><?= $total ?></div>
        <div class="lp-kpi-lbl">Colaboradores a cargo</div>
    </div>

    <div class="lp-kpi">
        <div class="lp-kpi-icon" style="background:#dcfce7;color:#16a34a;">
            <i class="ti ti-user-check"></i>
        </div>
        <div class="lp-kpi-val"><?= $conAuto ?><span style="font-size:16px;color:#94a3b8;font-weight:400;">/<?= $total ?></span></div>
        <div class="lp-kpi-lbl">Con autoevaluación</div>
        <div class="lp-kpi-bar">
            <div class="lp-kpi-fill" style="width:<?= $pctAuto ?>%;background:#22c55e;"></div>
        </div>
    </div>

    <div class="lp-kpi">
        <div class="lp-kpi-icon" style="background:#ede9fe;color:#7c3aed;">
            <i class="ti ti-clipboard-check"></i>
        </div>
        <div class="lp-kpi-val"><?= $conEval ?><span style="font-size:16px;color:#94a3b8;font-weight:400;">/<?= $total ?></span></div>
        <div class="lp-kpi-lbl">Evaluados por ti</div>
        <div class="lp-kpi-bar">
            <div class="lp-kpi-fill" style="width:<?= $pctEval ?>%;background:#8b5cf6;"></div>
        </div>
    </div>

    <div class="lp-kpi">
        <div class="lp-kpi-icon" style="background:#fef3c7;color:#d97706;">
            <i class="ti ti-message-circle"></i>
        </div>
        <div class="lp-kpi-val"><?= $fbOk ?><span style="font-size:16px;color:#94a3b8;font-weight:400;">/<?= $total ?></span></div>
        <div class="lp-kpi-lbl">Feedback completado</div>
        <div class="lp-kpi-bar">
            <div class="lp-kpi-fill" style="width:<?= $pctFb ?>%;background:#f59e0b;"></div>
        </div>
    </div>

</div>

<!-- Segunda fila KPIs: acuerdos -->
<div class="lp-kpi-grid lp-fade ld3" style="grid-template-columns:repeat(3,1fr);">

    <div class="lp-kpi">
        <div class="lp-kpi-icon" style="background:#f1f5f9;color:#64748b;">
            <i class="ti ti-target"></i>
        </div>
        <div class="lp-kpi-val"><?= $totalAc ?></div>
        <div class="lp-kpi-lbl">Acuerdos asignados</div>
    </div>

    <div class="lp-kpi">
        <div class="lp-kpi-icon" style="background:#fef9c3;color:#854d0e;">
            <i class="ti ti-clock-hour-4"></i>
        </div>
        <div class="lp-kpi-val" style="color:#854d0e;"><?= $acPend ?></div>
        <div class="lp-kpi-lbl">Acuerdos pendientes</div>
    </div>

    <div class="lp-kpi">
        <div class="lp-kpi-icon" style="background:#dcfce7;color:#16a34a;">
            <i class="ti ti-circle-check"></i>
        </div>
        <div class="lp-kpi-val" style="color:#16a34a;"><?= $acApro ?></div>
        <div class="lp-kpi-lbl">Acuerdos aprobados</div>
        <div class="lp-kpi-bar">
            <div class="lp-kpi-fill" style="width:<?= $pctAc ?>%;background:#22c55e;"></div>
        </div>
    </div>

</div>

<!-- Tabla de equipo completa -->
<?php if (!empty($equipo)): ?>
<div class="lp-section lp-fade ld3">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <div class="lp-section-title" style="margin-bottom:0;">Todos los colaboradores — haz clic para ver el perfil</div>
        <a href="<?= $urlBase ?>?action=exportar&tipo=equipo<?= $urlPeriodo ?>"
           style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#0058af;border:1.5px solid #0058af;padding:5px 12px;border-radius:99px;text-decoration:none;transition:all .15s;"
           onmouseover="this.style.background='#0058af';this.style.color='#fff';"
           onmouseout="this.style.background='';this.style.color='#0058af';">
            <i class="ti ti-download" style="font-size:13px;"></i> Exportar CSV
        </a>
    </div>
    <table class="lp-table">
        <thead>
            <tr>
                <th>Colaborador</th>
                <th>Cargo</th>
                <th>Autoevaluación</th>
                <th>Evaluado</th>
                <th>Feedback</th>
                <th>Acuerdos</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($equipo as $c): ?>
        <tr class="clickable" onclick="lpAbrirPerfil(<?= (int)$c['IDEMPLEADO'] ?>)">
            <td><strong><?= htmlspecialchars($c['EMPLEADO']) ?></strong></td>
            <td style="color:#64748b;"><?= htmlspecialchars($c['CARGO']) ?></td>
            <td>
                <?php if ((int)$c['TIENE_AUTO']): ?>
                    <span class="lp-st lp-st-ok"><i class="ti ti-check" style="font-size:11px;"></i> Sí</span>
                <?php else: ?>
                    <span class="lp-st lp-st-pend">Pendiente</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ((int)$c['TIENE_EVAL']): ?>
                    <span class="lp-st lp-st-ok"><i class="ti ti-check" style="font-size:11px;"></i> Sí</span>
                <?php else: ?>
                    <span class="lp-st lp-st-pend">Pendiente</span>
                <?php endif; ?>
            </td>
            <td>
                <?php
                $fbCompleto = (int)$c['FIRMADO_COLAB'];
                $tieneFb    = (int)$c['TIENE_FEEDBACK'];
                if ($fbCompleto): ?>
                    <span class="lp-st lp-st-ok"><i class="ti ti-check" style="font-size:11px;"></i> Completo</span>
                <?php elseif ($tieneFb): ?>
                    <span class="lp-st lp-st-info">Registrado</span>
                <?php else: ?>
                    <span class="lp-st lp-st-no">Sin feedback</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ((int)$c['TOTAL_AC'] > 0): ?>
                    <span style="font-size:12px;color:#0058af;font-weight:600;">
                        <?= (int)$c['APRO_AC'] ?>/<?= (int)$c['TOTAL_AC'] ?> aprobados
                    </span>
                <?php else: ?>
                    <span style="color:#94a3b8;font-size:12px;">Sin acuerdos</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="lp-empty">No hay colaboradores asignados en este período.</div>
<?php endif; ?>

<?php endif; /* /dashboard */ ?>




<!-- ══════════════════════════════════════════════════════════
     TAB: ACUERDOS
════════════════════════════════════════════════════════════ -->
<?php if ($activeTab === 'acuerdos'): ?>

<!-- Filtros -->
<div class="lp-filter-row lp-fade ld2">
    <?php
    $estados = ['' => 'Todos', 'PENDIENTE' => 'Pendientes', 'RESPONDIDO' => 'Respondidos', 'APROBADO' => 'Aprobados'];
    foreach ($estados as $eKey => $eLabel): ?>
    <a href="<?= $urlBase ?>?tab=acuerdos&estado=<?= $eKey ?><?= $urlPeriodo ?>"
       class="lp-filter-btn <?= $estadoAc === $eKey ? 'active' : '' ?>">
        <?= $eLabel ?>
    </a>
    <?php endforeach; ?>
    <span style="margin-left:auto;font-size:12px;color:#64748b;align-self:center;">
        <?= $totalAcuerdos ?> registro<?= $totalAcuerdos !== 1 ? 's' : '' ?>
    </span>
    <a href="<?= $urlBase ?>?action=exportar&tipo=acuerdos<?= $urlPeriodo ?>"
       style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#0058af;border:1.5px solid #0058af;padding:5px 12px;border-radius:99px;text-decoration:none;transition:all .15s;"
       onmouseover="this.style.background='#0058af';this.style.color='#fff';"
       onmouseout="this.style.background='';this.style.color='#0058af';">
        <i class="ti ti-download" style="font-size:13px;"></i> Exportar CSV
    </a>
</div>

<div class="lp-section lp-fade ld2">
    <?php if (!empty($acuerdos)): ?>
    <table class="lp-table">
        <thead>
            <tr>
                <th>Colaborador</th>
                <th>Flujo</th>
                <th>Competencia</th>
                <th>Objetivo</th>
                <th>Meta / Plazo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($acuerdos as $ac): ?>
        <tr>
            <td>
                <div style="font-weight:600;"><?= htmlspecialchars($ac['COLABORADOR']) ?></div>
                <div style="font-size:11px;color:#94a3b8;"><?= htmlspecialchars($ac['CARGO']) ?></div>
            </td>
            <td>
                <?php if ((int)($ac['FLUJO'] ?? 1) === 2): ?>
                    <span class="lp-st lp-st-info" style="font-size:10px;">Flujo 2</span>
                <?php else: ?>
                    <span class="lp-st lp-st-no" style="font-size:10px;">Flujo 1</span>
                <?php endif; ?>
            </td>
            <td style="font-size:12px;color:#64748b;"><?= htmlspecialchars($ac['NOMBRE_COMP'] ?: 'Comp. '.$ac['NUM_COMPETENCIA']) ?></td>
            <td style="font-size:12px;max-width:220px;"><?= htmlspecialchars($ac['OBJETIVO']) ?></td>
            <td style="font-size:11px;">
                <?php if ($ac['META']): ?><div style="color:#334155;"><?= htmlspecialchars($ac['META']) ?></div><?php endif; ?>
                <?php if ($ac['PLAZO']): ?><div style="color:#64748b;margin-top:2px;"><?= htmlspecialchars($ac['PLAZO']) ?></div><?php endif; ?>
            </td>
            <td>
                <?php
                $emap = ['PENDIENTE'=>['lp-st-pend','Pendiente'], 'RESPONDIDO'=>['lp-st-info','Respondido'], 'APROBADO'=>['lp-st-ok','Aprobado']];
                $ecls = $emap[$ac['ESTADO']] ?? ['lp-st-no', $ac['ESTADO']];
                ?>
                <span class="lp-st <?= $ecls[0] ?>"><?= $ecls[1] ?></span>
                <?php if ($ac['PLAN_ACCION']): ?>
                <div style="font-size:10.5px;color:#64748b;margin-top:4px;" title="<?= htmlspecialchars($ac['PLAN_ACCION']) ?>">
                    <i class="ti ti-file-text" style="font-size:12px;"></i> Plan ingresado
                </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginación -->
    <?php if ($totalPagesAc > 1): ?>
    <div class="lp-pages">
        <?php if ($pageAc > 1): ?>
        <a class="lp-page-btn" href="<?= $urlBase ?>?tab=acuerdos&estado=<?= $estadoAc ?>&page=<?= $pageAc-1 ?><?= $urlPeriodo ?>">‹</a>
        <?php endif; ?>
        <?php for ($pi = max(1,$pageAc-2); $pi <= min($totalPagesAc,$pageAc+2); $pi++): ?>
        <a class="lp-page-btn <?= $pi===$pageAc?'active':'' ?>"
           href="<?= $urlBase ?>?tab=acuerdos&estado=<?= $estadoAc ?>&page=<?= $pi ?><?= $urlPeriodo ?>">
            <?= $pi ?>
        </a>
        <?php endfor; ?>
        <?php if ($pageAc < $totalPagesAc): ?>
        <a class="lp-page-btn" href="<?= $urlBase ?>?tab=acuerdos&estado=<?= $estadoAc ?>&page=<?= $pageAc+1 ?><?= $urlPeriodo ?>">›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="lp-empty">No hay acuerdos con el filtro seleccionado.</div>
    <?php endif; ?>
</div>

<?php endif; /* /acuerdos */ ?>


<!-- ══════════════════════════════════════════════════════════
     TAB: FEEDBACK
════════════════════════════════════════════════════════════ -->
<?php if ($activeTab === 'feedback'): ?>

<?php $hayFb1 = !empty($feedbacks); $hayFb2 = $esDirector && !empty($feedbacksLideres); ?>

<?php if ($hayFb1 || $hayFb2): ?>

<?php if ($hayFb1): ?>
<?php if ($hayFb2): ?>
<div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#64748b;margin-bottom:10px;">
    <i class="ti ti-users" style="font-size:13px;margin-right:4px;"></i> Feedback a colaboradores (Flujo 1)
</div>
<?php endif; ?>
<div class="lp-fb-grid lp-fade ld2" style="margin-bottom:<?= $hayFb2 ? '28px' : '0' ?>;">
<?php foreach ($feedbacks as $fb): ?>
    <?php $fbDone = (int)$fb['FIRMADO_COLAB']; ?>
    <div class="lp-fb-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
            <div>
                <div class="lp-fb-name"><?= htmlspecialchars($fb['COLABORADOR']) ?></div>
                <div class="lp-fb-cargo"><?= htmlspecialchars($fb['CARGO']) ?></div>
            </div>
            <?php if ($fbDone): ?>
                <span class="lp-st lp-st-ok" style="flex-shrink:0;"><i class="ti ti-check" style="font-size:11px;"></i> Completo</span>
            <?php else: ?>
                <span class="lp-st lp-st-pend" style="flex-shrink:0;">Pendiente firma</span>
            <?php endif; ?>
        </div>
        <div style="font-size:11px;color:#64748b;display:flex;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
            <?php if ($fb['FECHA_FEEDBACK']): ?>
            <span><i class="ti ti-calendar" style="font-size:12px;"></i> <?= htmlspecialchars($fb['FECHA_FEEDBACK']) ?></span>
            <?php endif; ?>
            <?php if ($fbDone && $fb['FECHA_FIRMA']): ?>
            <span><i class="ti ti-pen" style="font-size:12px;"></i> Firmó: <?= htmlspecialchars($fb['FECHA_FIRMA']) ?></span>
            <?php endif; ?>
        </div>
        <?php if ($fb['OBSERVACION']): ?>
        <div style="font-size:12px;color:#64748b;border-top:1px solid #f1f5f9;padding-top:8px;margin-top:4px;line-height:1.5;">
            <?= nl2br(htmlspecialchars(mb_substr($fb['OBSERVACION'], 0, 120))) ?><?= mb_strlen($fb['OBSERVACION']) > 120 ? '…' : '' ?>
        </div>
        <?php endif; ?>
        <div class="lp-fb-meta">
            <a href="<?= APP_URL ?>feedback/" style="color:#0058af;font-size:11px;font-weight:600;text-decoration:none;">
                Ir a Feedback →
            </a>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; /* /hayFb1 */ ?>

<?php if ($hayFb2): ?>
<div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#64748b;margin-bottom:10px;margin-top:4px;">
    <i class="ti ti-sitemap" style="font-size:13px;margin-right:4px;"></i> Feedback a líderes (Flujo 2)
</div>
<div class="lp-fb-grid lp-fade ld2">
<?php foreach ($feedbacksLideres as $fl): ?>
    <?php $flDone = (int)$fl['FIRMADO_LIDER']; ?>
    <div class="lp-fb-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
            <div>
                <div class="lp-fb-name"><?= htmlspecialchars($fl['LIDER']) ?></div>
                <div class="lp-fb-cargo"><?= htmlspecialchars($fl['CARGO']) ?></div>
            </div>
            <?php if ($flDone): ?>
                <span class="lp-st lp-st-ok" style="flex-shrink:0;"><i class="ti ti-check" style="font-size:11px;"></i> Completo</span>
            <?php else: ?>
                <span class="lp-st lp-st-pend" style="flex-shrink:0;">Pendiente firma</span>
            <?php endif; ?>
        </div>
        <div style="font-size:11px;color:#64748b;display:flex;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
            <?php if ($fl['FECHA_FEEDBACK']): ?>
            <span><i class="ti ti-calendar" style="font-size:12px;"></i> <?= htmlspecialchars($fl['FECHA_FEEDBACK']) ?></span>
            <?php endif; ?>
            <?php if ($flDone && $fl['FECHA_FIRMA_LIDER']): ?>
            <span><i class="ti ti-pen" style="font-size:12px;"></i> Firmó: <?= htmlspecialchars($fl['FECHA_FIRMA_LIDER']) ?></span>
            <?php endif; ?>
        </div>
        <?php if ($fl['OBSERVACION']): ?>
        <div style="font-size:12px;color:#64748b;border-top:1px solid #f1f5f9;padding-top:8px;margin-top:4px;line-height:1.5;">
            <?= nl2br(htmlspecialchars(mb_substr($fl['OBSERVACION'], 0, 120))) ?><?= mb_strlen($fl['OBSERVACION']) > 120 ? '…' : '' ?>
        </div>
        <?php endif; ?>
        <div class="lp-fb-meta">
            <a href="<?= APP_URL ?>feedback/" style="color:#0058af;font-size:11px;font-weight:600;text-decoration:none;">
                Ir a Feedback →
            </a>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; /* /hayFb2 */ ?>

<?php else: ?>
<div class="lp-section lp-fade ld2">
    <div class="lp-empty">No has registrado feedback en este período.</div>
</div>
<?php endif; /* /hayFb1 || hayFb2 */ ?>

<?php endif; /* /feedback */ ?>


<!-- ══════════════════════════════════════════════════════════
     TAB: ALERTAS
════════════════════════════════════════════════════════════ -->
<?php if ($activeTab === 'alertas'): ?>

<?php
$grupos = [
    ['key'=>'sin_auto',      'label'=>'Sin autoevaluación (Flujo 1)',       'color'=>'#dc2626', 'icon'=>'ti-user-x'],
    ['key'=>'sin_eval',      'label'=>'Sin evaluación del líder (Flujo 1)', 'color'=>'#d97706', 'icon'=>'ti-clipboard-x'],
    ['key'=>'sin_fb',        'label'=>'Sin feedback Flujo 1',               'color'=>'#7c3aed', 'icon'=>'ti-message-off'],
    ['key'=>'ac_pendientes', 'label'=>'Acuerdos pendientes Flujo 1',        'color'=>'#0058af', 'icon'=>'ti-clock-hour-4', 'isAcuerdo'=>true],
];
if ($esDirector) {
    $grupos[] = ['key'=>'sin_fb_2',        'label'=>'Líderes sin feedback Flujo 2',         'color'=>'#0891b2', 'icon'=>'ti-message-off'];
    $grupos[] = ['key'=>'ac_pendientes_2', 'label'=>'Acuerdos pendientes Flujo 2 (P12-16)', 'color'=>'#0e7490', 'icon'=>'ti-clock-hour-4', 'isAcuerdo'=>true];
}
$hayAlertas = false;
foreach ($grupos as $g) {
    if (!empty($alertas[$g['key']])) { $hayAlertas = true; break; }
}
?>

<?php if (!$hayAlertas): ?>
<div class="lp-section lp-fade ld2">
    <div class="lp-empty" style="color:#16a34a;">
        <i class="ti ti-circle-check" style="font-size:36px;display:block;margin-bottom:8px;"></i>
        ¡Sin alertas! Todo está al día en este período.
    </div>
</div>
<?php else: ?>

<?php foreach ($grupos as $g): ?>
<?php if (empty($alertas[$g['key']])) continue; ?>
<div class="lp-section lp-fade ld2">
    <div class="lp-alert-head" style="color:<?= $g['color'] ?>;">
        <i class="ti <?= $g['icon'] ?>" style="font-size:16px;"></i>
        <?= $g['label'] ?>
        <span style="font-size:10px;background:<?= $g['color'] ?>;color:#fff;padding:1px 7px;border-radius:99px;font-weight:700;">
            <?= count($alertas[$g['key']]) ?>
        </span>
    </div>
    <?php if (!empty($g['isAcuerdo'])): ?>
        <?php foreach ($alertas[$g['key']] as $it): ?>
        <div class="lp-alert-row" style="flex-direction:column;align-items:flex-start;gap:3px;">
            <div style="display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;">
                <div class="lp-alert-name"><?= htmlspecialchars($it['COLABORADOR']) ?></div>
                <?php if (!empty($it['CARGO'])): ?>
                <div style="font-size:11px;color:#64748b;font-weight:500;"><?= htmlspecialchars($it['CARGO']) ?></div>
                <?php endif; ?>
            </div>
            <div class="lp-alert-cargo"><?= htmlspecialchars($it['OBJETIVO']) ?></div>
            <?php if ($it['PLAZO']): ?><div style="font-size:11px;color:#94a3b8;">Plazo: <?= htmlspecialchars($it['PLAZO']) ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php foreach ($alertas[$g['key']] as $it): ?>
        <div class="lp-alert-row">
            <div class="lp-alert-name"><?= htmlspecialchars($it['NOMBRE']) ?></div>
            <div class="lp-alert-cargo"><?= htmlspecialchars($it['CARGO']) ?></div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php endif; /* /alertas */ ?>

</div><!-- /lp-root -->


<!-- ══════════════════════════════════════════════════════════
     MODAL: PERFIL DEL COLABORADOR
════════════════════════════════════════════════════════════ -->
<div id="lpModalOverlay">
    <div id="lpModal">
        <div class="lp-modal-head">
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="lp-modal-avatar" id="lpModalAvatar">?</div>
                <div>
                    <div style="font-size:16px;font-weight:700;color:#fff;" id="lpModalNombre">Cargando…</div>
                    <div style="font-size:12px;color:rgba(255,255,255,.65);" id="lpModalCargo"></div>
                </div>
            </div>
            <button class="lp-modal-close" onclick="lpCerrarModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="lp-modal-body" id="lpModalBody">
            <div style="text-align:center;padding:32px;color:#94a3b8;">
                <i class="ti ti-loader-2" style="font-size:28px;display:block;margin-bottom:8px;animation:lpSpin 1s linear infinite;"></i>
                Cargando perfil…
            </div>
        </div>
    </div>
</div>

<style>
@keyframes lpSpin { to { transform:rotate(360deg); } }
</style>

<script>
(function() {
    var APP_URL        = '<?= APP_URL ?>';
    var LP_ES_DIRECTOR = <?= $esDirector ? 'true' : 'false' ?>;
    var idPeriodo = <?= $periodoActivo ? (int)$periodoActivo['IDPERIODO'] : 0 ?>;

    window.lpAbrirPerfil = function(idColab) {
        var overlay = document.getElementById('lpModalOverlay');
        var body    = document.getElementById('lpModalBody');
        document.getElementById('lpModalNombre').textContent = 'Cargando…';
        document.getElementById('lpModalCargo').textContent  = '';
        document.getElementById('lpModalAvatar').textContent = '?';
        body.innerHTML = '<div style="text-align:center;padding:32px;color:#94a3b8;">' +
            '<i class="ti ti-loader-2" style="font-size:28px;display:block;margin-bottom:8px;animation:lpSpin 1s linear infinite;"></i>' +
            'Cargando perfil…</div>';
        overlay.classList.add('open');

        fetch(APP_URL + 'liderPanel/?action=getPerfilColaborador&idcolab=' + idColab +
              (idPeriodo ? '&idperiodo=' + idPeriodo : ''), {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.error) { body.innerHTML = '<div class="lp-empty">' + d.error + '</div>'; return; }
            renderPerfil(d);
        })
        .catch(function() { body.innerHTML = '<div class="lp-empty">Error al cargar el perfil.</div>'; });
    };

    window.lpCerrarModal = function() {
        document.getElementById('lpModalOverlay').classList.remove('open');
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') lpCerrarModal();
    });

    function renderPerfil(d) {
        var emp    = d.empleado       || {};
        var cals   = d.califs         || [];
        var calsL  = d.califsLider    || [];
        var acs    = d.acuerdos       || [];
        var acs2   = d.acuerdosFlujo2 || [];
        var fb     = d.feedback       || null;
        var fb2    = d.feedbackFlujo2 || null;
        var esLider= d.esLider        || false;

        var nombre = emp.NOMBRE || 'Colaborador';
        var cargo  = emp.CARGO  || '';
        document.getElementById('lpModalNombre').textContent = nombre + (esLider ? ' · Líder' : '');
        document.getElementById('lpModalCargo').textContent  = cargo;
        document.getElementById('lpModalAvatar').textContent = nombre.charAt(0).toUpperCase();

        var html = '';

        // ── Calificaciones Flujo 1 (como colaborador) ─────────────────────
        html += '<div class="lp-modal-section">';
        html += '<div class="lp-modal-section-title">Calificaciones — Flujo 1 (como colaborador)</div>';
        if (cals.length) {
            cals.forEach(function(c) {
                var v = parseInt(c.VALOR);
                var bg = v >= 4 ? '#dcfce7' : v >= 3 ? '#fef9c3' : '#fee2e2';
                var co = v >= 4 ? '#15803d' : v >= 3 ? '#854d0e' : '#dc2626';
                html += '<div class="lp-cal-row">' +
                    '<span class="lp-cal-name">P' + c.NUM_PREGUNTA + '. ' + esc(c.NOMBRE_COMP) + '</span>' +
                    '<span class="lp-cal-val" style="background:' + bg + ';color:' + co + ';">' + v + '/5 — ' + esc(c.ETIQUETA) + '</span>' +
                '</div>';
            });
        } else {
            html += '<div style="font-size:12px;color:#94a3b8;padding:4px 0;">Aún no has evaluado a este colaborador.</div>';
        }
        html += '</div>';

        // ── Calificaciones Flujo 2 (promedio como líder) — solo directores ──
        if (esLider && LP_ES_DIRECTOR) {
            html += '<div class="lp-modal-section">';
            html += '<div class="lp-modal-section-title">Calificaciones — Flujo 2 (promedio recibido como líder)</div>';
            if (calsL.length) {
                calsL.forEach(function(c) {
                    var v = parseInt(c.VALOR_PROM);
                    var bg = v >= 4 ? '#dcfce7' : v >= 3 ? '#fef9c3' : '#fee2e2';
                    var co = v >= 4 ? '#15803d' : v >= 3 ? '#854d0e' : '#dc2626';
                    html += '<div class="lp-cal-row">' +
                        '<span class="lp-cal-name">P' + c.NUM_PREGUNTA + '. ' + esc(c.NOMBRE_COMP) + '</span>' +
                        '<span class="lp-cal-val" style="background:' + bg + ';color:' + co + ';">' + v + '/5 prom.</span>' +
                    '</div>';
                });
            } else {
                html += '<div style="font-size:12px;color:#94a3b8;padding:4px 0;">Sin evaluaciones Flujo 2 registradas.</div>';
            }
            html += '</div>';
        }

        // ── Feedback Flujo 1 ──────────────────────────────────────────────
        html += '<div class="lp-modal-section">';
        html += '<div class="lp-modal-section-title">Feedback Flujo 1</div>';
        if (fb) {
            var fbDone = parseInt(fb.FIRMADO_COLAB);
            var stFb = fbDone
                ? '<span class="lp-st lp-st-ok">Completo</span>'
                : '<span class="lp-st lp-st-pend">Pendiente firma</span>';
            html += '<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;font-size:13px;">' +
                stFb +
                (fb.FECHA_FEEDBACK ? '<span style="color:#64748b;font-size:12px;"><i class="ti ti-calendar"></i> ' + esc(fb.FECHA_FEEDBACK) + '</span>' : '') +
                (fbDone && fb.FECHA_FIRMA ? '<span style="color:#64748b;font-size:12px;"><i class="ti ti-pen"></i> Firmó: ' + esc(fb.FECHA_FIRMA) + '</span>' : '') +
                '</div>';
            if (fb.OBSERVACION) {
                html += '<div style="font-size:12px;color:#64748b;margin-top:8px;line-height:1.5;">' + esc(fb.OBSERVACION).substring(0,200) + (fb.OBSERVACION.length > 200 ? '…' : '') + '</div>';
            }
        } else {
            html += '<div style="font-size:12px;color:#94a3b8;">Sin feedback Flujo 1.</div>';
        }
        html += '</div>';

        // ── Feedback Flujo 2 (si es líder) — solo directores ─────────────
        if (esLider && LP_ES_DIRECTOR) {
            html += '<div class="lp-modal-section">';
            html += '<div class="lp-modal-section-title">Feedback Flujo 2 (del director)</div>';
            if (fb2) {
                var fb2Done = parseInt(fb2.FIRMADO_LIDER);
                var stFb2 = fb2Done
                    ? '<span class="lp-st lp-st-ok">Completo</span>'
                    : '<span class="lp-st lp-st-pend">Pendiente firma</span>';
                html += '<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;font-size:13px;">' +
                    stFb2 +
                    (fb2.FECHA_FEEDBACK ? '<span style="color:#64748b;font-size:12px;"><i class="ti ti-calendar"></i> ' + esc(fb2.FECHA_FEEDBACK) + '</span>' : '') +
                    (fb2Done && fb2.FECHA_FIRMA_LIDER ? '<span style="color:#64748b;font-size:12px;"><i class="ti ti-pen"></i> Firmó: ' + esc(fb2.FECHA_FIRMA_LIDER) + '</span>' : '') +
                    (fb2.NOMBRE_DIRECTOR ? '<span style="color:#64748b;font-size:12px;">Director: ' + esc(fb2.NOMBRE_DIRECTOR) + '</span>' : '') +
                    '</div>';
                if (fb2.OBSERVACION) {
                    html += '<div style="font-size:12px;color:#64748b;margin-top:8px;line-height:1.5;">' + esc(fb2.OBSERVACION).substring(0,200) + (fb2.OBSERVACION.length > 200 ? '…' : '') + '</div>';
                }
            } else {
                html += '<div style="font-size:12px;color:#94a3b8;">Sin feedback Flujo 2 registrado.</div>';
            }
            html += '</div>';
        }

        // ── Acuerdos Flujo 1 ─────────────────────────────────────────────
        html += '<div class="lp-modal-section">';
        html += '<div class="lp-modal-section-title">Acuerdos Flujo 1' + (acs.length ? ' (' + acs.length + ')' : '') + '</div>';
        if (acs.length) {
            acs.forEach(function(a) {
                var ecls = a.ESTADO === 'APROBADO' ? 'lp-st-ok' : a.ESTADO === 'RESPONDIDO' ? 'lp-st-info' : 'lp-st-pend';
                html += '<div class="lp-ac-item">' +
                    '<div class="lp-ac-item-obj">' + esc(a.OBJETIVO) + '</div>' +
                    '<div class="lp-ac-item-meta">' +
                    '<span class="lp-st ' + ecls + '" style="font-size:10px;">' + esc(a.ESTADO) + '</span>' +
                    (a.PLAZO ? '<span>' + esc(a.PLAZO) + '</span>' : '') +
                    '</div>' +
                    (a.PLAN_ACCION ? '<div style="font-size:11px;color:#64748b;margin-top:4px;border-top:1px dashed #e2e8f0;padding-top:4px;">' + esc(a.PLAN_ACCION).substring(0,120) + '</div>' : '') +
                '</div>';
            });
        } else {
            html += '<div style="font-size:12px;color:#94a3b8;">Sin acuerdos Flujo 1.</div>';
        }
        html += '</div>';

        // ── Acuerdos Flujo 2 (si es líder) — solo directores ─────────────
        if (esLider && LP_ES_DIRECTOR) {
            html += '<div class="lp-modal-section">';
            html += '<div class="lp-modal-section-title">Acuerdos Flujo 2' + (acs2.length ? ' (' + acs2.length + ')' : '') + '</div>';
            if (acs2.length) {
                acs2.forEach(function(a) {
                    var ecls = a.ESTADO === 'APROBADO' ? 'lp-st-ok' : a.ESTADO === 'RESPONDIDO' ? 'lp-st-info' : 'lp-st-pend';
                    html += '<div class="lp-ac-item">' +
                        '<div class="lp-ac-item-obj">' + esc(a.OBJETIVO) + '</div>' +
                        '<div class="lp-ac-item-meta">' +
                        '<span class="lp-st ' + ecls + '" style="font-size:10px;">' + esc(a.ESTADO) + '</span>' +
                        (a.PLAZO ? '<span>' + esc(a.PLAZO) + '</span>' : '') +
                        '</div>' +
                        (a.PLAN_ACCION ? '<div style="font-size:11px;color:#64748b;margin-top:4px;border-top:1px dashed #e2e8f0;padding-top:4px;">' + esc(a.PLAN_ACCION).substring(0,120) + '</div>' : '') +
                    '</div>';
                });
            } else {
                html += '<div style="font-size:12px;color:#94a3b8;">Sin acuerdos Flujo 2.</div>';
            }
            html += '</div>';
        }

        // Enlace a reportes
        html += '<div style="border-top:1px solid #f1f5f9;padding-top:14px;text-align:center;">' +
            '<a href="' + APP_URL + 'reportes/?tab=equipo" style="font-size:12px;color:#0058af;font-weight:600;text-decoration:none;">' +
            '<i class="ti ti-external-link" style="font-size:13px;"></i> Ver en Reportes → Mi Equipo</a></div>';

        document.getElementById('lpModalBody').innerHTML = html;
    }

    function esc(s) {
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

<!-- ══════════════════════════════════════════════════════════
     TOUR DE AYUDA — PANEL LÍDER
══════════════════════════════════════════════════════════ -->
<style>
#lp-tour-btn {
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
#lp-tour-btn:hover {
    transform: scale(1.12);
    box-shadow: 0 6px 22px rgba(0,88,175,0.55);
}
</style>

<button id="lp-tour-btn" title="Tour de ayuda" onclick="iniciarTourLiderPanel()">?</button>

<script>
(function () {
    var activeTab    = '<?= htmlspecialchars($activeTab ?? 'dashboard', ENT_QUOTES) ?>';
    var esDirector   = <?= !empty($esDirector)   ? 'true' : 'false' ?>;
    var totalAlertas = <?= (int)($totalAlertas ?? 0) ?>;

    // ── Loader CDN ────────────────────────────────────────────────────────────
    function _lpLoadDriver(cb) {
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

    // ── Pasos del tour ────────────────────────────────────────────────────────
    window.iniciarTourLiderPanel = function () {
        _lpLoadDriver(function () {
            var driverFn = (window['driver'] && window['driver']['js'] && window['driver']['js']['driver'])
                ? window['driver']['js']['driver']
                : window['driver'];

            // Paso adaptativo — elemento y descripción según tab activo
            var tabEl, tabTitle, tabDesc;
            if (activeTab === 'dashboard') {
                tabEl    = '.lp-kpi-grid';
                tabTitle = ic('chart-bar') + ' KPIs del equipo';
                tabDesc  = 'Las tarjetas resumen muestran: <strong>total de colaboradores</strong>, ' +
                           'cuántos completaron su autoevaluación, cuántos fueron evaluados por ti y ' +
                           'cuántos tienen feedback completado. La barra inferior indica el porcentaje de avance.';
            } else if (activeTab === 'acuerdos') {
                tabEl    = '.lp-filter-row';
                tabTitle = ic('clipboard-list') + ' Acuerdos de mejora';
                tabDesc  = 'Aquí ves <strong>todos los objetivos SMART</strong> asignados a tu equipo en el proceso de feedback. ' +
                           'Filtra por estado (Pendiente / Respondido / Aprobado) para hacer seguimiento.';
            } else if (activeTab === 'feedback') {
                tabEl    = '.lp-fb-grid';
                tabTitle = ic('message-circle') + ' Feedback realizado';
                tabDesc  = 'Lista de <strong>conversaciones de feedback</strong> que has registrado con cada colaborador. ' +
                           (esDirector ? 'Como director también ves el feedback de liderazgo entregado a tus líderes. ' : '') +
                           'Aquí puedes verificar quiénes ya tienen su sesión documentada.';
            } else {
                tabEl    = '.lp-alert-section';
                tabTitle = ic('alert-triangle', '#d97706') + ' Alertas del equipo';
                tabDesc  = 'Vista rápida de <strong>colaboradores con pendientes críticos</strong>: ' +
                           'sin autoevaluación, sin evaluar, sin feedback, y acuerdos vencidos. ' +
                           (totalAlertas > 0
                               ? 'Actualmente hay <strong>' + totalAlertas + ' alerta(s)</strong> activas.'
                               : 'En este momento no hay alertas activas — ¡todo al día!');
            }

            var pasos = [
                // 0 — Bienvenida
                {
                    popover: {
                        title: ic('layout-dashboard') + ' Panel de Liderazgo',
                        description:
                            'Este es tu <strong>centro de mando</strong> como líder. Aquí consultas el avance ' +
                            'de tu equipo, los acuerdos de mejora, el registro de feedback y las alertas de pendientes. ' +
                            'Te guiamos en un recorrido rápido.',
                        side: 'over', align: 'center',
                    }
                },
                // 1 — Hero
                {
                    element: '.lp-hero',
                    popover: {
                        title: ic('flag') + ' Encabezado del panel',
                        description:
                            'Muestra el <strong>período de evaluación activo</strong>. Si tienes varios períodos ' +
                            'puedes cambiar de uno a otro con el selector para ver históricos.',
                        side: 'bottom', align: 'start',
                    }
                },
                // 2 — Tabs
                {
                    element: '.lp-tabs',
                    popover: {
                        title: ic('layout-navbar') + ' Pestañas',
                        description:
                            '<strong>Dashboard</strong> — KPIs y tabla de colaboradores.<br>' +
                            '<strong>Acuerdos</strong> — objetivos SMART asignados y su estado.<br>' +
                            '<strong>Feedback</strong> — sesiones de feedback registradas.<br>' +
                            '<strong>Alertas</strong> — pendientes críticos del equipo.' +
                            (totalAlertas > 0
                                ? '<br><span style="color:#dc2626;font-weight:600;">⚠ ' + totalAlertas + ' alerta(s) activas.</span>'
                                : ''),
                        side: 'bottom', align: 'center',
                    }
                },
                // 3 — Contenido adaptativo
                {
                    element: tabEl,
                    popover: {
                        title: tabTitle,
                        description: tabDesc,
                        side: 'top', align: 'start',
                    }
                },
            ];

            // Paso extra: tabla de colaboradores (solo en dashboard)
            if (activeTab === 'dashboard') {
                pasos.push({
                    element: '.lp-section',
                    popover: {
                        title: ic('users') + ' Tabla de colaboradores',
                        description:
                            'Lista completa de tu equipo con el estado de cada etapa: ' +
                            '<strong>autoevaluación</strong>, <strong>evaluación de tu parte</strong> y ' +
                            '<strong>feedback</strong>. Haz clic en cualquier fila para ver el perfil detallado ' +
                            'del colaborador con sus calificaciones y acuerdos.',
                        side: 'top', align: 'start',
                    }
                });
            }

            // Paso final
            pasos.push({
                popover: {
                    title: ic('circle-check', '#16a34a') + ' ¡Todo listo!',
                    description:
                        'Ya conoces el <strong>Panel de Liderazgo</strong>. Revisa las alertas periódicamente ' +
                        'para mantener a tu equipo al día. ' +
                        'Puedes repetir este recorrido pulsando el botón ' + helpBtn + ' en la esquina inferior derecha.',
                    side: 'over', align: 'center',
                }
            });

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
