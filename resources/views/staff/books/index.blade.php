@extends('layouts.staff')

@section('title', 'Kitap Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kitap Yönetimi</h1>
        <a href="{{ route('staff.books.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Yeni Kitap
        </a>
    </div>

    <!-- İstatistik Kartları -->
    <div class="row mb-4">
        <!-- Toplam Kitap Sayısı -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Toplam Kitap</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBooks }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mevcut Kitap Sayısı -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Mevcut Kitap</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $availableBooks }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ödünç Verilen Kitap Sayısı -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Ödünç Verilen</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $borrowedBooks }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-heart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gecikmiş Kitap Sayısı -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Gecikmiş</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueBooks }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kitap Listesi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Başlık</th>
                            <th>Yazar</th>
                            <th>ISBN</th>
                            <th>Kategori</th>
                            <th>Yayınevi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($books as $book)
                        <tr>
                            <td>{{ $book->id }}</td>
                            <td>{{ $book->title }}</td>
                            <td>
                                @if($book->authors->count() > 0)
                                    {{ $book->authors->pluck('name')->join(', ') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $book->isbn }}</td>
                            <td>{{ $book->category ? $book->category->name : '-' }}</td>
                            <td>{{ $book->publisher_name }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('staff.books.show', $book->id) }}" class="btn btn-info btn-sm" title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('staff.books.edit', $book->id) }}" class="btn btn-primary btn-sm" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                @if($books instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $books->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('.datatable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json"
        }
    });
});
</script>
@endpush 