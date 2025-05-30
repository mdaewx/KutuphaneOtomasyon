@extends('layouts.staff')

@section('title', 'Ceza İşlemleri')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ceza İşlemleri</h1>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ceza Listesi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="finesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Üye</th>
                            <th>Kitap</th>
                            <th>Ceza Türü</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fines as $fine)
                            <tr>
                                <td>{{ $fine->id }}</td>
                                <td>
                                    {{ $fine->user->name }}
                                    <small class="d-block text-muted">{{ $fine->user->email }}</small>
                                </td>
                                <td>
                                    {{ $fine->book->title }}
                                    <small class="d-block text-muted">ISBN: {{ $fine->book->isbn }}</small>
                                </td>
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
                                    @if($fine->description)
                                        <small class="d-block">{{ $fine->description }}</small>
                                    @endif
                                </td>
                                <td>{{ number_format($fine->amount, 2) }} ₺</td>
                                <td>
                                    @if($fine->status == 'pending')
                                        <span class="badge bg-warning">Beklemede</span>
                                    @elseif($fine->status == 'paid')
                                        <span class="badge bg-success">Ödendi</span>
                                        @if($fine->paid_at)
                                            <small class="d-block">{{ $fine->paid_at->format('d.m.Y') }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">İptal Edildi</span>
                                    @endif
                                </td>
                                <td>{{ $fine->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if($fine->status == 'pending')
                                        <form action="{{ route('staff.fines.mark-as-paid', $fine) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Cezayı ödenmiş olarak işaretlemek istediğinize emin misiniz?')">
                                                <i class="fas fa-check"></i> Ödendi
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('staff.fines.forgive', $fine) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Cezayı affetmek istediğinize emin misiniz?')">
                                                <i class="fas fa-hand-holding-heart"></i> Affet
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('staff.fines.show', $fine) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Detay
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#finesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json"
        },
        "order": [[ 0, "desc" ]],
        "pageLength": 25
    });
});
</script>
@endpush 