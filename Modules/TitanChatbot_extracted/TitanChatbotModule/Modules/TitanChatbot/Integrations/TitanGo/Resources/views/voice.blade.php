@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Titan Go — Voice</h4>
    <span class="badge bg-dark">Powered by Titan Zero™</span>
  </div>

  <div class="card">
    <div class="card-body">
      <p class="text-muted mb-3">
        Voice control triggers Titan Zero actions. Nothing runs until you confirm.
      </p>

      <div class="mb-3">
        <button id="tg_start" class="btn btn-primary">🎙 Start</button>
        <button id="tg_stop" class="btn btn-outline-secondary" disabled>Stop</button>
        <span id="tg_state" class="ms-2 text-muted small"></span>
      </div>

      <div class="mb-3">
        <label class="form-label">Transcript</label>
        <textarea id="tg_transcript" class="form-control" rows="4" placeholder="Speak or type…"></textarea>
        <small id="tg_status" class="text-muted"></small>
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Context (optional)</label>
          <select id="tg_record_type" class="form-select">
            <option value="">No context</option>
            <option value="job">Job</option>
            <option value="quote">Quote</option>
            <option value="client">Client</option>
          </select>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Record ID (optional)</label>
          <input id="tg_record_id" type="number" class="form-control" placeholder="123">
        </div>
      </div>

      <div class="mb-3">
        <button id="tg_preview" class="btn btn-success">Preview Action</button>
      </div>

      <div id="tg_preview_box" class="alert alert-info d-none">
        <div class="fw-bold mb-2">Proposed Action</div>
        <pre id="tg_preview_json" class="mb-2"></pre>
        <button id="tg_confirm" class="btn btn-danger">Confirm & Run</button>
        <button id="tg_cancel" class="btn btn-outline-secondary ms-2">Cancel</button>
      </div>

      <div id="tg_result_box" class="alert alert-secondary d-none mt-3">
        <div class="fw-bold mb-2">Result</div>
        <pre id="tg_result_json" class="mb-0"></pre>
        <div class="mt-2">
          <button id="tg_speak" class="btn btn-outline-dark btn-sm d-none">🔊 Speak Result</button>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-3 text-muted small">
    Try: “create site diary”, “scan compliance”, “summarise job”, “quote risk scan”.
  </div>
</div>

@endsection
