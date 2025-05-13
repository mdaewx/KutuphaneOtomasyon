@extends('layouts.admin')

@section('title', 'Stok Düzenle')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Stok Düzenle</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Panel</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.stocks.index') }}">Stok Yönetimi</a></li>
        <li class="breadcrumb-item active">Stok Düzenle</li>
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

            <form action="{{ route('admin.stocks.update', $stock->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Hidden fields to prevent validation errors -->
                <input type="hidden" name="source_type_id" value="1">
                <input type="hidden" name="source_name" value="Default Source">

                <!-- ISBN ile Kitap Arama -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="isbn">ISBN ile Kitap Ara</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="isbn" name="isbn" value="{{ old('isbn', $stock->book->isbn ?? '') }}" required>
                                <button type="button" class="btn btn-primary" id="searchIsbn">
                                    <i class="fas fa-search"></i> Ara
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kitap Detayları -->
                <div id="bookDetails" class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kitap Başlığı</label>
                                <p class="form-control-static" id="bookTitle">{{ $stock->book->title ?? '' }}</p>
                                <input type="hidden" name="book_id" id="bookId" value="{{ $stock->book_id }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Yazar(lar)</label>
                                <p class="form-control-static" id="bookAuthors">
                                    @if($stock->book && $stock->book->authors)
                                        {{ $stock->book->authors->pluck('name')->join(', ') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Yayınevi</label>
                                <p class="form-control-static" id="bookPublisher">{{ $stock->book && $stock->book->publisher ? $stock->book->publisher->name : 'Belirtilmemiş' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Yayın Yılı</label>
                                <p class="form-control-static" id="bookYear">{{ $stock->book->publication_year ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Açıklama</label>
                                <p class="form-control-static" id="bookDescription">{{ $stock->book->description ?? 'Açıklama yok' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stok Bilgileri -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="barcode">Barkod <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="barcode" name="barcode" value="{{ old('barcode', $stock->barcode) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="shelf_id">Raf <span class="text-danger">*</span></label>
                            <select class="form-control" id="shelf_id" name="shelf_id" required>
                                <option value="">Raf Seçin</option>
                                @foreach($shelves as $shelf)
                                    <option value="{{ $shelf->id }}" {{ old('shelf_id', $stock->shelf_id) == $shelf->id ? 'selected' : '' }}>
                                        {{ $shelf->name }}
                                    </option>
                                @endforeach
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
                                    <option value="{{ $source->id }}" {{ old('acquisition_source_id', $stock->acquisition_source_id) == $source->id ? 'selected' : '' }}>
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
                            <input type="date" class="form-control" id="acquisition_date" name="acquisition_date" value="{{ old('acquisition_date', $stock->acquisition_date ? $stock->acquisition_date->format('Y-m-d') : '') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="acquisition_price">Edinme Fiyatı</label>
                            <input type="number" step="0.01" class="form-control" id="acquisition_price" name="acquisition_price" value="{{ old('acquisition_price', $stock->acquisition_price) }}">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Güncelle
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
    $('#searchIsbn').click(function() {
        var isbn = $('#isbn').val();
        if (!isbn) {
            alert('Lütfen bir ISBN numarası girin.');
            return;
        }

        // Arama butonunu devre dışı bırak
        $('#searchIsbn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Aranıyor...');

        $.ajax({
            url: '/admin/books/search/' + isbn,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // Arama butonunu normal haline getir
                $('#searchIsbn').prop('disabled', false).html('<i class="fas fa-search"></i> Ara');
                
                if (response.book) {
                    var book = response.book;
                    $('#bookId').val(book.id);
                    $('#bookTitle').text(book.title);
                    
                    // Yazarlar bilgisini ekle
                    if (book.details && book.details.authors) {
                        $('#bookAuthors').text(book.details.authors);
                    } else if (book.authors) {
                        if (Array.isArray(book.authors)) {
                            var authorNames = book.authors.map(function(author) {
                                return author.name + (author.surname ? ' ' + author.surname : '');
                            }).join(', ');
                            $('#bookAuthors').text(authorNames);
                        } else {
                            $('#bookAuthors').text(book.authors);
                        }
                    } else {
                        $('#bookAuthors').text('Belirtilmemiş');
                    }
                    
                    // Yayınevi bilgisini ekle - önce doğrudan response'dan kontrol et
                    console.log('Publisher info:', response.publisher, book.publisher);
                    var publisherName = response.publisher || 
                                      (book.details && book.details.publisher) || 
                                      (book.publisher ? book.publisher.name : 'Belirtilmemiş');
                    $('#bookPublisher').text(publisherName);
                    
                    $('#bookYear').text(book.publication_year || '');
                    $('#bookDescription').text(book.description || 'Açıklama yok');
                } else {
                    alert('Kitap bulunamadı.');
                    $('#bookId').val('');
                    $('#bookTitle').text('');
                    $('#bookAuthors').text('');
                    $('#bookPublisher').text('');
                    $('#bookYear').text('');
                    $('#bookDescription').text('');
                }
            },
            error: function(xhr, status, error) {
                // Arama butonunu normal haline getir
                $('#searchIsbn').prop('disabled', false).html('<i class="fas fa-search"></i> Ara');
                
                console.error('Hata:', xhr.responseText);
                alert('Bir hata oluştu. Lütfen tekrar deneyin.');
            }
        });
    });
});
</script>
@endsection 