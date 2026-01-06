@extends('layouts.public')

@section('title', 'Beri Penilaian - ' . $employee->name)

@section('content')
    <div class="max-w-3xl mx-auto">
        <!-- Employee Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6">
            <div class="flex flex-col items-center mb-8">
                @if ($employee->photo)
                    <img src="{{ Storage::url($employee->photo) }}" alt="{{ $employee->name }}"
                        class="w-32 h-32 rounded-full object-cover border-4 border-blue-500 mb-4">
                @else
                    <div class="w-32 h-32 rounded-full bg-blue-500 flex items-center justify-center mb-4">
                        <span class="text-4xl text-white font-bold">
                            {{ substr($employee->name, 0, 1) }}
                        </span>
                    </div>
                @endif

                <h1 class="text-3xl font-bold text-gray-800 text-center mb-2">
                    {{ $employee->name }}
                </h1>
                <p class="text-lg text-gray-600">{{ $employee->position }}</p>
                <p class="text-md text-gray-500">{{ $employee->department }}</p>
            </div>

            <!-- Rating Form -->
            <form action="{{ route('public.rate.store', $employee->uuid) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <ul class="list-disc list-inside text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Identity Section -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Data Penilai</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                NIK <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16"
                                pattern="[0-9]{16}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="16 digit NIK" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" pattern="[0-9]{10,15}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="08123456789" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jenis Kelamin
                            </label>
                            <select name="gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Hubungan dengan Pasien
                            </label>
                            <input type="text" name="relationship_to_patient"
                                value="{{ old('relationship_to_patient') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Pasien / Keluarga / dll">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Kunjungan
                            </label>
                            <input type="date" name="visit_date" value="{{ old('visit_date', date('Y-m-d')) }}"
                                max="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Rating Section -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Penilaian Layanan</h2>

                    <!-- Overall Satisfaction -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Kepuasan Keseluruhan <span class="text-red-500">*</span>
                        </label>
                        <div class="star-rating flex flex-row-reverse justify-center gap-2">
                            @for ($i = 5; $i >= 1; $i--)
                                <input type="radio" id="overall_{{ $i }}" name="overall_satisfaction"
                                    value="{{ $i }}" {{ old('overall_satisfaction') == $i ? 'checked' : '' }}
                                    required>
                                <label for="overall_{{ $i }}">★</label>
                            @endfor
                        </div>
                    </div>

                    <!-- Optional Aspects -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3 text-center">
                                Keramahan
                            </label>
                            <div class="star-rating flex flex-row-reverse justify-center gap-1">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="friendliness_{{ $i }}" name="friendliness"
                                        value="{{ $i }}">
                                    <label for="friendliness_{{ $i }}" class="text-xl">★</label>
                                @endfor
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3 text-center">
                                Profesionalisme
                            </label>
                            <div class="star-rating flex flex-row-reverse justify-center gap-1">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="professionalism_{{ $i }}" name="professionalism"
                                        value="{{ $i }}">
                                    <label for="professionalism_{{ $i }}" class="text-xl">★</label>
                                @endfor
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3 text-center">
                                Kecepatan Layanan
                            </label>
                            <div class="star-rating flex flex-row-reverse justify-center gap-1">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="service_speed_{{ $i }}" name="service_speed"
                                        value="{{ $i }}">
                                    <label for="service_speed_{{ $i }}" class="text-xl">★</label>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comment Section -->
                <div class="border-b pb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Komentar / Saran (Opsional)
                    </label>
                    <textarea name="comment" rows="4" maxlength="500"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Tuliskan komentar atau saran Anda...">{{ old('comment') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Maksimal 500 karakter</p>
                </div>

                <!-- Consent -->
                <div class="flex items-start">
                    <input type="checkbox" name="consent_given" id="consent"
                        class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                    <label for="consent" class="ml-2 text-sm text-gray-700">
                        Saya setuju bahwa data yang saya berikan akan digunakan untuk evaluasi pelayanan rumah sakit.
                        <span class="text-red-500">*</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-lg">
                        Kirim Penilaian
                    </button>
                </div>

                <p class="text-xs text-center text-gray-500 mt-4">
                    Data NIK dan nomor telepon Anda akan dienkripsi dan dijaga kerahasiaannya.
                </p>
            </form>
        </div>
    </div>
@endsection
