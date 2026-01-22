<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'uuid',
        'employee_code',
        'name',
        'photo',
        'position_id',
        'unit_id',
        'qr_code_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Auto-generate UUID on creation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->uuid)) {
                $employee->uuid = (string) Str::uuid();
            }
        });

        static::created(function ($employee) {
            $employee->generateQrCode();
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    // Accessors
    public function getRatingUrlAttribute(): string
    {
        return route('public.rate.show', ['uuid' => $this->uuid]);
    }

    // QR Code generation
    public function generateQrCode(): void
    {
        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->generate($this->rating_url);

        $filename = "qr_codes/{$this->uuid}.png";
        Storage::disk('public')->put($filename, $qrCode);

        $this->qr_code_path = $filename;
        $this->saveQuietly();
    }

    // Analytics methods
    public function averageRating(): float
    {
        return $this->ratings()
            ->where('is_approved', true)
            ->avg('overall_satisfaction') ?? 0;
    }

    public function totalRatings(): int
    {
        return $this->ratings()
            ->where('is_approved', true)
            ->count();
    }

    public function ratingsThisMonth(): int
    {
        return $this->ratings()
            ->where('is_approved', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }
}
