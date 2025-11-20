<!doctype html>
<html lang="en">

<head>
    <title>Privacy Policy - Face 2 Face</title>
    @include('layouts.meta')
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
                <a href="">Privacy Policy</a>
                {{-- <a href="#contact">Contact</a>
                <a href="#">Help</a> --}}
            </nav>
        </div>
    </header>

    <main class="container">
        <article class="card" role="article" aria-labelledby="privacy-title">
            <header>
                <h1 id="privacy-title">Privacy Policy</h1>
                <div class="meta">Last updated: <strong>November 20, 2025</strong></div>
            </header>

            <nav class="toc" aria-label="Table of contents">
                <strong>Contents</strong>
                <a href="#what">What information we collect</a>
                <a href="#how">How we use information</a>
                <a href="#share">Sharing & disclosure</a>
                <a href="#security">Security</a>
                <a href="#choices">Your choices</a>
                <a href="#contact">Contact us</a>
            </nav>

            <section id="what">
                <h2>1. What information we collect</h2>
                <p>We collect information when you create an account, use the free version, purchase premium access, buy
                    ebooks, or contact support.</p>
                <ul>
                    <li><strong>Account data:</strong> name, email, username, password.</li>
                    <li><strong>Usage data:</strong> progress tracking, which laws you read, bookmarks/favourites,
                        history.</li>
                    <li><strong>Purchase data:</strong> premium subscription details, in-app ebook purchases (Amazon
                        redirects only).</li>
                    <li><strong>Technical data:</strong> device type, OS version, IP address, analytics, crash logs.
                    </li>
                </ul>
                <p>We do not store any credit/debit card information. Payments are handled by secure third-party
                    platforms such as Play Store, App Store, or Amazon.</p>
            </section>

            <section id="how">
                <h2>2. How we use information</h2>
                <p>We use your information to provide and improve the core functionality of our ebook app.</p>
                <ul>
                    <li>To create and manage your account</li>
                    <li>To allow reading of free 10 laws and unlock all 48 laws after premium purchase</li>
                    <li>To save your reading progress, history, and favourites</li>
                    <li>To process premium subscription and verify purchases</li>
                    <li>To show ebooks available in the store (Amazon-linked items)</li>
                    <li>To improve performance, fix issues, and enhance user experience</li>
                    <li>To prevent unauthorized access or fraudulent activity</li>
                </ul>
            </section>

            <section id="share">
                <h2>3. Sharing & disclosure</h2>
                <p>We may share limited information only with trusted service providers who help us run the app (such as
                    hosting, analytics, or payment partners).</p>
                <p>Amazon purchases are external; we do not send your personal information to Amazon—only a redirect is
                    made.</p>
                <p>We do not sell your personal information to anyone.</p>
                <p>If we are involved in a merger or sale, user data may be transferred as part of that process.</p>
            </section>

            <section id="security">
                <h2>4. Security</h2>
                <p>We use encryption, secure servers, and industry-standard practices to protect your data. However, no
                    online service can guarantee 100% security.</p>
            </section>

            <section id="choices">
                <h2>5. Your choices</h2>
                <p>You can access, update, or delete your account information at any time. Deleting your account will
                    permanently remove your progress, favourites, and reading history.</p>
                <p>You can manage or cancel your subscription directly through the Play Store or App Store billing
                    settings.</p>
            </section>

            <section id="children">
                <h2>6. Children</h2>
                <p>Our services are not intended for children under 13. We do not knowingly collect data from children
                    under 13.</p>
            </section>

            <section id="contact">
                <h2>7. Contact us</h2>
                <p>If you have questions about this privacy policy, please contact us:</p>
                <ul>
                    <li>Email: <a href="mailto:privacy@yoursite.example">privacy@yoursite.example</a></li>
                    <li>Address: 123 Example Street, City, Country</li>
                </ul>
            </section>

            <section id="changes">
                <h2>8. Changes to this policy</h2>
                <p>We may update this policy from time to time. When changes are made, the "Last updated" date above
                    will be updated.</p>
            </section>

        </article>

        <footer class="footer" aria-label="Footer">
            <div class="container"
                style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div style="font-size:14px">&copy; <span id="year">2025</span> Face 2 Face. All rights reserved.
                </div>
            </div>
        </footer>
    </main>


    <script>
        // keep year up-to-date
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>

</html>
