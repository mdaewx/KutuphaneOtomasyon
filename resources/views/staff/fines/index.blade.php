@extends('layouts.staff')

@section('title', 'Ceza Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ceza Yönetimi</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ceza Listesi</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>Kullanıcı</th>
                            <th>Kitap</th>
                            <th>Gecikme</th>
                            <th>Tutar</th>
                            <th>Ödeme Durumu</th>
                            <th>Ödeme Yöntemi</th>
                            <th>Referans No</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fines as $fine)
                            <tr>
                                <td>
                                    {{ $fine->borrowing->user->name }}
                                    <small class="d-block text-muted">{{ $fine->borrowing->user->email }}</small>
                                </td>
                                <td>
                                    {{ $fine->borrowing->book->title }}
                                    <small class="d-block text-muted">
                                        Teslim: {{ $fine->borrowing->returned_at->format('d.m.Y') }}
                                    </small>
                                </td>
                                <td>{{ $fine->borrowing->returned_at->diffInDays($fine->borrowing->due_date) }} gün</td>
                                <td>{{ number_format($fine->amount, 2) }} TL</td>
                                <td>
                                    @if($fine->payment_status === 'paid')
                                        <span class="badge badge-success">Ödendi</span>
                                        <small class="d-block">
                                            {{ $fine->paid_at->format('d.m.Y H:i') }}
                                        </small>
                                    @elseif($fine->payment_status === 'pending')
                                        <span class="badge badge-warning">Beklemede</span>
                                    @else
                                        <span class="badge badge-secondary">İptal Edildi</span>
                                    @endif
                                </td>
                                <td>
                                    @if($fine->payment_method === 'cash')
                                        Nakit
                                    @elseif($fine->payment_method === 'bank_transfer')
                                        Banka Havalesi
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $fine->payment_reference ?: '-' }}</td>
                                <td>
                                    @if($fine->payment_status === 'pending')
                                        <button type="button" 
                                                class="btn btn-primary btn-sm" 
                                                data-toggle="modal" 
                                                data-target="#approveModal{{ $fine->id }}">
                                            <i class="fas fa-check"></i> Onayla
                                        </button>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm"
                                                data-toggle="modal" 
                                                data-target="#cancelModal{{ $fine->id }}">
                                            <i class="fas fa-times"></i> İptal
                                        </button>

                                        <!-- Onaylama Modalı -->
                                        <div class="modal fade" id="approveModal{{ $fine->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('staff.fines.approve', $fine->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Ceza Ödemesi Onaylama</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="payment_method">Ödeme Yöntemi</label>
                                                                <select class="form-control" name="payment_method" required>
                                                                    <option value="">Seçiniz</option>
                                                                    <option value="cash">Nakit</option>
                                                                    <option value="bank_transfer">Banka Havalesi</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="payment_reference">Ödeme Referansı</label>
                                                                <input type="text" 
                                                                       class="form-control" 
                                                                       name="payment_reference"
                                                                       placeholder="Dekont no veya makbuz no"
                                                                       required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="admin_notes">Notlar</label>
                                                                <textarea class="form-control" 
                                                                          name="admin_notes" 
                                                                          rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                                                            <button type="submit" class="btn btn-primary">Onayla</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- İptal Modalı -->
                                        <div class="modal fade" id="cancelModal{{ $fine->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('staff.fines.cancel', $fine->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Ceza İptali</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="admin_notes">İptal Nedeni</label>
                                                                <textarea class="form-control" 
                                                                          name="admin_notes" 
                                                                          rows="3"
                                                                          required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
                                                            <button type="submit" class="btn btn-danger">İptal Et</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
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
    $('#dataTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json'
        }
    });
});
</script>
@endsection 