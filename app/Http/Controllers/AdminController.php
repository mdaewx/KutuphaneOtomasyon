<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Borrowing;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
        // Middleware'leri controller seviyesinde kullanma şeklimizi düzeltelim
        // Laravel 8 ve üzeri versiyonlarda controller constructor'da doğrudan middleware kullanımı sorun çıkarabilir
        // Bu nedenle middleware'leri routes/web.php'de tanımlıyoruz
    }

    /**
     * Admin dashboard sayfasını göster
     */
    public function dashboard()
    {
        try {
            // Toplam kitap sayısı
            $totalBooks = Book::count();
            
            // Toplam kullanıcı sayısı (adminler hariç)
            $totalUsers = User::where('is_admin', 0)->count();
            
            // Aktif ödünç sayısı
            $activeBorrowings = Borrowing::whereNull('returned_at')->count();
            
            // Gecikmiş ödünç sayısı
            $overdueBorrowings = Borrowing::whereNull('returned_at')
                ->where('due_date', '<', now())
                ->count();
            
            // Son 5 kullanıcı
            $latestUsers = User::where('is_admin', 0)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            // Son 5 kitap
            $latestBooks = Book::orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            // Son 5 ödünç işlemi
            $recentBorrowings = Borrowing::with(['user', 'book'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            return view('admin.dashboard', compact(
                'totalBooks',
                'totalUsers',
                'activeBorrowings',
                'overdueBorrowings',
                'latestUsers',
                'latestBooks',
                'recentBorrowings'
            ));
        } catch (\Exception $e) {
            // Log the error
            \Log::error('AdminController@dashboard error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->view('errors.500', ['exception' => $e], 500);
        }
    }

    public function users()
    {
        $users = User::withCount('activeBorrowings')->get();
        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'profile_photo' => 'nullable|image|max:1024'
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->profile_photo = basename($path);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'profile_photo' => 'nullable|image|max:1024'
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete('profiles/' . $user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->profile_photo = basename($path);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'Kullanıcı başarıyla güncellendi.');
    }

    public function deleteUser(User $user)
    {
        if ($user->profile_photo) {
            Storage::disk('public')->delete('profiles/' . $user->profile_photo);
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Kullanıcı başarıyla silindi.');
    }

    public function books()
    {
        $books = Book::withCount('activeBorrowings')->get();
        return view('admin.books', compact('books'));
    }

    public function storeBook(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:1024'
        ]);

        $book = new Book();
        $book->title = $validated['title'];
        $book->author = $validated['author'];
        $book->isbn = $validated['isbn'];
        $book->quantity = $validated['quantity'];
        $book->description = $validated['description'];

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('covers', 'public');
            $book->cover_image = basename($path);
        }

        $book->save();

        return redirect()->route('admin.books')->with('success', 'Kitap başarıyla eklendi.');
    }

    public function updateBook(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $book->id,
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:1024'
        ]);

        $book->title = $validated['title'];
        $book->author = $validated['author'];
        $book->isbn = $validated['isbn'];
        $book->quantity = $validated['quantity'];
        $book->description = $validated['description'];

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete('covers/' . $book->cover_image);
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $book->cover_image = basename($path);
        }

        $book->save();

        return redirect()->route('admin.books')->with('success', 'Kitap başarıyla güncellendi.');
    }

    public function deleteBook(Book $book)
    {
        if ($book->cover_image) {
            Storage::disk('public')->delete('covers/' . $book->cover_image);
        }

        $book->delete();

        return redirect()->route('admin.books')->with('success', 'Kitap başarıyla silindi.');
    }

    public function borrowings()
    {
        $borrowings = Borrowing::with(['user', 'book'])->orderBy('created_at', 'desc')->paginate(10);
        $users = User::all();
        $books = Book::where('quantity', '>', 0)->get();
        
        return view('admin.borrowings', compact('borrowings', 'users', 'books'));
    }

    public function storeBorrowing(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'due_date' => 'required|date|after:today'
        ]);

        $book = Book::findOrFail($validated['book_id']);
        
        if ($book->quantity <= 0) {
            return redirect()->route('admin.borrowings')->with('error', 'Bu kitap stokta bulunmamaktadır.');
        }

        $borrowing = new Borrowing();
        $borrowing->user_id = $validated['user_id'];
        $borrowing->book_id = $validated['book_id'];
        $borrowing->borrow_date = now();
        $borrowing->due_date = $validated['due_date'];
        $borrowing->returned_at = null;
        $borrowing->save();

        $book->decrement('quantity');

        return redirect()->route('admin.borrowings')->with('success', 'Ödünç verme işlemi başarıyla kaydedildi.');
    }

    public function returnBorrowing(Borrowing $borrowing)
    {
        if ($borrowing->returned_at) {
            return redirect()->route('admin.borrowings')->with('error', 'Bu kitap zaten iade edilmiş.');
        }

        $borrowing->returned_at = now();
        $borrowing->status = 'returned';
        $borrowing->save();

        // Kitabın available_quantity değerini artır
        $book = $borrowing->book;
        $book->increment('quantity');
        $book->increment('available_quantity');
        
        // Ödünç alınan tüm kopya sayısını hesapla
        $activeBorrowings = $book->activeBorrowings()->count();
        
        // Kitabın durumunu güncelle
        if ($activeBorrowings < $book->quantity) {
            $book->status = 'available';
            $book->save();
        }

        return redirect()->route('admin.borrowings')->with('success', 'Kitap başarıyla iade alındı.');
    }

    public function deleteBorrowing(Borrowing $borrowing)
    {
        if (!$borrowing->returned_at) {
            $borrowing->book->increment('quantity');
        }

        $borrowing->delete();

        return redirect()->route('admin.borrowings')->with('success', 'Ödünç kaydı başarıyla silindi.');
    }
} 