@extends('layouts.admin')

@section('title', 'Edinme Kaynağı Ekle')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ $book->title }} - Edinme Kaynağı Ekle</h6>
            <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kitap Detayına Dön
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.books.store-acquisition', $book) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="source_type">Edinme Türü <span class="text-danger">*</span></label>
                            <select class="form-control @error('source_type') is-invalid @enderror" 
                                id="source_type" name="source_type" required>
                                <option value="">-- Edinme Türü Seçin --</option>
                                @foreach($sourceTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('source_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('source_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="source_name">Kaynak Adı</label>
                            <input type="text" class="form-control @error('source_name') is-invalid @enderror" 
                                id="source_name" name="source_name" value="{{ old('source_name') }}"
                                placeholder="Bağışçı adı, satın alınan yer vs.">
                            @error('source_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="quantity">Miktar <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required min="1">
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="acquisition_date">Edinme Tarihi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('acquisition_date') is-invalid @enderror" 
                                id="acquisition_date" name="acquisition_date" 
                                value="{{ old('acquisition_date', date('Y-m-d')) }}" required>
                            @error('acquisition_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group purchase-field d-none">
                            <label for="price">Fiyat</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                    id="price" name="price" value="{{ old('price') }}" step="0.01" min="0">
                                <div class="input-group-append">
                                    <span class="input-group-text">₺</span>
                                </div>
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group purchase-field d-none">
                            <label for="invoice_number">Fatura Numarası</label>
                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}">
                            @error('invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_info">İletişim Bilgileri</label>
                            <input type="text" class="form-control @error('contact_info') is-invalid @enderror" 
                                id="contact_info" name="contact_info" value="{{ old('contact_info') }}">
                            @error('contact_info')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="document_file">İlgili Belge</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('document_file') is-invalid @enderror" 
                                    id="document_file" name="document_file">
                                <label class="custom-file-label" for="document_file">Dosya seçin...</label>
                                @error('document_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                Fatura, bağış belgesi vb. (PDF, JPG, PNG - max: 2MB)
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notlar</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Edinme türüne göre alanları göster/gizle
    $('#source_type').change(function() {
        if ($(this).val() === 'purchase') {
            $('.purchase-field').removeClass('d-none');
        } else {
            $('.purchase-field').addClass('d-none');
        }
    });

    // Dosya seçildiğinde dosya adını göster
    $('#document_file').change(function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // Sayfa yüklendiğinde edinme türü seçili ise kontrol et
    if ($('#source_type').val() === 'purchase') {
        $('.purchase-field').removeClass('d-none');
    }
});
</script>
@endsection 