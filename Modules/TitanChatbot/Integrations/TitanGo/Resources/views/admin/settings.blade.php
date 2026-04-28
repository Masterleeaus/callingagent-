@extends('layouts.app')

@section('content')
<div class="container">
  <h4 class="mb-3">Titan Go Settings (MVP)</h4>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <p class="text-muted">
        Titan Go entitlement is currently controlled by <code>company_settings</code> key <code>titango_enabled</code> and/or <code>TITANGO_FORCE_ENABLED</code>.
      </p>

      <div class="alert alert-info">
        <div class="fw-bold mb-2">Speech Recognition Language</div>
        <div class="small text-muted">Current: <code>{{ $speechLang }}</code> (set via <code>TITANGO_SPEECH_LANG</code>)</div>
      </div>

      <form method="post" action="{{ route('dashboard.admin.titango.settings.update') }}">
        @csrf
        <button class="btn btn-primary" type="submit">Save</button>
      </form>
    </div>
  </div>
</div>
@endsection
