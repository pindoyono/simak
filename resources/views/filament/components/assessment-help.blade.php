<div class="space-y-8">
    {{-- Introduction --}}
    <div class="text-center">
        <div class="mx-auto w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-4">
            <x-heroicon-s-academic-cap class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Panduan Assessment Wizard
        </h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            Ikuti panduan ini untuk melengkapi penilaian sekolah dengan mudah dan akurat
        </p>
    </div>

    {{-- Steps Guide --}}
    <div class="space-y-6">
        {{-- Step 1 --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-start space-x-4">
                <div
                    class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center font-semibold">
                    1
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Informasi Dasar
                    </h3>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <p>• <strong>Pilih Sekolah:</strong> Tentukan sekolah yang akan dinilai dari daftar yang
                            tersedia</p>
                        <p>• <strong>Periode Penilaian:</strong> Pilih periode penilaian yang sedang aktif</p>
                        <p>• <strong>Tanggal Penilaian:</strong> Atur tanggal pelaksanaan penilaian</p>
                        <p>• <strong>Catatan Awal:</strong> Tambahkan informasi atau konteks tambahan jika diperlukan
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2 --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-start space-x-4">
                <div
                    class="flex-shrink-0 w-8 h-8 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center font-semibold">
                    2
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Penilaian Skor
                    </h3>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <p>• <strong>Baca Kriteria:</strong> Klik tombol "Detail Kriteria" untuk memahami standar
                            penilaian</p>
                        <p>• <strong>Berikan Skor:</strong> Pilih skor yang sesuai berdasarkan kondisi aktual sekolah
                        </p>
                        <p>• <strong>Bukti Pendukung:</strong> Jelaskan bukti atau dasar pemberian skor</p>
                        <p>• <strong>Catatan:</strong> Tambahkan catatan khusus untuk setiap indikator</p>
                        <p class="text-amber-600 dark:text-amber-400">⚠️ <strong>Tips:</strong> Berikan penilaian yang
                            objektif dan berdasarkan fakta</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3 --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-start space-x-4">
                <div
                    class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-full flex items-center justify-center font-semibold">
                    3
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Unggah Dokumen
                    </h3>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <p>• <strong>Format File:</strong> PDF, Word, atau gambar (JPG, PNG)</p>
                        <p>• <strong>Ukuran Maksimal:</strong> 10MB per file, maksimal 5 file per indikator</p>
                        <p>• <strong>Deskripsi:</strong> Jelaskan relevansi dokumen dengan indikator</p>
                        <p>• <strong>Organisasi:</strong> Upload dokumen pada indikator yang sesuai</p>
                        <p class="text-blue-600 dark:text-blue-400">💡 <strong>Saran:</strong> Dokumen pendukung
                            memperkuat validitas penilaian</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 4 --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-start space-x-4">
                <div
                    class="flex-shrink-0 w-8 h-8 bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400 rounded-full flex items-center justify-center font-semibold">
                    4
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Review & Submit
                    </h3>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <p>• <strong>Periksa Data:</strong> Pastikan semua informasi sudah benar</p>
                        <p>• <strong>Skor Total:</strong> Lihat hasil perhitungan skor dan grade akhir</p>
                        <p>• <strong>Komentar Akhir:</strong> Tambahkan kesimpulan atau rekomendasi</p>
                        <p>• <strong>Submit:</strong> Kirim penilaian untuk proses review</p>
                        <p class="text-red-600 dark:text-red-400">⚠️ <strong>Penting:</strong> Setelah submit, penilaian
                            tidak dapat diubah</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Best Practices --}}
    <div
        class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center">
            <x-heroicon-s-light-bulb class="w-5 h-5 mr-2" />
            Best Practices
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800 dark:text-blue-200">
            <div class="space-y-2">
                <p>✅ Baca kriteria penilaian dengan teliti</p>
                <p>✅ Gunakan bukti konkret dalam penilaian</p>
                <p>✅ Dokumentasikan dengan foto/dokumen</p>
                <p>✅ Berikan catatan yang jelas dan spesifik</p>
            </div>
            <div class="space-y-2">
                <p>✅ Konsisten dalam penerapan standar</p>
                <p>✅ Objektif dan tidak bias</p>
                <p>✅ Lengkapi semua indikator wajib</p>
                <p>✅ Review sebelum submit final</p>
            </div>
        </div>
    </div>

    {{-- Scoring Guide --}}
    <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <x-heroicon-s-star class="w-5 h-5 mr-2 text-yellow-500" />
            Panduan Penilaian Skor
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 text-gray-900 dark:text-gray-100">Skor</th>
                        <th class="text-left py-2 text-gray-900 dark:text-gray-100">Kategori</th>
                        <th class="text-left py-2 text-gray-900 dark:text-gray-100">Deskripsi</th>
                        <th class="text-left py-2 text-gray-900 dark:text-gray-100">Persentase</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 dark:text-gray-400">
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="py-2 font-medium">4</td>
                        <td class="py-2">Sangat Baik</td>
                        <td class="py-2">Memenuhi semua kriteria dengan sangat baik</td>
                        <td class="py-2 text-green-600 dark:text-green-400">≥ 85%</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="py-2 font-medium">3</td>
                        <td class="py-2">Baik</td>
                        <td class="py-2">Memenuhi sebagian besar kriteria dengan baik</td>
                        <td class="py-2 text-blue-600 dark:text-blue-400">70-84%</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="py-2 font-medium">2</td>
                        <td class="py-2">Cukup</td>
                        <td class="py-2">Memenuhi kriteria dasar</td>
                        <td class="py-2 text-yellow-600 dark:text-yellow-400">55-69%</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-medium">1</td>
                        <td class="py-2">Kurang</td>
                        <td class="py-2">Belum memenuhi kriteria yang diharapkan</td>
                        <td class="py-2 text-red-600 dark:text-red-400">
                            < 55%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Contact Support --}}
    <div class="text-center p-6 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <x-heroicon-o-question-mark-circle class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2" />
        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Butuh Bantuan?</h4>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Jika Anda mengalami kesulitan atau memiliki pertanyaan, silakan hubungi tim support atau admin sistem.
        </p>
    </div>
</div>
