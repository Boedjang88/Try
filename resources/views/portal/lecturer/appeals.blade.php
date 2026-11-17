@extends('layouts.portal')

@section('title', 'Dosen - Sanggah Nilai Mahasiswa (Grade Appeal)')

@section('content')
<div class="space-y-6" x-data="{
    openModal: false,
    selectedAppeal: null,
    actionType: 'request_unlock',
    openRespondModal(appeal) {
        this.selectedAppeal = appeal;
        this.openModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-gradient-to-br from-amber-500/20 to-orange-500/10 text-amber-400 border border-amber-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
                <h1 class="text-2xl font-bold text-white tracking-wide">Pusat Sanggah Nilai (Grade Appeal Review)</h1>
            </div>
            <p class="text-sm text-slate-400 mt-1">Evaluasi transparansi nilai hasil perkuliahan dan verifikasi bukti sanggahan sebelum sinkronisasi PDDIKTI.</p>
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

    <!-- Appeals Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Mahasiswa</th>
                        <th class="py-3 px-4">Mata Kuliah & Kelas</th>
                        <th class="py-3 px-4">Alasan & Bukti</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($appeals as $app)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $app->student->user->full_name }}</div>
                                <div class="text-xs font-mono text-amber-400">{{ $app->student->nim }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-white">{{ $app->krsItem->classSection->course->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">
                                    {{ $app->krsItem->classSection->course->code }} • {{ $app->krsItem->classSection->section_code }}
                                    (Nilai: <strong class="text-amber-400">{{ $app->krsItem->letter_grade }} / {{ $app->krsItem->final_numeric_score }}</strong>)
                                </div>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <p class="text-xs text-slate-300 truncate" title="{{ $app->reason }}">{{ $app->reason }}</p>
                                @if ($app->evidence_file_path)
                                    <a href="{{ route('portal.storage.download.appeal-evidence', $app->id) }}" class="inline-flex items-center gap-1 text-[11px] text-indigo-400 hover:text-indigo-300 mt-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <span>Unduh Bukti Mahasiswa</span>
                                    </a>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($app->status === 'unlock_requested')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                        Menunggu Unlock Kaprodi
                                    </span>
                                @elseif ($app->status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                        Ditolak Dosen
                                    </span>
                                @elseif ($app->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        Selesai / Direvisi
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/30">
                                        Baru Masuk
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if ($app->status === 'submitted')
                                    <button type="button" 
                                            @click="openRespondModal({{ json_encode($app) }})"
                                            class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs shadow-md transition-all">
                                        Tanggapi Sanggahan
                                    </button>
                                @else
                                    <span class="text-xs text-slate-500">Telah Ditanggapi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-500 text-sm">
                                Belum ada pengajuan sanggah nilai untuk kelas yang Anda ampu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $appeals->links() }}
        </div>
    </div>

    <!-- Response Modal -->
    <div x-show="openModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         style="display: none;">
        <div @click.away="openModal = false" class="relative w-full max-w-lg bg-slate-900 border border-slate-700 rounded-3xl p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                Tanggapi Sanggah Nilai Mahasiswa
            </h3>
            <p class="text-xs text-slate-400 mb-4" x-text="selectedAppeal ? selectedAppeal.student?.user?.full_name + ' (' + selectedAppeal.student?.nim + ')' : ''"></p>

            <form :action="'/portal/lecturer/appeal/' + (selectedAppeal ? selectedAppeal.id : '') + '/respond'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Keputusan Dosen Pengampu</label>
                    <select name="action_type" x-model="actionType" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-amber-400">
                        <option value="request_unlock">Terima Sanggahan & Minta Kaprodi Buka Kunci Nilai</option>
                        <option value="reject">Tolak Sanggahan (Nilai Tetap Sah)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Penjelasan Dosen</label>
                    <textarea name="response_note" rows="3" required placeholder="Tuliskan argumen koreksi atau alasan penolakan..." class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-amber-400"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" @click="openModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs shadow-lg transition-all">
                        Kirim Tanggapan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
