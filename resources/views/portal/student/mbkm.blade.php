@extends('layouts.portal')

@section('title', 'Program MBKM & Konversi 20 SKS - Mahasiswa')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/10 text-indigo-400 border border-indigo-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </span>
                <h1 class="text-2xl font-bold text-white tracking-wide">Merdeka Belajar Kampus Merdeka (MBKM)</h1>
            </div>
            <p class="text-sm text-slate-400 mt-1">Registrasi program magang/studi independen dan konversi hingga 20 SKS mata kuliah ekuivalen.</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $isEligible ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' }}">
                Status: {{ $isEligible ? 'Eligible (Semester ' . $student->current_semester . ' >= 5)' : 'Belum Memenuhi Syarat (Min. Sem 5)' }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Registration Form -->
        <div class="lg:col-span-1 bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Pendaftaran Program MBKM
            </h3>

            @if (!$isEligible)
                <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs leading-relaxed">
                    <strong>Perhatian:</strong> Sesuai regulasi akademik UPH & Kemendikbudristek, program MBKM konversi 20 SKS hanya dapat diambil oleh mahasiswa aktif pada <strong>Semester 5 ke atas</strong>.
                </div>
            @endif

            <form action="{{ route('portal.student.mbkm.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Jenis Aktivitas MBKM</label>
                    <select name="program_type" required {{ !$isEligible ? 'disabled' : '' }} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                        <option value="magang_industri">Magang Industri / Bersertifikat (MSIB)</option>
                        <option value="studi_independen">Studi Independen Bersertifikat</option>
                        <option value="pertukaran_mahasiswa">Pertukaran Mahasiswa Merdeka (PMM)</option>
                        <option value="riset">Riset / Riset Kolaboratif</option>
                        <option value="proyek_kemanusiaan">Proyek Kemanusiaan / Pengabdian</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Mitra / Perusahaan / Kampus</label>
                    <input type="text" name="partner_name" required placeholder="Contoh: PT Tokopedia / Google Indonesia" {{ !$isEligible ? 'disabled' : '' }} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat / Lokasi Mitra</label>
                    <input type="text" name="partner_address" placeholder="Jakarta Selatan" {{ !$isEligible ? 'disabled' : '' }} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Mentor Mitra</label>
                        <input type="text" name="mentor_name" placeholder="Nama Pembimbing" {{ !$isEligible ? 'disabled' : '' }} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kontak Mentor</label>
                        <input type="text" name="mentor_contact" placeholder="Email / HP" {{ !$isEligible ? 'disabled' : '' }} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" required {{ !$isEligible ? 'disabled' : '' }} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" required {{ !$isEligible ? 'disabled' : '' }} class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Surat Penerimaan / Logbook (PDF/ZIP max 10MB)</label>
                    <input type="file" name="logbook_document" accept=".pdf,.zip" {{ !$isEligible ? 'disabled' : '' }} class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-700 file:text-slate-200 hover:file:bg-slate-600">
                </div>

                <button type="submit" {{ !$isEligible ? 'disabled' : '' }} class="w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-sm shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    Ajukan Pendaftaran MBKM
                </button>
            </form>
        </div>

        <!-- Right: Registered Programs & Converted Courses -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h3 class="text-base font-bold text-white mb-4 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Riwayat Program & Konversi SKS Anda
                    </span>
                    <span class="text-xs text-slate-400">Maksimum Konversi: <strong>20 SKS</strong></span>
                </h3>

                @forelse ($programs as $prog)
                    <div class="mb-6 last:mb-0 rounded-2xl bg-slate-950/50 border border-slate-800 p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800/80 pb-3">
                            <div>
                                <span class="text-xs font-mono font-bold text-indigo-400 uppercase tracking-wider">
                                    {{ str_replace('_', ' ', $prog->program_type) }}
                                </span>
                                <h4 class="text-lg font-bold text-white">{{ $prog->partner_name }}</h4>
                                <p class="text-xs text-slate-400">
                                    Periode: {{ $prog->start_date->format('d M Y') }} s/d {{ $prog->end_date->format('d M Y') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($prog->status === 'approved')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        Disetujui Kaprodi ({{ $prog->total_converted_sks }} SKS)
                                    </span>
                                @elseif ($prog->status === 'rejected')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                        Menunggu Verifikasi Kaprodi
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Converted Courses Table -->
                        @if ($prog->conversions->count() > 0)
                            <div class="space-y-2">
                                <h5 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Mata Kuliah Hasil Konversi Kaprodi:</h5>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs text-slate-300">
                                        <thead class="bg-slate-900 border-b border-slate-800 text-slate-400">
                                            <tr>
                                                <th class="py-2 px-3">Kode</th>
                                                <th class="py-2 px-3">Mata Kuliah Ekuivalen</th>
                                                <th class="py-2 px-3 text-center">SKS</th>
                                                <th class="py-2 px-3 text-center">Nilai Angka</th>
                                                <th class="py-2 px-3 text-center">Huruf</th>
                                                <th class="py-2 px-3 text-center">Bobot</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-800/60">
                                            @foreach ($prog->conversions as $conv)
                                                <tr>
                                                    <td class="py-2 px-3 font-mono text-amber-400 font-semibold">{{ $conv->course->code }}</td>
                                                    <td class="py-2 px-3 text-white font-medium">{{ $conv->course->name }}</td>
                                                    <td class="py-2 px-3 text-center">{{ $conv->equivalent_sks }}</td>
                                                    <td class="py-2 px-3 text-center font-bold text-emerald-400">{{ $conv->converted_numeric_score }}</td>
                                                    <td class="py-2 px-3 text-center font-black text-amber-300">{{ $conv->converted_letter_grade }}</td>
                                                    <td class="py-2 px-3 text-center font-mono">{{ number_format($conv->grade_point, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if ($prog->approval_notes)
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300">
                                <strong class="text-amber-400">Catatan Kaprodi:</strong> {{ $prog->approval_notes }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-500 text-sm">
                        Belum ada pendaftaran program MBKM. Ajukan melalui formulir di sebelah kiri.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
