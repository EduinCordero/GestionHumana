<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('session');
    session_set_cookie_params(['lifetime'=>3600,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
if (empty($_SESSION['identificacion'])) {
    header('Location: /GestionHumana/');
    exit;
}
$isAdmin = (int)($_SESSION['esadmin'] ?? 0) === 1;
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manual de Usuario — Evaluación de Desempeño</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="../assets/css/docs.css">
</head>
<body
    data-md-file="../../MANUAL_USUARIO.md"
    data-doc-title="Manual de Usuario">

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

            <?php if ($isAdmin): ?>
            <div class="sb-section">Documentos</div>
            <a href="../index.php" class="sb-link" title="Portal principal">
                <i class="ti ti-home"></i><span>Portal principal</span>
            </a>
            <a href="./arquitectura.php" class="sb-link" title="Arquitectura">
                <i class="ti ti-topology-star-3"></i><span>Arquitectura</span>
            </a>
            <a href="./manual-tecnico.php" class="sb-link" title="Manual Técnico">
                <i class="ti ti-code"></i><span>Manual Técnico</span>
            </a>
            <a href="./manual-usuario.php" class="sb-link" title="Manual de Usuario">
                <i class="ti ti-user-check"></i><span>Manual de Usuario</span>
            </a>
            <a href="./flujos-sistema.php" class="sb-link" title="Flujos del Sistema">
                <i class="ti ti-git-branch"></i><span>Flujos del Sistema</span>
            </a>
            <div class="sb-section" style="margin-top:16px;">En este documento</div>
            <?php else: ?>
            <div class="sb-section">Contenido</div>
            <?php endif; ?>

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
                <?php if ($isAdmin): ?>
                <a href="../index.php">Docs</a>
                <i class="ti ti-chevron-right"></i>
                <?php endif; ?>
                <span class="tb-current">Manual de Usuario</span>
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
                    <span>Cargando Manual de Usuario…</span>
                </div>
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
