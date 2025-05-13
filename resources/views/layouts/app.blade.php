<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Kütüphane Sistemi') }}</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @yield('styles')
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --success-color: #27ae60;
            --info-color: #3498db;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }
        
        .navbar-dark {
            background-color: var(--primary-color) !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-info {
            background-color: var(--info-color);
            border-color: var(--info-color);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            font-weight: bold;
        }
        
        footer {
            margin-top: 4rem;
            padding: 2rem 0;
            background-color: #fff;
            border-top: 1px solid #e3e6f0;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <i class="fas fa-book me-2"></i>
                    Kütüphane Otomasyonu
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                                <i class="fas fa-home me-1"></i> Ana Sayfa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('books*') ? 'active' : '' }}" href="{{ route('books.index') }}">
                                <i class="fas fa-book me-1"></i> Kitaplar
                            </a>
                        </li>

                        @auth
                            @if(Auth::user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('staff*') ? 'active' : '' }}" href="{{ route('staff.dashboard') }}">
                                        <i class="fas fa-id-card me-1"></i> Memur Paneli
                                    </a>
                                </li>
                            @endif
                            
                            @if(Auth::user()->hasRole('staff') && !Auth::user()->isAdmin())
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-tie me-1"></i> Kütüphane Memuru
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="staffDropdown">
                                        <a class="dropdown-item" href="{{ route('staff.dashboard') }}">
                                            <i class="fas fa-tachometer-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                            Gösterge Paneli
                                        </a>
                                        <a class="dropdown-item" href="{{ route('staff.books.index') }}">
                                            <i class="fas fa-book fa-sm fa-fw me-2 text-gray-400"></i>
                                            Kitap Yönetimi
                                        </a>
                                        <a class="dropdown-item" href="{{ route('staff.borrowings.index') }}">
                                            <i class="fas fa-hand-holding-heart fa-sm fa-fw me-2 text-gray-400"></i>
                                            Ödünç İşlemleri
                                        </a>
                                        <a class="dropdown-item" href="{{ route('staff.stocks.index') }}">
                                            <i class="fas fa-boxes fa-sm fa-fw me-2 text-gray-400"></i>
                                            Stok Yönetimi
                                        </a>
                                    </div>
                                </li>
                            @endif
                        @endauth
                        
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <i class="fas fa-sign-in-alt me-1"></i> {{ __('Giriş') }}
                                    </a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">
                                        <i class="fas fa-user-plus me-1"></i> {{ __('Üye Ol') }}
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    @if(Auth::user()->hasRole('admin'))
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-tachometer-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                            Admin Dashboard
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @endif

                                    @if(Auth::user()->hasRole('staff'))
                                        <a class="dropdown-item" href="{{ route('staff.dashboard') }}">
                                            <i class="fas fa-tachometer-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                            Kütüphane Memuru Paneli
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    @endif
                                    
                                    <a class="dropdown-item" href="{{ route('profile') }}">
                                        <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                                        {{ __('Profil') }}
                                    </a>
                                    
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
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
            </div>
        </main>
        
        <footer class="text-center">
            <div class="container">
                <p class="mb-0">&copy; {{ date('Y') }} Kütüphane Otomasyonu. Tüm hakları saklıdır.</p>
            </div>
        </footer>
    </div>
    
    <!-- jQuery ve Bootstrap JS -->
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
    @stack('scripts')
</body>
</html> 