@extends('layouts.admin')

@section('page-title', 'Kullanıcı Yönetimi')

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary d-inline">Kullanıcı Listesi</h6>
        <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-1"></i>
            Yeni Kullanıcı Ekle
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Profil</th>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Rol</th>
                        <th>Kayıt Tarihi</th>
                        <th>Aktif Ödünç</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <img src="{{ $user->profile_photo ? asset('storage/profiles/'.$user->profile_photo) : 'https://via.placeholder.com/40x40' }}" 
                                 alt="{{ $user->name }}"
                                 class="rounded-circle"
                                 style="width: 40px; height: 40px; object-fit: cover;">
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge bg-danger">Yönetici</span>
                            @elseif($user->is_staff)
                                <span class="badge bg-primary">Personel</span>
                            @else
                                <span class="badge bg-success">Kullanıcı</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d.m.Y') }}</td>
                        <td>{{ $user->activeBorrowings()->count() }}</td>
                        <td>
                            <div class="d-flex">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info me-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-primary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal{{ $user->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteUserModal{{ $user->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Düzenleme Modal -->
                    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-edit me-2"></i>
                                        Kullanıcı Düzenle
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Ad Soyad</label>
                                            <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">E-posta</label>
                                            <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Rol</label>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="role" value="admin" id="admin_role_{{ $user->id }}" {{ $user->is_admin ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="admin_role_{{ $user->id }}">
                                                            <span class="badge bg-danger">Yönetici</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="role" value="staff" id="staff_role_{{ $user->id }}" {{ $user->is_staff && !$user->is_admin ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="staff_role_{{ $user->id }}">
                                                            <span class="badge bg-primary">Personel</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="role" value="user" id="user_role_{{ $user->id }}" {{ !$user->is_admin && !$user->is_staff ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="user_role_{{ $user->id }}">
                                                            <span class="badge bg-success">Kullanıcı</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Yeni Şifre</label>
                                            <input type="password" class="form-control" name="password">
                                            <small class="text-muted">Şifreyi değiştirmek istemiyorsanız boş bırakın.</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Şifre Tekrar</label>
                                            <input type="password" class="form-control" name="password_confirmation">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Profil Fotoğrafı</label>
                                            <input type="file" class="form-control" name="profile_photo">
                                            @if($user->profile_photo)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/profiles/'.$user->profile_photo) }}" 
                                                         alt="Mevcut Profil"
                                                         class="rounded-circle"
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                </div>
                                            @endif
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

                    <!-- Silme Modal -->
                    <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteUserModalLabel{{ $user->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteUserModalLabel{{ $user->id }}">
                                        <i class="fas fa-trash me-2"></i>
                                        Kullanıcı Sil
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>"{{ $user->name }}" kullanıcısını silmek istediğinizden emin misiniz?</p>
                                    @if($user->activeBorrowings()->count() > 0)
                                        <p class="text-danger">Bu kullanıcının {{ $user->activeBorrowings()->count() }} adet aktif ödünç kitabı bulunmaktadır!</p>
                                    @endif
                                    <p class="text-danger mb-0">Bu işlem geri alınamaz!</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Sil</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Yeni Kullanıcı Ekleme Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Yeni Kullanıcı Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="role" value="admin" id="new_admin_role">
                                    <label class="form-check-label" for="new_admin_role">
                                        <span class="badge bg-danger">Yönetici</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="role" value="staff" id="new_staff_role">
                                    <label class="form-check-label" for="new_staff_role">
                                        <span class="badge bg-primary">Personel</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="role" value="user" id="new_user_role" checked>
                                    <label class="form-check-label" for="new_user_role">
                                        <span class="badge bg-success">Kullanıcı</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şifre Tekrar</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profil Fotoğrafı</label>
                        <input type="file" class="form-control" name="profile_photo">
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