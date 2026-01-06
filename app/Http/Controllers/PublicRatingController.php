<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Rater;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class PublicRatingController extends Controller
{
    /**
     * Show the rating form for a specific employee
     */
    public function show(string $uuid)
    {
        $employee = Employee::where('uuid', $uuid)
            ->where('is_active', true)
            ->firstOrFail();

        return view('public.rate', compact('employee'));
    }

    /**
     * Store a new rating
     */
    public function store(Request $request, string $uuid)
    {
        // Rate limiting: max 5 submissions per minute per IP
        $executed = RateLimiter::attempt(
            'submit-rating:' . $request->ip(),
            $perMinute = 5,
            function () {},
            $decaySeconds = 60
        );

        if (!$executed) {
            return back()->withErrors([
                'rate_limit' => 'Terlalu banyak percobaan. Silakan coba lagi dalam beberapa saat.'
            ])->withInput();
        }

        // Validate input
        $validated = $request->validate([
            'nik' => 'required|string|size:16|regex:/^[0-9]+$/',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date|before:today',
            'relationship_to_patient' => 'nullable|string|max:100',
            'visit_date' => 'nullable|date|before_or_equal:today',
            'service_unit' => 'nullable|string|max:100',
            'overall_satisfaction' => 'required|integer|min:1|max:5',
            'friendliness' => 'nullable|integer|min:1|max:5',
            'professionalism' => 'nullable|integer|min:1|max:5',
            'service_speed' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'consent_given' => 'accepted',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus 16 digit.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'overall_satisfaction.required' => 'Penilaian kepuasan wajib diisi.',
            'consent_given.accepted' => 'Anda harus menyetujui penggunaan data.',
        ]);

        // Find employee
        $employee = Employee::where('uuid', $uuid)
            ->where('is_active', true)
            ->firstOrFail();

        // Check if NIK has already rated this employee today
        if (Rater::hasRatedToday($validated['nik'], $employee->id)) {
            return back()->withErrors([
                'duplicate' => 'Anda sudah memberikan penilaian untuk karyawan ini hari ini.'
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            // Find or create rater
            $rater = Rater::findOrCreateRater($validated);

            // Create rating
            Rating::create([
                'employee_id' => $employee->id,
                'rater_id' => $rater->id,
                'overall_satisfaction' => $validated['overall_satisfaction'],
                'friendliness' => $validated['friendliness'] ?? null,
                'professionalism' => $validated['professionalism'] ?? null,
                'service_speed' => $validated['service_speed'] ?? null,
                'comment' => $validated['comment'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_approved' => true,
            ]);

            DB::commit();

            return redirect()->route('public.rate.success', ['uuid' => $uuid]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Terjadi kesalahan. Silakan coba lagi.'
            ])->withInput();
        }
    }

    /**
     * Show success page
     */
    public function success(string $uuid)
    {
        $employee = Employee::where('uuid', $uuid)->firstOrFail();

        return view('public.success', compact('employee'));
    }
}
    