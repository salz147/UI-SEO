@extends('layouts.app')

@section('title', 'Edit SEO')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Edit SEO: {{ $seo->domain }}</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <h4>Validation Error:</h4>
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Data SEO</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('seo.update', $seo) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="marketing_id">Tim Marketing</label>
                                <select class="form-control" id="marketing_id" name="marketing_id" data-selected-conversation="{{ old('conversation_id', $seo->conversation_id) }}">
                                    <option value="">-- Pilih Tim Marketing --</option>
                                    @foreach($marketings as $marketing)
                                        <option value="{{ $marketing->id }}" {{ old('marketing_id', $seo->conversation?->user_id) == $marketing->id ? 'selected' : '' }}>
                                            {{ $marketing->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="conversation_id">Nama Klien / Chat Smartchat</label>
                                <select class="form-control @error('conversation_id') is-invalid @enderror" id="conversation_id" name="conversation_id">
                                    @if($seo->conversation)
                                        <option value="{{ $seo->conversation->id }}" selected>
                                            {{ $seo->conversation->nama ?? ('Conversation #' . $seo->conversation->id) }}
                                        </option>
                                    @else
                                        <option value="">-- Pilih marketing dulu --</option>
                                    @endif
                                </select>
                                @error('conversation_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="domain">Domain</label>
                                <input type="text" class="form-control @error('domain') is-invalid @enderror"
                                       id="domain" name="domain" value="{{ old('domain', $seo->domain) }}">
                                @error('domain') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="user_id">Tim SEO</label>
                                <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                    @foreach($timSeos as $tim)
                                        <option value="{{ $tim->id }}" {{ old('user_id', $seo->user_id) == $tim->id ? 'selected' : '' }}>
                                            {{ $tim->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="package">Package</label>
                                @php
                                    $selectedPackage = old('package', $seo->package);
                                    $packageOptions = collect($packageOptions ?? []);
                                @endphp
                                <select class="form-control @error('package') is-invalid @enderror" id="package" name="package">
                                    @foreach($packageOptions as $package)
                                        <option value="{{ $package }}" {{ $selectedPackage === $package ? 'selected' : '' }}>
                                            {{ $package }}
                                        </option>
                                    @endforeach
                                    @if($selectedPackage && !$packageOptions->contains($selectedPackage))
                                        <option value="{{ $selectedPackage }}" selected>{{ $selectedPackage }}</option>
                                    @endif
                                </select>
                                @error('package') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label for="bill_amount">Biaya (Rp)</label>
                                <input type="number" step="0.01" class="form-control @error('bill_amount') is-invalid @enderror"
                                       id="bill_amount" name="bill_amount" value="{{ old('bill_amount', $seo->bill_amount) }}">
                                @error('bill_amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label for="month_bill_at">Tanggal Billing</label>
                                <input type="date" class="form-control @error('month_bill_at') is-invalid @enderror"
                                       id="month_bill_at" name="month_bill_at" value="{{ old('month_bill_at', $seo->month_bill_at) }}">
                                @error('month_bill_at') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $seo->notes) }}</textarea>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', $seo->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Aktifkan SEO</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Periode SEO ({{ $seo->seo_periods->count() }})</h5>
                </div>
                <div class="card-body">
                    @php
                        $todayTimestamp = now()->startOfDay()->timestamp;
                        $primaryActivePeriod = $seo->seo_periods
                            ->where('is_billing_schedule', true)
                            ->filter(fn ($period) => $period->sort_timestamp <= $todayTimestamp)
                            ->sortByDesc('sort_timestamp')
                            ->first()
                            ?? $seo->seo_periods
                                ->where('is_billing_schedule', true)
                                ->sortBy('sort_timestamp')
                                ->first();

                        $orderedPeriods = $seo->seo_periods->sortByDesc(function ($period) use ($primaryActivePeriod) {
                            $activePriority = $primaryActivePeriod && $period->id === $primaryActivePeriod->id ? 10000000000 : 0;

                            return $activePriority + $period->sort_timestamp;
                        });
                    @endphp

                    @if($orderedPeriods->count() > 0)
                        <div class="list-group">
                            @foreach($orderedPeriods as $period)
                                @php
                                    $periodDate = \Carbon\Carbon::createFromTimestamp($period->sort_timestamp)->startOfDay();
                                    $today = now()->startOfDay();

                                    if ($primaryActivePeriod && $period->id === $primaryActivePeriod->id) {
                                        $scheduleLabel = 'Aktif';
                                        $scheduleBadge = 'success';
                                    } elseif ($periodDate->lt($today)) {
                                        $scheduleLabel = 'Lewat Tempo';
                                        $scheduleBadge = 'danger';
                                    } else {
                                        $scheduleLabel = 'Terjadwal';
                                        $scheduleBadge = 'secondary';
                                    }
                                @endphp
                                <a href="{{ route('seo.period.edit', $period) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong>{{ $period->display_date }}</strong>
                                        <span class="badge badge-{{ $scheduleBadge }}">{{ $scheduleLabel }}</span>
                                    </div>
                                    <small class="period-meta">
                                        <span>Bulan: {{ $period->display_month }}</span>
                                        <span>Items: {{ $period->seo_items->count() }}</span>
                                        @if($period->is_paid)
                                            <span class="text-success">Pembayaran: Dibayar</span>
                                        @elseif($periodDate->lt($today))
                                            <span class="text-warning">Pembayaran: Perlu Dicek</span>
                                        @else
                                            <span class="text-danger">Pembayaran: Belum Dibayar</span>
                                        @endif
                                        @if($period->is_followed_up)
                                            <span class="text-success">Laporan: Sudah Dikirim</span>
                                        @else
                                            <span class="text-muted">Laporan: Belum Dikirim</span>
                                        @endif
                                    </small>
                                    <div class="mt-2">
                                        <span class="btn btn-sm btn-info">
                                            <i class="fas fa-photo-video"></i> Media / Laporan
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Tidak ada periode</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Tambah Periode</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('seo.period.store', $seo) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="start_date">Tanggal Periode Pertama</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="month_reserved">Jumlah Periode</label>
                            <input type="number" class="form-control" id="month_reserved" name="month_reserved" value="{{ old('month_reserved', 1) }}" min="1" max="24" required>
                        </div>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="new_is_billing_schedule" name="is_billing_schedule" value="1" {{ old('is_billing_schedule') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="new_is_billing_schedule">Jadikan periode pertama sebagai aktif</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="new_is_paid" name="is_paid" value="1" {{ old('is_paid') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="new_is_paid">Tandai sudah dibayar</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-plus"></i> Tambah Periode
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .period-meta {
        display: grid;
        gap: 2px;
        margin-top: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var marketingSelect = document.getElementById('marketing_id');
        var conversationSelect = document.getElementById('conversation_id');

        if (!marketingSelect || !conversationSelect) {
            return;
        }

        function setConversationOptions(items, selectedId) {
            conversationSelect.innerHTML = '<option value="">-- Pilih Nama Klien / Chat --</option>';

            items.forEach(function (item) {
                var option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.label || ('Conversation #' + item.id);
                option.selected = String(item.id) === String(selectedId || '');
                conversationSelect.appendChild(option);
            });

            if (items.length === 0) {
                conversationSelect.innerHTML = '<option value="">Tidak ada conversation</option>';
            }
        }

        function loadConversations() {
            var marketingId = marketingSelect.value;
            var selectedId = marketingSelect.dataset.selectedConversation;

            if (!marketingId) {
                conversationSelect.innerHTML = '<option value="">-- Pilih marketing dulu --</option>';
                return;
            }

            conversationSelect.innerHTML = '<option value="">Memuat conversation...</option>';

            fetch('/get-conv-from-marketing/' + marketingId)
                .then(function (response) { return response.json(); })
                .then(function (items) {
                    setConversationOptions(items, selectedId);
                    marketingSelect.dataset.selectedConversation = '';
                })
                .catch(function () {
                    conversationSelect.innerHTML = '<option value="">Gagal memuat conversation</option>';
                });
        }

        marketingSelect.addEventListener('change', loadConversations);

        if (marketingSelect.value) {
            loadConversations();
        }
    })();
</script>
@endpush
