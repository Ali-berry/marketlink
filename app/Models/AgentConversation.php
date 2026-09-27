<?php

namespace App\Models;

use App\Enums\AgentMessageRole;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentConversation extends Model
{
    // har row ek chat turn hai, baad mein kabhi edit nahi hoti
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_type',
        'role',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'user_type' => UserRole::class,
            'role' => AgentMessageRole::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ek user ki ek agent ke saath history - doosre agent ki history mix nahi hoti
    public function scopeForUser(Builder $query, User $user, UserRole $userType): Builder
    {
        return $query->where('user_id', $user->id)->where('user_type', $userType->value);
    }
}
