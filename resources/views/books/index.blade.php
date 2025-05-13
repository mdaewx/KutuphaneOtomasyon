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
            background: white;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
        }

        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }

        .book-image {
            height: 250px;
            object-fit: cover;
        }

        .book-category {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
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
                            <img src="{{ $book->cover_image ? asset('storage/covers/' . $book->cover_image) : asset('images/no-cover.png') }}" 
                                 class="book-image w-100" alt="{{ $book->title }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $book->title }}</h5>
                                <p class="card-text text-muted">{{ $book->author }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    @if($book->isAvailable())
                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i>Mevcut</span>
                                    @else
                                    <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Ödünç Alındı</span>
                                    @endif
                                    <a href="{{ route('books.show', $book) }}" class="btn btn-outline-primary btn-sm">Detaylar</a>
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
                        fetch(`/books/search?query=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                // Arama sonuçlarını işleme
                                updateSearchResults(data);
                            })
                            .catch(error => console.error('Error:', error));
                    }, 300);
                }
            });

            function updateSearchResults(books) {
                const booksContainer = document.querySelector('.row');
                booksContainer.innerHTML = '';

                books.forEach(book => {
                    // Kitabın mevcut olup olmadığını API'den gelen değeri kullan
                    const isAvailable = book.is_available !== undefined ? book.is_available : (book.available_quantity > 0 && book.status !== 'borrowed');
                    
                    const bookCard = `
                        <div class="col-md-6 col-lg-4">
                            <div class="book-card">
                                <span class="book-category">${book.category ? book.category.name : 'Genel'}</span>
                                <img src="${book.cover_image ? `/storage/books/${book.cover_image}` : 'https://picsum.photos/300/400'}" 
                                     class="book-image w-100" alt="${book.title}">
                                <div class="card-body">
                                    <h5 class="card-title">${book.title}</h5>
                                    <p class="card-text text-muted">${book.author}</p>
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