@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <span class="display-4">{{ substr(Auth::user()->name, 0, 2) }}</span>
                        </div>
                    </div>
                    <h4 class="card-title">{{ Auth::user()->name }}</h4>
                    <p class="text-muted">{{ Auth::user()->email }}</p>

                    @if(Auth::user()->hasRole('staff'))
                    <div class="mt-4">
                        <a href="{{ route('staff.dashboard') }}" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-tachometer-alt me-2"></i> Personel Paneline Git
                        </a>
                    </div>
                    @endif

                    <button class="btn btn-outline-primary mt-3" id="profileEditBtn">
                        <i class="fas fa-edit"></i> Profili Düzenle
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- İstatistikler -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    İstatistikler
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="h2 text-primary">{{ $totalBorrowings ?? 0 }}</div>
                            <div class="text-muted">Toplam Ödünç Alma</div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="h2 text-warning">{{ $activeBorrowings ?? 0 }}</div>
                            <div class="text-muted">Aktif Ödünç</div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="h2 text-danger">{{ $totalFines ?? '0.00' }} ₺</div>
                            <div class="text-muted">Toplam Ceza</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aktif Ödünç Alınan Kitaplar -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-book-reader me-1"></i>
                    Aktif Ödünç Aldığım Kitaplar
                </div>
                <div class="card-body">
                    @if(isset($activeBorrowedBooks) && $activeBorrowedBooks->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kitap</th>
                                        <th>Alınma Tarihi</th>
                                        <th>Son Teslim Tarihi</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeBorrowedBooks as $borrowing)
                                    <tr>
                                        <td>{{ $borrowing->book->title }}</td>
                                        <td>{{ $borrowing->borrow_date->format('d.m.Y') }}</td>
                                        <td>
                                            <span class="@if($borrowing->is_overdue) text-danger @endif">
                                                {{ $borrowing->due_date->format('d.m.Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('profile.return-book', $borrowing->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-undo-alt"></i> İade Et
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <img src="{{ asset('images/no-books.svg') }}" alt="Kitap Yok" style="width: 120px; opacity: 0.5;">
                            <p class="text-muted mt-3">Aktif ödünç aldığınız kitap bulunmamaktadır.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profil Düzenleme Modalı -->
<div class="modal fade" id="profileEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Profili Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mevcut Şifre</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Yeni Şifre</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Yeni Şifre (Tekrar)</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('profileEditBtn').addEventListener('click', function() {
    var modal = new bootstrap.Modal(document.getElementById('profileEditModal'));
    modal.show();
});
</script>
@endpush
