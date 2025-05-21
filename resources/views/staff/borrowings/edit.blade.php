@extends('layouts.staff')

@section('title', 'Ödünç Verme Düzenle')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ödünç Verme Düzenle</h1>
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

            <form action="{{ route('staff.borrowings.update', $borrowing->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Kullanıcı Bilgileri -->
                <div class="mb-4">
                    <h5>Kullanıcı Bilgileri</h5>
                    <div class="border rounded p-3">
                        <p class="mb-1"><strong>Ad Soyad:</strong> {{ $borrowing->user->name }}</p>
                        <p class="mb-0"><strong>E-posta:</strong> {{ $borrowing->user->email }}</p>
                    </div>
                </div>

                <!-- Kitap Bilgileri -->
                <div class="mb-4">
                    <h5>Kitap Bilgileri</h5>
                    <div class="border rounded p-3">
                        <p class="mb-1"><strong>Kitap:</strong> {{ $borrowing->book->title }}</p>
                        <p class="mb-1"><strong>ISBN:</strong> {{ $borrowing->book->isbn }}</p>
                        <p class="mb-1"><strong>Yazar:</strong> {{ $borrowing->book->authors->pluck('name')->join(', ') }}</p>
                        <p class="mb-0"><strong>Barkod:</strong> {{ $borrowing->stock->barcode }}</p>
                    </div>
                </div>

                <!-- Ödünç Alma Tarihi -->
                <div class="mb-4">
                    <label for="borrow_date" class="form-label">Ödünç Alma Tarihi</label>
                    <input type="date" class="form-control" id="borrow_date" name="borrow_date" 
                           value="{{ old('borrow_date', $borrowing->borrow_date->format('Y-m-d')) }}" required>
                </div>

                <!-- Son Teslim Tarihi -->
                <div class="mb-4">
                    <label for="due_date" class="form-label">Son Teslim Tarihi</label>
                    <input type="date" class="form-control" id="due_date" name="due_date" 
                           value="{{ old('due_date', $borrowing->due_date->format('Y-m-d')) }}" required>
                </div>

                <!-- Teslim Tarihi -->
                <div class="mb-4">
                    <label for="returned_at" class="form-label">Teslim Tarihi</label>
                    <input type="date" class="form-control" id="returned_at" name="returned_at" 
                           value="{{ old('returned_at', $borrowing->returned_at ? $borrowing->returned_at->format('Y-m-d') : '') }}">
                </div>

                <!-- Durum -->
                <div class="mb-4">
                    <label for="status" class="form-label">Durum</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="pending" {{ $borrowing->status == 'pending' ? 'selected' : '' }}>Beklemede</option>
                        <option value="approved" {{ $borrowing->status == 'approved' ? 'selected' : '' }}>Onaylandı</option>
                        <option value="returned" {{ $borrowing->status == 'returned' ? 'selected' : '' }}>Teslim Edildi</option>
                        <option value="cancelled" {{ $borrowing->status == 'cancelled' ? 'selected' : '' }}>İptal Edildi</option>
                    </select>
                </div>

                <!-- Kitap Durumu -->
                <div id="bookCondition" class="mb-4 d-none">
                    <h5>Kitap Durumu</h5>
                    <div class="border rounded p-3">
                        <div class="form-group">
                            <label for="damage_level">Hasar Durumu</label>
                            <select class="form-control" id="damage_level" name="damage_level">
                                <option value="">Hasarsız</option>
                                <option value="minor">Hafif Hasar</option>
                                <option value="moderate">Orta Hasar</option>
                                <option value="severe">Ağır Hasar</option>
                            </select>
                        </div>

                        <div id="damageDetails" class="d-none">
                            <div class="form-group mt-3">
                                <label for="damage_description">Hasar Açıklaması</label>
                                <textarea class="form-control" id="damage_description" name="damage_description" rows="3"
                                    placeholder="Hasarın detaylı açıklamasını giriniz..."></textarea>
                            </div>

                            <div class="form-group mt-3">
                                <label>Hasar Fotoğrafları</label>
                                <input type="file" class="form-control" name="damage_photos[]" multiple 
                                       accept="image/*" id="damage_photos">
                                <small class="form-text text-muted">
                                    Birden fazla fotoğraf seçebilirsiniz. Her fotoğraf en fazla 2MB olmalıdır.
                                </small>
                            </div>

                            <div class="alert alert-info mt-3">
                                <h6 class="alert-heading">Hasar Ceza Tutarları:</h6>
                                <ul class="mb-0">
                                    <li>Hafif Hasar: Kitap değerinin %25'i</li>
                                    <li>Orta Hasar: Kitap değerinin %50'si</li>
                                    <li>Ağır Hasar: Kitap değerinin %100'ü</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gecikme Cezası Bilgileri -->
                <div id="fineDetails" class="mb-4 d-none">
                    <h5>Gecikme Cezası</h5>
                    <div class="border rounded p-3">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Gecikme Günü:</strong> <span id="lateDays">0</span> gün</p>
                                <p class="mb-1"><strong>Günlük Ceza:</strong> 1.00 TL</p>
                                <p class="mb-0"><strong>Toplam Ceza:</strong> <span id="totalFine">0.00</span> TL</p>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method">Ödeme Yöntemi</label>
                                    <select class="form-control" id="payment_method" name="payment_method">
                                        <option value="">Seçiniz</option>
                                        <option value="cash">Nakit</option>
                                        <option value="bank_transfer">Banka Havalesi</option>
                                    </select>
                                </div>
                                <div class="form-group mt-2">
                                    <label for="payment_reference">Ödeme Referansı</label>
                                    <input type="text" class="form-control" id="payment_reference" name="payment_reference" 
                                           placeholder="Dekont no veya nakit makbuz no">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notlar -->
                <div class="mb-4">
                    <label for="notes" class="form-label">Notlar</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $borrowing->notes) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kaydet
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    function calculateFine() {
        const returnedAt = new Date($('#returned_at').val());
        const dueDate = new Date($('#due_date').val());
        const status = $('#status').val();
        
        // Eğer teslim edildi durumunda ve geç teslim edildiyse
        if (status === 'returned' && returnedAt > dueDate) {
            const diffTime = Math.abs(returnedAt - dueDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const finePerDay = 1; // Günlük 1 TL ceza
            const totalFine = diffDays * finePerDay;
            
            $('#lateDays').text(diffDays);
            $('#totalFine').text(totalFine.toFixed(2));
            $('#fineDetails').removeClass('d-none');
            
            // Ödeme alanlarını zorunlu yap
            $('#payment_method').prop('required', true);
            $('#payment_reference').prop('required', true);
        } else {
            $('#fineDetails').addClass('d-none');
            $('#payment_method').prop('required', false);
            $('#payment_reference').prop('required', false);
        }
    }

    // Durum değiştiğinde kontrolleri yap
    $('#status').change(function() {
        if ($(this).val() === 'returned') {
            if (!$('#returned_at').val()) {
                $('#returned_at').val('{{ date('Y-m-d') }}');
            }
            $('#returned_at').prop('required', true);
            $('#bookCondition').removeClass('d-none');
            calculateFine();
        } else {
            $('#returned_at').prop('required', false);
            $('#bookCondition').addClass('d-none');
            $('#fineDetails').addClass('d-none');
        }
    });

    // Hasar durumu değiştiğinde
    $('#damage_level').change(function() {
        if ($(this).val()) {
            $('#damageDetails').removeClass('d-none');
            $('#damage_description').prop('required', true);
            $('#damage_photos').prop('required', true);
        } else {
            $('#damageDetails').addClass('d-none');
            $('#damage_description').prop('required', false);
            $('#damage_photos').prop('required', false);
        }
    });

    // Teslim tarihi değiştiğinde ceza hesapla
    $('#returned_at').change(function() {
        if ($('#status').val() === 'returned') {
            calculateFine();
        }
    });

    // Sayfa yüklendiğinde mevcut durumu kontrol et
    if ($('#status').val() === 'returned') {
        $('#bookCondition').removeClass('d-none');
        if ($('#damage_level').val()) {
            $('#damageDetails').removeClass('d-none');
        }
        calculateFine();
    }
});
</script>
@endsection 