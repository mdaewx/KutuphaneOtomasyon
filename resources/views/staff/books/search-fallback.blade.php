@extends('layouts.staff')

@section('title', 'Kitap Arama Sonuçları')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kitap Arama Sonuçları</h1>
        <div>
            <button onclick="window.close()" class="btn btn-secondary mr-2">
                <i class="fas fa-times"></i> Kapat
            </button>
            <button onclick="useThisBook()" class="btn btn-primary">
                <i class="fas fa-check"></i> Bu Kitabı Kullan
            </button>
        </div>
    </div>

    @if(isset($error))
        <div class="alert alert-warning">
            {{ $error }}
        </div>
    @endif

    @if(isset($book))
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Kitap Detayları (ISBN: {{ $isbn }})</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        @if($book->cover_image)
                            <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}" class="img-fluid rounded mb-3">
                        @else
                            <div class="text-center p-4 bg-light rounded mb-3">
                                <i class="fas fa-book fa-5x text-secondary"></i>
                                <p class="mt-2 text-muted">Kapak resmi yok</p>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <h3>{{ $book->title }}</h3>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <p><strong>Yazar:</strong> {{ $authorNames }}</p>
                                <p><strong>Yayınevi:</strong> {{ $publisherName }}</p>
                                <p><strong>Kategori:</strong> {{ $categoryName }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>ISBN:</strong> {{ $book->isbn }}</p>
                                <p><strong>Dil:</strong> {{ $book->language ?? 'Belirtilmemiş' }}</p>
                                <p><strong>Yayın Yılı:</strong> {{ $book->publication_year ?? 'Belirtilmemiş' }}</p>
                                <p><strong>Sayfa Sayısı:</strong> {{ $book->page_count ?? 'Belirtilmemiş' }}</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h5>Açıklama</h5>
                            <p>{{ $book->description ?? 'Kitap açıklaması bulunmuyor.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button onclick="useThisBook()" class="btn btn-primary btn-lg">
                        <i class="fas fa-check"></i> Bu Kitabı Kullan
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Farklı ISBN ile Arama Yap</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('staff.books.search-fallback') }}" method="GET">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="isbn">ISBN Numarası:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="isbn" name="isbn" value="{{ $isbn ?? '' }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Ara
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function useThisBook() {
    // Book details to pass back to the opener window
    var bookData = {
        id: '{{ $book->id ?? "" }}',
        title: '{{ $book->title ?? "" }}',
        isbn: '{{ $book->isbn ?? "" }}',
        authors: '{{ $authorNames ?? "" }}',
        publisher: '{{ $publisherName ?? "" }}',
        publication_year: '{{ $book->publication_year ?? "" }}',
        description: '{{ $book->description ?? "" }}',
        cover_image: '{{ $book->cover_image_url ?? "" }}'
    };
    
    if (window.opener && !window.opener.closed) {
        // Send the data to the opener and close
        if (typeof window.opener.receiveBookData === 'function') {
            window.opener.receiveBookData(bookData);
        } else {
            alert('Ana pencerede receiveBookData fonksiyonu bulunamadı.');
        }
        window.close();
    } else {
        alert('Ana pencere bulunamadı veya kapatılmış.');
    }
}
</script>
@endpush 