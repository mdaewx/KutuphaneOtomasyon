<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .success-banner {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .admin-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            background-color: white;
            margin-bottom: 20px;
        }
        h1 {
            margin-bottom: 30px;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="success-banner">
            <h4>Başarı! Admin panel çalışıyor!</h4>
            <p>Bu sayfayı görüyorsanız, admin paneline erişim sorunu çözülmüştür.</p>
        </div>
        
        <h1>Test Admin Dashboard</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="admin-card">
                    <h3>Tebrikler!</h3>
                    <p>Admin controller'ınız başarılı bir şekilde çalışıyor. Şimdi test yerine gerçek AdminController'ı kullanmaya geçebilirsiniz.</p>
                    <p>Bu test sayfası, routing sorununuzu çözmek için hazırlanmıştır.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="admin-card">
                    <h3>Sonraki Adımlar</h3>
                    <p>Admin panelinizi kullanmak için aşağıdaki adımları izleyin:</p>
                    <ol>
                        <li>Bu çalışan örneği model alarak gerçek dashboard'unuzu düzenleyin</li>
                        <li>Bu örnek controller ile ana sayfaya geri dönün</li>
                        <li>Tüm admin işlevlerini test edin</li>
                    </ol>
                    <a href="/" class="btn btn-primary">Ana Sayfaya Dön</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 