@extends('layouts.public')

@section('title', 'Terima Kasih')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
            <!-- Success Icon -->
            <div class="mx-auto w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Thank You Message -->
            <h1 class="text-3xl font-bold text-gray-800 mb-4">
                Terima Kasih!
            </h1>

            <p class="text-lg text-gray-600 mb-2">
                Penilaian Anda telah berhasil dikirim.
            </p>

            <p class="text-gray-500 mb-8">
                Bantu Kami untuk lebih baik.
            </p>

            <!-- Employee Info -->
            <div class="bg-blue-50 rounded-lg p-6 mb-8">
                <p class="text-sm text-gray-600 mb-2">Anda telah menilai:</p>
                <p class="text-xl font-semibold text-gray-800">{{ $employee->name }}</p>
                <p class="text-gray-600">{{ $employee->position }}</p>
                <p class="text-gray-500 text-sm">{{ $employee->department }}</p>
            </div>

            <!-- Info Box -->
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded text-left">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700">
                            Anda hanya dapat memberikan satu penilaian per hari untuk setiap karyawan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-8">
                <a href="{{ route('public.rate.show', $employee->uuid) }}"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-300 shadow-lg">
                    Kembali
                </a>
            </div>
        </div>
    </div>
@endsection
