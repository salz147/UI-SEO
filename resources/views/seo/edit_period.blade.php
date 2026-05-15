@extends('layouts.app')

@section('title', 'Edit Periode SEO')

@section('content')
@php
    $periodDate = \Carbon\Carbon::createFromTimestamp($period->sort_timestamp)->startOfDay();
    $today = now()->startOfDay();
    $todayTimestamp = $today->timestamp;
    $primaryActivePeriod = $period->seo_main->seo_periods
        ->where('is_billing_schedule', true)
        ->filter(fn ($item) => $item->sort_timestamp <= $todayTimestamp)
        ->sortByDesc('sort_timestamp')
        ->first()
        ?? $period->seo_main->seo_periods
            ->where('is_billing_schedule', true)
            ->sortBy('sort_timestamp')
            ->first();
    $isPrimaryActive = $primaryActivePeriod && $primaryActivePeriod->id === $period->id;

    if ($isPrimaryActive) {
        $scheduleLabel = 'Aktif';
        $scheduleBadge = 'success';
        $scheduleText = 'Periode ini sedang aktif untuk klien ini.';
    } elseif ($periodDate->lt($today)) {
        $scheduleLabel = 'Lewat Tempo';
        $scheduleBadge = 'danger';
        $scheduleText = 'Tanggal periode sudah terlewat. Cek pembayaran dan laporan sebelum follow up.';
    } else {
        $scheduleLabel = 'Terjadwal';
        $scheduleBadge = 'secondary';
        $scheduleText = 'Periode ini belum aktif dan masih terjadwal.';
    }
@endphp

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Edit Periode: {{ $period->display_date }}</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
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

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('seo.period.update', $period) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="date">Tanggal</label>
                                <input type="number" class="form-control" id="date" name="date"
                                       value="{{ old('date', $period->date) }}" min="1" max="31">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="month">Bulan</label>
                                <input type="number" class="form-control" id="month" name="month"
                                       value="{{ old('month', $period->month) }}" min="1" max="12" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="year">Tahun</label>
                                <input type="number" class="form-control" id="year" name="year"
                                       value="{{ old('year', $period->year) }}" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Status Jadwal</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="is_billing_schedule" name="is_billing_schedule"
                                           value="1" {{ old('is_billing_schedule', $period->is_billing_schedule) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_billing_schedule">Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_paid" name="is_paid"
                                           value="1" {{ old('is_paid', $period->is_paid) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_paid">Sudah Dibayar</label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_followed_up" name="is_followed_up"
                                           value="1" {{ old('is_followed_up', $period->is_followed_up) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_followed_up">Laporan Sudah Dikirim</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $period->notes) }}</textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Periode
                            </button>
                            <a href="{{ route('seo.edit', $period->seo_main) }}" class="btn btn-secondary">
                                Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Informasi</h5>
                </div>
                <div class="card-body">
                    <p><strong>Domain:</strong> {{ $period->seo_main->domain }}</p>
                    <p><strong>Tanggal:</strong> {{ $period->display_date }}</p>
                    <p><strong>Bulan:</strong> {{ $period->display_month }}</p>
                    <p><strong>Total Items:</strong> {{ $period->seo_items->count() }}</p>
                    <p><strong>Total Traffic:</strong> {{ number_format($period->seo_items->sum('traffic'), 0, ',', '.') }}</p>
                    <p>
                        <strong>Status Jadwal:</strong>
                        <span class="badge badge-{{ $scheduleBadge }}">{{ $scheduleLabel }}</span>
                    </p>
                    <p class="text-muted mb-2">{{ $scheduleText }}</p>
                    <p>
                        <strong>Pembayaran:</strong>
                        @if($period->is_paid)
                            <span class="badge badge-success">Dibayar</span>
                        @elseif($periodDate->lt($today))
                            <span class="badge badge-warning">Perlu Dicek</span>
                        @else
                            <span class="badge badge-warning">Belum Dibayar</span>
                        @endif
                    </p>
                    <p class="mb-0">
                        <strong>Laporan:</strong>
                        @if($period->is_followed_up)
                            <span class="badge badge-success">Sudah Dikirim</span>
                        @else
                            <span class="badge badge-secondary">Belum Dikirim</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Aksi Periode Ini</h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#uploadReportModal">
                        <i class="fas fa-upload"></i> Upload Media Laporan
                    </button>
                    <a href="{{ route('seo.items.view', $period) }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-list"></i> Lihat Items
                    </a>
                    <form action="{{ route('seo.report.send', $period) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block mb-2" onclick="return confirm('Kirim laporan untuk periode {{ $period->display_date }} saja?')">
                            <i class="fas fa-paper-plane"></i> Kirim Laporan
                        </button>
                    </form>
                    @if(!$period->is_paid)
                        <form action="{{ route('seo.period.paid', $period) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-check"></i> Tandai Sebagai Dibayar
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('seo.period.delete', $period) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Yakin ingin menghapus periode ini?')">
                            <i class="fas fa-trash"></i> Hapus Periode
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadReportModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('seo.report.upload', $period) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Media Laporan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">{{ $period->seo_main->domain }} - {{ $period->display_date }}</p>
                        <div class="form-group">
                            <label for="reportType">Tipe Laporan</label>
                            <select class="form-control" id="reportType" name="type" required>
                                <option value="laporan bulanan">Laporan Bulanan</option>
                                <option value="kata kunci prioritas">Kata Kunci Prioritas</option>
                                <option value="kata kunci bonus">Kata Kunci Bonus</option>
                                <option value="laporan traffic">Laporan Traffic</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reportFile">File PDF/Gambar</label>
                            <input type="file" class="form-control-file" id="reportFile" name="report_file" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                            <small class="form-text text-muted">Format: PDF, JPG, PNG, WEBP. Maksimal 10 MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Simpan Media
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
