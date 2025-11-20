@extends('frontend.app')

@section('title', 'Delete Account Request')

@section('css')
@endsection

@section('content')
    <article class="card" role="article" aria-labelledby="delete-title">
        <header>
            <h1 id="delete-title">Delete Account Request</h1>
            <div class="meta">Last updated: <strong>November 20, 2025</strong></div>
        </header>

        <section>
            <h2>How to Delete Your Account</h2>
            <p>
                If you would like to permanently delete your account, please submit the form below using the same
                email and password you used to create your account.
            </p>
            <p>
                Once your request is submitted, our team will verify your identity and begin the deletion process.
                You will receive a confirmation email when your request is accepted.
            </p>
        </section>

        <section>
            <h2>What Data Will Be Deleted?</h2>
            <ul>
                <li>Your profile information (name, email, username)</li>
                <li>Your reading progress, favourites, and history</li>
                <li>Your subscription and in-app purchase history inside the app (Amazon purchases won't be deleted
                    as they are external)</li>
                <li>Saved preferences, settings, and device-linked data</li>
            </ul>
            <p><strong>Important:</strong> This action is permanent and cannot be undone.</p>
        </section>

        <section>
            <h2>Retention Policy</h2>
            <p>
                Once your deletion request is submitted and verified, your account will be permanently erased from
                our systems within <strong>90 days</strong>.
            </p>
            <p>
                During this time, your data will no longer be accessible and will only remain stored securely
                for verification, fraud prevention, and legal compliance.
            </p>
        </section>

        <section>
            <h2>Submit Account Deletion Request</h2>

            <form method="POST" action="{{ route('frontend.delete-account-request') }}" style="display:flex;flex-direction:column;gap:14px">
                @csrf
                <div class="mb-6">
                    <label for="email_username" class="form-label">{{__('Email/Username')}}</label><span class="text-danger">*</span>
                    <input type="text" class="form-control @error('email_username') is-invalid @enderror" id="email_username" name="email_username"
                        placeholder="{{__('Enter your email or username')}}" autofocus required/>
                    @error('email_username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-6 form-password-toggle">
                    <label class="form-label" for="password">{{__('Password')}}</label><span class="text-danger">*</span>
                    <div class="input-group input-group-merge">
                        <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password"
                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                            aria-describedby="password" required/>
                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-danger d-grid w-100">{{__('Request Delete')}}</button>

                <p style="font-size:14px;color:#555;margin-top:6px;">
                    By submitting this request, you confirm that you want to permanently delete your account.
                </p>
            </form>
        </section>
    </article>
@endsection

@section('script')
@endsection
