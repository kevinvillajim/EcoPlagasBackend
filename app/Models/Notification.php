<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'sent_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // Notification types
    const TYPE_SERVICE_COMPLETED = 'service_completed';
    const TYPE_SERVICE_SCHEDULED = 'service_scheduled';
    const TYPE_SERVICE_REMINDER = 'service_reminder';
    const TYPE_SERVICE_CANCELLED = 'service_cancelled';
    const TYPE_SERVICE_RESCHEDULED = 'service_rescheduled';
    const TYPE_CERTIFICATE_READY = 'certificate_ready';
    const TYPE_CERTIFICATE_EXPIRING = 'certificate_expiring';
    const TYPE_PAYMENT_REMINDER = 'payment_reminder';
    const TYPE_REVIEW_SUBMITTED = 'review_submitted';
    const TYPE_GENERAL = 'general';

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if notification is read
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}