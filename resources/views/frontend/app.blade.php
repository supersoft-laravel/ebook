<!doctype html>
<html lang="en">

<head>
    <title>@yield('title') - Face 2 Face</title>
    @include('layouts.meta')
    @include('layouts.css')
    <style>
        /* Simple, clean styling */
        :root {
            --accent: #0d6efd;
            --muted: #6c757d;
            --bg: #f8f9fa;
            --max-width: 900px;
            --radius: 8px;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            color: #212529;
        }

        body {
            margin: 0;
            background: var(--bg);
            line-height: 1.6
        }

        .site-header {
            background: #ffffff;
            border-bottom: 1px solid #e9ecef
        }

        .container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 20px
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 0
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit
        }

        .logo {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: linear-gradient(135deg, #212529, #1c1922);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700
        }

        nav a {
            color: var(--muted);
            text-decoration: none;
            margin-left: 18px;
            font-size: 14px
        }

        main {
            padding: 28px 0
        }

        .card {
            background: white;
            border-radius: var(--radius);
            padding: 28px;
            box-shadow: 0 6px 20px rgba(18, 24, 32, 0.04)
        }

        h1 {
            margin: 0 0 6px;
            font-size: 1.5rem
        }

        .meta {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 18px
        }

        .toc {
            margin: 12px 0 20px;
            padding-left: 18px
        }

        .toc a {
            display: block;
            color: var(--accent);
            text-decoration: none;
            margin: 6px 0
        }

        section {
            margin-top: 18px
        }

        section h2 {
            font-size: 1.05rem;
            margin: 0 0 8px
        }

        p {
            margin: 0 0 12px
        }

        ul {
            margin: 0 0 12px 20px
        }

        .footer {
            margin-top: 28px;
            padding: 18px 0;
            color: var(--muted);
            font-size: 14px;
            border-top: 1px solid #e9ecef;
            background: transparent
        }

        .footer .links a {
            margin-right: 12px;
            color: var(--muted);
            text-decoration: none
        }

        /* responsive */
        @media (max-width:600px) {
            .header-inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px
            }

            nav a {
                margin-left: 0
            }
        }
    </style>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="#" class="brand">
                <div class="logo"><img height="30px" src="{{ asset(\App\Helpers\Helper::getLogoLight()) }}"
                        alt=""></div>
                <div>
                    <div style="font-weight:700">Face 2 Face</div>
                    <div style="font-size:13px;color:var(--muted);margin-top:2px">Simple & clear policies</div>
                </div>
            </a>
            <nav aria-label="Main navigation">
                <a href="{{ route('frontend.privacy-policy') }}">Privacy Policy</a>
                <a href="{{ route('frontend.delete-account') }}">Delete Account</a>
            </nav>
        </div>
    </header>

    <main class="container">
        @yield('content')

        <footer class="footer" aria-label="Footer">
            <div class="container"
                style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div style="font-size:14px">&copy; <span id="year">2025</span> Face 2 Face. All rights reserved.
                </div>
            </div>
        </footer>
    </main>

    <!-- jQuery (required for Toastr) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


    <script>
        // keep year up-to-date
        document.getElementById('year').textContent = new Date().getFullYear();
        toastr.options = {
            "closeButton": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "2000"
        };
        @if (session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if (session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        @if (session('message'))
            toastr.info("{{ session('message') }}");
        @endif

        @if ($errors->any())
            toastr.error("{{ $errors->first() }}");
        @endif
    </script>

</body>

</html>
