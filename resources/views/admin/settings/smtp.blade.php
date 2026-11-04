@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-SMTP-settings-page', 'pageClass' => 'admin-SMTP-settings-page'])
@section('page-title', 'SMTP Settings')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'SMTP Settings',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header"><h2 class="h6 mb-0">Mail Settings</h2></div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.smtp.update') }}" method="POST" autocomplete="off" class="smtp-settings-form">
                        @csrf
                        <div class="form-group row align-items-center">
                            <label class="col-md-3 col-form-label" for="mail-driver">Type</label>
                            <div class="col-md-9">
                                <select id="mail-driver" name="mail_driver" class="form-control form-select" required>
                                    <option value="sendmail" @selected($driver === 'sendmail')>Sendmail</option>
                                    <option value="smtp" @selected($driver === 'smtp')>SMTP</option>
                                    <option value="mailgun" @selected($driver === 'mailgun')>Mailgun</option>
                                    <option value="microsoft" @selected($driver === 'microsoft')>Microsoft 365</option>
                                    <option value="log" @selected($driver === 'log')>Log (testing only)</option>
                                </select>
                            </div>
                        </div>

                        <div data-mail-driver="smtp">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mail-host">MAIL HOST</label>
                                <div class="col-md-9"><input id="mail-host" name="mail_host" class="form-control" value="{{ old('mail_host', $mailHost) }}" placeholder="smtp.example.com"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mail-port">MAIL PORT</label>
                                <div class="col-md-9"><input id="mail-port" type="number" min="1" max="65535" name="mail_port" class="form-control" value="{{ old('mail_port', $mailPort) }}" placeholder="587"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mail-username">MAIL USERNAME</label>
                                <div class="col-md-9"><input id="mail-username" name="mail_username" class="form-control" value="{{ old('mail_username', $mailUsername) }}" autocomplete="username"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mail-password">MAIL PASSWORD</label>
                                <div class="col-md-9">
                                    <input id="mail-password" type="password" name="mail_password" class="form-control" value="" autocomplete="new-password" placeholder="{{ $mailPasswordConfigured ? 'Saved securely; leave blank to keep current' : 'Enter mail password' }}">
                                    @if ($mailPasswordConfigured) <small class="text-muted">A password is saved. It is never shown here.</small> @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mail-encryption">MAIL ENCRYPTION</label>
                                <div class="col-md-9">
                                    <select id="mail-encryption" name="mail_encryption" class="form-control">
                                        <option value="tls" @selected($mailEncryption === 'tls')>TLS</option>
                                        <option value="ssl" @selected($mailEncryption === 'ssl')>SSL</option>
                                        <option value="none" @selected($mailEncryption === 'none')>None</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div data-mail-driver="mailgun">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mailgun-domain">Mailgun Domain</label>
                                <div class="col-md-9"><input id="mailgun-domain" name="mailgun_domain" class="form-control" value="{{ old('mailgun_domain', $mailgunDomain) }}" placeholder="mg.example.com"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mailgun-secret">Mailgun API Key</label>
                                <div class="col-md-9">
                                    <input id="mailgun-secret" type="password" name="mailgun_secret" class="form-control" value="" autocomplete="new-password" placeholder="{{ $mailgunSecretConfigured ? 'Saved securely; leave blank to keep current' : 'Enter Mailgun API key' }}">
                                    @if ($mailgunSecretConfigured) <small class="text-muted">A key is saved. It is never shown here.</small> @endif
                                </div>
                            </div>
                        </div>

                        <div data-mail-driver="microsoft">
                            <div class="alert alert-info">Sends through Microsoft Graph using an Azure app registration with the Mail.Send application permission.</div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mail-tenant-id">Tenant ID</label>
                                <div class="col-md-9"><input id="mail-tenant-id" name="mail_tenant_id" class="form-control" value="{{ old('mail_tenant_id', $mailTenantId) }}" autocomplete="off"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mail-client-id">Client ID</label>
                                <div class="col-md-9"><input id="mail-client-id" name="mail_client_id" class="form-control" value="{{ old('mail_client_id', $mailClientId) }}" autocomplete="off"></div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label" for="mail-client-secret">Client Secret Value</label>
                                <div class="col-md-9">
                                    <input id="mail-client-secret" type="password" name="mail_client_secret" class="form-control" value="" autocomplete="new-password" placeholder="{{ $mailClientSecretConfigured ? 'Saved securely; leave blank to keep current' : 'Enter client secret' }}">
                                    @if ($mailClientSecretConfigured) <small class="text-muted">A secret is saved. It is never shown here.</small> @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="mail-from-address">MAIL FROM ADDRESS</label>
                            <div class="col-md-9"><input id="mail-from-address" type="email" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $mailFromAddress) }}" required></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="mail-from-name">MAIL FROM NAME</label>
                            <div class="col-md-9"><input id="mail-from-name" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $mailFromName) }}" required></div>
                        </div>
                        <div class="text-right"><button type="submit" class="btn btn-primary">Save Configuration</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card mb-3">
                <div class="card-header"><h2 class="h6 mb-0">Test SMTP configuration</h2></div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.smtp.test') }}" method="POST" class="form-inline">
                        @csrf
                        <div class="form-group mb-2 mr-2">
                            <label for="test-email" class="sr-only">Test recipient email</label>
                            <input id="test-email" type="email" name="email" class="form-control" value="{{ old('email', $testEmail) }}" placeholder="Enter your email address" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Send test email</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="h6 mb-0">Configuration notes</h2></div>
                <div class="card-body">
                    <p class="text-danger">Incorrect mail settings can prevent order confirmations, registration messages, and newsletters from being delivered.</p>
                    <h3 class="h6 text-muted">SMTP</h3>
                    <ul class="list-group mb-3">
                        <li class="list-group-item">Use the host and port provided by your mail provider.</li>
                        <li class="list-group-item">Use TLS with port 587 or SSL with port 465 when required by your provider.</li>
                        <li class="list-group-item">The From Address should be authorized by your provider.</li>
                    </ul>
                    <h3 class="h6 text-muted">Microsoft 365</h3>
                    <ul class="list-group">
                        <li class="list-group-item">Create an Azure app registration and add Microsoft Graph application permission <strong>Mail.Send</strong>.</li>
                        <li class="list-group-item">Grant admin consent and use a licensed mailbox as the From Address.</li>
                        <li class="list-group-item">Client secrets are encrypted before being saved and are never displayed again.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const mailDriverSelect = document.getElementById('mail-driver');
const mailDriverSections = document.querySelectorAll('[data-mail-driver]');

function updateMailDriverFields() {
    mailDriverSections.forEach((section) => {
        section.hidden = section.dataset.mailDriver !== mailDriverSelect.value;
    });
}

mailDriverSelect.addEventListener('change', updateMailDriverFields);
updateMailDriverFields();
</script>
@endsection
