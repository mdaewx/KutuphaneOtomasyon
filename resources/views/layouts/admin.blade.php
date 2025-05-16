<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'Kütüphane Otomasyon') }} Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --primary-color: #1f2937;
            --secondary-color: #3b82f6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --light-color: #f9fafb;
            --dark-color: #111827;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        /* Tüm tablo elemanları ve listelerde siyah metin rengi */
        table, .table, .table td, .table th, 
        select, select option, .select2-container--default .select2-selection--single .select2-selection__rendered,
        .dropdown-menu, .dropdown-item, .list-group-item {
            color: #000 !important;
        }
        
        /* Siyah metin rengine özel */
        .text-black, .publishers-text, .publisher-name, .publisher-item {
            color: #000 !important;
        }
        
        /* Yayınevi özel stilleri - Kesin siyah renk uygulaması */
        td, th, tr, select, select option, .select2-selection, .select2-selection__rendered, .select2-selection__choice, 
        .select2-container, .select2-dropdown, .select2-results__option, .select2-results__options li,
        .select2-search--dropdown .select2-search__field, #publisher_id option, #publisher, 
        option[value], .form-control, .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
            color: #000 !important;
        }
        
        /* En yüksek öncelik için geçersiz kılma */
        .form-control, select, select.form-control, select.form-select {
            color: #000 !important;
        }
        
        /* Kitap sayfası için özel stil */
        #booksTable td, #booksTable tr, #booksTable th, 
        input[name="publisher"], #publisher_id option, #publisher {
            color: #000 !important;
        }
        
        /* Badge stilleri - Daha iyi kontrast için */
        .badge {
            font-weight: 600 !important;
        }
        
        .badge-info, .badge-primary, .badge-secondary, 
        .badge-success, .badge-danger, .badge-warning {
            color: #fff !important;
        }
        
        /* Özel badge renkleri */
        .can-yayinlari, .badge-can-yayinlari {
            background-color: #264653 !important;
            color: #fff !important;
        }
        
        /* Yayınevi badge'lerinin tümünde siyah metin zorla */
        [class*="yayinevi"], td:nth-child(5) span {
            color: #fff !important;
            font-weight: 600 !important;
        }
        
        /* Sidebar Stilleri */
        .sidebar {
            background: var(--primary-color);
            min-height: 100vh;
            width: 250px;
            position: fixed;
            transition: all 0.3s;
            z-index: 999;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .sidebar-brand {
            padding: 1.5rem 1.5rem;
            color: white;
            font-weight: 700;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand sup {
            font-size: 0.6rem;
            top: -0.9em;
        }
        
        .sidebar .nav-item {
            margin-bottom: 0.25rem;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.85rem 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.75rem;
            font-size: 0.9rem;
            width: 20px;
            text-align: center;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-item.active .nav-link {
            color: white;
            background-color: var(--secondary-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .sidebar-heading {
            color: rgba(255,255,255,0.4);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1.5rem 1.5rem 0.5rem;
        }
        
        .sidebar-divider {
            margin: 0.5rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Content Stilleri */
        .content-wrapper {
            margin-left: 250px;
            padding: 1rem;
            padding-top: 86px; /* 70px navbar height + 16px padding */
            transition: all 0.3s;
            min-height: 100vh;
        }
        
        /* Kartlar */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.25rem;
        }
        
        .card-header h6 {
            font-weight: 700;
        }
        
        .border-left-primary {
            border-left: 4px solid var(--primary-color);
        }
        
        .border-left-success {
            border-left: 4px solid var(--success-color);
        }
        
        .border-left-info {
            border-left: 4px solid var(--info-color);
        }
        
        .border-left-warning {
            border-left: 4px solid var(--warning-color);
        }
        
        /* Navbar */
        .topbar {
            height: 70px;
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px 0 rgba(0,0,0,0.06);
            z-index: 100;
            position: fixed;
            top: 0;
            right: 0;
            width: calc(100% - 250px);
            z-index: 1030;
        }
        
        .navbar-search .input-group {
            border-radius: 1rem;
            overflow: hidden;
        }
        
        .navbar-search .form-control {
            padding-left: 1rem;
            border-top-left-radius: 1rem;
            border-bottom-left-radius: 1rem;
        }
        
        .navbar-search .btn {
            border-top-right-radius: 1rem;
            border-bottom-right-radius: 1rem;
        }
        
        /* Butonlar */
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        
        .text-primary {
            color: var(--secondary-color) !important;
        }
        
        /* Profil resmi */
        .img-profile {
            height: 40px;
            width: 40px;
        }
        
        /* Yayınevi özel renk stilleri ve diğer özel renk kodları için */
        .badge.badge-info { 
            background-color: #17a2b8 !important; 
            color: #fff !important; 
        }
        
        .badge.badge-primary { 
            background-color: #007bff !important; 
            color: #fff !important; 
        }
        
        .badge.badge-success { 
            background-color: #28a745 !important; 
            color: #fff !important; 
        }
        
        .badge.badge-danger { 
            background-color: #dc3545 !important; 
            color: #fff !important; 
        }
        
        .badge.badge-dark { 
            background-color: #343a40 !important; 
            color: #fff !important; 
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .content-wrapper {
                margin-left: 0;
                padding-top: 86px;
            }
            
            .sidebar.toggled {
                margin-left: 0;
            }
            
            .content-wrapper.toggled {
                margin-left: 250px;
            }
            
            .topbar {
                width: 100%;
            }
            
            .topbar.toggled {
                width: calc(100% - 250px);
            }
        }
    </style>

    @yield('styles')
</head>

<body>
    <div id="app">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar - Brand -->
            <div class="sidebar-brand">
                <i class="fas fa-book-open fa-lg me-2"></i>
                <span>Kütüphane<sup>Otomasyon</sup></span>
            </div>

            <!-- Nav Item - Dashboard -->
            <ul class="nav flex-column">
                <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <div class="sidebar-heading">Arayüz</div>
                <hr class="sidebar-divider">
                
                <li class="nav-item {{ request()->routeIs('admin.profiles.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.profiles.index') }}">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin Profili</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.users.index') }}">
                        <i class="fas fa-users"></i>
                        <span>Kullanıcılar</span>
                    </a>
                </li>

                <div class="sidebar-heading">Addonlar</div>
                <hr class="sidebar-divider">

                <li class="nav-item {{ request()->routeIs('admin.books.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.books.index') }}">
                        <i class="fas fa-book"></i>
                        <span>Kitaplar</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.authors.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.authors.index') }}">
                        <i class="fas fa-pen-fancy"></i>
                        <span>Yazarlar</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.publishers.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.publishers.index') }}">
                        <i class="fas fa-building"></i>
                        <span>Yayınevleri</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.categories.index') }}">
                        <i class="fas fa-folder"></i>
                        <span>Kategoriler</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.borrowings.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.borrowings.index') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Ödünç İşlemleri</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.fines.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.fines.index') }}">
                        <i class="fas fa-money-bill"></i>
                        <span>Ceza İşlemleri</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.acquisitions.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.acquisitions.index') }}">
                        <i class="fas fa-file-invoice"></i>
                        <span>Edinme Kaynakları</span>
                    </a>
                </li>

                <!-- Stok Yönetimi -->
                <li class="nav-item {{ request()->routeIs('admin.stocks.*') ? 'active' : '' }}">
                    <a class="nav-link" data-bs-toggle="collapse" href="#collapseStock" role="button"
                        aria-expanded="{{ request()->routeIs('admin.stocks.*') ? 'true' : 'false' }}" aria-controls="collapseStock">
                        <i class="fas fa-boxes"></i>
                        <span>Stok Yönetimi</span>
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.stocks.*') ? 'show' : '' }}" id="collapseStock">
                        <div class="nav flex-column ms-3 mt-2">
                            <a class="nav-link py-2 {{ request()->routeIs('admin.stocks.index') ? 'active' : '' }}" 
                               href="{{ route('admin.stocks.index') }}">
                                <i class="fas fa-list fa-sm fa-fw me-2"></i>Stok Listesi
                            </a>
                            <a class="nav-link py-2 {{ request()->routeIs('admin.stocks.create') ? 'active' : '' }}" 
                               href="{{ route('admin.stocks.create') }}">
                                <i class="fas fa-plus fa-sm fa-fw me-2"></i>Yeni Stok
                            </a>
                        </div>
                    </div>
                </li>

                <!-- Raf Yönetimi -->
                <li class="nav-item {{ request()->routeIs('admin.shelf-management.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.shelf-management.index') }}">
                        <i class="fas fa-th-large"></i>
                        <span>Raf Düzenleme</span>
                    </a>
                </li>

                <div class="sidebar-heading">Diğer</div>
                <hr class="sidebar-divider">

                <li class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.reports.index') }}">
                        <i class="fas fa-chart-area"></i>
                        <span>Raporlar</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.settings.index') }}">
                        <i class="fas fa-cog"></i>
                        <span>Ayarlar</span>
                    </a>
                </li>
            </ul>
            
            <!-- Sidebar Toggler -->
            <div class="text-center d-md-none mt-4">
                <button class="btn btn-sm btn-light rounded-circle border-0" id="sidebarToggle">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
        </div>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light topbar mb-4">
                <!-- Sidebar Toggle (Mobile) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <!-- Search -->
                <form class="d-none d-sm-inline-block form-inline me-auto ms-md-3 my-2 my-md-0 w-50">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Ara..."
                            aria-label="Search" aria-describedby="basic-addon2">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </form>

                <!-- Topbar Navbar -->
                <ul class="navbar-nav ms-auto">
                    <!-- User Information -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-none d-lg-inline text-gray-600 small me-2">{{ Auth::user()->name }}</span>
                            @if (Auth::user()->profile_photo)
                                <img class="img-profile rounded-circle" 
                                    src="{{ asset('storage/profiles/' . Auth::user()->profile_photo) }}" 
                                    alt="{{ Auth::user()->name }}">
                            @else
                                <img class="img-profile rounded-circle" 
                                    src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=7F9CF5&background=EBF4FF" 
                                    alt="{{ Auth::user()->name }}">
                            @endif
                        </a>
                        <!-- Dropdown - User Information -->
                        <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                            aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="{{ route('profile') }}">
                                <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                                Profil
                            </a>
                            <a class="dropdown-item" href="{{ route('staff.dashboard') }}">
                                <i class="fas fa-id-card fa-sm fa-fw me-2 text-gray-400"></i>
                                Personel Paneli
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                Çıkış Yap
                            </a>
                            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </nav>
            
            <!-- Main Content -->
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
            
            <!-- Footer -->
            <footer class="sticky-footer bg-white mt-4">
                <div class="container">
                    <div class="copyright text-center my-auto py-3">
                        <span>Copyright &copy; {{ config('app.name') }} {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Sidebar toggle for mobile
            $('#sidebarToggle, #sidebarToggleTop').on('click', function(e) {
                $('body').toggleClass('sidebar-toggled');
                $('.sidebar').toggleClass('toggled');
                $('.content-wrapper').toggleClass('toggled');
                $('.topbar').toggleClass('toggled');
            });

            // Close any open menu accordions when window is resized
            $(window).resize(function() {
                if ($(window).width() < 768) {
                    $('.sidebar .collapse').collapse('hide');
                }
            });

            // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
            $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
                if ($(window).width() > 768) {
                    var e0 = e.originalEvent,
                        delta = e0.wheelDelta || -e0.detail;
                    this.scrollTop += (delta < 0 ? 1 : -1) * 30;
                    e.preventDefault();
                }
            });

            // Active menu item highlight
            var current = location.pathname;
            $('.nav-item a.nav-link').each(function() {
                var $this = $(this);
                if ($this.attr('href') == current) {
                    $this.addClass('active');
                    $this.parents('.nav-item').addClass('active');
                    if ($this.parents('.collapse').length) {
                        $this.parents('.collapse').addClass('show');
                    }
                }
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html> 