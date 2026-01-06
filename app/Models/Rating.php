<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'rater_id',
        'overall_satisfaction',
        'friendliness',
        'professionalism',
        'service_speed',
        'comment',
        'ip_address',
        'user_agent',
        'is_approved',
        'is_flagged',
        'flag_reason',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_flagged' => 'boolean',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rater()
    {
        return $this->belongsTo(Rater::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // Calculate average of all rating aspects
    public function getAverageScoreAttribute(): float
    {
        $scores = array_filter([
            $this->overall_satisfaction,
            $this->friendliness,
            $this->professionalism,
            $this->service_speed,
        ]);

        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }
}
