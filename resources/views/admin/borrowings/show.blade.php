@extends('layouts.admin')

@section('title', 'Ödünç Detayları')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Ödünç Detayları</h1>
    <div>
        <a href="{{ route('admin.borrowings.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Ödünçlere Dön
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        @include('admin.borrowings.partials.borrowing_details')
        
        <div class="mt-4 text-center">
            <div class="btn-group">
                <a href="{{ route('admin.borrowings.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
                
                @if(!$borrowing->returned_at && $borrowing->status == 'approved')
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#returnBorrowingModal">
                        <i class="fas fa-undo"></i> İade Et
                    </a>
                @endif
                
                @if($borrowing->status == 'pending')
                    <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveBorrowingModal">
                        <i class="fas fa-check"></i> Onayla
                    </a>
                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectBorrowingModal">
                        <i class="fas fa-times"></i> Reddet
                    </a>
                @endif
                
                <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteBorrowingModal">
                    <i class="fas fa-trash"></i> Sil
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Return Borrowing Modal -->
<div class="modal fade" id="returnBorrowingModal" tabindex="-1" aria-labelledby="returnBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnBorrowingModalLabel">Kitap İadesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.borrowings.return', $borrowing->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-book-reader fa-4x text-primary mb-3"></i>
                        <h5 class="text-gray-900">{{ $borrowing->book->title }}</h5>
                        <p class="text-gray-600">{{ $borrowing->user->name }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="return_date" class="form-label">İade Tarihi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="return_date" name="returned_at" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="return_condition" class="form-label">Kitap Durumu</label>
                        <select class="form-select" id="return_condition" name="condition">
                            <option value="good">İyi Durumda</option>
                            <option value="damaged">Hasarlı</option>
                            <option value="lost">Kayıp</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="return_notes" class="form-label">Notlar</label>
                        <textarea class="form-control" id="return_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">İade Et</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Borrowing Modal -->
<div class="modal fade" id="deleteBorrowingModal" tabindex="-1" aria-labelledby="deleteBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBorrowingModalLabel">Ödünç Kaydını Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bu ödünç kaydını silmek istediğinize emin misiniz?</p>
                <p class="text-danger">Bu işlem geri alınamaz.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form action="{{ route('admin.borrowings.destroy', $borrowing->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($borrowing->status == 'pending')
<!-- Approve Borrowing Modal -->
<div class="modal fade" id="approveBorrowingModal" tabindex="-1" aria-labelledby="approveBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveBorrowingModalLabel">Ödünç Vermeyi Onayla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.borrowings.update-status', $borrowing->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="approved">
                <div class="modal-body">
                    <p>Bu ödünç verme işlemini onaylamak istediğinize emin misiniz?</p>
                    <p>Onaylandığında kitap stoktan düşülecektir.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Borrowing Modal -->
<div class="modal fade" id="rejectBorrowingModal" tabindex="-1" aria-labelledby="rejectBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectBorrowingModalLabel">Ödünç Vermeyi Reddet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.borrowings.update-status', $borrowing->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <div class="modal-body">
                    <p>Bu ödünç verme işlemini reddetmek istediğinize emin misiniz?</p>
                    
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Red Nedeni</label>
                        <textarea class="form-control" id="reject_reason" name="reject_reason" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">Reddet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection 