@extends('layouts.staff')

@section('title', 'Ceza Detayı')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ceza Detayı</h1>
        <a href="{{ route('staff.fines.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Geri Dön
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ceza Bilgileri</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th style="width: 200px;">Ceza ID:</th>
                                <td>{{ $fine->id }}</td>
                            </tr>
                            <tr>
                                <th>Ceza Türü:</th>
                                <td>
                                    @if($fine->type == 'late')
                                        <span class="badge bg-warning">Gecikme Cezası</span>
                                    @elseif($fine->type == 'damage')
                                        <span class="badge bg-danger">Hasar Cezası</span>
                                        @if($fine->damage_level)
                                            <small class="d-block mt-1">
                                                Hasar Seviyesi: 
                                                @if($fine->damage_level == 'minor')
                                                    <span class="text-warning">Hafif</span>
                                                @elseif($fine->damage_level == 'moderate')
                                                    <span class="text-orange">Orta</span>
                                                @elseif($fine->damage_level == 'severe')
                                                    <span class="text-danger">Ağır</span>
                                                @endif
                                            </small>
                                        @endif
                                    @elseif($fine->type == 'lost')
                                        <span class="badge bg-dark">Kayıp Cezası</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tutar:</th>
                                <td>{{ number_format($fine->amount, 2) }} ₺</td>
                            </tr>
                            <tr>
                                <th>Durum:</th>
                                <td>
                                    @if($fine->isPending())
                                        <span class="badge bg-warning">Beklemede</span>
                                    @elseif($fine->isPaid())
                                        <span class="badge bg-success">Ödendi</span>
                                    @else
                                        <span class="badge bg-secondary">İptal Edildi</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Oluşturulma Tarihi:</th>
                                <td>{{ $fine->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            @if($fine->paid_at)
                            <tr>
                                <th>Ödenme Tarihi:</th>
                                <td>{{ $fine->paid_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            @endif
                            @if($fine->payment_method)
                            <tr>
                                <th>Ödeme Yöntemi:</th>
                                <td>
                                    @if($fine->payment_method == 'cash')
                                        Nakit
                                    @elseif($fine->payment_method == 'credit_card')
                                        Kredi Kartı
                                    @elseif($fine->payment_method == 'bank_transfer')
                                        Banka Havalesi
                                    @else
                                        {{ $fine->payment_method }}
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if($fine->description)
                            <tr>
                                <th>Açıklama:</th>
                                <td>{{ $fine->description }}</td>
                            </tr>
                            @endif
                            @if($fine->damage_description)
                            <tr>
                                <th>Hasar Açıklaması:</th>
                                <td>{{ $fine->damage_description }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Üye ve Kitap Bilgileri</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th style="width: 200px;">Üye Adı:</th>
                                <td>{{ $fine->user->name }}</td>
                            </tr>
                            <tr>
                                <th>E-posta:</th>
                                <td>{{ $fine->user->email }}</td>
                            </tr>
                            <tr>
                                <th>Kitap:</th>
                                <td>{{ $fine->book->title }}</td>
                            </tr>
                            <tr>
                                <th>ISBN:</th>
                                <td>{{ $fine->book->isbn }}</td>
                            </tr>
                            <tr>
                                <th>Yazar:</th>
                                <td>{{ $fine->book->authors->pluck('name')->join(', ') }}</td>
                            </tr>
                        </table>
                    </div>

                    @if($fine->isPending())
                    <div class="mt-4">
                        <form action="{{ route('staff.fines.mark-as-paid', $fine) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success" onclick="return confirm('Cezayı ödenmiş olarak işaretlemek istediğinize emin misiniz?')">
                                <i class="fas fa-check"></i> Ödendi Olarak İşaretle
                            </button>
                        </form>
                        
                        <form action="{{ route('staff.fines.forgive', $fine) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Cezayı affetmek istediğinize emin misiniz?')">
                                <i class="fas fa-hand-holding-heart"></i> Affet
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 