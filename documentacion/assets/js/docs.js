/**
 * GestionHumana — Portal de Documentación
 * docs.js · v1.0
 *
 * Responsabilidades:
 * - Carga y renderizado de Markdown (marked.js)
 * - Renderizado de diagramas Mermaid
 * - Generación automática de TOC
 * - Buscador en página
 * - Modo oscuro
 * - Sidebar toggle (desktop + mobile)
 * - Scroll to top
 * - Copy code blocks
 * - Secciones colapsables (H2)
 */
(function () {
    'use strict';

    /* ─────────────────────────────────────────────────
       Estado global
    ───────────────────────────────────────────────── */
    const DOC_TITLE = document.body.dataset.docTitle || 'Documentación';
    const MD_FILE   = document.body.dataset.mdFile   || null;

    /* ─────────────────────────────────────────────────
       Inicialización
    ───────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        initTheme();
        initSidebar();
        initScrollTop();
        initKeyboard();

        if (MD_FILE) {
            loadMarkdown(MD_FILE);
        }
    });

    /* ─────────────────────────────────────────────────
       TEMA (Light / Dark)
    ───────────────────────────────────────────────── */
    function initTheme() {
        var saved = localStorage.getItem('docs_theme') || 'light';
        applyTheme(saved);

        var btn = document.getElementById('theme-toggle');
        if (btn) {
            btn.addEventListener('click', function () {
                var current = document.documentElement.getAttribute('data-theme') || 'light';
                var next    = current === 'dark' ? 'light' : 'dark';
                applyTheme(next);
                localStorage.setItem('docs_theme', next);
            });
        }
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        var icon = document.querySelector('#theme-toggle i');
        if (icon) {
            icon.className = theme === 'dark' ? 'ti ti-sun' : 'ti ti-moon';
        }
    }

    /* ─────────────────────────────────────────────────
       SIDEBAR
    ───────────────────────────────────────────────── */
    function initSidebar() {
        var sidebar  = document.getElementById('docs-sidebar');
        var main     = document.getElementById('docs-main');
        var toggle   = document.getElementById('sb-toggle');
        var overlay  = document.getElementById('sb-overlay');

        if (!sidebar) return;

        // Restaurar estado desktop
        var isMobile = window.innerWidth <= 768;
        if (!isMobile) {
            var collapsed = localStorage.getItem('docs_sb') === 'collapsed';
            if (collapsed) {
                sidebar.classList.add('collapsed');
                if (main) main.classList.add('sidebar-collapsed');
            }
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('mobile-open');
                    overlay && overlay.classList.toggle('open');
                } else {
                    var c = sidebar.classList.toggle('collapsed');
                    main  && main.classList.toggle('sidebar-collapsed', c);
                    localStorage.setItem('docs_sb', c ? 'collapsed' : 'open');
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function () {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('open');
            });
        }

        // Highlight active link — comparar pathname resuelto, no el atributo crudo
        var links = sidebar.querySelectorAll('.sb-link');
        var currentPath = window.location.pathname;
        links.forEach(function (link) {
            if (link.href && link.pathname && link.pathname === currentPath) {
                link.classList.add('active');
            }
        });
    }

    /* ─────────────────────────────────────────────────
       CARGAR Y RENDERIZAR MARKDOWN
    ───────────────────────────────────────────────── */
    function loadMarkdown(filePath) {
        var container = document.getElementById('doc-content');
        if (!container) return;

        container.innerHTML = '<div class="docs-loading"><div class="spinner"></div><span>Cargando documentación...</span></div>';

        fetch(filePath)
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.text();
            })
            .then(function (md) {
                renderMarkdown(md, container);
            })
            .catch(function (err) {
                container.innerHTML = '<div class="docs-loading"><i class="ti ti-alert-circle" style="font-size:40px;color:#ef4444"></i><p style="color:var(--text-muted)">No se pudo cargar el archivo de documentación.<br><small>' + err.message + '</small></p></div>';
            });
    }

    function renderMarkdown(md, container) {
        // 1. Extraer bloques mermaid y reemplazar con tags HTML que marked.js preserva intactos.
        //    IMPORTANTE: NO usar __PLACEHOLDER__ porque marked los convierte en <strong>
        //    al interpretar el doble guión bajo como negrita de Markdown.
        var mermaidCodes = [];
        var processed = md.replace(/```mermaid\n([\s\S]*?)```/g, function (_, code) {
            var idx = mermaidCodes.length;
            mermaidCodes.push(code.trim());
            // El tag <div data-mmph> es HTML válido: marked lo pasa intacto al output
            return '\n\n<div class="mmph" data-mi="' + idx + '"></div>\n\n';
        });

        // 2. Renderizar Markdown con marked
        if (typeof marked !== 'undefined') {
            marked.setOptions({ breaks: true, gfm: true, headerIds: true, mangle: false });
            var html = marked.parse(processed);
        } else {
            var html = '<pre>' + escapeHtml(processed) + '</pre>';
        }

        // 3. Inyectar HTML en el DOM
        container.innerHTML = html;

        // 4. Reemplazar placeholders usando DOM (nunca string-replace):
        //    querySelectorAll encuentra los <div class="mmph"> y los sustituye
        //    por el wrapper real de Mermaid usando textContent (evita doble escape)
        container.querySelectorAll('.mmph').forEach(function (ph) {
            var idx  = parseInt(ph.getAttribute('data-mi'), 10);
            var code = mermaidCodes[idx];
            if (code === undefined) { ph.remove(); return; }

            var wrapper = document.createElement('div');
            wrapper.className = 'mermaid-wrapper';

            var label = document.createElement('div');
            label.className = 'mermaid-label';
            label.innerHTML = '<i class="ti ti-topology-star-3" style="vertical-align:middle;margin-right:4px;"></i>Diagrama';

            var mermaidDiv = document.createElement('div');
            mermaidDiv.className = 'mermaid';
            // textContent asigna el texto plano sin escapar entidades HTML
            mermaidDiv.textContent = code;

            wrapper.appendChild(label);
            wrapper.appendChild(mermaidDiv);
            ph.parentNode.replaceChild(wrapper, ph);
        });

        // 5. Post-procesamiento (anchors, blockquotes, tablas, colapsables)
        postProcessContent(container);

        // 6. Inicializar Mermaid (el contenido ya está en textContent, sin escape)
        initMermaid();

        // 7. Construir TOC
        buildTOC(container);

        // 8. Inicializar búsqueda
        initSearch(container);

        // 9. Copy buttons en bloques de código
        initCodeBlocks(container);

        // 10. Scrollspy para TOC
        initScrollSpy();
    }

    /* ─────────────────────────────────────────────────
       POST-PROCESAMIENTO DEL CONTENIDO
    ───────────────────────────────────────────────── */
    function postProcessContent(container) {
        // Agregar anchor-links a H2
        container.querySelectorAll('h2').forEach(function (h2) {
            var id = h2.textContent
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .substring(0, 50);
            h2.id = id;

            var anchor = document.createElement('a');
            anchor.className = 'anchor-link';
            anchor.href = '#' + id;
            anchor.title = 'Enlace directo';
            anchor.innerHTML = '<i class="ti ti-link"></i>';
            h2.appendChild(anchor);
        });

        // Agregar IDs a H3
        container.querySelectorAll('h3').forEach(function (h3) {
            var id = 'sub-' + h3.textContent
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .substring(0, 40);
            h3.id = id;
        });

        // Transformar blockquotes con emojis / ⚠
        container.querySelectorAll('blockquote').forEach(function (bq) {
            var text = bq.innerHTML;
            var cardClass = 'info';
            var icon = '<i class="ti ti-info-circle" style="color:#2563eb"></i>';

            if (text.includes('⚠') || text.includes('IMPORTANTE') || text.includes('Nota:') || text.includes('⚠')) {
                cardClass = 'warning';
                icon = '<i class="ti ti-alert-triangle" style="color:#d97706"></i>';
                text = text.replace(/⚠\s*/g, '');
            } else if (text.includes('❌') || text.includes('Error') || text.includes('Crítico')) {
                cardClass = 'danger';
                icon = '<i class="ti ti-circle-x" style="color:#dc2626"></i>';
            } else if (text.includes('✅') || text.includes('Correcto') || text.includes('Éxito')) {
                cardClass = 'success';
                icon = '<i class="ti ti-circle-check" style="color:#16a34a"></i>';
            }

            var card = document.createElement('div');
            card.className = 'info-card ' + cardClass;
            card.innerHTML = '<span class="info-card-icon">' + icon + '</span><div class="info-card-body">' + bq.innerHTML + '</div>';
            bq.parentNode.replaceChild(card, bq);
        });

        // Agregar separador visual después del primer párrafo si hay H1
        var h1 = container.querySelector('h1');
        if (h1) {
            var nextSibling = h1.nextElementSibling;
            if (nextSibling && nextSibling.tagName === 'BLOCKQUOTE') {
                // Ya tiene metadata → OK
            }
        }

        // Tablas: hacer scroll horizontal en móvil
        container.querySelectorAll('table').forEach(function (table) {
            if (!table.parentElement.classList.contains('table-wrap')) {
                var wrap = document.createElement('div');
                wrap.style.overflowX = 'auto';
                wrap.style.webkitOverflowScrolling = 'touch';
                table.parentNode.insertBefore(wrap, table);
                wrap.appendChild(table);
            }
        });

        // Hacer H2 colapsables
        container.querySelectorAll('h2').forEach(function (h2) {
            h2.classList.add('collapsible-header');

            var icon = document.createElement('i');
            icon.className = 'ti ti-chevron-down collapse-icon';
            h2.appendChild(icon);

            // Recopilar elementos hasta el siguiente H2
            var siblings = [];
            var el = h2.nextElementSibling;
            while (el && el.tagName !== 'H2') {
                siblings.push(el);
                el = el.nextElementSibling;
            }

            if (siblings.length > 0) {
                var wrapper = document.createElement('div');
                wrapper.className = 'collapsible-content';
                h2.parentNode.insertBefore(wrapper, siblings[0]);
                siblings.forEach(function (s) { wrapper.appendChild(s); });

                h2.addEventListener('click', function () {
                    h2.classList.toggle('collapsed');
                    wrapper.classList.toggle('hidden');
                });
            }
        });
    }

    /* ─────────────────────────────────────────────────
       MERMAID
    ───────────────────────────────────────────────── */
    function initMermaid() {
        if (typeof mermaid === 'undefined') return;

        // El contenido fue asignado con textContent desde renderMarkdown(),
        // así que ya está en texto plano. No se necesita decodificar HTML entities.
        // Solo verificar que los divs .mermaid tengan contenido real.
        var hasDiagrams = document.querySelectorAll('.mermaid').length > 0;
        if (!hasDiagrams) return;

        var theme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'default';

        mermaid.initialize({
            startOnLoad: false,
            theme: theme,
            themeVariables: {
                primaryColor:       '#0058af',
                primaryTextColor:   '#ffffff',
                primaryBorderColor: '#003d82',
                lineColor:          '#64748b',
                secondaryColor:     '#dbeafe',
                tertiaryColor:      '#f8fafc',
                fontSize:           '13px',
            },
            flowchart: { curve: 'basis', padding: 20 },
            sequence:  { actorMargin: 50 },
        });

        try {
            mermaid.run({ querySelector: '.mermaid' });
        } catch (e) {
            console.warn('Mermaid error:', e);
        }
    }

    /* ─────────────────────────────────────────────────
       TABLE OF CONTENTS
    ───────────────────────────────────────────────── */
    function buildTOC(container) {
        var tocContainer = document.getElementById('sb-toc');
        if (!tocContainer) return;

        var headings = container.querySelectorAll('h2, h3');
        if (headings.length === 0) { tocContainer.innerHTML = '<span style="font-size:11px;color:var(--sidebar-dim);padding:6px 10px;display:block">Sin secciones</span>'; return; }

        var html = '';
        headings.forEach(function (h) {
            var isH3  = h.tagName === 'H3';
            var id    = h.id || '';
            var label = h.textContent.replace(/«|»|#/g, '').trim();
            // Limpiar ícono de collapse si lo hay
            label = label.replace(/\s*$/, '').replace(/[←-⇿]/g, '').trim();
            html += '<a href="#' + id + '" class="sb-toc-item ' + (isH3 ? 'h3' : '') + '" data-id="' + id + '">' + escapeHtml(label) + '</a>';
        });

        tocContainer.innerHTML = html;

        // Click suave en TOC
        tocContainer.querySelectorAll('.sb-toc-item').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var target = document.getElementById(link.dataset.id);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                // En móvil: cerrar sidebar
                if (window.innerWidth <= 768) {
                    var sb = document.getElementById('docs-sidebar');
                    var ov = document.getElementById('sb-overlay');
                    if (sb) sb.classList.remove('mobile-open');
                    if (ov) ov.classList.remove('open');
                }
            });
        });
    }

    /* ─────────────────────────────────────────────────
       SCROLLSPY
    ───────────────────────────────────────────────── */
    function initScrollSpy() {
        var tocLinks = document.querySelectorAll('.sb-toc-item[data-id]');
        if (tocLinks.length === 0) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var id = entry.target.id;
                    tocLinks.forEach(function (l) {
                        l.classList.toggle('active-toc', l.dataset.id === id);
                    });
                }
            });
        }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });

        document.querySelectorAll('h2[id], h3[id]').forEach(function (h) {
            observer.observe(h);
        });
    }

    /* ─────────────────────────────────────────────────
       BÚSQUEDA EN PÁGINA
    ───────────────────────────────────────────────── */
    function initSearch(container) {
        var searchBtn    = document.getElementById('search-toggle');
        var searchInput  = document.getElementById('tb-search-input');
        var searchIcon   = document.querySelector('.tb-search-icon');
        var resultsBox   = document.getElementById('search-results');

        if (!searchBtn || !searchInput) return;

        // Indexar texto del contenido
        var index = [];
        (container || document).querySelectorAll('h2, h3, p, li, td').forEach(function (el) {
            var text = el.textContent.trim();
            if (text.length < 5) return;
            var headingEl = el.closest ? el.closest('h2, h3') : null;
            if (el.tagName === 'H2' || el.tagName === 'H3') {
                index.push({ title: text, context: '', id: el.id || '', type: 'heading' });
            } else {
                var heading = '';
                var prev = el.previousElementSibling;
                while (prev) {
                    if (prev.tagName === 'H2' || prev.tagName === 'H3') { heading = prev.textContent.trim(); break; }
                    prev = prev.previousElementSibling;
                }
                index.push({ title: heading || DOC_TITLE, context: text.substring(0, 120), id: '', type: 'text' });
            }
        });

        searchBtn.addEventListener('click', function () {
            searchInput.classList.toggle('open');
            if (searchIcon) searchIcon.classList.toggle('show');
            if (searchInput.classList.contains('open')) {
                setTimeout(function () { searchInput.focus(); }, 150);
            } else {
                clearSearch(resultsBox);
            }
        });

        searchInput.addEventListener('input', function () {
            var q = searchInput.value.trim().toLowerCase();
            if (q.length < 2) { clearSearch(resultsBox); return; }
            var matches = index.filter(function (item) {
                return item.title.toLowerCase().includes(q) || item.context.toLowerCase().includes(q);
            }).slice(0, 8);
            renderSearchResults(matches, q, resultsBox);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchInput.classList.remove('open');
                if (searchIcon) searchIcon.classList.remove('show');
                clearSearch(resultsBox);
            }
        });

        document.addEventListener('click', function (e) {
            if (resultsBox && !resultsBox.contains(e.target) && e.target !== searchInput && e.target !== searchBtn) {
                clearSearch(resultsBox);
            }
        });
    }

    function renderSearchResults(matches, query, box) {
        if (!box) return;
        if (matches.length === 0) {
            box.innerHTML = '<div class="sr-empty"><i class="ti ti-search-off" style="font-size:28px;display:block;margin-bottom:8px;color:var(--border)"></i>Sin resultados para "' + escapeHtml(query) + '"</div>';
            box.classList.add('visible');
            return;
        }
        var html = '<div class="sr-header">Resultados (' + matches.length + ')</div>';
        matches.forEach(function (item) {
            var titleHL = highlight(item.title, query);
            var ctxHL   = item.context ? highlight(item.context.substring(0, 100), query) : '';
            html += '<div class="sr-item" data-id="' + escapeHtml(item.id) + '">';
            html += '<h6>' + titleHL + '</h6>';
            if (ctxHL) html += '<p>' + ctxHL + '…</p>';
            html += '</div>';
        });
        box.innerHTML = html;
        box.classList.add('visible');

        box.querySelectorAll('.sr-item').forEach(function (item) {
            item.addEventListener('click', function () {
                var id = item.dataset.id;
                if (id) {
                    var target = document.getElementById(id);
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                clearSearch(box);
            });
        });
    }

    function clearSearch(box) {
        if (box) { box.innerHTML = ''; box.classList.remove('visible'); }
    }

    function highlight(text, query) {
        var escaped = escapeHtml(text);
        var re = new RegExp('(' + escapeRegex(query) + ')', 'gi');
        return escaped.replace(re, '<mark>$1</mark>');
    }

    /* ─────────────────────────────────────────────────
       CODE BLOCKS — COPY BUTTON
    ───────────────────────────────────────────────── */
    function initCodeBlocks(container) {
        (container || document).querySelectorAll('pre').forEach(function (pre) {
            var code = pre.querySelector('code');
            if (!code) return;

            // Detectar lenguaje
            var lang = 'code';
            code.className.split(' ').forEach(function (cls) {
                if (cls.startsWith('language-')) lang = cls.replace('language-', '');
            });
            if (lang === 'plaintext' || lang === 'code') lang = 'código';

            var header = document.createElement('div');
            header.className = 'pre-header';
            header.innerHTML = '<span>' + escapeHtml(lang) + '</span>';

            var copyBtn = document.createElement('button');
            copyBtn.className = 'copy-btn';
            copyBtn.innerHTML = '<i class="ti ti-copy"></i> Copiar';
            copyBtn.addEventListener('click', function () {
                navigator.clipboard.writeText(code.textContent).then(function () {
                    copyBtn.innerHTML = '<i class="ti ti-check"></i> Copiado';
                    setTimeout(function () { copyBtn.innerHTML = '<i class="ti ti-copy"></i> Copiar'; }, 2000);
                });
            });
            header.appendChild(copyBtn);
            pre.insertBefore(header, pre.firstChild);
        });
    }

    /* ─────────────────────────────────────────────────
       SCROLL TO TOP
    ───────────────────────────────────────────────── */
    function initScrollTop() {
        var btn = document.getElementById('scroll-top');
        if (!btn) return;
        window.addEventListener('scroll', function () {
            btn.classList.toggle('visible', window.scrollY > 400);
        });
        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ─────────────────────────────────────────────────
       KEYBOARD SHORTCUTS
    ───────────────────────────────────────────────── */
    function initKeyboard() {
        document.addEventListener('keydown', function (e) {
            // Ctrl+K → abrir búsqueda
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                var searchBtn = document.getElementById('search-toggle');
                if (searchBtn) searchBtn.click();
            }
            // Ctrl+D → toggle dark mode
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                var themeBtn = document.getElementById('theme-toggle');
                if (themeBtn) themeBtn.click();
            }
        });
    }

    /* ─────────────────────────────────────────────────
       UTILIDADES
    ───────────────────────────────────────────────── */
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /* ─────────────────────────────────────────────────
       EXPORT para uso en consola (debug)
    ───────────────────────────────────────────────── */
    window.DocsPortal = {
        reload: function () { if (MD_FILE) loadMarkdown(MD_FILE); },
        toggleTheme: function () { document.getElementById('theme-toggle') && document.getElementById('theme-toggle').click(); }
    };

})();
