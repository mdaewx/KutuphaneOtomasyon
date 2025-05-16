@extends('layouts.admin')

@section('title', 'Kitap Yönetimi')

@section('content')
<style>
/* Kitaplar sayfası için özel stil - Yayınevi alanları için siyah renk */
#publisher, #edit_publisher, input[name="publisher"] {
    color: #000 !important;
    background-color: #fff !important;
}

/* Yayınevi badge styling - Improved contrast */
.yayinevi-badge, [class*="yayinevi"], td:nth-child(5) span, td:nth-child(5) a, 
td:nth-child(5) div, td:nth-child(5), th:nth-child(5),
[class*="can-yayinlari"], .can-yayinlari {
    color: #000 !important;
    background-color: #f0f0f0 !important; /* Lighter gray background */
    border: 1px solid #ddd;
    font-weight: bold !important;
}

/* Publisher color fix - darker background and white text for badges */
.badge.badge-info, 
.badge.badge-primary, 
.badge.badge-secondary,
.badge.badge-warning,
.badge.badge-success,
.badge.badge-danger {
    color: #fff !important;
    font-weight: bold !important;
    border: none !important;
}

/* Fix for Roman-type badges */
.badge.roman, span.roman {
    background-color: #3a86ff !important; /* Bright blue */
    color: #fff !important;
    font-weight: bold !important;
}

/* Fix for Can Yayınları badges */
.badge.can-yayinlari, span.can-yayinlari {
    background-color: #264653 !important; /* Dark teal */
    color: #fff !important;
    font-weight: bold !important;
}

/* Force publisher fields to show in black */
#publisher::placeholder, #edit_publisher::placeholder {
    color: #666 !important;
}

/* Table text general fix */
table.table-bordered td, 
table.table-bordered th {
    color: #000 !important;
}

/* Force all yayinevi elements to black text */
td:nth-child(5) *, tr td:nth-child(5) *, .yayinevi * {
    color: #000 !important;
}

.modal .form-control, table {
    color: #000 !important;
}
</style>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Kitap Yönetimi</h1>
    <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#addBookModal">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Yeni Kitap Ekle
    </button>
</div>

<!-- Search Card -->
<div class="card shadow mb-4">
    <a href="#collapseSearchCard" class="card-header py-3 d-flex flex-row align-items-center justify-content-between" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseSearchCard">
        <h6 class="m-0 font-weight-bold text-primary">Kitap Ara</h6>
        <div class="dropdown no-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </a>
    <div class="collapse show" id="collapseSearchCard">
        <div class="card-body">
            <form id="searchForm" action="{{ route('admin.books.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" name="search" placeholder="Başlık, Yazar veya ISBN..." value="{{ request()->search }}">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                            </div>
                            <select class="form-control" name="category">
                                <option value="">Tüm Kategoriler</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request()->category == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            </div>
                            <select class="form-control" name="status">
                                <option value="">Tüm Durumlar</option>
                                <option value="available" {{ request()->status == 'available' ? 'selected' : '' }}>Mevcut</option>
                                <option value="borrowed" {{ request()->status == 'borrowed' ? 'selected' : '' }}>Ödünç Verilmiş</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search mr-1"></i> Ara
                        </button>
                        <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt mr-1"></i> Sıfırla
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables Books -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Kitap Listesi</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="booksTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="50px">ID</th>
                        <th width="60px">Kapak</th>
                        <th>Başlık</th>
                        <th>Yazar</th>
                        <th>ISBN</th>
                        <th>Kategori</th>
                        <th>Raf</th>
                        <th width="70px">Adet</th>
                        <th width="80px">Durum</th>
                        <th width="120px">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $book)
                    <tr>
                        <td>{{ $book->id }}</td>
                        <td class="text-center">
                            <img src="{{ asset('images/icons/book-logo.png') }}" 
                                 alt="{{ $book->title }}" class="img-thumbnail" style="width: 50px; height: 70px; object-fit: contain;">
                        </td>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author }}</td>
                        <td>{{ $book->isbn }}</td>
                        <td>{{ $book->category }}</td>
                        <td>{{ $book->shelf_location }}</td>
                        <td class="text-center">{{ $book->quantity }}</td>
                        <td class="text-center">
                            @if($book->quantity > 0)
                                <span class="badge badge-success">Mevcut</span>
                            @else
                                <span class="badge badge-danger">Tükendi</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info edit-book" 
                                        data-id="{{ $book->id }}" 
                                        data-toggle="modal" 
                                        data-target="#editBookModal"
                                        title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-book" 
                                        data-id="{{ $book->id }}" 
                                        data-title="{{ $book->title }}"
                                        data-toggle="modal" 
                                        data-target="#deleteBookModal"
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
                {{ $books->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1" role="dialog" aria-labelledby="addBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBookModalLabel">Yeni Kitap Ekle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Kitap Başlığı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="author">Yazar <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="author" name="author" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="isbn">ISBN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="isbn" name="isbn" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="publisher">Yayınevi</label>
                                <input type="text" class="form-control" id="publisher" name="publisher">
                            </div>
                            
                            <div class="form-group">
                                <label for="publish_year">Basım Yılı</label>
                                <input type="number" class="form-control" id="publish_year" name="publish_year">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Kategori <span class="text-danger">*</span></label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Kategori Seçin</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="shelf_location">Raf Konumu</label>
                                <input type="text" class="form-control" id="shelf_location" name="shelf_location" placeholder="Örn: A-12">
                            </div>
                            
                            <div class="form-group">
                                <label for="quantity">Adet <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="page_count">Sayfa Sayısı</label>
                                <input type="number" class="form-control" id="page_count" name="page_count" min="1">
                            </div>
                            
                            <div class="form-group">
                                <label>Kitap Görseli</label>
                                <div class="text-center">
                                    <img src="{{ asset('images/icons/book-logo.png') }}" 
                                         alt="Kitap Logo" class="img-fluid mb-2" style="width: 100px; height: auto;">
                                    <p class="text-muted small">Tüm kitaplar için standart logo kullanılmaktadır.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-3">
                            <div class="form-group">
                                <label for="description">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Book Modal -->
<div class="modal fade" id="editBookModal" tabindex="-1" role="dialog" aria-labelledby="editBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBookModalLabel">Kitap Düzenle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editBookForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_title">Kitap Başlığı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_author">Yazar <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_author" name="author" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_isbn">ISBN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_isbn" name="isbn" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_publisher">Yayınevi</label>
                                <input type="text" class="form-control" id="edit_publisher" name="publisher">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_publish_year">Basım Yılı</label>
                                <input type="number" class="form-control" id="edit_publish_year" name="publish_year">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_category_id">Kategori <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_category_id" name="category_id" required>
                                    <option value="">Kategori Seçin</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_shelf_location">Raf Konumu</label>
                                <input type="text" class="form-control" id="edit_shelf_location" name="shelf_location" placeholder="Örn: A-12">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_quantity">Adet <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_page_count">Sayfa Sayısı</label>
                                <input type="number" class="form-control" id="edit_page_count" name="page_count" min="1">
                            </div>
                            
                            <div class="form-group">
                                <label>Kitap Görseli</label>
                                <div class="text-center">
                                    <img src="{{ asset('images/icons/book-logo.png') }}" 
                                         alt="Kitap Logo" class="img-fluid mb-2" style="width: 100px; height: auto;">
                                    <p class="text-muted small">Tüm kitaplar için standart logo kullanılmaktadır.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-3">
                            <div class="form-group">
                                <label for="edit_description">Açıklama</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Cover Modal - Disabled -->
<div class="modal fade" id="uploadCoverModal" tabindex="-1" role="dialog" aria-labelledby="uploadCoverModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadCoverModalLabel">Kapak Görseli Yükle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <p><i class="fas fa-info-circle"></i> Kitap kapak görseli yükleme özelliği devre dışı bırakılmıştır.</p>
                    <p>Tüm kitaplar için standart logo kullanılmaktadır.</p>
                </div>
                <div class="text-center">
                    <img src="{{ asset('images/icons/book-logo.png') }}" alt="Kitap Logo" class="img-fluid" style="width: 150px; height: auto;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Book Modal -->
<div class="modal fade" id="deleteBookModal" tabindex="-1" role="dialog" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBookModalLabel">Kitap Sil</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Bu kitabı silmek istediğinize emin misiniz?</p>
                <p class="text-danger font-weight-bold" id="delete_book_title"></p>
                <p>Bu işlem geri alınamaz ve kitapla ilgili tüm ödünç kayıtları da silinecektir.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                <form id="deleteBookForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // DataTable Initialization
        $('#booksTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Turkish.json"
            },
            "pageLength": 10,
            "ordering": true,
            "paging": false,
            "info": false,
            "searching": false
        });
        
        // ISBN Formatting
        $('#isbn, #edit_isbn').on('input', function() {
            var isbn = $(this).val().replace(/[^0-9]/g, '');
            if (isbn.length > 13) {
                isbn = isbn.substring(0, 13);
            }
            
            var formattedIsbn = '';
            if (isbn.length <= 13) {
                // ISBN-13 format: 978-3-16-148410-0
                if (isbn.length > 3) {
                    formattedIsbn += isbn.substring(0, 3) + '-';
                    if (isbn.length > 4) {
                        formattedIsbn += isbn.substring(3, 4) + '-';
                        if (isbn.length > 6) {
                            formattedIsbn += isbn.substring(4, 6) + '-';
                            if (isbn.length > 12) {
                                formattedIsbn += isbn.substring(6, 12) + '-';
                                formattedIsbn += isbn.substring(12, 13);
                            } else {
                                formattedIsbn += isbn.substring(6);
                            }
                        } else {
                            formattedIsbn += isbn.substring(4);
                        }
                    } else {
                        formattedIsbn += isbn.substring(3);
                    }
                } else {
                    formattedIsbn = isbn;
                }
            }
            
            $(this).val(formattedIsbn);
        });
        
        // Set up custom file input 
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
            
            // Preview image
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    if ($(this).attr('id') === 'cover_image_upload') {
                        $('#cover_preview').attr('src', e.target.result);
                    }
                }.bind(this);
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Edit Book Modal
        $('.edit-book').click(function() {
            var bookId = $(this).data('id');
            
            // Get book details via AJAX
            $.get('/admin/books/' + bookId + '/edit', function(data) {
                $('#editBookForm').attr('action', '/admin/books/' + bookId);
                
                // Fill form fields
                $('#edit_title').val(data.book.title);
                $('#edit_author').val(data.book.author);
                $('#edit_isbn').val(data.book.isbn);
                $('#edit_publisher').val(data.book.publisher);
                $('#edit_publish_year').val(data.book.publish_year);
                $('#edit_category_id').val(data.book.category_id);
                $('#edit_shelf_location').val(data.book.shelf_location);
                $('#edit_quantity').val(data.book.quantity);
                $('#edit_page_count').val(data.book.page_count);
                $('#edit_description').val(data.book.description);
                
                // Set cover image preview
                if (data.book.cover_image) {
                    $('#edit_cover_preview').attr('src', '/storage/covers/' + data.book.cover_image);
                } else {
                    $('#edit_cover_preview').attr('src', '/img/no-cover.png');
                }
                
                // Also set the same book ID for the upload cover form
                $('#uploadCoverForm').attr('action', '/admin/books/' + bookId + '/upload-cover');
            });
        });
        
        // Upload Cover Modal
        $('.upload-cover').click(function() {
            var bookId = $(this).data('id');
            var bookTitle = $(this).data('title');
            
            $('#uploadCoverForm').attr('action', '/admin/books/' + bookId + '/upload-cover');
            $('#uploadCoverModalLabel').text('Kapak Görseli Yükle: ' + bookTitle);
            
            // Get current cover image
            $.get('/admin/books/' + bookId + '/edit', function(data) {
                if (data.book.cover_image) {
                    $('#cover_preview').attr('src', '/storage/covers/' + data.book.cover_image);
                } else {
                    $('#cover_preview').attr('src', '/img/no-cover.png');
                }
            });
        });
        
        // Delete Book Modal
        $('.delete-book').click(function() {
            var bookId = $(this).data('id');
            var bookTitle = $(this).data('title');
            
            $('#deleteBookForm').attr('action', '/admin/books/' + bookId);
            $('#delete_book_title').text(bookTitle);
        });
    });
</script>
@endsection 