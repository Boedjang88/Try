@extends('layouts.portal', ['title' => 'Portal Mahasiswa - SIAKAD & LMS'])

@section('content')
    <!-- Main Student Dashboard Workspace -->
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-uph-navy dark:text-white font-display">Dashboard Mahasiswa</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Semester Ganjil 2026/2027 &bull; Program Studi S1 Informatika</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Status: Mahasiswa Aktif
                </span>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-kpi-card title="Indeks Prestasi Kumulatif" value="3.88" subtitle="Maks. 24 SKS Semester Ini" icon="fa-trophy" trend="up" />
            <x-kpi-card title="Total SKS Ditempuh" value="114 / 144" subtitle="Sisa 30 SKS menuju Kelulusan" icon="fa-graduation-cap" />
            <x-kpi-card title="Persentase Presensi" value="96.4%" subtitle="Memenuhi batas minimal 75%" icon="fa-check-circle" trend="up" />
            <x-kpi-card title="Status Finansial (BAUK)" value="Lunas" subtitle="Bebas Tagihan SPP & SKS" icon="fa-credit-card" />
        </div>

        <!-- Academic Table: Classes Today -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-uph-navy dark:text-uph-gold flex items-center gap-2">
                    <i class="fa fa-calendar text-uph-crimson"></i> Jadwal Kuliah Hari Ini (Senin)
                </h3>
                <a href="/portal/student/krs" class="text-xs text-uph-navy dark:text-uph-gold font-bold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-calendar-check"></i> Buka KRS War
                </a>
            </div>

            <x-data-table :headers="['Waktu', 'Kode MK', 'Mata Kuliah', 'SKS', 'Kelas', 'Ruang', 'Dosen Pengampu', 'Status Presensi']">
                <tr>
                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">08:00 - 10:30</td>
                    <td class="px-4 py-3 font-mono font-bold text-uph-navy dark:text-uph-gold">CS304</td>
                    <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">Kecerdasan Buatan & Deep Learning</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">3 SKS</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">IF-A</td>
                    <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">D502 (Smart Lab)</td>
                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">Dr. Eng. Handi Kusuma, M.Kom.</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-md bg-emerald-50 dark:bg-emerald-950/40 px-2 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                            <i class="fa fa-check mr-1"></i> Hadir
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">11:00 - 13:30</td>
                    <td class="px-4 py-3 font-mono font-bold text-uph-navy dark:text-uph-gold">CS309</td>
                    <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">Rekayasa Perangkat Lunak Skala Besar</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">3 SKS</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">IF-B</td>
                    <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">D401</td>
                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">Prof. Dr. Ir. Edwin Sutanto, M.Sc.</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-md bg-sky-50 dark:bg-sky-950/40 px-2 py-1 text-xs font-bold text-sky-700 dark:text-sky-400">
                            <i class="fa fa-clock-o mr-1"></i> Mendatang
                        </span>
                    </td>
                </tr>
            </x-data-table>
        </div>
    </div>
@endsection
