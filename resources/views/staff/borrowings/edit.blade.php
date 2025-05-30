@extends('layouts.staff')

@section('title', 'Ödünç Alma Kaydını Düzenle')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ödünç Alma Kaydını Düzenle</h1>
        <a href="{{ route('staff.borrowings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Geri Dön
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ödünç Bilgileri</h6>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('staff.borrowings.update', $borrowing->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kullanıcı</label>
                            <input type="text" class="form-control" value="{{ $borrowing->user->name }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kitap</label>
                            <input type="text" class="form-control" value="{{ $borrowing->book->title }}" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="due_date">İade Tarihi</label>
                            <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                                value="{{ old('due_date', $borrowing->due_date->format('Y-m-d')) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="condition">Kitap Durumu</label>
                            <select name="condition" id="condition" class="form-control @error('condition') is-invalid @enderror" required>
                                <option value="">Durum Seçin</option>
                                <option value="good" {{ old('condition', $borrowing->condition) === 'good' ? 'selected' : '' }}>İyi Durumda</option>
                                <option value="damaged" {{ old('condition', $borrowing->condition) === 'damaged' ? 'selected' : '' }}>Hasarlı</option>
                                <option value="lost" {{ old('condition', $borrowing->condition) === 'lost' ? 'selected' : '' }}>Kayıp</option>
                            </select>
                            @error('condition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div id="damageDescriptionGroup" class="form-group" style="display: none;">
                    <label for="damage_description">Hasar Açıklaması</label>
                    <textarea name="damage_description" id="damage_description" rows="3" 
                        class="form-control @error('damage_description') is-invalid @enderror">{{ old('damage_description', $borrowing->damage_description) }}</textarea>
                    <small class="form-text text-muted">Hasarın türünü ve kapsamını detaylı olarak açıklayın.</small>
                    @error('damage_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Notlar</label>
                    <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $borrowing->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                    <a href="{{ route('staff.borrowings.show', $borrowing) }}" class="btn btn-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Kitap durumu değiştiğinde hasar açıklaması alanını göster/gizle
    $('#condition').change(function() {
        if ($(this).val() === 'damaged') {
            $('#damageDescriptionGroup').show();
            $('#damage_description').prop('required', true);
        } else {
            $('#damageDescriptionGroup').hide();
            $('#damage_description').prop('required', false);
        }
    });

    // Sayfa yüklendiğinde mevcut durumu kontrol et
    if ($('#condition').val() === 'damaged') {
        $('#damageDescriptionGroup').show();
        $('#damage_description').prop('required', true);
    }
});
</script>
@endsection 