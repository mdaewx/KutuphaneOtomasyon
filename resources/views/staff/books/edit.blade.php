@extends('layouts.staff')

@section('title', 'Kitap Düzenle')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kitap Düzenle</h1>
        <a href="{{ route('staff.books.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 me-1"></i> Kitap Listesine Dön
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kitap Bilgileri Düzenle</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('staff.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Kitap Adı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $book->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="isbn">ISBN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('isbn') is-invalid @enderror" id="isbn" name="isbn" value="{{ old('isbn', $book->isbn) }}" required>
                            @error('isbn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="author_id">Yazar <span class="text-danger">*</span></label>
                            <select class="form-control @error('author_id') is-invalid @enderror" id="author_id" name="author_id" required>
                                <option value="">Yazar seçin...</option>
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}" {{ (old('author_id', $book->authors->first()->id ?? null) == $author->id) ? 'selected' : '' }}>
                                        {{ $author->name }} {{ $author->surname }}
                                    </option>
                                @endforeach
                            </select>
                            @error('author_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="publisher_id">Yayınevi <span class="text-danger">*</span></label>
                            <select class="form-control @error('publisher_id') is-invalid @enderror" id="publisher_id" name="publisher_id" required>
                                <option value="">Yayınevi seçin...</option>
                                @foreach($publishers as $publisher)
                                    <option value="{{ $publisher->id }}" {{ (old('publisher_id', $book->publisher_id) == $publisher->id) ? 'selected' : '' }}>
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

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category_id">Kategori <span class="text-danger">*</span></label>
                            <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">Kategori seçin...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (old('category_id', $book->category_id) == $category->id) ? 'selected' : '' }}>
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
                            <label for="page_count">Sayfa Sayısı <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('page_count') is-invalid @enderror" id="page_count" name="page_count" value="{{ old('page_count', $book->page_count) }}" min="1" required>
                            @error('page_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="language">Dil <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('language') is-invalid @enderror" id="language" name="language" value="{{ old('language', $book->language) }}" required>
                            @error('language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="publication_year">Basım Yılı <span class="text-danger">*</span></label>
                            <select class="form-control @error('publication_year') is-invalid @enderror" id="publication_year" name="publication_year" required>
                                <option value="">-- Yıl Seçin --</option>
                                @for($year = date('Y'); $year >= 1900; $year--)
                                    <option value="{{ $year }}" {{ (old('publication_year', $book->publication_year) == $year) ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                            @error('publication_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Kitap Görseli</label>
                            <div class="text-center mt-2">
                                <img src="{{ asset('images/icons/book-logo.png') }}" alt="Kitap Logo" class="img-fluid" style="width: 200px; height: auto;">
                                <p class="text-muted small mt-2">Tüm kitaplar için standart logo kullanılmaktadır.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="description">Açıklama</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $book->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-1"></i> Kaydet
                    </button>
                    <a href="{{ route('staff.books.index') }}" class="btn btn-secondary btn-lg px-5 ms-2">
                        <i class="fas fa-times me-1"></i> İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Kategori, yazar ve yayınevi seçimleri için select2
    $('#category_id, #publisher_id, #author_id, #language, #publication_year').select2({
        placeholder: 'Seçiniz',
        allowClear: true
    });
});
</script>
@endpush 