<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // İstatistikler
        $totalBorrowings = $user->borrowings()->count();
        $activeBorrowings = $user->borrowings()->whereNull('returned_at')->count();
        
        // fine_amount alanı yeni eklendiği için, veritabanında null değerler olabilir
        try {
            $totalFines = $user->borrowings()->sum('fine_amount') ?? 0.00;
        } catch (\Exception $e) {
            $totalFines = 0.00;
        }
        
        // Aktif ödünç alınan kitaplar
        $activeBorrowedBooks = $user->borrowings()
            ->with('book')
            ->whereNull('returned_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('profile', compact(
            'totalBorrowings',
            'activeBorrowings',
            'totalFines',
            'activeBorrowedBooks'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB maksimum boyut
        ], [
            'profile_photo.image' => 'Dosya bir resim olmalıdır.',
            'profile_photo.mimes' => 'Dosya jpeg, png, jpg veya gif formatında olmalıdır.',
            'profile_photo.max' => 'Dosya boyutu en fazla 2MB olabilir.',
        ]);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Mevcut şifre yanlış']);
            }
            $user->password = Hash::make($request->new_password);
        }
        
        // Profil fotoğrafı yükleme işlemi
        if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
            try {
                // Eğer daha önce bir profil fotoğrafı varsa sil
                if ($user->profile_photo) {
                    $oldPhotoPath = 'public/profiles/' . $user->profile_photo;
                    if (Storage::exists($oldPhotoPath)) {
                        Storage::delete($oldPhotoPath);
                    }
                }
                
                // Dosya adını güvenli hale getir
                $fileName = pathinfo($request->file('profile_photo')->getClientOriginalName(), PATHINFO_FILENAME);
                $fileName = Str::slug($fileName) . '_' . time() . '.' . $request->file('profile_photo')->getClientOriginalExtension();
                
                // Yeni fotoğrafı kaydet
                $path = $request->file('profile_photo')->storeAs('profiles', $fileName, 'public');
                $user->profile_photo = basename($path);
                
            } catch (\Exception $e) {
                return back()->withErrors(['profile_photo' => 'Fotoğraf yüklenirken bir hata oluştu: ' . $e->getMessage()]);
            }
        }

        $user->save();

        return back()->with('success', 'Profil başarıyla güncellendi');
    }

    public function returnBook(Borrowing $borrowing)
    {
        if ($borrowing->user_id !== Auth::id()) {
            return back()->with('error', 'Bu işlem için yetkiniz yok');
        }

        // Kitabın statusunu ve available_quantity değerini güncelle
        $book = $borrowing->book;
        $book->increment('available_quantity');
        
        // Eğer kitabın statusu 'borrowed' ise ve available_quantity > 0 ise, statusu 'available' yap
        if ($book->status === 'borrowed' && $book->available_quantity > 0) {
            $book->status = 'available';
            $book->save();
        }

        $borrowing->returned_at = now();
        $borrowing->status = 'returned';

        // Gecikme cezası hesaplama
        if ($borrowing->due_date < now()) {
            $daysLate = now()->diffInDays($borrowing->due_date);
            $borrowing->fine_amount = $daysLate * 1; // Günlük 1 TL ceza
        }

        $borrowing->save();

        return back()->with('success', 'Kitap başarıyla iade edildi');
    }
}
