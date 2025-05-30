@extends('layouts.staff')

@section('title', 'Kitap İadesi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kitap İadesi</h1>
        <a href="{{ route('staff.borrowings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Geri Dön
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">İade Bilgileri</h6>
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

            <!-- Kitap ve Kullanıcı Bilgileri -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Kitap Bilgileri</h5>
                            <p class="mb-1"><strong>Başlık:</strong> {{ $borrowing->book->title }}</p>
                            <p class="mb-1"><strong>ISBN:</strong> {{ $borrowing->book->isbn }}</p>
                            <p class="mb-1"><strong>Yazar:</strong> {{ $borrowing->book->authors->pluck('name')->join(', ') }}</p>
                            <p class="mb-0"><strong>Yayınevi:</strong> {{ $borrowing->book->publisher->name ?? 'Belirtilmemiş' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Ödünç Bilgileri</h5>
                            <p class="mb-1"><strong>Kullanıcı:</strong> {{ $borrowing->user->name }}</p>
                            <p class="mb-1"><strong>Ödünç Alma:</strong> {{ $borrowing->borrow_date->format('d.m.Y') }}</p>
                            <p class="mb-1"><strong>Son Teslim:</strong> {{ $borrowing->due_date->format('d.m.Y') }}</p>
                            <p class="mb-0"><strong>Durum:</strong> 
                                @if($borrowing->isOverdue())
                                    <span class="text-danger">{{ $borrowing->getDaysOverdueAttribute() }} Gün Gecikmiş</span>
                                @else
                                    <span class="text-success">Zamanında</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('staff.borrowings.return', $borrowing) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Kitap Durumu -->
                <div class="mb-4">
                    <label for="condition" class="form-label">Kitap Durumu <span class="text-danger">*</span></label>
                    <select class="form-control" id="condition" name="condition" required>
                        <option value="">Durum Seçin</option>
                        <option value="good">İyi Durumda</option>
                        <option value="damaged">Hasarlı</option>
                        <option value="lost">Kayıp</option>
                    </select>
                    <small class="form-text text-muted">
                        Hasarlı veya kayıp durumunda otomatik ceza uygulanacaktır.
                    </small>
                </div>

                <!-- Hasar Açıklaması -->
                <div class="mb-4" id="damageDescriptionGroup" style="display: none;">
                    <label for="damage_description" class="form-label">Hasar Açıklaması <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="damage_description" name="damage_description" rows="3" 
                              placeholder="Hasarın detaylı açıklamasını girin..."></textarea>
                    <small class="form-text text-muted">
                        Hasarın türünü ve kapsamını detaylı olarak açıklayın.
                    </small>
                </div>

                <!-- Notlar -->
                <div class="mb-4">
                    <label for="notes" class="form-label">Notlar</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                              placeholder="İade ile ilgili eklemek istediğiniz notlar..."></textarea>
                </div>

                <!-- Ceza Bilgilendirmesi -->
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Ceza Bilgilendirmesi</h6>
                    <ul class="mb-0">
                        <li>Hasarlı iade: Kitap değerinin %50'si kadar ceza uygulanır</li>
                        <li>Kayıp: Kitap değerinin 2 katı ceza uygulanır</li>
                        <li>Geç iade: Gün başına 1 TL gecikme bedeli uygulanır</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> İade Al
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('condition').addEventListener('change', function() {
    const damageDescriptionGroup = document.getElementById('damageDescriptionGroup');
    const damageDescription = document.getElementById('damage_description');
    
    if (this.value === 'damaged') {
        damageDescriptionGroup.style.display = 'block';
        damageDescription.setAttribute('required', 'required');
    } else {
        damageDescriptionGroup.style.display = 'none';
        damageDescription.removeAttribute('required');
        damageDescription.value = '';
    }
});
</script>
@endpush 