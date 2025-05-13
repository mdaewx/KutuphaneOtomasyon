@extends('layouts.staff')

@section('title', 'Yeni Ödünç Verme')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .select2-container {
        z-index: 9999;
    }
    .select2-dropdown {
        z-index: 10000;
    }
    .select2-results {
        max-height: 300px;
        overflow-y: auto;
    }
    /* Normal select için stil */
    select.form-control {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        height: auto;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Yeni Ödünç Verme</h1>
        <a href="{{ route('staff.borrowings.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 me-1"></i> Geri Dön
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

            <form action="{{ route('staff.borrowings.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="user_id" class="form-label">Kullanıcı <span class="text-danger">*</span></label>
                        
                        @if(isset($users) && count($users) > 0)
                            <select class="form-control select2" id="user_id" name="user_id" required>
                                <option value="">Kullanıcı Seçin</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email ?? 'Email yok' }}) - ID: {{ $user->id }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif(isset($all_users) && count($all_users) > 0)
                            <div class="alert alert-warning mb-2">
                                Uygun kullanıcı bulunamadı, tüm kullanıcılar listeleniyor:
                            </div>
                            <select class="form-control select2" id="user_id" name="user_id" required>
                                <option value="">Kullanıcı Seçin</option>
                                @foreach($all_users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email ?? 'Email yok' }}) - ID: {{ $user->id }} 
                                        @if($user->is_admin) [Admin] @endif
                                        @if($user->is_staff) [Personel] @endif
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="alert alert-danger">
                                Hiç kullanıcı kaydı bulunamadı. Lütfen önce kullanıcı ekleyin.
                            </div>
                            <select class="form-control select2" id="user_id" name="user_id" required>
                                <option value="">Kullanıcı Seçin</option>
                            </select>
                        @endif
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="book_id" class="form-label">Kitap <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="book_id" name="book_id" required>
                            <option value="">Kitap Seçin</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" {{ $book->available_copies <= 0 ? 'disabled' : '' }} {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                    {{ $book->title }} | ISBN: {{ $book->isbn }} {{ $book->available_copies <= 0 ? '(Stokta Yok)' : '(Mevcut Stok: ' . $book->available_copies . ')' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-info">Sadece ödünç verilebilir durumda olan stoklar gösterilmektedir.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="borrow_date" class="form-label">Ödünç Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="borrow_date" name="borrow_date" value="{{ old('borrow_date', date('Y-m-d')) }}" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">Son İade Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+15 days'))) }}" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notlar</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="auto_approve" name="auto_approve" value="1" checked>
                    <label class="form-check-label" for="auto_approve">
                        Otomatik Onayla
                    </label>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Kaydet
                    </button>
                    <a href="{{ route('staff.borrowings.index') }}" class="btn btn-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Kullanıcı ve kitap select alanlarını Select2 ile geliştir
    $('#user_id, #book_id').select2({
        theme: 'bootstrap4',
        width: '100%',
        dropdownParent: $('body'),
        dropdownAutoWidth: true
    });
    
    // Son tarih kontrolü
    $('#borrow_date, #due_date').on('change', function() {
        var borrowDate = new Date($('#borrow_date').val());
        var dueDate = new Date($('#due_date').val());
        
        if (dueDate <= borrowDate) {
            alert('Son iade tarihi, ödünç alma tarihinden sonra olmalıdır.');
            // 15 gün sonrasını hesapla
            borrowDate.setDate(borrowDate.getDate() + 15);
            var newDueDate = borrowDate.toISOString().split('T')[0];
            $('#due_date').val(newDueDate);
        }
    });
});
</script>
@endpush 