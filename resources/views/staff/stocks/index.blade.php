@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Stok Yönetimi</h1>
        <a href="{{ route('staff.stocks.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Yeni Stok
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Stok Listesi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kitap</th>
                            <th>ISBN</th>
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
                            <td>{{ $stock->book->title }}</td>
                            <td>{{ $stock->isbn }}</td>
                            <td>{{ $stock->quantity }}</td>
                            <td>
                                @if($stock->status == 'available')
                                    <span class="badge bg-success">Stokta</span>
                                @elseif($stock->status == 'borrowed')
                                    <span class="badge bg-danger">Ödünç Verildi</span>
                                @elseif($stock->status == 'reserved')
                                    <span class="badge bg-warning">Rezerve Edildi</span>
                                @elseif($stock->status == 'lost')
                                    <span class="badge bg-dark">Kayıp</span>
                                @elseif($stock->status == 'damaged')
                                    <span class="badge bg-danger">Hasarlı</span>
                                @else
                                    <span class="badge bg-secondary">{{ $stock->status }}</span>
                                @endif
                            </td>
                            <td>{{ $stock->location }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('staff.stocks.edit', $stock) }}" class="btn btn-sm btn-info" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('staff.stocks.destroy', $stock) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu stok kaydını silmek istediğinizden emin misiniz?')" title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                @if($stocks instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $stocks->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 