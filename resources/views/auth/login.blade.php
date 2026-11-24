<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>Masuk | PT Putri Hoki Logistik</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Portal masuk sistem manajemen armada dan logistik PT Putri Hoki Logistik (PHL)." />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="color-scheme" content="light dark" />

    <link rel="shortcut icon" href="{{ asset('assets/images/logo-phl.png') }}" />

    <!-- Sync theme before paint to prevent FOUC and stay compatible with app __CONFIG__ -->
    <script>
        (function () {
            var theme = "light";
            try {
                var saved = localStorage.getItem("__CONFIG__");
                if (saved) {
                    var parsed = JSON.parse(saved);
                    if (parsed && (parsed.theme === "dark" || parsed.theme === "light")) {
                        theme = parsed.theme;
                    }
                } else if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
                    theme = "dark";
                }
            } catch (e) {}
            document.documentElement.setAttribute("data-bs-theme", theme);
            var meta = document.querySelector('meta[name="color-scheme"]');
            if (meta) {
                meta.setAttribute("content", theme);
            }
        })();
    </script>

    <!-- Local app font & base styles (deferred non-critical vendor scripts omitted on auth screen) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        :root {
            color-scheme: light dark;

            /* Brand palette derived from official PHL lotus emblem (#332391 / #5F4DC5 / #9E9AF7) */
            --phl-indigo-deep: #181242;
            --phl-indigo-mid: #281E6E;
            --phl-accent: #5B4CE6;
            --phl-accent-hover: #4839C8;
            --phl-accent-active: #3B2EB3;
            --phl-accent-soft: #9E9AF7;

            /* Light surface tokens */
            --phl-bg: #F4F5FA;
            --phl-surface: #FFFFFF;
            --phl-surface-subtle: #F8F9FC;
            --phl-ink: #141928;
            --phl-ink-muted: #4E5A71;
            --phl-border: #838D9F;
            --phl-border-subtle: #E2E6F0;
            --phl-focus: #5B4CE6;
            --phl-error-bg: #FEF2F2;
            --phl-error-border: #F87171;
            --phl-error-ink: #B91C1C;
            --phl-success-bg: #ECFDF5;
            --phl-success-border: #34D399;
            --phl-success-ink: #065F46;
            --phl-logo-bg: transparent;
            --phl-logo-pad: 0;
            --phl-shadow: 0 20px 45px -24px rgba(20, 25, 40, 0.16);
        }

        html[data-bs-theme="dark"] {
            color-scheme: dark;
            --phl-bg: #0B0E17;
            --phl-surface: #111522;
            --phl-surface-subtle: #181D2E;
            --phl-ink: #F1F5F9;
            --phl-ink-muted: #A3B0C6;
            --phl-border: #6E7B96;
            --phl-border-subtle: #242B3E;
            --phl-focus: #8B7FF5;
            --phl-error-bg: #2B151B;
            --phl-error-border: #F87171;
            --phl-error-ink: #FCA5A5;
            --phl-success-bg: #0D2920;
            --phl-success-border: #34D399;
            --phl-success-ink: #6EE7B7;
            --phl-logo-bg: #F4F5FA;
            --phl-logo-pad: 8px 12px;
            --phl-shadow: 0 24px 50px -24px rgba(0, 0, 0, 0.55);
        }

        @media (prefers-color-scheme: dark) {
            html:not([data-bs-theme="light"]) {
                color-scheme: dark;
                --phl-bg: #0B0E17;
                --phl-surface: #111522;
                --phl-surface-subtle: #181D2E;
                --phl-ink: #F1F5F9;
                --phl-ink-muted: #A3B0C6;
                --phl-border: #6E7B96;
                --phl-border-subtle: #242B3E;
                --phl-focus: #8B7FF5;
                --phl-error-bg: #2B151B;
                --phl-error-border: #F87171;
                --phl-error-ink: #FCA5A5;
                --phl-success-bg: #0D2920;
                --phl-success-border: #34D399;
                --phl-success-ink: #6EE7B7;
                --phl-logo-bg: #F4F5FA;
                --phl-logo-pad: 8px 12px;
                --phl-shadow: 0 24px 50px -24px rgba(0, 0, 0, 0.55);
            }
        }

        html[data-bs-theme="light"] {
            color-scheme: light;
        }

        [hidden] {
            display: none !important;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: "Public Sans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--phl-bg);
            color: var(--phl-ink);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Interactive cursor affordance base layer (R-39) */
        a[href],
        button:not(:disabled),
        label[for],
        input[type="checkbox"]:not(:disabled),
        input[type="radio"]:not(:disabled) {
            cursor: pointer;
        }

        button:disabled,
        input:disabled {
            cursor: not-allowed;
        }

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip-path: inset(50%);
            white-space: nowrap;
            border: 0;
        }

        /* Layout: 3 responsive states (Mobile < 640px, Tablet 640-991px, Desktop >= 992px) */
        .phl-auth-shell {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background-color: var(--phl-bg);
        }

        /* Left Brand Panel (Architectural deep-indigo identity showcase) */
        .phl-brand-panel {
            display: none;
            position: relative;
            background: linear-gradient(155deg, var(--phl-indigo-deep) 0%, var(--phl-indigo-mid) 100%);
            color: #FFFFFF;
            padding: 3rem;
            overflow: hidden;
        }

        .phl-brand-inner {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            max-width: 560px;
            margin: 0 auto;
        }

        .phl-brand-lockup {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
        }

        .phl-brand-badge {
            flex: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            padding: 7px;
            border-radius: 14px;
            background-color: #FFFFFF;
            box-shadow: 0 12px 28px -10px rgba(8, 6, 30, 0.55);
        }

        .phl-brand-badge img {
            display: block;
            width: 58px;
            height: 58px;
            object-fit: contain;
        }

        .phl-brand-identity {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .phl-brand-name {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            line-height: 1.25;
            color: #FFFFFF;
        }

        .phl-brand-sub {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 500;
            color: #D6D3F8;
        }

        .phl-brand-copy {
            margin-block: auto;
            padding-block: 2.5rem;
        }

        .phl-brand-headline {
            margin: 0 0 1rem;
            font-size: clamp(1.75rem, 2.5vw, 2.375rem);
            font-weight: 700;
            line-height: 1.18;
            letter-spacing: -0.025em;
            color: #FFFFFF;
        }

        .phl-brand-lead {
            margin: 0 0 2.25rem;
            font-size: 1rem;
            line-height: 1.6;
            color: #D6D3F8;
            max-width: 44ch;
        }

        /* Bespoke PHL logistics route & lotus vector motif */
        .phl-route-art {
            width: 100%;
            height: auto;
            display: block;
        }

        .phl-brand-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(214, 211, 248, 0.16);
            font-size: 0.8125rem;
            color: #D6D3F8;
        }

        /* Right Form Panel */
        .phl-form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--phl-surface);
            padding: 1.25rem;
        }

        .phl-form-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            width: 100%;
            max-width: 420px;
            margin: 0 auto 1.5rem;
        }

        .phl-brand-link {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            border-radius: 10px;
            background-color: var(--phl-logo-bg);
            padding: var(--phl-logo-pad);
        }

        .phl-brand-link:focus-visible {
            outline: 2px solid var(--phl-focus);
            outline-offset: 4px;
        }

        .phl-brand-logo {
            display: block;
            width: 68px;
            height: 68px;
            object-fit: contain;
        }

        .phl-theme-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 0;
            border-radius: 10px;
            border: 1px solid var(--phl-border);
            background-color: var(--phl-surface-subtle);
            color: var(--phl-ink);
            transition: border-color 150ms ease, transform 120ms ease;
        }

        .phl-theme-btn:hover {
            background-color: var(--phl-bg);
            border-color: var(--phl-ink-muted);
        }

        .phl-theme-btn:active {
            transform: scale(0.97);
        }

        .phl-theme-btn:focus-visible {
            outline: 2px solid var(--phl-focus);
            outline-offset: 2px;
        }

        .phl-theme-icon-sun,
        .phl-theme-icon-moon {
            width: 20px;
            height: 20px;
        }

        html[data-bs-theme="light"] .phl-theme-icon-sun {
            display: none;
        }

        html[data-bs-theme="dark"] .phl-theme-icon-moon {
            display: none;
        }

        .phl-form-body {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        .phl-form-header {
            margin-bottom: 1.75rem;
        }

        .phl-form-title {
            margin: 0 0 0.375rem;
            font-size: clamp(1.5rem, 2.2vw, 1.75rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.25;
            color: var(--phl-ink);
        }

        .phl-form-subtitle {
            margin: 0;
            font-size: 0.9375rem;
            color: var(--phl-ink-muted);
        }

        /* Alert / Feedback States */
        .phl-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.875rem 1rem;
            margin-bottom: 1.25rem;
            border-radius: 10px;
            border: 1px solid var(--phl-error-border);
            background-color: var(--phl-error-bg);
            color: var(--phl-error-ink);
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.45;
        }

        .phl-alert--success {
            border-color: var(--phl-success-border);
            background-color: var(--phl-success-bg);
            color: var(--phl-success-ink);
        }

        .phl-alert-icon {
            flex: none;
            width: 18px;
            height: 18px;
            margin-top: 1px;
        }

        .phl-alert-list {
            margin: 0;
            padding-left: 1.125rem;
        }

        /* Form Fields */
        .phl-form {
            display: flex;
            flex-direction: column;
            gap: 1.125rem;
        }

        .phl-field {
            display: flex;
            flex-direction: column;
            gap: 0.4375rem;
        }

        .phl-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--phl-ink);
        }

        .phl-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .phl-input {
            width: 100%;
            min-height: 48px;
            padding: 0.75rem 0.9375rem;
            font: inherit;
            font-size: max(1rem, 1em);
            color: var(--phl-ink);
            background-color: var(--phl-surface-subtle);
            border: 1.5px solid var(--phl-border);
            border-radius: 10px;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .phl-input--password {
            padding-right: 3.25rem;
        }

        .phl-input::placeholder {
            color: var(--phl-ink-muted);
            opacity: 0.85;
        }

        .phl-input:hover {
            border-color: var(--phl-ink-muted);
        }

        .phl-input:focus {
            outline: none;
            background-color: var(--phl-surface);
            border-color: var(--phl-focus);
            box-shadow: 0 0 0 3px rgba(91, 76, 230, 0.22);
        }

        .phl-input:focus-visible {
            outline: 2px solid var(--phl-focus);
            outline-offset: 2px;
        }

        .phl-input[aria-invalid="true"] {
            border-color: var(--phl-error-border);
        }

        .phl-pw-toggle {
            position: absolute;
            right: 2px;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 0;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--phl-ink-muted);
            transition: color 150ms ease, background-color 150ms ease;
        }

        .phl-pw-toggle:hover {
            color: var(--phl-ink);
            background-color: rgba(131, 141, 159, 0.14);
        }

        .phl-pw-toggle:focus-visible {
            outline: 2px solid var(--phl-focus);
            outline-offset: 1px;
        }

        .phl-pw-toggle svg {
            width: 20px;
            height: 20px;
        }

        .phl-field-error {
            margin: 0;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--phl-error-ink);
            display: none;
        }

        .phl-field-error.is-visible {
            display: block;
        }

        /* Primary Submit Button */
        .phl-submit-btn {
            margin-top: 0.375rem;
            width: 100%;
            min-height: 48px;
            padding: 0.75rem 1.25rem;
            border: 0;
            border-radius: 10px;
            background-color: var(--phl-accent);
            color: #FFFFFF;
            font: inherit;
            font-size: 0.9375rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background-color 150ms ease, transform 120ms ease;
        }

        .phl-submit-btn:hover:not(:disabled) {
            background-color: var(--phl-accent-hover);
        }

        .phl-submit-btn:active:not(:disabled) {
            background-color: var(--phl-accent-active);
            transform: translateY(1px);
        }

        .phl-submit-btn:focus-visible {
            outline: 2px solid var(--phl-focus);
            outline-offset: 3px;
        }

        .phl-submit-btn:disabled {
            opacity: 0.72;
        }

        .phl-spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.38);
            border-top-color: #FFFFFF;
            border-radius: 50%;
            animation: phlSpin 700ms linear infinite;
        }

        .phl-submit-btn.is-loading .phl-spinner {
            display: inline-block;
        }

        @keyframes phlSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .phl-form-footer {
            width: 100%;
            max-width: 420px;
            margin: 2rem auto 0;
            padding-top: 1.25rem;
            border-top: 1px solid var(--phl-border-subtle);
            font-size: 0.8125rem;
            color: var(--phl-ink-muted);
        }

        /* Tablet breakpoint (640px - 991px): framed card on slate canvas */
        @media (min-width: 640px) {
            .phl-auth-shell {
                justify-content: center;
                padding: 2rem 1.5rem;
            }

            .phl-form-panel {
                flex: none;
                width: 100%;
                max-width: 480px;
                margin: 0 auto;
                padding: 2.25rem 2.5rem;
                border-radius: 16px;
                border: 1px solid var(--phl-border-subtle);
                box-shadow: var(--phl-shadow);
            }

            .phl-form-topbar {
                margin-bottom: 2rem;
            }
        }

        /* Desktop breakpoint (>= 992px): asymmetric architectural split */
        @media (min-width: 992px) {
            .phl-auth-shell {
                display: grid;
                grid-template-columns: minmax(0, 1.08fr) minmax(440px, 0.92fr);
                padding: 0;
            }

            .phl-brand-panel {
                display: block;
            }

            .phl-form-panel {
                flex: 1;
                max-width: none;
                margin: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                justify-content: center;
                padding: 2.5rem 3.5rem;
            }

            .phl-form-topbar {
                margin-bottom: 2.25rem;
                justify-content: flex-end;
            }

            .phl-form-topbar .phl-brand-link {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .phl-theme-btn,
            .phl-input,
            .phl-pw-toggle,
            .phl-submit-btn {
                transition: none;
            }

            .phl-spinner {
                animation: none;
            }
        }
    </style>
</head>

<body>
    <div class="phl-auth-shell">
        <aside class="phl-brand-panel" aria-label="Identitas sistem PHL">
            <div class="phl-brand-inner">
                <div class="phl-brand-lockup">
                    <span class="phl-brand-badge">
                        <img src="{{ asset('assets/images/logo-phl.png') }}" alt="Logo PT Putri Hoki Logistik"
                            width="58" height="58" fetchpriority="high" />
                    </span>
                    <div class="phl-brand-identity">
                        <p class="phl-brand-name">PT PUTRI HOKI LOGISTIK</p>
                        <p class="phl-brand-sub">Fleet Management System</p>
                    </div>
                </div>

                <div class="phl-brand-copy">
                    <h2 class="phl-brand-headline">Kendali Armada &amp; Distribusi Logistik.</h2>
                    <p class="phl-brand-lead">
                        Satu portal untuk operasional armada, surat jalan, inventaris gudang, dan penagihan.
                    </p>

                    <svg class="phl-route-art" viewBox="0 0 520 220" width="520" height="220" fill="none"
                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <!-- Subtle lotus-inspired petal geometry echoing PHL emblem -->
                        <path d="M405 210C405 142 446 92 504 72C492 138 458 188 405 210Z" fill="#5F4DC5"
                            fill-opacity="0.18" />
                        <path d="M398 210C362 156 366 96 406 50C436 96 432 156 398 210Z" fill="#9E9AF7"
                            fill-opacity="0.12" />
                        <path d="M388 210C336 196 300 156 286 106C340 120 374 156 388 210Z" fill="#5F4DC5"
                            fill-opacity="0.14" />

                        <!-- Concentric hub range rings around primary distribution hub -->
                        <circle cx="96" cy="146" r="46" stroke="#9E9AF7" stroke-opacity="0.16" stroke-width="1" />
                        <circle cx="96" cy="146" r="78" stroke="#9E9AF7" stroke-opacity="0.09" stroke-width="1"
                            stroke-dasharray="4 6" />
                        <circle cx="424" cy="74" r="38" stroke="#9E9AF7" stroke-opacity="0.16" stroke-width="1" />

                        <!-- Distribution corridor arcs -->
                        <path d="M96 146C166 78 268 48 424 74" stroke="#FFFFFF" stroke-opacity="0.9"
                            stroke-width="2" stroke-linecap="round" />
                        <path d="M96 146C174 166 254 170 332 134C374 114 402 92 424 74" stroke="#9E9AF7"
                            stroke-opacity="0.55" stroke-width="1.75" stroke-dasharray="5 5" />
                        <path d="M238 68C272 96 294 116 332 134" stroke="#9E9AF7" stroke-opacity="0.35"
                            stroke-width="1.25" />

                        <!-- Fleet & warehouse hub markers -->
                        <circle cx="96" cy="146" r="10" fill="#5B4CE6" fill-opacity="0.45" />
                        <circle cx="96" cy="146" r="4.5" fill="#FFFFFF" />

                        <circle cx="238" cy="68" r="4.5" fill="#FFFFFF" fill-opacity="0.92" />
                        <circle cx="332" cy="134" r="4.5" fill="#9E9AF7" />

                        <circle cx="424" cy="74" r="10" fill="#5B4CE6" fill-opacity="0.45" />
                        <circle cx="424" cy="74" r="4.5" fill="#FFFFFF" />
                    </svg>
                </div>

                <div class="phl-brand-footer">
                    <span>PHL Fleet Management System</span>
                    <span>Portal Internal</span>
                </div>
            </div>
        </aside>

        <main class="phl-form-panel">
            <div class="phl-form-topbar">
                <a href="{{ url('/') }}" class="phl-brand-link" aria-label="Beranda PT Putri Hoki Logistik">
                    <img src="{{ asset('assets/images/logo-phl.png') }}" alt="Logo PT Putri Hoki Logistik"
                        class="phl-brand-logo" width="68" height="68" fetchpriority="high" />
                </a>

                <button type="button" id="light-dark-mode" class="phl-theme-btn" aria-label="Ubah mode tampilan"
                    aria-pressed="false">
                    <svg class="phl-theme-icon-moon" viewBox="0 0 24 24" width="20" height="20" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z" />
                    </svg>
                    <svg class="phl-theme-icon-sun" viewBox="0 0 24 24" width="20" height="20" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="4" />
                        <path
                            d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
                    </svg>
                </button>
            </div>

            <div class="phl-form-body">
                <header class="phl-form-header">
                    <h1 class="phl-form-title">Masuk ke Akun</h1>
                    <p class="phl-form-subtitle">Gunakan username dan kata sandi Anda.</p>
                </header>

                @if (session('fail'))
                    <div class="phl-alert" role="alert" aria-live="polite">
                        <svg class="phl-alert-icon" viewBox="0 0 20 20" width="18" height="18" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM9 6.75a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5ZM10 14.5a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('fail') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="phl-alert" role="alert" aria-live="polite">
                        <svg class="phl-alert-icon" viewBox="0 0 20 20" width="18" height="18" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM9 6.75a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5ZM10 14.5a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div class="phl-alert phl-alert--success" role="status" aria-live="polite">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="phl-alert" role="alert" aria-live="polite">
                        <svg class="phl-alert-icon" viewBox="0 0 20 20" width="18" height="18" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM9 6.75a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5ZM10 14.5a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"
                                clip-rule="evenodd" />
                        </svg>
                        <ul class="phl-alert-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="login-form" method="POST" action="{{ route('authenticate') }}" class="phl-form" novalidate>
                    @csrf

                    <div class="phl-field">
                        <label for="username" class="phl-label">Username</label>
                        <input id="username" name="username" type="text" class="phl-input"
                            value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username"
                            autocorrect="off" autocapitalize="none" spellcheck="false" enterkeyhint="next"
                            aria-describedby="username-error" @error('username') aria-invalid="true" @enderror required
                            autofocus />
                        <p id="username-error" class="phl-field-error" role="status"></p>
                    </div>

                    <div class="phl-field">
                        <label for="password" class="phl-label">Kata Sandi</label>
                        <div class="phl-input-wrap">
                            <input id="password" name="password" type="password" class="phl-input phl-input--password"
                                placeholder="Masukkan kata sandi" autocomplete="current-password" enterkeyhint="done"
                                aria-describedby="password-error" @error('password') aria-invalid="true" @enderror
                                required />
                            <button type="button" id="toggle-password" class="phl-pw-toggle" aria-pressed="false"
                                aria-label="Tampilkan kata sandi" aria-controls="password">
                                <svg id="icon-eye-open" viewBox="0 0 24 24" width="20" height="20" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg id="icon-eye-closed" viewBox="0 0 24 24" width="20" height="20" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true" hidden>
                                    <path
                                        d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.5 0-10-7-10-7a18.45 18.45 0 0 1 5.06-5.94" />
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19" />
                                    <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                                    <path d="m2 2 20 20" />
                                </svg>
                            </button>
                        </div>
                        <p id="password-error" class="phl-field-error" role="status"></p>
                    </div>

                    <button type="submit" id="login-submit" class="phl-submit-btn">
                        <span class="phl-spinner" aria-hidden="true"></span>
                        <span id="login-submit-label">Masuk</span>
                    </button>
                </form>
            </div>

            <footer class="phl-form-footer">
                &copy; {{ date('Y') }} PT Putri Hoki Logistik
            </footer>
        </main>
    </div>

    <script>
        (function () {
            var root = document.documentElement;
            var themeBtn = document.getElementById("light-dark-mode");
            var metaScheme = document.querySelector('meta[name="color-scheme"]');

            function syncThemeUI(theme) {
                root.setAttribute("data-bs-theme", theme);
                if (metaScheme) {
                    metaScheme.setAttribute("content", theme);
                }
                if (themeBtn) {
                    var isDark = theme === "dark";
                    themeBtn.setAttribute("aria-pressed", String(isDark));
                    themeBtn.setAttribute("aria-label", isDark ? "Ubah ke mode terang" : "Ubah ke mode gelap");
                }
            }

            syncThemeUI(root.getAttribute("data-bs-theme") || "light");

            if (themeBtn) {
                themeBtn.addEventListener("click", function () {
                    var current = root.getAttribute("data-bs-theme") === "dark" ? "dark" : "light";
                    var next = current === "dark" ? "light" : "dark";
                    try {
                        var existing = JSON.parse(localStorage.getItem("__CONFIG__") || "{}");
                        existing.theme = next;
                        localStorage.setItem("__CONFIG__", JSON.stringify(existing));
                    } catch (e) {}
                    syncThemeUI(next);
                });
            }

            var passwordInput = document.getElementById("password");
            var togglePwBtn = document.getElementById("toggle-password");
            var iconOpen = document.getElementById("icon-eye-open");
            var iconClosed = document.getElementById("icon-eye-closed");

            if (passwordInput && togglePwBtn) {
                togglePwBtn.addEventListener("click", function () {
                    var showing = passwordInput.type === "text";
                    passwordInput.type = showing ? "password" : "text";
                    togglePwBtn.setAttribute("aria-pressed", String(!showing));
                    togglePwBtn.setAttribute("aria-label", showing ? "Tampilkan kata sandi" : "Sembunyikan kata sandi");
                    if (iconOpen && iconClosed) {
                        if (showing) {
                            iconOpen.removeAttribute("hidden");
                            iconClosed.setAttribute("hidden", "");
                        } else {
                            iconOpen.setAttribute("hidden", "");
                            iconClosed.removeAttribute("hidden");
                        }
                    }
                });
            }

            var form = document.getElementById("login-form");
            var usernameInput = document.getElementById("username");
            var usernameErr = document.getElementById("username-error");
            var passwordErr = document.getElementById("password-error");
            var submitBtn = document.getElementById("login-submit");
            var submitLabel = document.getElementById("login-submit-label");

            function setFieldError(input, errEl, message) {
                if (!input || !errEl) return;
                if (message) {
                    input.setAttribute("aria-invalid", "true");
                    errEl.textContent = message;
                    errEl.classList.add("is-visible");
                } else {
                    input.removeAttribute("aria-invalid");
                    errEl.textContent = "";
                    errEl.classList.remove("is-visible");
                }
            }

            function validateField(input, errEl, emptyMessage) {
                if (!input) return true;
                var val = input.value.trim();
                if (!val) {
                    setFieldError(input, errEl, emptyMessage);
                    return false;
                }
                setFieldError(input, errEl, "");
                return true;
            }

            if (usernameInput) {
                usernameInput.addEventListener("input", function () {
                    if (usernameInput.value.trim()) {
                        setFieldError(usernameInput, usernameErr, "");
                    }
                });
                usernameInput.addEventListener("blur", function () {
                    if (usernameInput.dataset.touched === "1") {
                        validateField(usernameInput, usernameErr, "Username wajib diisi.");
                    }
                });
                usernameInput.addEventListener("change", function () {
                    usernameInput.dataset.touched = "1";
                });
            }

            if (passwordInput) {
                passwordInput.addEventListener("input", function () {
                    if (passwordInput.value.trim()) {
                        setFieldError(passwordInput, passwordErr, "");
                    }
                });
                passwordInput.addEventListener("blur", function () {
                    if (passwordInput.dataset.touched === "1") {
                        validateField(passwordInput, passwordErr, "Kata sandi wajib diisi.");
                    }
                });
                passwordInput.addEventListener("change", function () {
                    passwordInput.dataset.touched = "1";
                });
            }

            if (form) {
                form.addEventListener("submit", function (event) {
                    var okUser = validateField(usernameInput, usernameErr, "Username wajib diisi.");
                    var okPass = validateField(passwordInput, passwordErr, "Kata sandi wajib diisi.");
                    if (!okUser || !okPass) {
                        event.preventDefault();
                        if (!okUser && usernameInput) {
                            usernameInput.focus();
                        } else if (!okPass && passwordInput) {
                            passwordInput.focus();
                        }
                        return;
                    }
                    if (submitBtn && submitLabel) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add("is-loading");
                        submitLabel.textContent = "Memproses...";
                    }
                });
            }
        })();
    </script>
</body>

</html>
