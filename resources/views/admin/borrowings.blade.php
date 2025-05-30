@extends('layouts.admin')

@section('title', 'Ödünç İşlemleri')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    /* Z-index fixes */
    .modal-backdrop {
        z-index: 1040 !important;
    }
    .modal {
        z-index: 1050 !important;
    }
    .select2-container {
        z-index: 1055 !important;
    }
    .select2-container--open {
        z-index: 1056 !important;
    }
    .select2-dropdown {
        z-index: 1056 !important;
        border: 1px solid #e3e6f0;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
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
    
    .select2-container--bootstrap4 .select2-results__option--highlighted {
        background-color: #4e73df !important;
    }
    .select2-container--bootstrap4 .select2-results__option {
        padding: 10px 15px;
    }
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px) !important;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }
    .select2-container--bootstrap4 .select2-selection__rendered {
        line-height: calc(1.5em + 0.75rem) !important;
        padding-left: 12px;
    }
    .select2-container--bootstrap4 .select2-selection__arrow {
        height: calc(1.5em + 0.75rem) !important;
    }
    
    .user-name {
        color: #2c3338;
        font-size: 14px;
        margin-bottom: 2px;
    }
    .select2-container--bootstrap4 .select2-results__option .text-muted {
        font-size: 12px;
        color: #6c757d !important;
    }
    
    /* Search results styling */
    #searchResults {
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-radius: 4px;
        position: absolute;
        width: 100%;
        z-index: 1000;
        background: white;
    }
    
    #searchResults .list-group-item-action:hover {
        background-color: #f8f9fa;
    }
    
    #selectedBook {
        border: 1px solid #d1e7dd;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .search-highlight {
        background-color: #ffff99;
        font-weight: bold;
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
                            <div class="d-flex align-items-center">
                                <div class="book-logo me-2">
                                    <img src="{{ asset('images/icons/book-logo.png') }}" alt="Kitap" style="width: 40px; height: auto;">
                                </div>
                                <div>
                                    <strong>{{ $borrowing->book->title }}</strong><br>
                                    <small class="text-muted">Yazar: {{ $borrowing->book->authors->implode('full_name', ', ') }}</small><br>
                                    <small class="text-muted">ISBN: {{ $borrowing->book->isbn }}</small>
                                </div>
                            </div>
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
                    <!-- Kullanıcı Seçimi -->
                    <div class="form-group mb-3">
                        <label for="user_id">Kullanıcı <span class="text-danger">*</span></label>
                        <select class="form-control select2-users" id="user_id" name="user_id" required>
                            <option value="">Kullanıcı seçin veya aramaya başlayın...</option>
                        </select>
                    </div>

                    <!-- Kitap Seçimi -->
                    <div class="form-group mb-3">
                        <label for="book_search">ISBN veya kitap adı ile ara</label>
                        <input type="text" class="form-control" id="book_search" placeholder="ISBN veya kitap adı...">
                        <div id="searchResults" class="list-group mt-2 d-none"></div>
                        <input type="hidden" name="book_id" id="book_id" required>
                        <div id="selectedBook" class="p-2 mt-2 d-none">
                            <h6 class="book-title mb-1"></h6>
                            <p class="book-details mb-0 small"></p>
                        </div>
                    </div>

                    <!-- Veriliş Tarihi -->
                    <div class="form-group mb-3">
                        <label for="borrow_date">Veriliş Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="borrow_date" name="borrow_date" required value="{{ date('Y-m-d') }}">
                    </div>

                    <!-- İade Tarihi -->
                    <div class="form-group mb-3">
                        <label for="due_date">İade Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="due_date" name="due_date" required value="{{ date('Y-m-d', strtotime('+15 days')) }}">
                    </div>

                    <!-- Notlar -->
                    <div class="form-group">
                        <label for="notes">Notlar</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/tr.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for user search
    $('.select2-users').select2({
        theme: 'bootstrap4',
        language: 'tr',
        placeholder: 'Kullanıcı seçin veya aramaya başlayın...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#addBorrowingModal'),
        ajax: {
            url: '{{ route("admin.borrowings.search.users") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.text,
                            name: item.name,
                            email: item.email
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        templateResult: formatUser,
        templateSelection: formatUserSelection,
        escapeMarkup: function(markup) {
            return markup;
        }
    }).on('select2:open', function() {
        setTimeout(function() {
            $('.select2-search__field').focus();
        }, 100);
    });

    // Format user in dropdown
    function formatUser(user) {
        if (!user.id) return user.text;
        return $(`
            <div class="d-flex flex-column">
                <div class="user-name">${user.name}</div>
                <small class="text-muted">${user.email}</small>
            </div>
        `);
    }

    // Format selected user
    function formatUserSelection(user) {
        if (!user.id) return user.text;
        return user.text;
    }

    // Book search functionality
    let searchTimeout = null;
    $('#book_search').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        const $searchResults = $('#searchResults');
        
        if (searchTerm.length < 2) {
            $searchResults.addClass('d-none').html('');
            return;
        }

        searchTimeout = setTimeout(function() {
            $.get('{{ route("admin.borrowings.search.books") }}', { search: searchTerm })
                .done(function(data) {
                    if (data.length === 0) {
                        $searchResults.html('<div class="list-group-item">Sonuç bulunamadı</div>').removeClass('d-none');
                        return;
                    }

                    const results = data.map(book => `
                        <a href="#" class="list-group-item list-group-item-action book-result" 
                           data-id="${book.id}" 
                           data-title="${book.title}"
                           data-isbn="${book.isbn}"
                           data-author="${book.author}"
                           data-publisher="${book.publisher}"
                           data-year="${book.publication_year}"
                           data-available="${book.available_count}"
                           data-total="${book.total_count}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${book.title}</h6>
                                    <div class="small text-muted">
                                        <div><strong>Yazar:</strong> ${book.author}</div>
                                        <div><strong>Yayınevi:</strong> ${book.publisher}</div>
                                        <div><strong>ISBN:</strong> ${book.isbn}</div>
                                        <div><strong>Yayın Yılı:</strong> ${book.publication_year}</div>
                                    </div>
                                </div>
                                <div class="text-end ms-3">
                                    <span class="badge bg-primary">${book.available_count}/${book.total_count} adet</span>
                                </div>
                            </div>
                        </a>
                    `).join('');

                    $searchResults.html(results).removeClass('d-none');
                });
        }, 300);
    });

    // Handle book selection
    $(document).on('click', '.book-result', function(e) {
        e.preventDefault();
        const $this = $(this);
        const bookId = $this.data('id');
        const bookTitle = $this.data('title');
        const bookIsbn = $this.data('isbn');
        const bookAuthor = $this.data('author');
        const bookPublisher = $this.data('publisher');
        const bookYear = $this.data('year');
        const availableCount = $this.data('available');
        const totalCount = $this.data('total');

        $('#book_id').val(bookId);
        $('#searchResults').addClass('d-none');
        $('#book_search').val(bookTitle);

        const $selectedBook = $('#selectedBook');
        $selectedBook.removeClass('d-none')
            .find('.book-title').text(bookTitle);
        $selectedBook.find('.book-details').html(`
            <div><strong>Yazar:</strong> ${bookAuthor}</div>
            <div><strong>Yayınevi:</strong> ${bookPublisher}</div>
            <div><strong>ISBN:</strong> ${bookIsbn}</div>
            <div><strong>Yayın Yılı:</strong> ${bookYear}</div>
            <div><strong>Mevcut:</strong> ${availableCount}/${totalCount} adet</div>
        `);
    });

    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#book_search, #searchResults').length) {
            $('#searchResults').addClass('d-none');
        }
    });

    // Tarih kontrolü
    $('#borrow_date, #due_date').on('change', function() {
        const borrowDate = new Date($('#borrow_date').val());
        const dueDate = new Date($('#due_date').val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // Veriliş tarihi bugünden önce olamaz
        if (borrowDate < today) {
            alert('Veriliş tarihi bugünden önce olamaz');
            $('#borrow_date').val(today.toISOString().split('T')[0]);
            return;
        }

        // İade tarihi veriliş tarihinden önce olamaz
        if (dueDate <= borrowDate) {
            alert('İade tarihi veriliş tarihinden sonra olmalıdır');
            $('#due_date').val('');
            return;
        }

        // Maksimum 30 gün kuralı
        const maxDueDate = new Date(borrowDate);
        maxDueDate.setDate(maxDueDate.getDate() + 30);
        if (dueDate > maxDueDate) {
            alert('İade tarihi veriliş tarihinden en fazla 30 gün sonra olabilir');
            $('#due_date').val(maxDueDate.toISOString().split('T')[0]);
        }
    });

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
                        <div class="book-logo mb-3">
                            <img src="{{ asset('images/icons/book-logo.png') }}" alt="Kitap" style="width: 50px; height: auto;">
                        </div>
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
                    <div class="book-logo mb-3">
                        <img src="{{ asset('images/icons/book-logo.png') }}" alt="Kitap" style="width: 50px; height: auto;">
                    </div>
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