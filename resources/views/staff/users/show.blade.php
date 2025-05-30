@extends('layouts.staff')

@section('title', 'Üye Detayı')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Üye Detayı</h1>
        <a href="{{ route('staff.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Geri Dön
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- Üye Bilgileri -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Üye Bilgileri</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                 class="img-profile rounded-circle" 
                                 style="width: 150px; height: 150px; object-fit: cover;"
                                 alt="{{ $user->name }}">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto"
                                 style="width: 150px; height: 150px; font-size: 3em;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th>Ad Soyad:</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>E-posta:</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>Üyelik Tarihi:</th>
                                <td>{{ $user->created_at->format('d.m.Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">İstatistikler</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Toplam Ödünç
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $stats['total_borrowings'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Aktif Ödünç
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $stats['active_borrowings'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Toplam Ceza
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($stats['total_fines'], 2) }} ₺
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Bekleyen Ceza
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ number_format($stats['pending_fines'], 2) }} ₺
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ödünç ve Ceza Bilgileri -->
        <div class="col-xl-8 col-lg-7">
            <!-- Aktif Ödünç Alınan Kitaplar -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aktif Ödünç Alınan Kitaplar</h6>
                </div>
                <div class="card-body">
                    @if($activeBorrowings->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kitap</th>
                                        <th>Alınma Tarihi</th>
                                        <th>İade Tarihi</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeBorrowings as $borrowing)
                                        <tr>
                                            <td>{{ $borrowing->book->title }}</td>
                                            <td>{{ $borrowing->borrow_date->format('d.m.Y') }}</td>
                                            <td>{{ $borrowing->due_date->format('d.m.Y') }}</td>
                                            <td>
                                                @if($borrowing->isOverdue())
                                                    <span class="badge bg-danger">{{ $borrowing->getOverdueDays() }} Gün Gecikmiş</span>
                                                @else
                                                    <span class="badge bg-success">Zamanında</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Aktif ödünç alınan kitap bulunmamaktadır.</p>
                    @endif
                </div>
            </div>

            <!-- Bekleyen Cezalar -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Bekleyen Cezalar</h6>
                </div>
                <div class="card-body">
                    @if($pendingFines->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kitap</th>
                                        <th>Ceza Türü</th>
                                        <th>Tutar</th>
                                        <th>Tarih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingFines as $fine)
                                        <tr>
                                            <td>{{ $fine->book->title }}</td>
                                            <td>
                                                @if($fine->type == 'late')
                                                    <span class="badge bg-warning">Gecikme</span>
                                                @elseif($fine->type == 'damage')
                                                    <span class="badge bg-danger">Hasar</span>
                                                @elseif($fine->type == 'lost')
                                                    <span class="badge bg-dark">Kayıp</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($fine->amount, 2) }} ₺</td>
                                            <td>{{ $fine->created_at->format('d.m.Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Bekleyen ceza bulunmamaktadır.</p>
                    @endif
                </div>
            </div>

            <!-- Ödünç Geçmişi -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ödünç Geçmişi</h6>
                </div>
                <div class="card-body">
                    @if($borrowingHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kitap</th>
                                        <th>Alınma Tarihi</th>
                                        <th>İade Tarihi</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrowingHistory as $borrowing)
                                        <tr>
                                            <td>{{ $borrowing->book->title }}</td>
                                            <td>{{ $borrowing->borrow_date->format('d.m.Y') }}</td>
                                            <td>{{ $borrowing->returned_at->format('d.m.Y') }}</td>
                                            <td>
                                                @if($borrowing->condition === 'good')
                                                    <span class="badge bg-success">İyi Durumda</span>
                                                @elseif($borrowing->condition === 'damaged')
                                                    <span class="badge bg-warning">Hasarlı</span>
                                                @elseif($borrowing->condition === 'lost')
                                                    <span class="badge bg-danger">Kayıp</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Ödünç geçmişi bulunmamaktadır.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 