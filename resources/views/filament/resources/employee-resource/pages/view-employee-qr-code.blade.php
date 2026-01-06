<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    QR Code Penilaian
                </h2>
                <p class="text-gray-600">
                    {{ $this->record->name }}
                </p>
                <p class="text-sm text-gray-500">
                    {{ $this->record->position }} - {{ $this->record->department }}
                </p>
            </div>

            <!-- QR Code Display -->
            <div class="flex justify-center mb-6">
                <div class="bg-white p-4 rounded-lg border-4 border-gray-200">
                    @if ($this->record->qr_code_path && Storage::disk('public')->exists($this->record->qr_code_path))
                        <img src="{{ Storage::url($this->record->qr_code_path) }}" alt="QR Code {{ $this->record->name }}"
                            class="w-64 h-64">
                    @else
                        <div class="w-64 h-64 flex items-center justify-center bg-gray-100 rounded">
                            <p class="text-gray-500">QR Code tidak tersedia</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Rating URL -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    URL Penilaian
                </label>
                <div class="flex gap-2">
                    <input type="text" value="{{ $this->record->rating_url }}" readonly id="ratingUrl"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm">
                    <button type="button" onclick="copyToClipboard()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Salin
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <a href="{{ Storage::url($this->record->qr_code_path) }}"
                    download="QR_{{ $this->record->employee_code }}.png"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-3 px-6 rounded-lg transition">
                    Unduh QR Code
                </a>

                <button type="button" onclick="window.print()"
                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-3 px-6 rounded-lg transition">
                    Cetak QR Code
                </button>
            </div>

            <!-- Instructions -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-semibold text-blue-900 mb-2">Cara Penggunaan:</h3>
                <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                    <li>Unduh atau cetak QR Code ini</li>
                    <li>Tempelkan di tempat yang mudah terlihat</li>
                    <li>Pasien/keluarga dapat memindai untuk memberikan penilaian</li>
                    <li>Penilaian akan langsung masuk ke sistem</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
            }
        }
    </style>

    <!-- Copy Script -->
    <script>
        function copyToClipboard() {
            const input = document.getElementById('ratingUrl');
            input.select();
            input.setSelectionRange(0, 99999);
            document.execCommand('copy');

            alert('URL berhasil disalin!');
        }
    </script>
</x-filament-panels::page>
