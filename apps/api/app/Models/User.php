<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['account_id', 'name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function farms(): BelongsToMany
    {
        return $this->belongsToMany(Farm::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    // The user's role on a farm, or null if they are not a member.
    public function roleOn(int $farmId): ?string
    {
        return $this->farms()->whereKey($farmId)->first()?->pivot->role;
    }

    public function isOwnerOf(int $farmId): bool
    {
        return $this->roleOn($farmId) === 'owner';
    }

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
        ];
    }
}
