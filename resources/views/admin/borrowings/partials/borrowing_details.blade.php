<div class="row">
    <div class="col-md-4 text-center mb-3 mb-md-0">
        <div class="border rounded p-3">
            <img src="{{ asset('images/icons/book-logo.png') }}" 
                alt="{{ $borrowing->book->title }}" class="img-fluid mb-3" style="width: 200px; height: auto;">
            <h5 class="mb-1">{{ $borrowing->book->title }}</h5>
            <p class="text-muted mb-1">{{ $borrowing->book->author }}</p>
            @if($borrowing->book->isbn)
                <small class="d-block mb-2"><strong>ISBN:</strong> {{ $borrowing->book->isbn }}</small>
            @endif
        </div>
    </div>
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Ödünç Bilgileri</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Ödünç Alan:</dt>
                    <dd class="col-sm-8">{{ $borrowing->user->name }} {{ $borrowing->user->surname }}</dd>
                    
                    <dt class="col-sm-4">Ödünç Tarihi:</dt>
                    <dd class="col-sm-8">{{ $borrowing->borrow_date ? $borrowing->borrow_date->format('d.m.Y') : '-' }}</dd>
                    
                    <dt class="col-sm-4">Son Teslim Tarihi:</dt>
                    <dd class="col-sm-8">
                        {{ $borrowing->due_date ? $borrowing->due_date->format('d.m.Y') : '-' }}
                        @if($borrowing->isOverdue() && !$borrowing->returned_at)
                            <span class="badge bg-danger ms-1">{{ now()->diffInDays($borrowing->due_date) }} gün gecikmiş</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-4">İade Tarihi:</dt>
                    <dd class="col-sm-8">
                        @if($borrowing->returned_at)
                            {{ $borrowing->returned_at->format('d.m.Y') }}
                            @if($borrowing->returned_at->isAfter($borrowing->due_date))
                                <span class="badge bg-warning ms-1">{{ $borrowing->returned_at->diffInDays($borrowing->due_date) }} gün geç</span>
                            @else
                                <span class="badge bg-success ms-1">Zamanında iade</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">İade Edilmedi</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-4">Durum:</dt>
                    <dd class="col-sm-8">
                        @if($borrowing->returned_at)
                            <span class="badge bg-success">İade Edildi</span>
                        @elseif($borrowing->status == 'pending')
                            <span class="badge bg-warning">Onay Bekliyor</span>
                        @elseif($borrowing->status == 'approved')
                            @if($borrowing->isOverdue())
                                <span class="badge bg-danger">Gecikmiş</span>
                            @else
                                <span class="badge bg-info">Ödünç Verildi</span>
                            @endif
                        @elseif($borrowing->status == 'rejected')
                            <span class="badge bg-danger">Reddedildi</span>
                        @endif
                    </dd>
                    
                    @if($borrowing->condition)
                        <dt class="col-sm-4">Kitap Durumu:</dt>
                        <dd class="col-sm-8">
                            @if($borrowing->condition == 'good')
                                <span class="badge bg-success">İyi Durumda</span>
                            @elseif($borrowing->condition == 'damaged')
                                <span class="badge bg-warning">Hasarlı</span>
                            @elseif($borrowing->condition == 'lost')
                                <span class="badge bg-danger">Kayıp</span>
                            @endif
                        </dd>
                    @endif
                    
                    @if($borrowing->fine_amount > 0)
                        <dt class="col-sm-4">Ceza Tutarı:</dt>
                        <dd class="col-sm-8">
                            <span class="text-danger fw-bold">{{ number_format($borrowing->fine_amount, 2) }} ₺</span>
                        </dd>
                    @endif
                </dl>
            </div>
        </div>
        
        @if($borrowing->notes || $borrowing->reject_reason)
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Notlar</h6>
                </div>
                <div class="card-body">
                    @if($borrowing->notes)
                        <p class="mb-1"><strong>İşlem Notları:</strong> {{ $borrowing->notes }}</p>
                    @endif
                    
                    @if($borrowing->reject_reason)
                        <p class="mb-0"><strong>Red Nedeni:</strong> {{ $borrowing->reject_reason }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div> 