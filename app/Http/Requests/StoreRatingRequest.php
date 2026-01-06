<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public access
    }

    public function rules(): array
    {
        return [
            // Identity - Required
            'nik' => [
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]+$/',
            ],
            'full_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'phone' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^[0-9]+$/',
            ],

            // Identity - Optional
            'gender' => [
                'nullable',
                'in:male,female,other',
            ],
            'birth_date' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01',
            ],
            'relationship_to_patient' => [
                'nullable',
                'string',
                'max:100',
            ],
            'visit_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'service_unit' => [
                'nullable',
                'string',
                'max:100',
            ],

            // Ratings - Required
            'overall_satisfaction' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            // Ratings - Optional
            'friendliness' => [
                'nullable',
                'integer',
                'min:1',
                'max:5',
            ],
            'professionalism' => [
                'nullable',
                'integer',
                'min:1',
                'max:5',
            ],
            'service_speed' => [
                'nullable',
                'integer',
                'min:1',
                'max:5',
            ],

            // Comment
            'comment' => [
                'nullable',
                'string',
                'max:500',
            ],

            // Consent
            'consent_given' => [
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus 16 digit.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',

            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',

            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.min' => 'Nomor telepon minimal 10 digit.',
            'phone.max' => 'Nomor telepon maksimal 15 digit.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka.',

            'overall_satisfaction.required' => 'Penilaian kepuasan keseluruhan wajib diisi.',
            'overall_satisfaction.min' => 'Penilaian minimal 1 bintang.',
            'overall_satisfaction.max' => 'Penilaian maksimal 5 bintang.',

            'comment.max' => 'Komentar maksimal 500 karakter.',

            'consent_given.accepted' => 'Anda harus menyetujui penggunaan data.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Sanitize NIK and phone - remove any non-numeric characters
        if ($this->has('nik')) {
            $this->merge([
                'nik' => preg_replace('/[^0-9]/', '', $this->nik),
            ]);
        }

        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^0-9]/', '', $this->phone),
            ]);
        }
    }
}
