<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = User::where('is_admin', 1)->get();
        return view('admin.profiles.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.profiles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->is_admin = 1;
        $user->role = 'admin';
        $user->save();

        return redirect()->route('admin.profiles.index')
            ->with('success', 'Yönetici hesabı başarıyla oluşturuldu.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $profile)
    {
        return view('admin.profiles.edit', compact('profile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $profile)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($profile->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $profile->name = $validated['name'];
        $profile->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $profile->password = Hash::make($validated['password']);
        }
        
        $profile->save();

        return redirect()->route('admin.profiles.index')
            ->with('success', 'Yönetici hesabı başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $profile)
    {
        // Mevcut giriş yapmış kullanıcının kendi hesabını silmesini engelle
        if (auth()->id() === $profile->id) {
            return redirect()->route('admin.profiles.index')
                ->with('error', 'Kendi yönetici hesabınızı silemezsiniz.');
        }

        $profile->delete();

        return redirect()->route('admin.profiles.index')
            ->with('success', 'Yönetici hesabı başarıyla silindi.');
    }
} 