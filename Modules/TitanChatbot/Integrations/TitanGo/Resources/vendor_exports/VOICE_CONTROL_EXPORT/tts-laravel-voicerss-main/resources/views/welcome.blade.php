<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laravel + Voice RSS</title>
  <style>
    body {
      background: #f3f4f6;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .container {
      background: #fff;
      padding: 32px;
      border-radius: 16px;
      width: 100%;
      max-width: 720px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }
    h1 {
      font-size: 1.8rem;
      font-weight: bold;
      margin-bottom: 8px;
      color: #111827;
    }
    p {
      color: #6b7280;
      margin-bottom: 20px;
    }
    textarea, select {
      width: 100%;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      padding: 12px;
      margin-top: 8px;
      font-size: 1rem;
      outline: none;
    }
    textarea:focus, select:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.2);
    }
    .btn {
      background: #2563eb;
      color: white;
      border: none;
      padding: 12px 18px;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 16px;
      transition: background 0.2s ease;
    }
    .btn:hover {
      background: #1d4ed8;
    }
    audio {
      width: 100%;
      margin-top: 16px;
    }
    .error {
      background: #fee2e2;
      border: 1px solid #fecaca;
      color: #991b1b;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 16px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Text-to-Speech (Voice RSS)</h1>
    <p>Digite um texto e gere o áudio em MP3 com a API Voice RSS.</p>

    @if ($errors->any())
      <div class="error">
        @foreach ($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('speak') }}">
      @csrf
      <label for="text">Texto:</label>
      <textarea name="text" id="text" rows="4" required maxlength="1000">{{ old('text', $text ?? '') }}</textarea>

      <label for="hl">Idioma:</label>
      <select name="hl" id="hl">
        @php $current = $hl ?? old('hl','pt-br'); @endphp
        <option value="pt-br" {{ $current=='pt-br'?'selected':'' }}>Português (Brasil)</option>
        <option value="en-us" {{ $current=='en-us'?'selected':'' }}>English (US)</option>
        <option value="es-mx" {{ $current=='es-mx'?'selected':'' }}>Español (MX)</option>
      </select>

      <button class="btn">Converter</button>
    </form>

    @isset($audioUrl)
      <h2 style="margin-top:24px;color:#111827;">Resultado:</h2>
      <audio controls>
        <source src="{{ $audioUrl }}" type="audio/mpeg">
      </audio>
      <a href="{{ $download }}" download class="btn" style="display:inline-block;margin-top:12px;">Baixar MP3</a>
      <p style="font-size:0.8rem;color:#6b7280;margin-top:8px;">
        Arquivo salvo em <code>storage/app/public/tts</code>
      </p>
    @endisset
  </div>
</body>
</html>
