@extends('layouts.portal')

@section('title', 'Tagihan & Keuangan Mahasiswa - UPH')

@section('content')
<div class="space-y-6" x-data="{
    activeBank: '{{ $currentInvoice ? $currentInvoice->payment_method ?? 'BCA_VA' : 'BCA_VA' }}',
    vaNumber: '{{ $currentInvoice ? $currentInvoice->va_number : '' }}',
    copied: false,
    copyVa() {
        navigator.clipboard.writeText(this.vaNumber);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    },
    switchBank(bank) {
        this.activeBank = bank;
        @if($currentInvoice && $currentInvoice->payment_status !== 'paid')
        fetch('/finance/invoice/{{ $currentInvoice->id }}/va', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify({ payment_method: bank })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.vaNumber = data.va_number;
                if (window.EnterpriseToast) window.EnterpriseToast.show('Nomor VA diperbarui: ' + bank, 'success');
            }
        });
        @endif
    }
}">
    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-uph-navy/10 text-uph-navy">
                    Biro Administrasi Keuangan (BAUK)
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Universitas Pelita Harapan</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Tagihan & Pembayaran Biaya Kuliah</h1>
            <p class="text-xs text-slate-500 mt-1">
                Layanan Virtual Account multi-bank dan QRIS terintegrasi untuk pelunasan BPP Pokok, BPP SKS, dan registrasi semester.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($isCleared)
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200 shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    Registrasi Finansial Aktif (Cleared)
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 text-rose-800 text-xs font-bold border border-rose-200 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                    Belum Lunas (Hold KRS/Ujian)
                </span>
            @endif
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Status Finansial</span>
            <div class="flex items-center gap-2 mt-2">
                @if($isCleared)
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <span class="font-bold text-emerald-800 text-sm block">Bebas Keuangan</span>
                        <span class="text-[10px] text-slate-400">Siap KRS & Ujian</span>
                    </div>
                @else
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <span class="font-bold text-rose-800 text-sm block">Menunggu Bayar</span>
                        <span class="text-[10px] text-slate-400">Segera lunasi VA</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Tagihan Kumulatif</span>
            <h3 class="text-xl sm:text-2xl font-display font-black text-slate-900 mt-1">
                Rp {{ number_format($totalBilled, 0, ',', '.') }}
            </h3>
            <span class="text-[10px] text-slate-400 mt-1 block">Seluruh riwayat semester</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Terbayar</span>
            <h3 class="text-xl sm:text-2xl font-display font-black text-emerald-600 mt-1">
                Rp {{ number_format($totalPaid, 0, ',', '.') }}
            </h3>
            <span class="text-[10px] text-slate-400 mt-1 block">Telah diverifikasi BAUK</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sisa Tagihan Berjalan</span>
            <h3 class="text-xl sm:text-2xl font-display font-black text-uph-crimson mt-1">
                Rp {{ number_format($outstandingBalance, 0, ',', '.') }}
            </h3>
            <span class="text-[10px] text-slate-400 mt-1 block">Jatuh tempo berjalan</span>
        </div>
    </div>

    <!-- Active Invoice & Payment Gateway Box -->
    @if($currentInvoice)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-slate-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-uph-navy text-white">
                    Tagihan Semester Aktif
                </span>
                <h2 class="text-lg font-bold text-slate-900 mt-1">{{ $currentInvoice->title }}</h2>
                <p class="text-xs text-slate-500 font-mono mt-0.5">
                    No. Invoice: <strong class="text-slate-800">{{ $currentInvoice->invoice_number }}</strong> &bull; 
                    Jatuh Tempo: {{ $currentInvoice->due_date->translatedFormat('d F Y') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if($currentInvoice->payment_status === 'paid')
                    <span class="px-4 py-2 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center gap-1.5 border border-emerald-300">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i> LUNAS TERVERIFIKASI
                    </span>
                    <a href="/finance/receipt/{{ $currentInvoice->id }}" target="_blank"
                       class="px-4 py-2 rounded-xl bg-uph-navy text-white font-bold text-xs hover:bg-uph-navy-dark transition-colors flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-receipt"></i> Unduh Kwitansi
                    </a>
                @else
                    <span class="px-4 py-2 rounded-xl bg-amber-100 text-amber-800 font-bold text-xs flex items-center gap-1.5 border border-amber-300">
                        <i class="fa-solid fa-clock"></i> MENUNGGU PEMBAYARAN
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left: Fee Breakdown -->
            <div class="lg:col-span-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Komponen Biaya</h3>
                <div class="space-y-3 text-xs divide-y divide-slate-100">
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-slate-600">BPP Pokok (Fixed Tuition)</span>
                        <span class="font-mono font-bold text-slate-800">Rp 12.500.000</span>
                    </div>
                    <div class="flex justify-between items-center pt-3">
                        <div>
                            <span class="text-slate-600 block">BPP SKS Variabel</span>
                            <span class="text-[10px] text-slate-400">Tarif Rp 450.000 / SKS</span>
                        </div>
                        <span class="font-mono font-bold text-slate-800">
                            Rp {{ number_format($currentInvoice->amount - 14000000, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-3">
                        <span class="text-slate-600">Fasilitas Laboratorium & IT</span>
                        <span class="font-mono font-bold text-slate-800">Rp 1.500.000</span>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t-2 border-slate-200 text-sm">
                        <span class="font-bold text-slate-900">Total Tagihan</span>
                        <span class="font-mono font-black text-uph-navy text-base">
                            Rp {{ number_format($currentInvoice->amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-slate-500">Jumlah Dibayar</span>
                        <span class="font-mono font-bold text-emerald-600">
                            Rp {{ number_format($currentInvoice->paid_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                @if($currentInvoice->payment_status !== 'paid')
                <!-- Dev Payment Simulation Button -->
                <div class="pt-4 border-t border-slate-100">
                    <form method="POST" action="/finance/invoice/{{ $currentInvoice->id }}/simulate">
                        @csrf
                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-bold text-xs uppercase tracking-wider shadow-md transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-bolt-lightning text-amber-300"></i> Simulasikan Pembayaran Lunas (Dev Mode)
                        </button>
                    </form>
                    <span class="block text-[10px] text-slate-400 text-center mt-1">
                        Mensimulasikan webhook settlement gateway dan membuka clearance secara instan.
                    </span>
                </div>
                @endif
            </div>

            <!-- Right: Virtual Account & QRIS Channel -->
            <div class="lg:col-span-7 bg-slate-50 rounded-2xl p-6 border border-slate-200 space-y-5">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Pilih Metode Pembayaran</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mt-3">
                        @php
                            $banks = [
                                'BCA_VA' => ['name' => 'BCA VA', 'icon' => 'fa-building-columns'],
                                'BNI_VA' => ['name' => 'BNI VA', 'icon' => 'fa-building-columns'],
                                'MANDIRI_VA' => ['name' => 'Mandiri VA', 'icon' => 'fa-building-columns'],
                                'CIMB_VA' => ['name' => 'CIMB VA', 'icon' => 'fa-building-columns'],
                                'QRIS' => ['name' => 'QRIS', 'icon' => 'fa-qrcode'],
                            ];
                        @endphp
                        @foreach($banks as $bKey => $bInfo)
                        <button type="button" 
                                @click="switchBank('{{ $bKey }}')"
                                class="p-2.5 rounded-xl border text-center transition-all select-none"
                                :class="activeBank === '{{ $bKey }}' ? 'bg-uph-navy text-white border-uph-navy shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300'">
                            <i class="fa-solid {{ $bInfo['icon'] }} text-xs mb-1 block"></i>
                            <span class="text-[11px] font-bold block">{{ $bInfo['name'] }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- VA Display Card -->
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="activeBank === 'QRIS' ? 'Kode QRIS Dinamis UPH' : 'Nomor Virtual Account ' + activeBank.replace('_', ' ')">
                            Nomor Virtual Account
                        </span>
                        <span class="text-[10px] font-mono text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded font-bold">
                            Verifikasi Real-Time 24/7
                        </span>
                    </div>

                    <div x-show="activeBank !== 'QRIS'">
                        <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl p-3 font-mono">
                            <span class="text-xl font-black text-slate-900 tracking-wider" x-text="vaNumber">
                                {{ $currentInvoice->va_number }}
                            </span>
                            <button type="button" @click="copyVa()" 
                                    class="px-3 py-1.5 rounded-lg bg-uph-navy text-white text-xs font-bold hover:bg-uph-navy-dark transition-colors flex items-center gap-1">
                                <i class="fa-regular fa-copy"></i>
                                <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
                            </button>
                        </div>
                    </div>

                    <div x-show="activeBank === 'QRIS'" x-cloak class="text-center py-2">
                        <div class="w-40 h-40 mx-auto bg-slate-100 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400">
                            <i class="fa-solid fa-qrcode text-6xl text-slate-700"></i>
                        </div>
                        <span class="text-[11px] text-slate-500 mt-2 block font-mono">
                            Pindai melalui GoPay, OVO, Dana, BCA Mobile, Livin, dll.
                        </span>
                    </div>

                    <div class="text-[11px] text-slate-500 space-y-1 pt-2 border-t border-slate-100">
                        <p>&bull; Tagihan akan diverifikasi otomatis maksimal 60 detik setelah pembayaran diterima.</p>
                        <p>&bull; Simpan bukti transfer hingga status KRS Anda berubah menjadi disetujui (Approved).</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Invoices Ledger History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h2 class="text-base font-bold text-slate-900">Riwayat Seluruh Tagihan & Pembayaran</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar buku besar keuangan mahasiswa terintegrasi dengan rekening kas universitas.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-4 px-4 text-center w-12">No</th>
                        <th class="py-4 px-6">No. Invoice & Judul</th>
                        <th class="py-4 px-4">Periode</th>
                        <th class="py-4 px-4 font-mono">Nomor VA</th>
                        <th class="py-4 px-4 text-right">Tagihan</th>
                        <th class="py-4 px-4 text-right">Dibayar</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Kwitansi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($invoices as $idx => $inv)
                    <tr class="hover:bg-slate-50/75 transition-colors">
                        <td class="py-4 px-4 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-4 px-6">
                            <span class="font-mono font-bold text-uph-navy text-xs block">{{ $inv->invoice_number }}</span>
                            <span class="text-slate-800 font-bold mt-0.5 block">{{ $inv->title }}</span>
                            <span class="text-[10px] text-slate-400">Jatuh Tempo: {{ $inv->due_date->format('d/m/Y') }}</span>
                        </td>
                        <td class="py-4 px-4 font-bold text-slate-700">
                            {{ $inv->academicPeriod->name ?? '-' }}
                        </td>
                        <td class="py-4 px-4 font-mono font-bold text-slate-700">
                            {{ $inv->va_number }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono font-bold text-slate-900">
                            Rp {{ number_format($inv->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right font-mono font-bold text-emerald-600">
                            Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($inv->payment_status === 'paid')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-check text-[9px]"></i> LUNAS
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fa-solid fa-clock text-[9px]"></i> PENDING
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right">
                            @if($inv->payment_status === 'paid')
                                <a href="/finance/receipt/{{ $inv->id }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors">
                                    <i class="fa-solid fa-file-invoice text-uph-navy"></i> Kwitansi
                                </a>
                            @else
                                <span class="text-slate-400 italic text-[11px]">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 italic">
                            Belum ada riwayat tagihan biaya perkuliahan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
