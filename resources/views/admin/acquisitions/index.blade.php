@php
use App\Models\AcquisitionSourceType;
@endphp

@extends('layouts.admin')

@section('title', 'Edinme Kaynakları')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edinme Kaynakları</h1>
        <a href="{{ route('admin.acquisitions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Edinme Kaynağı
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tüm Edinme Kaynakları</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kaynak Adı</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sources as $source)
                        <tr>
                            <td>{{ $source->id }}</td>
                            <td>{{ $source->source_name }}</td>
                            <td>
                                <a href="{{ route('admin.acquisitions.edit', $source->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.acquisitions.destroy', $source->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu kaynağı silmek istediğinizden emin misiniz?')">
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json'
            }
        });
    });
</script>
@endsection 