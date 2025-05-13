@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stok Düzenle</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.stocks.update', $stock) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <!-- Hidden fields to prevent validation errors -->
                        <input type="hidden" name="source_type_id" value="1">
                        <input type="hidden" name="source_name" value="Default Source">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="book_id">Kitap</label>
                                    <select name="book_id" id="book_id" class="form-control @error('book_id') is-invalid @enderror" required>
                                        <option value="">Kitap Seçin</option>
                                        @foreach($books as $book)
                                            <option value="{{ $book->id }}" {{ old('book_id', $stock->book_id) == $book->id ? 'selected' : '' }}>
                                                {{ $book->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('book_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="isbn">ISBN</label>
                                    <input type="text" name="isbn" id="isbn" class="form-control @error('isbn') is-invalid @enderror" value="{{ old('isbn', $stock->isbn) }}" required>
                                    @error('isbn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Miktar</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $stock->quantity) }}" min="1" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="condition">Durum</label>
                                    <select name="condition" id="condition" class="form-control @error('condition') is-invalid @enderror" required>
                                        <option value="new" {{ old('condition', $stock->condition) == 'new' ? 'selected' : '' }}>Yeni</option>
                                        <option value="good" {{ old('condition', $stock->condition) == 'good' ? 'selected' : '' }}>İyi</option>
                                        <option value="fair" {{ old('condition', $stock->condition) == 'fair' ? 'selected' : '' }}>Orta</option>
                                        <option value="poor" {{ old('condition', $stock->condition) == 'poor' ? 'selected' : '' }}>Kötü</option>
                                    </select>
                                    @error('condition')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="location">Konum</label>
                            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $stock->location) }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notlar</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $stock->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Güncelle</button>
                            <a href="{{ route('staff.stocks.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 