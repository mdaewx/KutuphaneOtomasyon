@extends('layouts.app')

@section('title', 'Gecikme Cezaları')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Gecikme Cezaları</h5>
                </div>
                <div class="card-body">
                    @if($fines->isEmpty())
                        <div class="alert alert-info">
                            Henüz gecikme cezanız bulunmamaktadır.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kitap</th>
                                        <th>Gecikme Süresi</th>
                                        <th>Ceza Tutarı</th>
                                        <th>Durum</th>
                                        <th>Ödeme Yöntemi</th>
                                        <th>Ödeme Tarihi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fines as $fine)
                                        <tr>
                                            <td>
                                                {{ $fine->borrowing->book->title }}
                                                <small class="d-block text-muted">
                                                    Son Teslim: {{ $fine->borrowing->due_date->format('d.m.Y') }}
                                                    <br>
                                                    Teslim Tarihi: {{ $fine->borrowing->returned_at->format('d.m.Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                {{ $fine->borrowing->returned_at->diffInDays($fine->borrowing->due_date) }} gün
                                            </td>
                                            <td>{{ number_format($fine->amount, 2) }} TL</td>
                                            <td>
                                                @if($fine->payment_status === 'paid')
                                                    <span class="badge bg-success">Ödendi</span>
                                                @elseif($fine->payment_status === 'pending')
                                                    <span class="badge bg-warning">Beklemede</span>
                                                @else
                                                    <span class="badge bg-secondary">İptal Edildi</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($fine->payment_method === 'cash')
                                                    Nakit
                                                @elseif($fine->payment_method === 'bank_transfer')
                                                    Banka Havalesi
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                {{ $fine->paid_at ? $fine->paid_at->format('d.m.Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading">Ödeme Bilgileri</h6>
                            <p class="mb-0">
                                Gecikme cezalarınızı aşağıdaki yöntemlerle ödeyebilirsiniz:
                            </p>
                            <ul class="mb-0">
                                <li>Kütüphane veznesine nakit ödeme yapabilirsiniz.</li>
                                <li>
                                    Banka havalesi ile ödeme yapabilirsiniz:
                                    <br>
                                    Banka: XXX Bank
                                    <br>
                                    IBAN: TR00 0000 0000 0000 0000 0000 00
                                    <br>
                                    <small class="text-muted">* Açıklama kısmına T.C. Kimlik numaranızı yazmayı unutmayınız.</small>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 