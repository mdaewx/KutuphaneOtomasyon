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
    .search-results {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
        display: none;
    }
    .search-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .search-item:hover {
        background-color: #f8f9fa;
    }
    .selected-item {
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 10px;
        display: none;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Yeni Ödünç Verme</h1>
        <a href="{{ route('staff.borrowings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Geri Dön
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

            <form id="borrowingForm" action="{{ route('staff.borrowings.store') }}" method="POST">
                @csrf
                
                <!-- Kullanıcı Arama -->
                <div class="mb-4">
                    <label class="form-label">Kullanıcı Ara <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="userSearch" placeholder="İsim veya email ile kullanıcı ara...">
                        <button class="btn btn-outline-secondary d-none" type="button" id="clearUserSearch">
                            <i class="fas fa-times"></i>
                        </button>
                            </div>
                    <div id="userSearchResults" class="search-results"></div>
                    <div id="selectedUser" class="selected-item">
                        <input type="hidden" name="user_id" id="selectedUserId">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1" id="selectedUserName"></h6>
                                <small class="text-muted" id="selectedUserEmail"></small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clearSelectedUser">
                                <i class="fas fa-times"></i>
                            </button>
                    </div>
                    </div>
                </div>

                <!-- Kitap Arama -->
                <div class="mb-4">
                    <label class="form-label">ISBN ile Kitap Ara <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="isbnSearch" placeholder="ISBN numarası girin...">
                        <button class="btn btn-outline-secondary" type="button" id="searchBookBtn">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-outline-secondary d-none" type="button" id="clearBookSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="bookSearchFeedback" class="mt-2" style="display: none;">
                        <div class="alert alert-danger"></div>
                    </div>
                    <div id="selectedBook" class="selected-item">
                        <input type="hidden" name="book_id" id="selectedBookId">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1" id="selectedBookTitle"></h6>
                                <small class="text-muted">ISBN: <span id="selectedBookIsbn"></span></small><br>
                                <small class="text-muted">Yazar(lar): <span id="selectedBookAuthor"></span></small><br>
                                <small class="text-muted">Yayınevi: <span id="selectedBookPublisher"></span></small><br>
                                <small class="text-muted">Kategori: <span id="selectedBookCategory"></span></small><br>
                                <small class="text-muted">Sayfa Sayısı: <span id="selectedBookPages"></span></small><br>
                                <small class="text-muted">Basım Yılı: <span id="selectedBookYear"></span></small><br>
                                <small class="text-muted">Uygun Kopya Sayısı: <span id="selectedBookAvailableStock"></span></small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clearSelectedBook">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Ödünç Alma Tarihi -->
                <div class="mb-4">
                    <label for="borrow_date" class="form-label">Ödünç Alma Tarihi <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="borrow_date" name="borrow_date" required 
                           value="{{ date('Y-m-d') }}">
                </div>

                <!-- Teslim Tarihi -->
                <div class="mb-4">
                    <label for="due_date" class="form-label">Son Teslim Tarihi <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="due_date" name="due_date" required 
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>

                <!-- Notlar -->
                <div class="mb-4">
                    <label for="notes" class="form-label">Notlar</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>

                <!-- Otomatik Onay -->
                <div class="mb-4">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="auto_approve" name="auto_approve" value="1" checked>
                    <label class="form-check-label" for="auto_approve">
                        Otomatik Onayla
                    </label>
                </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="fas fa-save"></i> Kaydet
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Kullanıcı arama
    let userSearchTimeout;
    $('#userSearch').on('input', function() {
        clearTimeout(userSearchTimeout);
        const searchTerm = $(this).val().trim();
        
        // Temizle butonunu göster/gizle
        if (searchTerm.length > 0) {
            $('#clearUserSearch').removeClass('d-none');
        } else {
            $('#clearUserSearch').addClass('d-none');
        }
        
        if (searchTerm.length < 2) {
            $('#userSearchResults').empty().hide();
            return;
        }

        userSearchTimeout = setTimeout(function() {
            $.ajax({
                url: '/staff/borrowings/search-user',
                method: 'GET',
                data: { term: searchTerm },
                beforeSend: function() {
                    $('#userSearchResults').html('<div class="p-2 text-center"><i class="fas fa-spinner fa-spin"></i> Aranıyor...</div>').show();
                },
                success: function(data) {
                    if (data.length === 0) {
                        $('#userSearchResults').html('<div class="p-2 text-center text-muted">Kullanıcı bulunamadı</div>');
                        return;
                    }

                    let html = '';
                    data.forEach(user => {
                        html += `
                            <div class="search-item" data-id="${user.id}" data-name="${user.name}" data-email="${user.email}">
                                <strong>${user.name}</strong><br>
                                <small class="text-muted">${user.email}</small>
                            </div>
                        `;
                    });
                    $('#userSearchResults').html(html);
                },
                error: function(xhr) {
                    $('#userSearchResults').html('<div class="p-2 text-center text-danger">Arama sırasında bir hata oluştu</div>');
                    console.error('Kullanıcı arama hatası:', xhr.responseText);
                }
            });
        }, 300);
    });

    // ISBN arama alanı için input olayı
    $('#isbnSearch').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        // Temizle butonunu göster/gizle
        if (searchTerm.length > 0) {
            $('#clearBookSearch').removeClass('d-none');
        } else {
            $('#clearBookSearch').addClass('d-none');
        }
    });

    // Kullanıcı seçimi
    $(document).on('click', '#userSearchResults .search-item', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const email = $(this).data('email');

        $('#selectedUserId').val(id);
        $('#selectedUserName').text(name);
        $('#selectedUserEmail').text(email);
        
        $('#userSearch').val('');
        $('#clearUserSearch').addClass('d-none');
        $('#userSearchResults').hide();
        $('#selectedUser').show();
        
        checkFormValidity();
    });

    // Kullanıcı seçimini temizle
    $('#clearSelectedUser').click(function() {
        $('#selectedUserId').val('');
        $('#selectedUser').hide();
        $('#userSearch').val('');
        $('#clearUserSearch').addClass('d-none');
        checkFormValidity();
    });

    // Kullanıcı aramasını temizle
    $('#clearUserSearch').click(function() {
        $('#userSearch').val('');
        $('#userSearchResults').hide();
        $(this).addClass('d-none');
    });

    // ISBN ile kitap arama
    $('#searchBookBtn').click(function() {
        const isbn = $('#isbnSearch').val().trim();
        if (!isbn) {
            showBookSearchError('Lütfen bir ISBN numarası girin.');
            return;
        }

        // Arama başlamadan önce loading göster
        $('#searchBookBtn i').removeClass('fa-search').addClass('fa-spinner fa-spin');
        hideBookSearchError();

        $.get(`/staff/books/search/${isbn}`)
            .done(function(response) {
                if (response.book) {
                    const book = response.book;
                    $('#selectedBookId').val(book.id);
                    $('#selectedBookTitle').text(book.title);
                    $('#selectedBookIsbn').text(book.isbn);
                    $('#selectedBookAuthor').text(response.authors);
                    $('#selectedBookPublisher').text(book.publisher ? book.publisher.name : '-');
                    $('#selectedBookCategory').text(book.category ? book.category.name : '-');
                    $('#selectedBookPages').text(book.pages || '-');
                    $('#selectedBookYear').text(book.publication_year || '-');
                    $('#selectedBookAvailableStock').text(response.available_copies);
                    
                    $('#selectedBook').show();
                    hideBookSearchError();
                    checkFormValidity();
                }
            })
            .fail(function(jqXHR) {
                let errorMessage = 'Kitap arama sırasında bir hata oluştu.';
                
                if (jqXHR.status === 404) {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                        errorMessage = jqXHR.responseJSON.error;
                    } else {
                        errorMessage = 'Kitap bulunamadı.';
                    }
                } else if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                    errorMessage = jqXHR.responseJSON.error;
                }
                
                showBookSearchError(errorMessage);
                console.error('Arama hatası:', jqXHR.responseJSON || jqXHR.statusText);
            })
            .always(function() {
                // Loading'i kaldır
                $('#searchBookBtn i').removeClass('fa-spinner fa-spin').addClass('fa-search');
            });
    });

    // Kitap seçimini temizle
    $('#clearSelectedBook').click(function() {
        $('#selectedBookId').val('');
        $('#selectedBook').hide();
        $('#isbnSearch').val('');
        $('#clearBookSearch').addClass('d-none');
        hideBookSearchError();
        checkFormValidity();
    });

    // Kitap aramasını temizle
    $('#clearBookSearch').click(function() {
        $('#isbnSearch').val('');
        $('#clearBookSearch').addClass('d-none');
        hideBookSearchError();
        checkFormValidity();
    });

    // Enter tuşu ile arama yapma
    $('#isbnSearch').on('keypress', function(e) {
        if (e.which === 13) { // Enter tuşu
            e.preventDefault();
            $('#searchBookBtn').click();
        }
    });

    // Kitap arama hata mesajını göster
    function showBookSearchError(message) {
        $('#bookSearchFeedback .alert').text(message);
        $('#bookSearchFeedback').show();
        $('#selectedBook').hide();
        $('#selectedBookId').val('');
    }

    // Kitap arama hata mesajını gizle
    function hideBookSearchError() {
        $('#bookSearchFeedback').hide();
    }

    // Form geçerliliğini kontrol et
    function checkFormValidity() {
        const hasUser = $('#selectedUserId').val() !== '';
        const hasBook = $('#selectedBookId').val() !== '';
        const hasBorrowDate = $('#borrow_date').val() !== '';
        const hasDueDate = $('#due_date').val() !== '';
        
        $('#submitBtn').prop('disabled', !(hasUser && hasBook && hasBorrowDate && hasDueDate));
    }

    // Tarih değişikliklerini izle
    $('#borrow_date, #due_date').on('change', checkFormValidity);
});
</script>
@endsection 