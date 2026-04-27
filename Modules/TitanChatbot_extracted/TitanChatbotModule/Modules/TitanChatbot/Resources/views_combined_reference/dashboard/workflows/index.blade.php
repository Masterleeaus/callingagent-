@extends('panel.layout.app')

@section('title', 'Chatbot Workflows')

@section('content')
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">
                            Workflows for: {{ $chatbot->title ?? $chatbot->name ?? $chatbot->uuid }}
                        </h3>
                        <p class="text-muted">
                            Enable only the workflows you want this chatbot to offer. Execution is webhook-first and will call the External Endpoint when workflows are run.
                        </p>
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="POST" action="{{ route('dashboard.chatbot.workflows.update', $chatbot) }}">
                            @csrf
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
                                        @foreach($rows as $row)
                                            <tr>
                                                <td>
                                                    <label class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" name="enabled[{{ $row['key'] }}]" value="1" @if($row['enabled']) checked @endif>
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
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a class="btn btn-outline-secondary" href="{{ route('dashboard.chatbot.index') }}">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
