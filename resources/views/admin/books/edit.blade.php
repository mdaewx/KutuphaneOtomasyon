@extends('layouts.admin')

@section('title', 'Kitap Düzenle')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kitap Düzenle</h1>
        <a href="{{ route('admin.books.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kitap Listesine Dön
        </a>
    </div>

    <!-- Edit Book Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Kitap Düzenle</h6>
            <a href="{{ route('admin.books.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kitap Listesine Dön
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data" id="bookForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="title">Kitap Adı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                id="title" name="title" value="{{ old('title', $book->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="authors">Yazarlar <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('authors') is-invalid @enderror" 
                                id="authors" name="authors[]" multiple required>
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}" 
                                        {{ in_array($author->id, old('authors', $book->authors->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $author->name }} {{ $author->surname }}
                                    </option>
                                @endforeach
                            </select>
                            @error('authors')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Birden fazla yazar seçmek için CTRL tuşuna basılı tutarak seçim yapabilirsiniz.
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                        <option value="">-- Kategori Seçin --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="publisher_id">Yayınevi <span class="text-danger">*</span></label>
                                    <select class="form-control @error('publisher_id') is-invalid @enderror" 
                                        id="publisher_id" name="publisher_id" required>
                                        <option value="">-- Yayınevi Seçin --</option>
                                        @foreach($publishers as $publisher)
                                            <option value="{{ $publisher->id }}" 
                                                {{ old('publisher_id', $book->publisher_id) == $publisher->id ? 'selected' : '' }}>
                                                {{ $publisher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('publisher_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="isbn">ISBN <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('isbn') is-invalid @enderror" 
                                        id="isbn" name="isbn" value="{{ old('isbn', $book->isbn) }}" required>
                                    @error('isbn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="page_count">Sayfa Sayısı <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('page_count') is-invalid @enderror" 
                                        id="page_count" name="page_count" value="{{ old('page_count', $book->page_count) }}" required min="1">
                                    @error('page_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="language">Kitap Dili</label>
                                    <select class="form-control @error('language') is-invalid @enderror" 
                                        id="language" name="language">
                                        <option value="">-- Dil Seçin --</option>
                                        <option value="Türkçe" {{ old('language', $book->language) == 'Türkçe' ? 'selected' : '' }}>Türkçe</option>
                                        <option value="İngilizce" {{ old('language', $book->language) == 'İngilizce' ? 'selected' : '' }}>İngilizce</option>
                                        <option value="Almanca" {{ old('language', $book->language) == 'Almanca' ? 'selected' : '' }}>Almanca</option>
                                        <option value="Fransızca" {{ old('language', $book->language) == 'Fransızca' ? 'selected' : '' }}>Fransızca</option>
                                        <option value="İspanyolca" {{ old('language', $book->language) == 'İspanyolca' ? 'selected' : '' }}>İspanyolca</option>
                                        <option value="İtalyanca" {{ old('language', $book->language) == 'İtalyanca' ? 'selected' : '' }}>İtalyanca</option>
                                        <option value="Rusça" {{ old('language', $book->language) == 'Rusça' ? 'selected' : '' }}>Rusça</option>
                                        <option value="Arapça" {{ old('language', $book->language) == 'Arapça' ? 'selected' : '' }}>Arapça</option>
                                        <option value="Çince" {{ old('language', $book->language) == 'Çince' ? 'selected' : '' }}>Çince</option>
                                        <option value="Japonca" {{ old('language', $book->language) == 'Japonca' ? 'selected' : '' }}>Japonca</option>
                                        <option value="Korece" {{ old('language', $book->language) == 'Korece' ? 'selected' : '' }}>Korece</option>
                                        <option value="Diğer" {{ old('language', $book->language) == 'Diğer' ? 'selected' : '' }}>Diğer</option>
                                    </select>
                                    @error('language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="publication_year">Basım Yılı</label>
                                    <select class="form-control @error('publication_year') is-invalid @enderror" 
                                        id="publication_year" name="publication_year">
                                        <option value="">-- Yıl Seçin --</option>
                                        @for($year = date('Y'); $year >= 1900; $year--)
                                            <option value="{{ $year }}" {{ (old('publication_year', $book->publication_year) == $year) ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('publication_year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Açıklama</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="4">{{ old('description', $book->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cover_image">Kapak Resmi</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('cover_image') is-invalid @enderror" 
                                    id="cover_image" name="cover_image" accept="image/*">
                                <label class="custom-file-label" for="cover_image">Dosya seçin...</label>
                                @error('cover_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Önerilen boyut: 300x450 piksel</small>
                        </div>

                        @if($book->cover_image)
                            <div class="mt-3">
                                <img src="{{ asset('storage/covers/' . $book->cover_image) }}" 
                                    alt="{{ $book->title }}" class="img-fluid">
                            </div>
                        @endif

                        <div class="mt-3">
                            <img id="cover_preview" src="#" alt="Kapak önizleme" class="img-fluid d-none">
                        </div>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for authors
    $('#authors').select2({
        placeholder: 'Yazar seçin...',
        allowClear: true,
        language: {
            noResults: function() {
                return 'Yazar bulunamadı...';
            }
        }
    });

    // Initialize Select2 for publisher
    $('#publisher_id').select2({
        placeholder: 'Yayınevi seçin...',
        allowClear: false,  // Must select a publisher
        language: {
            noResults: function() {
                return "Yayınevi bulunamadı...";
            }
        }
    });

    // Initialize Select2 for category
    $('#category_id').select2({
        placeholder: 'Kategori seçin...',
        allowClear: false,  // Must select a category
        language: {
            noResults: function() {
                return "Kategori bulunamadı...";
            }
        }
    });

    // Client-side validation for publisher
    $('#bookForm').submit(function(e) {
        if (!$('#publisher_id').val()) {
            e.preventDefault();
            alert('Lütfen bir yayınevi seçin!');
            $('#publisher_id').focus();
            return false;
        }
        return true;
    });

    // Kapak resmi önizleme
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#cover_preview').attr('src', e.target.result).removeClass('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#cover_image").change(function() {
        readURL(this);
        var fileName = $(this).val().split("\\").pop();
        $(this).next('.custom-file-label').html(fileName);
    });
});
</script>
@endsection 