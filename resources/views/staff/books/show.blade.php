@extends('layouts.staff')

@section('title', 'Kitap Detayları')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kitap Detayları</h1>
        <a href="{{ route('staff.books.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 me-1"></i> Kitap Listesine Dön
        </a>
    </div>

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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kitap Bilgileri</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-4">
                        <div class="card">
                            <div class="card-body">
                                <img src="{{ asset('images/icons/book-logo.png') }}" 
                                     alt="{{ $book->title }}" class="img-fluid" style="width: 200px; height: auto;">
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('staff.books.edit', $book) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
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
                                <td>{{ $book->publisher ? $book->publisher->name : 'Belirtilmemiş' }}</td>
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
                                    <span class="badge {{ $book->isAvailable() ? 'bg-success' : 'bg-danger' }}">
                                        {{ $book->getAvailableQuantityAttribute() }} / {{ $book->getTotalQuantityAttribute() }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h5 class="mt-4">Açıklama</h5>
                    <p>{{ $book->description ?? 'Açıklama bulunmuyor.' }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($book->activeBorrowings && $book->activeBorrowings->count() > 0)
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
                    </tr>
                </thead>
                <tbody>
                    @foreach($book->activeBorrowings as $borrowing)
                    <tr>
                        <td>{{ $borrowing->user->name }}</td>
                        <td>{{ $borrowing->borrow_date->format('d.m.Y') }}</td>
                        <td>{{ $borrowing->due_date->format('d.m.Y') }}</td>
                        <td>
                            @if($borrowing->due_date < now())
                                <span class="badge bg-danger">Gecikmiş</span>
                            @else
                                <span class="badge bg-success">Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection 