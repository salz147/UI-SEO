<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel App')</title>
    <script>
        (function () {
            var theme = localStorage.getItem('seo-theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    @stack('styles')
    <style>
        :root {
            --app-bg: #f7fbfd;
            --app-rail: #ffffff;
            --app-surface: #ffffff;
            --app-surface-soft: #f0f8fc;
            --app-border: #d6e2ea;
            --app-text: #101820;
            --app-muted: #71808d;
            --app-accent: #1ebce2;
            --app-accent-strong: #168fe6;
            --app-teal: #20cbd1;
            --app-success: #18d56e;
            --app-warning: #d89422;
            --app-danger: #d84c5b;
            --app-shadow: 0 10px 24px rgba(17, 82, 116, 0.08);
        }

        html[data-theme="dark"] {
            --app-bg: #202123;
            --app-rail: #17191b;
            --app-surface: #282a2d;
            --app-surface-soft: #303337;
            --app-border: #3a3e43;
            --app-text: #edf5f7;
            --app-muted: #9aa6ae;
            --app-accent: #0ca6cf;
            --app-accent-strong: #13bce8;
            --app-teal: #26d6c6;
            --app-success: #31c778;
            --app-warning: #edb24e;
            --app-danger: #ec5c6b;
            --app-shadow: 0 18px 38px rgba(0, 0, 0, 0.28);
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: var(--app-border) transparent;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--app-bg);
            color: var(--app-text);
            font-size: 14px;
        }

        .app-shell {
            display: flex;
            min-height: 100vh;
            background: var(--app-bg);
        }

        .app-rail {
            position: sticky;
            top: 0;
            width: 64px;
            height: 100vh;
            flex: 0 0 64px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            padding: 16px 10px;
            background: var(--app-rail);
            border-right: 1px solid var(--app-border);
        }

        .rail-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            width: 100%;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: transparent;
        }

        .brand-mark img {
            width: 44px;
            height: 44px;
            display: block;
        }

        .brand-mark::after {
            content: "Smartchat";
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translate(10px, -50%);
            white-space: nowrap;
            opacity: 0;
            color: var(--app-muted);
            font-size: 12px;
            transition: opacity .18s ease;
        }

        .brand-mark:hover::after {
            opacity: 1;
        }

        .rail-link {
            width: 38px;
            height: 38px;
            border: 1px solid transparent;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--app-muted);
            background: transparent;
            transition: background .18s ease, color .18s ease, border-color .18s ease;
        }

        .rail-icon {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .rail-link:hover {
            color: var(--app-text);
            text-decoration: none;
            background: var(--app-surface-soft);
            border-color: var(--app-border);
        }

        .rail-link.active {
            color: var(--app-accent-strong);
            background: #dff5ff;
        }

        .app-content {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .app-topbar {
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 24px;
            background: var(--app-surface);
            border-bottom: 1px solid var(--app-border);
        }

        .app-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--app-text);
        }

        .app-subtitle {
            color: var(--app-muted);
            font-size: 12px;
        }

        .app-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--app-muted);
            font-size: 12px;
        }

        .theme-toggle {
            margin-top: 2px;
            width: 42px;
            display: flex;
            justify-content: center;
        }

        .theme-toggle-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .theme-toggle-label {
            position: relative;
            width: 42px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            padding: 2px;
            border: 1px solid var(--app-border);
            border-radius: 999px;
            background: var(--app-surface-soft);
            cursor: pointer;
            transition: background .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .theme-toggle-label::before {
            content: "";
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: linear-gradient(145deg, var(--app-accent-strong), var(--app-teal));
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.18);
            transform: translateX(0);
            transition: transform .18s ease;
        }

        .theme-toggle-icon {
            position: absolute;
            top: 50%;
            width: 10px;
            height: 10px;
            transform: translateY(-50%);
            fill: currentColor;
            pointer-events: none;
            transition: opacity .18s ease, color .18s ease;
        }

        .theme-toggle-icon.moon {
            left: 6px;
            color: var(--app-text);
            opacity: 1;
        }

        .theme-toggle-icon.sun {
            right: 6px;
            color: var(--app-warning);
            opacity: .45;
        }

        .theme-toggle-input:checked + .theme-toggle-label::before {
            transform: translateX(18px);
        }

        .theme-toggle-input:checked + .theme-toggle-label .moon {
            opacity: .4;
        }

        .theme-toggle-input:checked + .theme-toggle-label .sun {
            opacity: 1;
        }

        .theme-toggle-input:focus + .theme-toggle-label {
            box-shadow: 0 0 0 3px rgba(12, 166, 207, 0.16);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--app-success);
            box-shadow: 0 0 0 4px rgba(49, 199, 120, 0.14);
        }

        .app-main {
            flex: 1;
            padding: 10px 12px 28px;
        }

        .app-main .container-fluid {
            max-width: none;
            width: 100%;
        }

        h1 {
            color: var(--app-text);
            font-size: 20px;
            font-weight: 700;
        }

        .card {
            background: var(--app-surface);
            border: 1px solid var(--app-border);
            border-radius: 8px;
            box-shadow: var(--app-shadow);
            color: var(--app-text);
        }

        .card-header {
            background: var(--app-surface);
            border-bottom: 1px solid var(--app-border);
            color: var(--app-text);
        }

        .card-header h5,
        .card-title {
            color: var(--app-text);
            font-weight: 700;
        }

        .card-title {
            color: var(--app-muted);
            font-size: 12px;
        }

        .card h2 {
            color: var(--app-text);
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 0;
        }

        .table {
            color: var(--app-text);
        }

        .table th,
        .table td {
            border-top-color: var(--app-border);
            vertical-align: middle;
            padding: .55rem .7rem;
        }

        .table thead th,
        .table-dark th,
        .table-light th {
            color: var(--app-text);
            background: var(--app-surface-soft) !important;
            border-color: var(--app-border) !important;
        }

        .table-hover tbody tr:hover,
        html[data-theme="light"] .table-hover tbody tr:hover {
            color: var(--app-text);
            background: #eaf8ff;
        }

        html[data-theme="dark"] .table-hover tbody tr:hover {
            color: var(--app-text);
            background: #34383d;
        }

        html[data-theme="dark"] .table-hover tbody tr:hover td,
        html[data-theme="dark"] .table-hover tbody tr:hover th,
        html[data-theme="dark"] .table-hover tbody tr:hover a,
        html[data-theme="dark"] .table-hover tbody tr:hover small,
        html[data-theme="dark"] .table-hover tbody tr:hover strong {
            color: inherit;
        }

        .table-responsive {
            border-radius: 8px;
        }

        .text-muted,
        .form-text {
            color: var(--app-muted) !important;
        }

        .form-control {
            color: var(--app-text);
            background: #ffffff;
            border-color: var(--app-border);
            border-radius: 7px;
        }

        html[data-theme="dark"] .form-control,
        html[data-theme="dark"] textarea.form-control,
        html[data-theme="dark"] select.form-control {
            color: var(--app-text);
            background: var(--app-surface-soft);
            border-color: var(--app-border);
            color-scheme: dark;
        }

        .form-control:focus {
            color: var(--app-text);
            background: var(--app-surface);
            border-color: var(--app-accent);
            box-shadow: 0 0 0 .2rem rgba(12, 166, 207, .18);
        }

        html[data-theme="dark"] .form-control:focus,
        html[data-theme="dark"] textarea.form-control:focus,
        html[data-theme="dark"] select.form-control:focus {
            color: var(--app-text);
            background: var(--app-surface);
            border-color: var(--app-accent);
        }

        .form-control[readonly],
        .form-control:disabled {
            color: var(--app-text) !important;
            background: var(--app-surface-soft) !important;
            border-color: var(--app-border) !important;
            opacity: 1;
            -webkit-text-fill-color: var(--app-text);
        }

        html[data-theme="dark"] .form-control::placeholder {
            color: var(--app-muted);
            opacity: 1;
        }

        html[data-theme="dark"] .form-control:-webkit-autofill,
        html[data-theme="dark"] .form-control:-webkit-autofill:hover,
        html[data-theme="dark"] .form-control:-webkit-autofill:focus {
            -webkit-text-fill-color: var(--app-text);
            box-shadow: 0 0 0 1000px var(--app-surface-soft) inset;
            caret-color: var(--app-text);
        }

        .custom-control-label::before {
            background: var(--app-surface-soft);
            border-color: var(--app-border);
        }

        .btn {
            border-radius: 7px;
            font-weight: 600;
        }

        .btn-primary,
        .btn-info {
            color: #fff;
            background: #bdefff;
            color: #0076a3;
            border-color: transparent;
        }

        .btn-primary:hover,
        .btn-info:hover {
            color: #005f84;
            background: #a7e8ff;
            filter: none;
        }

        .btn-success {
            color: #fff;
            background: var(--app-success);
            border-color: var(--app-success);
        }

        .btn-warning {
            color: #211b0d;
            background: var(--app-warning);
            border-color: var(--app-warning);
        }

        .btn-danger {
            color: #fff;
            background: var(--app-danger);
            border-color: var(--app-danger);
        }

        .btn-secondary,
        .btn-outline-secondary {
            color: var(--app-text);
            background: #f7fbfd;
            border-color: var(--app-border);
        }

        .bg-light,
        .active-conv {
            background-color: #eef2f5 !important;
        }

        .message-out,
        .chat-bubble.outgoing,
        .bubble-out,
        .my-message {
            background: #5dc9f4 !important;
            color: #0a2733 !important;
        }

        .message-in,
        .chat-bubble.incoming,
        .bubble-in,
        .their-message {
            background: #f1f3f5 !important;
            color: var(--app-text) !important;
        }

        .btn-outline-danger {
            color: var(--app-danger);
            border-color: var(--app-danger);
        }

        .badge {
            border-radius: 999px;
            font-weight: 600;
        }

        .badge-info {
            color: #eaffff;
            background: rgba(12, 166, 207, 0.72);
        }

        .badge-success {
            color: #f4fff9;
            background: var(--app-success);
        }

        .badge-danger {
            color: #fff;
            background: var(--app-danger);
        }

        .badge-warning {
            color: #241a07;
            background: var(--app-warning);
        }

        .badge-secondary {
            color: var(--app-text);
            background: var(--app-surface-soft);
            border: 1px solid var(--app-border);
        }

        .alert {
            border-radius: 8px;
            border-color: var(--app-border);
        }

        .alert-info {
            color: var(--app-text);
            background: rgba(12, 166, 207, 0.12);
        }

        .alert-success {
            color: var(--app-text);
            background: rgba(49, 199, 120, 0.16);
        }

        .alert-danger {
            color: var(--app-text);
            background: rgba(236, 92, 107, 0.16);
        }

        .list-group-item {
            color: var(--app-text);
            background: var(--app-surface);
            border-color: var(--app-border);
        }

        .list-group-item-action:hover {
            color: var(--app-text);
            background: var(--app-surface-soft);
        }

        .modal-content {
            color: var(--app-text);
            background: var(--app-surface);
            border-color: var(--app-border);
        }

        .modal-header {
            border-bottom-color: var(--app-border);
        }

        .close {
            color: var(--app-text);
            text-shadow: none;
        }

        .page-link {
            color: var(--app-text);
            background: var(--app-surface);
            border-color: var(--app-border);
        }

        .page-item.active .page-link {
            color: #fff;
            background: var(--app-accent);
            border-color: var(--app-accent);
        }

        .page-item.disabled .page-link {
            color: var(--app-muted);
            background: var(--app-surface-soft);
            border-color: var(--app-border);
        }

        a {
            color: var(--app-accent-strong);
        }

        a:hover {
            color: var(--app-teal);
        }

        @media (max-width: 767.98px) {
            .app-shell {
                display: block;
            }

            .app-rail {
                position: static;
                width: 100%;
                height: auto;
                flex-direction: row;
                justify-content: space-between;
                border-right: 0;
                border-bottom: 1px solid var(--app-border);
            }

            .app-topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .app-main {
                padding: 6px 0 24px;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="app-rail" aria-label="SEO navigation">
            <div class="rail-group">
                <a class="brand-mark" href="{{ route('seo.index') }}" title="Smartchat SEO">
                    <img src="{{ asset('logo.webp') }}" alt="Smartchat SEO">
                </a>
                <div class="theme-toggle" title="Ubah mode tampilan">
                    <input class="theme-toggle-input" id="themeToggle" type="checkbox" aria-label="Ubah dark mode dan light mode">
                    <label class="theme-toggle-label" for="themeToggle">
                        <svg class="theme-toggle-icon moon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20.7 14.4A8 8 0 0 1 9.6 3.3 9 9 0 1 0 20.7 14.4Z"/>
                        </svg>
                        <svg class="theme-toggle-icon sun" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Zm0 4-1.1-2.6L12 17l1.1 2.4L12 22Zm0-20 1.1 2.6L12 7l-1.1-2.4L12 2Zm10 10-2.6 1.1L17 12l2.4-1.1L22 12ZM2 12l2.6-1.1L7 12l-2.4 1.1L2 12Zm16.4 7.8-2.5-.9.8-2.5 1.7 1.7Zm-11.8-14 .9 2.5-2.5.8 1.6-3.3Zm9.3 2.5-.8-2.5 2.5-.9-1.7 3.4ZM5.8 18.4l2.5-.8.9 2.5-3.4-1.7Z"/>
                        </svg>
                    </label>
                </div>
                <a class="rail-link {{ request()->routeIs('seo.index') ? 'active' : '' }}" href="{{ route('seo.index') }}" title="Daftar SEO">
                    <svg class="rail-icon" viewBox="0 0 24 24" role="img" aria-hidden="true"><title>Daftar SEO</title><path d="M4 5h16v10H7l-3 3V5z"/><path d="M8 9h8M8 13h5"/></svg>
                </a>
                <a class="rail-link {{ request()->routeIs('seo.logging') ? 'active' : '' }}" href="{{ route('seo.logging') }}" title="Logging">
                    <svg class="rail-icon" viewBox="0 0 24 24" role="img" aria-hidden="true"><title>Logging</title><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M8 13h8M8 17h5M8 9h2"/></svg>
                </a>
            </div>
        </aside>

        <div class="app-content">
            <header class="app-topbar">
                <div>
                    <p class="app-title">@yield('title', 'SEO App')</p>
                    <span class="app-subtitle">Smartchat SEO workspace</span>
                </div>
                <div class="app-status">
                    <span class="status-dot"></span>
                    <span>Local active</span>
                </div>
            </header>

            <main class="app-main">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var toggle = document.getElementById('themeToggle');
            if (!toggle) {
                return;
            }

            toggle.checked = (document.documentElement.getAttribute('data-theme') || 'light') === 'light';

            toggle.addEventListener('change', function () {
                var next = toggle.checked ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('seo-theme', next);
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
