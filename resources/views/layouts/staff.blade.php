<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Personel Paneli</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom styles -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #4e73df;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            min-height: 100vh;
            width: 250px;
            position: fixed;
            transition: all 0.3s;
            z-index: 999;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .sidebar-brand {
            padding: 1.5rem 1rem;
            color: white;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-item {
            margin-bottom: 0;
            position: relative;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            font-weight: 600;
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
        
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.15);
            font-weight: 700;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .sidebar-heading {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 1rem 1rem 0.5rem;
            margin-top: 0.5rem;
        }
        
        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 0 1rem 0.5rem;
        }
        
        /* Content Styles */
        .content-wrapper {
            margin-left: 250px;
            padding: 0;
            transition: all 0.3s;
            min-height: 100vh;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
        }
        
        .card-header h6 {
            font-weight: 700;
            margin: 0;
        }
        
        /* Navbar */
        .topbar {
            height: 4.375rem;
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            position: fixed;
            top: 0;
            right: 0;
            width: calc(100% - 250px);
            z-index: 1030;
            transition: all 0.3s;
        }
        
        /* Main Content Container */
        .container-fluid {
            padding-top: 4.375rem; /* Match the topbar height */
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            padding-bottom: 1.5rem;
        }
        
        /* Form fixes */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .row {
            --bs-gutter-x: 1.5rem;
            margin-left: calc(var(--bs-gutter-x) * -0.5);
            margin-right: calc(var(--bs-gutter-x) * -0.5);
        }
        
        .row > * {
            padding-right: calc(var(--bs-gutter-x) * 0.5);
            padding-left: calc(var(--bs-gutter-x) * 0.5);
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        /* User Profile */
        .img-profile {
            height: 2rem;
            width: 2rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .content-wrapper {
                margin-left: 0;
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
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar - Brand -->
            <div class="sidebar-brand">
                <i class="fas fa-book-reader me-2"></i>
                <span>Personel Paneli</span>
            </div>

            <div class="sidebar-heading">Temel</div>
            <hr class="sidebar-divider">

            <!-- Nav Items -->
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" 
                       href="{{ route('staff.dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <div class="sidebar-heading">Kütüphane</div>
                <hr class="sidebar-divider">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.books.*') ? 'active' : '' }}"
                       href="{{ route('staff.books.index') }}">
                        <i class="fas fa-book"></i>
                        <span>Kitaplar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.borrowings.*') ? 'active' : '' }}"
                       href="{{ route('staff.borrowings.index') }}">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Ödünç İşlemleri</span>
                    </a>
                </li>
                
                <!-- Stok Yönetimi -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.stocks.*') ? 'active' : '' }}" 
                       href="#collapseStock" 
                       data-bs-toggle="collapse" 
                       role="button" 
                       aria-expanded="{{ request()->routeIs('staff.stocks.*') ? 'true' : 'false' }}" 
                       aria-controls="collapseStock">
                        <i class="fas fa-boxes"></i>
                        <span>Stok Yönetimi</span>
                        <i class="fas fa-angle-down ms-auto"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('staff.stocks.*') ? 'show' : '' }}" id="collapseStock">
                        <div class="ms-4">
                            <a class="nav-link py-1 {{ request()->routeIs('staff.stocks.index') ? 'active' : '' }}" 
                               href="{{ route('staff.stocks.index') }}">
                                <i class="fas fa-list fa-sm fa-fw me-2"></i>
                                <span>Stok Listesi</span>
                            </a>
                            <a class="nav-link py-1 {{ request()->routeIs('staff.stocks.create') ? 'active' : '' }}" 
                               href="{{ route('staff.stocks.create') }}">
                                <i class="fas fa-plus fa-sm fa-fw me-2"></i>
                                <span>Yeni Stok</span>
                            </a>
                        </div>
                    </div>
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
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <i class="fas fa-home fa-sm fa-fw me-2 text-gray-400"></i>
                                Ana Sayfa
                            </a>
                            <a class="dropdown-item" href="{{ route('profile') }}">
                                <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                                Profil
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