@extends('panel.layout.app')

@section('title', 'Chatbot Automation')

@section('content')
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">
                            Automation for: {{ $chatbot->title ?? $chatbot->name ?? $chatbot->uuid }}
                        </h3>
                        <p class="text-muted mb-3">
                            Manage workflows, tools, and the external tool gateway for this chatbot. Execution is webhook-first.
                        </p>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <ul class="nav nav-tabs" data-bs-toggle="tabs">
                            <li class="nav-item">
                                <a href="#tab-workflows" class="nav-link active" data-bs-toggle="tab">Workflows</a>
                            </li>
                            <li class="nav-item">
                                <a href="#tab-tools" class="nav-link" data-bs-toggle="tab">Tools</a>
                            </li>
                            <li class="nav-item">
                                <a href="#tab-gateway" class="nav-link" data-bs-toggle="tab">Gateway</a>
                            </li>
                            <li class="nav-item">
                                <a href="#tab-runs" class="nav-link" data-bs-toggle="tab">Runs</a>
                            </li>
                        </ul>

                        <form method="POST" action="{{ route('dashboard.chatbot.automation.update', $chatbot) }}" class="mt-3">
                            @csrf

                            <div class="tab-content">
                                <div class="tab-pane active show" id="tab-workflows">
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                            <tr>
                                                <th style="width: 60px;">On</th>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Confirmation</th>
                                                <th>Key</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($workflowRows as $row)
                                                <tr>
                                                    <td>
                                                        <label class="form-check form-switch m-0">
                                                            <input class="form-check-input" type="checkbox" name="workflows[{{ $row['key'] }}]" value="1" @if($row['enabled']) checked @endif>
                                                        </label>
                                                    </td>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td><span class="badge bg-secondary">{{ $row['category'] }}</span></td>
                                                    <td>
                                                        @if($row['requires_confirmation'])
                                                            <span class="badge bg-warning">Confirm</span>
                                                        @else
                                                            <span class="badge bg-success">Auto</span>
                                                        @endif
                                                    </td>
                                                    <td><code>{{ $row['key'] }}</code></td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab-tools">
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                            <tr>
                                                <th style="width: 60px;">On</th>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Confirmation</th>
                                                <th>Description</th>
                                                <th>Key</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($toolRows as $row)
                                                <tr>
                                                    <td>
                                                        <label class="form-check form-switch m-0">
                                                            <input class="form-check-input" type="checkbox" name="tools[{{ $row['key'] }}]" value="1" @if($row['enabled']) checked @endif>
                                                        </label>
                                                    </td>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td><span class="badge bg-secondary">{{ $row['category'] }}</span></td>
                                                    <td>
                                                        @if($row['requires_confirmation'])
                                                            <span class="badge bg-warning">Confirm</span>
                                                        @else
                                                            <span class="badge bg-success">Auto</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-muted">{{ $row['description'] }}</td>
                                                    <td><code>{{ $row['key'] }}</code></td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab-gateway">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">External Endpoint URL (Tool Gateway)</label>
                                            <input type="text" class="form-control" name="gateway[external_endpoint_url]" value="{{ old('gateway.external_endpoint_url', $chatbot->external_endpoint_url ?? '') }}" placeholder="https://your-saas.com/api/tool-gateway">
                                            <div class="form-hint">All workflow tools (webhook-first) post to this endpoint unless a step overrides the URL.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Auth Type</label>
                                            <select class="form-select" name="gateway[external_auth_type]">
                                                @php($auth = old('gateway.external_auth_type', $chatbot->external_auth_type ?? ''))
                                                <option value="" @if($auth==='') selected @endif>None</option>
                                                <option value="bearer" @if($auth==='bearer') selected @endif>Bearer</option>
                                                <option value="header" @if($auth==='header') selected @endif>Header Token</option>
                                            </select>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Auth Token</label>
                                            <input type="text" class="form-control" name="gateway[external_auth_token]" value="{{ old('gateway.external_auth_token', $chatbot->external_auth_token ?? '') }}" placeholder="token">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">HMAC Signing Secret</label>
                                            <input type="text" class="form-control" name="gateway[external_signing_secret]" value="{{ old('gateway.external_signing_secret', $chatbot->external_signing_secret ?? '') }}" placeholder="optional">
                                            <div class="form-hint">If set, requests include X-Workflow-Signature.</div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Timeout (ms)</label>
                                            <input type="number" class="form-control" name="gateway[external_timeout_ms]" value="{{ old('gateway.external_timeout_ms', $chatbot->external_timeout_ms ?? 15000) }}" min="1000" step="500">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Rate limit / min</label>
                                            <input type="number" class="form-control" name="gateway[limit_per_minute]" value="{{ old('gateway.limit_per_minute', $chatbot->limit_per_minute ?? 60) }}" min="1" step="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab-runs">
                                    <p class="text-muted">Latest workflow runs (most recent first).</p>
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Workflow</th>
                                                <th>Status</th>
                                                <th>Conversation</th>
                                                <th>Created</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($runs as $run)
                                                <tr>
                                                    <td><code>{{ $run->id }}</code></td>
                                                    <td><code>{{ $run->workflow_key }}</code></td>
                                                    <td>
                                                        @php($st = (string)$run->status)
                                                        <span class="badge @if($st==='executed') bg-success @elseif($st==='failed') bg-danger @elseif($st==='confirmed') bg-warning @else bg-secondary @endif">{{ $st }}</span>
                                                    </td>
                                                    <td>{{ $run->conversation_id }}</td>
                                                    <td>{{ $run->created_at }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-muted">No runs yet.</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a class="btn btn-outline-secondary" href="{{ route('dashboard.chatbot.index') }}">Back</a>
                                <a class="btn btn-outline-secondary" href="{{ route('dashboard.chatbot.workflows.index', $chatbot) }}">Legacy Workflows Page</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
