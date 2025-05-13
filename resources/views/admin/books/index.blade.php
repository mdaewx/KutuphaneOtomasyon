@extends('layouts.admin')

@section('title', 'Kitaplar')

@section('content')
<style>
/* Badge style for publishers - improve contrast */
.badge-info {
    background-color: #17a2b8 !important; /* Darker blue */
    color: #ffffff !important; /* White text */
    font-weight: bold !important;
}
/* Add custom style for dark badges */
.badge-dark {
    background-color: #212529 !important;
    color: #ffffff !important;
    font-weight: bold !important;
}
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kitaplar</h1>
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Yeni Kitap Ekle
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
                            <th>Kapak</th>
                            <th>Başlık</th>
                            <th>Yazarlar</th>
                            <th>Yayınevi</th>
                            <th>Kategori</th>
                            <th>ISBN</th>
                            <th>Açıklama</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($books as $book)
                            <tr>
                                <td>{{ $book->id }}</td>
                                <td>
                                    @php
                                        $coverExists = $book->cover_image && file_exists(public_path('storage/covers/' . $book->cover_image));
                                    @endphp
                                    
                                    @if($book->cover_image && $coverExists)
                                        <img src="{{ asset('storage/covers/' . $book->cover_image) }}" 
                                            alt="{{ $book->title }}" class="img-thumbnail" style="height: 50px; width: auto;">
                                    @else
                                        <img src="{{ asset('images/no-cover.png') }}" 
                                            alt="Kapak Yok" class="img-thumbnail" style="height: 50px; width: auto;">
                                    @endif
                                </td>
                                <td>{{ $book->title }}</td>
                                <td>
                                    @foreach($book->authors as $author)
                                        {{ $author->name }} {{ $author->surname }}@if(!$loop->last), @endif
                                    @endforeach
                                </td>
                                <td>
                                    @if($book->publisher_id)
                                        <span class="badge badge-info">
                                            {{ $book->publisher_name }}
                                        </span>
                                    @else
                                        <span class="badge badge-dark">
                                            Yayınevi Belirtilmemiş
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $book->category ? $book->category->name : '-' }}</td>
                                <td>{{ $book->isbn }}</td>
                                <td>{{ $book->description ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.books.show', $book) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu kitabı silmek istediğinizden emin misiniz?')">
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

@section('styles')
<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" rel="stylesheet">
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Turkish.json'
        }
    });
});
</script>
@endsection 