<?php /* analitica-view.php — Módulo BI Admin */
// Valores por defecto para satisfacer el analizador estático del IDE.
// En ejecución el controlador siempre inyecta estas variables.
$periodoActivo   = $periodoActivo   ?? null;
$idPeriodoFiltro = $idPeriodoFiltro ?? 0;
$activeTab       = $activeTab       ?? 'dashboard';
$periodos        = $periodos        ?? [];
$kpis            = $kpis            ?? [];
$evolucion       = $evolucion       ?? [];
$ranking         = $ranking         ?? [];
$resumenProcesos = $resumenProcesos ?? [];
$acuerdos        = $acuerdos        ?? [];
$totalAcuerdos   = $totalAcuerdos   ?? 0;
$pageAc          = $pageAc          ?? 1;
$tipoEval        = $tipoEval        ?? 'LIDER_A_COLAB';
$proceso         = $proceso         ?? '';
$estadoAc        = $estadoAc        ?? '';
$alertas            = $alertas            ?? ['sin_autoeval' => [], 'acuerdos_vencidos' => [], 'lideres_sin_evaluar' => []];
$colaboradores      = $colaboradores      ?? [];
$totalColaboradores = $totalColaboradores ?? 0;
$pageColab          = $pageColab          ?? 1;
$filtrosColab       = $filtrosColab       ?? ['nombre' => '', 'proceso' => '', 'estado' => ''];
$procesosLista      = $procesosLista      ?? [];
$brechas            = $brechas            ?? [];
$expAzul            = $expAzul            ?? ['kpi' => [], 'competencias' => [], 'pendientes' => [], 'evaluados' => []];
$resumenFeedback    = $resumenFeedback    ?? ['kpis' => [], 'porLider' => []];
$topColaboradores    = $topColaboradores    ?? [];
$bottomColaboradores = $bottomColaboradores ?? [];

$_tabUrl = function(string $tab) use ($periodoActivo, $idPeriodoFiltro): string {
    $p = $idPeriodoFiltro ?: ($periodoActivo['IDPERIODO'] ?? 0);
    return APP_URL . 'analitica/?tab=' . $tab . ($p ? '&idperiodo=' . $p : '');
};
$_exportUrl = rtrim(str_replace('GestionHumana/', 'GestionHumana/exportar.php', APP_URL), '/');
$_periodoLabel = $periodoActivo ? htmlspecialchars($periodoActivo['NOMBRE'] ?? '', ENT_QUOTES) : 'Sin período activo';
?>
<style>
/* ── Variables ── */
:root {
    --an-primary:  #0058af;
    --an-dark:     #003d82;
    --an-success:  #16a34a;
    --an-warn:     #d97706;
    --an-danger:   #dc2626;
    --an-muted:    #64748b;
    --an-border:   #e2e8f0;
    --an-bg:       #f8fafc;
    --an-card:     #ffffff;
    --an-radius:   14px;
    --an-shadow:   0 1px 3px rgba(0,0,0,.06), 0 8px 24px rgba(0,0,0,.07);
    --an-font:     'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ── Layout ── */
.an-wrap {
    padding: 68px 32px 56px;
    font-family: var(--an-font);
    background: var(--an-bg);
    min-height: calc(100vh - 64px);
}
@media (max-width: 768px) { .an-wrap { padding: 68px 16px 56px; } }

/* ── Hero banner ── */
.an-hero {
    background: linear-gradient(135deg,#0058af 0%,#1a73e8 60%,#2563eb 100%);
    border-radius: 20px; padding: 28px 36px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 20px; margin-bottom: 24px; position: relative; overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,88,175,0.25);
}
.an-hero::before {
    content:''; position:absolute; width:260px; height:260px; border-radius:50%;
    background:rgba(255,255,255,0.06); top:-70px; right:-50px; pointer-events:none;
}
.an-hero-title { font-size:22px; font-weight:700; color:#fff; margin-bottom:4px; }
.an-hero-sub   { font-size:13px; color:rgba(255,255,255,0.65); }
.an-periodo-select {
    display: flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 10px; padding: 8px 12px;
    font-size: .82rem; font-family: var(--an-font);
    color: #fff; cursor: pointer; flex-shrink: 0; position: relative; z-index:1;
}
.an-periodo-select select {
    border: none; outline: none; background: transparent;
    font-size: .82rem; font-family: inherit; color: #fff; cursor: pointer;
}
.an-periodo-select option { color: #1e293b; background:#fff; }

/* ── Tabs ── */
.an-tabs {
    display: flex; gap: 4px; flex-wrap: wrap;
    background: var(--an-card);
    border: 1.5px solid var(--an-border);
    border-radius: var(--an-radius);
    padding: 6px;
    margin-bottom: 22px;
    box-shadow: var(--an-shadow);
}
.an-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 16px;
    border-radius: 10px;
    font-size: .8rem; font-weight: 600;
    text-decoration: none;
    color: var(--an-muted);
    transition: all .2s;
    white-space: nowrap;
}
.an-tab i { font-size: 15px; }
.an-tab:hover { background: #f1f5f9; color: #1e293b; }
.an-tab.active {
    background: var(--an-primary);
    color: #fff;
    box-shadow: 0 4px 12px rgba(0,88,175,.28);
}

/* ── Cards ── */
.an-card {
    background: var(--an-card);
    border-radius: var(--an-radius);
    box-shadow: var(--an-shadow);
    border: 1px solid var(--an-border);
    overflow: hidden;
}
.an-card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--an-border);
    gap: 10px; flex-wrap: wrap;
}
.an-card-title {
    font-size: .88rem; font-weight: 700; color: #1e293b;
    display: flex; align-items: center; gap: 7px; margin: 0;
}
.an-card-title i { font-size: 18px; color: var(--an-primary); }
.an-card-body { padding: 20px; }

/* ── KPI Grid ── */
.an-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
.an-kpi {
    background: var(--an-card);
    border-radius: var(--an-radius);
    border: 1px solid var(--an-border);
    padding: 18px 20px;
    box-shadow: var(--an-shadow);
    display: flex; flex-direction: column; gap: 6px;
    position: relative; overflow: hidden;
}
.an-kpi::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
}
.an-kpi.blue::before   { background: var(--an-primary); }
.an-kpi.green::before  { background: var(--an-success); }
.an-kpi.warn::before   { background: var(--an-warn); }
.an-kpi.red::before    { background: var(--an-danger); }
.an-kpi.purple::before { background: #7c3aed; }
.an-kpi-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; margin-bottom: 4px;
}
.blue  .an-kpi-icon { background: #dbeafe; color: var(--an-primary); }
.green .an-kpi-icon { background: #dcfce7; color: var(--an-success); }
.warn  .an-kpi-icon { background: #fef3c7; color: var(--an-warn); }
.red   .an-kpi-icon { background: #fee2e2; color: var(--an-danger); }
.purple .an-kpi-icon { background: #ede9fe; color: #7c3aed; }
.an-kpi-value {
    font-size: 1.9rem; font-weight: 800; color: #0f172a; line-height: 1;
}
.an-kpi-label { font-size: .73rem; font-weight: 600; color: var(--an-muted); text-transform: uppercase; letter-spacing: .5px; }
.an-kpi-sub   { font-size: .72rem; color: var(--an-muted); margin-top: 2px; }

/* ── Two-column charts row ── */
.an-charts-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px; margin-bottom: 22px;
}
@media (max-width: 900px) { .an-charts-row { grid-template-columns: 1fr; } }

/* ── Chart canvases ── */
.an-chart-wrap { position: relative; height: 240px; }
.an-chart-wrap-tall { position: relative; height: 320px; }

/* ── Table ── */
.an-table-wrap { overflow-x: auto; }
.an-table {
    width: 100%; border-collapse: collapse;
    font-size: .8rem;
}
.an-table th {
    background: #f8fafc;
    padding: 10px 14px;
    text-align: left;
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .5px;
    color: var(--an-muted);
    border-bottom: 1px solid var(--an-border);
    white-space: nowrap;
}
.an-table td {
    padding: 11px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}
.an-table tr:last-child td { border-bottom: none; }
.an-table tr:hover td { background: #f8fafc; }
.an-table .num { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; }
.an-table .bold { font-weight: 700; color: #1e293b; }

/* ── Badges ── */
.an-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 20px;
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px;
}
.an-badge.completo    { background: #dcfce7; color: #15803d; }
.an-badge.en_progreso { background: #fef3c7; color: #92400e; }
.an-badge.sin_iniciar { background: #f1f5f9; color: var(--an-muted); }
.an-badge.pendiente   { background: #fef3c7; color: #92400e; }
.an-badge.respondido  { background: #dbeafe; color: #1d4ed8; }
.an-badge.aprobado    { background: #dcfce7; color: #15803d; }

/* ── Progress bar ── */
.an-progress { height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; }
.an-progress-bar { height: 100%; border-radius: 3px; transition: width .4s; }
.an-progress-bar.blue   { background: var(--an-primary); }
.an-progress-bar.green  { background: var(--an-success); }
.an-progress-bar.warn   { background: var(--an-warn); }
.an-progress-bar.danger { background: var(--an-danger); }

/* ── Filter bar ── */
.an-filter-bar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 14px 20px;
    background: #f8fafc;
    border-bottom: 1px solid var(--an-border);
}
.an-filter-label { font-size: .75rem; font-weight: 700; color: var(--an-muted); text-transform: uppercase; letter-spacing: .4px; }
.an-filter-select, .an-filter-input {
    border: 1.5px solid var(--an-border);
    border-radius: 8px;
    padding: 7px 12px;
    font-size: .8rem; font-family: var(--an-font);
    color: #1e293b; background: #fff; outline: none;
    cursor: pointer;
}
.an-filter-select:focus, .an-filter-input:focus { border-color: var(--an-primary); }
.an-filter-input { min-width: 200px; }

/* ── Export btn ── */
.an-export-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px;
    background: linear-gradient(135deg, var(--an-primary), #2563eb);
    color: #fff; border: none; border-radius: 9px;
    font-size: .78rem; font-weight: 700; font-family: var(--an-font);
    cursor: pointer; transition: all .2s;
    box-shadow: 0 3px 10px rgba(0,88,175,.25);
    text-decoration: none;
}
.an-export-btn:hover { opacity: .9; transform: translateY(-1px); }
.an-export-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

/* ── Empty state ── */
.an-empty {
    text-align: center; padding: 48px 20px; color: var(--an-muted);
}
.an-empty i { font-size: 40px; margin-bottom: 10px; display: block; }
.an-empty p { font-size: .83rem; margin: 0; }

/* ── Alert sections (tab alertas) ── */
.an-alert-section { margin-bottom: 24px; }
.an-alert-header {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 20px;
    border-radius: 10px 10px 0 0;
    font-size: .83rem; font-weight: 700;
}
.an-alert-header.warn  { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.an-alert-header.red   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.an-alert-header.blue  { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
.an-alert-header i { font-size: 18px; }
.an-alert-count {
    margin-left: auto;
    background: rgba(0,0,0,.1);
    border-radius: 20px; padding: 2px 10px;
    font-size: .72rem;
}

/* ── Alert filter buttons ── */
.an-flt-btns { display: flex; gap: 8px; flex-wrap: wrap; }
.an-flt-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px; border-radius: 10px;
    border: 1.5px solid var(--an-border);
    background: var(--an-card); font-size: .8rem; font-weight: 600;
    font-family: var(--an-font); color: #334155; cursor: pointer;
    transition: all .18s;
}
.an-flt-btn:hover { border-color: var(--an-primary); color: var(--an-primary); }
.an-flt-btn.active { background: var(--an-primary); color: #fff; border-color: var(--an-primary); box-shadow: 0 3px 10px rgba(0,88,175,.25); }
.an-flt-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 22px; height: 20px; border-radius: 20px;
    font-size: .7rem; font-weight: 800; padding: 0 6px;
}
.an-flt-count.warn { background: #fef3c7; color: #92400e; }
.an-flt-count.red  { background: #fee2e2; color: #991b1b; }
.an-flt-count.blue { background: #dbeafe; color: #1d4ed8; }
.an-flt-btn.active .an-flt-count { background: rgba(255,255,255,.25); color: #fff; }

/* ── Justification highlight in modal ── */
.an-just-row {
    background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px;
    padding: 10px 12px; margin-bottom: 8px;
}
.an-just-comp { font-size: .72rem; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: .04em; }
.an-just-score { font-size: .75rem; font-weight: 800; color: var(--an-primary); margin: 2px 0; }
.an-just-text { font-size: .8rem; color: #1e293b; line-height: 1.5; }

/* ── Individal search ── */
.an-search-wrap { position: relative; max-width: 480px; }
.an-search-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--an-muted); font-size: 17px; pointer-events: none; }
.an-search-input {
    width: 100%;
    padding: 11px 14px 11px 40px;
    border: 1.5px solid var(--an-border);
    border-radius: 10px;
    font-size: .87rem; font-family: var(--an-font);
    color: #1e293b; background: #fff; outline: none;
    transition: border-color .18s;
}
.an-search-input:focus { border-color: var(--an-primary); box-shadow: 0 0 0 3px rgba(0,88,175,.1); }
.an-search-results { margin-top: 10px; }
.an-search-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 14px;
    border-radius: 10px; cursor: pointer;
    border: 1px solid var(--an-border);
    background: var(--an-card);
    margin-bottom: 6px;
    transition: all .15s;
}
.an-search-item:hover { border-color: var(--an-primary); background: #f0f7ff; }
.an-search-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--an-primary), #2563eb);
    color: #fff; font-size: .8rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.an-search-info { flex: 1; min-width: 0; }
.an-search-name { font-size: .83rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.an-search-sub  { font-size: .73rem; color: var(--an-muted); }

/* ── Modal perfil ── */
#an-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.5);
    z-index: 9990; display: none; align-items: center; justify-content: center;
    padding: 20px; backdrop-filter: blur(3px);
}
#an-modal-overlay.open { display: flex; }
.an-modal {
    background: var(--an-card);
    border-radius: 18px;
    width: 100%; max-width: 780px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: anModalIn .2s ease;
}
@keyframes anModalIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.an-modal-head {
    background: linear-gradient(135deg, var(--an-dark), var(--an-primary));
    padding: 22px 24px; border-radius: 18px 18px 0 0;
    display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;
    position: relative; overflow: hidden;
}
.an-modal-head::before {
    content:''; position:absolute; width:180px; height:180px; border-radius:50%;
    background:rgba(255,255,255,.06); top:-50px; right:-30px; pointer-events:none;
}
.an-modal-head-info h3 { font-size: 1.05rem; font-weight: 800; color: #fff; margin: 0 0 4px; }
.an-modal-head-info p  { font-size: .78rem; color: rgba(255,255,255,.72); margin: 0; }
.an-modal-close {
    background: rgba(255,255,255,.2); border: none; border-radius: 8px;
    width: 30px; height: 30px; cursor: pointer; color: #fff; font-size: 16px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    transition: background .15s; position: relative; z-index: 1;
}
.an-modal-close:hover { background: rgba(255,255,255,.35); }
.an-modal-body { padding: 22px 24px; }
.an-modal-section-title {
    font-size: .73rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .6px; color: var(--an-muted); margin: 16px 0 10px;
}
.an-modal-kpis { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 4px; }
.an-modal-kpi {
    flex: 1; min-width: 120px;
    background: #f8fafc; border-radius: 10px; padding: 12px 14px;
    border: 1px solid var(--an-border);
}
.an-modal-kpi-val { font-size: 1.5rem; font-weight: 800; color: var(--an-primary); line-height: 1; }
.an-modal-kpi-lbl { font-size: .71rem; color: var(--an-muted); margin-top: 3px; }

/* ── Pagination ── */
.an-pagination { display: flex; align-items: center; gap: 6px; padding: 14px 20px; justify-content: flex-end; }
.an-page-btn {
    padding: 6px 12px; border-radius: 7px;
    border: 1.5px solid var(--an-border);
    background: var(--an-card); color: #334155;
    font-size: .78rem; font-weight: 600; font-family: var(--an-font);
    cursor: pointer; text-decoration: none;
    transition: all .15s;
}
.an-page-btn:hover { border-color: var(--an-primary); color: var(--an-primary); }
.an-page-btn.active { background: var(--an-primary); color: #fff; border-color: var(--an-primary); }
.an-page-info { font-size: .78rem; color: var(--an-muted); }

/* ── Spinner ── */
.an-spinner {
    width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
    border-radius: 50%; animation: anSpin .7s linear infinite; display: none;
}
@keyframes anSpin { to { transform: rotate(360deg); } }
.an-export-btn.loading .an-spinner  { display: inline-block; }
.an-export-btn.loading .an-btn-text { display: none; }

/* ── V2: Brecha indicator ── */
.an-brecha {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 8px; border-radius: 20px;
    font-size: .73rem; font-weight: 700; white-space: nowrap;
}
.an-brecha.pos  { background: #fef3c7; color: #92400e; }
.an-brecha.neg  { background: #fee2e2; color: #991b1b; }
.an-brecha.zero { background: #f1f5f9; color: var(--an-muted); }

/* ── V2: Exp Azul badges ── */
.an-badge.ea-ok      { background: #dbeafe; color: #1d4ed8; }
.an-badge.ea-pending { background: #fef3c7; color: #92400e; }
.an-badge.ea-na      { background: #f1f5f9; color: #94a3b8; letter-spacing:.03em; }

/* ── V2: Colaboradores chip filters ── */
.an-chip-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px; }
.an-chip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 20px;
    border: 1.5px solid var(--an-border);
    background: var(--an-card); font-size: .75rem;
    font-weight: 600; cursor: pointer; color: #334155;
    transition: all .15s; text-decoration: none;
}
.an-chip:hover,
.an-chip.active { background: var(--an-primary); color: #fff; border-color: var(--an-primary); }

/* ── V2: Score pill ── */
.an-score {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 44px; padding: 2px 8px;
    border-radius: 20px; font-size: .78rem; font-weight: 800;
}
.an-score.s-hi   { background: #dcfce7; color: #15803d; }
.an-score.s-mid  { background: #dbeafe; color: #1d4ed8; }
.an-score.s-low  { background: #fef3c7; color: #92400e; }
.an-score.s-bad  { background: #fee2e2; color: #991b1b; }
.an-score.s-none { background: #f1f5f9; color: var(--an-muted); }

/* ── Top Colaboradores — Ranking cards ── */
.an-top-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    padding: 0 20px 20px;
}
.an-top-card {
    position: relative; background: var(--an-card);
    border: 1.5px solid var(--an-border); border-radius: 12px;
    padding: 16px 14px 14px; cursor: pointer;
    transition: box-shadow .18s, border-color .18s;
    text-align: center;
}
.an-top-card:hover { box-shadow: 0 4px 18px rgba(0,88,175,.13); border-color: var(--an-primary); }
.an-top-card:nth-child(1) { border-color: #f59e0b; }
.an-top-card:nth-child(2) { border-color: #94a3b8; }
.an-top-card:nth-child(3) { border-color: #b45309; }
.an-top-rank {
    position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
    background: var(--an-primary); color: #fff;
    font-size: .68rem; font-weight: 800; border-radius: 20px;
    padding: 2px 10px; letter-spacing: .04em;
}
.an-top-card:nth-child(1) .an-top-rank { background: #f59e0b; }
.an-top-card:nth-child(2) .an-top-rank { background: #64748b; }
.an-top-card:nth-child(3) .an-top-rank { background: #b45309; }
.an-top-avatar {
    width: 46px; height: 46px; border-radius: 50%;
    background: linear-gradient(135deg,#0058af22,#0058af44);
    display: flex; align-items: center; justify-content: center;
    margin: 10px auto 8px; font-size: 1.3rem; color: var(--an-primary);
}
.an-top-name { font-size: .82rem; font-weight: 700; color: #1e293b; line-height: 1.3; margin-bottom: 2px; }
.an-top-cargo { font-size: .70rem; color: var(--an-muted); margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.an-top-score {
    font-size: 1.4rem; font-weight: 900;
    color: var(--an-primary); line-height: 1;
    margin-bottom: 6px;
}
.an-top-score span { font-size: .70rem; font-weight: 500; color: var(--an-muted); }
.an-top-stats { display: flex; justify-content: center; gap: 8px; font-size: .68rem; color: var(--an-muted); }
.an-top-stats b { color: #334155; }

/* ── Ranking profile modal ── */
#rkModalOverlay {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,.45); z-index: 9000;
    align-items: center; justify-content: center;
}
#rkModalOverlay.open { display: flex; }
#rkModal {
    background: #fff; border-radius: 16px; width: 640px; max-width: 96vw;
    max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.2);
    padding: 28px 28px 20px; position: relative;
}
.rk-modal-close {
    position: absolute; top: 14px; right: 16px;
    background: none; border: none; font-size: 1.5rem;
    cursor: pointer; color: var(--an-muted); line-height: 1;
}
.rk-modal-head { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
.rk-modal-avatar {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg,#0058af22,#0058af55);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: var(--an-primary); flex-shrink: 0;
}
.rk-modal-info h4 { font-size: 1.05rem; font-weight: 800; margin: 0 0 2px; }
.rk-modal-info p  { font-size: .78rem; color: var(--an-muted); margin: 0; }
.rk-section-title {
    font-size: .72rem; font-weight: 800; letter-spacing: .07em;
    text-transform: uppercase; color: var(--an-muted);
    border-bottom: 1px solid var(--an-border);
    padding-bottom: 4px; margin: 16px 0 10px;
}
.rk-kpi-row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
.rk-kpi {
    flex: 1; min-width: 90px; background: #f8fafc;
    border: 1px solid var(--an-border); border-radius: 10px;
    padding: 10px 12px; text-align: center;
}
.rk-kpi-val { font-size: 1.35rem; font-weight: 900; color: var(--an-primary); line-height: 1; }
.rk-kpi-lbl { font-size: .65rem; color: var(--an-muted); margin-top: 2px; }
#rkLoadingMsg { text-align: center; padding: 40px; color: var(--an-muted); }
#rkModalContent { display: none; }

/* ── Acuerdos expandible ── */
.ac-row-main { cursor: pointer; }
.ac-row-main:hover td { background: #f0f7ff !important; }
.ac-row-main td:first-child::before {
    content: '▶'; font-size: .55rem; color: var(--an-muted);
    margin-right: 4px; display: inline-block; transition: transform .15s;
}
.ac-row-main.expanded td:first-child::before { transform: rotate(90deg); }
.ac-detail-tr { display: none; }
.ac-detail-tr.open { display: table-row; }
.ac-detail-panel {
    padding: 14px 20px 16px; background: #f8fafc;
    border-bottom: 2px solid var(--an-border);
}
.ac-detail-title {
    font-size: .70rem; font-weight: 800; letter-spacing: .07em;
    text-transform: uppercase; color: var(--an-muted);
    margin-bottom: 10px;
}
.ac-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px;
}
.ac-det-field { background: #fff; border: 1px solid var(--an-border); border-radius: 8px; padding: 8px 10px; }
.ac-det-lbl { font-size: .62rem; font-weight: 800; text-transform: uppercase; color: var(--an-muted); letter-spacing: .05em; margin-bottom: 3px; }
.ac-det-val { font-size: .78rem; color: #1e293b; line-height: 1.4; }
.ac-det-full { grid-column: 1/-1; }
</style>

<!-- Chart.js debe cargarse antes de los scripts que usan `new Chart()` -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="an-wrap">

    <!-- ── Hero ── -->
    <div class="an-hero">
        <div style="z-index:1;">
            <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;
                        color:rgba(255,255,255,0.55);margin-bottom:6px;">
                Clínica Zayma · Evaluación de Desempeño
            </div>
            <div class="an-hero-title">Analítica BI</div>
            <div class="an-hero-sub">Indicadores de desempeño y gestión humana · <?= $_periodoLabel ?></div>
        </div>
        <form method="GET" action="" style="display:flex;align-items:center;gap:8px;position:relative;z-index:1;">
            <input type="hidden" name="views" value="analitica">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab, ENT_QUOTES) ?>">
            <div class="an-periodo-select">
                <i class="ti ti-calendar" style="font-size:15px;"></i>
                <select name="idperiodo" onchange="this.form.submit()">
                    <option value="">Todos los períodos</option>
                    <?php foreach ($periodos as $p):
                        $sel = ($periodoActivo && (int)$p['IDPERIODO'] === (int)$periodoActivo['IDPERIODO']) ? 'selected' : ''; ?>
                    <option value="<?= (int)$p['IDPERIODO'] ?>" <?= $sel ?>><?= htmlspecialchars($p['NOMBRE'] ?? '', ENT_QUOTES) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- ── Tab Navigation ── -->
    <div class="an-tabs">
        <?php
        $tabs = [
            ['id'=>'dashboard',     'icon'=>'ti-layout-dashboard', 'label'=>'Dashboard'],
            ['id'=>'colaboradores', 'icon'=>'ti-users',            'label'=>'Colaboradores'],
            ['id'=>'individual',    'icon'=>'ti-user-search',      'label'=>'Individual'],
            ['id'=>'ranking',       'icon'=>'ti-trophy',           'label'=>'Ranking'],
            ['id'=>'brechas',       'icon'=>'ti-git-compare',      'label'=>'Brechas'],
            ['id'=>'historico',     'icon'=>'ti-timeline',         'label'=>'Histórico'],
            ['id'=>'acuerdos',      'icon'=>'ti-clipboard-list',   'label'=>'Acuerdos'],
            ['id'=>'exp_azul',      'icon'=>'ti-droplet',          'label'=>'Exp. Azul'],
            ['id'=>'alertas',       'icon'=>'ti-alert-triangle',   'label'=>'Alertas'],
            ['id'=>'feedback',      'icon'=>'ti-message-check',    'label'=>'Feedback'],
        ];
        foreach ($tabs as $t): ?>
        <a href="<?= $_tabUrl($t['id']) ?>" class="an-tab <?= $activeTab === $t['id'] ? 'active' : '' ?>">
            <i class="ti <?= $t['icon'] ?>"></i> <?= $t['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: DASHBOARD -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php if ($activeTab === 'dashboard'): ?>

    <!-- KPI cards -->
    <div class="an-kpi-grid">
        <?php
        $pct_ae  = $kpis['PCT_AUTOEVAL']  ?? 0;
        $pct_ev  = $kpis['PCT_EVALUADOS'] ?? 0;
        $prom    = number_format((float)($kpis['PROMEDIO_GLOBAL'] ?? 0), 2);
        $total   = (int)($kpis['TOTAL_EMPLEADOS'] ?? 0);
        $comp    = (int)($kpis['COMPLETOS'] ?? 0);
        $pct_comp = $total > 0 ? round($comp * 100 / $total, 1) : 0;
        ?>
        <div class="an-kpi blue">
            <div class="an-kpi-icon"><i class="ti ti-users"></i></div>
            <div class="an-kpi-value"><?= $total ?></div>
            <div class="an-kpi-label">Total empleados</div>
        </div>
        <div class="an-kpi green">
            <div class="an-kpi-icon"><i class="ti ti-user-check"></i></div>
            <div class="an-kpi-value"><?= $pct_ae ?>%</div>
            <div class="an-kpi-label">Con autoevaluación</div>
            <div class="an-kpi-sub"><?= (int)($kpis['CON_AUTOEVAL'] ?? 0) ?> colaboradores</div>
        </div>
        <div class="an-kpi warn">
            <div class="an-kpi-icon"><i class="ti ti-star"></i></div>
            <div class="an-kpi-value"><?= $pct_ev ?>%</div>
            <div class="an-kpi-label">Evaluados por líder</div>
            <div class="an-kpi-sub"><?= (int)($kpis['EVALUADOS_LIDER'] ?? 0) ?> colaboradores</div>
        </div>
        <div class="an-kpi green">
            <div class="an-kpi-icon"><i class="ti ti-circle-check"></i></div>
            <div class="an-kpi-value"><?= $pct_comp ?>%</div>
            <div class="an-kpi-label">Proceso completo</div>
            <div class="an-kpi-sub"><?= $comp ?> / <?= $total ?></div>
        </div>
        <div class="an-kpi purple">
            <div class="an-kpi-icon"><i class="ti ti-chart-bar"></i></div>
            <div class="an-kpi-value"><?= $prom ?></div>
            <div class="an-kpi-label">Promedio global</div>
            <div class="an-kpi-sub">Escala 1–5</div>
        </div>
        <div class="an-kpi blue">
            <div class="an-kpi-icon"><i class="ti ti-clipboard-list"></i></div>
            <div class="an-kpi-value"><?= (int)($kpis['ACUERDOS_TOTAL'] ?? 0) ?></div>
            <div class="an-kpi-label">Acuerdos totales</div>
        </div>
        <div class="an-kpi warn">
            <div class="an-kpi-icon"><i class="ti ti-clock"></i></div>
            <div class="an-kpi-value"><?= (int)($kpis['ACUERDOS_PENDIENTES'] ?? 0) ?></div>
            <div class="an-kpi-label">Pendientes</div>
        </div>
        <div class="an-kpi green">
            <div class="an-kpi-icon"><i class="ti ti-check"></i></div>
            <div class="an-kpi-value"><?= (int)($kpis['ACUERDOS_APROBADOS'] ?? 0) ?></div>
            <div class="an-kpi-label">Aprobados</div>
        </div>
    </div>

    <!-- Charts row -->
    <div class="an-charts-row">
        <!-- Donut: estado evaluación -->
        <div class="an-card">
            <div class="an-card-head">
                <h3 class="an-card-title"><i class="ti ti-chart-donut"></i> Estado evaluación</h3>
            </div>
            <div class="an-card-body">
                <div class="an-chart-wrap">
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>
        </div>
        <!-- Line: evolución histórica -->
        <div class="an-card">
            <div class="an-card-head">
                <h3 class="an-card-title"><i class="ti ti-timeline"></i> Evolución histórica</h3>
            </div>
            <div class="an-card-body">
                <div class="an-chart-wrap">
                    <canvas id="chartEvolucion"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por proceso -->
    <div class="an-card" style="margin-bottom:0">
        <div class="an-card-head">
            <h3 class="an-card-title"><i class="ti ti-building"></i> Avance por proceso</h3>
            <button class="an-export-btn" onclick="anExportar('resumen_procesos',this)">
                <div class="an-spinner"></div>
                <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar</span>
            </button>
        </div>
        <div class="an-table-wrap">
            <table class="an-table">
                <thead>
                    <tr>
                        <th>Proceso</th>
                        <th class="num">Total</th>
                        <th class="num">Autoevaluaron</th>
                        <th class="num">Evaluados</th>
                        <th>% Autoeval</th>
                        <th>% Evaluados</th>
                        <th class="num">Ac. Pendientes</th>
                        <th class="num">Ac. Aprobados</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($resumenProcesos)): ?>
                <tr><td colspan="8"><div class="an-empty"><i class="ti ti-database-off"></i><p>Sin datos</p></div></td></tr>
                <?php else: foreach ($resumenProcesos as $rp): ?>
                <tr>
                    <td class="bold"><?= htmlspecialchars($rp['PROCESO'] ?? '', ENT_QUOTES) ?></td>
                    <td class="num"><?= (int)($rp['TOTAL'] ?? 0) ?></td>
                    <td class="num"><?= (int)($rp['CON_AUTOEVAL'] ?? 0) ?></td>
                    <td class="num"><?= (int)($rp['EVALUADOS'] ?? 0) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="an-progress" style="flex:1;min-width:60px">
                                <div class="an-progress-bar blue" style="width:<?= min(100,(float)($rp['PCT_AUTOEVAL']??0)) ?>%"></div>
                            </div>
                            <span style="font-size:.75rem;font-weight:700;min-width:34px"><?= (float)($rp['PCT_AUTOEVAL'] ?? 0) ?>%</span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <?php $pct = (float)($rp['PCT_EVALUADOS'] ?? 0); $cls = $pct >= 80 ? 'green' : ($pct >= 50 ? 'warn' : 'danger'); ?>
                            <div class="an-progress" style="flex:1;min-width:60px">
                                <div class="an-progress-bar <?= $cls ?>" style="width:<?= min(100,$pct) ?>%"></div>
                            </div>
                            <span style="font-size:.75rem;font-weight:700;min-width:34px"><?= $pct ?>%</span>
                        </div>
                    </td>
                    <td class="num"><?= (int)($rp['ACUERDOS_PEND'] ?? 0) ?></td>
                    <td class="num"><?= (int)($rp['ACUERDOS_APRO'] ?? 0) ?></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // JSON para charts
    $completos   = (int)($kpis['COMPLETOS']    ?? 0);
    $enProgreso  = (int)($kpis['EN_PROGRESO']  ?? 0);
    $sinIniciar  = (int)($kpis['SIN_INICIAR']  ?? 0);
    $evoLabels   = json_encode(array_column($evolucion, 'FECHA_CORTA'));
    $evoDesempen = json_encode(array_map(fn($r) => (float)($r['PROM_DESEMPENO'] ?? 0), $evolucion));
    $evoLider    = json_encode(array_map(fn($r) => (float)($r['PROM_LIDERAZGO'] ?? 0), $evolucion));
    $evoAuto     = json_encode(array_map(fn($r) => (float)($r['PROM_AUTOEVAL']  ?? 0), $evolucion));
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Donut — estado evaluación
        new Chart(document.getElementById('chartEstado'), {
            type: 'doughnut',
            data: {
                labels: ['Completo', 'En progreso', 'Sin iniciar'],
                datasets: [{ data: [<?= $completos ?>, <?= $enProgreso ?>, <?= $sinIniciar ?>],
                    backgroundColor: ['#16a34a','#d97706','#e2e8f0'],
                    borderWidth: 0, hoverOffset: 6 }]
            },
            options: {
                cutout: '68%', responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11, family: 'Plus Jakarta Sans' } } },
                    tooltip: { callbacks: { label: function(ctx) { return ' ' + ctx.label + ': ' + ctx.raw; } } }
                }
            }
        });

        // Line — evolución
        new Chart(document.getElementById('chartEvolucion'), {
            type: 'line',
            data: {
                labels: <?= $evoLabels ?>,
                datasets: [
                    { label: 'Desempeño', data: <?= $evoDesempen ?>, borderColor: '#0058af', backgroundColor: 'rgba(0,88,175,.08)', tension: .35, fill: true, pointRadius: 4, pointBackgroundColor: '#0058af' },
                    { label: 'Liderazgo', data: <?= $evoLider ?>,    borderColor: '#7c3aed', backgroundColor: 'transparent', tension: .35, fill: false, pointRadius: 4, pointBackgroundColor: '#7c3aed' },
                    { label: 'Autoeval',  data: <?= $evoAuto ?>,     borderColor: '#16a34a', backgroundColor: 'transparent', tension: .35, fill: false, pointRadius: 4, pointBackgroundColor: '#16a34a', borderDash: [5,4] },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: {
                    y: { min: 0, max: 5, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } }
            }
        });
    });
    </script>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: INDIVIDUAL -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'individual'): ?>

    <div class="an-card">
        <div class="an-card-head">
            <h3 class="an-card-title"><i class="ti ti-user-search"></i> Búsqueda de colaborador</h3>
        </div>
        <div class="an-card-body">
            <div class="an-search-wrap">
                <i class="ti ti-search an-search-icon"></i>
                <input type="text" id="anSearchInput" class="an-search-input"
                       placeholder="Buscar por nombre, cédula o cargo..."
                       autocomplete="off">
            </div>
            <div id="anSearchResults" class="an-search-results"></div>
        </div>
    </div>

    <!-- Modal perfil -->
    <div id="an-modal-overlay" onclick="if(event.target===this)anCloseModal()">
        <div class="an-modal" id="an-modal">
            <div class="an-modal-head">
                <div class="an-modal-head-info">
                    <h3 id="modalNombre">—</h3>
                    <p id="modalSub">—</p>
                </div>
                <button class="an-modal-close" onclick="anCloseModal()"><i class="ti ti-x"></i></button>
            </div>
            <div class="an-modal-body" id="modalBody">
                <div class="an-empty"><i class="ti ti-loader an-spinner" style="display:inline-block;width:28px;height:28px;border-color:rgba(0,88,175,.3);border-top-color:var(--an-primary);"></i></div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var debounceTimer;
        var AN_BASE = '<?= APP_URL ?>?views=analitica';
        var currentPeriodo = <?= (int)($idPeriodoFiltro ?: ($periodoActivo['IDPERIODO'] ?? 0)) ?>;

        document.getElementById('anSearchInput').addEventListener('input', function() {
            clearTimeout(debounceTimer);
            var q = this.value.trim();
            if (q.length < 2) { document.getElementById('anSearchResults').innerHTML = ''; return; }
            debounceTimer = setTimeout(function() { anSearch(q); }, 320);
        });

        window.anSearch = function(q) {
            var url = AN_BASE + '&action=buscarColaboradores&q=' + encodeURIComponent(q)
                    + (currentPeriodo ? '&idperiodo=' + currentPeriodo : '');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(rows) {
                    var el = document.getElementById('anSearchResults');
                    if (!rows || rows.length === 0) {
                        el.innerHTML = '<div class="an-empty"><i class="ti ti-search-off" style="font-size:28px"></i><p>Sin resultados</p></div>';
                        return;
                    }
                    var html = '';
                    rows.forEach(function(r) {
                        var initials = (r.NOMBRE || '').split(' ').slice(0,2).map(function(w){return w[0]||'';}).join('').toUpperCase();
                        var bdg = r.ESTADO === 'completo' ? 'completo' : (r.ESTADO === 'en_progreso' ? 'en_progreso' : 'sin_iniciar');
                        var bdgLabel = r.ESTADO === 'completo' ? 'Completo' : (r.ESTADO === 'en_progreso' ? 'En progreso' : 'Sin iniciar');
                        html += '<div class="an-search-item" onclick="anOpenPerfil(' + r.IDEMPLEADO + ')">'
                              + '<div class="an-search-avatar">' + initials + '</div>'
                              + '<div class="an-search-info">'
                              + '<div class="an-search-name">' + (r.NOMBRE || '') + '</div>'
                              + '<div class="an-search-sub">' + (r.CARGO || '') + ' &bull; ' + (r.PROCESO || '') + '</div>'
                              + '</div>'
                              + '<span class="an-badge ' + bdg + '">' + bdgLabel + '</span>'
                              + '</div>';
                    });
                    el.innerHTML = html;
                })
                .catch(function() {
                    document.getElementById('anSearchResults').innerHTML = '<div class="an-empty"><i class="ti ti-wifi-off" style="font-size:28px"></i><p>Error de conexión</p></div>';
                });
        };

        window.anOpenPerfil = function(idEmp) {
            document.getElementById('an-modal-overlay').classList.add('open');
            document.getElementById('modalNombre').textContent = 'Cargando...';
            document.getElementById('modalSub').textContent = '';
            document.getElementById('modalBody').innerHTML = '<div class="an-empty" style="padding:40px"><div style="width:28px;height:28px;border:3px solid #dbeafe;border-top-color:var(--an-primary);border-radius:50%;animation:anSpin .7s linear infinite;margin:0 auto"></div></div>';

            var url = AN_BASE + '&action=getPerfilColaborador&idempleado=' + idEmp
                    + (currentPeriodo ? '&idperiodo=' + currentPeriodo : '');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) { anRenderModal(data); })
                .catch(function() {
                    document.getElementById('modalBody').innerHTML = '<div class="an-empty"><i class="ti ti-wifi-off"></i><p>Error al cargar el perfil</p></div>';
                });
        };

        window.anCloseModal = function() {
            document.getElementById('an-modal-overlay').classList.remove('open');
        };

        function fmtProm(v) { return v > 0 ? parseFloat(v).toFixed(2) : '—'; }

        window.anRenderModal = function(data) {
            var emp  = data.empleado  || {};
            var prom = data.promedios || {};
            var ac   = data.acuerdos  || {};
            var hist = data.historial || [];
            var comp = data.competencias || [];

            document.getElementById('modalNombre').textContent = emp.NOMBRE_COMPLETO || emp.NOMBRE || '—';
            document.getElementById('modalSub').textContent = (emp.CARGO || '') + ' · ' + (emp.PROCESO || '');

            var promDesem = prom['LIDER_A_COLAB'] ? fmtProm(prom['LIDER_A_COLAB'].PROMEDIO) : '—';
            var promAuto  = prom['AUTO']          ? fmtProm(prom['AUTO'].PROMEDIO)           : '—';
            var promLider = prom['COLAB_A_LIDER'] ? fmtProm(prom['COLAB_A_LIDER'].PROMEDIO)  : '—';

            var html = '';
            html += '<div class="an-modal-section-title">Promedios del período</div>';
            html += '<div class="an-modal-kpis">'
                  + '<div class="an-modal-kpi"><div class="an-modal-kpi-val">' + promDesem + '</div><div class="an-modal-kpi-lbl">Desempeño (líder)</div></div>'
                  + '<div class="an-modal-kpi"><div class="an-modal-kpi-val">' + promAuto  + '</div><div class="an-modal-kpi-lbl">Autoevaluación</div></div>'
                  + '<div class="an-modal-kpi"><div class="an-modal-kpi-val">' + promLider + '</div><div class="an-modal-kpi-lbl">Eval. al líder</div></div>'
                  + '<div class="an-modal-kpi"><div class="an-modal-kpi-val">' + (ac.TOTAL || 0) + '</div><div class="an-modal-kpi-lbl">Acuerdos</div></div>'
                  + '</div>';

            // Competencias — tabla resumida
            if (comp.length > 0) {
                var lcComp = comp.filter(function(c){ return c.TIPO_EVAL === 'LIDER_A_COLAB'; });
                if (lcComp.length > 0) {
                    html += '<div class="an-modal-section-title">Competencias evaluadas por líder</div>';
                    html += '<div class="an-table-wrap"><table class="an-table"><thead><tr><th>#</th><th>Competencia</th><th class="num">Promedio</th><th>Nivel</th></tr></thead><tbody>';
                    lcComp.forEach(function(c) {
                        var val = parseFloat(c.PROMEDIO) || 0;
                        var bar = val > 0 ? Math.round(val/5*100) : 0;
                        var cls = val >= 4 ? 'green' : (val >= 3 ? 'blue' : (val >= 2 ? 'warn' : 'danger'));
                        html += '<tr><td>' + (c.NUM_PREGUNTA||'') + '</td>'
                              + '<td class="bold">' + (c.NOMBRE_COMP||'') + '</td>'
                              + '<td class="num">' + (val > 0 ? val.toFixed(2) : '—') + '</td>'
                              + '<td style="min-width:100px"><div class="an-progress"><div class="an-progress-bar ' + cls + '" style="width:' + bar + '%"></div></div></td>'
                              + '</tr>';
                    });
                    html += '</tbody></table></div>';
                }
            }

            // Evolución histórica personal
            if (hist.length > 0) {
                html += '<div class="an-modal-section-title">Evolución histórica personal</div>';
                html += '<div style="position:relative;height:180px"><canvas id="chartPerfil"></canvas></div>';
            }

            document.getElementById('modalBody').innerHTML = html;

            if (hist.length > 0) {
                var labels = hist.map(function(h){ return h.PERIODO_LABEL || ''; });
                var daAuto  = hist.map(function(h){ return parseFloat(h.PROM_AUTO)  || null; });
                var daLider = hist.map(function(h){ return parseFloat(h.PROM_LIDER) || null; });
                new Chart(document.getElementById('chartPerfil'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Autoeval', data: daAuto, borderColor:'#16a34a', tension:.3, pointRadius:3, fill:false },
                            { label: 'Lider',    data: daLider,borderColor:'#0058af', tension:.3, pointRadius:3, fill:false }
                        ]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false,
                        scales: { y:{ min:0,max:5,ticks:{stepSize:1}, grid:{color:'#f1f5f9'} }, x:{grid:{display:false}} },
                        plugins: { legend:{ labels:{ boxWidth:10, font:{size:10} } } }
                    }
                });
            }
        };
    })();
    </script>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: RANKING -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'ranking'): ?>

    <?php if (!empty($topColaboradores)): ?>
    <div class="an-card" style="margin-bottom:16px;">
        <div class="an-card-head">
            <h3 class="an-card-title"><i class="ti ti-trophy"></i> Top <?= count($topColaboradores) ?> — Mejores promedios</h3>
            <span style="font-size:.72rem;color:var(--an-muted)">Clic en una tarjeta para ver el perfil completo</span>
        </div>
        <div class="an-top-grid">
        <?php foreach ($topColaboradores as $i => $tc):
            $prom    = (float)($tc['PROMEDIO'] ?? 0);
            $promCls = $prom >= 4 ? 'green' : ($prom >= 3 ? 'blue' : ($prom >= 2 ? 'warn' : 'danger'));
            $initials= implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w,0,1)), array_slice(explode(' ', $tc['NOMBRE'] ?? 'N'), 0, 2)));
        ?>
        <div class="an-top-card" onclick="anOpenRankPerfil(<?= (int)$tc['IDEMPLEADO'] ?>)">
            <div class="an-top-rank">#<?= $i + 1 ?></div>
            <div class="an-top-avatar"><span style="font-weight:800;font-size:.95rem"><?= htmlspecialchars($initials, ENT_QUOTES) ?></span></div>
            <div class="an-top-name"><?= htmlspecialchars($tc['NOMBRE'] ?? '', ENT_QUOTES) ?></div>
            <div class="an-top-cargo" title="<?= htmlspecialchars($tc['CARGO'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars(mb_strimwidth($tc['CARGO'] ?? '', 0, 28, '…'), ENT_QUOTES) ?></div>
            <div class="an-top-score"><?= number_format($prom, 2) ?><span> / 5</span></div>
            <div class="an-top-stats">
                <span><i class="ti ti-clipboard-list" style="font-size:.72rem"></i> <b><?= (int)($tc['TOTAL_AC'] ?? 0) ?></b> ac.</span>
                <?php if ((int)($tc['PEND_AC'] ?? 0) > 0): ?>
                <span style="color:#92400e"><b><?= (int)$tc['PEND_AC'] ?></b> pend.</span>
                <?php endif; ?>
                <?php if ((int)($tc['APRO_AC'] ?? 0) > 0): ?>
                <span style="color:#15803d"><b><?= (int)$tc['APRO_AC'] ?></b> apro.</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($bottomColaboradores)): ?>
    <div class="an-card" style="margin-bottom:16px;">
        <div class="an-card-head">
            <h3 class="an-card-title"><i class="ti ti-trending-down" style="color:#dc2626"></i> Oportunidades de mejora — Promedios más bajos</h3>
            <span style="font-size:.72rem;color:var(--an-muted)">Colaboradores con mayor necesidad de acompañamiento</span>
        </div>
        <div class="an-top-grid">
        <?php foreach ($bottomColaboradores as $i => $bc):
            $prom    = (float)($bc['PROMEDIO'] ?? 0);
            $promCls = $prom >= 4 ? 'green' : ($prom >= 3 ? 'blue' : ($prom >= 2 ? 'warn' : 'danger'));
            $initials= implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w,0,1)), array_slice(explode(' ', $bc['NOMBRE'] ?? 'N'), 0, 2)));
        ?>
        <div class="an-top-card" onclick="anOpenRankPerfil(<?= (int)$bc['IDEMPLEADO'] ?>)"
             style="border-top:3px solid <?= $prom < 2 ? '#dc2626' : ($prom < 3 ? '#f59e0b' : '#3b82f6') ?>">
            <div class="an-top-rank" style="background:#fee2e2;color:#dc2626"><?= $i + 1 ?></div>
            <div class="an-top-avatar" style="background:linear-gradient(135deg,#fee2e2,#fecaca);color:#dc2626">
                <span style="font-weight:800;font-size:.95rem"><?= htmlspecialchars($initials, ENT_QUOTES) ?></span>
            </div>
            <div class="an-top-name"><?= htmlspecialchars($bc['NOMBRE'] ?? '', ENT_QUOTES) ?></div>
            <div class="an-top-cargo" title="<?= htmlspecialchars($bc['CARGO'] ?? '', ENT_QUOTES) ?>"><?= htmlspecialchars(mb_strimwidth($bc['CARGO'] ?? '', 0, 28, '…'), ENT_QUOTES) ?></div>
            <div class="an-top-score" style="color:#dc2626"><?= number_format($prom, 2) ?><span> / 5</span></div>
            <div class="an-top-stats">
                <span><i class="ti ti-clipboard-list" style="font-size:.72rem"></i> <b><?= (int)($bc['TOTAL_AC'] ?? 0) ?></b> ac.</span>
                <?php if ((int)($bc['PEND_AC'] ?? 0) > 0): ?>
                <span style="color:#92400e"><b><?= (int)$bc['PEND_AC'] ?></b> pend.</span>
                <?php endif; ?>
                <?php if ((int)($bc['APRO_AC'] ?? 0) > 0): ?>
                <span style="color:#15803d"><b><?= (int)$bc['APRO_AC'] ?></b> apro.</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal perfil colaborador (ranking) -->
    <div id="rkModalOverlay" onclick="if(event.target===this)rkClosePerfil()">
        <div id="rkModal">
            <button class="rk-modal-close" onclick="rkClosePerfil()" title="Cerrar">×</button>
            <div id="rkLoadingMsg"><i class="ti ti-loader" style="font-size:2rem;animation:anSpin 1s linear infinite"></i><br>Cargando perfil…</div>
            <div id="rkModalContent">
                <div class="rk-modal-head">
                    <div class="rk-modal-avatar"><i class="ti ti-user"></i></div>
                    <div class="rk-modal-info">
                        <h4 id="rkNombre">—</h4>
                        <p id="rkCargo">—</p>
                        <p id="rkProceso" style="margin-top:2px"></p>
                    </div>
                </div>
                <div class="rk-section-title"><i class="ti ti-chart-bar"></i> Promedios por tipo de evaluación</div>
                <div class="rk-kpi-row" id="rkPromedios"></div>
                <div class="rk-section-title"><i class="ti ti-clipboard-list"></i> Acuerdos de mejora</div>
                <div class="rk-kpi-row" id="rkAcuerdos"></div>
                <div class="rk-section-title"><i class="ti ti-chart-radar"></i> Competencias evaluadas</div>
                <div style="max-height:240px;overflow-y:auto;border:1px solid var(--an-border);border-radius:10px;">
                    <canvas id="rkChartComp" height="200"></canvas>
                </div>
                <div style="text-align:right;margin-top:12px">
                    <a id="rkLinkPerfil" href="#" class="an-export-btn" style="display:inline-flex;text-decoration:none">
                        <i class="ti ti-external-link"></i> Ver perfil completo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="an-card">
        <form method="GET" action="">
            <input type="hidden" name="views" value="analitica">
            <input type="hidden" name="tab" value="ranking">
            <?php if ($idPeriodoFiltro): ?><input type="hidden" name="idperiodo" value="<?= $idPeriodoFiltro ?>"> <?php endif; ?>
            <div class="an-filter-bar">
                <span class="an-filter-label">Filtros</span>
                <select name="tipoeval" class="an-filter-select">
                    <option value="LIDER_A_COLAB" <?= $tipoEval==='LIDER_A_COLAB'?'selected':''?>>Lider → Colaborador</option>
                    <option value="AUTO"          <?= $tipoEval==='AUTO'         ?'selected':''?>>Autoevaluación</option>
                    <option value="COLAB_A_LIDER" <?= $tipoEval==='COLAB_A_LIDER'?'selected':''?>>Colaborador → Líder</option>
                </select>
                <input type="text" name="proceso" class="an-filter-input"
                       placeholder="Filtrar por proceso..."
                       value="<?= htmlspecialchars($proceso, ENT_QUOTES) ?>">
                <button type="submit" class="an-export-btn" style="background:var(--an-dark)">
                    <i class="ti ti-filter"></i> Aplicar
                </button>
                <button type="button" class="an-export-btn" onclick="anExportar('ranking_comp',this)" style="margin-left:auto">
                    <div class="an-spinner"></div>
                    <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar</span>
                </button>
            </div>
        </form>

        <?php if (!empty($ranking)): ?>
        <div class="an-card-body" style="padding-top:0;">
            <!-- Horizontal bar chart -->
            <div class="an-chart-wrap-tall" style="margin-bottom:20px;">
                <canvas id="chartRanking"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <div class="an-table-wrap">
            <table class="an-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Competencia</th>
                        <th>Dimensión</th>
                        <th class="num">Promedio</th>
                        <th class="num">Mín</th>
                        <th class="num">Máx</th>
                        <th class="num">Evaluados</th>
                        <th class="num">% Bajo</th>
                        <th class="num">% Exc.</th>
                        <th>Nivel</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($ranking)): ?>
                <tr><td colspan="10"><div class="an-empty"><i class="ti ti-database-off"></i><p>Sin datos para los filtros seleccionados</p></div></td></tr>
                <?php else: foreach ($ranking as $rk): $prom = (float)($rk['PROMEDIO'] ?? 0); $bar = $prom > 0 ? round($prom/5*100) : 0; $cls = $prom>=4?'green':($prom>=3?'blue':($prom>=2?'warn':'danger')); ?>
                <tr>
                    <td><?= $rk['NUM_PREGUNTA'] ?? '' ?></td>
                    <td class="bold"><?= htmlspecialchars($rk['NOMBRE_COMP'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($rk['DIMENSION'] ?? '', ENT_QUOTES) ?></td>
                    <td class="num" style="font-size:.95rem;color:var(--an-primary)"><?= number_format($prom,2) ?></td>
                    <td class="num"><?= $rk['MINIMO'] ?? '—' ?></td>
                    <td class="num"><?= $rk['MAXIMO'] ?? '—' ?></td>
                    <td class="num"><?= (int)($rk['TOTAL_EVALUADOS'] ?? 0) ?></td>
                    <td class="num" style="color:var(--an-danger)"><?= number_format((float)($rk['PCT_BAJO'] ?? 0),1) ?>%</td>
                    <td class="num" style="color:var(--an-success)"><?= number_format((float)($rk['PCT_EXCELENTE'] ?? 0),1) ?>%</td>
                    <td style="min-width:90px"><div class="an-progress"><div class="an-progress-bar <?= $cls ?>" style="width:<?= $bar ?>%"></div></div></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($ranking)):
        $rLabels = json_encode(array_map(fn($r) => 'P' . ($r['NUM_PREGUNTA'] ?? '') . ' ' . mb_strimwidth($r['NOMBRE_COMP'] ?? '', 0, 30, '…'), $ranking));
        $rData   = json_encode(array_map(fn($r) => (float)($r['PROMEDIO'] ?? 0), $ranking));
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('chartRanking'), {
            type: 'bar',
            data: {
                labels: <?= $rLabels ?>,
                datasets: [{ label: 'Promedio', data: <?= $rData ?>,
                    backgroundColor: function(ctx) {
                        var v = ctx.raw;
                        return v >= 4 ? '#16a34a' : (v >= 3 ? '#0058af' : (v >= 2 ? '#d97706' : '#dc2626'));
                    },
                    borderRadius: 4, barThickness: 18 }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                scales: {
                    x: { min:0, max:5, ticks:{stepSize:1}, grid:{color:'#f1f5f9'} },
                    y: { grid:{display:false}, ticks:{font:{size:10}} }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(c){ return ' ' + c.raw.toFixed(2) + ' / 5'; } } }
                }
            }
        });
    });
    </script>
    <?php endif; ?>

    <script>
    var _rkChartInst = null;
    function anOpenRankPerfil(id) {
        var overlay = document.getElementById('rkModalOverlay');
        var loading = document.getElementById('rkLoadingMsg');
        var content = document.getElementById('rkModalContent');
        overlay.classList.add('open');
        loading.style.display = 'block';
        content.style.display = 'none';
        if (_rkChartInst) { _rkChartInst.destroy(); _rkChartInst = null; }
        var url = '<?= APP_URL ?>analitica/?action=getPerfilColaborador&idempleado=' + id
                + (<?= $idPeriodoFiltro ?: 0 ?> ? '&idperiodo=<?= $idPeriodoFiltro ?>' : '');
        fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} })
            .then(function(r){ return r.json(); })
            .then(function(data) {
                var emp  = data.empleado  || {};
                var prom = data.promedios || {};
                var ac   = data.acuerdos  || {};
                var comp = data.competencias || [];
                document.getElementById('rkNombre').textContent  = emp.NOMBRE  || '—';
                document.getElementById('rkCargo').textContent   = emp.CARGO   || '—';
                document.getElementById('rkProceso').textContent = emp.PROCESO  || '';
                // Promedios — prom[k] = {PROMEDIO: N, EVALUADORES: N}
                var promHtml = '';
                var promMap = {LIDER_A_COLAB:'Desempeño',AUTO:'Autoevaluación',COLAB_A_LIDER:'Liderazgo'};
                Object.keys(promMap).forEach(function(k){
                    var obj = prom[k] || {};
                    var v   = parseFloat(obj.PROMEDIO || 0);
                    var ev  = parseInt(obj.EVALUADORES || 0);
                    var cls = v>=4?'#16a34a':(v>=3?'#0058af':(v>=2?'#d97706':'#dc2626'));
                    promHtml += '<div class="rk-kpi"><div class="rk-kpi-val" style="color:'+cls+'">'+(v>0?v.toFixed(2):'—')+'</div>'
                              + '<div class="rk-kpi-lbl">'+promMap[k]+(ev>0?' ('+ev+')':'')+'</div></div>';
                });
                document.getElementById('rkPromedios').innerHTML = promHtml;
                // Acuerdos
                var acMap = [['TOTAL','Total'],['PENDIENTES','Pendientes'],['RESPONDIDOS','Respondidos'],['APROBADOS','Aprobados']];
                var acHtml = '';
                acMap.forEach(function(m){
                    acHtml += '<div class="rk-kpi"><div class="rk-kpi-val">'+(ac[m[0]]||0)+'</div>'
                            + '<div class="rk-kpi-lbl">'+m[1]+'</div></div>';
                });
                document.getElementById('rkAcuerdos').innerHTML = acHtml;
                // Chart
                // Filtrar solo LIDER_A_COLAB para el chart
                var compChart = comp.filter(function(c){ return c.TIPO_EVAL === 'LIDER_A_COLAB'; });
                if (compChart.length === 0) compChart = comp; // fallback: mostrar todos
                if (compChart.length > 0) {
                    var ctx = document.getElementById('rkChartComp').getContext('2d');
                    _rkChartInst = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: compChart.map(function(c){ return 'P'+c.NUM_PREGUNTA+' '+(c.NOMBRE_COMP||c.NOMBRE||'').substring(0,28); }),
                            datasets: [{ label:'Promedio', data: compChart.map(function(c){ return parseFloat(c.PROMEDIO||0); }),
                                backgroundColor: compChart.map(function(c){
                                    var v = parseFloat(c.PROMEDIO||0);
                                    return v>=4?'#16a34a':(v>=3?'#0058af':(v>=2?'#d97706':'#dc2626'));
                                }), borderRadius:4, barThickness:14 }]
                        },
                        options: {
                            indexAxis:'y', responsive:true, maintainAspectRatio:false,
                            scales: { x:{min:0,max:5,ticks:{stepSize:1}}, y:{ticks:{font:{size:9}}} },
                            plugins: { legend:{display:false},
                                tooltip:{callbacks:{label:function(c){return ' '+c.raw.toFixed(2)+' / 5';}}} }
                        }
                    });
                }
                // Enlace
                document.getElementById('rkLinkPerfil').href = '<?= APP_URL ?>analitica/?tab=individual&idempleado='+emp.IDEMPLEADO
                    + (<?= $idPeriodoFiltro ?: 0 ?> ? '&idperiodo=<?= $idPeriodoFiltro ?>' : '');
                loading.style.display = 'none';
                content.style.display = 'block';
            })
            .catch(function(){ loading.innerHTML = '<p style="color:#dc2626">Error al cargar el perfil.</p>'; });
    }
    function rkClosePerfil() {
        document.getElementById('rkModalOverlay').classList.remove('open');
        if (_rkChartInst) { _rkChartInst.destroy(); _rkChartInst = null; }
    }
    document.addEventListener('keydown', function(e){ if(e.key==='Escape') rkClosePerfil(); });
    </script>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: HISTÓRICO -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'historico'): ?>

    <div class="an-card">
        <div class="an-card-head">
            <h3 class="an-card-title"><i class="ti ti-timeline"></i> Evolución multi-período (últimos 12)</h3>
        </div>
        <div class="an-card-body">
            <div class="an-chart-wrap-tall">
                <canvas id="chartHistorico"></canvas>
            </div>
        </div>
    </div>

    <?php if (!empty($evolucion)): ?>
    <div class="an-card" style="margin-top:16px;margin-bottom:0">
        <div class="an-card-head">
            <h3 class="an-card-title"><i class="ti ti-table"></i> Detalle por período</h3>
        </div>
        <div class="an-table-wrap">
            <table class="an-table">
                <thead><tr><th>Período</th><th class="num">Desempeño</th><th class="num">Liderazgo</th><th class="num">Autoeval</th><th class="num">Participantes</th></tr></thead>
                <tbody>
                <?php foreach ($evolucion as $ev): ?>
                <tr>
                    <td class="bold"><?= htmlspecialchars($ev['PERIODO_LABEL'] ?? '', ENT_QUOTES) ?></td>
                    <td class="num"><?= $ev['PROM_DESEMPENO'] > 0 ? number_format((float)$ev['PROM_DESEMPENO'],2) : '—' ?></td>
                    <td class="num"><?= $ev['PROM_LIDERAZGO'] > 0 ? number_format((float)$ev['PROM_LIDERAZGO'],2) : '—' ?></td>
                    <td class="num"><?= $ev['PROM_AUTOEVAL']  > 0 ? number_format((float)$ev['PROM_AUTOEVAL'],2)  : '—' ?></td>
                    <td class="num"><?= (int)($ev['TOTAL_PARTICIPANTES'] ?? 0) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="an-card" style="margin-top:16px"><div class="an-empty"><i class="ti ti-database-off"></i><p>Sin datos históricos disponibles</p></div></div>
    <?php endif; ?>

    <?php
    $hLabels = json_encode(array_column($evolucion, 'FECHA_CORTA'));
    $hDesem  = json_encode(array_map(fn($r) => $r['PROM_DESEMPENO'] > 0 ? (float)$r['PROM_DESEMPENO'] : null, $evolucion));
    $hLider  = json_encode(array_map(fn($r) => $r['PROM_LIDERAZGO'] > 0 ? (float)$r['PROM_LIDERAZGO'] : null, $evolucion));
    $hAuto   = json_encode(array_map(fn($r) => $r['PROM_AUTOEVAL']  > 0 ? (float)$r['PROM_AUTOEVAL']  : null, $evolucion));
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('chartHistorico'), {
            type: 'line',
            data: {
                labels: <?= $hLabels ?>,
                datasets: [
                    { label: 'Desempeño (lider→colab)', data: <?= $hDesem ?>, borderColor:'#0058af', backgroundColor:'rgba(0,88,175,.08)', tension:.35, fill:true, pointRadius:5 },
                    { label: 'Liderazgo (colab→lider)', data: <?= $hLider ?>, borderColor:'#7c3aed', backgroundColor:'transparent',         tension:.35, fill:false,pointRadius:5 },
                    { label: 'Autoevaluación',           data: <?= $hAuto ?>,  borderColor:'#16a34a', backgroundColor:'transparent',         tension:.35, fill:false,pointRadius:5, borderDash:[6,4] },
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                scales: {
                    y: { min:0, max:5, ticks:{stepSize:.5}, grid:{color:'#f1f5f9'} },
                    x: { grid:{display:false} }
                },
                plugins: { legend:{ position:'bottom', labels:{ boxWidth:12, font:{size:11} } } }
            }
        });
    });
    </script>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: ACUERDOS -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'acuerdos'): ?>

    <?php
    $perPage    = 40;
    $totalPages = $totalAcuerdos > 0 ? (int)ceil($totalAcuerdos / $perPage) : 1;
    $baseUrl    = APP_URL . 'analitica/?tab=acuerdos' . ($idPeriodoFiltro ? '&idperiodo=' . $idPeriodoFiltro : '') . ($estadoAc ? '&estado=' . $estadoAc : '');
    ?>

    <div class="an-card">
        <form method="GET" action="">
            <input type="hidden" name="views" value="analitica">
            <input type="hidden" name="tab" value="acuerdos">
            <?php if ($idPeriodoFiltro): ?><input type="hidden" name="idperiodo" value="<?= $idPeriodoFiltro ?>"> <?php endif; ?>
            <div class="an-filter-bar">
                <span class="an-filter-label">Estado</span>
                <select name="estado" class="an-filter-select">
                    <option value="">Todos</option>
                    <option value="PENDIENTE"  <?= $estadoAc==='PENDIENTE' ?'selected':''?>>Pendiente</option>
                    <option value="RESPONDIDO" <?= $estadoAc==='RESPONDIDO'?'selected':''?>>Respondido</option>
                    <option value="APROBADO"   <?= $estadoAc==='APROBADO'  ?'selected':''?>>Aprobado</option>
                </select>
                <button type="submit" class="an-export-btn" style="background:var(--an-dark)">
                    <i class="ti ti-filter"></i> Filtrar
                </button>
                <button type="button" class="an-export-btn" onclick="anExportar('acuerdos_detalle',this)" style="margin-left:auto">
                    <div class="an-spinner"></div>
                    <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar todo</span>
                </button>
                <span class="an-page-info"><?= number_format($totalAcuerdos) ?> acuerdos</span>
            </div>
        </form>

        <div class="an-table-wrap">
            <table class="an-table">
                <thead>
                    <tr>
                        <th>Colaborador</th><th>Cargo</th><th>Proceso</th><th>Líder</th>
                        <th>Objetivo</th><th>Estado</th>
                        <th class="num">F. Asignación</th><th class="num">F. Respuesta</th>
                        <th class="num">Días</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($acuerdos)): ?>
                <tr><td colspan="9"><div class="an-empty"><i class="ti ti-clipboard-off"></i><p>Sin acuerdos para los filtros seleccionados</p></div></td></tr>
                <?php else: foreach ($acuerdos as $ac):
                    $estCls  = strtolower($ac['ESTADO'] ?? '');
                    $estLbl  = match($ac['ESTADO'] ?? '') { 'PENDIENTE'=>'Pendiente','RESPONDIDO'=>'Respondido','APROBADO'=>'Aprobado', default=>'—' };
                    $dias    = (int)($ac['DIAS_RESP'] ?? 0);
                    $diasCls = $dias > 15 && $ac['ESTADO'] === 'PENDIENTE' ? 'style="color:var(--an-danger);font-weight:700"' : '';
                    $acId    = (int)($ac['IDACUERDO'] ?? 0);
                ?>
                <tr class="ac-row-main" data-acid="<?= $acId ?>" onclick="acToggle(<?= $acId ?>)">
                    <td class="bold"><?= htmlspecialchars($ac['COLABORADOR'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($ac['CARGO'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($ac['PROCESO'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($ac['LIDER'] ?? '', ENT_QUOTES) ?></td>
                    <td style="max-width:200px;white-space:normal;"><?= htmlspecialchars(mb_strimwidth($ac['OBJETIVO'] ?? '', 0, 80, '…'), ENT_QUOTES) ?></td>
                    <td><span class="an-badge <?= $estCls ?>"><?= $estLbl ?></span></td>
                    <td class="num"><?= $ac['FECHA_ASIG'] ?? '—' ?></td>
                    <td class="num"><?= $ac['FECHA_RESP'] ?? '—' ?></td>
                    <td class="num" <?= $diasCls ?>><?= $dias ?>d</td>
                </tr>
                <tr class="ac-detail-tr" id="ac-detail-<?= $acId ?>">
                    <td colspan="9" style="padding:0">
                        <div class="ac-detail-panel">
                            <div class="ac-detail-title"><i class="ti ti-list-details"></i> Detalle del acuerdo #<?= $acId ?></div>
                            <div class="ac-detail-grid">
                                <?php if (!empty($ac['NOMBRE_COMP'])): ?>
                                <div class="ac-det-field">
                                    <div class="ac-det-lbl">Competencia</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['NOMBRE_COMP'], ENT_QUOTES) ?> (P<?= (int)($ac['NUM_COMPETENCIA'] ?? 0) ?>)</div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['CALIFICACION'])): ?>
                                <div class="ac-det-field">
                                    <div class="ac-det-lbl">Calificación</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['CALIFICACION'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['FECHA_APRO'])): ?>
                                <div class="ac-det-field">
                                    <div class="ac-det-lbl">F. Aprobación</div>
                                    <div class="ac-det-val"><?= $ac['FECHA_APRO'] ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['MODELO'])): ?>
                                <div class="ac-det-field">
                                    <div class="ac-det-lbl">Modelo</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['MODELO'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['INDICADOR'])): ?>
                                <div class="ac-det-field">
                                    <div class="ac-det-lbl">Indicador</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['INDICADOR'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['META'])): ?>
                                <div class="ac-det-field">
                                    <div class="ac-det-lbl">Meta</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['META'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['PLAZO'])): ?>
                                <div class="ac-det-field">
                                    <div class="ac-det-lbl">Plazo</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['PLAZO'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['OBJETIVO'])): ?>
                                <div class="ac-det-field ac-det-full">
                                    <div class="ac-det-lbl">Objetivo</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['OBJETIVO'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['EVIDENCIA'])): ?>
                                <div class="ac-det-field ac-det-full">
                                    <div class="ac-det-lbl">Evidencia</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['EVIDENCIA'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['SEGUIMIENTO'])): ?>
                                <div class="ac-det-field ac-det-full">
                                    <div class="ac-det-lbl">Seguimiento</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['SEGUIMIENTO'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['USO_RECOMENDADO'])): ?>
                                <div class="ac-det-field ac-det-full">
                                    <div class="ac-det-lbl">Uso Recomendado</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['USO_RECOMENDADO'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ac['PLAN_ACCION'])): ?>
                                <div class="ac-det-field ac-det-full" style="border-color:var(--an-primary)">
                                    <div class="ac-det-lbl" style="color:var(--an-primary)">Plan de Acción</div>
                                    <div class="ac-det-val"><?= htmlspecialchars($ac['PLAN_ACCION'], ENT_QUOTES) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($ac['IDEMPLEADO'])): ?>
                            <div style="margin-top:10px;text-align:right">
                                <button class="an-export-btn" style="font-size:.72rem"
                                        onclick="anOpenPerfil(<?= (int)$ac['IDEMPLEADO'] ?>)">
                                    <i class="ti ti-user-search"></i> Ver perfil de <?= htmlspecialchars(explode(' ', $ac['COLABORADOR'] ?? 'colaborador')[0], ENT_QUOTES) ?>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="an-pagination">
            <?php if ($pageAc > 1): ?><a href="<?= $baseUrl ?>&page=<?= $pageAc-1 ?>" class="an-page-btn">‹ Anterior</a><?php endif; ?>
            <span class="an-page-info">Pág. <?= $pageAc ?> / <?= $totalPages ?></span>
            <?php if ($pageAc < $totalPages): ?><a href="<?= $baseUrl ?>&page=<?= $pageAc+1 ?>" class="an-page-btn">Siguiente ›</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function acToggle(id) {
        var mainRow   = document.querySelector('.ac-row-main[data-acid="'+id+'"]');
        var detailRow = document.getElementById('ac-detail-' + id);
        if (!detailRow) return;
        var isOpen = detailRow.classList.contains('open');
        // Cerrar todos los abiertos
        document.querySelectorAll('.ac-detail-tr.open').forEach(function(r){ r.classList.remove('open'); });
        document.querySelectorAll('.ac-row-main.expanded').forEach(function(r){ r.classList.remove('expanded'); });
        if (!isOpen) {
            detailRow.classList.add('open');
            if (mainRow) mainRow.classList.add('expanded');
            detailRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
    </script>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: PROCESOS -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'procesos'): ?>

    <div class="an-card">
        <div class="an-card-head">
            <h3 class="an-card-title"><i class="ti ti-building"></i> Resumen por proceso / área</h3>
            <button class="an-export-btn" onclick="anExportar('resumen_procesos',this)">
                <div class="an-spinner"></div>
                <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar</span>
            </button>
        </div>
        <?php if (!empty($resumenProcesos)): ?>
        <div class="an-card-body" style="padding-bottom:0">
            <div class="an-chart-wrap-tall">
                <canvas id="chartProcesos"></canvas>
            </div>
        </div>
        <?php endif; ?>
        <div class="an-table-wrap">
            <table class="an-table">
                <thead>
                    <tr><th>Proceso</th><th class="num">Total</th><th class="num">Autoeval</th><th class="num">Evaluados</th><th>% Autoeval</th><th>% Evaluados</th><th class="num">Ac. Pend.</th><th class="num">Ac. Apro.</th></tr>
                </thead>
                <tbody>
                <?php if (empty($resumenProcesos)): ?>
                <tr><td colspan="8"><div class="an-empty"><i class="ti ti-database-off"></i><p>Sin datos</p></div></td></tr>
                <?php else: foreach ($resumenProcesos as $rp): $pctEv=(float)($rp['PCT_EVALUADOS']??0); $cls2=$pctEv>=80?'green':($pctEv>=50?'warn':'danger'); ?>
                <tr>
                    <td class="bold"><?= htmlspecialchars($rp['PROCESO']??'',ENT_QUOTES) ?></td>
                    <td class="num"><?= (int)($rp['TOTAL']??0) ?></td>
                    <td class="num"><?= (int)($rp['CON_AUTOEVAL']??0) ?></td>
                    <td class="num"><?= (int)($rp['EVALUADOS']??0) ?></td>
                    <td><div style="display:flex;align-items:center;gap:8px;"><div class="an-progress" style="flex:1"><div class="an-progress-bar blue" style="width:<?= min(100,(float)($rp['PCT_AUTOEVAL']??0)) ?>%"></div></div><span style="font-size:.75rem;font-weight:700"><?= (float)($rp['PCT_AUTOEVAL']??0) ?>%</span></div></td>
                    <td><div style="display:flex;align-items:center;gap:8px;"><div class="an-progress" style="flex:1"><div class="an-progress-bar <?= $cls2 ?>" style="width:<?= min(100,$pctEv) ?>%"></div></div><span style="font-size:.75rem;font-weight:700"><?= $pctEv ?>%</span></div></td>
                    <td class="num"><?= (int)($rp['ACUERDOS_PEND']??0) ?></td>
                    <td class="num"><?= (int)($rp['ACUERDOS_APRO']??0) ?></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($resumenProcesos)):
        $pLabels = json_encode(array_map(fn($r) => mb_strimwidth($r['PROCESO']??'',0,25,'…'), $resumenProcesos));
        $pAutoD  = json_encode(array_map(fn($r) => (float)($r['PCT_AUTOEVAL']??0), $resumenProcesos));
        $pEvalD  = json_encode(array_map(fn($r) => (float)($r['PCT_EVALUADOS']??0), $resumenProcesos));
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('chartProcesos'), {
            type: 'bar',
            data: {
                labels: <?= $pLabels ?>,
                datasets: [
                    { label: '% Autoeval', data: <?= $pAutoD ?>, backgroundColor: 'rgba(0,88,175,.7)', borderRadius:3, barThickness:12 },
                    { label: '% Evaluados', data: <?= $pEvalD ?>, backgroundColor: 'rgba(22,163,74,.7)', borderRadius:3, barThickness:12 }
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                scales: {
                    y: { min:0, max:100, ticks:{callback:function(v){return v+'%'}}, grid:{color:'#f1f5f9'} },
                    x: { grid:{display:false}, ticks:{font:{size:10}} }
                },
                plugins: { legend:{ position:'bottom', labels:{ boxWidth:12, font:{size:11} } } }
            }
        });
    });
    </script>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: ALERTAS -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'alertas'): ?>

    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;flex-wrap:wrap;gap:10px">
        <div class="an-flt-btns">
            <button class="an-flt-btn active" onclick="filtrarAlerta('sin_autoeval',this)">
                <i class="ti ti-clipboard-x"></i> Sin autoevaluación
                <span class="an-flt-count warn"><?= count($alertas['sin_autoeval']) ?></span>
            </button>
            <button class="an-flt-btn" onclick="filtrarAlerta('acuerdos_vencidos',this)">
                <i class="ti ti-clock-exclamation"></i> Acuerdos vencidos
                <span class="an-flt-count red"><?= count($alertas['acuerdos_vencidos']) ?></span>
            </button>
            <button class="an-flt-btn" onclick="filtrarAlerta('lideres_sin_evaluar',this)">
                <i class="ti ti-user-exclamation"></i> Líderes pendientes
                <span class="an-flt-count blue"><?= count($alertas['lideres_sin_evaluar']) ?></span>
            </button>
        </div>
        <button class="an-export-btn" onclick="anExportar('alertas_admin',this)">
            <div class="an-spinner"></div>
            <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar alertas</span>
        </button>
    </div>

    <!-- Buscador de alertas -->
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <div style="position:relative;flex:1;max-width:380px;">
            <i class="ti ti-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);
               color:var(--an-muted);font-size:15px;pointer-events:none;"></i>
            <input id="anAlertSearch" type="text" placeholder="Buscar por nombre, cargo o proceso…"
                   oninput="anAlertBuscar(this.value)"
                   style="width:100%;padding:9px 14px 9px 34px;border:1.5px solid var(--an-border);
                          border-radius:10px;font-size:.86rem;font-family:var(--an-font);
                          outline:none;transition:border-color .2s;background:#fff;">
        </div>
        <span id="anAlertCounter" style="font-size:.78rem;color:var(--an-muted);white-space:nowrap;"></span>
    </div>

    <!-- Sin autoevaluación -->
    <div class="an-alert-section" id="sec-sin_autoeval">
        <div class="an-alert-header warn">
            <i class="ti ti-clipboard-x"></i>
            <span>Sin autoevaluación</span>
            <span class="an-alert-count"><?= count($alertas['sin_autoeval']) ?> colaboradores</span>
        </div>
        <div class="an-card" style="border-radius:0 0 14px 14px;">
        <?php if (empty($alertas['sin_autoeval'])): ?>
        <div class="an-empty" style="padding:28px"><i class="ti ti-circle-check" style="color:var(--an-success)"></i><p>Sin pendientes</p></div>
        <?php else: ?>
        <div class="an-table-wrap"><table class="an-table">
            <thead><tr><th>Colaborador</th><th>Cargo</th><th>Proceso</th><th>Líder</th><th>Última notif.</th></tr></thead>
            <tbody id="atb-sin_autoeval">
            <?php foreach ($alertas['sin_autoeval'] as $a): ?>
            <tr data-search="<?= strtolower(htmlspecialchars(($a['COLABORADOR']??'').' '.($a['CARGO']??'').' '.($a['PROCESO']??'').' '.($a['LIDER']??''), ENT_QUOTES)) ?>">
                <td class="bold"><?= htmlspecialchars($a['COLABORADOR']??'',ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($a['CARGO']??'',ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($a['PROCESO']??'',ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($a['LIDER']??'—',ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($a['ULTIMA_NOTIF']??'—',ENT_QUOTES) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Acuerdos vencidos -->
    <div class="an-alert-section" id="sec-acuerdos_vencidos" style="display:none">
        <div class="an-alert-header red">
            <i class="ti ti-clock-exclamation"></i>
            <span>Acuerdos vencidos (&gt;15 días sin respuesta)</span>
            <span class="an-alert-count"><?= count($alertas['acuerdos_vencidos']) ?></span>
        </div>
        <div class="an-card" style="border-radius:0 0 14px 14px;">
        <?php if (empty($alertas['acuerdos_vencidos'])): ?>
        <div class="an-empty" style="padding:28px"><i class="ti ti-circle-check" style="color:var(--an-success)"></i><p>Sin acuerdos vencidos</p></div>
        <?php else: ?>
        <div class="an-table-wrap"><table class="an-table">
            <thead><tr><th>Colaborador</th><th>Cargo</th><th>Proceso</th><th>Líder</th><th class="num">Días pendiente</th></tr></thead>
            <tbody id="atb-acuerdos_vencidos">
            <?php foreach ($alertas['acuerdos_vencidos'] as $a): $d=(int)($a['DIAS_PENDIENTE']??0); ?>
            <tr data-search="<?= strtolower(htmlspecialchars(($a['COLABORADOR']??'').' '.($a['CARGO']??'').' '.($a['PROCESO']??'').' '.($a['LIDER']??''), ENT_QUOTES)) ?>">
                <td class="bold"><?= htmlspecialchars($a['COLABORADOR']??'',ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($a['CARGO']??'',ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($a['PROCESO']??'',ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($a['LIDER']??'—',ENT_QUOTES) ?></td>
                <td class="num" style="color:var(--an-danger);font-weight:700"><?= $d ?>d</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Líderes sin evaluar -->
    <div class="an-alert-section" id="sec-lideres_sin_evaluar" style="display:none">
        <div class="an-alert-header blue">
            <i class="ti ti-user-exclamation"></i>
            <span>Líderes con evaluaciones pendientes</span>
            <span class="an-alert-count"><?= count($alertas['lideres_sin_evaluar']) ?></span>
        </div>
        <div class="an-card" style="border-radius:0 0 14px 14px;">
        <?php if (empty($alertas['lideres_sin_evaluar'])): ?>
        <div class="an-empty" style="padding:28px"><i class="ti ti-circle-check" style="color:var(--an-success)"></i><p>Todos los líderes han evaluado a su equipo</p></div>
        <?php else: ?>
        <div class="an-table-wrap"><table class="an-table">
            <thead><tr><th>Líder</th><th>Cargo</th><th>Proceso</th><th class="num">Pendientes de evaluar</th></tr></thead>
            <tbody id="atb-lideres_sin_evaluar">
            <?php foreach ($alertas['lideres_sin_evaluar'] as $a): ?>
            <tr data-search="<?= strtolower(htmlspecialchars(($a['LIDER']??'').' '.($a['CARGO']??'').' '.($a['PROCESO']??''), ENT_QUOTES)) ?>">
                <td class="bold"><?= htmlspecialchars($a['LIDER']??'',ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($a['CARGO']??'',ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($a['PROCESO']??'',ENT_QUOTES) ?></td>
                <td class="num" style="color:var(--an-primary);font-weight:700"><?= (int)($a['PENDIENTES_EVALUAR']??0) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
        </div>
    </div>
    <script>
    var _anAlertKey = 'sin_autoeval';

    function filtrarAlerta(key, btn) {
        _anAlertKey = key;
        document.querySelectorAll('.an-flt-btn').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        ['sin_autoeval','acuerdos_vencidos','lideres_sin_evaluar'].forEach(function(k){
            var sec = document.getElementById('sec-' + k);
            if (sec) sec.style.display = k === key ? '' : 'none';
        });
        // Limpiar buscador al cambiar sección
        var inp = document.getElementById('anAlertSearch');
        if (inp) { inp.value = ''; }
        anAlertBuscar('');
    }

    function anAlertBuscar(q) {
        q = q.trim().toLowerCase();
        var tbody = document.getElementById('atb-' + _anAlertKey);
        var counter = document.getElementById('anAlertCounter');
        if (!tbody) { if (counter) counter.textContent = ''; return; }

        var rows = tbody.querySelectorAll('tr');
        var visible = 0;
        rows.forEach(function(tr) {
            var haystack = tr.getAttribute('data-search') || '';
            var show = !q || haystack.indexOf(q) !== -1;
            tr.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (counter) {
            counter.textContent = q
                ? visible + ' de ' + rows.length + ' resultado' + (visible !== 1 ? 's' : '')
                : '';
        }
    }
    </script>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: COLABORADORES -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'colaboradores'):
    $perPageColab = 30;
    $totalPagesColab = $totalColaboradores > 0 ? (int)ceil($totalColaboradores / $perPageColab) : 1;
    $baseUrlColab = APP_URL . 'analitica/?tab=colaboradores'
        . ($idPeriodoFiltro ? '&idperiodo=' . $idPeriodoFiltro : '')
        . (!empty($filtrosColab['nombre'])  ? '&q='        . urlencode($filtrosColab['nombre'])  : '')
        . (!empty($filtrosColab['proceso']) ? '&proceso='  . urlencode($filtrosColab['proceso']) : '')
        . (!empty($filtrosColab['estado'])  ? '&estcolab=' . urlencode($filtrosColab['estado'])  : '');
    ?>

    <!-- Filtros -->
    <div class="an-card" style="margin-bottom:14px">
        <form method="GET" action="">
            <input type="hidden" name="views" value="analitica">
            <input type="hidden" name="tab" value="colaboradores">
            <?php if ($idPeriodoFiltro): ?><input type="hidden" name="idperiodo" value="<?= $idPeriodoFiltro ?>"> <?php endif; ?>
            <div class="an-filter-bar">
                <span class="an-filter-label">Buscar</span>
                <input type="text" name="q" class="an-filter-input"
                       placeholder="Nombre o cédula..."
                       value="<?= htmlspecialchars($filtrosColab['nombre'] ?? '', ENT_QUOTES) ?>">
                <select name="proceso" class="an-filter-select">
                    <option value="">Todos los procesos</option>
                    <?php foreach ($procesosLista as $pr):
                        $selPr = ($filtrosColab['proceso'] === $pr) ? 'selected' : ''; ?>
                    <option value="<?= htmlspecialchars($pr, ENT_QUOTES) ?>" <?= $selPr ?>><?= htmlspecialchars($pr, ENT_QUOTES) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="estcolab" class="an-filter-select">
                    <option value="">Todos los estados</option>
                    <option value="completo"    <?= ($filtrosColab['estado'] === 'completo'   ) ? 'selected' : '' ?>>Completo</option>
                    <option value="en_progreso" <?= ($filtrosColab['estado'] === 'en_progreso') ? 'selected' : '' ?>>En progreso</option>
                    <option value="sin_iniciar" <?= ($filtrosColab['estado'] === 'sin_iniciar') ? 'selected' : '' ?>>Sin iniciar</option>
                </select>
                <button type="submit" class="an-export-btn" style="background:var(--an-dark)">
                    <i class="ti ti-filter"></i> Filtrar
                </button>
                <button type="button" class="an-export-btn" onclick="anExportar('analitica_colaboradores',this)" style="margin-left:auto">
                    <div class="an-spinner"></div>
                    <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar lista</span>
                </button>
                <button type="button" class="an-export-btn" onclick="anExportar('analitica_justificaciones',this)" style="background:var(--an-dark)">
                    <div class="an-spinner"></div>
                    <span class="an-btn-text"><i class="ti ti-message-dots"></i> Exportar justificaciones</span>
                </button>
                <span class="an-page-info"><?= number_format($totalColaboradores) ?> colaboradores</span>
            </div>
        </form>
    </div>

    <!-- Tabla de colaboradores -->
    <div class="an-card">
        <div class="an-table-wrap">
            <table class="an-table">
                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Cargo</th>
                        <th>Proceso</th>
                        <th>Líder</th>
                        <th>Estado</th>
                        <th class="num">Prom. Líder</th>
                        <th class="num">Prom. Auto</th>
                        <th class="num">Acuerdos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($colaboradores)): ?>
                <tr><td colspan="9"><div class="an-empty"><i class="ti ti-database-off"></i><p>Sin resultados para los filtros aplicados</p></div></td></tr>
                <?php else: foreach ($colaboradores as $col):
                    $estCls = $col['ESTADO'] ?? 'sin_iniciar';
                    $estLbl = match($estCls) { 'completo'=>'Completo','en_progreso'=>'En progreso', default=>'Sin iniciar' };
                    $promL  = (float)($col['PROM_LIDER'] ?? 0);
                    $promA  = (float)($col['PROM_AUTO']  ?? 0);
                    $sclL   = $promL >= 4 ? 's-hi' : ($promL >= 3 ? 's-mid' : ($promL >= 2 ? 's-low' : ($promL > 0 ? 's-bad' : 's-none')));
                    $sclA   = $promA >= 4 ? 's-hi' : ($promA >= 3 ? 's-mid' : ($promA >= 2 ? 's-low' : ($promA > 0 ? 's-bad' : 's-none')));
                ?>
                <tr>
                    <td class="bold"><?= htmlspecialchars($col['NOMBRE_COMPLETO'] ?? $col['NOMBRE'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($col['CARGO'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($col['PROCESO'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($col['NOMBRE_LIDER'] ?? '—', ENT_QUOTES) ?></td>
                    <td><span class="an-badge <?= $estCls ?>"><?= $estLbl ?></span></td>
                    <td class="num"><span class="an-score <?= $sclL ?>"><?= $promL > 0 ? number_format($promL,2) : '—' ?></span></td>
                    <td class="num"><span class="an-score <?= $sclA ?>"><?= $promA > 0 ? number_format($promA,2) : '—' ?></span></td>
                    <td class="num">
                        <?php if ((int)($col['TOTAL_ACUERDOS'] ?? 0) > 0): ?>
                        <span title="Pendientes: <?= (int)($col['ACUERDOS_PEND']??0) ?> / Aprobados: <?= (int)($col['ACUERDOS_APRO']??0) ?>">
                            <?= (int)($col['TOTAL_ACUERDOS'] ?? 0) ?>
                        </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <button class="an-export-btn" style="padding:5px 10px;font-size:.72rem;box-shadow:none;"
                                onclick="anOpenPerfil(<?= (int)$col['IDEMPLEADO'] ?>)">
                            <i class="ti ti-eye"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPagesColab > 1): ?>
        <div class="an-pagination">
            <?php if ($pageColab > 1): ?><a href="<?= $baseUrlColab ?>&page=<?= $pageColab-1 ?>" class="an-page-btn">‹ Anterior</a><?php endif; ?>
            <span class="an-page-info">Pág. <?= $pageColab ?> / <?= $totalPagesColab ?></span>
            <?php if ($pageColab < $totalPagesColab): ?><a href="<?= $baseUrlColab ?>&page=<?= $pageColab+1 ?>" class="an-page-btn">Siguiente ›</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal se carga globalmente al final del bloque de tabs -->

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: BRECHAS -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'brechas'): ?>

    <div class="an-card">
        <form method="GET" action="">
            <input type="hidden" name="views" value="analitica">
            <input type="hidden" name="tab" value="brechas">
            <?php if ($idPeriodoFiltro): ?><input type="hidden" name="idperiodo" value="<?= $idPeriodoFiltro ?>"> <?php endif; ?>
            <div class="an-filter-bar">
                <span class="an-filter-label">Proceso</span>
                <select name="proceso" class="an-filter-select">
                    <option value="">Todos los procesos</option>
                    <?php foreach ($procesosLista as $pr):
                        $selPr = ($proceso === $pr) ? 'selected' : ''; ?>
                    <option value="<?= htmlspecialchars($pr, ENT_QUOTES) ?>" <?= $selPr ?>><?= htmlspecialchars($pr, ENT_QUOTES) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="an-export-btn" style="background:var(--an-dark)">
                    <i class="ti ti-filter"></i> Filtrar
                </button>
                <button type="button" class="an-export-btn" onclick="anExportar('analitica_brechas',this)" style="margin-left:auto">
                    <div class="an-spinner"></div>
                    <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar</span>
                </button>
            </div>
        </form>

        <?php if (!empty($brechas)): ?>
        <div class="an-card-body" style="padding-bottom:0">
            <p style="font-size:.78rem;color:var(--an-muted);margin:0 0 10px">
                <strong>Brecha = Promedio Líder − Promedio Autoeval.</strong>
                Positivo: el líder califica más alto que el colaborador. Negativo: el colaborador se autocalifica más alto.
            </p>
            <div class="an-chart-wrap-tall">
                <canvas id="chartBrechas"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <div class="an-table-wrap">
            <table class="an-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Competencia</th>
                        <th>Dimensión</th>
                        <th class="num">Prom. Líder</th>
                        <th class="num">Prom. Auto</th>
                        <th class="num">Brecha</th>
                        <th class="num">N Líder</th>
                        <th class="num">N Auto</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($brechas)): ?>
                <tr><td colspan="8"><div class="an-empty"><i class="ti ti-database-off"></i><p>Sin datos para el período/proceso seleccionado</p></div></td></tr>
                <?php else: foreach ($brechas as $br):
                    $brVal = (float)($br['BRECHA'] ?? 0);
                    $brCls = $brVal > 0.2 ? 'pos' : ($brVal < -0.2 ? 'neg' : 'zero');
                    $brTxt = ($brVal > 0 ? '+' : '') . number_format($brVal, 2);
                    $pL    = (float)($br['PROM_LIDER'] ?? 0);
                    $pA    = (float)($br['PROM_AUTO']  ?? 0);
                    $sL    = $pL >= 4 ? 's-hi' : ($pL >= 3 ? 's-mid' : ($pL >= 2 ? 's-low' : 's-bad'));
                    $sA    = $pA >= 4 ? 's-hi' : ($pA >= 3 ? 's-mid' : ($pA >= 2 ? 's-low' : 's-bad'));
                ?>
                <tr>
                    <td><?= $br['NUM_PREGUNTA'] ?? '' ?></td>
                    <td class="bold"><?= htmlspecialchars($br['NOMBRE_COMP'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($br['DIMENSION'] ?? '', ENT_QUOTES) ?></td>
                    <td class="num"><span class="an-score <?= $sL ?>"><?= number_format($pL,2) ?></span></td>
                    <td class="num"><span class="an-score <?= $sA ?>"><?= number_format($pA,2) ?></span></td>
                    <td class="num"><span class="an-brecha <?= $brCls ?>"><?= $brTxt ?></span></td>
                    <td class="num"><?= (int)($br['N_LIDER'] ?? 0) ?></td>
                    <td class="num"><?= (int)($br['N_AUTO']  ?? 0) ?></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($brechas)):
        $bLabels = json_encode(array_map(fn($r) => 'P' . ($r['NUM_PREGUNTA'] ?? '') . ' ' . mb_strimwidth($r['NOMBRE_COMP'] ?? '', 0, 28, '…'), $brechas));
        $bLider  = json_encode(array_map(fn($r) => (float)($r['PROM_LIDER'] ?? 0), $brechas));
        $bAuto   = json_encode(array_map(fn($r) => (float)($r['PROM_AUTO']  ?? 0), $brechas));
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('chartBrechas'), {
            type: 'bar',
            data: {
                labels: <?= $bLabels ?>,
                datasets: [
                    { label: 'Prom. Líder',    data: <?= $bLider ?>, backgroundColor: 'rgba(0,88,175,.75)',  borderRadius: 3, barThickness: 10 },
                    { label: 'Prom. Autoeval', data: <?= $bAuto  ?>, backgroundColor: 'rgba(22,163,74,.75)', borderRadius: 3, barThickness: 10 }
                ]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                scales: {
                    x: { min: 0, max: 5, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } },
                    y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function(c) { return ' ' + c.dataset.label + ': ' + c.raw.toFixed(2); } } }
                }
            }
        });
    });
    </script>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: EXPERIENCIA AZUL -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'exp_azul'):
    $eaKpi  = $expAzul['kpi']         ?? [];
    $eaComp = $expAzul['competencias'] ?? [];
    $eaPend = $expAzul['pendientes']   ?? [];
    $eaEval = $expAzul['evaluados']    ?? [];
    ?>

    <!-- KPIs Exp. Azul -->
    <div class="an-kpi-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));margin-bottom:20px">
        <div class="an-kpi blue">
            <div class="an-kpi-icon"><i class="ti ti-droplet"></i></div>
            <div class="an-kpi-value"><?= (int)($eaKpi['TOTAL_APLICAN'] ?? 0) ?></div>
            <div class="an-kpi-label">Aplican Exp. Azul</div>
           <!-- <div class="an-kpi-sub">IDROL=1 o APLICA_EXP_AZUL=1</div> -->
        </div>
        <div class="an-kpi green">
            <div class="an-kpi-icon"><i class="ti ti-arrow-up-right"></i></div>
            <div class="an-kpi-value"><?= (float)($eaKpi['PCT_COLAB'] ?? 0) ?>%</div>
            <div class="an-kpi-label">Completaron (Colab → Líder)</div>
            <div class="an-kpi-sub"><?= (int)($eaKpi['COMPLETARON_COLAB'] ?? 0) ?> de <?= (int)($eaKpi['TOTAL_APLICA_COLAB'] ?? 0) ?> aplican</div>
        </div>
        <div class="an-kpi warn">
            <div class="an-kpi-icon"><i class="ti ti-arrow-down-right"></i></div>
            <div class="an-kpi-value"><?= (float)($eaKpi['PCT_LIDER'] ?? 0) ?>%</div>
            <div class="an-kpi-label">Completaron (Líder → Colab)</div>
            <div class="an-kpi-sub"><?= (int)($eaKpi['COMPLETARON_LIDER'] ?? 0) ?> colaboradores</div>
        </div>
        <div class="an-kpi red">
            <div class="an-kpi-icon"><i class="ti ti-clock-exclamation"></i></div>
            <div class="an-kpi-value"><?= count($eaPend) ?></div>
            <div class="an-kpi-label">Pendientes</div>
            <div class="an-kpi-sub">Falta completar alguna parte</div>
        </div>
    </div>

    <div class="an-charts-row">
        <!-- Promedios por competencia -->
        <div class="an-card">
            <div class="an-card-head">
                <h3 class="an-card-title"><i class="ti ti-chart-bar"></i> Promedios Exp. Azul por competencia</h3>
                <button class="an-export-btn" onclick="anExportar('analitica_exp_azul_comp',this)">
                    <div class="an-spinner"></div>
                    <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar</span>
                </button>
            </div>
            <?php if (empty($eaComp)): ?>
            <div class="an-empty"><i class="ti ti-database-off"></i><p>Sin evaluaciones de Experiencia Azul en este período</p></div>
            <?php else: ?>
            <div class="an-table-wrap">
                <table class="an-table">
                    <thead><tr><th>#</th><th>Competencia</th><th>Tipo</th><th class="num">Promedio</th><th class="num">Evaluados</th></tr></thead>
                    <tbody>
                    <?php foreach ($eaComp as $ec):
                        $epv = (float)($ec['PROMEDIO'] ?? 0);
                        $epCls = $epv >= 4 ? 's-hi' : ($epv >= 3 ? 's-mid' : ($epv >= 2 ? 's-low' : 's-bad'));
                        $tipo  = $ec['TIPO_EVAL'] === 'EXPERIENCIA_COLAB' ? 'Líder → Colab' : 'Colab → Líder';
                    ?>
                    <tr>
                        <td><?= $ec['NUM_PREGUNTA'] ?? '' ?></td>
                        <td class="bold"><?= htmlspecialchars($ec['NOMBRE_COMP'] ?? '', ENT_QUOTES) ?></td>
                        <td><span class="an-badge ea-ok" style="font-size:.68rem"><?= $tipo ?></span></td>
                        <td class="num"><span class="an-score <?= $epCls ?>"><?= $epv > 0 ? number_format($epv,2) : '—' ?></span></td>
                        <td class="num"><?= (int)($ec['EVALUADOS'] ?? 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pendientes -->
        <div class="an-card">
            <div class="an-card-head">
                <h3 class="an-card-title"><i class="ti ti-clock-exclamation"></i> Pendientes de completar</h3>
                <div style="display:flex;align-items:center;gap:8px">
                    <?php if (!empty($eaPend)): ?>
                    <input type="text" id="eaPendSearch" class="an-filter-input"
                           style="max-width:170px;font-size:.76rem;padding:5px 10px"
                           placeholder="Buscar...">
                    <?php endif; ?>
                    <button class="an-export-btn" onclick="anExportar('analitica_exp_azul',this)">
                        <div class="an-spinner"></div>
                        <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar</span>
                    </button>
                </div>
            </div>
            <?php if (empty($eaPend)): ?>
            <div class="an-empty" style="padding:32px"><i class="ti ti-circle-check" style="color:var(--an-success)"></i><p>Todos completaron Experiencia Azul</p></div>
            <?php else: ?>
            <div class="an-table-wrap">
                <table class="an-table">
                    <thead><tr><th>Colaborador</th><th>Proceso</th><th>Líder</th><th>Colab→Líder</th><th>Líder→Colab</th></tr></thead>
                    <tbody id="eaPendBody">
                    <?php foreach ($eaPend as $ep): ?>
                    <tr class="ea-pend-row"
                        data-name="<?= htmlspecialchars(mb_strtolower($ep['COLABORADOR'] ?? ''), ENT_QUOTES) ?>"
                        data-proceso="<?= htmlspecialchars(mb_strtolower($ep['PROCESO'] ?? ''), ENT_QUOTES) ?>">
                        <td class="bold"><?= htmlspecialchars($ep['COLABORADOR'] ?? '', ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($ep['PROCESO'] ?? '', ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($ep['LIDER'] ?? '—', ENT_QUOTES) ?></td>
                        <td><?php $hc = (int)($ep['HIZO_COLAB'] ?? 0); if ($hc === 2): ?><span class="an-badge ea-na">N/A</span><?php elseif ($hc): ?><span class="an-badge completo">Listo</span><?php else: ?><span class="an-badge ea-pending">Pendiente</span><?php endif; ?></td>
                        <td><?php if ((int)($ep['HIZO_LIDER'] ?? 0)): ?><span class="an-badge completo">Listo</span><?php else: ?><span class="an-badge ea-pending">Pendiente</span><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-top:1px solid var(--an-border);font-size:.76rem;color:var(--an-muted)">
                <span id="eaPendInfo"></span>
                <div style="display:flex;gap:6px">
                    <button id="eaPrevBtn" class="an-page-btn" style="font-size:.72rem;padding:4px 10px">‹</button>
                    <button id="eaNextBtn" class="an-page-btn" style="font-size:.72rem;padding:4px 10px">›</button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!empty($eaPend)): ?>
    <script>
    (function() {
        var PAGE_SIZE = 8;
        var page = 1;
        var q    = '';
        var rows = Array.from(document.querySelectorAll('.ea-pend-row'));

        function visible() {
            if (!q) return rows;
            return rows.filter(function(r) {
                return r.dataset.name.indexOf(q) !== -1 || r.dataset.proceso.indexOf(q) !== -1;
            });
        }
        function render() {
            var vis   = visible();
            var total = vis.length;
            var pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
            page = Math.min(page, pages);
            var start = (page - 1) * PAGE_SIZE;
            var end   = start + PAGE_SIZE;
            rows.forEach(function(r){ r.style.display = 'none'; });
            vis.slice(start, end).forEach(function(r){ r.style.display = ''; });
            document.getElementById('eaPendInfo').textContent =
                'Mostrando ' + (total === 0 ? 0 : start + 1) + '–' + Math.min(end, total) + ' de ' + total;
            document.getElementById('eaPrevBtn').disabled = page <= 1;
            document.getElementById('eaNextBtn').disabled = page >= pages;
        }
        document.getElementById('eaPendSearch').addEventListener('input', function() {
            q = this.value.trim().toLowerCase();
            page = 1;
            render();
        });
        document.getElementById('eaPrevBtn').addEventListener('click', function() { page--; render(); });
        document.getElementById('eaNextBtn').addEventListener('click', function() { page++; render(); });
        render();
    })();
    </script>
    <?php endif; ?>

    <!-- Evaluados con calificaciones para seguimiento -->
    <?php if (!empty($eaEval)): ?>
    <div class="an-card" style="margin-top:20px">
        <div class="an-card-head">
            <h3 class="an-card-title"><i class="ti ti-star-half-filled"></i> Calificaciones obtenidas — seguimiento individual</h3>
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:.75rem;color:var(--an-muted)"><?= count($eaEval) ?> colaboradores</span>
                <button class="an-export-btn" onclick="anExportar('analitica_exp_azul_eval',this)">
                    <div class="an-spinner"></div>
                    <span class="an-btn-text"><i class="ti ti-file-spreadsheet"></i> Exportar</span>
                </button>
            </div>
        </div>
        <div class="an-table-wrap">
            <table class="an-table">
                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Cargo</th>
                        <th>Proceso</th>
                        <th>Líder</th>
                        <th>Colab→Líder</th>
                        <th>Líder→Colab</th>
                        <th class="num">Prom. Colab</th>
                        <th class="num">Prom. Líder</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($eaEval as $ev):
                    $pcL = (float)($ev['PROM_COLAB'] ?? 0);
                    $plL = (float)($ev['PROM_LIDER'] ?? 0);
                    $sC  = $pcL >= 4 ? 's-hi' : ($pcL >= 3 ? 's-mid' : ($pcL >= 2 ? 's-low' : ($pcL > 0 ? 's-bad' : 's-none')));
                    $sL2 = $plL >= 4 ? 's-hi' : ($plL >= 3 ? 's-mid' : ($plL >= 2 ? 's-low' : ($plL > 0 ? 's-bad' : 's-none')));
                ?>
                <tr>
                    <td class="bold"><?= htmlspecialchars($ev['COLABORADOR'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($ev['CARGO'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($ev['PROCESO'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($ev['LIDER'] ?? '—', ENT_QUOTES) ?></td>
                    <td><?php $hc = (int)($ev['HIZO_COLAB'] ?? 0); if ($hc === 2): ?><span class="an-badge ea-na">N/A</span><?php elseif ($hc): ?><span class="an-badge completo">Completó</span><?php else: ?><span class="an-badge ea-pending">Pendiente</span><?php endif; ?></td>
                    <td><?php if ((int)($ev['HIZO_LIDER'] ?? 0)): ?><span class="an-badge completo">Completó</span><?php else: ?><span class="an-badge ea-pending">Pendiente</span><?php endif; ?></td>
                    <td class="num"><span class="an-score <?= $sC ?>"><?= $pcL > 0 ? number_format($pcL,2) : '—' ?></span></td>
                    <td class="num"><span class="an-score <?= $sL2 ?>"><?= $plL > 0 ? number_format($plL,2) : '—' ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- TAB: SEGUIMIENTO FEEDBACK -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php elseif ($activeTab === 'feedback'):
    $fbKpis    = $resumenFeedback['kpis']    ?? [];
    $fbLideres = $resumenFeedback['porLider'] ?? [];

    $fl1T  = (int)($fbKpis['FL1_TOTAL']     ?? 0);
    $fl1F  = (int)($fbKpis['FL1_FIRMADOS']  ?? 0);
    $fl2T  = (int)($fbKpis['FL2_TOTAL']     ?? 0);
    $fl2F  = (int)($fbKpis['FL2_FIRMADOS']  ?? 0);
    $fl3T  = (int)($fbKpis['FL3_TOTAL']     ?? 0);
    $fl3F  = (int)($fbKpis['FL3_FIRMADOS']  ?? 0);
    $smT   = (int)($fbKpis['SMART_TOTAL']   ?? 0);
    $smA   = (int)($fbKpis['SMART_APROBADOS'] ?? 0);

    $pctFl1 = $fl1T ? round($fl1F / $fl1T * 100) : 0;
    $pctFl2 = $fl2T ? round($fl2F / $fl2T * 100) : 0;
    $pctFl3 = $fl3T ? round($fl3F / $fl3T * 100) : 0;
    $pctSm  = $smT  ? round($smA  / $smT  * 100) : 0;
    ?>

    <!-- KPIs globales -->
    <div class="an-kpi-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));margin-bottom:22px;">
        <div class="an-kpi blue">
            <div class="an-kpi-icon"><i class="ti ti-message-check"></i></div>
            <div class="an-kpi-value"><?= $fl1T ?></div>
            <div class="an-kpi-label">Desempeño registrado</div>
            <div class="an-kpi-sub"><?= $fl1F ?> firmados · <?= $pctFl1 ?>%</div>
        </div>
        <div class="an-kpi" style="border-top-color:#7c3aed">
            <div class="an-kpi-icon" style="background:#ede9fe;color:#7c3aed"><i class="ti ti-shield-check"></i></div>
            <div class="an-kpi-value"><?= $fl2T ?></div>
            <div class="an-kpi-label">Liderazgo registrado</div>
            <div class="an-kpi-sub"><?= $fl2F ?> firmados · <?= $pctFl2 ?>%</div>
        </div>
        <div class="an-kpi" style="border-top-color:#0891b2">
            <div class="an-kpi-icon" style="background:#cffafe;color:#0891b2"><i class="ti ti-droplet-check"></i></div>
            <div class="an-kpi-value"><?= $fl3T ?></div>
            <div class="an-kpi-label">Exp. Azul registrado</div>
            <div class="an-kpi-sub"><?= $fl3F ?> firmados · <?= $pctFl3 ?>%</div>
        </div>
        <div class="an-kpi green">
            <div class="an-kpi-icon"><i class="ti ti-target"></i></div>
            <div class="an-kpi-value"><?= $smT ?></div>
            <div class="an-kpi-label">Objetivos SMART</div>
            <div class="an-kpi-sub"><?= $smA ?> aprobados · <?= $pctSm ?>%</div>
        </div>
    </div>

    <!-- Tabla por líder -->
    <div class="an-card">
        <div class="an-card-head">
            <h3 class="an-card-title"><i class="ti ti-users-group"></i> Avance por líder</h3>
            <span style="font-size:.75rem;color:var(--an-muted);"><?= count($fbLideres) ?> líderes activos</span>
        </div>
        <div class="an-table-wrap">
            <table class="an-table">
                <thead>
                    <tr>
                        <th>Líder</th>
                        <th>Área / Proceso</th>
                        <th style="text-align:center;">Equipo</th>
                        <th style="text-align:center;">Desempeño</th>
                        <th style="text-align:center;">Liderazgo</th>
                        <th style="text-align:center;">Exp. Azul</th>
                        <th style="text-align:center;">SMART asig.</th>
                        <th style="text-align:center;">SMART apro.</th>
                        <th style="text-align:center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($fbLideres)): ?>
                <tr><td colspan="9" style="text-align:center;color:var(--an-muted);padding:32px;">Sin datos para este período</td></tr>
                <?php endif; ?>
                <?php foreach ($fbLideres as $ldr):
                    $tc     = (int)($ldr['TOTAL_COLAB']     ?? 0);
                    $fl1fb  = (int)($ldr['FL1_FEEDBACK']    ?? 0);
                    $fl1fm  = (int)($ldr['FL1_FIRMADOS']    ?? 0);
                    $fl2fb  = (int)($ldr['FL2_FEEDBACK']    ?? 0);
                    $fl2fm  = (int)($ldr['FL2_FIRMADOS']    ?? 0);
                    $fl3fb  = (int)($ldr['FL3_FEEDBACK']    ?? 0);
                    $fl3fm  = (int)($ldr['FL3_FIRMADOS']    ?? 0);
                    $cfl2   = (int)($ldr['COLABS_FL2']      ?? 0);
                    $cfl3   = (int)($ldr['COLABS_FL3']      ?? 0);
                    $smt    = (int)($ldr['SMART_TOTAL']     ?? 0);
                    $sma    = (int)($ldr['SMART_APROBADOS'] ?? 0);
                    $nafl2  = $cfl2 === 0;
                    $nafl3  = $cfl3 === 0;

                    $pct1 = $tc   > 0 ? round($fl1fm / $tc   * 100) : 0;
                    $pct2 = $cfl2 > 0 ? round($fl2fm / $cfl2 * 100) : 0;
                    $pct3 = $cfl3 > 0 ? round($fl3fm / $cfl3 * 100) : 0;

                    $fl1done = ($fl1fm >= $tc   && $tc   > 0);
                    $fl2done = $nafl2 || ($fl2fm >= $cfl2 && $cfl2 > 0);
                    $fl3done = $nafl3 || ($fl3fm >= $cfl3 && $cfl3 > 0);

                    if ($fl1done && $fl2done && $fl3done) {
                        $est = ['bg'=>'#dcfce7','c'=>'#15803d','ic'=>'circle-check','lbl'=>'Completo'];
                    } elseif ($fl1fb > 0 || $fl2fb > 0 || $fl3fb > 0) {
                        $est = ['bg'=>'#fef3c7','c'=>'#92400e','ic'=>'clock',        'lbl'=>'En curso'];
                    } else {
                        $est = ['bg'=>'#fee2e2','c'=>'#991b1b','ic'=>'x-circle',     'lbl'=>'Sin iniciar'];
                    }
                ?>
                <tr>
                    <td class="bold"><?= htmlspecialchars($ldr['NOMBRE'] ?? '', ENT_QUOTES) ?></td>
                    <td style="font-size:.78rem;color:var(--an-muted);"><?= htmlspecialchars($ldr['PROCESO'] ?? '—', ENT_QUOTES) ?></td>
                    <td class="num"><?= $tc ?></td>
                    <!-- Desempeño (FL1 — siempre aplica) -->
                    <td style="text-align:center;white-space:nowrap;">
                        <div style="font-size:.78rem;">
                            <span style="font-weight:700;color:var(--an-primary);"><?= $fl1fb ?></span><span style="color:var(--an-muted);font-size:.7rem;"> fb · </span><span style="font-weight:700;color:<?= $fl1fm > 0 ? '#15803d' : '#94a3b8' ?>;"><?= $fl1fm ?></span><span style="color:var(--an-muted);font-size:.7rem;"> firm</span>
                        </div>
                        <?php if ($tc > 0): ?>
                        <div style="margin-top:3px;height:3px;width:46px;display:inline-block;background:#e2e8f0;border-radius:2px;overflow:hidden;">
                            <div style="height:100%;width:<?= $pct1 ?>%;background:<?= $pct1 >= 100 ? '#16a34a' : '#0058af' ?>;border-radius:2px;"></div>
                        </div>
                        <?php endif; ?>
                    </td>
                    <!-- Liderazgo (FL2) -->
                    <td style="text-align:center;white-space:nowrap;">
                        <?php if ($nafl2): ?>
                        <span class="an-badge ea-na">N/A</span>
                        <?php else: ?>
                        <div style="font-size:.78rem;">
                            <span style="font-weight:700;color:#7c3aed;"><?= $fl2fb ?></span><span style="color:var(--an-muted);font-size:.7rem;"> fb · </span><span style="font-weight:700;color:<?= $fl2fm > 0 ? '#15803d' : '#94a3b8' ?>;"><?= $fl2fm ?></span><span style="color:var(--an-muted);font-size:.7rem;"> firm</span>
                        </div>
                        <?php if ($cfl2 > 0): ?>
                        <div style="margin-top:3px;height:3px;width:46px;display:inline-block;background:#e2e8f0;border-radius:2px;overflow:hidden;">
                            <div style="height:100%;width:<?= $pct2 ?>%;background:<?= $pct2 >= 100 ? '#16a34a' : '#7c3aed' ?>;border-radius:2px;"></div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <!-- Exp. Azul (FL3) -->
                    <td style="text-align:center;white-space:nowrap;">
                        <?php if ($nafl3): ?>
                        <span class="an-badge ea-na">N/A</span>
                        <?php else: ?>
                        <div style="font-size:.78rem;">
                            <span style="font-weight:700;color:#0891b2;"><?= $fl3fb ?></span><span style="color:var(--an-muted);font-size:.7rem;"> fb · </span><span style="font-weight:700;color:<?= $fl3fm > 0 ? '#15803d' : '#94a3b8' ?>;"><?= $fl3fm ?></span><span style="color:var(--an-muted);font-size:.7rem;"> firm</span>
                        </div>
                        <?php if ($cfl3 > 0): ?>
                        <div style="margin-top:3px;height:3px;width:46px;display:inline-block;background:#e2e8f0;border-radius:2px;overflow:hidden;">
                            <div style="height:100%;width:<?= $pct3 ?>%;background:<?= $pct3 >= 100 ? '#16a34a' : '#0891b2' ?>;border-radius:2px;"></div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td class="num"><?= $smt > 0 ? $smt : '<span style="color:#94a3b8;">—</span>' ?></td>
                    <td class="num"><?= $sma > 0 ? '<span style="color:#16a34a;font-weight:700;">' . $sma . '</span>' : '<span style="color:#94a3b8;">—</span>' ?></td>
                    <td style="text-align:center;">
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;
                                     border-radius:20px;font-size:.72rem;font-weight:700;
                                     background:<?= $est['bg'] ?>;color:<?= $est['c'] ?>;">
                            <i class="ti ti-<?= $est['ic'] ?>" style="font-size:11px;"></i> <?= $est['lbl'] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>

</div><!-- .an-wrap -->

<!-- ── Modal perfil colaborador (global) ── -->
<div id="an-modal-overlay" onclick="if(event.target===this)anCloseModal()">
    <div class="an-modal" id="an-modal">
        <div class="an-modal-head">
            <div class="an-modal-head-info">
                <h3 id="modalNombre">—</h3>
                <p id="modalSub">—</p>
            </div>
            <button class="an-modal-close" onclick="anCloseModal()"><i class="ti ti-x"></i></button>
        </div>
        <div class="an-modal-body" id="modalBody">
            <div class="an-empty"><i class="ti ti-loader an-spinner" style="display:inline-block;width:28px;height:28px;border-color:rgba(0,88,175,.3);border-top-color:var(--an-primary);"></i></div>
        </div>
    </div>
</div>
<script>
(function() {
    var AN_BASE        = '<?= APP_URL ?>?views=analitica';
    var currentPeriodo = <?= (int)(($idPeriodoFiltro ?? 0) ?: ($periodoActivo['IDPERIODO'] ?? 0)) ?>;

    window.anOpenPerfil = function(idEmp) {
        document.getElementById('an-modal-overlay').classList.add('open');
        document.getElementById('modalNombre').textContent = 'Cargando...';
        document.getElementById('modalSub').textContent    = '';
        document.getElementById('modalBody').innerHTML     = '<div class="an-empty" style="padding:40px"><div style="width:28px;height:28px;border:3px solid #dbeafe;border-top-color:var(--an-primary);border-radius:50%;animation:anSpin .7s linear infinite;margin:0 auto"></div></div>';
        var url = AN_BASE + '&action=getPerfilColaborador&idempleado=' + idEmp
                + (currentPeriodo ? '&idperiodo=' + currentPeriodo : '');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) { anRenderModal(data); })
            .catch(function() {
                document.getElementById('modalBody').innerHTML = '<div class="an-empty"><i class="ti ti-wifi-off"></i><p>Error al cargar el perfil</p></div>';
            });
    };

    window.anCloseModal = function() {
        document.getElementById('an-modal-overlay').classList.remove('open');
    };

    function fmtProm(v) { return v > 0 ? parseFloat(v).toFixed(2) : '—'; }

    window.anRenderModal = function(data) {
        var emp  = data.empleado       || {};
        var prom = data.promedios      || {};
        var ac   = data.acuerdos       || {};
        var hist = data.historial      || [];
        var comp = data.competencias   || [];
        var just = data.justificaciones || [];

        document.getElementById('modalNombre').textContent = emp.NOMBRE_COMPLETO || emp.NOMBRE || '—';
        document.getElementById('modalSub').textContent    = (emp.CARGO || '') + ' · ' + (emp.PROCESO || '');

        var promDesem = prom['LIDER_A_COLAB'] ? fmtProm(prom['LIDER_A_COLAB'].PROMEDIO) : '—';
        var promAuto  = prom['AUTO']          ? fmtProm(prom['AUTO'].PROMEDIO)           : '—';
        var promLider = prom['COLAB_A_LIDER'] ? fmtProm(prom['COLAB_A_LIDER'].PROMEDIO)  : '—';

        var html = '';
        html += '<div class="an-modal-section-title">Promedios del período</div>';
        html += '<div class="an-modal-kpis">'
              + '<div class="an-modal-kpi"><div class="an-modal-kpi-val">' + promDesem + '</div><div class="an-modal-kpi-lbl">Desempeño (líder)</div></div>'
              + '<div class="an-modal-kpi"><div class="an-modal-kpi-val">' + promAuto  + '</div><div class="an-modal-kpi-lbl">Autoevaluación</div></div>'
              + '<div class="an-modal-kpi"><div class="an-modal-kpi-val">' + promLider + '</div><div class="an-modal-kpi-lbl">Eval. al líder</div></div>'
              + '<div class="an-modal-kpi"><div class="an-modal-kpi-val">' + (ac.TOTAL || 0) + '</div><div class="an-modal-kpi-lbl">Acuerdos</div></div>'
              + '</div>';

        if (comp.length > 0) {
            var lcComp = comp.filter(function(c){ return c.TIPO_EVAL === 'LIDER_A_COLAB'; });
            if (lcComp.length > 0) {
                html += '<div class="an-modal-section-title">Competencias evaluadas por líder</div>';
                html += '<div class="an-table-wrap"><table class="an-table"><thead><tr><th>#</th><th>Competencia</th><th class="num">Promedio</th><th>Nivel</th></tr></thead><tbody>';
                lcComp.forEach(function(c) {
                    var val = parseFloat(c.PROMEDIO) || 0;
                    var bar = val > 0 ? Math.round(val/5*100) : 0;
                    var cls = val >= 4 ? 'green' : (val >= 3 ? 'blue' : (val >= 2 ? 'warn' : 'danger'));
                    html += '<tr><td>' + (c.NUM_PREGUNTA||'') + '</td>'
                          + '<td class="bold">' + (c.NOMBRE_COMP||'') + '</td>'
                          + '<td class="num">' + (val > 0 ? val.toFixed(2) : '—') + '</td>'
                          + '<td style="min-width:100px"><div class="an-progress"><div class="an-progress-bar ' + cls + '" style="width:' + bar + '%"></div></div></td>'
                          + '</tr>';
                });
                html += '</tbody></table></div>';
            }
        }

        if (just.length > 0) {
            html += '<div class="an-modal-section-title">Justificaciones del líder</div>';
            just.forEach(function(j) {
                var score = j.VALOR > 0 ? 'Calificación: ' + j.VALOR + '/5' : '';
                html += '<div class="an-just-row">'
                      + '<div class="an-just-comp">P' + (j.NUM_PREGUNTA||'') + ' — ' + (j.NOMBRE_COMP||'') + '</div>'
                      + (score ? '<div class="an-just-score">' + score + '</div>' : '')
                      + '<div class="an-just-text">' + (j.JUSTIFICACION||'').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>'
                      + '</div>';
            });
        }

        if (hist.length > 0) {
            html += '<div class="an-modal-section-title">Evolución histórica personal</div>';
            html += '<div style="position:relative;height:180px"><canvas id="chartPerfil"></canvas></div>';
        }
        document.getElementById('modalBody').innerHTML = html;
        if (hist.length > 0) {
            var labels  = hist.map(function(h){ return h.PERIODO_LABEL || ''; });
            var daAuto  = hist.map(function(h){ return parseFloat(h.PROM_AUTO)  || null; });
            var daLider = hist.map(function(h){ return parseFloat(h.PROM_LIDER) || null; });
            new Chart(document.getElementById('chartPerfil'), {
                type: 'line',
                data: { labels: labels, datasets: [
                    { label:'Autoeval', data:daAuto,  borderColor:'#16a34a', tension:.3, pointRadius:3, fill:false },
                    { label:'Lider',    data:daLider, borderColor:'#0058af', tension:.3, pointRadius:3, fill:false }
                ]},
                options: { responsive:true, maintainAspectRatio:false,
                    scales: { y:{min:0,max:5,ticks:{stepSize:1},grid:{color:'#f1f5f9'}}, x:{grid:{display:false}} },
                    plugins:{ legend:{ labels:{ boxWidth:10, font:{size:10} } } }
                }
            });
        }
    };
})();
</script>

<!-- ── Export helper ── -->
<script>
(function() {
    var EXPORT_BASE = '<?= $_exportUrl ?>';
    var PERIODO_ID  = <?= (int)(($idPeriodoFiltro ?? 0) ?: ($periodoActivo['IDPERIODO'] ?? 0)) ?>;

    window.anExportar = function(tipo, btn) {
        var orig = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('loading');

        var url = EXPORT_BASE + '?tipo=' + tipo + (PERIODO_ID ? '&idperiodo=' + PERIODO_ID : '');
        fetch(url)
            .then(function(r) {
                if (!r.ok) throw new Error('Error ' + r.status);
                return r.blob();
            })
            .then(function(blob) {
                var a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = tipo + '_' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '.csv';
                document.body.appendChild(a); a.click(); document.body.removeChild(a);
                URL.revokeObjectURL(a.href);
                btn.disabled = false; btn.classList.remove('loading'); btn.innerHTML = orig;
                if (window.Swal) Swal.fire({ icon:'success', title:'Exportación exitosa', timer:1800, showConfirmButton:false, toast:true, position:'top-end' });
            })
            .catch(function(err) {
                btn.disabled = false; btn.classList.remove('loading'); btn.innerHTML = orig;
                if (window.Swal) Swal.fire({ icon:'error', title:'Error al exportar', text: err.message });
            });
    };
})();
</script>

<!-- ══════════════════════════════════════════════════════════
     TOUR INTERACTIVO — Fase 4 · Analítica BI
     Driver.js 1.3.1 · Carga diferida · Reversible sin impacto
══════════════════════════════════════════════════════════ -->

<button id="an-tour-btn"
        onclick="iniciarTourAnalitica()"
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

<div id="an-tour-tip"
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
(function(){
    var btn = document.getElementById('an-tour-btn');
    var tip = document.getElementById('an-tour-tip');
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

function _anLoadDriver(cb) {
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
        'max-width:440px!important;min-width:300px!important;overflow:hidden!important;' +
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

function iniciarTourAnalitica() {
    _anLoadDriver(function() {
        var driverFn  = window.driver.js.driver;
        var activeTab = '<?= htmlspecialchars($activeTab, ENT_QUOTES) ?>';

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

        var pasos = [];

        // ── 0. Bienvenida ───────────────────────────────────────────────────
        pasos.push({
            popover: {
                title:       ic('chart-dots') + ' Panel de Analítica BI',
                description: 'Panel exclusivo para administradores. Centraliza todos los indicadores ' +
                             'de evaluación de desempeño, planes de mejora SMART y alertas críticas. ' +
                             'Toda la información se filtra por período.',
                side: 'over', align: 'center'
            }
        });

        // ── 1. Hero ─────────────────────────────────────────────────────────
        if (document.querySelector('.an-hero')) {
            pasos.push({
                element: '.an-hero',
                popover: {
                    title:       ic('layout-dashboard') + ' Analítica BI',
                    description: 'El banner indica el período activo que se está analizando. ' +
                                 'Usa el selector de período a la derecha para ver datos históricos de evaluaciones anteriores.',
                    side: 'bottom', align: 'start'
                }
            });
        }

        // ── 2. Selector de período ──────────────────────────────────────────
        if (document.querySelector('.an-periodo-select')) {
            pasos.push({
                element: '.an-periodo-select',
                popover: {
                    title:       ic('calendar') + ' Filtro de período',
                    description: 'Cambia el período de evaluación para comparar datos históricos. ' +
                                 'Al cambiar, la página recarga mostrando los indicadores del período seleccionado ' +
                                 'en todas las secciones.',
                    side: 'bottom', align: 'end'
                }
            });
        }

        // ── 3. Navegación por pestañas ──────────────────────────────────────
        if (document.querySelector('.an-tabs')) {
            pasos.push({
                element: '.an-tabs',
                popover: {
                    title:       ic('layout-columns') + ' 10 secciones de análisis',
                    description:
                        ic('layout-dashboard','#64748b') + ' <strong>Dashboard</strong> — KPIs y gráficos del período<br>' +
                        ic('users','#64748b')             + ' <strong>Colaboradores</strong> — tabla detallada por persona<br>' +
                        ic('user-search','#64748b')       + ' <strong>Individual</strong> — perfil completo de un colaborador<br>' +
                        ic('trophy','#64748b')            + ' <strong>Ranking</strong> — mejores calificaciones<br>' +
                        ic('git-compare','#64748b')       + ' <strong>Brechas</strong> — competencias por reforzar<br>' +
                        ic('clipboard-list','#64748b')    + ' <strong>Acuerdos</strong> — plan de mejora SMART<br>' +
                        ic('droplet','#64748b')           + ' <strong>Exp. Azul</strong> — evaluación asistencial<br>' +
                        ic('alert-triangle','#d97706')    + ' <strong>Alertas</strong> — pendientes críticos del período',
                    side: 'bottom', align: 'start'
                }
            });
        }

        // ── 4. Contenido activo — adaptativo por pestaña ───────────────────
        if (activeTab === 'dashboard' && document.querySelector('.an-kpi-grid')) {
            pasos.push({
                element: '.an-kpi-grid',
                popover: {
                    title:       ic('chart-bar') + ' KPIs del período',
                    description: 'Indicadores clave del estado de la evaluación:<br><br>' +
                                 ic('users','#64748b')        + ' Total de empleados en el período<br>' +
                                 ic('user-check','#64748b')   + ' % con autoevaluación completada<br>' +
                                 ic('star','#64748b')         + ' % evaluados por su líder<br>' +
                                 ic('circle-check','#15803d') + ' % con proceso completo<br>' +
                                 ic('chart-bar','#64748b')    + ' Promedio global de calificaciones (1–5)',
                    side: 'bottom', align: 'start'
                }
            });
        } else if (activeTab === 'colaboradores' && document.querySelector('.an-table-wrap')) {
            pasos.push({
                element: '.an-table-wrap',
                popover: {
                    title:       ic('users') + ' Tabla de colaboradores',
                    description: 'Todos los colaboradores del período con su estado de evaluación, ' +
                                 'calificación promedio y avance general. Puedes filtrar por ' +
                                 '<strong>nombre</strong>, <strong>proceso</strong> o <strong>estado</strong>.',
                    side: 'top', align: 'start'
                }
            });
        } else if (activeTab === 'ranking' && document.querySelector('.an-table-wrap')) {
            pasos.push({
                element: '.an-table-wrap',
                popover: {
                    title:       ic('trophy') + ' Ranking del período',
                    description: 'Colaboradores ordenados por calificación promedio de mayor a menor. ' +
                                 'Puedes filtrar por tipo de evaluación y proceso para comparar grupos específicos.',
                    side: 'top', align: 'start'
                }
            });
        } else if (activeTab === 'brechas' && document.querySelector('.an-card')) {
            pasos.push({
                element: '.an-card',
                popover: {
                    title:       ic('git-compare') + ' Análisis de brechas',
                    description: 'Compara las calificaciones de <strong>autoevaluación</strong> vs <strong>evaluación del líder</strong> ' +
                                 'por competencia. Las brechas altas indican diferencias significativas de percepción.',
                    side: 'top', align: 'start'
                }
            });
        } else if (activeTab === 'acuerdos' && document.querySelector('.an-table-wrap')) {
            pasos.push({
                element: '.an-table-wrap',
                popover: {
                    title:       ic('clipboard-list') + ' Acuerdos SMART',
                    description: 'Todos los objetivos de mejora asignados en las sesiones de feedback. ' +
                                 'Filtra por proceso o estado (Pendiente, Respondido, Aprobado) para hacer seguimiento.',
                    side: 'top', align: 'start'
                }
            });
        } else if (activeTab === 'alertas') {
            var alertEl = document.querySelector('[id^="atb-"]') ||
                          document.querySelector('.an-card');
            if (alertEl) {
                pasos.push({
                    element: alertEl,
                    popover: {
                        title:       ic('alert-triangle','#d97706') + ' Alertas del período',
                        description: 'Tres categorías de alertas críticas:<br><br>' +
                                     ic('user-x','#dc2626')      + ' <strong>Sin autoevaluación</strong> — no han iniciado<br>' +
                                     ic('clock','#d97706')        + ' <strong>Acuerdos vencidos</strong> — sin respuesta del colaborador<br>' +
                                     ic('users-minus','#dc2626')  + ' <strong>Líderes sin evaluar</strong> — no han evaluado a su equipo<br><br>' +
                                     'Usa el buscador para filtrar por nombre dentro de cada sección.',
                        side: 'top', align: 'start'
                    }
                });
            }
        } else if (document.querySelector('.an-card')) {
            pasos.push({
                element: '.an-card',
                popover: {
                    title:       ic('table') + ' Sección activa',
                    description: 'Los datos se actualizan automáticamente según el período seleccionado. ' +
                                 'Navega entre las pestañas para acceder a diferentes análisis sin perder el filtro activo.',
                    side: 'top', align: 'start'
                }
            });
        }

        // ── 5. Final ────────────────────────────────────────────────────────
        pasos.push({
            popover: {
                title:       ic('circle-check','#15803d') + ' Panel listo para analizar',
                description: 'Ya conoces el panel de Analítica BI. Explora cada sección para obtener ' +
                             'una visión completa del desempeño organizacional. Puedes repetir este tour ' +
                             'pulsando el botón ' + helpBtn + ' en la esquina inferior derecha.',
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
