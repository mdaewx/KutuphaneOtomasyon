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
                        @if(Auth::user()->hasRole('admin'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-1"></i>Admin Panel
                                </a>
                            </li>
                        @elseif(Auth::user()->hasRole('staff'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('staff.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-1"></i>Personel Panel
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
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-3">Kütüphane Otomasyonu</h1>
                <p class="lead mb-4">Binlerce kitaba erişim sağlayın, ödünç alın ve kütüphane deneyiminizi yönetin</p>
                
                @guest
                <div class="d-flex gap-2">
                    <a href="{{ route('login') }}" class="btn btn-light">Giriş Yap</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light">Üye Ol</a>
                </div>
                @else
                <a href="{{ route('books.index') }}" class="btn btn-light">Kitapları Keşfet</a>
                @endguest
            </div>
                <div class="col-md-6">
                    <div class="search-container">
                        <form action="{{ route('books.index') }}" method="GET">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="search" class="form-control search-input" 
                                    placeholder="Kitap adı, yazar veya ISBN ile arayın...">
                            </div>
                        </form>
            </div>
        </div>
    </div>
</div>
    </section>

    <!-- Kullanıcı Rolleri -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Kütüphane Otomasyonu Rolleri</h2>
            <div class="row">
                <!-- Normal Kullanıcı -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fas fa-user fa-3x text-primary"></i>
                            </div>
                            <h4 class="card-title">Kullanıcı</h4>
                            <p class="card-text">Kitap arama, ödünç alma istekleri, kişisel okuma listesi ve kitap geçmişinizi yönetme.</p>
                            @guest
                            <a href="{{ route('login') }}" class="btn btn-outline-primary">Kullanıcı Girişi</a>
                            @else
                                @if(!auth()->user()->isAdmin() && !auth()->user()->isStaff())
                                <span class="badge bg-success p-2">Aktif Rol</span>
                                @else
                                <button class="btn btn-outline-primary" disabled>Giriş Yapıldı</button>
                                @endif
                            @endguest
                        </div>
                    </div>
                </div>
                
                <!-- Kütüphane Memuru -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-primary">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fas fa-user-tie fa-3x text-info"></i>
                            </div>
                            <h4 class="card-title">Kütüphane Memuru</h4>
                            <p class="card-text">Ödünç verme, iade alma, üye kaydı, stok takibi ve raf düzenleme işlemleri.</p>
                            @guest
                            <a href="{{ route('login') }}" class="btn btn-outline-info">Memur Girişi</a>
                            @else
                                @if(auth()->user()->isStaff())
                                <a href="{{ route('librarian.dashboard') }}" class="btn btn-info">Memur Paneline Git</a>
                                @else
                                <button class="btn btn-outline-info" disabled>Yetkiniz Yok</button>
                                @endif
                            @endguest
                        </div>
                    </div>
                </div>
                
                <!-- Admin -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fas fa-user-shield fa-3x text-danger"></i>
                            </div>
                            <h4 class="card-title">Yönetici</h4>
                            <p class="card-text">Sistem yapılandırması, kullanıcı yönetimi, raporlar ve tam kütüphane kontrolü.</p>
                            @guest
                            <a href="{{ route('login') }}" class="btn btn-outline-danger">Yönetici Girişi</a>
                            @else
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-danger">Yönetici Paneline Git</a>
                                @else
                                <button class="btn btn-outline-danger" disabled>Yetkiniz Yok</button>
                                @endif
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
    <div class="row">
        <!-- Sol Taraf - Kategoriler -->
            <div class="col-lg-3">
                <div class="filter-section">
                    <h4 class="filter-title">Kategoriler</h4>
                    <div class="mb-4">
                        @forelse($categories as $category)
                        <div class="form-check mb-2">
                            <a href="{{ route('books.index', ['category' => $category->id]) }}" class="text-decoration-none text-dark d-flex justify-content-between">
                                <label class="form-check-label">
                                    {{ $category->name }}
                                </label>
                                <span class="badge bg-primary rounded-pill">{{ $category->books_count }}</span>
                            </a>
                        </div>
                        @empty
                        <div class="text-center py-3">
                            <p class="text-muted">Henüz kategori bulunmamaktadır.</p>
                </div>
                        @endforelse
                </div>
            </div>

                <div class="filter-section">
                    <h4 class="filter-title">Hızlı İşlemler</h4>
                    <div class="d-grid gap-2">
                        <a href="{{ route('books.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>Kitap Ara
                        </a>
                        @auth
                        <a href="{{ route('profile') }}" class="btn btn-outline-primary">
                            <i class="fas fa-book me-2"></i>Ödünç Aldıklarım
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                        </a>
                        @endauth
                </div>
            </div>
        </div>

        <!-- Sağ Taraf - Kitaplar -->
            <div class="col-lg-9">
            <!-- Popüler Kitaplar -->
                <h3 class="section-title">Popüler Kitaplar</h3>
            <div class="row">
                    @forelse($popular_books as $book)
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
                    @empty
                    <div class="col-12 text-center">
                        <p>Henüz kitap eklenmemiş.</p>
                    </div>
                    @endforelse
                </div>
                
                <!-- Son Eklenen Kitaplar -->
                <h3 class="section-title mt-5">Son Eklenen Kitaplar</h3>
                <div class="row">
                    @forelse($latest_books as $book)
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
                    @empty
                    <div class="col-12 text-center">
                        <p>Henüz kitap eklenmemiş.</p>
                    </div>
                    @endforelse
                </div>
                
                <div class="text-center mt-4 mb-5">
                    <a href="{{ route('books.index') }}" class="btn btn-primary px-4 py-2">Tüm Kitapları Görüntüle</a>
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
</body>
</html> 