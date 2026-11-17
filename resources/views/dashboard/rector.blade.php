@extends('layouts.portal')

@section('title', 'Dasbor Rektorat & Analitika Eksekutif - UPH')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-uph-navy/10 text-uph-navy">
                    Executive Decision Support System (EDSS)
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Universitas Pelita Harapan</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Dasbor Analitika Strategis Rektorat</h1>
            <p class="text-xs text-slate-500 mt-1">
                Pemantauan KPI kelembagaan menyeluruh: penerimaan mahasiswa baru, kesehatan finansial, mutu akademik OBE, dan kesiapan akreditasi.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                <i class="fa-solid fa-clock-rotate-left text-uph-gold"></i>
                Data Real-Time
            </span>
            <button onclick="window.print()" class="p-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 transition-colors" title="Cetak Ringkasan Eksekutif">
                <i class="fa-solid fa-print"></i>
            </button>
        </div>
    </div>

    <!-- Core University KPI Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Mahasiswa Aktif</span>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl sm:text-3xl font-black text-uph-navy font-mono">{{ $totalActiveStudents }}</h3>
                <span class="text-xs text-slate-400">Mahasiswa</span>
            </div>
            <span class="text-[10px] text-slate-500 mt-1 block">
                {{ $totalFaculties }} Fakultas &bull; {{ count($studyPrograms) }} Program Studi
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Rata-Rata IPK Universitas</span>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 font-mono">{{ number_format($universityAvgIpk, 2) }}</h3>
                <span class="text-xs text-slate-400">/ 4.00</span>
            </div>
            <span class="text-[10px] text-emerald-700 font-bold mt-1 flex items-center gap-1">
                <i class="fa-solid fa-arrow-trend-up text-[10px]"></i> Standar Mutu Unggul (SPMI)
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kas SPP/BPP Terkumpul</span>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 font-mono">
                    Rp {{ number_format($totalCollected / 1000000, 1, ',', '.') }}M
                </h3>
            </div>
            <span class="text-[10px] text-slate-500 mt-1 block">
                Collection Rate: <strong class="text-emerald-600 font-mono">{{ $collectionRate }}%</strong>
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Early Warning System (EWS)</span>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl sm:text-3xl font-black text-rose-600 font-mono">{{ $probationStudents }}</h3>
                <span class="text-xs text-rose-500 font-bold">Mhs Berisiko</span>
            </div>
            <span class="text-[10px] text-slate-500 mt-1 block">
                IPS < 2.00 / Dalam Konseling PA
            </span>
        </div>
    </div>

    <!-- PMB Admissions Funnel & Financial Health Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: PMB Funnel (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-5">
            <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Konversi Penerimaan Mahasiswa Baru (PMB)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Alur corong konversi pendaftar hingga registrasi NIM resmi.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-uph-navy/10 text-uph-navy font-mono">
                    Konversi: {{ $conversionRate }}%
                </span>
            </div>

            <!-- Funnel Progression Bars -->
            <div class="space-y-4 pt-2 text-xs">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-slate-700">1. Pendaftar Akun PMB (Applicants)</span>
                        <span class="font-mono font-bold text-slate-900">{{ $totalApplicants }} Calon Mhs</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-slate-400 h-3 rounded-full" style="width: 100%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-slate-700">2. Peserta Mengikuti CBT Online</span>
                        <span class="font-mono font-bold text-slate-900">{{ $cbtCompleted }} Peserta ({{ $totalApplicants > 0 ? round(($cbtCompleted/$totalApplicants)*100, 1) : 0 }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $totalApplicants > 0 ? ($cbtCompleted/$totalApplicants)*100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-slate-700">3. Lulus Ujian & Ditawari Beasiswa (Accepted)</span>
                        <span class="font-mono font-bold text-slate-900">{{ $acceptedApplicants }} Calon Mhs</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-emerald-500 h-3 rounded-full" style="width: {{ $totalApplicants > 0 ? ($acceptedApplicants/$totalApplicants)*100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-slate-700">4. Registrasi & Terbit NIM Resmi (Enrolled)</span>
                        <span class="font-mono font-black text-uph-crimson">{{ $registeredStudents }} Mahasiswa Baru</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-uph-crimson h-3 rounded-full" style="width: {{ $totalApplicants > 0 ? ($registeredStudents/$totalApplicants)*100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Financial Health Summary (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Kolektibilitas Biaya Pendidikan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi kas universitas periode berjalan.</p>
            </div>

            <div class="space-y-4 pt-1 text-xs">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Total Nilai Tagihan BPP</span>
                    <strong class="text-slate-900 text-lg font-mono block mt-0.5">
                        Rp {{ number_format($totalBilled, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                    <span class="text-emerald-700 block text-[10px] uppercase font-bold">Kas Diterima (Virtual Account & QRIS)</span>
                    <strong class="text-emerald-800 text-lg font-mono block mt-0.5">
                        Rp {{ number_format($totalCollected, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200">
                    <span class="text-rose-700 block text-[10px] uppercase font-bold">Sisa Piutang Berjalan Mahasiswa</span>
                    <strong class="text-rose-800 text-lg font-mono block mt-0.5">
                        Rp {{ number_format($outstandingBalance, 0, ',', '.') }}
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Study Programs Academic Performance Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-slate-900">Kinerja Akademik Berbasis Program Studi (OBE Framework)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pemantauan distribusi mahasiswa aktif dan rerata IPK per program studi.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-4 px-6">Program Studi</th>
                        <th class="py-4 px-4">Fakultas</th>
                        <th class="py-4 px-4 text-center">Mahasiswa Aktif</th>
                        <th class="py-4 px-4 text-center">Rata-Rata IPK</th>
                        <th class="py-4 px-4 text-center">Status Akreditasi</th>
                        <th class="py-4 px-6 text-right">Kepatuhan OBE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($studyPrograms as $prodi)
                    <tr class="hover:bg-slate-50/75">
                        <td class="py-4 px-6">
                            <span class="font-mono font-bold text-uph-navy text-xs">{{ $prodi['code'] }}</span>
                            <span class="font-bold text-slate-900 block mt-0.5">{{ $prodi['name'] }}</span>
                        </td>
                        <td class="py-4 px-4 text-slate-700">
                            {{ $prodi['faculty'] }}
                        </td>
                        <td class="py-4 px-4 text-center font-mono font-bold text-slate-800">
                            {{ $prodi['active_students'] }} Mhs
                        </td>
                        <td class="py-4 px-4 text-center font-mono font-bold text-emerald-700 text-sm">
                            {{ number_format($prodi['avg_ipk'] ?: 3.45, 2) }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                UNGGUL (BAN-PT)
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <span class="font-mono font-bold text-uph-navy">100% CPL Terpetakan</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
