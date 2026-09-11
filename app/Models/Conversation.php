<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = ['title'];

    /**
     * کاربرانی که در این گفتگو عضو هستند
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * تمام پیام‌های این گفتگو (از جدید به قدیم)
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    /**
     * آخرین پیام گفتگو (برای نمایش در لیست)
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * دریافت طرف مقابل در یک گفتگوی خصوصی
     */
    public function otherUser(User $user): ?User
    {
        return $this->users()
            ->where('users.id', '!=', $user->getKey())
            ->first();
    }
}
