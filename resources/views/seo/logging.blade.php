@extends('layouts.app')

@section('title', 'Logging')

@push('styles')
<style>
    .logging-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .logging-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .logging-controls .form-control {
        width: auto;
        min-width: 160px;
    }

    .log-panel {
        height: calc(100vh - 150px);
        min-height: 420px;
        overflow: auto;
        padding: 14px;
        border: 1px solid var(--app-border);
        border-radius: 8px;
        background: #ffffff;
        color: #0f1720;
        font-family: Consolas, "Courier New", monospace;
        font-size: 13px;
        line-height: 1.55;
        white-space: pre-wrap;
        word-break: break-word;
    }

    html[data-theme="dark"] .log-panel {
        background: #101418;
        color: #d9eef5;
    }

    .log-meta {
        color: var(--app-muted);
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="logging-toolbar">
        <div>
            <h1 class="mb-1">Application Logs</h1>
            @if($selectedLog)
                <div class="log-meta">
                    {{ $selectedLog['name'] }} · {{ number_format($selectedLog['size'] / 1024, 1) }} KB ·
                    updated {{ $selectedLog['modified']->format('Y-m-d H:i:s') }}
                </div>
            @endif
        </div>

        <form action="{{ route('seo.logging') }}" method="GET" class="logging-controls">
            <select name="file" class="form-control" aria-label="Pilih file log">
                @foreach($logFiles as $logFile)
                    <option value="{{ $logFile['name'] }}" @selected($selectedLog && $selectedLog['name'] === $logFile['name'])>
                        {{ $logFile['name'] }}
                    </option>
                @endforeach
            </select>
            <select name="lines" class="form-control" aria-label="Jumlah baris">
                @foreach([200, 500, 1000, 2000] as $option)
                    <option value="{{ $option }}" @selected($lineCount === $option)>{{ $option }} lines</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Refresh</button>
        </form>
    </div>

    <pre class="log-panel">{{ $logContent !== '' ? $logContent : 'Belum ada log yang bisa ditampilkan.' }}</pre>
</div>
@endsection
