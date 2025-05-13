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
        
        .category-card {
            background: white;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            padding: 2rem 1rem;
            height: 100%;
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
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
                    <h1 class="display-4 mb-4">Kütüphane Otomasyonu</h1>
                    <p class="lead mb-4">Binlerce kitaba erişim sağlayın, ödünç alın ve kütüphane deneyiminizi yönetin</p>
                    
                    @auth
                        @if(Auth::user()->is_admin)
                            <div class="mb-4">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>Admin Paneline Git
                                </a>
                            </div>
                        @endif
                    @endauth
                    
                    <div class="search-container">
                        <form action="{{ route('books.index') }}" method="GET">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="search" class="form-control search-input" placeholder="Kitap adı, yazar veya ISBN ile arayın...">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <section class="popular-books mb-5">
            <h2 class="section-title">Popüler Kitaplar</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="book-card">
                        <span class="book-category">Roman</span>
                        <img src="https://picsum.photos/300/400" class="book-image w-100" alt="Kitap 1">
                        <div class="card-body">
                            <h5 class="card-title">Suç ve Ceza</h5>
                            <p class="card-text text-muted">Fyodor Dostoyevski</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i>Mevcut</span>
                                <a href="{{ route('books.index') }}" class="btn btn-outline-primary btn-sm">Detaylar</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="book-card">
                        <span class="book-category">Bilim Kurgu</span>
                        <img src="https://picsum.photos/300/401" class="book-image w-100" alt="Kitap 2">
                        <div class="card-body">
                            <h5 class="card-title">1984</h5>
                            <p class="card-text text-muted">George Orwell</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i>Mevcut</span>
                                <a href="{{ route('books.index') }}" class="btn btn-outline-primary btn-sm">Detaylar</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="book-card">
                        <span class="book-category">Felsefe</span>
                        <img src="https://picsum.photos/300/402" class="book-image w-100" alt="Kitap 3">
                        <div class="card-body">
                            <h5 class="card-title">Sofie'nin Dünyası</h5>
                            <p class="card-text text-muted">Jostein Gaarder</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Ödünç Alındı</span>
                                <a href="{{ route('books.index') }}" class="btn btn-outline-primary btn-sm">Detaylar</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="book-card">
                        <span class="book-category">Psikoloji</span>
                        <img src="https://picsum.photos/300/403" class="book-image w-100" alt="Kitap 4">
                        <div class="card-body">
                            <h5 class="card-title">İnsan Ne İle Yaşar</h5>
                            <p class="card-text text-muted">Lev Tolstoy</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i>Mevcut</span>
                                <a href="{{ route('books.index') }}" class="btn btn-outline-primary btn-sm">Detaylar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('books.index') }}" class="btn btn-primary">Tüm Kitapları Görüntüle</a>
            </div>
        </section>

        <section class="categories mb-5">
            <h2 class="section-title">Kategoriler</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="category-card">
                        <i class="fas fa-book-open category-icon"></i>
                        <h5>Roman</h5>
                        <p class="text-muted mb-3">250+ Kitap</p>
                        <a href="{{ route('books.index') }}" class="btn btn-outline-primary btn-sm">Keşfet</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="category-card">
                        <i class="fas fa-rocket category-icon"></i>
                        <h5>Bilim Kurgu</h5>
                        <p class="text-muted mb-3">120+ Kitap</p>
                        <a href="{{ route('books.index') }}" class="btn btn-outline-primary btn-sm">Keşfet</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="category-card">
                        <i class="fas fa-brain category-icon"></i>
                        <h5>Felsefe</h5>
                        <p class="text-muted mb-3">80+ Kitap</p>
                        <a href="{{ route('books.index') }}" class="btn btn-outline-primary btn-sm">Keşfet</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="category-card">
                        <i class="fas fa-microscope category-icon"></i>
                        <h5>Bilim</h5>
                        <p class="text-muted mb-3">150+ Kitap</p>
                        <a href="{{ route('books.index') }}" class="btn btn-outline-primary btn-sm">Keşfet</a>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="features mb-5">
            <h2 class="section-title">Kütüphane Hizmetleri</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                            <h4>Geniş Kitap Koleksiyonu</h4>
                            <p class="text-muted">Binlerce kitap arasından seçim yapabilir, ilgi alanlarınıza göre filtreleyebilirsiniz.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-clock fa-3x mb-3 text-primary"></i>
                            <h4>Kolay Ödünç Alma</h4>
                            <p class="text-muted">Kitapları hızlıca ödünç alabilir ve iade sürecinizi kolayca takip edebilirsiniz.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-user-circle fa-3x mb-3 text-primary"></i>
                            <h4>Kişisel Kütüphane</h4>
                            <p class="text-muted">Kişisel profilinizde ödünç işlemlerinizi ve okuma geçmişinizi yönetebilirsiniz.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
