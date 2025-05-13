@extends('layouts.admin')

@section('title', 'Sistem Ayarları')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Sistem Ayarları</h1>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Genel Ayarlar</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group row mb-3">
                            <label for="site_title" class="col-sm-3 col-form-label">Site Başlığı</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="site_title" name="site_title" value="{{ $settings['site_title'] ?? 'Kütüphane Otomasyonu' }}">
                                @error('site_title')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="welcome_message" class="col-sm-3 col-form-label">Karşılama Mesajı</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="welcome_message" name="welcome_message" rows="3">{{ $settings['welcome_message'] ?? 'Kütüphane Otomasyonu Sistemine Hoş Geldiniz' }}</textarea>
                                @error('welcome_message')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="max_books_per_user" class="col-sm-3 col-form-label">Kullanıcı Başına Maksimum Kitap</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="max_books_per_user" name="max_books_per_user" min="1" max="20" value="{{ $settings['max_books_per_user'] ?? 5 }}">
                                @error('max_books_per_user')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="loan_period_days" class="col-sm-3 col-form-label">Ödünç Verme Süresi (Gün)</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="loan_period_days" name="loan_period_days" min="1" max="60" value="{{ $settings['loan_period_days'] ?? 14 }}">
                                @error('loan_period_days')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="overdue_fine_per_day" class="col-sm-3 col-form-label">Gecikme Cezası (₺/Gün)</label>
                            <div class="col-sm-9">
                                <input type="number" step="0.01" class="form-control" id="overdue_fine_per_day" name="overdue_fine_per_day" min="0" max="10" value="{{ $settings['overdue_fine_per_day'] ?? 1.00 }}">
                                @error('overdue_fine_per_day')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-sm-3 col-form-label">E-posta Bildirimleri</label>
                            <div class="col-sm-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" {{ ($settings['email_notifications'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_notifications">
                                        E-posta bildirimlerini etkinleştir
                                    </label>
                                </div>
                                @error('email_notifications')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-sm-3 col-form-label">Bakım Modu</label>
                            <div class="col-sm-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="maintenance_mode">
                                        Bakım modunu etkinleştir
                                    </label>
                                </div>
                                @error('maintenance_mode')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Ayarları Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sistem İşlemleri</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>Önbellek Yönetimi</h5>
                        <p class="text-muted small">Sistem performansını artırmak için önbelleğe alınan verileri temizleyebilirsiniz.</p>
                        <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-broom me-1"></i> Önbelleği Temizle
                            </button>
                        </form>
                    </div>

                    <div class="mb-3">
                        <h5>Sistem Bilgileri</h5>
                        <table class="table table-sm">
                            <tr>
                                <td>PHP Versiyonu</td>
                                <td><span class="badge bg-info">{{ phpversion() }}</span></td>
                            </tr>
                            <tr>
                                <td>Laravel Versiyonu</td>
                                <td><span class="badge bg-info">{{ app()->version() }}</span></td>
                            </tr>
                            <tr>
                                <td>Ortam</td>
                                <td><span class="badge bg-{{ app()->environment('production') ? 'success' : 'warning' }}">{{ app()->environment() }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 