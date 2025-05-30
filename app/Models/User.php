<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'phone',
        'address',
        'is_admin',
        'is_staff',
        'profile_photo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'is_staff' => 'boolean'
    ];

    public function validateCredentials(array $credentials)
    {
        $plain = $credentials['password'];
        
        return Hash::check($plain, $this->password);
    }

    /**
     * Automatically hash the password when it's set
     */
    public function setPasswordAttribute($value)
    {
        if ($value && strlen($value) < 60) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * Get all borrowings for this user
     */
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    /**
     * Get active borrowings for this user
     */
    public function activeBorrowings()
    {
        return $this->hasMany(Borrowing::class)->whereNull('returned_at');
    }

    /**
     * Get all fines for this user
     */
    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    /**
     * Get user's favorite books
     */
    public function favoriteBooks()
    {
        return $this->belongsToMany(Book::class, 'favorite_books');
    }

    /**
     * Get books suggested to this user
     */
    public function suggestedBooks()
    {
        return $this->belongsToMany(Book::class, 'suggested_books');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role): bool
    {
        if ($role === 'admin') {
            return $this->isAdmin();
        } elseif ($role === 'staff') {
            return $this->isStaff();
        }
        return !$this->isAdmin() && !$this->isStaff();
    }

    public function getRoleNameAttribute(): string
    {
        if ($this->isAdmin()) {
            return 'Yönetici';
        } elseif ($this->isStaff()) {
            return 'Memur';
        }
        return 'Kullanıcı';
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isStaff(): bool
    {
        return $this->is_staff || $this->isAdmin();
    }

    public function isUser(): bool
    {
        return !$this->isAdmin() && !$this->isStaff();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->surname);
    }
}
