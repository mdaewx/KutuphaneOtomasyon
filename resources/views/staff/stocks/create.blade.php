@extends('layouts.staff')

@section('title', 'Yeni Stok Ekle')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Yeni Stok Kaydı</h1>
        <a href="{{ route('staff.stocks.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 me-1"></i> Stok Listesine Dön
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Stok Bilgileri</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('staff.stocks.store') }}" method="POST">
                @csrf
                
                <!-- ISBN veya Kitap Adı ile Ara -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="search">ISBN veya Kitap Adı ile Ara</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search" placeholder="ISBN veya kitap adı girin">
                                <button type="button" class="btn btn-primary" id="searchBook">
                                    <i class="fas fa-search"></i> Ara
                                </button>
                            </div>
                            <div id="searchResult" class="mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Kitap Detayları -->
                <div id="bookDetails" class="d-none">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="book-cover-container text-center mb-3">
                                <div id="noCover" class="no-cover-placeholder">
                                    <img src="{{ asset('images/book-default.png') }}" alt="Varsayılan Kitap Logosu" class="img-fluid" style="max-height: 200px;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Kitap Başlığı</label>
                                        <p class="form-control-static h5" id="bookTitle"></p>
                                        <input type="hidden" name="book_id" id="bookId">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Yazar(lar)</label>
                                        <p class="form-control-static" id="bookAuthors"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">ISBN</label>
                                        <p class="form-control-static" id="bookIsbn"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Kategori</label>
                                        <p class="form-control-static" id="bookCategory"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stok Bilgileri -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="barcode">Barkod <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode') }}" required>
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="shelf_id" class="form-label">Raf <span class="text-danger">*</span></label>
                            <select class="form-select" id="shelf_id" name="shelf_id" required>
                                <option value="">Raf Seçin</option>
                                @foreach($shelves as $shelf)
                                    <option value="{{ $shelf->id }}">{{ $shelf->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Stok Adedi -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="quantity">Stok Adedi</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1">
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="condition">Kitap Durumu <span class="text-danger">*</span></label>
                            <select class="form-control @error('condition') is-invalid @enderror" id="condition" name="condition" required>
                                <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>Yeni</option>
                                <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>İyi</option>
                                <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Orta</option>
                                <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Kötü</option>
                            </select>
                            @error('condition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Edinme Kaynağı Seçimi -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="acquisition_source_id">Edinme Kaynağı <span class="text-danger">*</span></label>
                            <select class="form-control @error('acquisition_source_id') is-invalid @enderror" id="acquisition_source_id" name="acquisition_source_id" required>
                                <option value="">Edinme Kaynağı Seçin</option>
                                @foreach($acquisitionSources as $source)
                                    <option value="{{ $source->id }}" {{ old('acquisition_source_id') == $source->id ? 'selected' : '' }}>
                                        {{ $source->source_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('acquisition_source_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="acquisition_date">Edinme Tarihi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('acquisition_date') is-invalid @enderror" id="acquisition_date" name="acquisition_date" value="{{ old('acquisition_date') }}" required>
                            @error('acquisition_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="acquisition_price">Edinme Fiyatı</label>
                            <input type="number" step="0.01" class="form-control @error('acquisition_price') is-invalid @enderror" id="acquisition_price" name="acquisition_price" value="{{ old('acquisition_price') }}">
                            @error('acquisition_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-1"></i> Kaydet
                    </button>
                    <a href="{{ route('staff.stocks.index') }}" class="btn btn-secondary btn-lg px-5 ms-2">
                        <i class="fas fa-times me-1"></i> İptal
                    </a>
                </div>
            </form>
            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // CSRF token ayarı
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Arama işlemi
    $('#searchBook').click(function() {
        searchBook();
    });
    
    // Enter tuşu ile arama
    $('#search').keydown(function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBook();
        }
    });
    
    function searchBook() {
        var searchTerm = $('#search').val().trim();
        if (!searchTerm) {
            showError('Lütfen ISBN veya kitap adı girin.');
            return;
        }

        // Arama düğmesini devre dışı bırak ve yükleniyor göster
        $('#searchBook').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Aranıyor...');
        
        // Sonuç alanlarını temizle
        $('#searchResult').html('');
        $('#bookDetails').addClass('d-none');

        // AJAX ile kitap ara
        $.ajax({
            url: '/staff/stocks/search',
            type: 'GET',
            data: {
                search: searchTerm
            },
            success: function(response) {
                if (response.success && response.book) {
                    // Kitap ID'sini gizli alana kaydet
                    $('#bookId').val(response.book.id);
                    
                    // Kitap bilgilerini göster
                    $('#bookTitle').text(response.book.title);
                    $('#bookAuthors').text(response.book.authors);
                    $('#bookIsbn').text(response.book.isbn);
                    $('#bookCategory').text(response.book.category);
                    
                    // Müsait kopya sayısını göster
                    var availabilityText = response.book.available_copies > 0 
                        ? response.book.available_copies + ' adet müsait kopya var'
                        : 'Müsait kopya bulunmuyor';
                    $('#searchResult').html(
                        '<div class="alert alert-info">' + availabilityText + '</div>'
                    );
                    
                    // Kapak resmini güncelle
                    if (response.book.has_cover) {
                        $('#noCover').hide();
                    } else {
                        $('#noCover').show();
                    }
                    
                    // Otomatik barkod oluştur
                    if (!$('#barcode').val()) {
                        var timestamp = new Date().getTime().toString().substr(-6);
                        $('#barcode').val(response.book.isbn + '-' + timestamp);
                    }
                    
                    // Kitap detayları bölümünü göster
                    $('#bookDetails').removeClass('d-none');
                } else {
                    showError(response.message || 'Kitap bulunamadı.');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Arama sırasında bir hata oluştu.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showError(errorMessage);
            },
            complete: function() {
                // Arama düğmesini normal haline getir
                $('#searchBook').prop('disabled', false)
                    .html('<i class="fas fa-search"></i> Ara');
            }
        });
    }

    function showError(message) {
        $('#searchResult').html(
            '<div class="alert alert-warning">' + message + '</div>'
        );
        $('#bookDetails').addClass('d-none');
    }
});
</script>
@endpush 