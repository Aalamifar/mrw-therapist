<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'role',
        'password',
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
            'password' => 'hashed',
        ];
    }

    public function classesAsTherapist()
    {
        return $this->hasMany(SchoolClass::class, 'therapist_id');
    }

    public function classesAsStudent()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_student', 'student_id', 'class_id');
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class, 'student_id');
    }

    /**
    * تمام گفتگوهایی که این کاربر در آن‌ها عضو است
    */
    public function conversations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Conversation::class)->withTimestamps();
    }

    /**
    * دریافت یا ساخت گفتگوی خصوصی با یک کاربر دیگر
    * اگر قبلاً گفتگویی وجود داشته باشد، همان را برمی‌گرداند
    * در غیر این صورت، یک گفتگوی جدید می‌سازد
    */
    public function getPrivateConversationWith(\App\Models\User $otherUser): \App\Models\Conversation
    {
        // بررسی می‌کنیم آیا گفتگویی وجود دارد که فقط من و این کاربر در آن باشیم
        $existing = $this->conversations()
            ->whereDoesntHave('users', function ($q) use ($otherUser) {
                $q->where('users.id', '!=', $this->id)
                ->where('users.id', '!=', $otherUser->id);
            })
            ->whereHas('users', function ($q) use ($otherUser) {
                $q->where('users.id', $otherUser->id);
            })
            ->withCount('users')
            ->having('users_count', 2)
            ->first();

        // اگر وجود داشت، همان را برمی‌گردانیم
        if ($existing) {
            return $existing;
        }

        // در غیر این صورت، یک گفتگوی جدید می‌سازیم
        $conversation = \App\Models\Conversation::create();
        $conversation->users()->attach([$this->id, $otherUser->id]);
    
        return $conversation;
    }
}