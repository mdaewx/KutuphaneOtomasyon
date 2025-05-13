@extends('layouts.admin')

@section('title', 'Edinme Türleri')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Edinme Türleri</h1>
                <a href="{{ route('admin.acquisition-types.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni Edinme Türü
                </a>
            </div>

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ad</th>
                                    <th>Açıklama</th>
                                    <th>Kaynak Sayısı</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $type)
                                <tr>
                                    <td>{{ $type->id }}</td>
                                    <td>{{ $type->name }}</td>
                                    <td>{{ $type->description }}</td>
                                    <td>{{ $type->acquisitionSources()->count() }}</td>
                                    <td>
                                        <a href="{{ route('admin.acquisition-types.edit', $type) }}" 
                                           class="btn btn-sm btn-primary" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.acquisition-types.destroy', $type) }}" 
                                              method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Bu edinme türünü silmek istediğinizden emin misiniz?')"
                                                    title="Sil">
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
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json'
        }
    });
});
</script>
@endpush 