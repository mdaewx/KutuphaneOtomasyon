@extends('layouts.admin')

@section('title', 'Kullanıcı Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kullanıcı Yönetimi</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus fa-sm text-white-50"></i> Yeni Kullanıcı Ekle
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0 font-weight-bold text-primary">Kullanıcı Listesi</h6>
                </div>
                <div class="col-auto">
                    <form action="{{ route('admin.users.index') }}" method="GET" class="form-inline">
                        <div class="input-group mr-2">
                            <select name="role" class="form-control">
                                <option value="">Tüm Roller</option>
                                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Yönetici</option>
                                <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Memur</option>
                                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Kullanıcı</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Ara..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fotoğraf</th>
                            <th>İsim</th>
                            <th>E-posta</th>
                            <th>Telefon</th>
                            <th>Rol</th>
                            <th>Ödünç Alınan</th>
                            <th>Kayıt Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td class="text-center">
                                @if($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="rounded-circle" width="40">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge badge-danger px-3 py-2">Yönetici</span>
                                @elseif($user->role === 'staff')
                                    <span class="badge badge-success px-3 py-2">Memur</span>
                                @else
                                    <span class="badge badge-primary px-3 py-2">Kullanıcı</span>
                                @endif
                            </td>
                            <td>{{ $user->borrowings_count }}</td>
                            <td>{{ $user->created_at->format('d.m.Y') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm" title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Sil" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
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
            
            <div class="d-flex justify-content-end mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-danger {
        background-color: #e74a3b !important;
        color: white !important;
    }
    .badge-success {
        background-color: #1cc88a !important;
        color: white !important;
    }
    .badge-primary {
        background-color: #4e73df !important;
        color: white !important;
    }
    .table td {
        vertical-align: middle;
    }
</style>
@endpush 