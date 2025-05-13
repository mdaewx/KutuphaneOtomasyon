@extends('layouts.admin')

@section('title', 'Ödünç İşlemleri')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .select2-container {
        z-index: 9999;
    }
    .modal {
        z-index: 1050;
    }
    .modal-dialog {
        overflow-y: initial !important
    }
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    .modal .select2-container {
        width: 100% !important;
    }
    /* Dropdown menü için düzeltme */
    .modal-open .select2-dropdown {
        z-index: 10000;
    }
    .select2-dropdown {
        z-index: 10000;
    }
    .select2-results {
        max-height: 200px;
        overflow-y: auto;
    }
    /* Form elemanları için */
    .modal select.form-select,
    .modal select.form-control {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        display: block !important;
        position: relative !important;
        height: auto;
    }
    
    /* Dropdown açılıp kapanma düzeltmesi */
    .modal-content {
        overflow: visible;
    }
    
    /* Çakışmaları önlemek için */
    .modal-backdrop {
        z-index: 1040;
    }
</style>
@endsection

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Ödünç İşlemleri</h1>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBorrowingModal">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Yeni Ödünç Verme
    </button>
</div>

<!-- DataTables Borrowings -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Ödünç İşlemleri Listesi</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="borrowingsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kullanıcı</th>
                        <th>Kitap</th>
                        <th>Ödünç Tarihi</th>
                        <th>Teslim Tarihi</th>
                        <th>Son Tarih</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowings as $borrowing)
                    <tr>
                        <td>{{ $borrowing->id }}</td>
                        <td>
                            <strong>{{ $borrowing->user->name }}</strong><br>
                            <small class="text-muted">{{ $borrowing->user->email }}</small>
                        </td>
                        <td>
                            <strong>{{ $borrowing->book->title }}</strong><br>
                            <small class="text-muted">Yazar: {{ $borrowing->book->author }}</small><br>
                            <small class="text-muted">ISBN: {{ $borrowing->book->isbn }}</small>
                        </td>
                        <td>{{ $borrowing->borrow_date ? date('d.m.Y', strtotime($borrowing->borrow_date)) : '-' }}</td>
                        <td>{{ $borrowing->returned_at ? date('d.m.Y', strtotime($borrowing->returned_at)) : '-' }}</td>
                        <td>{{ $borrowing->due_date ? date('d.m.Y', strtotime($borrowing->due_date)) : '-' }}</td>
                        <td>
                            @if($borrowing->isOverdue())
                                <span class="badge bg-danger">Gecikmiş</span>
                            @elseif($borrowing->status == 'pending')
                                <span class="badge bg-warning">Beklemede</span>
                            @elseif($borrowing->status == 'approved')
                                <span class="badge bg-success">Onaylandı</span>
                            @elseif($borrowing->status == 'rejected')
                                <span class="badge bg-danger">Reddedildi</span>
                            @elseif($borrowing->status == 'returned')
                                <span class="badge bg-info">İade Edildi</span>
                            @else
                                <span class="badge bg-secondary">{{ $borrowing->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @if($borrowing->status == 'pending')
                                    <button class="btn btn-sm btn-success approve-borrowing" 
                                            data-id="{{ $borrowing->id }}" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveBorrowingModal"
                                            title="Onayla">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger reject-borrowing" 
                                            data-id="{{ $borrowing->id }}" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectBorrowingModal"
                                            title="Reddet">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                
                                @if($borrowing->status == 'approved' || $borrowing->isOverdue())
                                    <button class="btn btn-sm btn-primary return-borrowing" 
                                            data-id="{{ $borrowing->id }}" 
                                            data-book="{{ $borrowing->book->title }}"
                                            data-user="{{ $borrowing->user->name }}"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#returnBorrowingModal"
                                            title="İade Et">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                @endif
                                
                                <button class="btn btn-sm btn-info view-borrowing" 
                                        data-id="{{ $borrowing->id }}" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewBorrowingModal"
                                        title="Görüntüle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <button class="btn btn-sm btn-danger delete-borrowing" 
                                        data-id="{{ $borrowing->id }}" 
                                        data-book="{{ $borrowing->book->title }}"
                                        data-user="{{ $borrowing->user->name }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteBorrowingModal"
                                        title="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                @if($borrowings instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $borrowings->appends(request()->all())->links() }}
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Borrowing Modal -->
<div class="modal fade" id="addBorrowingModal" tabindex="-1" aria-labelledby="addBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBorrowingModalLabel">Yeni Ödünç Verme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.borrowings.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Kullanıcı <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Kullanıcı Seçin</option>
                            @forelse($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) - ID: {{ $user->id }}</option>
                            @empty
                                <option value="" disabled>Kullanıcı bulunamadı</option>
                            @endforelse
                        </select>
                        <small class="text-muted">
                            Listede {{ count($users) }} kullanıcı var. Personel ve yöneticiler gösterilmemektedir.
                            <a href="{{ route('admin.users.index') }}" target="_blank" class="text-primary">Kullanıcı eklemek için tıklayın</a>
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="book_id" class="form-label">Kitap <span class="text-danger">*</span></label>
                        <select class="form-select" id="book_id" name="book_id" required>
                            <option value="">Kitap Seçin</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" {{ $book->quantity <= 0 ? 'disabled' : '' }}>
                                    {{ $book->title }} | {{ $book->author }} | ISBN: {{ $book->isbn }} {{ $book->quantity <= 0 ? '(Stokta Yok)' : '(Stok: ' . $book->quantity . ')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="borrow_date" class="form-label">Ödünç Alma Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="borrow_date" name="borrow_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Son İade Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="due_date" name="due_date" value="{{ date('Y-m-d', strtotime('+15 days')) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notlar</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="auto_approve" name="auto_approve" value="1" checked>
                        <label class="form-check-label" for="auto_approve">Otomatik Onayla</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {
        // DataTable Initialization
        $('#borrowingsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Turkish.json"
            },
            "pageLength": 25,
            "ordering": true,
            "paging": false,
            "info": false,
            "searching": false
        });
        
        // Modal açıldığında form verilerini sıfırla
        $('#addBorrowingModal').on('shown.bs.modal', function() {
            console.log('Modal açıldı, kullanıcı sayısı: ' + {{ count($users) }});
            $(this).find('form')[0].reset();
        });
        
        // DateRangePicker Initialization
        $('#dateRange').daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            locale: {
                format: 'DD.MM.YYYY',
                separator: ' - ',
                applyLabel: 'Uygula',
                cancelLabel: 'İptal',
                fromLabel: 'Başlangıç',
                toLabel: 'Bitiş',
                customRangeLabel: 'Özel Aralık',
                weekLabel: 'H',
                daysOfWeek: ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'],
                monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
                firstDay: 1
            }
        });
        
        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
        });
        
        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
        // Return Borrowing Modal
        $('.return-borrowing').click(function() {
            var id = $(this).data('id');
            var book = $(this).data('book');
            var user = $(this).data('user');
            
            $('#returnBorrowingForm').attr('action', "{{ route('admin.borrowings.return', ':id') }}".replace(':id', id));
            $('#return_book_title').text(book);
            $('#return_user_name').text(user);
        });
        
        // View Borrowing Modal
        $('.view-borrowing').click(function() {
            var id = $(this).data('id');
            
            // Reset content and show loader
            $('#borrowingDetailContent').html(`
                <div class="text-center mb-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                    <p class="mt-2">Bilgiler yükleniyor...</p>
                </div>
            `);
            
            // Get borrowing details via AJAX
            $.get("{{ route('admin.borrowings.show', ':id') }}".replace(':id', id), function(data) {
                if (data.html) {
                    $('#borrowingDetailContent').html(data.html);
                } else {
                    $('#borrowingDetailContent').html('<div class="alert alert-danger">Bilgiler yüklenirken bir hata oluştu.</div>');
                }
            }).fail(function() {
                $('#borrowingDetailContent').html('<div class="alert alert-danger">Bilgiler yüklenirken bir hata oluştu.</div>');
            });
        });
        
        // Approve Borrowing Modal
        $('.approve-borrowing').click(function() {
            var id = $(this).data('id');
            
            $('#approveBorrowingForm').attr('action', "{{ route('admin.borrowings.update-status', ':id') }}".replace(':id', id));
        });
        
        // Reject Borrowing Modal
        $('.reject-borrowing').click(function() {
            var id = $(this).data('id');
            
            $('#rejectBorrowingForm').attr('action', "{{ route('admin.borrowings.update-status', ':id') }}".replace(':id', id));
        });
        
        // Delete Borrowing Modal
        $('.delete-borrowing').click(function() {
            var id = $(this).data('id');
            var book = $(this).data('book');
            var user = $(this).data('user');
            
            $('#deleteBorrowingForm').attr('action', "{{ route('admin.borrowings.destroy', ':id') }}".replace(':id', id));
            $('#delete_book_title').text(book);
            $('#delete_user_name').text(user);
        });
        
        // Validate due date is after borrow date
        $('#borrow_date, #due_date').on('change', function() {
            var borrowDate = new Date($('#borrow_date').val());
            var dueDate = new Date($('#due_date').val());
            
            if (dueDate <= borrowDate) {
                alert('Son iade tarihi, ödünç alma tarihinden sonra olmalıdır.');
                $('#due_date').val(moment(borrowDate).add(15, 'days').format('YYYY-MM-DD'));
            }
        });
        
        // Add Borrowing Modal - Yeni Ödünç Verme İşlemi
        $('#addBorrowingModal').on('shown.bs.modal', function () {
            // Form validasyonu
            var borrowDateInput = $('#borrow_date');
            var dueDateInput = $('#due_date');
            
            // Tarihleri kontrol et
            function checkDates() {
                var borrowDate = new Date(borrowDateInput.val());
                var dueDate = new Date(dueDateInput.val());
                
                if (dueDate <= borrowDate) {
                    alert('Son iade tarihi, ödünç alma tarihinden sonra olmalıdır.');
                    var newDueDate = new Date(borrowDate);
                    newDueDate.setDate(newDueDate.getDate() + 15);
                    dueDateInput.val(formatDate(newDueDate));
                }
            }
            
            function formatDate(date) {
                var d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();
            
                if (month.length < 2) 
                    month = '0' + month;
                if (day.length < 2) 
                    day = '0' + day;
            
                return [year, month, day].join('-');
            }
            
            borrowDateInput.on('change', checkDates);
            dueDateInput.on('change', checkDates);
        });
    });
</script>
@endsection

<!-- Return Borrowing Modal -->
<div class="modal fade" id="returnBorrowingModal" tabindex="-1" aria-labelledby="returnBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnBorrowingModalLabel">Kitap İadesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="returnBorrowingForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-book-reader fa-4x text-primary mb-3"></i>
                        <h5 class="text-gray-900" id="return_book_title"></h5>
                        <p class="text-gray-600" id="return_user_name"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="return_date" class="form-label">İade Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="return_date" name="returned_at" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="return_condition" class="form-label">Kitap Durumu</label>
                        <select class="form-select" id="return_condition" name="condition">
                            <option value="good">İyi Durumda</option>
                            <option value="damaged">Hasarlı</option>
                            <option value="lost">Kayıp</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="return_notes" class="form-label">Notlar</label>
                        <textarea class="form-control" id="return_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">İade Et</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Borrowing Modal -->
<div class="modal fade" id="viewBorrowingModal" tabindex="-1" aria-labelledby="viewBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewBorrowingModalLabel">Ödünç Detayları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="borrowingDetailContent">
                    <div class="text-center mb-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <p class="mt-2">Bilgiler yükleniyor...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Borrowing Modal -->
<div class="modal fade" id="approveBorrowingModal" tabindex="-1" aria-labelledby="approveBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveBorrowingModalLabel">Ödünç İşlemini Onayla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveBorrowingForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="approve">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <p>Bu ödünç işlemini onaylamak istediğinize emin misiniz?</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="approve_due_date" class="form-label">Son İade Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="approve_due_date" name="due_date" value="{{ date('Y-m-d', strtotime('+15 days')) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="approve_notes" class="form-label">Notlar</label>
                        <textarea class="form-control" id="approve_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Borrowing Modal -->
<div class="modal fade" id="rejectBorrowingModal" tabindex="-1" aria-labelledby="rejectBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectBorrowingModalLabel">Ödünç İşlemini Reddet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectBorrowingForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="reject">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                        <p>Bu ödünç işlemini reddetmek istediğinize emin misiniz?</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Ret Nedeni <span class="text-danger">*</span></label>
                        <select class="form-select" id="reject_reason" name="reason" required>
                            <option value="">Ret Nedeni Seçin</option>
                            <option value="quota_exceeded">Kullanıcı kotası aşıldı</option>
                            <option value="book_not_available">Kitap mevcut değil</option>
                            <option value="user_has_overdue">Kullanıcının gecikmiş kitapları var</option>
                            <option value="user_has_fines">Kullanıcının ödenmemiş cezaları var</option>
                            <option value="other">Diğer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reject_notes" class="form-label">Notlar</label>
                        <textarea class="form-control" id="reject_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">Reddet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Borrowing Modal -->
<div class="modal fade" id="deleteBorrowingModal" tabindex="-1" aria-labelledby="deleteBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBorrowingModalLabel">Ödünç İşlemini Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-trash fa-4x text-danger mb-3"></i>
                    <p>Bu ödünç işlemini silmek istediğinize emin misiniz?</p>
                    <div>
                        <strong>Kitap:</strong> <span id="delete_book_title"></span><br>
                        <strong>Kullanıcı:</strong> <span id="delete_user_name"></span>
                    </div>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i> Bu işlem geri alınamaz ve ilgili kayıtlar tamamen silinecektir.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form id="deleteBorrowingForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>