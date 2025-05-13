<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminBookController;
use App\Http\Controllers\AdminBorrowingController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\TestAdminController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\ShelfManagementController;
use App\Http\Controllers\Admin\AcquisitionSourceController;
use App\Http\Controllers\Admin\AcquisitionTypeController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\BookController as StaffBookController;
use App\Http\Controllers\Staff\BorrowingController as StaffBorrowingController;
use App\Http\Controllers\Staff\MemberController;
use App\Http\Controllers\Staff\FineController;
use App\Http\Controllers\Staff\ProfileController as StaffProfileController;
use App\Http\Controllers\Staff\StockController as StaffStockController;
use App\Http\Controllers\Librarian\DashboardController as LibrarianDashboardController;
use App\Http\Controllers\Librarian\SearchController;
use App\Http\Controllers\Librarian\BookController as LibrarianBookController;
use App\Http\Controllers\Librarian\CategoryController;
use App\Http\Controllers\Librarian\BorrowingController as LibrarianBorrowingController;
use App\Http\Controllers\Librarian\ReturnController;
use App\Http\Controllers\Librarian\MemberController as LibrarianMemberController;
use App\Http\Controllers\Librarian\ActivityController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ana sayfa ve kitaplar
Route::get('/', [LibraryController::class, 'index'])->name('home');
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/search-by-isbn', [BookController::class, 'searchByIsbn'])->name('books.search-by-isbn');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// Ödünç alma işlemi için route
Route::post('/borrowings', [BookController::class, 'borrow'])->name('borrowings.store')->middleware('auth');

// Redirect from /dashboard to appropriate dashboard based on role
Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } else if (auth()->user()->hasRole('staff')) {
        return redirect()->route('staff.dashboard');
    }
    return redirect()->route('profile');
})->middleware(['auth'])->name('dashboard');

// Kullanıcı profili
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/return-book/{borrowing}', [ProfileController::class, 'returnBook'])->name('profile.return-book');
});

// Admin Panel
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/{path?}', [AdminController::class, 'dashboard'])
        ->name('dashboard')
        ->where('path', '^$|^dashboard$'); // Boş string veya "dashboard" kabul eder
    
    // Borrowings
    Route::get('/borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
    Route::get('/borrowings/create', [AdminBorrowingController::class, 'create'])->name('borrowings.create');
    Route::post('/borrowings', [AdminBorrowingController::class, 'store'])->name('borrowings.store');
    Route::get('/borrowings/{borrowing}', [AdminBorrowingController::class, 'show'])->name('borrowings.show');
    Route::put('/borrowings/{borrowing}/return', [AdminBorrowingController::class, 'returnBook'])->name('borrowings.return');
    Route::put('/borrowings/{borrowing}/update-status', [AdminBorrowingController::class, 'updateStatus'])->name('borrowings.update-status');
    Route::delete('/borrowings/{borrowing}', [AdminBorrowingController::class, 'destroy'])->name('borrowings.destroy');
    
    // Admin Profile Management
    Route::resource('profiles', AdminProfileController::class);
    
    // Kullanıcı Yönetimi
    Route::resource('users', AdminUserController::class);
    
    // Kitap Yönetimi
    Route::resource('books', AdminBookController::class);
    Route::post('/books/{book}/upload-cover', [AdminBookController::class, 'uploadCover'])->name('books.upload-cover');
    Route::get('/books/check-isbn/{isbn}', [AdminBookController::class, 'checkIsbn'])->name('books.check-isbn');
    Route::get('/books/search/{isbn}', [AdminBookController::class, 'searchByIsbn'])->name('books.search');
    
    // Yazar Yönetimi
    Route::resource('authors', AuthorController::class);
    
    // Yayınevi Yönetimi
    Route::resource('publishers', PublisherController::class);
    
    // Kategori Yönetimi
    Route::resource('categories', AdminCategoryController::class);
    
    // Raporlar
    Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('reports/popular-books', [AdminReportController::class, 'popularBooks'])->name('reports.popular-books');
    Route::get('reports/active-users', [AdminReportController::class, 'activeUsers'])->name('reports.active-users');
    Route::get('reports/overdue', [AdminReportController::class, 'overdue'])->name('reports.overdue');
    Route::get('reports/monthly-stats', [AdminReportController::class, 'monthlyStats'])->name('reports.monthly-stats');
    Route::get('reports/categories', [AdminReportController::class, 'categories'])->name('reports.categories');
    
    // Ayarlar
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::post('settings/clear-cache', [AdminSettingController::class, 'clearCache'])->name('settings.clear-cache');

    // Stok Yönetimi
    Route::resource('stocks', StockController::class);
    Route::get('books/search/{isbn}', [StockController::class, 'searchBook'])->name('books.search');

    // Edinme Kaynakları
    Route::resource('acquisitions', AcquisitionSourceController::class);
    Route::get('/acquisitions/source-types', [AcquisitionSourceController::class, 'sourceTypeIndex'])->name('acquisitions.source-types');
    Route::get('/acquisitions/source-types/create', [AcquisitionSourceController::class, 'sourceTypeCreate'])->name('acquisitions.source-types.create');
    Route::post('/acquisitions/source-types', [AcquisitionSourceController::class, 'sourceTypeStore'])->name('acquisitions.source-types.store');
    Route::get('/acquisitions/source-types/{sourceType}/edit', [AcquisitionSourceController::class, 'sourceTypeEdit'])->name('acquisitions.source-types.edit');
    Route::put('/acquisitions/source-types/{sourceType}', [AcquisitionSourceController::class, 'sourceTypeUpdate'])->name('acquisitions.source-types.update');
    Route::delete('/acquisitions/source-types/{sourceType}', [AcquisitionSourceController::class, 'sourceTypeDestroy'])->name('acquisitions.source-types.destroy');

    // Raf Yönetimi
    Route::get('/shelf-management', [ShelfManagementController::class, 'index'])->name('shelf-management.index');
    Route::get('/shelves/create', [ShelfManagementController::class, 'create'])->name('shelves.create');
    Route::post('/shelves', [ShelfManagementController::class, 'store'])->name('shelves.store');
    Route::get('/shelves/{shelf}/edit', [ShelfManagementController::class, 'edit'])->name('shelves.edit');
    Route::put('/shelves/{shelf}', [ShelfManagementController::class, 'update'])->name('shelves.update');
    Route::delete('/shelves/{shelf}', [ShelfManagementController::class, 'destroy'])->name('shelves.destroy');
    Route::post('/shelf-management/assign', [ShelfManagementController::class, 'assign'])->name('shelf-management.assign');
    Route::post('/shelf-management/{book}/update-shelf', [ShelfManagementController::class, 'updateShelf'])->name('shelf-management.update-shelf');
    Route::get('/shelf-management/search', [ShelfManagementController::class, 'search'])->name('shelf-management.search');

    // Kitap edinme kaynakları
    Route::get('/books/{book}/add-acquisition', [AdminBookController::class, 'addAcquisitionSource'])
        ->name('books.add-acquisition');
    Route::post('/books/{book}/store-acquisition', [AdminBookController::class, 'storeAcquisitionSource'])
        ->name('books.store-acquisition');

    // Edinme Türleri
    Route::resource('acquisition-types', AcquisitionTypeController::class);
});

// Memur Panel
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Test route for book search - direct JSON response
    Route::get('/test-search', function() {
        return response()->json([
            'success' => true,
            'message' => 'Test route works!',
            'timestamp' => now()->toDateTimeString()
        ]);
    });
    
    // Test route for book database check
    Route::get('/check-books', function() {
        $books = \App\Models\Book::with(['authors', 'publisher', 'category'])
            ->take(5)
            ->get(['id', 'title', 'isbn']);
            
        if ($books->isEmpty()) {
            // Create a test book if none exists
            $category = \App\Models\Category::first() ?? \App\Models\Category::create(['name' => 'Test Kategori']);
            $publisher = \App\Models\Publisher::first() ?? \App\Models\Publisher::create(['name' => 'Test Yayınevi']);
            $author = \App\Models\Author::first() ?? \App\Models\Author::create(['name' => 'Test Yazar']);
            
            $book = \App\Models\Book::create([
                'title' => 'Test Kitap',
                'isbn' => '9789750719387',
                'category_id' => $category->id,
                'publisher_id' => $publisher->id,
                'page_count' => 100,
                'language' => 'Türkçe',
                'publication_year' => 2023,
                'description' => 'Bu bir test kitabıdır.',
            ]);
            
            $book->authors()->attach($author->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Test kitap oluşturuldu!',
                'book' => $book,
                'isbn' => $book->isbn
            ]);
        }
        
        return response()->json([
            'success' => true,
            'books' => $books,
            'count' => $books->count(),
            'first_isbn' => $books->first() ? $books->first()->isbn : null
        ]);
    });
    
    // Stok Yönetimi
    Route::resource('stocks', StaffStockController::class);
    
    // Kitap Arama Routes - Ana ve Fallback
    Route::get('books/search', [StaffBookController::class, 'search'])->name('books.search');
    Route::get('books/search-fallback', [StaffBookController::class, 'searchFallback'])->name('books.search-fallback');
    
    // Ödünç İşlemleri
    Route::resource('borrowings', StaffBorrowingController::class);
    
    // Kitap Yönetimi
    Route::resource('books', StaffBookController::class);
    
    // Yazar Yönetimi
    Route::resource('authors', AuthorController::class);
    
    // Yayınevi Yönetimi
    Route::resource('publishers', PublisherController::class);
});

// Test route for admin dashboard
Route::get('/test-dashboard', [TestAdminController::class, 'dashboard'])
    ->middleware(['auth'])
    ->name('test.dashboard');

// Kütüphane Memuru Routes
Route::middleware(['auth', 'librarian'])->prefix('librarian')->name('librarian.')->group(function () {
    Route::get('/dashboard', [LibrarianDashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    
    // Kitap İşlemleri
    Route::resource('books', LibrarianBookController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('authors', App\Http\Controllers\Librarian\AuthorController::class);
    
    // Ödünç İşlemleri
    Route::resource('borrowings', LibrarianBorrowingController::class);
    Route::get('/returns/create', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
    Route::get('/overdue', [LibrarianBorrowingController::class, 'overdue'])->name('overdue');
    
    // Stok Yönetimi
    Route::resource('stocks', App\Http\Controllers\Librarian\StockController::class);
    Route::get('/shelf-management', [App\Http\Controllers\Librarian\ShelfManagementController::class, 'index'])->name('shelf-management.index');
    Route::post('/shelf-management/assign', [App\Http\Controllers\Librarian\ShelfManagementController::class, 'assign'])->name('shelf-management.assign');
    
    // Üye İşlemleri
    Route::resource('members', LibrarianMemberController::class);
    
    // Etkinlik Kaydı
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
});

// Test routes for debugging
Route::get('/test-isbn-search', function() {
    return view('test-isbn-search');
})->name('test.isbn-search');

require __DIR__.'/auth.php';
