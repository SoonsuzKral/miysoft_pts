<!DOCTYPE html>
<html lang="tr" data-theme="slate">
<head>
    @include('partials.head')
    <title>@yield('title', 'Dökümantasyon') — {{ config('app.name') }}</title>
    @stack('styles')
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ── Theme Variables ── */
        html[data-theme="slate"] {
            --bg-body: #0f172a;
            --bg-topbar: #1e293b;
            --bg-card: #1e293b;
            --bg-sidebar: #1e293b;
            --bg-input: #0f172a;
            --bg-flow: #0f172a;
            --bg-code: #0f172a;
            --bg-hover: #334155;
            --bg-active: #0f172a;
            --bg-overlay: rgba(0,0,0,.5);
            --border: #334155;
            --border-light: #475569;
            --text-primary: #f1f5f9;
            --text-secondary: #e2e8f0;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --text-body: #cbd5e1;
            --accent: #02E0FB;
            --accent-grad: linear-gradient(135deg, #02E0FB, #38bdf8);
            --accent-hover: #0369a1;
            --scrollbar: #475569;
            --shadow: 0 4px 24px rgba(0,0,0,.3);
            --box-shadow: 0 1px 3px rgba(0,0,0,.2);
            --table-stripe: #0f172a;
            --table-border: #1e293b;
            --tag-blue-bg: #0c4a6e;
            --tag-blue-text: #7dd3fc;
            --tag-green-bg: #064e3b;
            --tag-green-text: #6ee7b7;
            --tag-amber-bg: #451a03;
            --tag-amber-text: #fcd34d;
            --tag-purple-bg: #3b0764;
            --tag-purple-text: #d8b4fe;
            --tag-red-bg: #450a0a;
            --tag-red-text: #fca5a5;
            --diagram-primary-bg: #0c4a6e;
            --diagram-primary-text: #7dd3fc;
            --diagram-primary-border: #02E0FB;
            --diagram-success-bg: #064e3b;
            --diagram-success-text: #6ee7b7;
            --diagram-success-border: #10b981;
            --diagram-warning-bg: #451a03;
            --diagram-warning-text: #fcd34d;
            --diagram-warning-border: #f59e0b;
            --diagram-danger-bg: #450a0a;
            --diagram-danger-text: #fca5a5;
            --diagram-danger-border: #ef4444;
            --btn-primary-bg: #0284c7;
            --btn-primary-border: #0284c7;
            --btn-primary-hover: #0369a1;
        }

        html[data-theme="navy"] {
            --bg-body: #0a0e27;
            --bg-topbar: #111638;
            --bg-card: #111638;
            --bg-sidebar: #111638;
            --bg-input: #0a0e27;
            --bg-flow: #0a0e27;
            --bg-code: #0a0e27;
            --bg-hover: #1a2050;
            --bg-active: #0a0e27;
            --bg-overlay: rgba(0,0,0,.6);
            --border: #1e2350;
            --border-light: #2a3060;
            --text-primary: #eef1ff;
            --text-secondary: #d4d8f0;
            --text-muted: #8890b8;
            --text-dim: #5a6090;
            --text-body: #b8bcd8;
            --accent: #818cf8;
            --accent-grad: linear-gradient(135deg, #818cf8, #6366f1);
            --accent-hover: #4f46e5;
            --scrollbar: #2a3060;
            --shadow: 0 4px 24px rgba(0,0,0,.4);
            --box-shadow: 0 1px 3px rgba(0,0,0,.3);
            --table-stripe: #0a0e27;
            --table-border: #111638;
            --tag-blue-bg: #1e1b4b;
            --tag-blue-text: #a5b4fc;
            --tag-green-bg: #052e16;
            --tag-green-text: #86efac;
            --tag-amber-bg: #451a03;
            --tag-amber-text: #fcd34d;
            --tag-purple-bg: #3b0764;
            --tag-purple-text: #d8b4fe;
            --tag-red-bg: #450a0a;
            --tag-red-text: #fca5a5;
            --diagram-primary-bg: #1e1b4b;
            --diagram-primary-text: #a5b4fc;
            --diagram-primary-border: #818cf8;
            --diagram-success-bg: #052e16;
            --diagram-success-text: #86efac;
            --diagram-success-border: #22c55e;
            --diagram-warning-bg: #451a03;
            --diagram-warning-text: #fcd34d;
            --diagram-warning-border: #f59e0b;
            --diagram-danger-bg: #450a0a;
            --diagram-danger-text: #fca5a5;
            --diagram-danger-border: #ef4444;
            --btn-primary-bg: #4f46e5;
            --btn-primary-border: #4f46e5;
            --btn-primary-hover: #4338ca;
        }

        html { transition: background .15s; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-body); color: var(--text-secondary); min-height: 100vh; display: flex; flex-direction: column; }

        /* ── Topbar ── */
        .doc-topbar {
            background: var(--bg-topbar); border-bottom: 1px solid var(--border);
            padding: 0 1.5rem; height: 60px; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0; z-index: 50;
        }
        .doc-topbar-left { display: flex; align-items: center; gap: .75rem; }
        .doc-topbar-brand { font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: .5rem; text-decoration: none; color: var(--text-primary); }
        .doc-topbar-brand .accent { background: var(--accent-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .doc-topbar-brand .sub { font-weight: 400; font-size: .75rem; color: var(--text-dim); -webkit-text-fill-color: var(--text-dim); }
        .doc-topbar-right { display: flex; align-items: center; gap: .375rem; }
        .doc-topbar-btn {
            display: flex; align-items: center; gap: .375rem; padding: .4rem .7rem;
            font-size: .8125rem; font-weight: 500; color: var(--text-muted); text-decoration: none;
            border-radius: .5rem; transition: all .15s; border: 1px solid var(--border); white-space: nowrap;
        }
        .doc-topbar-btn:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-light); }
        .doc-topbar-btn.primary { background: var(--btn-primary-bg); color: white; border-color: var(--btn-primary-border); }
        .doc-topbar-btn.primary:hover { background: var(--btn-primary-hover); }

        /* ── Theme Toggle ── */
        .doc-theme-btn {
            display: flex; align-items: center; gap: .3rem; padding: .3rem .5rem;
            font-size: .75rem; font-weight: 500; color: var(--text-muted); cursor: pointer;
            border-radius: .5rem; transition: all .15s; border: 1px solid var(--border);
            background: transparent; white-space: nowrap;
        }
        .doc-theme-btn:hover { background: var(--bg-hover); color: var(--text-primary); border-color: var(--border-light); }

        /* ── Hamburger ── */
        .doc-hamburger { display: none; background: none; border: none; padding: .375rem; border-radius: .5rem; cursor: pointer; color: var(--text-muted); }
        .doc-hamburger:hover { background: var(--bg-hover); }

        /* ── Layout ── */
        .doc-layout { display: flex; flex: 1; background: var(--bg-body); }
        body, .doc-layout, .doc-topbar, .doc-sidebar, .doc-card, .doc-flow, .doc-diagram, .doc-table { transition: background .15s, border-color .15s, color .15s; }

        /* ── Sidebar ── */
        .doc-sidebar {
            width: 270px; flex-shrink: 0; background: var(--bg-sidebar); border-right: 1px solid var(--border);
            height: calc(100vh - 60px); position: sticky; top: 60px; overflow-y: auto;
            transition: transform .25s ease; z-index: 40;
        }
        .doc-sidebar::-webkit-scrollbar { width: 4px; }
        .doc-sidebar::-webkit-scrollbar-thumb { background: var(--scrollbar); border-radius: 4px; }
        .doc-sidebar-search { padding: .75rem; border-bottom: 1px solid var(--border); }
        .doc-sidebar-search input {
            width: 100%; padding: .4rem .75rem .4rem 2rem; font-size: .8125rem;
            background: var(--bg-input); border: 1px solid var(--border); border-radius: .5rem;
            outline: none; color: var(--text-secondary); transition: border-color .15s;
        }
        .doc-sidebar-search input:focus { border-color: var(--accent); }
        .doc-sidebar-search input::placeholder { color: var(--text-dim); }
        .doc-sidebar-search-wrap { position: relative; }
        .doc-sidebar-search-wrap svg { position: absolute; left: .5rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); }

        .doc-section-title {
            font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
            color: var(--text-dim); padding: 1rem 1rem .35rem 1rem;
        }
        .doc-nav-item {
            display: flex; align-items: center; gap: .5rem; padding: .35rem .75rem .35rem 1rem;
            margin: 0 .5rem; font-size: .8125rem; color: var(--text-muted); border-radius: .375rem;
            transition: all .12s; text-decoration: none;
        }
        .doc-nav-item:hover { background: var(--bg-hover); color: var(--text-secondary); }
        .doc-nav-item.active { background: var(--bg-active); color: var(--accent); font-weight: 600; }
        .doc-nav-item .icon { font-size: .875rem; width: 1.25rem; text-align: center; flex-shrink: 0; }

        /* ── Content ── */
        .doc-content { flex: 1; padding: 2rem; min-width: 0; max-width: 1000px; }
        .doc-card { background: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border); padding: 2.25rem; box-shadow: var(--shadow); }
        .doc-card-header { display: flex; align-items: flex-start; gap: .75rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border); margin-bottom: 1.5rem; }
        .doc-card-header .icon { font-size: 1.75rem; line-height: 1; margin-top: 2px; }
        .doc-card-header h2 { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); }
        .doc-card-header p { font-size: .8125rem; color: var(--text-dim); margin-top: .125rem; }

        /* ── Content Typography ── */
        .doc-content-body h3 { font-size: 1.125rem; font-weight: 600; color: var(--text-primary); margin: 2rem 0 .75rem; }
        .doc-content-body h3:first-child { margin-top: 0; }
        .doc-content-body h4 { font-size: .9375rem; font-weight: 600; color: var(--text-secondary); margin: 1.5rem 0 .5rem; }
        .doc-content-body p { font-size: .875rem; line-height: 1.8; color: var(--text-body); margin: 0 0 1rem; }
        .doc-content-body ul, .doc-content-body ol { margin: 0 0 1rem; padding-left: 1.5rem; }
        .doc-content-body li { font-size: .875rem; line-height: 1.8; color: var(--text-body); margin-bottom: .25rem; }
        .doc-content-body li strong, .doc-content-body p strong { color: var(--text-primary); }
        .doc-content-body code {
            font-size: .8125rem; background: var(--bg-code); color: var(--accent); padding: .125rem .4rem;
            border-radius: .25rem; border: 1px solid var(--border);
        }

        /* ── Flow Diagram ── */
        .doc-flow {
            display: flex; align-items: center; gap: .375rem; flex-wrap: wrap;
            padding: 1rem 1.25rem; background: var(--bg-flow); border-radius: .75rem;
            border: 1px solid var(--border); margin: 1.25rem 0;
        }
        .doc-flow-step {
            background: var(--bg-card); border: 1px solid var(--border-light); border-radius: .5rem;
            padding: .5rem .875rem; font-size: .8125rem; font-weight: 500;
            color: var(--text-secondary); box-shadow: var(--box-shadow);
        }
        .doc-flow-arrow { color: var(--text-dim); font-size: 1.125rem; flex-shrink: 0; }

        /* ── Vertical Diagram ── */
        .doc-diagram {
            display: flex; flex-direction: column; align-items: center; gap: .375rem;
            padding: 1.5rem; background: var(--bg-flow); border-radius: .75rem;
            border: 1px solid var(--border); margin: 1.25rem 0;
        }
        .doc-diagram-row { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; justify-content: center; }
        .doc-diagram-box {
            background: var(--bg-card); border: 2px solid var(--border-light); border-radius: .5rem;
            padding: .625rem 1rem; font-size: .8125rem; font-weight: 600;
            color: var(--text-secondary); text-align: center; min-width: 90px;
        }
        .doc-diagram-box.primary { border-color: var(--diagram-primary-border); background: var(--diagram-primary-bg); color: var(--diagram-primary-text); }
        .doc-diagram-box.success { border-color: var(--diagram-success-border); background: var(--diagram-success-bg); color: var(--diagram-success-text); }
        .doc-diagram-box.warning { border-color: var(--diagram-warning-border); background: var(--diagram-warning-bg); color: var(--diagram-warning-text); }
        .doc-diagram-box.danger { border-color: var(--diagram-danger-border); background: var(--diagram-danger-bg); color: var(--diagram-danger-text); }
        .doc-diagram-arrow { color: var(--text-dim); font-size: 1.125rem; }

        /* ── Table ── */
        .doc-table { width: 100%; border-collapse: collapse; font-size: .8125rem; margin: 1rem 0; border-radius: .5rem; overflow: hidden; border: 1px solid var(--border); }
        .doc-table th { text-align: left; padding: .6rem .75rem; background: var(--table-stripe); font-weight: 600; color: var(--text-muted); border-bottom: 1px solid var(--border); font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; }
        .doc-table td { padding: .6rem .75rem; border-bottom: 1px solid var(--table-border); color: var(--text-body); }
        .doc-table tr:last-child td { border-bottom: none; }
        .doc-table tr:hover td { background: var(--table-stripe); }

        /* ── Tags ── */
        .doc-tag { display: inline-flex; align-items: center; gap: .25rem; padding: .125rem .5rem; font-size: .6875rem; font-weight: 500; border-radius: 9999px; }
        .doc-tag-blue { background: var(--tag-blue-bg); color: var(--tag-blue-text); }
        .doc-tag-green { background: var(--tag-green-bg); color: var(--tag-green-text); }
        .doc-tag-amber { background: var(--tag-amber-bg); color: var(--tag-amber-text); }
        .doc-tag-purple { background: var(--tag-purple-bg); color: var(--tag-purple-text); }
        .doc-tag-red { background: var(--tag-red-bg); color: var(--tag-red-text); }

        /* ── Sidebar Overlay ── */
        .doc-sidebar-overlay { display: none; position: fixed; inset: 0; background: var(--bg-overlay); z-index: 35; }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .doc-content { padding: 1.25rem; }
            .doc-card { padding: 1.5rem; }
        }

        @media (max-width: 768px) {
            .doc-hamburger { display: block; }
            .doc-topbar { padding: 0 .75rem; height: 56px; }
            .doc-topbar-btn span, .doc-theme-btn span { display: none; }
            .doc-sidebar { position: fixed; left: 0; top: 56px; height: calc(100vh - 56px); transform: translateX(-100%); }
            .doc-sidebar.open { transform: translateX(0); box-shadow: 4px 0 24px rgba(0,0,0,.4); }
            .doc-sidebar-overlay.open { display: block; }
            .doc-content { padding: 1rem; }
            .doc-card { padding: 1.25rem; border-radius: .75rem; }
            .doc-flow { flex-direction: column; align-items: stretch; gap: .375rem; }
            .doc-flow-arrow { transform: rotate(90deg); align-self: center; }
            .doc-diagram { padding: 1rem; }
            .doc-diagram-box { min-width: 70px; font-size: .75rem; padding: .5rem .75rem; }
        }
    </style>
</head>
<body>
    {{-- Top Bar --}}
    <header class="doc-topbar">
        <div class="doc-topbar-left">
            <button class="doc-hamburger" onclick="toggleDocSidebar()" aria-label="Menü">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <a href="{{ route('admin.dokumantasyon.page') }}" class="doc-topbar-brand">
                <svg width="22" height="22" fill="none" stroke="var(--accent)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span class="accent">{{ config('app.name', 'MİYSOFT') }}</span>
                <span class="sub">| Dökümantasyon</span>
            </a>
        </div>
        <div class="doc-topbar-right">
            {{-- Theme Switcher --}}
            <button class="doc-theme-btn" onclick="toggleTheme()" title="Tema değiştir">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="themeIcon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <span id="themeLabel">Slate</span>
            </button>

            <a href="{{ route('admin.dashboard.index') }}" class="doc-topbar-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Admin Panel</span>
            </a>
            <a href="{{ config('app.website_url', '#') }}" target="_blank" class="doc-topbar-btn primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
                <span>Website</span>
            </a>
        </div>
    </header>

    {{-- Sidebar Overlay --}}
    <div class="doc-sidebar-overlay" id="docSidebarOverlay" onclick="toggleDocSidebar()"></div>

    {{-- Layout --}}
    <div class="doc-layout">
        <aside class="doc-sidebar" id="docSidebar">
            <div class="doc-sidebar-search">
                <div class="doc-sidebar-search-wrap">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="docSearchInput" placeholder="Sayfa ara..." oninput="filterSidebar(this.value)">
                </div>
            </div>
            <nav>
                @foreach ($sidebar as $section)
                <div class="doc-sidebar-section">
                    <div class="doc-section-title">{{ $section['icon'] }} {{ $section['label'] }}</div>
                    @foreach ($section['pages'] as $p)
                    @php
                        $route = route('admin.dokumantasyon.page', [$section['id'], $p['id']]);
                        $isActive = $section['id'] === $category && $p['id'] === $page;
                    @endphp
                    <a href="{{ $route }}" class="doc-nav-item @if($isActive) active @endif" data-search="{{ strtolower($section['label'].' '.$p['label']) }}">
                        <span class="icon">{{ $p['icon'] }}</span>
                        {{ $p['label'] }}
                    </a>
                    @endforeach
                </div>
                @endforeach
            </nav>
        </aside>

        <main class="doc-content">
            <div class="doc-card">
                <div class="doc-card-header">
                    <div class="icon">{{ $pageMeta['icon'] }}</div>
                    <div>
                        <h2>{{ $pageMeta['title'] }}</h2>
                        <p>{{ $pageMeta['description'] ?? $pageMeta['title'] . ' modülü hakkında detaylı bilgi' }}</p>
                    </div>
                </div>
                <div class="doc-content-body">
                    {!! $html !!}
                </div>
            </div>
        </main>
    </div>

    <script>
    function toggleDocSidebar() {
        document.getElementById('docSidebar').classList.toggle('open');
        document.getElementById('docSidebarOverlay').classList.toggle('open');
    }
    function filterSidebar(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.doc-nav-item').forEach(el => {
            const text = el.dataset.search || el.textContent.toLowerCase();
            el.style.display = !q || text.includes(q) ? '' : 'none';
        });
        document.querySelectorAll('.doc-sidebar-section').forEach(section => {
            const items = section.querySelectorAll('.doc-nav-item');
            const visible = [...items].some(el => el.style.display !== 'none');
            section.style.display = visible || !q ? '' : 'none';
        });
    }
    document.querySelectorAll('.doc-nav-item').forEach(el => {
        el.addEventListener('click', () => { if (window.innerWidth <= 768) toggleDocSidebar(); });
    });

    // ── Theme Switcher ──
    const THEMES = ['slate', 'navy'];
    const THEME_LABELS = { slate: 'Slate', navy: 'Navy' };

    function getTheme() { return localStorage.getItem('docTheme') || 'slate'; }

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('docTheme', theme);
        document.getElementById('themeLabel').textContent = THEME_LABELS[theme] || theme;
    }

    function toggleTheme() {
        const current = getTheme();
        const next = THEMES[(THEMES.indexOf(current) + 1) % THEMES.length];
        setTheme(next);
    }

    setTheme(getTheme());
    </script>
</body>
</html>
