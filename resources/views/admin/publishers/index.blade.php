@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Yayınevleri</h1>
        <a href="{{ route('admin.publishers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Yayınevi Ekle
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad</th>
                            <th>Adres</th>
                            <th>Telefon</th>
                            <th>E-posta</th>
                            <th width="120">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($publishers as $publisher)
                            <tr>
                                <td>{{ $publisher->id }}</td>
                                <td>{{ $publisher->name }}</td>
                                <td>{{ $publisher->address }}</td>
                                <td>{{ $publisher->phone }}</td>
                                <td>{{ $publisher->email ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.publishers.edit', $publisher) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.publishers.destroy', $publisher) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu yayınevini silmek istediğinizden emin misiniz?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $publishers->links() }}
        </div>
    </div>
</div>
@endsection 