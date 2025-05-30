@extends('layouts.staff')

@section('title', 'Yeni Ödünç Verme')

@section('styles')
<style>
    .search-results {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
        z-index: 1000;
        position: absolute;
        width: 100%;
    }
    .search-item {
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .search-item:hover {
        background-color: #f8f9fa;
    }
    .book-title {
        font-weight: bold;
        margin-bottom: 5px;
    }
    .book-info {
        font-size: 0.9em;
        color: #666;
    }
    .selected-item {
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 10px;
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
            <form id="borrowingForm" method="POST" action="{{ route('staff.borrowings.store') }}">
                @csrf
                <div class="row">
                    <!-- Kullanıcı Seçimi -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kullanıcı <span class="text-danger">*</span></label>
                        <div class="user-search-container position-relative">
                            <div class="input-group">
                                <input type="text" class="form-control" id="userSearch" placeholder="Ad, soyad, e-posta veya telefon ile ara...">
                                <button class="btn btn-outline-secondary" type="button" id="searchUserBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <input type="hidden" name="user_id" id="selectedUserId">
                            <div id="userSearchResults" class="search-results" style="display: none;"></div>
                            <div id="selectedUserDetails" class="selected-item" style="display: none;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1" id="selectedUserName"></h6>
                                        <p class="mb-1" id="selectedUserEmail"></p>
                                        <p class="mb-1" id="selectedUserPhone"></p>
                                        <p class="mb-0" id="selectedUserMemberSince"></p>
                                        <div id="selectedUserCurrentBooks" class="mt-2"></div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="clearUserSelection">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kitap Seçimi -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kitap Ara <span class="text-danger">*</span></label>
                        <div class="book-search-container position-relative">
                            <div class="input-group">
                                <input type="text" class="form-control" id="bookSearch" placeholder="ISBN veya kitap adı ile ara...">
                                <button class="btn btn-outline-secondary" type="button" id="searchBookBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <input type="hidden" name="book_id" id="selectedBookId">
                            <div id="bookSearchResults" class="search-results" style="display: none;"></div>
                            <div id="selectedBookDetails" class="selected-item" style="display: none;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1" id="selectedBookTitle"></h6>
                                        <p class="mb-1" id="selectedBookAuthor"></p>
                                        <p class="mb-1" id="selectedBookPublisher"></p>
                                        <p class="mb-0" id="selectedBookIsbn"></p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="clearBookSelection">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <label class="form-label">Kopya Seçimi:</label>
                                    <select class="form-select" id="stockSelect" name="stock_id" required>
                                        <option value="">Kopya seçiniz...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Ödünç Tarihi -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ödünç Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="borrow_date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <!-- İade Tarihi -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Son İade Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="due_date" value="{{ date('Y-m-d', strtotime('+15 days')) }}" required>
                    </div>
                </div>

                <!-- Notlar -->
                <div class="mb-3">
                    <label class="form-label">Notlar</label>
                    <textarea class="form-control" name="notes" rows="3"></textarea>
                </div>

                <!-- Otomatik Onay -->
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="auto_approve" id="autoApprove" value="1" checked>
                        <label class="form-check-label" for="autoApprove">
                            Otomatik Onayla
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('staff.borrowings.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Geri Dön
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
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
    
    $('#userSearch').on('keyup', function() {
        clearTimeout(userSearchTimeout);
        const searchTerm = $(this).val().trim();
        
        if (searchTerm.length < 2) {
            $('#userSearchResults').hide();
            return;
        }
        
        userSearchTimeout = setTimeout(function() {
            $.get("{{ route('staff.borrowings.search.user') }}", { search: searchTerm })
                .done(function(users) {
                    if (users.length === 0) {
                        $('#userSearchResults').html('<div class="p-3 text-center">Kullanıcı bulunamadı</div>').show();
                        return;
                    }
                    
                    let html = '<div class="list-group">';
                    users.forEach(function(user) {
                        let statusClass = user.can_borrow ? 'text-success' : 'text-danger';
                        let statusText = '';
                        
                        if (!user.can_borrow) {
                            if (user.overdue_books > 0) {
                                statusText = `${user.overdue_books} gecikmiş kitap`;
                            } else {
                                statusText = `Maksimum kitap limitine ulaşıldı (${user.active_books}/4)`;
                            }
                        } else {
                            statusText = `${user.active_books}/4 kitap`;
                        }
                            
                        html += `
                            <a href="#" class="list-group-item list-group-item-action user-result ${!user.can_borrow ? 'disabled' : ''}"
                               data-user='${JSON.stringify(user)}'>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">${user.name}</h6>
                                        <small class="text-muted d-block">
                                            ${user.email}<br>
                                            ${user.phone ? 'Tel: ' + user.phone : ''}<br>
                                            Üyelik: ${user.member_since}
                                        </small>
                                    </div>
                                    <small class="${statusClass}">${statusText}</small>
                                </div>
                            </a>`;
                    });
                    html += '</div>';
                    
                    $('#userSearchResults').html(html).show();
                })
                .fail(function(error) {
                    console.error('Kullanıcı arama hatası:', error);
                    $('#userSearchResults').html('<div class="p-3 text-center text-danger">Arama sırasında bir hata oluştu</div>').show();
                });
        }, 500);
    });

    // Kullanıcı seçimi
    $(document).on('click', '.user-result', function(e) {
        e.preventDefault();
        
        if ($(this).hasClass('disabled')) {
            return;
        }
        
        const user = JSON.parse($(this).attr('data-user'));
        
        $('#selectedUserId').val(user.id);
        $('#selectedUserName').text(user.name);
        $('#selectedUserEmail').text(user.email);
        $('#selectedUserPhone').text(user.phone ? 'Tel: ' + user.phone : '');
        $('#selectedUserMemberSince').text('Üyelik Tarihi: ' + user.member_since);
        
        // Mevcut kitapları göster
        let currentBooksHtml = '';
        if (user.current_books && user.current_books.length > 0) {
            currentBooksHtml = '<div class="mt-2"><strong>Mevcut Kitapları:</strong><ul class="list-unstyled mb-0">';
            user.current_books.forEach(function(book) {
                let bookClass = book.is_overdue ? 'text-danger' : 'text-muted';
                currentBooksHtml += `
                    <li class="${bookClass}">
                        <small>
                            ${book.title}<br>
                            <span class="text-muted">Teslim: ${book.due_date}</span>
                            ${book.is_overdue ? ' <span class="badge bg-danger">Gecikmiş</span>' : ''}
                        </small>
                    </li>`;
            });
            currentBooksHtml += '</ul></div>';
        }
        $('#selectedUserCurrentBooks').html(currentBooksHtml);
        
        $('#selectedUserDetails').show();
        $('#userSearch').val('');
        $('#userSearchResults').hide();
        
        checkFormValidity();
    });

    // Kullanıcı seçimini temizle
    $('#clearUserSelection').click(function() {
        $('#selectedUserId').val('');
        $('#selectedUserDetails').hide();
        $('#userSearch').val('').focus();
        checkFormValidity();
    });

    // Kitap arama
    let bookSearchTimeout;
    
    $('#bookSearch').on('keyup', function() {
        clearTimeout(bookSearchTimeout);
        const searchTerm = $(this).val().trim();
        
        if (searchTerm.length < 2) {
            $('#bookSearchResults').hide();
            return;
        }
        
        bookSearchTimeout = setTimeout(function() {
            $.get("{{ route('staff.borrowings.search.book') }}", { search: searchTerm })
                .done(function(books) {
                    if (!books || books.length === 0) {
                        $('#bookSearchResults').html('<div class="p-3 text-center">Kitap bulunamadı</div>').show();
                        return;
                    }
                    
                    let html = '<div class="list-group">';
                    books.forEach(function(book) {
                        html += `
                            <a href="#" class="list-group-item list-group-item-action book-result" 
                               data-book='${JSON.stringify(book)}'>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">${book.title}</h6>
                                        <small class="text-muted">
                                            ISBN: ${book.isbn || 'Belirtilmemiş'}<br>
                                            Yazar: ${book.author}<br>
                                            Yayınevi: ${book.publisher}
                                        </small>
                                    </div>
                                    <small class="text-success">${book.available_count} kopya mevcut</small>
                                </div>
                            </a>`;
                    });
                    html += '</div>';
                    
                    $('#bookSearchResults').html(html).show();
                })
                .fail(function(error) {
                    console.error('Kitap arama hatası:', error);
                    $('#bookSearchResults').html('<div class="p-3 text-center text-danger">Arama sırasında bir hata oluştu</div>').show();
                });
        }, 500);
    });

    // Kitap seçimi
    $(document).on('click', '.book-result', function(e) {
        e.preventDefault();
        const book = JSON.parse($(this).attr('data-book'));
        
        $('#selectedBookId').val(book.id);
        $('#selectedBookTitle').text(book.title);
        $('#selectedBookAuthor').text('Yazar: ' + book.author);
        $('#selectedBookPublisher').text('Yayınevi: ' + book.publisher);
        $('#selectedBookIsbn').text('ISBN: ' + (book.isbn || 'Belirtilmemiş'));

        // Stok seçeneklerini güncelle
        const $stockSelect = $('#stockSelect');
        $stockSelect.empty().append('<option value="">Kopya seçiniz...</option>');
        
        if (book.stocks && book.stocks.length > 0) {
            book.stocks.forEach(function(stock) {
                $stockSelect.append(`
                    <option value="${stock.id}">
                        ${stock.barcode ? 'Barkod: ' + stock.barcode + ' | ' : ''}
                        Durum: ${stock.condition || 'Belirtilmemiş'} | 
                        Raf: ${stock.shelf}
                    </option>
                `);
            });
        }

        $('#selectedBookDetails').show();
        $('#bookSearchResults').hide();
        $('#bookSearch').val('');
        checkFormValidity();
    });

    // Kitap seçimini temizle
    $('#clearBookSelection').click(function() {
        $('#selectedBookId').val('');
        $('#stockSelect').val('');
        $('#selectedBookDetails').hide();
        $('#bookSearch').val('').focus();
        checkFormValidity();
    });

    // Dışarı tıklandığında sonuçları gizle
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.user-search-container').length) {
            $('#userSearchResults').hide();
        }
        if (!$(e.target).closest('.book-search-container').length) {
            $('#bookSearchResults').hide();
        }
    });

    // Form doğrulama
    function checkFormValidity() {
        const isUserSelected = $('#selectedUserId').val() !== '';
        const isBookSelected = $('#selectedBookId').val() !== '';
        const isStockSelected = $('#stockSelect').val() !== '';
        
        $('#submitBtn').prop('disabled', !(isUserSelected && isBookSelected && isStockSelected));
    }

    // Stok seçimi değiştiğinde form doğrulaması
    $('#stockSelect').on('change', checkFormValidity);

    // Form gönderimi
    $('#borrowingForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!confirm('Ödünç verme işlemini onaylıyor musunuz?')) {
            return;
        }

        const $form = $(this);
        const $submitBtn = $('#submitBtn');
        const originalHtml = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Kaydediliyor...</span></div>');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect || "{{ route('staff.borrowings.index') }}";
                } else {
                    alert(response.message || 'Bir hata oluştu');
                    $submitBtn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                alert(response?.message || 'İşlem sırasında bir hata oluştu');
                $submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });
});
</script> 