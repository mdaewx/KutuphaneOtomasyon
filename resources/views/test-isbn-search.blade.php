<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ISBN Arama Testi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
        }
        .response-area {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .book-cover {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">ISBN Arama Testi</h1>
                <div class="alert alert-info">
                    <p>Bu sayfada, kitap arama özelliğinin test edilebilir. Kullanılabilecek örnek ISBN numaraları:</p>
                    <ul>
                        <li>9789758727049</li>
                        <li>9789750506260</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Test Araması</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="isbnInput" class="form-label">ISBN Numarası</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="isbnInput" placeholder="ISBN girin">
                                <button class="btn btn-primary" id="searchBtn">Ara</button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Test Seçenekleri</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="debugModeCheck">
                                <label class="form-check-label" for="debugModeCheck">Debug Modu (Detaylı Bilgi)</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button class="btn btn-outline-secondary" id="checkRouteBtn">Route Kontrolü</button>
                            <button class="btn btn-outline-secondary ms-2" id="checkCSRFBtn">CSRF Kontrolü</button>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Durum Bilgisi</h5>
                    </div>
                    <div class="card-body">
                        <div id="statusArea" class="alert alert-light">
                            Test yapmak için yukarıdaki arama formunu kullanın.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Sonuçlar</h5>
                        <button class="btn btn-sm btn-outline-secondary" id="clearBtn">Temizle</button>
                    </div>
                    <div class="card-body">
                        <div id="responseArea" class="response-area">
                            <p class="text-muted">Sonuç bilgileri burada görüntülenecek...</p>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3" id="bookDetails" style="display:none;">
                    <div class="card-header">
                        <h5>Kitap Detayları</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <img id="bookCover" src="" alt="Kitap Kapağı" class="book-cover mb-3">
                            </div>
                            <div class="col-md-8">
                                <h4 id="bookTitle"></h4>
                                <p><strong>Yazarlar:</strong> <span id="bookAuthors"></span></p>
                                <p><strong>Yayınevi:</strong> <span id="bookPublisher"></span></p>
                                <p><strong>Yayın Yılı:</strong> <span id="bookYear"></span></p>
                                <p id="bookDescription"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // ISBN arama işlemi
        $('#searchBtn').click(function() {
            var isbn = $('#isbnInput').val().trim();
            var debugMode = $('#debugModeCheck').is(':checked');
            
            if (!isbn) {
                updateStatus('warning', 'Lütfen bir ISBN numarası girin.');
                return;
            }
            
            updateStatus('info', 'Arama yapılıyor...');
            
            // CSRF token ayarla
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // AJAX isteği
            $.ajax({
                url: '{{ route('books.search-by-isbn') }}',
                method: 'GET',
                data: { isbn: isbn, debug: debugMode ? 1 : 0 },
                dataType: 'json',
                success: function(data) {
                    console.log("Arama başarılı:", data);
                    
                    // Sonuç göster
                    $('#responseArea').html('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
                    
                    if (data.success) {
                        updateStatus('success', 'Kitap bulundu!');
                        displayBookDetails(data.book);
                    } else {
                        updateStatus('warning', 'Kitap bulunamadı.');
                        $('#bookDetails').hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Arama hatası:", error);
                    console.error("Hata durumu:", status);
                    console.error("Sunucu yanıtı:", xhr.responseText);
                    
                    updateStatus('danger', 'Arama hatası: ' + error);
                    
                    // Hata detaylarını göster
                    $('#responseArea').html(
                        '<div class="alert alert-danger mb-3">Hata: ' + error + '</div>' +
                        '<h6>Durum: ' + status + '</h6>' +
                        '<h6>Yanıt:</h6>' +
                        '<pre>' + xhr.responseText + '</pre>'
                    );
                    
                    $('#bookDetails').hide();
                }
            });
        });
        
        // Enter tuşu ile arama
        $('#isbnInput').keypress(function(e) {
            if (e.which == 13) {
                e.preventDefault();
                $('#searchBtn').click();
            }
        });
        
        // Route kontrolü
        $('#checkRouteBtn').click(function() {
            updateStatus('info', 'Route kontrolü yapılıyor...');
            
            $('#responseArea').html(
                '<div class="alert alert-info">Route bilgisi: <code>{{ route('books.search-by-isbn') }}</code></div>' +
                '<p>Test için basit bir GET isteği gönderilecek...</p>'
            );
            
            $.ajax({
                url: '{{ route('books.search-by-isbn') }}',
                method: 'GET',
                data: { test: 1 },
                dataType: 'json',
                success: function(data) {
                    $('#responseArea').append(
                        '<div class="alert alert-success mt-3">Route erişilebilir!</div>' +
                        '<pre>' + JSON.stringify(data, null, 2) + '</pre>'
                    );
                    updateStatus('success', 'Route erişilebilir.');
                },
                error: function(xhr, status, error) {
                    $('#responseArea').append(
                        '<div class="alert alert-danger mt-3">Route hatası: ' + error + '</div>' +
                        '<pre>' + xhr.responseText + '</pre>'
                    );
                    updateStatus('danger', 'Route hatası: ' + error);
                }
            });
        });
        
        // CSRF kontrolü
        $('#checkCSRFBtn').click(function() {
            updateStatus('info', 'CSRF token kontrolü yapılıyor...');
            
            var token = $('meta[name="csrf-token"]').attr('content');
            
            $('#responseArea').html(
                '<div class="alert alert-info">CSRF Token: <code>' + token + '</code></div>'
            );
            
            if (token) {
                $('#responseArea').append('<div class="alert alert-success mt-3">CSRF token mevcut.</div>');
                updateStatus('success', 'CSRF token mevcut.');
            } else {
                $('#responseArea').append('<div class="alert alert-danger mt-3">CSRF token eksik!</div>');
                updateStatus('danger', 'CSRF token eksik!');
            }
        });
        
        // Temizle butonuna tıklama
        $('#clearBtn').click(function() {
            $('#responseArea').html('<p class="text-muted">Sonuç bilgileri burada görüntülenecek...</p>');
            $('#bookDetails').hide();
            updateStatus('light', 'Sonuçlar temizlendi.');
        });
        
        // Kitap detaylarını gösterme
        function displayBookDetails(book) {
            $('#bookTitle').text(book.title);
            $('#bookAuthors').text(book.authors ? book.authors.join(', ') : 'Belirtilmemiş');
            $('#bookPublisher').text(book.publisher || 'Belirtilmemiş');
            $('#bookYear').text(book.publication_year || 'Belirtilmemiş');
            $('#bookDescription').text(book.description || 'Açıklama yok');
            
            if (book.cover_image) {
                $('#bookCover').attr('src', book.cover_image).show();
            } else {
                $('#bookCover').hide();
            }
            
            $('#bookDetails').show();
        }
        
        // Durum güncelleme
        function updateStatus(type, message) {
            $('#statusArea').removeClass('alert-success alert-warning alert-danger alert-info alert-light')
                .addClass('alert-' + type)
                .html(message);
        }
    });
    </script>
</body>
</html> 