@extends('layouts.staff')

@section('title', 'Ödünç İşlemleri')

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ödünç İşlemleri</h1>
        <a href="{{ route('staff.borrowings.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Yeni Ödünç Verme
        </a>
    </div>

    <!-- Search Card -->
    <div class="card shadow mb-4">
        <a href="#collapseSearchCard" class="card-header py-3 d-flex flex-row align-items-center justify-content-between" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseSearchCard">
            <h6 class="m-0 font-weight-bold text-primary">Ödünç İşlemi Ara</h6>
            <div class="dropdown no-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
        </a>
        <div class="collapse show" id="collapseSearchCard">
            <div class="card-body">
                <form id="searchForm" action="{{ route('staff.borrowings.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-book"></i></span>
                                </div>
                                <input type="text" class="form-control" name="book" placeholder="Kitap adı..." value="{{ request()->book }}">
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" name="user" placeholder="Kullanıcı adı..." value="{{ request()->user }}">
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-clipboard-check"></i></span>
                                </div>
                                <select class="form-control" name="status">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="pending" {{ request()->status == 'pending' ? 'selected' : '' }}>Beklemede</option>
                                    <option value="approved" {{ request()->status == 'approved' ? 'selected' : '' }}>Onaylandı</option>
                                    <option value="returned" {{ request()->status == 'returned' ? 'selected' : '' }}>İade Edildi</option>
                                    <option value="overdue" {{ request()->status == 'overdue' ? 'selected' : '' }}>Gecikmiş</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="text" class="form-control" id="dateRange" name="date_range" placeholder="Tarih aralığı..." value="{{ request()->date_range }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search mr-1"></i> Ara
                            </button>
                            <a href="{{ route('staff.borrowings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-sync-alt mr-1"></i> Sıfırla
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ödünç İşlemleri Listesi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı</th>
                            <th>Kitap</th>
                            <th>Ödünç Tarihi</th>
                            <th>Son Tarih</th>
                            <th>İade Tarihi</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($borrowings as $borrowing)
                        <tr>
                            <td>{{ $borrowing->id }}</td>
                            <td>
                                <strong>{{ $borrowing->user->name }}</strong><br>
                                <small class="text-muted">{{ $borrowing->user->email }}</small>
                            </td>
                            <td>
                                <strong>{{ $borrowing->book->title }}</strong><br>
                                <small class="text-muted">Yazar: {{ $borrowing->book->author }}</small><br>
                                <small class="text-muted">ISBN: {{ $borrowing->book->isbn }}</small>
                            </td>
                            <td>{{ $borrowing->created_at ? $borrowing->created_at->format('d.m.Y') : '-' }}</td>
                            <td>{{ $borrowing->due_date ? \Carbon\Carbon::parse($borrowing->due_date)->format('d.m.Y') : '-' }}</td>
                            <td>{{ $borrowing->returned_at ? \Carbon\Carbon::parse($borrowing->returned_at)->format('d.m.Y') : '-' }}</td>
                            <td>
                                @if($borrowing->status == 'pending')
                                    <span class="badge bg-warning">Beklemede</span>
                                @elseif($borrowing->status == 'approved' && $borrowing->due_date < now() && !$borrowing->returned_at)
                                    <span class="badge bg-danger">Gecikmiş</span>
                                @elseif($borrowing->status == 'approved' && !$borrowing->returned_at)
                                    <span class="badge bg-success">Onaylandı</span>
                                @elseif($borrowing->status == 'returned' || $borrowing->returned_at)
                                    <span class="badge bg-info">İade Edildi</span>
                                @else
                                    <span class="badge bg-secondary">{{ $borrowing->status }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if(!$borrowing->returned_at)
                                        <a href="{{ route('staff.borrowings.edit', $borrowing->id) }}" class="btn btn-primary btn-sm" title="İade Al">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('staff.borrowings.show', $borrowing->id) }}" class="btn btn-info btn-sm" title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                @if($borrowings instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $borrowings->appends(request()->all())->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {
    // Tarih aralığı seçici
    $('#dateRange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Temizle',
            applyLabel: 'Uygula',
            fromLabel: 'Başlangıç',
            toLabel: 'Bitiş',
            customRangeLabel: 'Özel Aralık',
            weekLabel: 'H',
            daysOfWeek: ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'],
            monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
            firstDay: 1
        }
    });

    $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });

    $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
@endpush 