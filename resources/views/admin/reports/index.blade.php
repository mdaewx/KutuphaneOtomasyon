@extends('layouts.admin')

@section('title', 'Raporlar')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Raporlar</h1>

    <div class="row">
        <!-- Popüler Kitaplar Raporu -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Popüler Kitaplar</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">En çok ödünç alınan kitaplar</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.reports.popular-books') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-chart-bar mr-1"></i> Raporu Görüntüle
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktif Kullanıcılar Raporu -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aktif Kullanıcılar</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">En çok kitap ödünç alan kullanıcılar</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.reports.active-users') }}" class="btn btn-success btn-block">
                            <i class="fas fa-chart-bar mr-1"></i> Raporu Görüntüle
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gecikmiş Teslimler Raporu -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Gecikmiş Teslimler</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Zamanında teslim edilmeyen kitaplar</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.reports.overdue') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-chart-bar mr-1"></i> Raporu Görüntüle
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aylık İstatistikler Raporu -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Aylık İstatistikler</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Aylık ödünç verme istatistikleri</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.reports.monthly-stats') }}" class="btn btn-info btn-block">
                            <i class="fas fa-chart-bar mr-1"></i> Raporu Görüntüle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Kategori İstatistikleri Raporu -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Kategori İstatistikleri</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Kategorilere göre kitap dağılımları</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.reports.categories') }}" class="btn btn-danger btn-block">
                            <i class="fas fa-chart-pie mr-1"></i> Raporu Görüntüle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 