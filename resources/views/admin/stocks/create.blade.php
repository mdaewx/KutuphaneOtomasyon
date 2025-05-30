@extends('layouts.admin')

@section('title', 'Yeni Stok Ekle')

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Yeni Stok Kaydı</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Panel</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.stocks.index') }}">Stok Yönetimi</a></li>
        <li class="breadcrumb-item active">Yeni Stok</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-box-open me-1"></i>
            Stok Bilgileri
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

            <form action="{{ route('admin.stocks.store') }}" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="book_id" id="book_id" value="{{ old('book_id') }}">

                <!-- ISBN ile Kitap Arama -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="isbn">ISBN ile Kitap Ara</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="isbn" name="isbn" value="{{ old('isbn') }}" required>
                                <button type="button" class="btn btn-primary" id="searchIsbn">
                                    <i class="fas fa-search"></i> Ara
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kitap Detayları -->
                <div id="bookDetails" class="d-none">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kitap Başlığı</label>
                                <p class="form-control-static" id="bookTitle"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Yazar(lar)</label>
                                <p class="form-control-static" id="bookAuthors"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Yayınevi</label>
                                <p class="form-control-static" id="bookPublisher"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kategori</label>
                                <p class="form-control-static" id="bookCategory"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Yayın Yılı</label>
                                <p class="form-control-static" id="bookPublicationYear"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sayfa Sayısı</label>
                                <p class="form-control-static" id="bookPageCount"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dil</label>
                                <p class="form-control-static" id="bookLanguage"></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Mevcut Stok</label>
                                <p class="form-control-static">
                                    <span id="bookAvailableQuantity"></span> / <span id="bookTotalQuantity"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stok Bilgileri -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="barcode">Barkod <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="barcode" name="barcode" value="{{ old('barcode') }}" required>
                            <small class="form-text text-muted">Her kopya için bu barkoda sıra numarası eklenecektir. Örnek: 123-001, 123-002</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="quantity">Adet <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="100" required>
                            <small class="form-text text-muted">Eklenecek kopya sayısı (1-100 arası)</small>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="shelf_id">Raf <span class="text-danger">*</span></label>
                            <select class="form-control" id="shelf_id" name="shelf_id" required>
                                <option value="">Raf Seçin</option>
                                @foreach($shelves as $shelf)
                                    <option value="{{ $shelf->id }}" {{ old('shelf_id') == $shelf->id ? 'selected' : '' }}>
                                        {{ $shelf->name }} ({{ $shelf->stocks_count ?? 0 }}/{{ $shelf->capacity }} kitap)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="condition">Kitap Durumu <span class="text-danger">*</span></label>
                            <select class="form-control" id="condition" name="condition" required>
                                <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>Yeni</option>
                                <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>İyi</option>
                                <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Orta</option>
                                <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Kötü</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Edinme Kaynağı Seçimi -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="acquisition_source_id">Edinme Kaynağı <span class="text-danger">*</span></label>
                            <select class="form-control" id="acquisition_source_id" name="acquisition_source_id" required>
                                <option value="">Edinme Kaynağı Seçin</option>
                                @foreach($acquisitionSources as $source)
                                    <option value="{{ $source->id }}" {{ old('acquisition_source_id') == $source->id ? 'selected' : '' }}>
                                        {{ $source->source_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="acquisition_date">Edinme Tarihi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="acquisition_date" name="acquisition_date" value="{{ old('acquisition_date') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="acquisition_price">Edinme Fiyatı</label>
                            <input type="number" step="0.01" class="form-control" id="acquisition_price" name="acquisition_price" value="{{ old('acquisition_price') }}">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Kaydet
                    </button>
                    <a href="{{ route('admin.stocks.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // ISBN search functionality
    $('#searchIsbn').click(function() {
        var isbn = $('#isbn').val();
        if (!isbn) {
            alert('Lütfen bir ISBN numarası girin.');
            return;
        }

        // Disable search button
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Aranıyor...');

        // Make AJAX request
        $.ajax({
            url: "{{ route('admin.books.search', '') }}/" + isbn,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.book) {
                    console.log('Book details:', response.book); // Debug için log
                    $('#book_id').val(response.book.id);
                    $('#bookTitle').text(response.book.title);
                    $('#bookAuthors').text(response.book.details.authors);
                    $('#bookPublisher').text(response.book.details.publisher || 'Belirtilmemiş');
                    $('#bookCategory').text(response.book.details.category);
                    $('#bookPublicationYear').text(response.book.details.publication_year || '-');
                    $('#bookPageCount').text(response.book.details.page_count || '-');
                    $('#bookLanguage').text(response.book.details.language || '-');
                    $('#bookAvailableQuantity').text(response.book.details.available_quantity);
                    $('#bookTotalQuantity').text(response.book.details.total_quantity);
                    $('#bookDetails').removeClass('d-none');
                    
                    // Otomatik barkod oluştur
                    if (!$('#barcode').val()) {
                        var timestamp = new Date().getTime().toString().substr(-5);
                        $('#barcode').val(isbn + '-' + timestamp);
                    }
                } else {
                    alert('Kitap bulunamadı.');
                    $('#bookDetails').addClass('d-none');
                }
            },
            error: function() {
                alert('Arama sırasında bir hata oluştu.');
            },
            complete: function() {
                // Re-enable search button
                $('#searchIsbn').prop('disabled', false).html('<i class="fas fa-search"></i> Ara');
            }
        });
    });
});
</script>
@endsection 