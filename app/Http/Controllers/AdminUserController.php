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

        // Apply role filter
        if ($request->has('role') && !empty($request->role)) {
            if ($request->role === 'admin') {
                $query->where('is_admin', 1);
            } elseif ($request->role === 'staff') {
                $query->where('is_staff', 1)->where('is_admin', 0);
            } else {
                $query->where('is_admin', 0)->where('is_staff', 0);
            }
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);
        
        return view('admin.users.index', compact('users'));
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
            'role' => ['required', 'in:user,staff,admin'],
            'profile_photo' => ['nullable', 'image', 'max:1024'],
        ]);

        $userData = [
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'is_admin' => $request->role === 'admin' ? 1 : 0,
            'is_staff' => $request->role === 'staff' ? 1 : 0,
        ];

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
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
            'role' => ['required', 'in:user,staff,admin'],
            'profile_photo' => ['nullable', 'image', 'max:1024'],
        ]);

        $userData = [
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_admin' => $request->role === 'admin' ? 1 : 0,
            'is_staff' => $request->role === 'staff' ? 1 : 0,
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        // Handle profile photo upload or removal
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            // Save new photo
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $userData['profile_photo'] = $path;
        } elseif ($request->has('remove_photo') && $request->remove_photo) {
            // Delete photo if remove_photo is checked
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $userData['profile_photo'] = null;
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
} 