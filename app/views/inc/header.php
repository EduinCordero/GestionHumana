<?php
/**
 * header.php — Sidebar lateral colapsable V2
 * Reemplaza el header horizontal estático.
 * Compatible con el layout existente de index.php (main > div#main-wrapper.flex.pt-16)
 */
$currentUrl = $_GET['views'] ?? 'home';
$currentSection = explode('/', $currentUrl)[0];
$isAdmin = isset($_SESSION['esadmin']) && $_SESSION['esadmin'] == 1;
$userName = htmlspecialchars(($_SESSION['nombres'] ?? '') . ' ' . ($_SESSION['apellidos'] ?? ''));
$userIdentificacion = htmlspecialchars($_SESSION['identificacion'] ?? '');
$isMale = ($_SESSION['sexo'] ?? '') === 'MASCULINO';
$nivelCargo = $_SESSION['nivelcargo'] ?? '';


// Verificar si es líder funcional para mostrar Mi Equipo en reportes
$esLiderFuncionalSb = false;
try {
    $connSbLf = (new app\models\mainModel)->conectar();
    $idemSb   = (int)($_SESSION['idempleado'] ?? 0);
    $sqlSbLf  = "SELECT ES_LIDER_FUNCIONAL FROM VAADINWEB.HUMEMPLEADOEVAL
                 WHERE IDEMPLEADO = $idemSb AND ACTIVO = 1 AND ROWNUM = 1";
    $qSbLf    = oci_parse($connSbLf, $sqlSbLf);
    if ($qSbLf && oci_execute($qSbLf)) {
        $rSbLf = oci_fetch_assoc($qSbLf);
        $esLiderFuncionalSb = $rSbLf && (int)$rSbLf['ES_LIDER_FUNCIONAL'] === 1;
        oci_free_statement($qSbLf);
    }
} catch (Exception $eSbLf) { /* silencioso */ }

// Tab activo actual (para resaltar subítem correcto)
$activeTab = $_GET['tab'] ?? '';

// Submenús por módulo
$subMenus = [
    'reportes' => array_values(array_filter([
        ['tab'=>'autoeval',  'icon'=>'ti-user-check',      'label'=>'Autoevaluación'],
        ['tab'=>'recibidas', 'icon'=>'ti-arrow-down-circle','label'=>'Recibidas'],
        ['tab'=>'realizadas','icon'=>'ti-arrow-up-circle',  'label'=>'Realizadas'],
        $esLiderFuncionalSb
            ? ['tab'=>'equipo', 'icon'=>'ti-users', 'label'=>'Mi Equipo'] : null,
        ['tab'=>'plan',      'icon'=>'ti-target',           'label'=>'Mi Plan de Mejora'],
    ])),
    'admin' => [
        ['tab'=>'periodos',      'icon'=>'ti-calendar',        'label'=>'Períodos'],
        ['tab'=>'seguimiento',   'icon'=>'ti-eye',              'label'=>'Seguimiento'],
        ['tab'=>'avance',        'icon'=>'ti-trending-up',      'label'=>'Avance'],
        ['tab'=>'promedios',     'icon'=>'ti-chart-bar',        'label'=>'Promedios'],
        ['tab'=>'usuarios',      'icon'=>'ti-users',            'label'=>'Usuarios'],
        ['tab'=>'objetivos',     'icon'=>'ti-target',           'label'=>'Objetivos'],
        ['tab'=>'competencias',  'icon'=>'ti-award',            'label'=>'Competencias'],
        ['tab'=>'colaboradores', 'icon'=>'ti-id-badge',         'label'=>'Colaboradores'],
    ],
    'analitica' => [
        ['tab'=>'dashboard',     'icon'=>'ti-layout-dashboard', 'label'=>'Dashboard'],
        ['tab'=>'individual',    'icon'=>'ti-user-search',      'label'=>'Individual'],
        ['tab'=>'ranking',       'icon'=>'ti-trophy',           'label'=>'Ranking'],
        ['tab'=>'historico',     'icon'=>'ti-timeline',         'label'=>'Histórico'],
        ['tab'=>'acuerdos',      'icon'=>'ti-clipboard-list',   'label'=>'Acuerdos'],
        ['tab'=>'procesos',      'icon'=>'ti-building',         'label'=>'Procesos'],
        ['tab'=>'alertas',       'icon'=>'ti-alert-triangle',   'label'=>'Alertas'],
        ['tab'=>'colaboradores', 'icon'=>'ti-users',            'label'=>'Colaboradores'],
        ['tab'=>'brechas',       'icon'=>'ti-git-compare',      'label'=>'Brechas'],
        ['tab'=>'exp_azul',      'icon'=>'ti-droplet',          'label'=>'Exp. Azul'],
    ],
    'liderPanel' => [
        ['tab'=>'dashboard', 'icon'=>'ti-speedometer',      'label'=>'Dashboard'],
        ['tab'=>'acuerdos',  'icon'=>'ti-clipboard-list',   'label'=>'Acuerdos'],
        ['tab'=>'feedback',  'icon'=>'ti-message-circle',   'label'=>'Feedback'],
        ['tab'=>'alertas',   'icon'=>'ti-alert-triangle',   'label'=>'Alertas'],
    ],
];

// Navegación principal
$navItems = [
    ['url' => 'home',        'icon' => 'ti-home',            'label' => 'Inicio'],
    ['url' => 'evaluarList', 'icon' => 'ti-clipboard-check', 'label' => 'Evaluaciones'],
    ['url' => 'feedback',    'icon' => 'ti-message-circle',  'label' => 'Feedback',        'liderOnly' => true],
    ['url' => 'reportes',    'icon' => 'ti-chart-bar',       'label' => 'Reportes',        'sub' => true],
    ['url' => 'liderPanel',  'icon' => 'ti-layout-dashboard','label' => 'Panel Líder',     'sub' => true, 'liderOnly' => true],
    ['url' => 'seguridad',   'icon' => 'ti-shield-lock',     'label' => 'Seguridad'],
    ['url' => 'admin',       'icon' => 'ti-settings',        'label' => 'Admin',           'sub' => true, 'adminOnly' => true],
  //  ['url' => 'analitica',   'icon' => 'ti-chart-histogram', 'label' => 'Analítica',       'sub' => true, 'adminOnly' => true],
];
?>

<style>
/* ── Variables ───────────────────────────────────────────────────────────── */
:root {
    --sb-w:        258px;
    --sb-w-mini:    64px;
    --sb-bg:       #0058af;
    --sb-accent:   #ffffff;
    --sb-accent2:  #93c5fd;
    --sb-text:     rgba(255,255,255,0.85);
    --sb-text-dim: rgba(255,255,255,0.45);
    --sb-hover:    rgba(255,255,255,0.12);
    --sb-active:   rgba(255,255,255,0.22);
    --sb-border:   rgba(255,255,255,0.15);
    --sb-radius:   12px;
    --sb-trans:    all 0.28s cubic-bezier(0.4,0,0.2,1);
}

/* ── Reset layout: mover todo el contenido a la derecha ─────────────────── */
#main-wrapper {
    padding-top: 0 !important;
    padding-left: var(--sb-w);
    transition: padding-left 0.28s cubic-bezier(0.4,0,0.2,1);
}
#main-wrapper.sb-mini { padding-left: var(--sb-w-mini); }

/* ── Sidebar ─────────────────────────────────────────────────────────────── */
#zayma-sidebar {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    width: var(--sb-w);
    background: var(--sb-bg);
    display: flex;
    flex-direction: column;
    z-index: 9999;
    transition: var(--sb-trans);
    overflow: hidden;
    box-shadow: 4px 0 24px rgba(0,0,0,0.35);
}
#zayma-sidebar.mini { width: var(--sb-w-mini); }

/* Fondo decorativo — círculos claros como en los modales de feedback */
#zayma-sidebar::before {
    content: '';
    position: absolute;
    width: 280px; height: 280px; border-radius: 50%;
    background: rgba(255,255,255,0.06);
    top: -80px; right: -60px;
    pointer-events: none;
}
#zayma-sidebar::after {
    content: '';
    position: absolute;
    width: 160px; height: 160px; border-radius: 50%;
    background: rgba(255,255,255,0.04);
    bottom: 60px; left: -40px;
    pointer-events: none;
}

/* ── Logo / Brand ────────────────────────────────────────────────────────── */
.sb-brand {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 20px 16px 18px;
    border-bottom: 1px solid var(--sb-border);
    min-height: 68px;
    flex-shrink: 0;
    position: relative;
}
.sb-brand-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: rgba(255,255,255,0.18);
    border: 1.5px solid rgba(255,255,255,0.3);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    backdrop-filter: blur(4px);
}
.sb-brand-icon i { font-size: 18px; color: #fff; }
.sb-brand-text {
    display: flex; flex-direction: column;
    overflow: hidden;
    transition: var(--sb-trans);
    white-space: nowrap;
}
.sb-brand-text span:first-child {
    font-size: 13px; font-weight: 800;
    color: #fff; letter-spacing: 0.5px;
    text-transform: uppercase;
}
.sb-brand-text span:last-child {
    font-size: 10px; color: var(--sb-text-dim);
    letter-spacing: 1px; text-transform: uppercase;
    margin-top: 1px;
}
#zayma-sidebar.mini .sb-brand-text { opacity: 0; width: 0; }

/* Toggle btn */
.sb-toggle {
    position: absolute; right: 0; top: 50%;
    transform: translateY(-50%);
    width: 28px; height: 44px;
    background: rgba(255,255,255,0.15);
    border-radius: 0 8px 8px 0;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: var(--sb-trans);
    border: none;
    border-left: 1px solid rgba(255,255,255,0.2);
    z-index: 10;
}
.sb-toggle:hover { background: rgba(255,255,255,0.25); }
.sb-toggle i { color: #fff; font-size: 11px; }
.sb-toggle i {
    font-size: 13px; color: #fff;
    transition: transform 0.28s ease;
}
#zayma-sidebar.mini .sb-toggle i { transform: rotate(180deg); }

/* ── Nav ─────────────────────────────────────────────────────────────────── */
.sb-nav {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 12px 10px;
    scrollbar-width: none;
}
.sb-nav::-webkit-scrollbar { display: none; }

.sb-section-label {
    font-size: 9.5px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--sb-text-dim);
    padding: 10px 8px 5px;
    white-space: nowrap;
    overflow: hidden;
    transition: var(--sb-trans);
}
#zayma-sidebar.mini .sb-section-label { opacity: 0; height: 0; padding: 0; }

.sb-item {
    display: flex; align-items: center;
    gap: 11px;
    padding: 10px 10px;
    border-radius: var(--sb-radius);
    color: var(--sb-text);
    text-decoration: none;
    transition: var(--sb-trans);
    position: relative;
    white-space: nowrap;
    margin-bottom: 2px;
    font-size: 13.5px; font-weight: 500;
    overflow: hidden;
}
.sb-item i {
    font-size: 19px;
    flex-shrink: 0;
    width: 22px; text-align: center;
    transition: var(--sb-trans);
}
.sb-item-label {
    flex: 1;
    transition: opacity 0.2s ease, width 0.28s ease;
    overflow: hidden;
}
.sb-item:hover {
    background: var(--sb-hover);
    color: #fff;
    transform: translateX(2px);
}
.sb-item.active {
    background: var(--sb-active);
    color: #fff;
}
.sb-item.active::before {
    content: '';
    position: absolute; left: 0; top: 20%; bottom: 20%;
    width: 3px;
    background: rgba(255,255,255,0.9);
    border-radius: 0 3px 3px 0;
}
.sb-item.active i { color: #fff; }
#zayma-sidebar.mini .sb-item-label { opacity: 0; width: 0; }
#zayma-sidebar.mini .sb-item { justify-content: center; padding: 10px; }

/* ── Submenú ─────────────────────────────────────────────────────────────── */
.sb-group { position: relative; }

.sb-group-toggle {
    display: flex; align-items: center;
    gap: 11px;
    padding: 10px 10px;
    border-radius: var(--sb-radius);
    color: var(--sb-text);
    cursor: pointer;
    transition: var(--sb-trans);
    position: relative;
    white-space: nowrap;
    margin-bottom: 2px;
    font-size: 13.5px; font-weight: 500;
    user-select: none;
}
.sb-group-toggle:hover { background: var(--sb-hover); color: #fff; }
.sb-group-toggle.active { background: var(--sb-active); color: #fff; }
.sb-group-toggle.active::before {
    content: '';
    position: absolute; left: 0; top: 20%; bottom: 20%;
    width: 3px; background: rgba(255,255,255,0.9);
    border-radius: 0 3px 3px 0;
}
.sb-group-toggle i.main-icon {
    font-size: 19px; flex-shrink: 0;
    width: 22px; text-align: center;
}
.sb-group-label {
    flex: 1; overflow: hidden;
    transition: opacity 0.2s ease, width 0.28s ease;
}
.sb-group-arrow {
    font-size: 12px;
    transition: transform 0.25s ease;
    opacity: 0.6;
    flex-shrink: 0;
}
.sb-group.open .sb-group-arrow { transform: rotate(90deg); }

/* Subítems */
.sb-sub {
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.3s cubic-bezier(0.4,0,0.2,1);
    margin-left: 12px;
    border-left: 1.5px solid rgba(255,255,255,0.15);
    padding-left: 4px;
}
.sb-group.open .sb-sub { max-height: 400px; }

.sb-sub-item {
    display: flex; align-items: center; gap: 9px;
    padding: 7px 10px;
    border-radius: 8px;
    color: var(--sb-text-dim);
    text-decoration: none;
    font-size: 12.5px; font-weight: 500;
    transition: var(--sb-trans);
    white-space: nowrap;
    margin-bottom: 1px;
}
.sb-sub-item i { font-size: 15px; flex-shrink: 0; width: 18px; text-align: center; }
.sb-sub-item:hover { background: var(--sb-hover); color: #fff; }
.sb-sub-item.active {
    background: rgba(255,255,255,0.15);
    color: #fff; font-weight: 700;
}
.sb-sub-label { flex: 1; overflow: hidden; transition: opacity 0.2s ease; }

/* Modo mini — ocultar labels y mostrar flyout */
#zayma-sidebar.mini .sb-group-label,
#zayma-sidebar.mini .sb-group-arrow,
#zayma-sidebar.mini .sb-sub { display: none; }
#zayma-sidebar.mini .sb-group-toggle { justify-content: center; padding: 10px; }
#zayma-sidebar.mini .sb-item-label { opacity: 0; width: 0; }
#zayma-sidebar.mini .sb-item { justify-content: center; padding: 10px; }

/* Flyout en modo mini */
#zayma-sidebar.mini .sb-group { position: relative; }
#zayma-sidebar.mini .sb-group:hover .sb-sub-flyout {
    display: block;
    animation: sbFlyout 0.15s ease;
}
.sb-sub-flyout {
    display: none;
    position: absolute;
    left: calc(var(--sb-w-mini) - 4px);
    top: 0;
    background: #003d82;
    border-radius: 10px;
    padding: 6px;
    min-width: 180px;
    box-shadow: 4px 4px 20px rgba(0,0,0,0.25);
    z-index: 9999;
}
.sb-sub-flyout .sb-sub-item { color: rgba(255,255,255,0.75); }
.sb-sub-flyout .sb-sub-item:hover { color: #fff; }
@keyframes sbFlyout { from{opacity:0;transform:translateX(-6px)} to{opacity:1;transform:translateX(0)} }

/* Tooltip en modo mini */
#zayma-sidebar.mini .sb-item::after {
    content: attr(data-tip);
    position: absolute;
    left: calc(var(--sb-w-mini) - 6px);
    background: #1e293b;
    color: #fff;
    font-size: 12px; font-weight: 600;
    padding: 5px 10px;
    border-radius: 6px;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.15s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 999;
}
#zayma-sidebar.mini .sb-item:hover::after { opacity: 1; }

/* ── Divider ─────────────────────────────────────────────────────────────── */
.sb-divider {
    height: 1px;
    background: var(--sb-border);
    margin: 8px 0;
    flex-shrink: 0;
}

/* ── Footer (usuario) ────────────────────────────────────────────────────── */
.sb-footer {
    padding: 12px 10px;
    border-top: 1px solid var(--sb-border);
    flex-shrink: 0;
}
.sb-user {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 8px;
    border-radius: var(--sb-radius);
    cursor: pointer;
    transition: var(--sb-trans);
    position: relative;
}
.sb-user:hover { background: var(--sb-hover); }
.sb-user-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: rgba(255,255,255,0.22);
    border: 1.5px solid rgba(255,255,255,0.35);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800; color: #fff;
    flex-shrink: 0;
    letter-spacing: -0.5px;
}
.sb-user-info {
    flex: 1; overflow: hidden;
    transition: var(--sb-trans);
}
.sb-user-name {
    font-size: 12.5px; font-weight: 700;
    color: #fff; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.sb-user-id {
    font-size: 10.5px; color: var(--sb-text-dim);
}
.sb-user-actions {
    display: flex; gap: 6px;
    transition: var(--sb-trans);
}
.sb-user-btn {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    background: var(--sb-hover);
    color: var(--sb-text);
    text-decoration: none;
    transition: var(--sb-trans);
    border: none; cursor: pointer;
    font-size: 14px;
}
.sb-user-btn:hover { background: var(--sb-accent); color: #fff; }
#zayma-sidebar.mini .sb-user-info,
#zayma-sidebar.mini .sb-user-actions { opacity: 0; width: 0; overflow: hidden; }
#zayma-sidebar.mini .sb-user { justify-content: center; }

/* ── Top bar: solo para el toggle en móvil ──────────────────────────────── */
#zayma-topbar {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0;
    height: 56px;
    background: #0058af;
    z-index: 9998;
    align-items: center;
    padding: 0 16px;
    gap: 12px;
    box-shadow: 0 2px 16px rgba(0,88,175,0.3);
}
#zayma-topbar .sb-brand-icon {
    width: 32px; height: 32px; border-radius: 8px;
}
#zayma-topbar-title {
    font-size: 13px; font-weight: 800;
    color: #fff; text-transform: uppercase; letter-spacing: 0.5px;
    flex: 1;
}

/* ── Overlay móvil ───────────────────────────────────────────────────────── */
#sb-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 9997;
    backdrop-filter: blur(2px);
}

/* ── Panel de notificaciones ─────────────────────────────────────────────── */
#sb-notif-panel {
    position: fixed;
    right: 20px; top: 20px;
    width: 340px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    z-index: 9000;
    display: none;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    transform: translateY(8px);
    opacity: 0;
    transition: all 0.2s ease;
}
#sb-notif-panel.open {
    display: block;
    transform: translateY(0);
    opacity: 1;
}
/* Flecha apuntando hacia arriba (al botón) */
#sb-notif-panel::before {
    content: '';
    position: absolute;
    top: -7px; right: 18px;
    width: 14px; height: 14px;
    background: #0058af;
    transform: rotate(45deg);
    border-radius: 2px 0 0 0;
}
.sb-notif-head {
    background: linear-gradient(135deg,#0058af,#2563eb);
    padding: 14px 18px;
    display: flex; align-items: center; justify-content: space-between;
    position: relative; overflow: hidden;
}
.sb-notif-head::before {
    content:''; position:absolute;
    width:120px;height:120px;border-radius:50%;
    background:rgba(255,255,255,0.07);
    top:-30px;right:-20px;pointer-events:none;
}
.sb-notif-head-left { display:flex;align-items:center;gap:8px; }
.sb-notif-head span { font-size: 13px; font-weight: 700; color: #fff; }
.sb-notif-badge {
    background: #ef4444; color: #fff;
    font-size: 10px; font-weight: 800;
    padding: 1px 6px; border-radius: 20px;
    min-width: 18px; text-align: center;
    display: none;
}
.sb-notif-badge.has-items { display: inline-block; }
.sb-notif-head button {
    background: rgba(255,255,255,0.2); border: none; border-radius: 6px;
    width: 26px; height: 26px; cursor: pointer; color: #fff; font-size: 14px;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s; position: relative; z-index: 1;
}
.sb-notif-head button:hover { background: rgba(255,255,255,0.35); }
.sb-notif-body { max-height: 360px; overflow-y: auto; }
.sb-notif-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    display: flex; gap: 12px; align-items: flex-start;
    text-decoration: none;
    transition: background 0.15s;
    cursor: pointer;
}
.sb-notif-item:hover { background: #f8fafc; }
.sb-notif-item:last-child { border-bottom: none; }
.sb-notif-dot {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
    margin-top: 1px;
}
.sb-notif-dot.warn  { background: #fef3c7; color: #d97706; }
.sb-notif-dot.info  { background: #dbeafe; color: #2563eb; }
.sb-notif-dot.alert { background: #fee2e2; color: #dc2626; }
.sb-notif-dot.ok    { background: #dcfce7; color: #16a34a; }
.sb-notif-content h6 {
    font-size: 12.5px; font-weight: 700; color: #1e293b; margin: 0 0 3px;
}
.sb-notif-content p {
    font-size: 11.5px; color: #64748b; line-height: 1.5; margin: 0;
}
.sb-notif-empty {
    padding: 32px 16px; text-align: center; color: #94a3b8;
}
.sb-notif-empty i { font-size: 32px; margin-bottom: 8px; display: block; }
.sb-notif-empty p { font-size: 12.5px; margin: 0; }

/* ── Float actions — esquina superior derecha ───────────────────────────── */
.sb-float-actions {
    position: fixed;
    top: 20px; right: 20px;
    display: flex; flex-direction: row; gap: 8px;
    z-index: 8999;
}
.sb-float-btn {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
    font-size: 17px; color: #475569;
    position: relative;
}
.sb-float-btn:hover {
    background: #0058af; color: #fff;
    box-shadow: 0 6px 20px rgba(0,88,175,0.3);
    transform: scale(1.08);
}
.sb-float-btn .notif-dot {
    position: absolute; top: 2px; right: 2px;
    width: 10px; height: 10px;
    background: #ef4444; border-radius: 50%;
    border: 2px solid #fff;
    display: none;
}
.sb-float-btn .notif-dot.visible { display: block; }

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    #main-wrapper,
    #main-wrapper.sb-mini { padding-left: 0; padding-top: 56px !important; }
    #zayma-topbar { display: flex; }
    #zayma-sidebar {
        transform: translateX(-100%);
        width: var(--sb-w) !important;
    }
    #zayma-sidebar.mobile-open {
        transform: translateX(0);
    }
    #sb-overlay.open { display: block; }
    .sb-float-actions { top: 10px; right: 10px; }
}
</style>

<!-- ── TOP BAR MÓVIL ─────────────────────────────────────────────────────── -->
<div id="zayma-topbar">
    <button onclick="sbMobileToggle()" style="background:none;border:none;cursor:pointer;color:#fff;font-size:22px;">
        <i class="ti ti-menu-2"></i>
    </button>
    <div class="sb-brand-icon">
        <i class="ti ti-heart-rate-monitor"></i>
    </div>
    <div id="zayma-topbar-title">Gestión Humana</div>
</div>

<!-- ── OVERLAY MÓVIL ─────────────────────────────────────────────────────── -->
<div id="sb-overlay" onclick="sbMobileToggle()"></div>

<!-- ══════════════ SIDEBAR ══════════════ -->
<aside id="zayma-sidebar">

    <!-- Brand -->
    <div class="sb-brand">
        <div class="sb-brand-icon">
            <i class="ti ti-heart-rate-monitor"></i>
        </div>
        <div class="sb-brand-text">
            <span style="font-size:9px;letter-spacing:1px;font-weight:800;text-transform:uppercase;white-space:nowrap;">Gestión Humana y Cultura</span>
            <span style="font-size:10.5px;font-weight:500;color:rgba(255,255,255,0.8);letter-spacing:0.2px;text-transform:none;white-space:nowrap;">Evaluación de desempeño V2</span>
            <span style="font-size:8.5px;letter-spacing:0.8px;font-weight:600;text-transform:uppercase;color:rgba(255,255,255,0.45);margin-top:1px;white-space:nowrap;">Clínica Zayma SAS</span>
        </div>
        <div class="sb-toggle" onclick="sbToggle()" title="Colapsar menú" id="sb-toggle-btn">
            <span style="font-size:10px;font-weight:800;color:#fff;letter-spacing:-1px;line-height:1;">«</span>
        </div>
    </div>

    <!-- Navegación -->
    <nav class="sb-nav">
        <div class="sb-section-label">Menú principal</div>

        <?php foreach ($navItems as $item):
            if (!empty($item['adminOnly']) && !$isAdmin) continue;
            if (!empty($item['liderOnly']) && !$esLiderFuncionalSb && !$isAdmin) continue;
            $isActive   = $currentSection === $item['url'];
            $hasSub     = !empty($item['sub']) && isset($subMenus[$item['url']]);
            $isOpen     = $isActive && $hasSub;
        ?>

        <?php if ($hasSub): ?>
        <!-- Ítem con submenú -->
        <div class="sb-group <?= $isOpen ? 'open' : '' ?>" id="sbg-<?= $item['url'] ?>">
            <div class="sb-group-toggle <?= $isActive ? 'active' : '' ?>"
                 onclick="sbGroupToggle('<?= $item['url'] ?>')"
                 data-tip="<?= $item['label'] ?>">
                <i class="ti <?= $item['icon'] ?> main-icon"></i>
                <span class="sb-group-label"><?= $item['label'] ?></span>
                <i class="ti ti-chevron-right sb-group-arrow"></i>
            </div>
            <!-- Flyout para modo mini -->
            <div class="sb-sub-flyout">
                <a href="<?= APP_URL ?><?= $item['url'] ?>/"
                   class="sb-sub-item" style="font-weight:700;margin-bottom:4px;">
                    <i class="ti <?= $item['icon'] ?>"></i>
                    <span><?= $item['label'] ?></span>
                </a>
                <?php foreach ($subMenus[$item['url']] as $sub):
                    $subActive = $isActive && $activeTab === $sub['tab']; ?>
                <a href="<?= APP_URL ?><?= $item['url'] ?>/?tab=<?= $sub['tab'] ?>"
                   class="sb-sub-item <?= $subActive ? 'active' : '' ?>">
                    <i class="ti <?= $sub['icon'] ?>"></i>
                    <span><?= $sub['label'] ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <!-- Subítems colapsables -->
            <div class="sb-sub">
                <?php foreach ($subMenus[$item['url']] as $sub):
                    $subActive = $isActive && $activeTab === $sub['tab']; ?>
                <a href="<?= APP_URL ?><?= $item['url'] ?>/?tab=<?= $sub['tab'] ?>"
                   class="sb-sub-item <?= $subActive ? 'active' : '' ?>">
                    <i class="ti <?= $sub['icon'] ?>"></i>
                    <span class="sb-sub-label"><?= $sub['label'] ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- Ítem simple -->
        <a href="<?= APP_URL ?><?= $item['url'] ?>/"
           class="sb-item <?= $isActive ? 'active' : '' ?>"
           data-tip="<?= $item['label'] ?>">
            <i class="ti <?= $item['icon'] ?>"></i>
            <span class="sb-item-label"><?= $item['label'] ?></span>
        </a>
        <?php endif; ?>

        <?php endforeach; ?>

    </nav>

    <!-- Divider -->
    <div class="sb-divider"></div>

    <!-- Footer usuario -->
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-user-avatar">
                <?= mb_strtoupper(mb_substr($_SESSION['nombres'] ?? 'U', 0, 1)) . mb_strtoupper(mb_substr($_SESSION['apellidos'] ?? '', 0, 1)) ?>
            </div>
            <div class="sb-user-info">
                <div class="sb-user-name"><?= $userName ?></div>
                <div class="sb-user-id"><?= $userIdentificacion ?></div>
            </div>
            <div class="sb-user-actions">
                <a href="<?= APP_URL ?>logout/" class="sb-user-btn" title="Cerrar sesión">
                    <i class="ti ti-logout"></i>
                </a>
            </div>
        </div>
    </div>

</aside>

<?php
// ── Notificaciones — calculadas en PHP para mostrar en el panel ───────────────
$sbNotifs = [];
$idempleadoSb = (int)($_SESSION['idempleado'] ?? 0);

try {
    $connSb = (new app\models\mainModel)->conectar();

    // 1. Tiene autoevaluación pendiente?
    if ($idempleadoSb) {
        $sqlSb = "SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMRESPUESTA
                  WHERE IDEMPLEADO = $idempleadoSb AND TIPO_EVAL = 'AUTO'
                    AND CONFIRMADO = 1 AND IDPERIODO = (
                        SELECT IDPERIODO FROM VAADINWEB.HUMPERIODOEVALUACION
                        WHERE ESTADO = 1 AND ROWNUM = 1)";
        $qSb = oci_parse($connSb, $sqlSb);
        if ($qSb && oci_execute($qSb)) {
            $rSb = oci_fetch_assoc($qSb);
            if ((int)($rSb['CNT'] ?? 0) === 0) {
                $sbNotifs[] = ['type'=>'warn','icon'=>'ti-clipboard-check',
                    'title'=>'Autoevaluación pendiente',
                    'desc'=>'Aún no has completado tu autoevaluación del período activo.',
                    'url'=>'evaluarList'];
            }
            oci_free_statement($qSb);
        }
    }

    // 2. Tiene feedback pendiente de firmar (Proceso 1)?
    if ($idempleadoSb) {
        $sqlSb2 = "SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMFEEDBACK
                   WHERE IDEMPLEADO = $idempleadoSb AND TIPO_FEEDBACK = 1
                     AND FIRMADO_COLAB = 0 AND IDPERIODO = (
                         SELECT IDPERIODO FROM VAADINWEB.HUMPERIODOEVALUACION
                         WHERE ESTADO = 1 AND ROWNUM = 1)";
        $qSb2 = oci_parse($connSb, $sqlSb2);
        if ($qSb2 && oci_execute($qSb2)) {
            $rSb2 = oci_fetch_assoc($qSb2);
            if ((int)($rSb2['CNT'] ?? 0) > 0) {
                $sbNotifs[] = ['type'=>'alert','icon'=>'ti-signature',
                    'title'=>'Feedback pendiente de firma',
                    'desc'=>'Tu líder te ha dejado feedback. Fírmalo para activar tu plan de mejora.',
                    'url'=>'reportes'];
            }
            oci_free_statement($qSb2);
        }
    }

    // 3. Es líder funcional? Tiene colaboradores sin evaluar?
    $sqlLfSb = "SELECT ES_LIDER_FUNCIONAL FROM VAADINWEB.HUMEMPLEADOEVAL
                WHERE IDEMPLEADO = $idempleadoSb AND ACTIVO = 1 AND ROWNUM = 1";
    $qLfSb = oci_parse($connSb, $sqlLfSb);
    if ($qLfSb && oci_execute($qLfSb)) {
        $rLf = oci_fetch_assoc($qLfSb);
        oci_free_statement($qLfSb);
        if ($rLf && (int)$rLf['ES_LIDER_FUNCIONAL'] === 1) {
            $sqlPend = "SELECT COUNT(*) AS CNT
                        FROM VAADINWEB.HUMEMPLEADOEVAL HE
                        WHERE HE.IDEMPLEADO_EVAL = $idempleadoSb AND HE.ACTIVO = 1
                          AND HE.IDEMPLEADO NOT IN (
                              SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                              WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND CONFIRMADO = 1
                                AND IDEMPLEADO_EVAL = $idempleadoSb
                                AND IDPERIODO = (SELECT IDPERIODO FROM VAADINWEB.HUMPERIODOEVALUACION
                                                 WHERE ESTADO = 1 AND ROWNUM = 1))";
            $qPend = oci_parse($connSb, $sqlPend);
            if ($qPend && oci_execute($qPend)) {
                $rPend = oci_fetch_assoc($qPend);
                $cntPend = (int)($rPend['CNT'] ?? 0);
                if ($cntPend > 0) {
                    $sbNotifs[] = ['type'=>'info','icon'=>'ti-users',
                        'title'=>"$cntPend colaborador" . ($cntPend > 1 ? 'es' : '') . " sin evaluar",
                        'desc'=>'Tienes evaluaciones de colaboradores pendientes en el período activo.',
                        'url'=>'evaluarList'];
                }
                oci_free_statement($qPend);
            }

            // 4. Tiene feedback sin registrar a algún colaborador?
            $sqlFbPend = "SELECT COUNT(*) AS CNT
                          FROM VAADINWEB.HUMEMPLEADOEVAL HE
                          WHERE HE.IDEMPLEADO_EVAL = $idempleadoSb AND HE.ACTIVO = 1
                            AND HE.IDEMPLEADO NOT IN (
                                SELECT IDEMPLEADO FROM VAADINWEB.HUMFEEDBACK
                                WHERE IDEMPLEADO_LIDER = $idempleadoSb AND TIPO_FEEDBACK = 1
                                  AND IDPERIODO = (SELECT IDPERIODO FROM VAADINWEB.HUMPERIODOEVALUACION
                                                   WHERE ESTADO = 1 AND ROWNUM = 1))";
            $qFbPend = oci_parse($connSb, $sqlFbPend);
            if ($qFbPend && oci_execute($qFbPend)) {
                $rFbPend = oci_fetch_assoc($qFbPend);
                if ((int)($rFbPend['CNT'] ?? 0) > 0) {
                    $sbNotifs[] = ['type'=>'warn','icon'=>'ti-message-circle',
                        'title'=>'Feedback pendiente por registrar',
                        'desc'=>'Hay colaboradores que aún no han recibido su reunión de feedback.',
                        'url'=>'feedback'];
                }
                oci_free_statement($qFbPend);
            }
        }
    }
} catch (Exception $eSb) { /* silencioso */ }
$sbCount = count($sbNotifs);
?>

<!-- ── PANEL DE NOTIFICACIONES ───────────────────────────────────────────── -->
<div id="sb-notif-panel">
    <div class="sb-notif-head">
        <div class="sb-notif-head-left">
            <i class="ti ti-bell" style="color:#fff;font-size:16px;"></i>
            <span>Notificaciones</span>
            <span class="sb-notif-badge <?= $sbCount > 0 ? 'has-items' : '' ?>"><?= $sbCount ?></span>
        </div>
        <button onclick="sbNotifToggle()">✕</button>
    </div>
    <div class="sb-notif-body">
        <?php if (empty($sbNotifs)): ?>
        <div class="sb-notif-empty">
            <i class="ti ti-bell-off"></i>
            <p>Sin notificaciones pendientes</p>
        </div>
        <?php else: ?>
        <?php foreach ($sbNotifs as $n): ?>
        <a class="sb-notif-item" href="<?= APP_URL ?><?= $n['url'] ?>/" onclick="sbNotifToggle()">
            <div class="sb-notif-dot <?= $n['type'] ?>">
                <i class="ti <?= $n['icon'] ?>"></i>
            </div>
            <div class="sb-notif-content">
                <h6><?= $n['title'] ?></h6>
                <p><?= $n['desc'] ?></p>
            </div>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ── BOTONES FLOTANTES ──────────────────────────────────────────────────── -->
<div class="sb-float-actions">
    <button class="sb-float-btn" onclick="sbNotifToggle()" title="Notificaciones" id="sb-notif-btn">
        <i class="ti ti-bell"></i>
        <span class="notif-dot <?= $sbCount > 0 ? 'visible' : '' ?>"></span>
    </button>
    <button class="sb-float-btn hs-dark-mode-active:hidden hs-dark-mode" data-hs-theme-click-value="dark" title="Modo oscuro">
        <i class="ti ti-moon"></i>
    </button>
    <button class="sb-float-btn hs-dark-mode-active:block hidden hs-dark-mode" data-hs-theme-click-value="light" title="Modo claro">
        <i class="ti ti-sun"></i>
    </button>
  <!--  <a class="sb-float-btn"
       href="/GestionHumana/documentacion/<?= $isAdmin ? 'index.php' : 'docs/manual-usuario.php' ?>"
       target="_blank"
       title="<?= $isAdmin ? 'Portal de Documentación' : 'Manual de Usuario' ?>"
       aria-label="<?= $isAdmin ? 'Abrir portal de documentación' : 'Abrir Manual de Usuario' ?>">
        <i class="ti ti-book-2"></i>
    </a>-->
</div>

<script>
(function() {
    var MINI_KEY = 'zayma_sb_mini';

    // Restaurar estado guardado
    var isMini = localStorage.getItem(MINI_KEY) === '1';
    var sb      = document.getElementById('zayma-sidebar');
    var wrapper = document.getElementById('main-wrapper');

    function applyState() {
        var toggleBtn = document.getElementById('sb-toggle-btn');
        if (isMini) {
            sb.classList.add('mini');
            wrapper && wrapper.classList.add('sb-mini');
            if (toggleBtn) toggleBtn.querySelector('span').textContent = '»';
        } else {
            sb.classList.remove('mini');
            wrapper && wrapper.classList.remove('sb-mini');
            if (toggleBtn) toggleBtn.querySelector('span').textContent = '«';
        }
    }
    applyState();

    window.sbToggle = function() {
        isMini = !isMini;
        localStorage.setItem(MINI_KEY, isMini ? '1' : '0');
        applyState();
    };

    window.sbMobileToggle = function() {
        sb.classList.toggle('mobile-open');
        document.getElementById('sb-overlay').classList.toggle('open');
    };

    // Marcar sección activa por URL real
    var path = window.location.href;
    document.querySelectorAll('.sb-item').forEach(function(a) {
        if (a.href && path.indexOf(a.getAttribute('href')) > -1) {
            a.classList.add('active');
        }
    });

    // Submenús
    window.sbGroupToggle = function(id) {
        var group = document.getElementById('sbg-' + id);
        if (!group) return;
        // En modo mini no colapsar — se maneja con CSS hover
        if (document.getElementById('zayma-sidebar').classList.contains('mini')) return;
        group.classList.toggle('open');
    };

    // Notificaciones
    window.sbNotifToggle = function() {
        var panel = document.getElementById('sb-notif-panel');
        panel.classList.toggle('open');
    };

    // Cerrar notificaciones al hacer click fuera
    document.addEventListener('click', function(e) {
        var panel = document.getElementById('sb-notif-panel');
        var btn   = document.getElementById('sb-notif-btn');
        if (panel && !panel.contains(e.target) && btn && !btn.contains(e.target)) {
            panel.classList.remove('open');
        }
    });
})();
</script>
