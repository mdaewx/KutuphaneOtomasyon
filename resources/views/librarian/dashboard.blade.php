@extends('layouts.librarian')

@section('title', 'Kütüphane Memuru Paneli')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-4">Kütüphane Memuru Paneli</h1>
            <p class="text-muted mb-4">Hoş geldiniz, {{ Auth::user()->name }}. Kütüphane işlemlerini buradan yönetebilirsiniz.</p>
        </div>
    </div>

    <!-- Bilgi Kartları -->
    <div class="row">
        <!-- Bugün Ödünç Verilenler -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Bugün Ödünç</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayBorrowings ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- İade Beklenenler -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                İade Bekleyen</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingReturns ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gecikmiş İadeler -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Gecikmiş</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueBooks ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boş Raflar -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Kullanılabilir Raf</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $availableShelves ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bookmark fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hızlı İşlemler -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hızlı İşlemler</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('librarian.borrowings.create') }}" class="btn btn-primary btn-block py-3">
                                <i class="fas fa-hand-holding-heart mr-2"></i> Kitap Ödünç Ver
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('librarian.returns.create') }}" class="btn btn-success btn-block py-3">
                                <i class="fas fa-undo mr-2"></i> Kitap İadesi Al
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('librarian.stocks.create') }}" class="btn btn-info btn-block py-3">
                                <i class="fas fa-box mr-2"></i> Yeni Stok Ekle
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('librarian.members.create') }}" class="btn btn-secondary btn-block py-3">
                                <i class="fas fa-user-plus mr-2"></i> Yeni Üye Ekle
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Bugün Gerçekleşen İşlemler -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Bugünün İşlemleri</h6>
                    <a href="{{ route('librarian.activities.index') }}" class="btn btn-sm btn-primary">Tümünü Gör</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>İşlem</th>
                                    <th>Kitap</th>
                                    <th>Üye</th>
                                    <th>Saat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayActivities ?? [] as $activity)
                                <tr>
                                    <td>
                                        @if($activity->type == 'borrow')
                                            <span class="badge bg-primary">Ödünç</span>
                                        @elseif($activity->type == 'return')
                                            <span class="badge bg-success">İade</span>
                                        @else
                                            <span class="badge bg-secondary">Diğer</span>
                                        @endif
                                    </td>
                                    <td>{{ $activity->book->title ?? 'Bilinmiyor' }}</td>
                                    <td>{{ $activity->user->name ?? 'Bilinmiyor' }}</td>
                                    <td>{{ $activity->created_at ? $activity->created_at->format('H:i') : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Bugün henüz işlem yapılmadı.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yakında Raf Değişimi Gereken Kitaplar -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Gecikmiş Kitaplar</h6>
                    <a href="{{ route('librarian.overdue') }}" class="btn btn-sm btn-primary">Tümünü Gör</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Kitap</th>
                                    <th>Üye</th>
                                    <th>Son Tarih</th>
                                    <th>Gecikme</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overdueBorrowings ?? [] as $borrowing)
                                <tr>
                                    <td>{{ $borrowing->book->title ?? 'Bilinmiyor' }}</td>
                                    <td>{{ $borrowing->user->name ?? 'Bilinmiyor' }}</td>
                                    <td>{{ $borrowing->due_date ? $borrowing->due_date->format('d.m.Y') : '-' }}</td>
                                    <td>
                                        @if($borrowing->due_date)
                                            {{ now()->diffInDays($borrowing->due_date, false) * -1 }} gün
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Gecikmiş kitap bulunmamaktadır.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 