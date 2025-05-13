@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Sol Taraf - Profil Kartı -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ $user->profile_photo ? asset('storage/profiles/' . $user->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" 
                             alt="Profil Fotoğrafı"
                             class="rounded-circle"
                             style="width: 120px; height: 120px; object-fit: cover;">
                        <button class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle"
                                style="width: 32px; height: 32px; padding: 0;"
                                data-bs-toggle="modal" 
                                data-bs-target="#editProfileModal">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary"
                                data-bs-toggle="modal" 
                                data-bs-target="#editProfileModal">
                            <i class="fas fa-edit me-2"></i>Profili Düzenle
                        </button>
                    </div>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        İstatistikler
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fas fa-book text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Toplam Ödünç Alma</h6>
                            <p class="mb-0 text-muted">{{ $user->borrowings()->count() }} kitap</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Aktif Ödünç</h6>
                            <p class="mb-0 text-muted">{{ $activeBorrowings->count() }} kitap</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                                <i class="fas fa-money-bill text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Toplam Ceza</h6>
                            <p class="mb-0 text-muted">{{ number_format($totalFines, 2) }} ₺</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sağ Taraf -->
        <div class="col-md-8">
            <!-- Aktif Ödünç Alınanlar -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-book-reader me-2 text-primary"></i>
                        Aktif Ödünç Aldığım Kitaplar
                    </h5>
                </div>
                <div class="card-body p-0">
                    @forelse($activeBorrowings as $borrowing)
                        <div class="d-flex align-items-center p-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="rounded bg-primary bg-opacity-10 p-3">
                                    <i class="fas fa-book fa-lg text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $borrowing->book->title }}</h6>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Alınma: {{ $borrowing->borrow_date->format('d.m.Y') }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-calendar-check me-1"></i>
                                    İade: {{ $borrowing->due_date->format('d.m.Y') }}
                                </p>
                            </div>
                            <div class="ms-auto d-flex align-items-center">
                                @if($borrowing->isOverdue())
                                    <span class="badge bg-danger me-2">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Gecikmiş
                                    </span>
                                @else
                                    <span class="badge bg-success me-2">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Aktif
                                    </span>
                                @endif
                                
                                <form action="{{ route('profile.return-book', $borrowing) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-undo-alt"></i>
                                        İade Et
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2702/2702162.png" 
                                 alt="Boş Liste" 
                                 style="width: 120px; height: 120px; opacity: 0.5;">
                            <p class="text-muted mt-3">Aktif ödünç aldığınız kitap bulunmamaktadır.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Geçmiş İşlemler -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>
                        Geçmiş İşlemler
                    </h5>
                </div>
                <div class="card-body p-0">
                    @forelse($borrowingHistory as $borrowing)
                        <div class="d-flex align-items-center p-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="rounded bg-secondary bg-opacity-10 p-3">
                                    <i class="fas fa-history fa-lg text-secondary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $borrowing->book->title }}</h6>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-check-circle me-1"></i>
                                    İade Edildi: {{ $borrowing->returned_at->format('d.m.Y') }}
                                    @if($borrowing->fine_amount > 0)
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-money-bill me-1"></i>
                                        Ceza: {{ number_format($borrowing->fine_amount, 2) }} ₺
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/1380/1380641.png" 
                                 alt="Boş Geçmiş" 
                                 style="width: 120px; height: 120px; opacity: 0.5;">
                            <p class="text-muted mt-3">Henüz iade edilmiş kitabınız bulunmamaktadır.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profil Düzenleme Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit me-2"></i>
                    Profili Düzenle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user me-1"></i>
                            Ad Soyad
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-envelope me-1"></i>
                            E-posta
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-camera me-1"></i>
                            Profil Fotoğrafı
                        </label>
                        <input type="file" 
                               class="form-control @error('profile_photo') is-invalid @enderror" 
                               name="profile_photo" 
                               accept="image/jpeg,image/png,image/jpg,image/gif">
                        <div class="form-text">PNG, JPG veya JPEG (Max. 2MB)</div>
                        @error('profile_photo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        
                        @if($user->profile_photo)
                            <div class="mt-2">
                                <p class="mb-1">Mevcut fotoğraf:</p>
                                <img src="{{ asset('storage/profiles/' . $user->profile_photo) }}" 
                                     alt="Mevcut profil fotoğrafı" 
                                     class="img-thumbnail" 
                                     style="max-height: 100px;">
                            </div>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lock me-1"></i>
                            Mevcut Şifre
                        </label>
                        <input type="password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               name="current_password">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-key me-1"></i>
                            Yeni Şifre
                        </label>
                        <input type="password" 
                               class="form-control @error('new_password') is-invalid @enderror" 
                               name="new_password">
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-check-double me-1"></i>
                            Yeni Şifre Tekrar
                        </label>
                        <input type="password" 
                               class="form-control @error('new_password_confirmation') is-invalid @enderror" 
                               name="new_password_confirmation">
                        @error('new_password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        İptal
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                        <i class="fas fa-save me-1"></i>
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form gönderildiğinde butonu devre dışı bırak
        const form = document.querySelector('form[action="{{ route('profile.update') }}"]');
        const saveBtn = document.getElementById('saveProfileBtn');
        
        if (form && saveBtn) {
            form.addEventListener('submit', function() {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Kaydediliyor...';
            });
        }
        
        // Hata varsa modalı otomatik olarak aç
        @if($errors->any())
            const editModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
            editModal.show();
        @endif
    });
</script>
@endpush
@endsection 