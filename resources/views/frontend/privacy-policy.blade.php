@extends('frontend.app')

@section('title', 'Privacy Policy')

@section('css')
@endsection

@section('content')
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
@endsection

@section('script')
@endsection
