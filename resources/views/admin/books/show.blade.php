@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Kitap Detayları</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.books.index') }}">Kitaplar</a></li>
        <li class="breadcrumb-item active">{{ $book->title }}</li>
    </ol>

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

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-book me-1"></i>
            Kitap Bilgileri
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <div class="card mb-4">
                        <div class="card-body">
                            <img src="{{ asset('images/icons/book-logo.png') }}" alt="Kitap Logo" class="img-fluid" style="width: 200px; height: auto;">
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash"></i> Sil
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <h3>{{ $book->title }}</h3>
                    <p class="text-muted">
                        @foreach($book->authors as $author)
                            {{ $author->name }} {{ $author->surname }}@if(!$loop->last), @endif
                        @endforeach
                    </p>
                    
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 200px;">ISBN</th>
                                <td>{{ $book->isbn }}</td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>{{ $book->category->name ?? 'Belirtilmemiş' }}</td>
                            </tr>
                            <tr>
                                <th>Yayınevi</th>
                                <td>{{ $book->publisher_name }}</td>
                            </tr>
                            <tr>
                                <th>Yayın Yılı</th>
                                <td>{{ $book->publication_year }}</td>
                            </tr>
                            <tr>
                                <th>Dil</th>
                                <td>{{ $book->language ?? 'Belirtilmemiş' }}</td>
                            </tr>
                            <tr>
                                <th>Sayfa Sayısı</th>
                                <td>{{ $book->page_count }}</td>
                            </tr>
                            <tr>
                                <th>Stok Durumu</th>
                                <td>
                                    <span class="badge bg-{{ $book->available_quantity > 0 ? 'success' : 'danger' }}">
                                        {{ $book->available_quantity }} / {{ $book->quantity }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Durum</th>
                                <td>
                                    @if($book->isAvailable())
                                        <span class="badge bg-success">Müsait</span>
                                    @else
                                        <span class="badge bg-danger">Ödünç Verildi</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h5 class="mt-4">Açıklama</h5>
                    <p>{{ $book->description }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($book->activeBorrowings->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i>
            Kitabı Ödünç Alanlar
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Kullanıcı</th>
                        <th>Ödünç Alma Tarihi</th>
                        <th>İade Tarihi</th>
                        <th>Durumu</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($book->activeBorrowings as $borrowing)
                    <tr>
                        <td>{{ $borrowing->user->name }}</td>
                        <td>{{ $borrowing->borrow_date->format('d.m.Y') }}</td>
                        <td>{{ $borrowing->due_date->format('d.m.Y') }}</td>
                        <td>
                            @if($borrowing->status == 'pending')
                                <span class="badge bg-warning text-dark">Beklemede</span>
                            @elseif($borrowing->status == 'approved')
                                <span class="badge bg-success">Onaylandı</span>
                            @elseif($borrowing->status == 'rejected')
                                <span class="badge bg-danger">Reddedildi</span>
                            @elseif($borrowing->status == 'returned')
                                <span class="badge bg-info">İade Edildi</span>
                            @elseif($borrowing->status == 'overdue')
                                <span class="badge bg-danger">Gecikmiş</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.borrowings.return', $borrowing) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check-circle"></i> İade Al
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Kitap Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ $book->title }} adlı kitabı silmek istediğinize emin misiniz?</p>
                <p class="text-danger">Bu işlem geri alınamaz!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Evet, Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Modal tetiklendiğinde submit butonunun çalışmasını sağla
    document.addEventListener('DOMContentLoaded', function() {
        var deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('shown.bs.modal', function() {
                // Modal görünür olduğunda submit butonunu seç
                var deleteButton = deleteModal.querySelector('button[type="submit"]');
                if (deleteButton) {
                    // Butona odaklan
                    deleteButton.focus();
                }
            });
        }
    });
</script>
@endsection 