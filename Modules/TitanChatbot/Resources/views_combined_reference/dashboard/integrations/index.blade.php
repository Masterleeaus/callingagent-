@extends('layouts.app')

@section('title', 'Integrations')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-1">Integrations</h3>
      <div class="text-muted">Chatbot: <strong>{{ $chatbot->name ?? ('#'.$chatbot->id) }}</strong></div>
    </div>
    <div>
      <a class="btn btn-outline-secondary" href="{{ url()->previous() }}">Back</a>
    </div>
  </div>

  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="tab-channels" data-bs-toggle="tab" data-bs-target="#pane-channels" type="button" role="tab">Channels</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="tab-connectors" data-bs-toggle="tab" data-bs-target="#pane-connectors" type="button" role="tab">Connectors</button>
    </li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="pane-channels" role="tabpanel">
      <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <strong>Messaging Channels</strong>
            <div class="text-muted small">WhatsApp, Telegram, Messenger and any other inbound/outbound chat channel.</div>
          </div>
        </div>
        <div class="card-body">
          @if(empty($channelRows))
            <div class="text-muted">No channels connected for this chatbot yet.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead>
                  <tr>
                    <th>Channel</th>
                    <th>Connected</th>
                    <th>Credentials</th>
                    <th>Meta</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($channelRows as $row)
                    <tr>
                      <td><span class="badge bg-primary">{{ $row['channel'] }}</span></td>
                      <td>
                        @if($row['connected_at'])
                          <span class="text-success">Yes</span>
                          <div class="text-muted small">{{ $row['connected_at'] }}</div>
                        @else
                          <span class="text-muted">No</span>
                        @endif
                      </td>
                      <td>
                        @if($row['has_credentials'])
                          <span class="text-success">Present</span>
                        @else
                          <span class="text-muted">Missing</span>
                        @endif
                      </td>
                      <td class="text-muted small">
                        @php($label = $row['meta']['label'] ?? '')
                        @php($account = $row['meta']['account'] ?? '')
                        @if($label) <div>Label: {{ $label }}</div> @endif
                        @if($account) <div>Account: {{ $account }}</div> @endif
                        @if(!$label && !$account) <span class="text-muted">—</span> @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

          <div class="alert alert-info mt-3 mb-0">
            <strong>Note:</strong> Channel connection details are managed via the channel-specific cards in the chatbot UI (WhatsApp/Telegram/Messenger).
            This page provides a unified overview and will be expanded with connect/test actions.
          </div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="pane-connectors" role="tabpanel">
      <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <strong>Business Connectors</strong>
            <div class="text-muted small">HubSpot, Xero, WordPress, Mailchimp, Stripe, etc.</div>
          </div>
          <div>
            @if(!empty($connectorsRouteAvailable))
              <a class="btn btn-sm btn-primary" href="{{ route('connectors.index') }}">Open Connector Manager</a>
            @else
              <button class="btn btn-sm btn-primary" disabled>Connector Manager not registered</button>
            @endif
          </div>
        </div>
        <div class="card-body">
          @if(empty($connectorsRouteAvailable))
            <div class="alert alert-warning">
              The <strong>Connectors</strong> extension routes are not currently registered in this build.
              If you have the extension loader enabled, ensure the Connectors extension is installed and its service provider is booted.
            </div>
          @endif

          @if(!empty($connectorAccounts) && count($connectorAccounts))
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead>
                  <tr>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Updated</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($connectorAccounts as $acc)
                    <tr>
                      <td><span class="badge bg-secondary">{{ $acc->provider ?? 'unknown' }}</span></td>
                      <td>{{ $acc->status ?? '—' }}</td>
                      <td class="text-muted small">{{ $acc->updated_at ?? $acc->created_at ?? '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-muted">
              No connector accounts detected (or Connectors extension not installed yet).
              Use <strong>Open Connector Manager</strong> to add HubSpot / Xero / WordPress / Mailchimp accounts.
            </div>
          @endif

          <div class="alert alert-info mt-3 mb-0">
            <strong>How this integrates with workflows/tools:</strong>
            Workflows remain <em>webhook-first</em>. Your external Tool Gateway can either call these connectors directly,
            or your Connectors extension can serve as the internal execution layer.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
