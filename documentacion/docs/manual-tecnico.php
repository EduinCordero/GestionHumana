<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('session');
    session_set_cookie_params(['lifetime'=>3600,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
if (empty($_SESSION['identificacion']) || (int)($_SESSION['esadmin'] ?? 0) !== 1) {
    header('Location: /GestionHumana/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manual Técnico — GestionHumana Docs</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="../assets/css/docs.css">
</head>
<body
    data-md-file="../../MANUAL_TECNICO.md"
    data-doc-title="Manual Técnico">

<div id="docs-layout">

    <div id="sb-overlay"></div>

    <aside id="docs-sidebar">
        <div class="sb-brand">
            <div class="sb-brand-icon"><i class="ti ti-heart-rate-monitor"></i></div>
            <div class="sb-brand-text">
                <strong>Evaluación de Desempeño</strong>
                <span>Documentación V2</span>
            </div>
        </div>

        <nav class="sb-nav">
            <div class="sb-section">Documentos</div>
            <a href="../index.php" class="sb-link"><i class="ti ti-home"></i><span>Portal principal</span></a>
            <a href="./arquitectura.php" class="sb-link"><i class="ti ti-topology-star-3"></i><span>Arquitectura</span></a>
            <a href="./manual-tecnico.php" class="sb-link"><i class="ti ti-code"></i><span>Manual Técnico</span></a>
            <a href="./manual-usuario.php" class="sb-link"><i class="ti ti-user-check"></i><span>Manual de Usuario</span></a>
            <a href="./flujos-sistema.php" class="sb-link"><i class="ti ti-git-branch"></i><span>Flujos del Sistema</span></a>

            <div class="sb-section" style="margin-top:16px;">En este documento</div>
            <div id="sb-toc" class="sb-toc"></div>
        </nav>

        <div class="sb-footer">
            <a href="/GestionHumana/" class="sb-back-btn">
                <i class="ti ti-arrow-left"></i> Volver al sistema
            </a>
        </div>
    </aside>

    <div id="docs-main">
        <div id="docs-topbar">
            <button class="tb-toggle" id="sb-toggle" title="Colapsar menú">
                <i class="ti ti-menu-2"></i>
            </button>
            <div class="tb-breadcrumb">
                <a href="../index.php">Docs</a>
                <i class="ti ti-chevron-right"></i>
                <span class="tb-current">Manual Técnico</span>
            </div>
            <div class="tb-actions">
                <div class="tb-search-wrap">
                    <button class="tb-btn" id="search-toggle" title="Buscar (Ctrl+K)">
                        <i class="ti ti-search"></i>
                    </button>
                    <input type="text" id="tb-search-input" placeholder="Buscar en el documento…" autocomplete="off">
                    <i class="ti ti-search tb-search-icon"></i>
                    <div id="search-results"></div>
                </div>
                <button class="tb-btn" id="theme-toggle" title="Modo oscuro (Ctrl+D)">
                    <i class="ti ti-moon"></i>
                </button>
                <button class="tb-btn" onclick="window.print()" title="Imprimir / Exportar PDF">
                    <i class="ti ti-printer"></i>
                </button>
            </div>
        </div>

        <div id="docs-content-wrap">
            <div id="doc-content">
                <div class="docs-loading">
                    <div class="spinner"></div>
                    <span>Cargando Manual Técnico…</span>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;margin-top:48px;padding-top:24px;border-top:1px solid var(--border);">
                <a href="./arquitectura.php" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:var(--surface);border:1px solid var(--border);color:var(--text);border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
                    <i class="ti ti-arrow-left"></i> Arquitectura
                </a>
                <a href="./manual-usuario.php" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:var(--primary);color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">
                    Manual de Usuario <i class="ti ti-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

</div>

<button id="scroll-top" title="Subir al inicio"><i class="ti ti-arrow-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/marked@9/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script src="../assets/js/docs.js"></script>
</body>
</html>
