@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Ceza İşlemleri</h1>
    <p class="mb-4">Gecikmiş kitaplar ve uygulanan cezaların yönetimi</p>

    <!-- Günlük Ceza Tutarı Güncelleme -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Günlük Ceza Tutarı Ayarı</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.fines.update-rate') }}" method="POST" class="form-inline">
                @csrf
                <div class="form-group mb-2">
                    <label for="daily_fine_rate" class="sr-only">Günlük Ceza Tutarı</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="daily_fine_rate" name="daily_fine_rate" value="{{ $dailyFineRate }}" required>
                        <div class="input-group-append">
                            <span class="input-group-text">TL</span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mb-2 ml-2">Güncelle</button>
            </form>
        </div>
    </div>

    <!-- Gecikmiş Kitaplar Tablosu -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Gecikmiş Kitaplar</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Kitap</th>
                            <th>Üye</th>
                            <th>Ödünç Tarihi</th>
                            <th>İade Tarihi</th>
                            <th>Gecikme (Gün)</th>
                            <th>Tahmini Ceza</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueBorrowings as $borrowing)
                        <tr>
                            <td>{{ $borrowing->book->title }}</td>
                            <td>{{ $borrowing->user->name }}</td>
                            <td>{{ isset($borrowing->borrowed_at) ? $borrowing->borrowed_at->format('d.m.Y') : (isset($borrowing->borrow_date) ? $borrowing->borrow_date->format('d.m.Y') : $borrowing->created_at->format('d.m.Y')) }}</td>
                            <td class="text-danger">{{ $borrowing->due_date->format('d.m.Y') }}</td>
                            <td class="text-danger font-weight-bold">{{ $borrowing->overdue_days }}</td>
                            <td class="text-danger font-weight-bold">{{ number_format($borrowing->potential_fine, 2) }} TL</td>
                            <td>
                                <form action="{{ route('admin.fines.return-overdue-book', $borrowing->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning">İade Et ve Cezalandır</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Gecikmiş kitap bulunmamaktadır.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cezalar Tablosu -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Cezalar</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="finesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Üye</th>
                            <th>Kitap</th>
                            <th>Gecikme (Gün)</th>
                            <th>Ceza Tutarı</th>
                            <th>Ödeme Durumu</th>
                            <th>Tarih</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fines as $fine)
                        <tr>
                            <td>{{ $fine->user->name }}</td>
                            <td>{{ $fine->book->title }}</td>
                            <td>{{ $fine->days_late }}</td>
                            <td>{{ number_format($fine->fine_amount, 2) }} TL</td>
                            <td>
                                @if($fine->paid)
                                    <span class="badge badge-success">Ödendi</span>
                                @else
                                    <span class="badge badge-danger">Ödenmedi</span>
                                @endif
                            </td>
                            <td>{{ $fine->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.fines.show', $fine->id) }}" class="btn btn-sm btn-info">Detay</a>
                                    
                                    @if(!$fine->paid)
                                    <form action="{{ route('admin.fines.mark-as-paid', $fine->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success ml-1">Ödenmiş İşaretle</button>
                                    </form>
                                    
                                    <form action="{{ route('admin.fines.forgive', $fine->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-warning ml-1">Affet</button>
                                    </form>
                                    @endif
                                    
                                    <form action="{{ route('admin.fines.destroy', $fine->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger ml-1" onclick="return confirm('Bu ceza kaydını silmek istediğinize emin misiniz?')">Sil</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Ceza kaydı bulunmamaktadır.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Manually enable search and pagination, without DataTables warnings
    if ($('#finesTable tbody tr').length > 1) {
        $('#finesTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true
        });
    }
});
</script>
@endsection 