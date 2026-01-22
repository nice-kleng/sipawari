<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class Rater extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik_hash',
        'full_name',
        'phone_encrypted',
        'gender',
        'birth_date',
        'relationship_to_patient',
        'visit_date',
        'service_unit',
        'consent_given',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'visit_date' => 'date',
        'consent_given' => 'boolean',
    ];

    protected $hidden = [
        'nik_hash',
        'phone_encrypted',
    ];

    // Relationships
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // Static method to hash NIK
    public static function hashNik(string $nik): string
    {
        return hash('sha256', config('app.key') . $nik);
    }

    // Static method to encrypt phone
    public static function encryptPhone(string $phone): string
    {
        return Crypt::encryptString($phone);
    }

    // Decrypt phone for admin view
    public function getDecryptedPhoneAttribute(): ?string
    {
        try {
            return $this->phone_encrypted ? Crypt::decryptString($this->phone_encrypted) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Check if this rater has rated an employee today
    public static function hasRatedToday(string $nik, int $employeeId): bool
    {
        $nikHash = self::hashNik($nik);

        return self::whereHas('ratings', function ($query) use ($employeeId) {
            $query->where('employee_id', $employeeId)
                ->whereDate('created_at', today());
        })->where('nik_hash', $nikHash)->exists();
    }

    // Find or create rater
    public static function findOrCreateRater(array $data): self
    {
        $nikHash = self::hashNik($data['nik']);

        return self::firstOrCreate(
            ['nik_hash' => $nikHash],
            [
                'full_name' => $data['full_name'],
                'phone_encrypted' => self::encryptPhone($data['phone']),
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'relationship_to_patient' => $data['relationship_to_patient'] ?? null,
                'visit_date' => $data['visit_date'] ?? null,
                'service_unit' => $data['service_unit'] ?? null,
                'consent_given' => $data['consent_given'] ?? false,
            ]
        );
    }
}
