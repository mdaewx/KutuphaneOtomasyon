<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'stock_id',
        'borrow_date',
        'due_date',
        'returned_at',
        'condition',
        'status',
        'notes',
        'reject_reason',
        'fine_amount'
    ];

    protected $casts = [
        'borrow_date' => 'datetime',
        'due_date' => 'datetime',
        'returned_at' => 'datetime',
        'fine_amount' => 'decimal:2'
    ];

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

    public function isOverdue()
    {
        if ($this->returned_at) {
            return $this->returned_at->isAfter($this->due_date);
        }
        
        return now()->isAfter($this->due_date);
    }
    
    public function getStatus()
    {
        if ($this->isOverdue()) {
            return 'overdue';
        }
        
        return $this->status;
    }
    
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    public function isApproved()
    {
        return $this->status === 'approved';
    }
    
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
    
    public function isReturned()
    {
        return $this->status === 'returned' || !is_null($this->returned_at);
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
        return $this->due_date->diffInDays($endDate, false);
    }

    /**
     * Ceza tutarını hesapla
     */
    public function calculateFine($dailyRate = 1.0)
    {
        return $this->getOverdueDays() * $dailyRate;
    }

    /**
     * Kitabı iade et
     */
    public function returnBook($condition = 'good')
    {
        $this->returned_at = now();
        $this->condition = $condition;
        $this->status = 'returned';
        
        if ($this->isOverdue()) {
            $this->fine_amount = $this->calculateFine();
        }

        return $this->save();
    }
}
