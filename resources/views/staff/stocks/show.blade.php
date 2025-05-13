@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Stok Detayları</h6>
                    <div>
                        <a href="{{ route('staff.stocks.edit', $stock) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-edit"></i> Düzenle
                        </a>
                        <form action="{{ route('staff.stocks.destroy', $stock) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bu stok kaydını silmek istediğinizden emin misiniz?')">
                                <i class="fas fa-trash"></i> Sil
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">Kitap</th>
                                    <td>{{ $stock->book->title }}</td>
                                </tr>
                                <tr>
                                    <th>ISBN</th>
                                    <td>{{ $stock->isbn }}</td>
                                </tr>
                                <tr>
                                    <th>Miktar</th>
                                    <td>{{ $stock->quantity }}</td>
                                </tr>
                                <tr>
                                    <th>Durum</th>
                                    <td>
                                        @switch($stock->condition)
                                            @case('new')
                                                <span class="badge bg-success">Yeni</span>
                                                @break
                                            @case('good')
                                                <span class="badge bg-info">İyi</span>
                                                @break
                                            @case('fair')
                                                <span class="badge bg-warning">Orta</span>
                                                @break
                                            @case('poor')
                                                <span class="badge bg-danger">Kötü</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">Belirsiz</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>Konum</th>
                                    <td>{{ $stock->location }}</td>
                                </tr>
                                <tr>
                                    <th>Notlar</th>
                                    <td>{{ $stock->notes ?? 'Not bulunmuyor' }}</td>
                                </tr>
                                <tr>
                                    <th>Oluşturulma Tarihi</th>
                                    <td>{{ $stock->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Son Güncelleme</th>
                                    <td>{{ $stock->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 