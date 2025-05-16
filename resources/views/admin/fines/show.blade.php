@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ceza Detayı</h1>
        <a href="{{ route('admin.fines.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Geri Dön
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ceza Bilgileri</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 30%">Ceza ID</th>
                                    <td>{{ $fine->id }}</td>
                                </tr>
                                <tr>
                                    <th>Üye</th>
                                    <td>{{ $fine->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Kitap</th>
                                    <td>{{ $fine->book->title }}</td>
                                </tr>
                                <tr>
                                    <th>Gecikme (Gün)</th>
                                    <td>{{ $fine->days_late }}</td>
                                </tr>
                                <tr>
                                    <th>Ceza Tutarı</th>
                                    <td class="font-weight-bold">{{ number_format($fine->fine_amount, 2) }} TL</td>
                                </tr>
                                <tr>
                                    <th>Ödeme Durumu</th>
                                    <td>
                                        @if($fine->paid)
                                            <span class="badge badge-success">Ödendi</span>
                                            @if($fine->paid_at)
                                                <small class="text-muted ml-2">{{ $fine->paid_at->format('d.m.Y H:i') }}</small>
                                            @endif
                                        @else
                                            <span class="badge badge-danger">Ödenmedi</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Oluşturulma Tarihi</th>
                                    <td>{{ $fine->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">İşlemler</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <a href="{{ route('admin.fines.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> Tüm Cezalara Dön
                        </a>
                    </div>
                    
                    @if(!$fine->paid)
                    <div class="mb-2">
                        <form action="{{ route('admin.fines.mark-as-paid', $fine->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> Ödenmiş İşaretle
                            </button>
                        </form>
                    </div>
                    
                    <div class="mb-2">
                        <form action="{{ route('admin.fines.forgive', $fine->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-hand-holding-usd"></i> Cezayı Affet
                            </button>
                        </form>
                    </div>
                    @endif
                    
                    <div>
                        <form action="{{ route('admin.fines.destroy', $fine->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Bu ceza kaydını silmek istediğinize emin misiniz?')">
                                <i class="fas fa-trash"></i> Cezayı Sil
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 