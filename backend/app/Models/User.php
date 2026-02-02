<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the properties for the agent user.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'agent_id');
    }

    /**
     * Get the enquiries for the user.
     */
    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }

    /**
     * Get the wishlist items for the user.
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the AI requests for the user.
     */
    public function aiRequests()
    {
        return $this->hasMany(AIRequest::class);
    }

    /**
     * Get the analytics logs for the user.
     */
    public function analyticsLogs()
    {
        return $this->hasMany(AnalyticsLog::class);
    }

    /**
     * Get the properties that the user has wishlisted.
     */
    public function wishlistProperties()
    {
        return $this->belongsToMany(Property::class, 'wishlists');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin()
    {
        return $this->role && $this->role->name === 'admin';
    }

    /**
     * Check if user is an agent.
     */
    public function isAgent()
    {
        return $this->role && $this->role->name === 'agent';
    }

    /**
     * Check if user is a regular user.
     */
    public function isRegularUser()
    {
        return $this->role && $this->role->name === 'user';
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role && $this->role->name === $role;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->role || !is_array($this->role->permissions)) {
            return false;
        }

        return in_array($permission, $this->role->permissions);
    }
}
