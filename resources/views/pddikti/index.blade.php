@extends('layouts.portal')

@section('title', 'PDDIKTI Neo Feeder Sync - UPH')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-uph-navy/10 text-uph-navy">
                    Kementerian Pendidikan Tinggi RI
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">PDDIKTI Neo Feeder 2.0 Web Services</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Pusat Sinkronisasi PDDIKTI Neo Feeder</h1>
            <p class="text-xs text-slate-500 mt-1">
                Pengiriman data terintegrasi berkala: biodata mahasiswa, penugasan dosen, jadwal kelas, ledger nilai KHS, dan AKM.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200 shadow-sm">
                <i class="fa-solid fa-satellite-dish text-emerald-600"></i>
                Neo Feeder Endpoint Terhubung
            </span>
        </div>
    </div>

    <!-- 5 Sync Entity Action Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Mahasiswa -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-xs">Data Mahasiswa</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Biodata & Histori Studi</p>
                <div class="text-xl font-black text-slate-800 font-mono mt-2">
                    {{ $studentCount }} <span class="text-xs font-normal text-slate-400">data</span>
                </div>
            </div>
            <form method="POST" action="/pddikti/sync/Mahasiswa" class="mt-4">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-[11px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-rotate"></i> Sinkron
                </button>
            </form>
        </div>

        <!-- 2. Dosen -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-xs">Penugasan Dosen</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">NIDN & Homebase Prodi</p>
                <div class="text-xl font-black text-slate-800 font-mono mt-2">
                    {{ $lecturerCount }} <span class="text-xs font-normal text-slate-400">data</span>
                </div>
            </div>
            <form method="POST" action="/pddikti/sync/Dosen" class="mt-4">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-[11px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-rotate"></i> Sinkron
                </button>
            </form>
        </div>

        <!-- 3. KelasKuliah -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-door-open"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-xs">Kelas Kuliah</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Seksi & Jadwal Ruang</p>
                <div class="text-xl font-black text-slate-800 font-mono mt-2">
                    {{ $classCount }} <span class="text-xs font-normal text-slate-400">data</span>
                </div>
            </div>
            <form method="POST" action="/pddikti/sync/KelasKuliah" class="mt-4">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-[11px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-rotate"></i> Sinkron
                </button>
            </form>
        </div>

        <!-- 4. Nilai -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-table-cells"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-xs">Nilai Perkuliahan</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">KRS & Huruf Mutu</p>
                <div class="text-xl font-black text-slate-800 font-mono mt-2">
                    {{ $gradeCount }} <span class="text-xs font-normal text-slate-400">data</span>
                </div>
            </div>
            <form method="POST" action="/pddikti/sync/Nilai" class="mt-4">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-[11px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-rotate"></i> Sinkron
                </button>
            </form>
        </div>

        <!-- 5. AKM -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg mb-3">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-xs">Aktivitas Mhs (AKM)</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">IPS, IPK, SKS Semester</p>
                <div class="text-xl font-black text-slate-800 font-mono mt-2">
                    {{ $akmCount }} <span class="text-xs font-normal text-slate-400">data</span>
                </div>
            </div>
            <form method="POST" action="/pddikti/sync/AKM" class="mt-4">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-[11px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-rotate"></i> Sinkron
                </button>
            </form>
        </div>
    </div>

    <!-- Sync Transaction Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-slate-900">Buku Log Transaksi Sinkronisasi Neo Feeder</h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan audit riwayat transaksi pengiriman data ke server PDDikti.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-4 px-6">Batch Reference</th>
                        <th class="py-4 px-4">Tipe Entitas</th>
                        <th class="py-4 px-4 text-center">Total</th>
                        <th class="py-4 px-4 text-center">Sukses</th>
                        <th class="py-4 px-4 text-center">Gagal</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Waktu Sinkron</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($syncLogs as $log)
                    <tr class="hover:bg-slate-50/75">
                        <td class="py-4 px-6 font-mono font-bold text-slate-800 text-xs">
                            {{ $log->batch_reference }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded font-bold text-[10px] uppercase bg-slate-100 text-slate-700">
                                {{ $log->entity_type }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-center font-mono font-bold text-slate-800">
                            {{ $log->total_records }}
                        </td>
                        <td class="py-4 px-4 text-center font-mono font-bold text-emerald-600">
                            {{ $log->success_count }}
                        </td>
                        <td class="py-4 px-4 text-center font-mono font-bold text-rose-600">
                            {{ $log->failed_count }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ strtoupper($log->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right font-mono text-slate-500 text-[11px]">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 italic">
                            Belum ada riwayat pengiriman transaksi ke Neo Feeder PDDIKTI.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
