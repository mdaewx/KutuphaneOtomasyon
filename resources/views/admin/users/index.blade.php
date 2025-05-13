@extends('layouts.admin')

@section('title', 'Kullanıcılar')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kullanıcı Yönetimi</h1>
        <a href="{{ route('admin.users.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Yeni Kullanıcı Ekle
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kullanıcı Arama</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="search" class="sr-only">Arama</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="İsim, e-posta veya telefon" value="{{ request('search') }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="role" class="sr-only">Rol</label>
                    <select class="form-control" id="role" name="role">
                        <option value="">Tüm Roller</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Kullanıcı</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Yönetici</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">Ara</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mb-2 ml-2">Sıfırla</a>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kullanıcı Listesi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 70px;">Fotoğraf</th>
                            <th>İsim</th>
                            <th>E-posta</th>
                            <th>Telefon</th>
                            <th>Rol</th>
                            <th>Ödünç Alınan</th>
                            <th>Kayıt Tarihi</th>
                            <th style="width: 150px;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td class="text-center">
                                @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40">
                                @else
                                <img src="{{ asset('img/default-user.png') }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40">
                                @endif
                            </td>
                            <td>{{ $user->name }} {{ $user->surname }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?: '-' }}</td>
                            <td>
                                @if ($user->role == 'admin')
                                <span class="badge badge-primary">Yönetici</span>
                                @else
                                <span class="badge badge-secondary">Kullanıcı</span>
                                @endif
                            </td>
                            <td>{{ $user->borrowings_count ?? 0 }}</td>
                            <td>{{ $user->created_at ? $user->created_at->format('d.m.Y') : '-' }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm me-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $user->id }}">Kullanıcıyı Sil</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>{{ $user->name }} {{ $user->surname }} isimli kullanıcıyı silmek istediğinize emin misiniz?</p>
                                                <p class="text-danger">Bu işlem geri alınamaz ve kullanıcının tüm verileri silinecektir.</p>
                                                
                                                @if($user->borrowings_count > 0)
                                                <div class="alert alert-warning">
                                                    Bu kullanıcının {{ $user->borrowings_count }} adet ödünç alma işlemi var!
                                                </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Kullanıcıyı Sil</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Kayıtlı kullanıcı bulunamadı.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
            
            <!-- Users Count -->
            <div class="mt-3">
                <p class="text-muted">
                    Toplam {{ $users->total() }} kullanıcıdan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} arası gösteriliyor.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle visibility of delete confirmation modals
        $('.delete-user-btn').on('click', function() {
            var userId = $(this).data('user-id');
            $('#deleteModal' + userId).modal('show');
        });
    });
</script>
@endsection 