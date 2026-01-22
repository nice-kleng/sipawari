<x-filament-panels::page class="!p-0">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">QR Code Penilaian</h1>
            <p class="text-lg text-gray-600 dark:text-gray-300 font-semibold">{{ $this->record->name }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span class="inline-block">{{ $this->record->position }}</span>
                <span class="text-gray-400 dark:text-gray-500 mx-2">•</span>
                <span class="inline-block">{{ $this->record->department }}</span>
            </p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column - QR Code -->
            <div class="flex flex-col items-center justify-start">
                <div class="w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mb-6">
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 text-center">Kode QR Penilaian</p>
                    </div>
                    <div class="flex justify-center bg-gradient-to-br from-gray-50 dark:from-gray-700 to-white dark:to-gray-800 p-6 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                        @if ($this->record->qr_code_path && Storage::disk('public')->exists($this->record->qr_code_path))
                            <img src="{{ Storage::url($this->record->qr_code_path) }}"
                                alt="QR Code {{ $this->record->name }}"
                                class="w-72 h-72 print-qr-code">
                        @else
                            <div class="w-72 h-72 flex items-center justify-center bg-gray-100 dark:bg-gray-600 rounded-lg">
                                <div class="text-center">
                                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4v2m0 4v2M6 9v12m12-12v12M9 5h6M3 5h18a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">QR Code tidak tersedia</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Information & Actions -->
            <div class="flex flex-col gap-6">
                <!-- Rating URL Section -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">
                        URL Penilaian
                    </label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="text"
                            value="{{ $this->record->rating_url ?? '#' }}"
                            readonly
                            id="ratingUrl"
                            class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm font-mono text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="button"
                            onclick="copyToClipboard()"
                            class="sm:flex-shrink-0 px-6 py-3 bg-blue-700 hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-black dark:text-white font-bold rounded-lg active:bg-blue-900 dark:active:bg-blue-800 transition-colors duration-200 flex items-center justify-center gap-2 whitespace-nowrap shadow-lg border border-blue-800 dark:border-blue-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span>Salin</span>
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    @if ($this->record->qr_code_path && Storage::disk('public')->exists($this->record->qr_code_path))
                        <a href="{{ Storage::url($this->record->qr_code_path) }}"
                            download="QR_{{ $this->record->employee_code }}.png"
                            class="w-full bg-green-700 hover:bg-green-800 dark:bg-green-600 dark:hover:bg-green-700 text-black dark:text-white font-bold py-3 px-6 rounded-lg active:bg-green-900 dark:active:bg-green-800 transition-colors duration-200 flex items-center justify-center gap-2 shadow-lg border border-green-800 dark:border-green-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh QR Code</span>
                        </a>
                    @endif
                </div>

                <!-- Instructions -->
                <div class="bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 dark:border-blue-400 rounded-lg p-6">
                    <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Cara Penggunaan
                    </h3>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800 dark:text-blue-200 ml-1">
                        <li>Unduh atau cetak QR Code ini</li>
                        <li>Tempelkan di tempat yang mudah terlihat</li>
                        <li>Pasien/keluarga dapat memindai untuk memberikan penilaian</li>
                        <li>Penilaian akan langsung masuk ke sistem</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            * {
                background: transparent !important;
                color: black !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            body > * {
                display: none !important;
            }

            .print-qr-code {
                display: block !important;
                position: fixed !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                width: 300px !important;
                height: 300px !important;
                z-index: 9999 !important;
            }

            @page {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>

    <!-- Copy Script -->
    <script>
        function copyToClipboard() {
            const input = document.getElementById('ratingUrl');
            const originalValue = input.value;

            input.select();
            input.setSelectionRange(0, 99999);

            try {
                document.execCommand('copy');

                // Show feedback
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg><span>Disalin!</span>';

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 2000);
            } catch (err) {
                alert('Gagal menyalin URL');
            }
        }
    </script>
</x-filament-panels::page>
