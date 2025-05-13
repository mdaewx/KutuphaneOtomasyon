@extends('admin.layouts.app')

@section('title', 'Raf Düzenleme')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Raf Düzenleme</h1>
                <a href="{{ route('admin.shelves.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni Raf Ekle
                </a>
            </div>

            <div class="row">
                <!-- Raflar Listesi -->
                <div class="col-md-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Raflar</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group" id="shelfList">
                                @foreach($shelves as $shelf)
                                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center shelf-item" 
                                       data-shelf-id="{{ $shelf->id }}">
                                        {{ $shelf->name }}
                                        <span class="badge badge-primary badge-pill">
                                            {{ $shelf->stocks_count }}/{{ $shelf->capacity }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kitap Listesi -->
                <div class="col-md-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Kitap Reftarı</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="booksTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ISBN</th>
                                            <th>Kitap Adı</th>
                                            <th>Yazar(lar)</th>
                                            <th>Kategori</th>
                                            <th>Yayınevi</th>
                                            <th>Raf Adı</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($books as $book)
                                            <tr>
                                                <td>{{ $book->isbn }}</td>
                                                <td>{{ $book->title }}</td>
                                                <td>{{ $book->authors->pluck('name')->join(', ') }}</td>
                                                <td>{{ $book->category ? $book->category->name : 'Belirtilmemiş' }}</td>
                                                <td>{{ $book->publisher ? $book->publisher->name : 'Belirtilmemiş' }}</td>
                                                <td>
                                                    <select class="form-control shelf-select" data-book-id="{{ $book->id }}">
                                                        <option value="">Raf Seçin</option>
                                                        @foreach($shelves as $shelf)
                                                            <option value="{{ $shelf->id }}" 
                                                                @if($book->stocks->isNotEmpty() && $book->stocks->first()->shelf_id == $shelf->id) selected @endif
                                                                {{ $shelf->stocks_count >= $shelf->capacity ? 'disabled' : '' }}>
                                                                {{ $shelf->name }} 
                                                                ({{ $shelf->stocks_count }}/{{ $shelf->capacity }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm assign-shelf" data-book-id="{{ $book->id }}">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // DataTables initialization
    var table = $('#booksTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json'
        },
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            {
                targets: [5, 6],
                orderable: false
            }
        ]
    });

    // Raf seçimi değiştiğinde
    $('.shelf-select').change(function() {
        var bookId = $(this).data('book-id');
        var shelfId = $(this).val();
        var button = $(this).closest('tr').find('.assign-shelf');
        
        if (shelfId) {
            button.prop('disabled', false);
        } else {
            button.prop('disabled', true);
        }
    });

    // Raf atama butonu tıklandığında
    $('.assign-shelf').click(function() {
        var button = $(this);
        var bookId = button.data('book-id');
        var shelfId = button.closest('tr').find('.shelf-select').val();
        
        if (!shelfId) {
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Lütfen bir raf seçin.'
            });
            return;
        }

        $.ajax({
            url: '/admin/shelf-management/assign',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                book_id: bookId,
                shelf_id: shelfId
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Kitap rafa başarıyla atandı.'
                }).then(function() {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: xhr.responseJSON.message || 'Bir hata oluştu.'
                });
            }
        });
    });

    // Raf listesi tıklamaları
    $('.shelf-item').click(function(e) {
        e.preventDefault();
        var shelfId = $(this).data('shelf-id');
        
        // Seçili rafı vurgula
        $('.shelf-item').removeClass('active');
        $(this).addClass('active');
        
        // Tabloyu filtrele
        table.column(5).search($(this).text().trim()).draw();
    });
});
</script>
@endsection 