<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $book->title }} - Kütüphane Otomasyonu</title>
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

        .book-details {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 2rem;
            margin-bottom: 2rem;
            padding: 2rem;
        }

        .book-cover {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            max-height: 400px;
            object-fit: cover;
        }

        .book-info h1 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .book-meta {
            margin-bottom: 1.5rem;
        }

        .meta-item {
            display: flex;
            margin-bottom: 0.8rem;
        }

        .meta-label {
            width: 120px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .badge-category {
            background-color: var(--primary-color);
        }

        .action-buttons {
            margin-top: 2rem;
        }

        .book-available {
            color: #2ecc71;
            font-weight: 600;
        }

        .book-borrowed {
            color: #e74c3c;
            font-weight: 600;
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

    <div class="container">
        <!-- Mesajlar -->
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        <div class="book-details">
            <div class="row">
                <div class="col-md-4 text-center mb-4 mb-md-0">
                    <img src="{{ asset('images/icons/book-logo.png') }}" 
                         alt="{{ $book->title }}" class="book-cover img-fluid" style="width: 200px; height: auto; margin: 0 auto; display: block;">
                </div>
                <div class="col-md-8 book-info">
                    <h1>{{ $book->title }}</h1>
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary badge-category me-2">{{ $book->category->name ?? 'Genel' }}</span>
                        @if($book->isAvailable())
                            <span class="book-available"><i class="fas fa-check-circle me-1"></i>Mevcut</span>
                        @else
                            <span class="book-borrowed"><i class="fas fa-times-circle me-1"></i>Ödünç Alındı</span>
                        @endif
                    </div>
                    
                    <div class="book-meta">
                        <div class="meta-item">
                            <div class="meta-label">Yazar:</div>
                            <div>
                                @foreach($book->authors as $author)
                                    {{ $author->name }} {{ $author->surname }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Yayınevi:</div>
                            <div>{{ $book->publisher->name ?? 'Belirtilmemiş' }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Yayın Yılı:</div>
                            <div>{{ $book->publication_year }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">ISBN:</div>
                            <div>{{ $book->isbn }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Dil:</div>
                            <div>{{ $book->language ?? 'Belirtilmemiş' }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Sayfa Sayısı:</div>
                            <div>{{ $book->page_count }} sayfa</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Raf Numarası:</div>
                            <div>{{ $book->shelf_number }}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Stok Durumu:</div>
                            <div>{{ $book->available_quantity }} / {{ $book->quantity }}</div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Açıklama</h5>
                    <p>{{ $book->description }}</p>
                    
                    <div class="action-buttons">
                        @auth
                            @if($book->isAvailable())
                                <form action="{{ route('borrowings.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <button type="submit" id="borrowButton" class="btn btn-primary">
                                        <i class="fas fa-book me-1"></i>Ödünç Al
                                    </button>
                                </form>
                            @else
                                <button disabled class="btn btn-secondary"><i class="fas fa-book me-1"></i>Şu Anda Mevcut Değil</button>
                            @endif
                            <a href="{{ route('books.index') }}" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left me-1"></i>Geri Dön</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary"><i class="fas fa-sign-in-alt me-1"></i>Ödünç Almak İçin Giriş Yapın</a>
                            <a href="{{ route('books.index') }}" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left me-1"></i>Geri Dön</a>
                        @endauth
                    </div>
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
        // Ödünç al butonunu kontrol et
        const borrowButton = document.getElementById('borrowButton');
        
        if (borrowButton) {
            borrowButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (confirm('Bu kitabı ödünç almak istediğinize emin misiniz?')) {
                    this.closest('form').submit();
                }
            });
        }
        
        // Hata mesajlarını kontrol et ve göster
        @if (session('error'))
            alert("{{ session('error') }}");
        @endif
        
        @if (session('success'))
            alert("{{ session('success') }}");
        @endif
    });
    </script>
</body>
</html> 