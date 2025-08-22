<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'rating',
        'comment',
        'response_message',
        'response_by',
        'response_at',
        'is_public',
        'status',
        'moderated_by',
        'moderated_at',
        'is_auto_approved',
        'location',
        'verified',
        'is_featured',
        'helpful_count',
    ];

    protected $casts = [
        'response_at' => 'datetime',
        'moderated_at' => 'datetime',
        'is_public' => 'boolean',
        'is_auto_approved' => 'boolean',
        'verified' => 'boolean',
        'is_featured' => 'boolean',
        'helpful_count' => 'integer',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relationship with responder (User)
     */
    public function responder()
    {
        return $this->belongsTo(User::class, 'response_by');
    }

    /**
     * Relationship with moderator (User)
     */
    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Check if review has response
     */
    public function hasResponse()
    {
        return !is_null($this->response_message);
    }

    /**
     * Scope for public reviews
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for high ratings
     */
    public function scopeHighRating($query, $rating = 4)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope for specific status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'auto_approved']);
    }

    /**
     * Scope for pending reviews
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for featured reviews
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for verified reviews
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Check if review is approved
     */
    public function isApproved()
    {
        return in_array($this->status, ['approved', 'auto_approved']);
    }

    /**
     * Check if review is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if review is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the review
     */
    public function approve($moderatorId = null)
    {
        $this->update([
            'status' => 'approved',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'is_auto_approved' => false,
        ]);
    }

    /**
     * Reject the review
     */
    public function reject($moderatorId = null)
    {
        $this->update([
            'status' => 'rejected',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'is_auto_approved' => false,
        ]);
    }

    /**
     * Auto-approve the review
     */
    public function autoApprove()
    {
        $this->update([
            'status' => 'auto_approved',
            'moderated_at' => now(),
            'is_auto_approved' => true,
        ]);
    }

    /**
     * Increment helpful count
     */
    public function incrementHelpful()
    {
        $this->increment('helpful_count');
    }
}