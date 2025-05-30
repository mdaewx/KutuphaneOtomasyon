<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()->withCount(['borrowings', 'activeBorrowings']);

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

        $users = $query->orderBy('id', 'asc')->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    private function getUserRole($user)
    {
        if ($user->is_admin) {
            return 'Yönetici';
        } elseif ($user->is_staff) {
            return 'Personel';
        }
        return 'Üye';
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'in:user,staff,admin']
        ]);

        $userData = [
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'is_admin' => $request->user_type === 'admin',
            'is_staff' => in_array($request->user_type, ['admin', 'staff'])
        ];

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $userData['profile_photo'] = $path;
        }

        $user = User::create($userData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
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

        return view('admin.users.show', compact('user', 'activeBorrowings', 'returnedBorrowings', 'overdueBorrowings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'in:user,staff,admin']
        ]);

        $userData = [
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_admin' => $request->user_type === 'admin',
            'is_staff' => in_array($request->user_type, ['admin', 'staff'])
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $userData['profile_photo'] = $path;
        }

        $user->update($userData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı bilgileri başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent admin from deleting their own account
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        // Delete profile photo if exists
        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Delete user
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı başarıyla silindi.');
    }

    public function search(Request $request)
    {
        $term = $request->input('term');
        
        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }
        
        $users = \App\Models\User::where(function($query) use ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('surname', 'LIKE', "%{$term}%")
                  ->orWhere('email', 'LIKE', "%{$term}%");
        })
        ->where('is_admin', 0) // Exclude admin users
        ->select('id', 'name', 'surname', 'email')
        ->limit(10)
        ->get();
        
        return response()->json($users);
    }
} 