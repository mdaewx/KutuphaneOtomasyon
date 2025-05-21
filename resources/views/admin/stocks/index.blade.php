@extends('layouts.admin')

@section('title', 'Stok Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Stok Yönetimi</h1>
                <div>
                   
                    <a href="{{ route('admin.stocks.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Stok Ekle
                    </a>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stok Listesi</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="stocksTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Barkod</th>
                                    <th>Kitap</th>
                                    <th>ISBN</th>
                                    <th>Raf</th>
                                    <th>Edinme Kaynağı</th>
                                    <th>Edinme Tarihi</th>
                                    <th>Edinme Fiyatı</th>
                                    <th>Durum</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                <tr>
                                    <td>{{ $stock->barcode }}</td>
                                    <td>{{ $stock->book->title }}</td>
                                    <td>{{ $stock->book->isbn }}</td>
                                    <td>{{ $stock->shelf ? $stock->shelf->name : '-' }}</td>
                                    <td>
                                        @if($stock->acquisitionSource && $stock->acquisitionSource->sourceType)
                                            <strong>{{ $stock->acquisitionSource->sourceType->name }}</strong>
                                            <small>{{ $stock->acquisitionSource->source_name }}</small>
                                        @elseif($stock->acquisitionSource)
                                            <strong>-</strong>
                                            <small>{{ $stock->acquisitionSource->source_name }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $stock->acquisition_date ? $stock->acquisition_date->format('d.m.Y') : '-' }}</td>
                                    <td>{{ $stock->acquisition_price ? number_format($stock->acquisition_price, 2) . ' ₺' : '-' }}</td>
                                    <td>
                                        @if($stock->is_available)
                                            <span class="badge badge-success">Müsait</span>
                                        @else
                                            <span class="badge badge-warning">Ödünç Verilmiş</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.stocks.show', $stock) }}" class="btn btn-info btn-sm" title="Görüntüle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.stocks.edit', $stock) }}" class="btn btn-primary btn-sm" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($stock->is_available)
                                                <form action="{{ route('admin.stocks.destroy', $stock) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Sil"
                                                            onclick="return confirm('Bu stok kaydını silmek istediğinize emin misiniz?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $stocks->links() }}
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
    $('#stocksTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        columnDefs: [
            {
                targets: [8],
                orderable: false
            }
        ]
    });
});
</script>
@endsection 