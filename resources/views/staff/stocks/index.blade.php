@extends('layouts.staff')

@section('title', 'Stok Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Stok Yönetimi</h1>
        <a href="{{ route('staff.stocks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Stok
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Stok Listesi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kitap</th>
                            <th>ISBN</th>
                            <th>Barkod</th>
                            <th>Miktar</th>
                            <th>Ödünç Durumu</th>
                            <th>Konum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $stock)
                        <tr>
                            <td>{{ $stock->id }}</td>
                            <td>{{ $stock->book->title ?? '-' }}</td>
                            <td>{{ $stock->book->isbn ?? '-' }}</td>
                            <td>{{ $stock->barcode }}</td>
                            <td>1</td>
                            <td>
                                <span class="badge badge-{{ $stock->status == 'available' ? 'success' : 'warning' }}">
                                    {{ $stock->status == 'available' ? 'Stokta' : 'Ödünç Verildi' }}
                                </span>
                            </td>
                            <td>{{ $stock->shelf->name ?? 'Belirtilmemiş' }}</td>
                            <td>
                                <a href="{{ route('staff.stocks.edit', $stock->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('staff.stocks.destroy', $stock->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bu stok kaydını silmek istediğinizden emin misiniz?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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

@section('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json'
        }
    });
});
</script>
@endsection 