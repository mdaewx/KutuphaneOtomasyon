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
                                <img id="bookCover" src="{{ asset('images/icons/book-logo.png') }}" alt="Kitap Kapağı" class="img-fluid book-cover" style="max-height: 200px; display: none;">
                                <div id="noCover" class="no-cover-placeholder">
                                    <i class="fas fa-book fa-5x text-secondary"></i>
                                    <p class="mt-2">Kapak yok</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Kitap Başlığı</label>
                                        <p class="form-control-static fw-bold" id="bookTitle"></p>
                                        <input type="hidden" name="book_id" id="bookId">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Yazar(lar)</label>
                                        <p class="form-control-static" id="bookAuthors"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>ISBN</label>
                                        <p class="form-control-static" id="bookIsbn"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Yayınevi</label>
                                        <p class="form-control-static" id="bookPublisher"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kategori</label>
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
                            <label for="shelf_id">Raf <span class="text-danger">*</span></label>
                            <select class="form-control @error('shelf_id') is-invalid @enderror" id="shelf_id" name="shelf_id" required>
                                <option value="">Raf Seçin</option>
                                @foreach($shelves as $shelf)
                                    <option value="{{ $shelf->id }}" {{ old('shelf_id') == $shelf->id ? 'selected' : '' }}>
                                        {{ $shelf->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shelf_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Stok Adedi -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="quantity">Stok Adedi</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1">
                            <small class="form-text text-muted">Eklenecek kopya sayısı. Her kopya için otomatik barkod oluşturulacaktır.</small>
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
    // İşlevsel olmasını sağlamak için CSRF token'ı ekleyelim
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Bu fonksiyon otomatik barkod oluşturur
    function generateBarcode(isbn) {
        // ISBN'i barkoda dönüştür, ancak tarihi de ekleyerek benzersiz olmasını sağla
        let timestamp = new Date().getTime().toString().substr(-6);
        return isbn + '-' + timestamp;
    }
    
    // Arama işlemi
    $('#searchBook').click(function() {
        searchBook();
    });
    
    // Arama alanında Enter tuşu ile arama yapma
    $('#search').keydown(function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBook();
        }
    });
    
    function searchBook() {
        var searchTerm = $('#search').val().trim();
        if (!searchTerm) {
            alert('Lütfen ISBN veya kitap adı girin.');
            return;
        }

        // Arama düğmesini devre dışı bırak
        $('#searchBook').html('<i class="fas fa-spinner fa-spin"></i> Aranıyor...');
        $('#searchBook').prop('disabled', true);
        
        // Sonuç alanını temizle
        $('#searchResult').html('');

        // AJAX ile kitap ara
        $.ajax({
            url: '{{ route("staff.stocks.search") }}',
            type: 'GET',
            data: {
                search: searchTerm
            },
            success: function(response) {
                // Arama düğmesini normal haline getir
                $('#searchBook').html('<i class="fas fa-search"></i> Ara');
                $('#searchBook').prop('disabled', false);
                
                if (response.book) {
                    var book = response.book;
                    
                    // Kitap ID'sini gizli alana kaydet
                    $('#bookId').val(book.id);
                    
                    // Otomatik barkod oluştur ve alana yerleştir
                    if (!$('#barcode').val()) {
                        $('#barcode').val(generateBarcode(book.isbn));
                    }
                    
                    // Kitap bilgilerini göster
                    $('#bookTitle').text(book.title || 'Başlık belirtilmemiş');
                    $('#bookAuthors').text(response.authors || 'Belirtilmemiş');
                    $('#bookIsbn').text(book.isbn || 'Belirtilmemiş');
                    $('#bookPublisher').text(response.publisher || 'Belirtilmemiş');
                    $('#bookCategory').text(response.category || 'Belirtilmemiş');
                    
                    // Kapak resmi kontrolü
                    $('#bookCover').attr('src', '{{ asset('images/icons/book-logo.png') }}').show();
                    $('#noCover').hide();
                    
                    // Kitap detayları bölümünü göster
                    $('#bookDetails').removeClass('d-none');
                    
                    // Başarılı mesajı göster
                    $('#searchResult').html('<div class="alert alert-success">Kitap bulundu: ' + book.title + '</div>');
                } else {
                    // Hata mesajı göster
                    $('#searchResult').html('<div class="alert alert-danger">Kitap bulunamadı.</div>');
                    // Kitap detayları bölümünü gizle
                    $('#bookDetails').addClass('d-none');
                }
            },
            error: function(xhr) {
                // Arama düğmesini normal haline getir
                $('#searchBook').html('<i class="fas fa-search"></i> Ara');
                $('#searchBook').prop('disabled', false);
                
                // Hata mesajı göster
                $('#searchResult').html('<div class="alert alert-danger">Arama sırasında bir hata oluştu.</div>');
                // Kitap detayları bölümünü gizle
                $('#bookDetails').addClass('d-none');
            }
        });
    }
});
</script>
@endpush 