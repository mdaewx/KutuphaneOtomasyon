<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kütüphane Otomasyonu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --background-color: #f8f9fa;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 4rem 0;
            color: white;
            margin-bottom: 3rem;
        }

        .search-container {
            max-width: 600px;
            margin: 2rem auto;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding-left: 3rem;
            height: 3.5rem;
            border-radius: 50px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .book-card {
            min-height: 450px;
            max-height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: stretch;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 24px;
            padding: 12px 8px 0 8px;
            background: #fff;
            overflow: hidden;
            position: relative;
        }
        .book-card .card-body {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 0 12px 12px 12px;
        }
        .book-card .card-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 6px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .book-card .card-text {
            font-size: 0.95rem;
            color: #666;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 8px;
        }
        .book-card .d-flex {
            margin-top: 8px;
        }
        .book-card img {
            max-width: 110px;
            max-height: 110px;
            margin: 0 auto 8px auto;
            display: block;
        }
        .book-category {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #f1f3f6;
            color: #3a3a3a;
            font-size: 0.85rem;
            padding: 2px 10px;
            border-radius: 8px;
            z-index: 2;
        }
        .book-details {
            font-size: 0.9rem;
            padding: 0 0.5rem;
        }
        .book-details p {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        @media (max-width: 991px) {
            .book-card { min-height: 420px; max-height: 480px; }
        }
        @media (max-width: 767px) {
            .book-card { min-height: 400px; max-height: 460px; }
            .book-card .card-title, .book-card .card-text { font-size: 0.98rem; }
        }

        .section-title {
            position: relative;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 3px;
            background: var(--secondary-color);
        }
        
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filter-title {
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination {
            margin-top: 2rem;
            justify-content: center;
        }
        
        .page-link {
            color: var(--primary-color);
            border: none;
            margin: 0 0.2rem;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/"><i class="fas fa-book-reader me-2"></i>Kütüphane Otomasyonu</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="fas fa-home me-1"></i>Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/books"><i class="fas fa-book me-1"></i>Kitaplar</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="/profile"><i class="fas fa-user me-1"></i>{{ Auth::user()->name }}</a>
                        </li>
                        @if(Auth::user()->is_admin)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-1"></i>Admin Panel
                                </a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i>Giriş</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i>Kayıt</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h1 class="display-4 mb-4">Kitaplar</h1>
                    <p class="lead mb-4">Kütüphanemizdeki tüm kitapları keşfedin</p>
                    
                    <div class="search-container">
                        <form action="{{ route('books.index') }}" method="GET">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="search" class="form-control search-input" id="searchInput" 
                                    placeholder="Kitap adı, yazar veya ISBN ile arayın...">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="filter-section">
                    <h4 class="filter-title">Filtreler</h4>
                    <div class="mb-4">
                        <h5 class="mb-3">Kategoriler</h5>
                        @foreach($categories as $category)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="{{ $category->id }}" 
                                   id="category{{ $loop->index }}">
                            <label class="form-check-label" for="category{{ $loop->index }}">
                                {{ $category->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <div class="mb-4">
                        <h5 class="mb-3">Durum</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" value="available" id="statusAvailable">
                            <label class="form-check-label" for="statusAvailable">
                                Mevcut
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="borrowed" id="statusBorrowed">
                            <label class="form-check-label" for="statusBorrowed">
                                Ödünç Alınmış
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="row">
                    @foreach($books as $book)
                    <div class="col-md-6 col-lg-4">
                        <div class="book-card">
                            <span class="book-category">{{ $book->category->name ?? 'Genel' }}</span>
                            <div class="text-center p-3">
                                <img src="{{ asset('images/icons/book-logo.png') }}" 
                                     alt="{{ $book->title }}" class="img-fluid" style="width: 150px; height: auto;">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $book->title }}</h5>
                                <div class="book-details">
                                    <p class="card-text text-muted mb-1">
                                        <i class="fas fa-user me-1"></i> {{ $book->authors->pluck('full_name')->join(', ') }}
                                    </p>
                                    <p class="card-text text-muted mb-1">
                                        <i class="fas fa-building me-1"></i> 
                                        @php
                                            $publisherName = $book->publisher ? $book->publisher->name : ($book->publisher_id ? \App\Models\Publisher::find($book->publisher_id)->name : 'Belirtilmemiş');
                                        @endphp
                                        {{ $publisherName }}
                                    </p>
                                    @if($book->stocks->count() > 0)
                                        <p class="card-text text-muted mb-1">
                                            <i class="fas fa-bookmark me-1"></i> 
                                            @foreach($book->stocks as $stock)
                                                {{ $stock->shelf->name ?? 'Belirtilmemiş' }}
                                                @if(!$loop->last), @endif
                                            @endforeach
                                        </p>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge {{ $book->isAvailable() ? 'bg-success' : 'bg-danger' }}">
                                        {{ $book->isAvailable() ? 'Mevcut' : 'Mevcut Değil' }}
                                    </span>
                                    <div class="btn-group">
                                        <a href="{{ route('books.show', $book) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Detay
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="pagination">
                    {{ $books->links() }}
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-book-reader me-2"></i>Kütüphane Otomasyonu</h5>
                    <p class="small">Kitapları keşfedin, bilgiye erişin, ödünç alın ve okuma deneyiminizi zenginleştirin.</p>
                </div>
                <div class="col-md-3">
                    <h5>Hızlı Linkler</h5>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-white text-decoration-none">Ana Sayfa</a></li>
                        <li><a href="{{ route('books.index') }}" class="text-white text-decoration-none">Kitaplar</a></li>
                        <li><a href="{{ route('login') }}" class="text-white text-decoration-none">Giriş Yap</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>İletişim</h5>
                    <address class="small">
                        <i class="fas fa-map-marker-alt me-1"></i> Kütüphane Caddesi No:123<br>
                        <i class="fas fa-phone me-1"></i> (0123) 456 78 90<br>
                        <i class="fas fa-envelope me-1"></i> info@kutuphane.com
                    </address>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="small">&copy; {{ date('Y') }} Kütüphane Otomasyonu. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;

            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const query = e.target.value;

                if (query.length >= 3) {
                    searchTimeout = setTimeout(() => {
                        console.log('Searching for:', query);
                        fetch(`/books/search?query=${encodeURIComponent(query)}`, {
                            headers: {
                                'Cache-Control': 'no-cache',
                                'Pragma': 'no-cache'
                            }
                        })
                            .then(response => {
                                console.log('Search response status:', response.status);
                                return response.json();
                            })
                            .then(data => {
                                console.log('Search results:', data);
                                // Arama sonuçlarını işleme
                                updateSearchResults(data);
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                // Hata durumunda sabit test verisi göster
                                const testData = [
                                    {
                                        id: 999,
                                        title: 'Test Kitabı (API hatası nedeniyle gösteriliyor)',
                                        authors: [],
                                        category: { name: 'Test' },
                                        is_available: true
                                    }
                                ];
                                updateSearchResults(testData);
                            });
                    }, 300);
                }
            });

            function updateSearchResults(books) {
                const booksContainer = document.querySelector('.row');
                booksContainer.innerHTML = '';

                books.forEach(book => {
                    // Kitabın mevcut olup olmadığını API'den gelen değeri kullan
                    // veya varsayılan olarak mevcut kabul et (ödünç verme kaydı yoksa)
                    const isAvailable = book.is_available !== undefined ? book.is_available : true;
                    
                    const bookCard = `
                        <div class="col-md-6 col-lg-4">
                            <div class="book-card">
                                <span class="book-category">${book.category ? book.category.name : 'Genel'}</span>
                                <div class="text-center p-3">
                                    <img src="{{ asset('images/icons/book-logo.png') }}" 
                                         alt="${book.title}" class="img-fluid" style="width: 150px; height: auto;">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">${book.title}</h5>
                                    <div class="book-details">
                                        <p class="card-text text-muted mb-1">
                                            <i class="fas fa-user me-1"></i>
                                            ${book.authors ? book.authors.map(author => author.full_name).join(', ') : ''}
                                        </p>
                                        <p class="card-text text-muted mb-1">
                                            <i class="fas fa-building me-1"></i>
                                            ${book.publisher ? book.publisher.name : 'Belirtilmemiş'}
                                        </p>
                                        <p class="card-text text-muted mb-1">
                                            <i class="fas fa-barcode me-1"></i>
                                            ${book.isbn ?? 'ISBN Belirtilmemiş'}
                                        </p>
                                        <p class="card-text text-muted mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            ${book.publication_year ?? 'Yıl Belirtilmemiş'}
                                        </p>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        ${isAvailable 
                                            ? `<span class="text-success"><i class="fas fa-check-circle me-1"></i>Mevcut</span>`
                                            : `<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Ödünç Alındı</span>`
                                        }
                                        <a href="/books/${book.id}" class="btn btn-outline-primary btn-sm">Detaylar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    booksContainer.innerHTML += bookCard;
                });
            }
        });
    </script>
</body>
</html> 