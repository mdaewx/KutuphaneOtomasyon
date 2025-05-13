@extends('layouts.admin')

@section('title', 'Yeni Edinme Kaynağı')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Yeni Edinme Kaynağı</h1>
        <a href="{{ route('admin.acquisitions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Geri Dön
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edinme Kaynağı Bilgileri</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.acquisitions.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="source_name">Kaynak Adı <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('source_name') is-invalid @enderror" 
                           id="source_name" name="source_name" value="{{ old('source_name') }}" required>
                    @error('source_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                    <a href="{{ route('admin.acquisitions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 