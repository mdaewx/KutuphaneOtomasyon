<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StaffUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->withCount('borrowings');

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Personel sadece normal üyeleri görebilir
        $query->where('is_admin', 0)->where('is_staff', 0);

        $users = $query->orderBy('id', 'asc')->paginate(15);
        
        // Her kullanıcı için rol bilgisini ekle
        $users->getCollection()->transform(function ($user) {
            $user->role_name = 'Üye';
            return $user;
        });
        
        return view('staff.users.index', compact('users'));
    }

    public function show(User $user)
    {
        // Sadece normal üyelerin detaylarını görebilir
        if ($user->is_admin || $user->is_staff) {
            return redirect()->route('staff.users.index')
                ->with('error', 'Bu kullanıcının detaylarını görüntüleme yetkiniz yok.');
        }

        // Get user's borrowings
        $activeBorrowings = $user->borrowings()
            ->where('status', 'approved')
            ->whereNull('returned_at')
            ->latest('borrow_date')
            ->get();

        $returnedBorrowings = $user->borrowings()
            ->where('status', 'returned')
            ->whereNotNull('returned_at')
            ->latest('returned_at')
            ->get();

        $overdueBorrowings = $user->borrowings()
            ->where('status', 'approved')
            ->whereNull('returned_at')
            ->whereDate('due_date', '<', now())
            ->latest('due_date')
            ->get();

        return view('staff.users.show', compact('user', 'activeBorrowings', 'returnedBorrowings', 'overdueBorrowings'));
    }
} 