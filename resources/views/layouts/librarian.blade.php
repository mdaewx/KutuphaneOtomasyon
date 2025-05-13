<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kütüphane Memuru Paneli') - {{ config('app.name', 'Kütüphane Otomasyonu') }}</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        :root {
            --primary-color: #3a4b68;
            --secondary-color: #5a6f8f;
            --accent-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-color);
            overflow-x: hidden;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 100;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 1rem 1.5rem;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,.1);
            border-left-color: var(--accent-color);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,.1);
            border-left-color: var(--accent-color);
        }
        
        .sidebar .dropdown-item {
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        .sidebar-heading {
            color: rgba(255,255,255,.4);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            padding: 1rem 1.5rem 0.5rem;
        }
        
        .content {
            min-height: 100vh;
        }
        
        .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 700;
        }
        
        .card .border-left-primary {
            border-left: 0.25rem solid var(--accent-color) !important;
        }
        
        .card .border-left-success {
            border-left: 0.25rem solid var(--success-color) !important;
        }
        
        .card .border-left-info {
            border-left: 0.25rem solid var(--info-color) !important;
        }
        
        .card .border-left-warning {
            border-left: 0.25rem solid var(--warning-color) !important;
        }
        
        .card .border-left-danger {
            border-left: 0.25rem solid var(--danger-color) !important;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky pt-3">
                <a class="navbar-brand px-3 mb-3 d-flex align-items-center" href="{{ route('librarian.dashboard') }}">
                    <i class="fas fa-book-reader me-2"></i>
                    Kütüphane Memuru
                </a>
                
                <ul class="nav flex-column mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.dashboard') ? 'active' : '' }}" 
                           href="{{ route('librarian.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Gösterge Paneli
                        </a>
                    </li>
                </ul>
                
                <div class="sidebar-heading">
                    Kitap İşlemleri
                </div>
                <ul class="nav flex-column mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.books.*') ? 'active' : '' }}"
                           href="{{ route('librarian.books.index') }}">
                            <i class="fas fa-book me-2"></i>
                            Kitaplar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.books.create') ? 'active' : '' }}"
                           href="{{ route('librarian.books.create') }}">
                            <i class="fas fa-plus-circle me-2"></i>
                            Yeni Kitap Ekle
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.categories.*') ? 'active' : '' }}"
                           href="{{ route('librarian.categories.index') }}">
                            <i class="fas fa-tags me-2"></i>
                            Kategoriler
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.authors.*') ? 'active' : '' }}"
                           href="{{ route('librarian.authors.index') }}">
                            <i class="fas fa-user-edit me-2"></i>
                            Yazarlar
                        </a>
                    </li>
                </ul>
                
                <div class="sidebar-heading">
                    Ödünç İşlemleri
                </div>
                <ul class="nav flex-column mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.borrowings.*') ? 'active' : '' }}"
                           href="{{ route('librarian.borrowings.index') }}">
                            <i class="fas fa-hand-holding-heart me-2"></i>
                            Tüm Ödünçler
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.borrowings.create') ? 'active' : '' }}"
                           href="{{ route('librarian.borrowings.create') }}">
                            <i class="fas fa-paper-plane me-2"></i>
                            Kitap Ödünç Ver
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.returns.*') ? 'active' : '' }}"
                           href="{{ route('librarian.returns.create') }}">
                            <i class="fas fa-undo me-2"></i>
                            Kitap İadesi Al
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.overdue') ? 'active' : '' }}"
                           href="{{ route('librarian.overdue') }}">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Gecikmiş Kitaplar
                        </a>
                    </li>
                </ul>
                
                <div class="sidebar-heading">
                    Stok Yönetimi
                </div>
                <ul class="nav flex-column mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.stocks.*') ? 'active' : '' }}"
                           href="{{ route('librarian.stocks.index') }}">
                            <i class="fas fa-boxes me-2"></i>
                            Stok Listesi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.shelf-management.*') ? 'active' : '' }}"
                           href="{{ route('librarian.shelf-management.index') }}">
                            <i class="fas fa-bookmark me-2"></i>
                            Raf Düzeni
                        </a>
                    </li>
                </ul>
                
                <div class="sidebar-heading">
                    Üye İşlemleri
                </div>
                <ul class="nav flex-column mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.members.*') ? 'active' : '' }}"
                           href="{{ route('librarian.members.index') }}">
                            <i class="fas fa-users me-2"></i>
                            Üye Listesi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('librarian.members.create') ? 'active' : '' }}"
                           href="{{ route('librarian.members.create') }}">
                            <i class="fas fa-user-plus me-2"></i>
                            Yeni Üye Ekle
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="d-flex align-items-center">
                        <form class="me-3" action="{{ route('librarian.search') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Kitap, üye veya barkod ara..." name="q" required>
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle text-dark text-decoration-none" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2"></i>{{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile') }}">
                                        <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                                        Profil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                            Çıkış Yap
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto close alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                var closeBtn = new bootstrap.Alert(alert);
                closeBtn.close();
            });
        }, 5000);
    </script>
    
    @yield('scripts')
</body>
</html> 