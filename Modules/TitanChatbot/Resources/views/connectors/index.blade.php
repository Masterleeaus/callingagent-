@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3">Connector Hub</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        {{-- HUBSPOT --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>HubSpot</strong>
                    <small>Status: {{ $accounts['hubspot']['status'] ?? 'disconnected' }}</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('connectors.provider.save') }}">
                        @csrf
                        <input type="hidden" name="provider" value="hubspot" />
                        <div class="mb-2">
                            <label class="form-label">Access Token (Private App Token)</label>
                            <input type="text" class="form-control" name="access_token" value="{{ $accounts['hubspot']['credentials']['access_token'] ?? '' }}" />
                        </div>
                        <button class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="testProvider('hubspot')">Test</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- MAILCHIMP --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Mailchimp</strong>
                    <small>Status: {{ $accounts['mailchimp']['status'] ?? 'disconnected' }}</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('connectors.provider.save') }}">
                        @csrf
                        <input type="hidden" name="provider" value="mailchimp" />
                        <div class="mb-2">
                            <label class="form-label">API Key</label>
                            <input type="text" class="form-control" name="api_key" value="{{ $accounts['mailchimp']['credentials']['api_key'] ?? '' }}" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Audience ID (List ID)</label>
                            <input type="text" class="form-control" name="audience_id" value="{{ $accounts['mailchimp']['credentials']['audience_id'] ?? '' }}" />
                        </div>
                        <button class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="testProvider('mailchimp')">Test</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- WORDPRESS --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>WordPress</strong>
                    <small>Status: {{ $accounts['wordpress']['status'] ?? 'disconnected' }}</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('connectors.provider.save') }}">
                        @csrf
                        <input type="hidden" name="provider" value="wordpress" />
                        <div class="mb-2">
                            <label class="form-label">Site URL</label>
                            <input type="text" class="form-control" name="site_url" value="{{ $accounts['wordpress']['credentials']['site_url'] ?? '' }}" placeholder="https://example.com" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="{{ $accounts['wordpress']['credentials']['username'] ?? '' }}" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">App Password</label>
                            <input type="text" class="form-control" name="app_password" value="{{ $accounts['wordpress']['credentials']['app_password'] ?? '' }}" />
                        </div>
                        <button class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="testProvider('wordpress')">Test</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- STRIPE --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Stripe</strong>
                    <small>Status: {{ $accounts['stripe']['status'] ?? 'disconnected' }}</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('connectors.provider.save') }}">
                        @csrf
                        <input type="hidden" name="provider" value="stripe" />
                        <div class="mb-2">
                            <label class="form-label">Secret Key</label>
                            <input type="text" class="form-control" name="secret_key" value="{{ $accounts['stripe']['credentials']['secret_key'] ?? '' }}" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Success URL</label>
                            <input type="text" class="form-control" name="success_url" value="{{ $accounts['stripe']['credentials']['success_url'] ?? '' }}" placeholder="https://yoursite.com/paid" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Cancel URL</label>
                            <input type="text" class="form-control" name="cancel_url" value="{{ $accounts['stripe']['credentials']['cancel_url'] ?? '' }}" placeholder="https://yoursite.com/cancel" />
                        </div>
                        <button class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="testProvider('stripe')">Test</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- SQUARE --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Square</strong>
                    <small>Status: {{ $accounts['square']['status'] ?? 'disconnected' }}</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('connectors.provider.save') }}">
                        @csrf
                        <input type="hidden" name="provider" value="square" />
                        <div class="mb-2">
                            <label class="form-label">Access Token</label>
                            <input type="text" class="form-control" name="access_token" value="{{ $accounts['square']['credentials']['access_token'] ?? '' }}" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Location ID</label>
                            <input type="text" class="form-control" name="location_id" value="{{ $accounts['square']['credentials']['location_id'] ?? '' }}" />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Square-Version (optional)</label>
                            <input type="text" class="form-control" name="square_version" value="{{ $accounts['square']['credentials']['square_version'] ?? '2024-06-04' }}" />
                        </div>
                        <button class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="testProvider('square')">Test</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4" />

    <h4>Client Pack (runs everything)</h4>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label">Template</label>
                    <select class="form-select" id="template_id">
                        <option value="">(default)</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} @if($t->is_default) (default) @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Payments Provider</label>
                    <select class="form-select" id="payments_provider">
                        <option value="stripe">Stripe</option>
                        <option value="square">Square</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label">Name</label>
                    <input class="form-control" id="cp_name" />
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Email</label>
                    <input class="form-control" id="cp_email" />
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Phone</label>
                    <input class="form-control" id="cp_phone" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label">Amount (for payment link)</label>
                    <input class="form-control" id="cp_amount" value="0" />
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Currency</label>
                    <input class="form-control" id="cp_currency" value="AUD" />
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Payment Description</label>
                    <input class="form-control" id="cp_payment_description" value="Deposit / Payment" />
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label">Notes</label>
                <textarea class="form-control" id="cp_notes" rows="3"></textarea>
            </div>

            <button class="btn btn-success" type="button" onclick="runClientPack()">Run Client Pack</button>
        </div>
    </div>

    <h4>Client Pack Templates</h4>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('connectors.templates.save') }}">
                @csrf
                <input type="hidden" name="id" value="" />
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Template Name</label>
                        <input class="form-control" name="name" placeholder="Plumbing - Residential" />
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Set Default</label>
                        <select class="form-select" name="is_default">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Template JSON</label>
                    <textarea class="form-control" name="template_json" rows="6" placeholder='{"mailchimp_tags":["Lead","Plumbing"],"wp_title":"New Job Lead","wp_content":"New lead: {{name}} ({{email}})\n{{notes}}","wp_status":"publish","wp_categories":[],"amount":0,"currency":"AUD","payment_description":"Deposit / Payment"}'></textarea>
                </div>
                <button class="btn btn-primary">Save Template</button>
            </form>

            <hr class="my-3" />

            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Default</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $t)
                            <tr>
                                <td>{{ $t->name }}</td>
                                <td>{{ $t->is_default ? 'Yes' : 'No' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('connectors.templates.default') }}" style="display:inline-block">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $t->id }}" />
                                        <button class="btn btn-outline-secondary btn-sm">Make Default</button>
                                    </form>
                                    <form method="POST" action="{{ route('connectors.templates.delete') }}" style="display:inline-block" onsubmit="return confirm('Delete template?')">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $t->id }}" />
                                        <button class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
async function testProvider(provider) {
    const res = await fetch("{{ route('connectors.provider.test') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
        },
        body: JSON.stringify({provider})
    });
    const j = await res.json();
    alert((j.ok ? 'OK: ' : 'ERROR: ') + (j.message || ''));
}

async function runClientPack() {
    const payload = {
        template_id: document.getElementById('template_id').value,
        payments_provider: document.getElementById('payments_provider').value,
        name: document.getElementById('cp_name').value,
        email: document.getElementById('cp_email').value,
        phone: document.getElementById('cp_phone').value,
        notes: document.getElementById('cp_notes').value,
        amount: document.getElementById('cp_amount').value,
        currency: document.getElementById('cp_currency').value,
        payment_description: document.getElementById('cp_payment_description').value,
    };

    const res = await fetch("{{ route('connectors.clientpack.run') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });
    const j = await res.json();
    console.log(j);
    alert('Client Pack complete. Open console for details.');
}
</script>
@endsection
