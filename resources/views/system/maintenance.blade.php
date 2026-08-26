<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#8f5d54">
    <title>System Maintenance</title>
    @if(!empty($favicon))
        <link rel="icon" href="{{ \App\Helpers\CloudinaryHelper::thumbnail($favicon, 96) }}">
        <link rel="apple-touch-icon" href="{{ \App\Helpers\CloudinaryHelper::thumbnail($favicon, 96) }}">
    @endif
    <style>
        :root {
            --primary: #8f5d54;
            --text-primary: #1a202c;
            --text-secondary: #6b7280;
            --bg-light: #d9e0e7;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text-primary);
            background: linear-gradient(135deg, #cbd5e0 0%, #d9e0e7 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            overflow: hidden;
        }

        .container {
            width: 100%;
            max-width: 900px;
        }

        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 32px 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Logo */
        .logo-area {
            margin-bottom: 4px;
            background: var(--white);
            padding: 12px;
            border-radius: 12px;
        }

        .logo-img {
            max-width: 240px;
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Content */
        .content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            line-height: 1.2;
            color: var(--text-primary);
            word-wrap: break-word;
        }

        .description {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            word-wrap: break-word;
        }

        /* Illustration */
        .illustration {
            width: 100%;
            max-width: 150px;
            margin: 6px auto 2px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gear-icon {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: #e5e7eb;
        }

        /* Footer */
        .footer {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .footer-text {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex-wrap: wrap;
            row-gap: 8px;
        }

        .footer-text strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        .footer-text a {
            color: inherit;
            text-decoration: none;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .social-link {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease, transform 0.2s ease;
            border-radius: 50%;
            background: #f3f4f6;
        }

        .social-link:hover {
            color: var(--primary);
            background: #e5e7eb;
            transform: scale(1.1);
        }

        /* Tablet breakpoint */
        @media (max-width: 768px) {
            .card {
                padding: 28px 24px;
                gap: 14px;
            }

            h1 {
                font-size: 28px;
            }

            .description {
                font-size: 12px;
            }

            .logo-img {
                max-width: 180px;
            }

            .illustration {
                width: 100%;
                max-width: 140px;
                margin: 4px auto 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Mobile breakpoint */
        @media (max-width: 480px) {
            html, body {
                padding: 12px;
            }

            .card {
                padding: 20px 16px;
                gap: 12px;
                border-radius: 10px;
            }

            h1 {
                font-size: 22px;
                line-height: 1.2;
            }

            .description {
                font-size: 11px;
                line-height: 1.4;
            }

            .logo-area {
                padding: 12px;
                margin-bottom: 2px;
            }

            .logo-img {
                max-width: 150px;
            }

            .illustration {
                width: 100%;
                max-width: 130px;
                margin: 4px auto 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .footer-text {
                font-size: 10px;
                gap: 2px;
            }

            .social-links {
                gap: 12px;
            }

            .social-link {
                width: 28px;
                height: 28px;
            }

            .divider {
                margin: 6px 0;
            }
        }

        /* Small phone breakpoint */
        @media (max-width: 360px) {
            .card {
                padding: 18px 14px;
                gap: 10px;
            }

            h1 {
                font-size: 18px;
            }

            .description {
                font-size: 10px;
            }

            .logo-img {
                max-width: 130px;
            }

            .illustration {
                width: 100%;
                max-width: 120px;
                margin: 4px auto 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .footer-text {
                flex-direction: column;
                gap: 3px;
                font-size: 9px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card" role="alert" aria-live="polite">
            <!-- Logo -->
            <div class="logo-area">
                @if(!empty($logo))
                    <img
                        src="{{ $logo }}"
                        alt="Calaia PH Logo"
                        class="logo-img"
                    >
                @else
                    <div class="logo-img" style="display:flex;align-items:center;justify-content:center;min-height:72px;font-weight:700;letter-spacing:.25em;color:var(--primary);">
                        CALAIA PH
                    </div>
                @endif
            </div>

            <!-- Content -->
            <div class="content">
                <h1>The site is currently down for maintenance</h1>
                <p class="description">{{ $message }}</p>
            </div>

            <!-- Illustration -->
            <div class="illustration" aria-hidden="true">
                <svg class="gear-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <style>
                        .gear-rotate {
                            transform-origin: 50px 50px;
                            animation: gear-spin 6s linear infinite;
                        }

                        @keyframes gear-spin {
                            from { transform: rotate(0deg); }
                            to { transform: rotate(360deg); }
                        }
                    </style>

                    <g class="gear-rotate" fill="#000000">
                        <!-- Teeth -->
                        <rect x="42" y="2" width="16" height="18" rx="3"/>
                        <rect x="42" y="80" width="16" height="18" rx="3"/>
                        <rect x="80" y="42" width="18" height="16" rx="3"/>
                        <rect x="2" y="42" width="18" height="16" rx="3"/>

                        <g transform="translate(50 50)">
                            <g transform="rotate(45)">
                                <rect x="30" y="-8" width="16" height="16" rx="3"/>
                            </g>
                            <g transform="rotate(135)">
                                <rect x="30" y="-8" width="16" height="16" rx="3"/>
                            </g>
                            <g transform="rotate(225)">
                                <rect x="30" y="-8" width="16" height="16" rx="3"/>
                            </g>
                            <g transform="rotate(315)">
                                <rect x="30" y="-8" width="16" height="16" rx="3"/>
                            </g>
                        </g>

                        <!-- Gear body -->
                        <circle cx="50" cy="50" r="30"/>
                    </g>

                    <!-- Hole -->
                    <circle cx="50" cy="50" r="12" fill="#ffffff"/>
                </svg>
            </div>

            <!-- Divider -->
            <div class="divider"></div>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-text">
                    <span>You can contact us:</span>
                    <span>Phone: <strong><a href="{{ $contactPhoneUri }}">{{ $contactPhone }}</a></strong></span>
                    @if($contactEmail !== '')
                        <span>Email: <strong><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></strong></span>
                    @endif
                </div>
                @if(!empty($socialLinks))
                    <div class="social-links">
                        @foreach($socialLinks as $socialLink)
                            <a
                                href="{{ $socialLink['url'] }}"
                                class="social-link"
                                title="{{ $socialLink['label'] }}"
                                aria-label="Open Calaia {{ $socialLink['label'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                @switch($socialLink['icon'])
                                    @case('instagram')
                                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
                                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" fill="none" stroke="currentColor" stroke-width="2"/>
                                            <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/>
                                            <circle cx="18" cy="6" r="1" fill="currentColor"/>
                                        </svg>
                                        @break
                                    @case('facebook')
                                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        @break
                                    @case('messenger')
                                        <svg viewBox="0 0 24 24" fill="none" width="18" height="18" aria-hidden="true">
                                            <path d="M4 4h16v12H8l-4 4V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                            <path d="m8 12 3-3 2.5 2L17 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        @break
                                @endswitch
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
