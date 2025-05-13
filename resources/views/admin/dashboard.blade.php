@extends('layouts.admin')

@section('title', 'Yönetici Paneli')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Yönetici Paneli</h1>
    </div>

    <!-- İstatistik Kartları -->
    <div class="row mb-4">
        <!-- Toplam Kitap Kartı -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Toplam Kitap</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBooks }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toplam Kullanıcı Kartı -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Toplam Kullanıcı</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktif Ödünç Kartı -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Aktif Ödünç</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeBorrowings }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gecikmiş İadeler Kartı -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Gecikmiş İadeler</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueBorrowings }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- İçerik Satırı -->
    <div class="row">
        <!-- Son Eklenen Kullanıcılar -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Son Eklenen Kullanıcılar</h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-users"></i> Tümünü Gör
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>İsim</th>
                                    <th>E-posta</th>
                                    <th>Kayıt Tarihi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestUsers as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->created_at ? $user->created_at->format('d.m.Y') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Son Eklenen Kitaplar -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Son Eklenen Kitaplar</h6>
                    <a href="{{ route('admin.books.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-book"></i> Tümünü Gör
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>Yazar</th>
                                    <th>ISBN</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestBooks as $book)
                                <tr>
                                    <td>{{ $book->title }}</td>
                                    <td>{{ $book->author }}</td>
                                    <td>{{ $book->isbn }}</td>
                                    <td>{{ $book->quantity }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Son Ödünç İşlemleri -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Son Ödünç İşlemleri</h6>
                    <a href="{{ route('admin.borrowings.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-clipboard-list"></i> Tümünü Gör
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Kullanıcı</th>
                                    <th>Kitap</th>
                                    <th>Ödünç Tarihi</th>
                                    <th>Son İade Tarihi</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBorrowings as $borrowing)
                                <tr class="{{ $borrowing->returned_at ? '' : ($borrowing->due_date < now() ? 'table-danger' : '') }}">
                                    <td>{{ $borrowing->user->name }}</td>
                                    <td>{{ $borrowing->book->title }}</td>
                                    <td>{{ $borrowing->borrow_date->format('d.m.Y') }}</td>
                                    <td>{{ $borrowing->due_date->format('d.m.Y') }}</td>
                                    <td>
                                        @if($borrowing->returned_at)
                                            <span class="badge bg-success">İade Edildi</span>
                                        @elseif($borrowing->due_date < now())
                                            <span class="badge bg-danger">Gecikmiş</span>
                                        @else
                                            <span class="badge bg-info">Devam Ediyor</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Data Tables için varsayılan ayarlar
        $('.table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Turkish.json"
            },
            "pageLength": 5,
            "ordering": true,
            "info": false,
            "dom": 'ftp'
        });
    });
</script>
@endsection 