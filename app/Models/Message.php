<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'reply',
        'replied_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'replied_at' => 'datetime',
    ];

    /**
     * Scope a query to only include new messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope a query to only include read messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Scope a query to only include replied messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    /**
     * Check if the message is new.
     *
     * @return bool
     */
    public function isNew()
    {
        return $this->status === 'new';
    }

    /**
     * Mark the message as read.
     *
     * @return bool
     */
    public function markAsRead()
    {
        if ($this->status === 'new') {
            return $this->update(['status' => 'read']);
        }
        
        return true;
    }

    /**
     * Mark the message as replied.
     *
     * @param  string  $reply
     * @return bool
     */
    public function markAsReplied($reply)
    {
        return $this->update([
            'status' => 'replied',
            'reply' => $reply,
            'replied_at' => now(),
        ]);
    }
}