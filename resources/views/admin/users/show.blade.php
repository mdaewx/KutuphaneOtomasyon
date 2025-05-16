@extends('layouts.admin')

@section('title', 'Kullanıcı Detayları')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Kullanıcı Detayları</h1>
    <div>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Düzenle
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary shadow-sm ms-2">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kullanıcılara Dön
        </a>
    </div>
</div>

<div class="row">
    <!-- User Profile Information -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <!-- Card Header -->
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profil Bilgileri</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="text-center mb-4">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/profiles/' . $user->profile_photo) }}" class="img-profile rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF" class="img-profile rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                    @endif
                    <h4 class="mt-3">{{ $user->name }} {{ $user->surname }}</h4>
                    <span class="badge {{ $user->role == 'admin' ? 'bg-primary' : 'bg-secondary' }} p-2">
                        {{ $user->role == 'admin' ? 'Yönetici' : 'Kullanıcı' }}
                    </span>
                </div>

                <div class="mb-4">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 40%;"><i class="fas fa-envelope me-2 text-gray-500"></i> E-posta:</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th scope="row"><i class="fas fa-phone me-2 text-gray-500"></i> Telefon:</th>
                                <td>{{ $user->phone ?: 'Belirtilmemiş' }}</td>
                            </tr>
                            <tr>
                                <th scope="row"><i class="fas fa-map-marker-alt me-2 text-gray-500"></i> Adres:</th>
                                <td>{{ $user->address ?: 'Belirtilmemiş' }}</td>
                            </tr>
                            <tr>
                                <th scope="row"><i class="fas fa-calendar me-2 text-gray-500"></i> Kayıt Tarihi:</th>
                                <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th scope="row"><i class="fas fa-clock me-2 text-gray-500"></i> Son Güncelleme:</th>
                                <td>{{ $user->updated_at->format('d.m.Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                        <i class="fas fa-trash-alt me-1"></i> Kullanıcıyı Sil
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Borrowings -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <!-- Card Header -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Kullanıcının Ödünç Aldığı Kitaplar</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Ödünç İşlemleri:</div>
                        <a class="dropdown-item" href="{{ route('admin.borrowings.index') }}">
                            <i class="fas fa-list fa-sm fa-fw me-2 text-gray-400"></i>Tüm Ödünçler
                        </a>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addBorrowingModal" onclick="selectUserInBorrowingModal({{ $user->id }})">
                            <i class="fas fa-plus fa-sm fa-fw me-2 text-gray-400"></i>Yeni Ödünç Verme
                        </a>
                    </div>
                </div>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <ul class="nav nav-tabs" id="borrowingsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="active-tab" data-bs-toggle="tab" href="#active" role="tab">
                            Aktif Ödünçler <span class="badge bg-primary ms-1">{{ $activeBorrowings->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="history-tab" data-bs-toggle="tab" href="#history" role="tab">
                            Geçmiş <span class="badge bg-secondary ms-1">{{ $returnedBorrowings->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="overdue-tab" data-bs-toggle="tab" href="#overdue" role="tab">
                            Geciken <span class="badge bg-danger ms-1">{{ $overdueBorrowings->count() }}</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="borrowingsTabContent">
                    <!-- Active Borrowings Tab -->
                    <div class="tab-pane fade show active" id="active" role="tabpanel">
                        @if($activeBorrowings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kitap</th>
                                            <th>Ödünç Tarihi</th>
                                            <th>Son Teslim Tarihi</th>
                                            <th>Durum</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeBorrowings as $borrowing)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ asset('images/icons/book-logo.png') }}" 
                                                            alt="{{ $borrowing->book->title }}" class="me-2" style="width: 40px; height: 60px; object-fit: contain;">
                                                        <div>
                                                            <div class="fw-bold">{{ $borrowing->book->title }}</div>
                                                            <small class="text-muted">{{ $borrowing->book->author }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $borrowing->borrow_date ? date('d.m.Y', strtotime($borrowing->borrow_date)) : '-' }}</td>
                                                <td>{{ $borrowing->due_date ? date('d.m.Y', strtotime($borrowing->due_date)) : '-' }}</td>
                                                <td>
                                                    @if($borrowing->isOverdue())
                                                        <span class="badge bg-danger">{{ now()->diffInDays($borrowing->due_date) }} gün gecikmiş</span>
                                                    @elseif(now()->diffInDays($borrowing->due_date, false) <= 3)
                                                        <span class="badge bg-warning">{{ now()->diffInDays($borrowing->due_date, false) }} gün kaldı</span>
                                                    @else
                                                        <span class="badge bg-success">{{ now()->diffInDays($borrowing->due_date, false) }} gün kaldı</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm return-borrowing" 
                                                        data-id="{{ $borrowing->id }}" 
                                                        data-book="{{ $borrowing->book->title }}"
                                                        data-user="{{ $borrowing->user->name }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#returnBorrowingModal"
                                                        title="İade Et">
                                                        <i class="fas fa-undo"></i> İade Et
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i> Kullanıcının aktif ödünç aldığı kitap bulunmamaktadır.
                            </div>
                        @endif
                    </div>
                    
                    <!-- History Tab -->
                    <div class="tab-pane fade" id="history" role="tabpanel">
                        @if($returnedBorrowings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kitap</th>
                                            <th>Ödünç Tarihi</th>
                                            <th>İade Tarihi</th>
                                            <th>Son Teslim Tarihi</th>
                                            <th>Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($returnedBorrowings as $borrowing)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ asset('images/icons/book-logo.png') }}" 
                                                            alt="{{ $borrowing->book->title }}" class="me-2" style="width: 40px; height: 60px; object-fit: contain;">
                                                        <div>
                                                            <div class="fw-bold">{{ $borrowing->book->title }}</div>
                                                            <small class="text-muted">{{ $borrowing->book->author }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $borrowing->borrow_date ? date('d.m.Y', strtotime($borrowing->borrow_date)) : '-' }}</td>
                                                <td>{{ $borrowing->returned_at ? date('d.m.Y', strtotime($borrowing->returned_at)) : '-' }}</td>
                                                <td>{{ $borrowing->due_date ? date('d.m.Y', strtotime($borrowing->due_date)) : '-' }}</td>
                                                <td>
                                                    @if($borrowing->returned_at && $borrowing->due_date && \Carbon\Carbon::parse($borrowing->returned_at)->gt(\Carbon\Carbon::parse($borrowing->due_date)))
                                                        <span class="badge bg-warning">{{\Carbon\Carbon::parse($borrowing->returned_at)->diffInDays(\Carbon\Carbon::parse($borrowing->due_date))}} gün geç iade</span>
                                                    @else
                                                        <span class="badge bg-success">Zamanında iade edildi</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i> Kullanıcının iade edilmiş kitabı bulunmamaktadır.
                            </div>
                        @endif
                    </div>
                    
                    <!-- Overdue Tab -->
                    <div class="tab-pane fade" id="overdue" role="tabpanel">
                        @if($overdueBorrowings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kitap</th>
                                            <th>Ödünç Tarihi</th>
                                            <th>Son Teslim Tarihi</th>
                                            <th>Gecikme</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($overdueBorrowings as $borrowing)
                                            <tr class="table-danger">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ asset('images/icons/book-logo.png') }}" 
                                                            alt="{{ $borrowing->book->title }}" class="me-2" style="width: 40px; height: 60px; object-fit: contain;">
                                                        <div>
                                                            <div class="fw-bold">{{ $borrowing->book->title }}</div>
                                                            <small class="text-muted">{{ $borrowing->book->author }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $borrowing->borrow_date ? date('d.m.Y', strtotime($borrowing->borrow_date)) : '-' }}</td>
                                                <td class="text-danger fw-bold">{{ $borrowing->due_date ? date('d.m.Y', strtotime($borrowing->due_date)) : '-' }}</td>
                                                <td>
                                                    <span class="badge bg-danger">{{ now()->diffInDays($borrowing->due_date) }} gün</span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm return-borrowing" 
                                                        data-id="{{ $borrowing->id }}" 
                                                        data-book="{{ $borrowing->book->title }}"
                                                        data-user="{{ $borrowing->user->name }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#returnBorrowingModal"
                                                        title="İade Et">
                                                        <i class="fas fa-undo"></i> İade Et
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-1"></i> Kullanıcının gecikmiş kitabı bulunmamaktadır.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Kullanıcıyı Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ $user->name }} {{ $user->surname }} isimli kullanıcıyı silmek istediğinize emin misiniz?</p>
                <p class="text-danger">Bu işlem geri alınamaz ve kullanıcının tüm verileri silinecektir.</p>
                
                @if($activeBorrowings->count() > 0 || $returnedBorrowings->count() > 0)
                <div class="alert alert-warning">
                    Bu kullanıcının {{ $activeBorrowings->count() + $returnedBorrowings->count() }} adet ödünç alma işlemi var!
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Kullanıcıyı Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Borrowing Modal -->
<div class="modal fade" id="addBorrowingModal" tabindex="-1" role="dialog" aria-labelledby="addBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
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
                            @foreach(App\Models\User::where('is_admin', 0)->get() as $selectUser)
                                <option value="{{ $selectUser->id }}" {{ $selectUser->id == $user->id ? 'selected' : '' }}>
                                    {{ $selectUser->name }} {{ $selectUser->surname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="book_id" class="form-label">Kitap <span class="text-danger">*</span></label>
                        <select class="form-select" id="book_id" name="book_id" required>
                            <option value="">Kitap Seçin</option>
                            @foreach(App\Models\Book::where('quantity', '>', 0)->get() as $book)
                                <option value="{{ $book->id }}">
                                    {{ $book->title }} - {{ $book->author }} 
                                    ({{ $book->quantity > 0 ? $book->quantity . ' adet mevcut' : 'Stokta Yok' }})
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

<!-- Return Borrowing Modal -->
<div class="modal fade" id="returnBorrowingModal" tabindex="-1" role="dialog" aria-labelledby="returnBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Return Borrowing Modal
        $('.return-borrowing').click(function() {
            var id = $(this).data('id');
            var book = $(this).data('book');
            var user = $(this).data('user');
            
            $('#returnBorrowingForm').attr('action', "{{ route('admin.borrowings.return', '') }}/" + id);
            $('#return_book_title').text(book);
            $('#return_user_name').text(user);
        });
        
        // 30 gün kısıtlaması için tarih kontrolleri
        // Format date helper function
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
        
        // Enforce 30-day maximum between borrow_date and due_date
        $('#borrow_date, #due_date').on('change', function() {
            var borrowDate = new Date($('#borrow_date').val());
            var dueDate = new Date($('#due_date').val());
            
            // Maximum 30 days rule
            var maxDueDate = new Date(borrowDate);
            maxDueDate.setDate(maxDueDate.getDate() + 30);
            
            // Format dates for comparison
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Ensure borrow date is not in the past
            if (borrowDate < today) {
                alert('Ödünç alma tarihi geçmiş bir tarih olamaz.');
                $('#borrow_date').val(formatDate(today));
                borrowDate = today;
                
                // Recalculate max due date
                maxDueDate = new Date(today);
                maxDueDate.setDate(maxDueDate.getDate() + 30);
            }
            
            // Ensure due date is after borrow date
            if (dueDate <= borrowDate) {
                alert('Son iade tarihi, ödünç alma tarihinden sonra olmalıdır.');
                var newDueDate = new Date(borrowDate);
                newDueDate.setDate(newDueDate.getDate() + 15); // Default to 15 days
                $('#due_date').val(formatDate(newDueDate));
                dueDate = newDueDate;
            }
            
            // Ensure due date is not more than 30 days after borrow date
            if (dueDate > maxDueDate) {
                alert('Son iade tarihi, ödünç alma tarihinden en fazla 30 gün sonra olabilir.');
                $('#due_date').val(formatDate(maxDueDate));
            }
        });
        
        // Set max attribute for due_date based on borrow_date
        $('#borrow_date').on('change', function() {
            var borrowDate = new Date($(this).val());
            var maxDueDate = new Date(borrowDate);
            maxDueDate.setDate(maxDueDate.getDate() + 30);
            
            // Set max attribute for due_date
            $('#due_date').attr('max', formatDate(maxDueDate));
        });
        
        // Initialize max attribute on page load
        var borrowDate = new Date($('#borrow_date').val());
        var maxDueDate = new Date(borrowDate);
        maxDueDate.setDate(maxDueDate.getDate() + 30);
        $('#due_date').attr('max', formatDate(maxDueDate));
    });
    
    // Function to select user in the borrowing modal
    function selectUserInBorrowingModal(userId) {
        setTimeout(function() {
            $('#user_id').val(userId);
        }, 500); // slight delay to ensure modal is fully loaded
    }
</script>
@endsection 