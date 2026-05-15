@extends('layouts.app')

@section('title', 'Lihat Items SEO')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Items: {{ $period->display_date }}</h1>
            <p class="text-muted">Domain: {{ $period->seo_main->domain }}</p>
        </div>
        <div class="col-md-4 text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadReportModal">
                <i class="fas fa-upload"></i> Upload Media
            </button>
            <form action="{{ route('seo.report.send', $period) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Kirim Laporan
                </button>
            </form>
            <a href="{{ route('seo.edit', $period->seo_main) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Alerts --}}
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

    {{-- Upload Modal --}}
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

    {{-- Items List --}}
    <div class="card">
        <div class="card-header">
            <h5>Daftar Items ({{ $items->total() }} total)</h5>
        </div>
        <div class="card-body">
            @if($items->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>File</th>
                                <th>Tipe</th>
                                <th>Preview</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @php
                                    $mediaUrl = $item->resolved_media_url;
                                    $mediaKind = $item->media_kind;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $item->media_display_name }}</strong>
                                        @if(!$item->media_exists_locally && !config('services.seo_media_base_url') && $mediaUrl)
                                            <br><small class="text-muted">File relatif. Pastikan file ada di storage lokal.</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ ucwords($item->type ?? '-') }}</span>
                                    </td>
                                    <td class="media-preview-cell">
                                        @if($mediaUrl && $mediaKind === 'image')
                                            <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">
                                                <img src="{{ $mediaUrl }}" alt="{{ $item->type }}" class="media-thumb">
                                            </a>
                                        @elseif($mediaUrl && $mediaKind === 'pdf')
                                            <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        @elseif($mediaUrl && $mediaKind === 'video')
                                            <video class="media-video" controls preload="metadata">
                                                <source src="{{ $mediaUrl }}">
                                            </video>
                                        @elseif($mediaUrl)
                                            <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file"></i> File
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($mediaUrl)
                                            <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        @endif
                                        <form action="{{ route('seo.item.delete', $item) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="fas fa-trash"></i>
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
                    {{ $items->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    Tidak ada items untuk periode ini
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .media-preview-cell {
        min-width: 160px;
    }
    .media-thumb {
        width: 140px;
        max-height: 110px;
        object-fit: contain;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background: #fff;
    }
    .media-video {
        width: 180px;
        max-height: 120px;
        border-radius: 4px;
        background: #000;
    }
</style>
@endpush
