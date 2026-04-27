@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card">
    <div class="card-body">
      <h4 class="mb-2">Titan Go — Voice Upgrade</h4>
      <p class="text-muted mb-3">
        Titan Go is a paid voice control upgrade for Titan Pro.
      </p>

      <div class="alert alert-info">
        <div class="fw-bold">To enable (MVP):</div>
        <ol class="mb-0">
          <li>Set <code>company_settings</code> key <code>titango_enabled</code> to <code>1</code> for this company</li>
          <li>Or set <code>TITANGO_FORCE_ENABLED=true</code> temporarily for testing</li>
          <li>Ensure <code>TITANGO_TITANZERO_ACTION_URL</code> is configured</li>
        </ol>
      </div>

      <p class="small text-muted mb-0">Powered by Titan Zero™ — Intelligence without friction.</p>
    </div>
  </div>
</div>
@endsection
