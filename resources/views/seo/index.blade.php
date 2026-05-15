@extends('layouts.app')

@section('title', 'Daftar SEO')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-3 align-items-center">
        <div class="col-lg-3 col-md-12 mb-2 mb-lg-0">
            <h1>Manajemen SEO</h1>
        </div>
        <div class="col-lg-9 col-md-12">
            <div class="top-actions">
                <form action="{{ route('seo.index') }}" method="GET" class="top-search">
                    <div class="search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Cari domain, nama klien, tim, atau package">
                    </div>
                    @if($focus)
                        <input type="hidden" name="focus" value="{{ $focus }}">
                    @endif
                    <button type="submit" class="btn btn-primary">Cari</button>
                    @if($search)
                        <a href="{{ route('seo.index', array_filter(['focus' => $focus])) }}" class="btn btn-secondary">Reset</a>
                    @endif
                </form>
                <a href="{{ route('seo.create') }}" class="btn btn-primary add-seo-btn">
                    <i class="fas fa-plus"></i> Tambah SEO Baru
                </a>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    @if(isset($summary))
    <div class="row mb-3 stat-row">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Total SEO</h5>
                    <h2>{{ $summary['total_seo'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Perlu Dicek</h5>
                    <h2>{{ $summary['needs_review'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Belum Kirim Laporan</h5>
                    <h2>{{ $summary['report_pending'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Periode Aktif</h5>
                    <h2>{{ $summary['active_now'] }}</h2>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Alerts --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- SEO Table --}}
    <div class="card">
        <div class="card-header">
            <h5>Daftar SEO Aktif</h5>
        </div>
        <div class="card-body">
            <div class="filter-pills">
                <a href="{{ route('seo.index', array_filter(['search' => $search])) }}" class="filter-pill {{ $focus === '' ? 'active' : '' }}">Semua</a>
                <a href="{{ route('seo.index', array_filter(['search' => $search, 'focus' => 'needs_review'])) }}" class="filter-pill {{ $focus === 'needs_review' ? 'active' : '' }}">Perlu Dicek</a>
                <a href="{{ route('seo.index', array_filter(['search' => $search, 'focus' => 'report_pending'])) }}" class="filter-pill {{ $focus === 'report_pending' ? 'active' : '' }}">Belum Kirim Laporan</a>
                <a href="{{ route('seo.index', array_filter(['search' => $search, 'focus' => 'active_now'])) }}" class="filter-pill {{ $focus === 'active_now' ? 'active' : '' }}">Periode Aktif</a>
                <a href="{{ route('seo.index', array_filter(['search' => $search, 'focus' => 'due_soon'])) }}" class="filter-pill {{ $focus === 'due_soon' ? 'active' : '' }}">7 Hari ke Depan</a>
            </div>
            @if($seos->count() > 0)
                @if($search)
                    <p class="text-muted mb-3">Hasil pencarian untuk <strong>{{ $search }}</strong>: {{ $seos->total() }} data</p>
                @elseif($focus)
                    <p class="text-muted mb-3">Filter aktif: <strong>{{ str_replace('_', ' ', $focus) }}</strong></p>
                @endif
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Domain</th>
                                <th>Nama Klien</th>
                                <th>Tim Marketing</th>
                                <th>Tim SEO</th>
                                <th>Package</th>
                                <th>Biaya</th>
                                <th>Periode Aktif</th>
                                <th>Total Items</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seos as $seo)
                                <tr>
                                    <td><strong>{{ $seo->domain }}</strong></td>
                                    <td>
                                        {{ $seo->conversation?->nama ?? 'Belum Ada' }}
                                    </td>
                                    <td>{{ $seo->conversation?->marketing?->name ?? '-' }}</td>
                                    <td>{{ $seo->user->name ?? '-' }}</td>
                                    <td>
                                        @php $packageLabel = trim((string) $seo->package) ?: '-'; @endphp
                                        <span class="badge badge-info package-badge" title="{{ $packageLabel }}">
                                            {{ $packageLabel }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($seo->bill_amount, 0, ',', '.') }}</td>
                                    <td>
                                        {{ $seo->seo_periods->count() }} periode
                                        @php
                                            $activePeriod = $seo->seo_periods->where('is_billing_schedule', true)
                                                ->sortByDesc('sort_timestamp')
                                                ->first()
                                                ?? $seo->seo_periods->sortByDesc('sort_timestamp')->first();
                                        @endphp
                                        @if($activePeriod)
                                            <br><small class="text-success">Aktif: {{ $activePeriod->display_date }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $totalItems = $seo->seo_periods->sum(function($period) {
                                                return $period->seo_items->count();
                                            });
                                        @endphp
                                        {{ $totalItems }}
                                    </td>
                                    <td>
                                        @if($seo->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('seo.edit', $seo) }}" class="btn btn-sm btn-warning action-icon-btn" title="Detail SEO" aria-label="Detail SEO {{ $seo->domain }}">
                                            <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M4 20h4.7L19.4 9.3a1.8 1.8 0 0 0 0-2.5l-2.2-2.2a1.8 1.8 0 0 0-2.5 0L4 15.3V20Zm2-2v-1.9l10-10 1.9 1.9-10 10H6Z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('seo.local.hide', $seo) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button class="btn btn-sm btn-danger action-icon-btn" title="Hapus dari tampilan lokal" aria-label="Hapus {{ $seo->domain }} dari tampilan lokal" onclick="return confirm('Sembunyikan data ini dari localhost? Database tidak akan diubah.')">
                                                <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M7 21c-1.1 0-2-.9-2-2V8H4V6h5V4h6v2h5v2h-1v11c0 1.1-.9 2-2 2H7ZM7 8v11h10V8H7Zm2 9h2v-7H9v7Zm4 0h2v-7h-2v7Z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-4">
                    {{ $seos->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    Tidak ada data SEO aktif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .container-fluid.mt-4 {
        margin-top: 1rem !important;
    }
    .stat-row {
        margin-left: -8px;
        margin-right: -8px;
    }
    .stat-row > [class*="col-"] {
        padding-left: 8px;
        padding-right: 8px;
    }
    .stat-card .card-body {
        min-height: 78px;
        padding: 14px 16px;
    }
    .stat-card .card-title {
        margin-bottom: 8px;
    }
    .filter-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .filter-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--app-border);
        color: var(--app-muted);
        background: var(--app-surface-soft);
        font-size: 12px;
        font-weight: 600;
    }
    .filter-pill:hover {
        color: var(--app-text);
        text-decoration: none;
    }
    .filter-pill.active {
        color: #fff;
        background: var(--app-accent);
        border-color: var(--app-accent);
    }
    .badge { padding: 5px 10px; }
    .package-badge {
        display: inline-block;
        max-width: 170px;
        white-space: normal;
        line-height: 1.35;
        text-align: center;
    }
    .pagination {
        flex-wrap: wrap;
        margin-bottom: 0;
    }
    .pagination .page-link {
        min-width: 38px;
        text-align: center;
    }
    .table th,
    .table td {
        white-space: nowrap;
        font-size: 12.5px;
    }
    .table thead th {
        text-align: center;
        background: #143745 !important;
        color: #f4fdff !important;
        border-color: #1d4d5e !important;
    }
    html[data-theme="light"] .table thead th {
        background: #d8eef5 !important;
        color: #12313b !important;
        border-color: #b7d8e2 !important;
    }
    .table tbody td {
        text-align: center;
    }
    .table tbody td:first-child,
    .table tbody td:nth-child(2),
    .table tbody td:nth-child(5) {
        text-align: left;
    }
    .table td:first-child,
    .table td:nth-child(2),
    .table td:nth-child(5) {
        white-space: normal;
        min-width: 130px;
    }
    .action-icon-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        vertical-align: middle;
    }
    .action-icon {
        width: 18px;
        height: 18px;
        fill: currentColor;
        display: block;
    }
    .top-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: nowrap;
    }
    .top-search {
        display: flex;
        gap: 8px;
        align-items: center;
        min-width: 0;
        flex: 1;
    }
    .search-input-wrap {
        position: relative;
        flex: 1;
        min-width: 180px;
        max-width: 520px;
    }
    .search-input-wrap i {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: var(--app-muted);
        pointer-events: none;
    }
    .search-input-wrap .form-control {
        padding-left: 36px;
        height: 100%;
    }
    .add-seo-btn {
        white-space: nowrap;
    }
    @media (max-width: 991.98px) {
        .top-actions {
            justify-content: flex-start;
        }
    }
    @media (max-width: 767.98px) {
        .top-actions,
        .top-search {
            flex-direction: column;
            align-items: stretch;
            min-width: 100%;
        }
    }
</style>
@endpush
