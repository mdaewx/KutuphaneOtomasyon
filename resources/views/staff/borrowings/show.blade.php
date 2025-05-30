@extends('layouts.staff')

@section('title', 'Ödünç Detayı')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ödünç Detayı</h1>
        <a href="{{ route('staff.borrowings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Geri Dön
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ödünç Bilgileri</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="font-weight-bold">Kullanıcı Bilgileri</h5>
                    <hr>
                    <p><strong>Ad Soyad:</strong> {{ $borrowing->user->name }}</p>
                    <p><strong>E-posta:</strong> {{ $borrowing->user->email }}</p>
                </div>
                <div class="col-md-6">
                    <h5 class="font-weight-bold">Kitap Bilgileri</h5>
                    <hr>
                    <p><strong>Kitap Adı:</strong> {{ $borrowing->book->title }}</p>
                    <p><strong>Yazar:</strong> {{ $borrowing->book->author }}</p>
                    <p><strong>ISBN:</strong> {{ $borrowing->book->isbn }}</p>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="font-weight-bold">Ödünç Detayları</h5>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">Ödünç Tarihi</th>
                                <td>{{ $borrowing->borrow_date ? $borrowing->borrow_date->format('d.m.Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Son İade Tarihi</th>
                                <td>{{ $borrowing->due_date ? $borrowing->due_date->format('d.m.Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>İade Tarihi</th>
                                <td>{{ $borrowing->returned_at ? $borrowing->returned_at->format('d.m.Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Durum</th>
                                <td>
                                    @if($borrowing->status == 'pending')
                                        <span class="badge bg-warning">Beklemede</span>
                                    @elseif($borrowing->status == 'approved' && $borrowing->due_date < now() && !$borrowing->returned_at)
                                        <span class="badge bg-danger">Gecikmiş</span>
                                    @elseif($borrowing->status == 'approved' && !$borrowing->returned_at)
                                        <span class="badge bg-success">Onaylandı</span>
                                    @elseif($borrowing->status == 'returned' || $borrowing->returned_at)
                                        <span class="badge bg-info">İade Edildi</span>
                                        @if($borrowing->condition == 'damaged')
                                            <span class="badge bg-warning">Hasarlı</span>
                                        @elseif($borrowing->condition == 'lost')
                                            <span class="badge bg-danger">Kayıp</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">{{ $borrowing->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($borrowing->condition == 'damaged')
                            <tr>
                                <th>Hasar Açıklaması</th>
                                <td>{{ $borrowing->damage_description ?: 'Açıklama girilmemiş' }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Notlar</th>
                                <td>{{ $borrowing->notes ?: 'Not girilmemiş' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            @if($borrowing->fines->isNotEmpty())
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="font-weight-bold">Cezalar</h5>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Tür</th>
                                    <th>Açıklama</th>
                                    <th>Tutar</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($borrowing->fines as $fine)
                                <tr>
                                    <td>{{ $fine->created_at->format('d.m.Y') }}</td>
                                    <td>
                                        @if($fine->type == 'late')
                                            <span class="badge bg-warning">Gecikme</span>
                                        @elseif($fine->type == 'damage')
                                            <span class="badge bg-danger">Hasar</span>
                                        @elseif($fine->type == 'lost')
                                            <span class="badge bg-dark">Kayıp</span>
                                        @endif
                                    </td>
                                    <td>{{ $fine->description }}</td>
                                    <td>{{ number_format($fine->amount, 2) }} ₺</td>
                                    <td>
                                        @if($fine->status == 'pending')
                                            <span class="badge bg-warning">Ödenmedi</span>
                                        @elseif($fine->status == 'paid')
                                            <span class="badge bg-success">Ödendi</span>
                                        @elseif($fine->status == 'forgiven')
                                            <span class="badge bg-info">Affedildi</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-4">
                @if(!$borrowing->returned_at)
                    <a href="{{ route('staff.borrowings.edit', $borrowing) }}" class="btn btn-primary">
                        <i class="fas fa-undo"></i> İade Al
                    </a>
                @endif
                <a href="{{ route('staff.borrowings.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri Dön
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 