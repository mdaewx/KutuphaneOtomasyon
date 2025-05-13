<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunucu Hatası</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .error-container {
            max-width: 600px;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .error-code {
            font-size: 72px;
            font-weight: bold;
            color: #dc3545;
        }
        .error-details {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container error-container">
        <div class="text-center mb-4">
            <div class="error-code">500</div>
            <h1 class="h4 mb-3">Sunucu Hatası</h1>
            <p class="lead">Maalesef bir hata oluştu ve isteğiniz tamamlanamadı.</p>
        </div>
        
        <div class="d-flex justify-content-center mb-3">
            <a href="{{ url('/') }}" class="btn btn-primary me-2">Ana Sayfaya Dön</a>
            @auth
                <a href="{{ url('/profile') }}" class="btn btn-outline-secondary">Profilinize Gidin</a>
            @endauth
        </div>
        
        @if(app()->environment('local') && isset($exception))
        <div class="error-details">
            <h5>Hata Detayları:</h5>
            <p>{{ $exception->getMessage() }}</p>
            <p><small>{{ $exception->getFile() }} ({{ $exception->getLine() }})</small></p>
        </div>
        @endif
    </div>
</body>
</html> 