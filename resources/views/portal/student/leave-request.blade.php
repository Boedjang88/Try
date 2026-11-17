@extends('layouts.portal')

@section('content')
<div class="space-y-6" x-data="{
    rejectModalOpen: false,
    selectedRequestId: null,
    openRejectModal(id) {
        this.selectedRequestId = id;
        this.rejectModalOpen = true;
    }
}">

    <!-- Top Banner -->
    <div class="bg-gradient-to-r from-uph-navy via-uph-navy-dark to-slate-900 rounded-2xl p-6 text-white shadow-xl border border-white/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-extrabold uppercase tracking-widest">
                    <i class="fa-solid fa-user-clock"></i> Administrasi Akademik
                </span>
                <span class="text-xs text-slate-300 font-mono">&bull; Periode {{ $activePeriod->name ?? 'Aktif' }}</span>
            </div>
            <h1 class="text-2xl font-display font-black text-white">Pengajuan &amp; Verifikasi Cuti Akademik</h1>
            <p class="text-xs text-slate-300 mt-1">
                Layanan penghentian studi sementara resmi mahasiswa dengan persetujuan bertingkat Dosen PA dan Kaprodi.
            </p>
        </div>

        @if($student)
            <div class="bg-white/10 backdrop-blur-md px-5 py-3 rounded-xl border border-white/20 text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Status Akademik Saat Ini</span>
                <span class="text-sm font-bold {{ $student->academic_status === 'on_leave' ? 'text-amber-400' : 'text-emerald-400' }}">
                    {{ strtoupper($student->academic_status) }}
                </span>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-700 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-blue-600 text-base"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 text-xs font-bold space-y-1">
            <div class="flex items-center gap-2 font-black text-rose-800">
                <i class="fa-solid fa-triangle-exclamation"></i> Terdapat kesalahan pengisian:
            </div>
            <ul class="list-disc pl-5 font-normal">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($student)
    <!-- STUDENT SECTION: Form & History -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Application Form -->
        <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="font-display font-extrabold text-base text-slate-900">Formulir Pengajuan Cuti</h2>
                <p class="text-xs text-slate-500">Maksimal 2 semester berturut-turut sesuai statuta UPH.</p>
            </div>

            <form action="{{ route('portal.student.leave-request.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Periode Cuti Akademik</label>
                    <input type="text" readonly value="{{ $activePeriod->name ?? '' }} ({{ $activePeriod->code ?? '' }})" class="w-full p-2.5 bg-slate-100 border rounded-xl font-semibold text-slate-700 outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alasan Pengajuan Cuti Akademik *</label>
                    <textarea name="reason" rows="4" required placeholder="Jelaskan alasan pengajuan cuti (pekerjaan, kesehatan, dinas luar, dll.). Minimum 10 karakter." class="w-full p-3 border rounded-xl outline-none focus:ring-1 focus:ring-uph-navy">{{ old('reason') }}</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Dokumen Pendukung (Opsional)</label>
                    <input type="file" name="supporting_doc" accept=".pdf,.jpg,.jpeg,.png" class="w-full p-2 border rounded-xl text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-xs file:font-bold hover:file:bg-slate-200">
                    <span class="text-[10px] text-slate-400 block mt-1">Format: PDF, JPG, PNG (Maks 5 MB)</span>
                </div>

                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-amber-900">
                        <i class="fa-solid fa-circle-exclamation"></i> Konsekuensi Finansial
                    </div>
                    <p>Setelah cuti disahkan oleh Kaprodi, tagihan SPP semester ini akan disesuaikan menjadi biaya administrasi cuti (Holding Fee Rp 1.500.000).</p>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-uph-gold font-bold text-xs uppercase tracking-wider shadow-md transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Pengajuan ke Dosen PA
                </button>
            </form>
        </div>

        <!-- Student Requests History -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                <div>
                    <h2 class="font-display font-extrabold text-base text-slate-900">Riwayat Pengajuan Cuti Saya</h2>
                    <p class="text-xs text-slate-500">Pantau status verifikasi Dosen PA dan pengesahan Kaprodi</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b">
                        <tr>
                            <th class="py-2.5 px-3">Periode</th>
                            <th class="py-2.5 px-3">Alasan</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($myRequests as $req)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-3 font-semibold whitespace-nowrap">{{ $req->academicPeriod->name ?? '-' }}</td>
                                <td class="py-3 px-3 max-w-xs truncate" title="{{ $req->reason }}">{{ $req->reason }}</td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $req->status === 'advisor_approved' ? 'bg-cyan-100 text-cyan-800' : '' }}
                                        {{ $req->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $req->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}">
                                        {{ str_replace('_', ' ', $req->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($req->document_path)
                                        <a href="/portal/storage/download/leave/{{ $req->id }}" class="text-uph-navy font-bold hover:underline flex items-center gap-1">
                                            <i class="fa-solid fa-file-arrow-down"></i> Unduh
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">
                                    Belum ada pengajuan cuti akademik sebelumnya.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- DOSEN PA SECTION: Advisee Requests Approval -->
    @if($adviseeRequests->count() > 0)
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
            <div>
                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-bold uppercase tracking-wider">Peran: Dosen Pembimbing Akademik</span>
                <h2 class="font-display font-extrabold text-base text-slate-900 mt-1">Daftar Pengajuan Cuti Mahasiswa Bimbingan (Advisees)</h2>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $adviseeRequests->count() }} Permohonan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b">
                    <tr>
                        <th class="py-2.5 px-3">Mahasiswa</th>
                        <th class="py-2.5 px-3">Periode</th>
                        <th class="py-2.5 px-3">Alasan Pengajuan</th>
                        <th class="py-2.5 px-3">Status Saat Ini</th>
                        <th class="py-2.5 px-3 text-right">Aksi Dosen PA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($adviseeRequests as $advReq)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900">{{ $advReq->student->user->full_name }}</div>
                                <span class="font-mono text-slate-400 text-[11px]">{{ $advReq->student->nim }}</span>
                            </td>
                            <td class="py-3 px-3 font-semibold">{{ $advReq->academicPeriod->name }}</td>
                            <td class="py-3 px-3 max-w-xs truncate" title="{{ $advReq->reason }}">{{ $advReq->reason }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    {{ $advReq->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $advReq->status === 'advisor_approved' ? 'bg-cyan-100 text-cyan-800' : '' }}
                                    {{ $advReq->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $advReq->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}">
                                    {{ str_replace('_', ' ', $advReq->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($advReq->status === 'pending')
                                    <div class="inline-flex items-center gap-2">
                                        <form method="POST" action="/portal/lecturer/leave-request/{{ $advReq->id }}/approve">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow">
                                                Rekomendasikan ke Kaprodi
                                            </button>
                                        </form>
                                        <button type="button" @click="openRejectModal('{{ $advReq->id }}')" class="px-3 py-1.5 rounded-lg border border-rose-300 text-rose-600 hover:bg-rose-50 font-bold text-xs">
                                            Tolak
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">Telah Diproses</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- KAPRODI SECTION: Final Program Approval -->
    @if($prodiRequests->count() > 0)
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
            <div>
                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold uppercase tracking-wider">Peran: Ketua Program Studi (Kaprodi)</span>
                <h2 class="font-display font-extrabold text-base text-slate-900 mt-1">Pengesahan Akhir Cuti Mahasiswa Program Studi</h2>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $prodiRequests->count() }} Pengajuan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b">
                    <tr>
                        <th class="py-2.5 px-3">Mahasiswa</th>
                        <th class="py-2.5 px-3">Dosen PA</th>
                        <th class="py-2.5 px-3">Periode</th>
                        <th class="py-2.5 px-3">Status Saat Ini</th>
                        <th class="py-2.5 px-3 text-right">Aksi Pengesahan Kaprodi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($prodiRequests as $prodiReq)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900">{{ $prodiReq->student->user->full_name }}</div>
                                <span class="font-mono text-slate-400 text-[11px]">{{ $prodiReq->student->nim }}</span>
                            </td>
                            <td class="py-3 px-3 text-slate-600 font-semibold">
                                {{ $prodiReq->student->academicAdvisor->formatted_name ?? ($prodiReq->student->academicAdvisor->user->full_name ?? '-') }}
                            </td>
                            <td class="py-3 px-3 font-semibold">{{ $prodiReq->academicPeriod->name }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    {{ $prodiReq->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $prodiReq->status === 'advisor_approved' ? 'bg-cyan-100 text-cyan-800' : '' }}
                                    {{ $prodiReq->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $prodiReq->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}">
                                    {{ str_replace('_', ' ', $prodiReq->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($prodiReq->status === 'advisor_approved' || $prodiReq->status === 'pending')
                                    <div class="inline-flex items-center gap-2">
                                        <form method="POST" action="/portal/kaprodi/leave-request/{{ $prodiReq->id }}/approve">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow flex items-center gap-1.5">
                                                <i class="fa-solid fa-stamp"></i> Sahkan Cuti (Update Status &amp; Holding Fee)
                                            </button>
                                        </form>
                                        <button type="button" @click="openRejectModal('{{ $prodiReq->id }}')" class="px-3 py-1.5 rounded-lg border border-rose-300 text-rose-600 hover:bg-rose-50 font-bold text-xs">
                                            Tolak
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">Resmi Disahkan</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Reject Modal -->
    <div x-show="rejectModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200" @click.away="rejectModalOpen = false">
            <h3 class="font-bold text-sm text-slate-900 pb-2 border-b">Tolak Permohonan Cuti Akademik</h3>
            <form :action="'/portal/leave-request/' + selectedRequestId + '/reject'" method="POST" class="mt-4 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alasan Penolakan *</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Tuliskan alasan penolakan secara jelas kepada mahasiswa..." class="w-full p-3 border rounded-xl outline-none focus:ring-1 focus:ring-uph-navy"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 border rounded-xl text-slate-600 font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold">Kirim Penolakan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
