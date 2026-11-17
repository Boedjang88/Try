@extends('layouts.portal')

@section('title', 'Manajemen Billing & Rekonsiliasi VA - BAUK UPH')

@section('content')
<div class="space-y-6" x-data="{
    verifyingId: null,
    async verifyInvoice(id, invoiceNumber) {
        if (!confirm(`Verifikasi pembayaran dan lepas status hold untuk tagihan ${invoiceNumber}?`)) return;
        this.verifyingId = id;
        try {
            const res = await fetch(`/portal/bauk/billing/verify/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                }
            });
            const data = await res.json();
            if (data.success) {
                window.EnterpriseToast.success(data.message, 'Pembayaran Diverifikasi');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                window.EnterpriseToast.error(data.message || 'Gagal memverifikasi', 'Kesalahan');
            }
        } catch (err) {
            window.EnterpriseToast.error('Gagal menghubungi server.', 'Error Jaringan');
        } finally {
            this.verifyingId = null;
        }
    }
}">

    <!-- Header Section -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div>
            <div class="flex items-center gap-2 mb-0.5">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-cyan-50 text-cyan-800 border border-cyan-200 font-mono">
                    Biro Administrasi Keuangan (BAUK)
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-bold">Periode: {{ $activePeriod->name ?? '2025/2026 Ganjil' }}</span>
            </div>
            <h1 class="text-2xl font-display font-black text-slate-900">Manajemen Tagihan UKT & Virtual Account</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Verifikasi real-time pembayaran VA, mutasi kas, dan pelepasan status penangguhan akademik (Financial Hold).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-mono text-xs font-bold border border-slate-200">
                Total Hold: <strong class="text-red-700">{{ count($heldStudents) }} Mahasiswa</strong>
            </span>
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Tagihan Diterbitkan</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="text-xs text-slate-400 font-bold">Rp</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">
                    {{ number_format($totalBilled / 1000000, 1) }}M
                </h3>
            </div>
            <span class="text-[10px] text-slate-500 mt-1 block">BPP Pokok & Biaya SKS</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kas UKT Terkumpul</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="text-xs text-emerald-600 font-bold">Rp</span>
                <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 font-mono">
                    {{ number_format($totalCollected / 1000000, 1) }}M
                </h3>
            </div>
            <span class="text-[10px] text-emerald-700 font-bold mt-1 block">
                {{ $collectionRate }}% Tingkat Pelunasan
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Piutang / Pending</span>
            <div class="flex items-baseline gap-1 mt-1">
                <span class="text-xs text-red-600 font-bold">Rp</span>
                <h3 class="text-2xl sm:text-3xl font-black text-red-600 font-mono">
                    {{ number_format($totalPending / 1000000, 1) }}M
                </h3>
            </div>
            <span class="text-[10px] text-red-600 font-bold mt-1 block">Tunggakan Belum Lunas</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Mahasiswa Dalam Hold</span>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl sm:text-3xl font-black text-red-700 font-mono">{{ count($heldStudents) }}</h3>
                <span class="text-xs text-slate-400">Mahasiswa</span>
            </div>
            <span class="text-[10px] text-slate-500 mt-1 block">KRS Terkunci Otomatis</span>
        </div>
    </div>

    <!-- Prominent Financial Holds Management Card (Loop 1 Target) -->
    <div class="bg-white p-6 rounded-2xl border-2 border-red-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-red-600 animate-pulse"></span>
                    <i class="fa-solid fa-user-lock text-red-600"></i>
                    <span>Daftar Mahasiswa Dalam Status Penangguhan (Financial Hold)</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Klik <strong>"Verifikasi Lunas & Buka Hold"</strong> untuk membuka kunci akses KRS dan akademik mahasiswa secara instan.
                </p>
            </div>
        </div>

        @if(count($heldStudents) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-red-100 bg-red-50/60 text-slate-700 uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Mahasiswa & NIM</th>
                            <th class="py-3 px-3">Program Studi</th>
                            <th class="py-3 px-3">Nomor Tagihan & VA</th>
                            <th class="py-3 px-3">Tunggakan (Rp)</th>
                            <th class="py-3 px-2 text-center">Status Hold</th>
                            <th class="py-3 px-3 text-right">Aksi Eksekusi BAUK</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-red-100">
                        @foreach($heldStudents as $st)
                            @php
                                $unpaidInv = $st->invoices->first();
                            @endphp
                            <tr class="hover:bg-red-50/40 transition-colors">
                                <td class="py-3 px-3">
                                    <p class="font-bold text-slate-900">{{ $st->user->full_name ?? 'Mahasiswa' }}</p>
                                    <span class="font-mono text-[11px] text-slate-500">NIM: {{ $st->nim }}</span>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $st->studyProgram->name_id ?? 'Informatika' }}
                                </td>
                                <td class="py-3 px-3">
                                    <p class="font-mono font-bold text-slate-900">{{ $unpaidInv->invoice_number ?? '-' }}</p>
                                    <span class="text-[10px] text-slate-500 font-mono">BCA VA: {{ $unpaidInv->va_number ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-mono font-black text-red-700 text-sm">
                                        Rp {{ number_format($unpaidInv->amount ?? 16500000, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase font-mono bg-red-100 text-red-800 border border-red-300">
                                        TERKUNCI
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if($unpaidInv)
                                        <button @click="verifyInvoice('{{ $unpaidInv->id }}', '{{ $unpaidInv->invoice_number }}')" 
                                                :disabled="verifyingId === '{{ $unpaidInv->id }}'"
                                                class="px-3.5 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition-all duration-150 shadow-sm flex items-center gap-1.5 ml-auto disabled:opacity-50">
                                            <i class="fa-solid" :class="verifyingId === '{{ $unpaidInv->id }}' ? 'fa-spinner fa-spin' : 'fa-circle-check'"></i>
                                            <span>Verifikasi Lunas & Buka Hold</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-slate-400">
                <i class="fa-solid fa-circle-check text-emerald-500 text-3xl mb-2 block"></i>
                <p class="text-xs font-bold text-slate-700">Tidak ada mahasiswa yang sedang berstatus Financial Hold.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Seluruh mahasiswa telah memenuhi kewajiban pembayaran biaya kuliah.</p>
            </div>
        @endif
    </div>

    <!-- Full Invoices Master Table -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-uph-gold"></i>
                    <span>Daftar Seluruh Tagihan Mahasiswa</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Data invoice per periode akademik dengan status rekonsiliasi gateway</p>
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl text-xs font-bold">
                <a href="{{ route('portal.bauk.billing', ['status' => 'all']) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $statusFilter === 'all' ? 'bg-white text-uph-navy shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('portal.bauk.billing', ['status' => 'pending']) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $statusFilter === 'pending' ? 'bg-white text-uph-navy shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Pending / Belum Lunas
                </a>
                <a href="{{ route('portal.bauk.billing', ['status' => 'paid']) }}" 
                   class="px-3 py-1.5 rounded-lg transition-colors {{ $statusFilter === 'paid' ? 'bg-white text-uph-navy shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Lunas
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-3">No. Invoice & VA</th>
                        <th class="py-3 px-3">Mahasiswa</th>
                        <th class="py-3 px-3">Uraian Tagihan</th>
                        <th class="py-3 px-3">Nominal (Rp)</th>
                        <th class="py-3 px-2 text-center">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3">
                                <p class="font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</p>
                                <span class="font-mono text-[11px] text-slate-500">VA: {{ $inv->va_number }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <p class="font-bold text-slate-900">{{ $inv->student->user->full_name ?? 'Mahasiswa' }}</p>
                                <span class="font-mono text-[11px] text-slate-400">NIM: {{ $inv->student->nim ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3 text-slate-700 max-w-xs truncate">
                                {{ $inv->title }}
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                Rp {{ number_format($inv->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-2 text-center">
                                @if($inv->payment_status === 'paid')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        LUNAS
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-red-100 text-red-800 border border-red-200">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    @if($inv->payment_status === 'paid')
                                        <a href="/portal/print/kwitansi/{{ $inv->id }}" target="_blank" class="px-2.5 py-1 rounded-lg border border-slate-300 text-slate-700 font-bold text-[11px] hover:bg-slate-50 flex items-center gap-1">
                                            <i class="fa-solid fa-print"></i> Kwitansi
                                        </a>
                                    @else
                                        <button @click="verifyInvoice('{{ $inv->id }}', '{{ $inv->invoice_number }}')" 
                                                class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-bold text-[11px] hover:bg-emerald-700 transition-colors shadow-sm">
                                            Verifikasi Bayar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada data tagihan yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    </div>

</div>
@endsection
