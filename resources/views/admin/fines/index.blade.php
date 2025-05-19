@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Ceza İşlemleri</h1>
    <p class="mb-4">Gecikmiş kitaplar ve uygulanan cezaların yönetimi</p>

    <!-- Günlük Ceza Tutarı Güncelleme -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Günlük Ceza Tutarı Ayarı</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.fines.update-rate') }}" method="POST" class="form-inline">
                @csrf
                <div class="form-group mb-2">
                    <label for="daily_fine_rate" class="sr-only">Günlük Ceza Tutarı</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="daily_fine_rate" name="daily_fine_rate" value="{{ $dailyFineRate }}" required>
                        <div class="input-group-append">
                            <span class="input-group-text">TL</span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mb-2 ml-2">Güncelle</button>
            </form>
        </div>
    </div>

    <!-- Ödenmemiş Cezalar -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Ödenmemiş Cezalar</h6>
            <div>
                <div class="input-group mb-3" style="width: 300px;">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Ara...">
                    <div class="input-group-append">
                        <button id="searchBtn" class="btn btn-sm btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="togglePaidFines">
                    <i class="fas fa-filter"></i> Tümünü Göster
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" id="addNewFine" data-bs-toggle="modal" data-bs-target="#addFineModal">
                    <i class="fas fa-plus"></i> Yeni Ceza Ekle
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="finesTable">
                    <thead>
                        <tr>
                            <th>Üye</th>
                            <th>Kitap</th>
                            <th>Alış Tarihi</th>
                            <th>Son Teslim Tarihi</th>
                            <th>Teslim Tarihi</th>
                            <th>Gecikme (Gün)</th>
                            <th>Ceza Tutarı</th>
                            <th>Ödeme Durumu</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fines as $fine)
                        <tr class="{{ $fine->paid ? 'table-success fine-paid' : 'table-danger fine-unpaid' }}">
                            <td>{{ $fine->user->name }}</td>
                            <td>{{ $fine->book->title }}</td>
                            <td>{{ optional($fine->borrowing)->borrow_date ? $fine->borrowing->borrow_date->format('d.m.Y') : '-' }}</td>
                            <td>{{ optional($fine->borrowing)->due_date ? $fine->borrowing->due_date->format('d.m.Y') : '-' }}</td>
                            <td>{{ optional($fine->borrowing)->returned_at ? $fine->borrowing->returned_at->format('d.m.Y') : '-' }}</td>
                            <td class="text-danger font-weight-bold">{{ $fine->days_late }}</td>
                            <td class="font-weight-bold">{{ number_format($fine->fine_amount, 2) }} TL</td>
                            <td>
                                @if($fine->paid)
                                    <span class="badge badge-success">Ödendi</span>
                                    @if($fine->paid_at)
                                        <small class="d-block text-muted">{{ $fine->paid_at->format('d.m.Y') }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-danger">Bekliyor</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.fines.show', $fine->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(!$fine->paid)
                                    <button type="button" class="btn btn-sm btn-success ml-1 btn-pay-fine" data-toggle="modal" data-target="#payFineModal" data-fine-id="{{ $fine->id }}" data-fine-amount="{{ number_format($fine->fine_amount, 2) }} TL" data-user-name="{{ $fine->user->name }}" data-book-title="{{ $fine->book->title }}">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                    
                                    <form action="{{ route('admin.fines.forgive', $fine->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-warning ml-1" onclick="return confirm('Bu cezayı gerçekten affetmek istiyor musunuz?')">
                                            <i class="fas fa-hand-holding-heart"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    <form action="{{ route('admin.fines.destroy', $fine->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu ceza kaydını silmek istediğinize emin misiniz?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Ceza kaydı bulunmamaktadır.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gecikmiş Kitaplar Tablosu -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Gecikmiş Kitaplar</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="overdueTable">
                    <thead>
                        <tr>
                            <th>Kitap</th>
                            <th>Üye</th>
                            <th>Ödünç Tarihi</th>
                            <th>İade Tarihi</th>
                            <th>Gecikme (Gün)</th>
                            <th>Tahmini Ceza</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueBorrowings as $borrowing)
                        <tr>
                            <td>{{ $borrowing->book->title }}</td>
                            <td>{{ $borrowing->user->name }}</td>
                            <td>{{ optional($borrowing)->borrow_date ? $borrowing->borrow_date->format('d.m.Y') : (optional($borrowing)->created_at ? $borrowing->created_at->format('d.m.Y') : '-') }}</td>
                            <td class="text-danger">{{ optional($borrowing->due_date)->format('d.m.Y') ?? '-' }}</td>
                            <td class="text-danger font-weight-bold">{{ $borrowing->overdue_days ?? 0 }}</td>
                            <td class="text-danger font-weight-bold">{{ number_format($borrowing->potential_fine ?? 0, 2) }} TL</td>
                            <td>
                                <form action="{{ route('admin.fines.return-overdue-book', $borrowing->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning">İade Et ve Cezalandır</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Gecikmiş kitap bulunmamaktadır.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Ceza Ödeme Modal -->
<div class="modal fade" id="payFineModal" tabindex="-1" role="dialog" aria-labelledby="payFineModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payFineModalLabel">Ceza Ödeme</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" id="finePaymentForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong><span id="payFineUserName"></span></strong> isimli üyenin
                        <strong><span id="payFineBookTitle"></span></strong> kitabı için
                        <strong><span id="payFineAmount"></span></strong> tutarındaki cezası ödenecektir.
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_date">Ödeme Tarihi</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Ödeme Yöntemi</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="cash">Nakit</option>
                            <option value="card">Kredi Kartı</option>
                            <option value="bank">Banka Havalesi</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_notes">Notlar (İsteğe Bağlı)</label>
                        <textarea class="form-control" id="payment_notes" name="payment_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Ödemeyi Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Yeni Ceza Ekleme Modal -->
<div class="modal fade" id="addFineModal" tabindex="-1" aria-labelledby="addFineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFineModalLabel">Yeni Ceza Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.fines.store') }}" method="POST" id="addFineForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="fine_type">Ceza Türü</label>
                        <select class="form-control" id="fine_type" name="reason" required>
                            <option value="">Ceza Türü Seçin</option>
                            <option value="late_return">Geç İade</option>
                            <option value="damaged">Hasarlı Kitap</option>
                            <option value="lost">Kayıp Kitap</option>
                            <option value="other">Diğer</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="user_id">Üye</label>
                        <select class="form-control" id="user_id" name="user_id" required>
                            <option value="">Üye Seçin</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="book_id">Kitap</label>
                        <select class="form-control" id="book_id" name="book_id" required>
                            <option value="">Kitap Seçin</option>
                            @foreach($books ?? [] as $book)
                                <option value="{{ $book->id }}">{{ $book->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="borrowing_id">İlişkili Ödünç (Opsiyonel)</label>
                        <select class="form-control" id="borrowing_id" name="borrowing_id">
                            <option value="">İlişkili Ödünç Yoksa Boş Bırakın</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="fine_amount">Ceza Tutarı (TL)</label>
                        <input type="number" class="form-control" id="fine_amount" name="fine_amount" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="days_late">Gecikme Günü (Varsa)</label>
                        <input type="number" class="form-control" id="days_late" name="days_late" min="0" value="0">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="notes">Notlar</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="paid" name="paid">
                            <label class="custom-control-label" for="paid">Ödendi olarak işaretle</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Ceza Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // CSRF token ayarlarını ekle
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Modal işlemleri
    $('.btn-pay-fine').click(function() {
        var fineId = $(this).data('fine-id');
        var fineAmount = $(this).data('fine-amount');
        var userName = $(this).data('user-name');
        var bookTitle = $(this).data('book-title');
        
        // Modal içeriğini güncelle
        $('#payFineUserName').text(userName);
        $('#payFineBookTitle').text(bookTitle);
        $('#payFineAmount').text(fineAmount);
        
        // Form action'ını ayarla
        $('#finePaymentForm').attr('action', '/admin/fines/' + fineId + '/mark-as-paid');
    });

    // "Yeni Ceza Ekle" butonu tıklandığında modal açılmadan önce hazırlık yap
    var addFineModal = document.getElementById('addFineModal')
    if (addFineModal) {
        addFineModal.addEventListener('show.bs.modal', function (event) {
            // Modal içeriğini sıfırla
            $('#addFineForm')[0].reset();
            console.log('Yeni ceza ekle modalı açılıyor...');
        });
    }
    
    // Üye seçildiğinde ilgili ödünçleri getir
    $('#user_id').change(function() {
        var userId = $(this).val();
        if (userId) {
            console.log('Kullanıcı ID seçildi:', userId);
            
            $.ajax({
                url: '/admin/users/' + userId + '/borrowings',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Borrowings loaded:', data);
                    var options = '<option value="">İlişkili Ödünç Yoksa Boş Bırakın</option>';
                    if (data && data.length > 0) {
                        $.each(data, function(key, value) {
                            options += '<option value="' + value.id + '">' + value.book_title + ' (' + value.borrow_date + ')</option>';
                        });
                    } else {
                        options += '<option value="" disabled>Kullanıcının ödünç kaydı bulunamadı</option>';
                    }
                    $('#borrowing_id').html(options);
                },
                error: function(xhr, status, error) {
                    console.error('Ödünç yükleme hatası:', error);
                    console.error('Durum:', status);
                    console.error('XHR:', xhr.responseText);
                    alert('Ödünç kayıtları yüklenirken bir hata oluştu.');
                }
            });
        } else {
            $('#borrowing_id').html('<option value="">İlişkili Ödünç Yoksa Boş Bırakın</option>');
        }
    });

    // Ödünç kaydı seçildiğinde kitabı otomatik doldur
    $('#borrowing_id').change(function() {
        var borrowingId = $(this).val();
        if (borrowingId) {
            $.ajax({
                url: '/admin/borrowings/' + borrowingId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Borrowing details loaded:', data);
                    $('#book_id').val(data.book_id);
                    
                    // Gecikme varsa hesapla
                    if (data.is_overdue) {
                        $('#fine_type').val('late_return');
                        $('#days_late').val(data.overdue_days);
                        $('#fine_amount').val(data.potential_fine);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ödünç detayları yükleme hatası:', error);
                }
            });
        }
    });
    
    // Ceza türü seçildiğinde önerilen tutarı göster
    $('#fine_type').change(function() {
        var fineType = $(this).val();
        var suggestedAmount = 0;
        
        switch(fineType) {
            case 'damaged':
                suggestedAmount = 50; // Hasarlı kitap için önerilen tutar
                break;
            case 'lost':
                suggestedAmount = 100; // Kayıp kitap için önerilen tutar
                break;
            case 'late_return':
                suggestedAmount = 10; // Geç iade için önerilen tutar
                break;
            default:
                suggestedAmount = 0;
        }
        
        if(suggestedAmount > 0) {
            $('#fine_amount').val(suggestedAmount);
        }
    });
    
    // Ödenen/Ödenmemiş ceza filtresi
    var showOnlyUnpaid = true;
    
    function togglePaidFines() {
        if (showOnlyUnpaid) {
            $('.fine-paid').show();
            $('#togglePaidFines').html('<i class="fas fa-filter"></i> Sadece Ödenmemiş');
            showOnlyUnpaid = false;
        } else {
            $('.fine-paid').hide();
            $('#togglePaidFines').html('<i class="fas fa-filter"></i> Tümünü Göster');
            showOnlyUnpaid = true;
        }
    }
    
    // Başlangıçta ödenen cezaları gizle
    $('.fine-paid').hide();
    
    // Filtre düğmesi işlevi
    $('#togglePaidFines').click(function() {
        togglePaidFines();
    });
    
    // Basit tablo arama fonksiyonu
    $("#searchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#finesTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Debug bilgisi
    console.log('Ceza işlemleri sayfası JavaScript yüklendi');
});
</script>
@endsection 