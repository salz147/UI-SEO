@extends('layouts.app')

@section('title', 'Tambah SEO Baru')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Tambah SEO Baru</h1>
        </div>
    </div>

    {{-- Errors --}}
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

    <div class="card">
        <div class="card-body">
            <form action="{{ route('seo.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="marketing_id">Tim Marketing</label>
                        <select class="form-control" id="marketing_id" name="marketing_id" data-selected-conversation="{{ old('conversation_id') }}">
                            <option value="">-- Pilih Tim Marketing --</option>
                            @foreach($marketings as $marketing)
                                <option value="{{ $marketing->id }}" {{ old('marketing_id') == $marketing->id ? 'selected' : '' }}>
                                    {{ $marketing->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="conversation_id">Nama Klien / Chat Smartchat</label>
                        <select class="form-control @error('conversation_id') is-invalid @enderror" id="conversation_id" name="conversation_id">
                            <option value="">-- Pilih marketing dulu --</option>
                        </select>
                        <small class="form-text text-muted">Nama klien diambil dari conversation milik Tim Marketing.</small>
                        @error('conversation_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="domain">Domain <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                               id="domain" name="domain" placeholder="example.com" value="{{ old('domain') }}">
                        @error('domain') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="user_id">Tim SEO <span class="text-danger">*</span></label>
                        <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                            <option value="">-- Pilih Tim SEO --</option>
                            @foreach($timSeos as $tim)
                                <option value="{{ $tim->id }}" {{ old('user_id') == $tim->id ? 'selected' : '' }}>
                                    {{ $tim->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="package">Package <span class="text-danger">*</span></label>
                        @php
                            $selectedPackage = old('package');
                            $packageOptions = collect($packageOptions ?? []);
                        @endphp
                        <select class="form-control @error('package') is-invalid @enderror" id="package" name="package">
                            <option value="">-- Pilih Package --</option>
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
                        <label for="bill_amount">Biaya (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('bill_amount') is-invalid @enderror" 
                               id="bill_amount" name="bill_amount" value="{{ old('bill_amount') }}">
                        @error('bill_amount') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label for="month_bill_at">Tanggal Billing <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('month_bill_at') is-invalid @enderror" 
                               id="month_bill_at" name="month_bill_at" value="{{ old('month_bill_at', now()->toDateString()) }}">
                        @error('month_bill_at') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="month_reserved">Bulan Pemesanan <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('month_reserved') is-invalid @enderror" 
                               id="month_reserved" name="month_reserved" min="1" max="24" value="{{ old('month_reserved', 1) }}">
                        <small class="form-text text-muted">Jumlah bulan yang akan dibuat periode</small>
                        @error('month_reserved') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="starting_month">Bulan Mulai <span class="text-danger">*</span></label>
                        <input type="month" class="form-control @error('starting_month') is-invalid @enderror" 
                               id="starting_month" name="starting_month" value="{{ old('starting_month', now()->format('Y-m')) }}">
                        @error('starting_month') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Catatan</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">
                            Aktifkan SEO
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('seo.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

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
