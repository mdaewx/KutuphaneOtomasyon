@extends('layouts.admin')

@section('title', 'Yeni Ödünç Verme')

@section('styles')
<style>
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
        <a href="{{ route('admin.borrowings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Geri Dön
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ödünç Bilgileri</h6>
        </div>
        <div class="card-body">
            <form id="borrowingForm" action="{{ route('admin.borrowings.store') }}" method="POST">
                @csrf
                
                <!-- Kullanıcı Arama -->
                <div class="mb-4">
                    <label class="form-label">Kullanıcı Ara <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="userSearch" placeholder="İsim veya email ile kullanıcı ara...">
                        <button class="btn btn-outline-secondary" type="button" id="clearUserSearch">
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
                        <button class="btn btn-outline-secondary" type="button" id="clearBookSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="selectedBook" class="selected-item">
                        <input type="hidden" name="book_id" id="selectedBookId">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1" id="selectedBookTitle"></h6>
                                <small class="text-muted">ISBN: <span id="selectedBookIsbn"></span></small><br>
                                <small class="text-muted">Yazar: <span id="selectedBookAuthor"></span></small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clearSelectedBook">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
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

                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
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
    // Kullanıcı arama
    let userSearchTimeout;
    $('#userSearch').on('input', function() {
        clearTimeout(userSearchTimeout);
        const searchTerm = $(this).val();
        
        if (searchTerm.length < 2) {
            $('#userSearchResults').hide();
            return;
        }

        userSearchTimeout = setTimeout(function() {
            $.get('/admin/users/search', { term: searchTerm }, function(data) {
                let html = '';
                data.forEach(user => {
                    html += `
                        <div class="search-item" data-id="${user.id}" data-name="${user.name}" data-email="${user.email}">
                            <strong>${user.name}</strong><br>
                            <small class="text-muted">${user.email}</small>
                        </div>
                    `;
                });
                $('#userSearchResults').html(html).show();
            });
        }, 300);
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
        $('#userSearchResults').hide();
        $('#selectedUser').show();
        
        checkFormValidity();
    });

    // Kullanıcı seçimini temizle
    $('#clearSelectedUser').click(function() {
        $('#selectedUserId').val('');
        $('#selectedUser').hide();
        checkFormValidity();
    });

    // Kullanıcı aramasını temizle
    $('#clearUserSearch').click(function() {
        $('#userSearch').val('');
        $('#userSearchResults').hide();
    });

    // ISBN ile kitap arama
    $('#searchBookBtn').click(function() {
        const isbn = $('#isbnSearch').val().trim();
        if (!isbn) return;

        $.get(`/admin/books/search/${isbn}`, function(response) {
            if (response.success) {
                const book = response.book;
                $('#selectedBookId').val(book.id);
                $('#selectedBookTitle').text(book.title);
                $('#selectedBookIsbn').text(book.isbn);
                $('#selectedBookAuthor').text(book.authors);
                
                $('#selectedBook').show();
                checkFormValidity();
            } else {
                alert('Kitap bulunamadı.');
            }
        }).fail(function() {
            alert('Kitap arama sırasında bir hata oluştu.');
        });
    });

    // Kitap seçimini temizle
    $('#clearSelectedBook').click(function() {
        $('#selectedBookId').val('');
        $('#selectedBook').hide();
        checkFormValidity();
    });

    // Kitap aramasını temizle
    $('#clearBookSearch').click(function() {
        $('#isbnSearch').val('');
        $('#selectedBook').hide();
        $('#selectedBookId').val('');
        checkFormValidity();
    });

    // Form geçerliliğini kontrol et
    function checkFormValidity() {
        const hasUser = $('#selectedUserId').val() !== '';
        const hasBook = $('#selectedBookId').val() !== '';
        const hasDueDate = $('#due_date').val() !== '';
        
        $('#submitBtn').prop('disabled', !(hasUser && hasBook && hasDueDate));
    }

    // Teslim tarihi değişikliğini izle
    $('#due_date').on('change', checkFormValidity);
});
</script>
@endsection 