@extends('layouts.staff')

@section('title', 'Yeni Kitap Ekle')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Yeni Kitap Ekle</h1>
        <a href="{{ route('staff.books.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 me-1"></i> Kitap Listesine Dön
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kitap Bilgileri</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('staff.books.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Kitap Adı <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="isbn">ISBN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('isbn') is-invalid @enderror" id="isbn" name="isbn" value="{{ old('isbn') }}" required>
                            @error('isbn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="author_id">Yazarlar <span class="text-danger">*</span></label>
                            <select class="form-control select2-authors @error('author_id') is-invalid @enderror" id="author_id" name="author_id" required>
                                <option value="">Yazar seçin veya aramak için yazmaya başlayın...</option>
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}" {{ old('author_id') == $author->id ? 'selected' : '' }}>{{ $author->name }} {{ $author->surname }}</option>
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
                                    <option value="{{ $publisher->id }}" {{ old('publisher_id') == $publisher->id ? 'selected' : '' }}>{{ $publisher->name }}</option>
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
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
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
                            <input type="number" class="form-control @error('page_count') is-invalid @enderror" id="page_count" name="page_count" value="{{ old('page_count') }}" min="1" required>
                            @error('page_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="language">Kitap Dili</label>
                            <select class="form-control @error('language') is-invalid @enderror" id="language" name="language">
                                <option value="">-- Dil Seçin --</option>
                                <option value="Türkçe" {{ old('language') == 'Türkçe' ? 'selected' : '' }}>Türkçe</option>
                                <option value="İngilizce" {{ old('language') == 'İngilizce' ? 'selected' : '' }}>İngilizce</option>
                                <option value="Almanca" {{ old('language') == 'Almanca' ? 'selected' : '' }}>Almanca</option>
                                <option value="Fransızca" {{ old('language') == 'Fransızca' ? 'selected' : '' }}>Fransızca</option>
                                <option value="Diğer" {{ old('language') == 'Diğer' ? 'selected' : '' }}>Diğer</option>
                            </select>
                            @error('language')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="publication_year">Basım Yılı</label>
                            <select class="form-control @error('publication_year') is-invalid @enderror" id="publication_year" name="publication_year">
                                <option value="">-- Yıl Seçin --</option>
                                @for($year = date('Y'); $year >= 1900; $year--)
                                    <option value="{{ $year }}" {{ old('publication_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
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
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Kategori ve yayınevi seçimleri için select2
    $('#category_id, #publisher_id, #language, #publication_year').select2({
        placeholder: 'Seçiniz',
        allowClear: true
    });

    // Yazar seçimi için gelişmiş select2
    $('.select2-authors').select2({
        placeholder: 'Yazar seçin veya aramak için yazmaya başlayın...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '/staff/authors/search',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(function(author) {
                        return {
                            id: author.id,
                            text: author.name + ' ' + (author.surname || '')
                        };
                    })
                };
            },
            cache: true
        }
    });
});
</script>
@endpush 