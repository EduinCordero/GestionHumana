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
<title>Portal de Documentación — Evaluación de Desempeño</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="./assets/css/docs.css">
<style>
body { background: var(--bg); }
.index-main { min-height: 100vh; display: flex; flex-direction: column; }
.index-content { flex: 1; }
</style>
</head>
<body>

<div class="index-main">

    <!-- TOP BAR -->
    <div class="index-topbar">
        <div class="index-topbar-brand">
            <div class="brand-icon"><i class="ti ti-heart-rate-monitor"></i></div>
            <span class="brand-name">Evaluación de Desempeño</span>
            <span style="font-size:11px;color:rgba(255,255,255,0.4);margin-left:8px;">· Portal de Documentación</span>
        </div>
        <a href="/GestionHumana/" class="index-topbar-btn" title="Ir al sistema">
            <i class="ti ti-external-link"></i>
        </a>
        <button class="index-topbar-btn" id="theme-toggle" title="Modo oscuro (Ctrl+D)">
            <i class="ti ti-moon"></i>
        </button>
    </div>

    <div class="index-content">

        <!-- HERO -->
        <div class="index-hero">
            <div class="hero-badge"><i class="ti ti-books"></i> Documentación Técnica V2</div>
            <h1 class="hero-title">Portal de Documentación<br>Evaluación de Desempeño</h1>
            <p class="hero-sub">Documentación técnica y funcional del sistema de Evaluación de Desempeño y Cultura · Clínica Zayma SAS</p>
        </div>

        <!-- BODY -->
        <div class="index-body">

            <!-- STATS -->
            <div class="section-title">Resumen del sistema</div>
            <div class="stats-row" style="margin-bottom:32px;">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="ti ti-cpu"></i></div>
                    <div>
                        <div class="stat-label">Controladores</div>
                        <div class="stat-value">6</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ti ti-database"></i></div>
                    <div>
                        <div class="stat-label">Modelos</div>
                        <div class="stat-value">8</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ti ti-layout"></i></div>
                    <div>
                        <div class="stat-label">Vistas</div>
                        <div class="stat-value">14</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fce7f3;color:#be185d;"><i class="ti ti-table"></i></div>
                    <div>
                        <div class="stat-label">Tablas Oracle</div>
                        <div class="stat-value">15+</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="ti ti-clipboard-check"></i></div>
                    <div>
                        <div class="stat-label">Tipos de eval.</div>
                        <div class="stat-value">5</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ffedd5;color:#c2410c;"><i class="ti ti-users"></i></div>
                    <div>
                        <div class="stat-label">Perfiles usuario</div>
                        <div class="stat-value">4</div>
                    </div>
                </div>
            </div>

            <!-- DOCUMENTS -->
            <div class="section-title">Documentos disponibles</div>
            <div class="doc-cards-grid">

                <!-- Arquitectura -->
                <a href="./docs/arquitectura.php" class="doc-card" style="--card-accent:#0058af;">
                    <div class="doc-card-icon" style="background:#dbeafe;color:#1d4ed8;">
                        <i class="ti ti-topology-star-3"></i>
                    </div>
                    <h3>Arquitectura del Sistema</h3>
                    <p>Visión general de la arquitectura MVC, flujo de enrutamiento, capa de base de datos Oracle, esquemas, tablas y deuda técnica identificada.</p>
                    <div class="doc-card-footer">
                        <span class="doc-card-tag">Arquitectura · BD</span>
                        <i class="ti ti-arrow-right doc-card-arrow"></i>
                    </div>
                </a>

                <!-- Manual Técnico -->
                <a href="./docs/manual-tecnico.php" class="doc-card" style="--card-accent:#7c3aed;">
                    <div class="doc-card-icon" style="background:#ede9fe;color:#7c3aed;">
                        <i class="ti ti-code"></i>
                    </div>
                    <h3>Manual Técnico</h3>
                    <p>Estructura de carpetas, flujo MVC detallado, modelos de dominio, sesiones y seguridad, consultas SQL clave, encoding UTF-8/Oracle y riesgos técnicos.</p>
                    <div class="doc-card-footer">
                        <span class="doc-card-tag">Desarrollo · SQL</span>
                        <i class="ti ti-arrow-right doc-card-arrow"></i>
                    </div>
                </a>

                <!-- Manual Usuario -->
                <a href="./docs/manual-usuario.php" class="doc-card" style="--card-accent:#16a34a;">
                    <div class="doc-card-icon" style="background:#dcfce7;color:#16a34a;">
                        <i class="ti ti-user-check"></i>
                    </div>
                    <h3>Manual de Usuario</h3>
                    <p>Guía completa para colaboradores, líderes y administradores. Navegación, módulos, formularios, flujos de uso y errores comunes con soluciones.</p>
                    <div class="doc-card-footer">
                        <span class="doc-card-tag">Usuario · Guía</span>
                        <i class="ti ti-arrow-right doc-card-arrow"></i>
                    </div>
                </a>

                <!-- Flujos del Sistema -->
                <a href="./docs/flujos-sistema.php" class="doc-card" style="--card-accent:#d97706;">
                    <div class="doc-card-icon" style="background:#fef3c7;color:#d97706;">
                        <i class="ti ti-git-branch"></i>
                    </div>
                    <h3>Flujos del Sistema</h3>
                    <p>13 diagramas Mermaid profesionales: arquitectura, autenticación, flujo MVC, evaluaciones, notificaciones, exportación CSV y seguridad de sesiones.</p>
                    <div class="doc-card-footer">
                        <span class="doc-card-tag">Diagramas · Flujos</span>
                        <i class="ti ti-arrow-right doc-card-arrow"></i>
                    </div>
                </a>

            </div>

            <!-- INFO ROW -->
            <div class="section-title" style="margin-top:8px;">Acceso rápido</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">

                <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;box-shadow:var(--shadow-sm);">
                    <div style="font-size:13px;font-weight:700;color:var(--heading);margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                        <i class="ti ti-link" style="color:var(--primary);font-size:16px;"></i>
                        Vistas del sistema
                    </div>
                    <div style="font-size:12.5px;color:var(--text-muted);line-height:2;">
                        <a href="/GestionHumana/?views=home">→ Inicio</a><br>
                        <a href="/GestionHumana/?views=evaluarList">→ Evaluaciones</a><br>
                        <a href="/GestionHumana/?views=reportes">→ Reportes</a><br>
                        <a href="/GestionHumana/?views=feedback">→ Feedback</a><br>
                        <a href="/GestionHumana/?views=admin">→ Administración</a>
                    </div>
                </div>

                <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;box-shadow:var(--shadow-sm);">
                    <div style="font-size:13px;font-weight:700;color:var(--heading);margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                        <i class="ti ti-keyboard" style="color:var(--primary);font-size:16px;"></i>
                        Atajos de teclado
                    </div>
                    <div style="font-size:12.5px;line-height:2.2;">
                        <div style="display:flex;justify-content:space-between;color:var(--text-muted);">
                            <span>Buscar en página</span>
                            <kbd style="background:var(--surface2);border:1px solid var(--border);border-radius:4px;padding:1px 6px;font-size:11px;color:var(--text);">Ctrl+K</kbd>
                        </div>
                        <div style="display:flex;justify-content:space-between;color:var(--text-muted);">
                            <span>Modo oscuro</span>
                            <kbd style="background:var(--surface2);border:1px solid var(--border);border-radius:4px;padding:1px 6px;font-size:11px;color:var(--text);">Ctrl+D</kbd>
                        </div>
                        <div style="display:flex;justify-content:space-between;color:var(--text-muted);">
                            <span>Colapsar sidebar</span>
                            <kbd style="background:var(--surface2);border:1px solid var(--border);border-radius:4px;padding:1px 6px;font-size:11px;color:var(--text);">☰</kbd>
                        </div>
                    </div>
                </div>

                <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;box-shadow:var(--shadow-sm);">
                    <div style="font-size:13px;font-weight:700;color:var(--heading);margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                        <i class="ti ti-info-circle" style="color:var(--primary);font-size:16px;"></i>
                        Información
                    </div>
                    <div style="font-size:12.5px;color:var(--text-muted);line-height:2.1;">
                        <div style="display:flex;align-items:center;gap:7px;"><i class="ti ti-calendar" style="font-size:14px;color:var(--primary);flex-shrink:0;"></i> Generado: <strong style="color:var(--text)">2026-05-14</strong></div>
                        <div style="display:flex;align-items:center;gap:7px;"><i class="ti ti-building-hospital" style="font-size:14px;color:var(--primary);flex-shrink:0;"></i> Organización: <strong style="color:var(--text)">Clínica Zayma SAS</strong></div>
                        <div style="display:flex;align-items:center;gap:7px;"><i class="ti ti-rocket" style="font-size:14px;color:var(--primary);flex-shrink:0;"></i> Versión: <strong style="color:var(--text)">Sistema V2</strong></div>
                        <div style="display:flex;align-items:center;gap:7px;"><i class="ti ti-database" style="font-size:14px;color:var(--primary);flex-shrink:0;"></i> Base de datos: <strong style="color:var(--text)">Oracle / VAADINWEB</strong></div>
                    </div>
                </div>

            </div>

        </div><!-- /index-body -->
    </div><!-- /index-content -->

    <!-- Footer -->
    <div style="padding:16px 40px;border-top:1px solid var(--border);font-size:11.5px;color:var(--text-muted);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
        <span>GestionHumana · Documentación Técnica · Clínica Zayma SAS</span>
        <span>Generado: 2026-05-14</span>
    </div>

</div><!-- /index-main -->

<script>
// Tema en index (sin docs.js completo)
(function(){
    var saved = localStorage.getItem('docs_theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    var btn = document.getElementById('theme-toggle');
    if(btn){
        var icon = btn.querySelector('i');
        if(icon) icon.className = saved==='dark'?'ti ti-sun':'ti ti-moon';
        btn.addEventListener('click',function(){
            var cur = document.documentElement.getAttribute('data-theme')||'light';
            var next = cur==='dark'?'light':'dark';
            document.documentElement.setAttribute('data-theme',next);
            localStorage.setItem('docs_theme',next);
            if(icon) icon.className = next==='dark'?'ti ti-sun':'ti ti-moon';
        });
    }
})();
</script>
</body>
</html>
