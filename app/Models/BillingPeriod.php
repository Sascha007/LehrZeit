<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class BillingPeriod extends Model
{
    use Auditable, HasFactory;
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'status',
        'submitted_at',
        'approved_at',
        'exported_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'exported_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the billing period.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the teaching sessions for the billing period.
     */
    public function teachingSessions(): HasMany
    {
        return $this->hasMany(TeachingSession::class);
    }

    /**
     * Get the expenses for the billing period.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Check if the period is editable (status is OPEN).
     */
    public function isEditable(): bool
    {
        return $this->status === 'OPEN';
    }

    /**
     * Check if the period can be submitted.
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === 'OPEN';
    }

    /**
     * Check if the period can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'SUBMITTED';
    }

    /**
     * Check if the period can be reopened.
     */
    public function canBeReopened(): bool
    {
        return in_array($this->status, ['SUBMITTED', 'APPROVED']);
    }

    /**
     * Get the total hours for the billing period.
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->teachingSessions()->sum('hours');
    }

    /**
     * Get the total expenses for the billing period.
     */
    public function getTotalExpensesAttribute(): float
    {
        return $this->expenses()->sum('amount');
    }
}
