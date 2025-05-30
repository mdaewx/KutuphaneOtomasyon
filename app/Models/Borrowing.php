<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Borrowing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'stock_id',
        'borrow_date',
        'due_date',
        'returned_at',
        'condition',
        'notes',
        'fine_amount',
        'status'
    ];

    protected $dates = [
        'borrow_date',
        'due_date',
        'returned_at',
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'status' => 'pending',
        'fine_amount' => 0.00
    ];

    protected $casts = [
        'borrow_date' => 'datetime',
        'due_date' => 'datetime',
        'returned_at' => 'datetime',
        'fine_amount' => 'decimal:2'
    ];

    // Borrowing statuses
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_RETURNED = 'returned';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_LOST = 'lost';

    // Book conditions
    const CONDITION_GOOD = 'good';
    const CONDITION_DAMAGED = 'damaged';
    const CONDITION_LOST = 'lost';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($borrowing) {
            if (!isset($borrowing->fine_amount)) {
                $borrowing->fine_amount = 0.00;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function returner()
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function isOverdue()
    {
        if ($this->returned_at) {
            return $this->returned_at > $this->due_date;
        }
        return now() > $this->due_date;
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    public function getStatus()
    {
        if ($this->returned_at) {
            return self::STATUS_RETURNED;
        }

        if ($this->isOverdue()) {
            return self::STATUS_OVERDUE;
        }

        return $this->status;
    }

    public function getStatusLabelAttribute()
    {
        return match($this->getStatus()) {
            self::STATUS_PENDING => 'Beklemede',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_RETURNED => 'İade Edildi',
            self::STATUS_OVERDUE => 'Gecikmiş',
            self::STATUS_LOST => 'Kayıp',
            default => 'Bilinmiyor'
        };
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isReturned()
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isLost()
    {
        return $this->status === self::STATUS_LOST;
    }

    /**
     * Gecikme süresini hesapla (gün olarak)
     */
    public function getOverdueDays()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $endDate = $this->returned_at ?? now();
        return $endDate->diffInDays($this->due_date);
    }

    /**
     * Ceza tutarını hesapla
     */
    public function calculateFine($dailyRate = 1.0)
    {
        return $this->getOverdueDays() * $dailyRate;
    }

    public function updateFineAmount()
    {
        $this->fine_amount = $this->calculateFine();
        return $this->save();
    }

    /**
     * Kitabı iade et
     */
    public function returnBook($condition = self::CONDITION_GOOD)
    {
        $this->returned_at = now();
        $this->condition = $condition;
        $this->status = self::STATUS_RETURNED;
        
        // Stok durumunu güncelle
        if ($this->stock) {
            $this->stock->status = match($condition) {
                self::CONDITION_DAMAGED => Stock::STATUS_DAMAGED,
                self::CONDITION_LOST => Stock::STATUS_LOST,
                default => Stock::STATUS_AVAILABLE
            };
            
            $this->stock->is_available = $condition === self::CONDITION_GOOD;
            $this->stock->save();
        }

        // Gecikme cezası kontrolü
        if ($this->isOverdue()) {
            $overdueDays = $this->getOverdueDays();
            $fineAmount = $overdueDays * (session('overdue_fine_per_day') ?? 1.0);

            $fine = new Fine([
                'user_id' => $this->user_id,
                'book_id' => $this->book_id,
                'amount' => $fineAmount,
                'reason' => 'late',
                'days_late' => $overdueDays,
                'status' => Fine::STATUS_PENDING
            ]);

            $this->fines()->save($fine);
            $this->fine_amount = $fineAmount;
        }

        return $this->save();
    }
}
