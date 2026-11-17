@extends('layouts.portal')

@section('title', 'OpenAPI & Webhook Interoperability Sandbox')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'webhook',
    invoiceNumber: '',
    vaNumber: '',
    amountPaid: '',
    secretKey: '{{ $defaultHmacSecret }}',
    providedSig: '',
    executeSettlement: true,
    webhookLoading: false,
    webhookResult: null,
    
    selectInvoice(inv) {
        this.invoiceNumber = inv.invoice_number;
        this.vaNumber = inv.va_number || '';
        this.amountPaid = inv.amount;
    },

    async testWebhook() {
        this.webhookLoading = true;
        this.webhookResult = null;
        try {
            const res = await fetch('{{ route('portal.developer.simulate-webhook') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    invoice_number: this.invoiceNumber,
                    va_number: this.vaNumber,
                    amount_paid: this.amountPaid,
                    secret_key: this.secretKey,
                    provided_signature: this.providedSig,
                    execute_settlement: this.executeSettlement
                })
            });
            const data = await res.json();
            this.webhookResult = {
                status: res.status,
                data: data
            };
        } catch (e) {
            this.webhookResult = {
                status: 500,
                data: { success: false, message: e.message }
            };
        } finally {
            this.webhookLoading = false;
        }
    }
}">

    <!-- Header & Hero -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-uph-navy to-slate-900 p-6 rounded-2xl border border-white/10 shadow-xl">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase tracking-widest">
                    Enterprise Interoperability
                </span>
                <span class="text-xs text-slate-400">OpenAPI &amp; Bank Gateway Hub</span>
            </div>
            <h1 class="text-2xl font-bold font-serif text-white flex items-center gap-2">
                <i class="fa-solid fa-code text-uph-gold"></i>
                Developer API &amp; Webhook Interoperability Sandbox
            </h1>
            <p class="text-xs text-slate-300">
                Simulator interaktif untuk pengujian callback Bank VA/QRIS dengan verifikasi HMAC-SHA256 dan inspektor skema JSON Kemdiktisaintek PDDIKTI Neo Feeder.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('portal.developer.outbox') }}" 
               class="px-4 py-2.5 rounded-xl bg-white/10 text-white font-bold text-xs hover:bg-white/20 border border-white/20 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-paper-plane text-uph-gold"></i>
                Buka Outbound Notification Log
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-white/10 pb-2">
        <button @click="activeTab = 'webhook'" 
                :class="activeTab === 'webhook' ? 'bg-uph-navy text-uph-gold border-uph-gold' : 'text-slate-400 hover:text-white border-transparent'"
                class="px-4 py-2 rounded-xl font-bold text-xs border transition-colors flex items-center gap-2">
            <i class="fa-solid fa-bolt"></i>
            Bank VA / QRIS Webhook Simulator
        </button>

        <button @click="activeTab = 'pddikti'" 
                :class="activeTab === 'pddikti' ? 'bg-uph-navy text-uph-gold border-uph-gold' : 'text-slate-400 hover:text-white border-transparent'"
                class="px-4 py-2 rounded-xl font-bold text-xs border transition-colors flex items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            PDDIKTI Neo Feeder JSON Inspector
        </button>
    </div>

    <!-- Tab 1: Bank VA Webhook Simulator -->
    <div x-show="activeTab === 'webhook'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Left: Form Controls -->
        <div class="bg-slate-900/80 p-6 rounded-2xl border border-white/10 space-y-4 shadow-xl">
            <h2 class="text-sm font-bold text-white flex items-center gap-2 border-b border-white/10 pb-3">
                <i class="fa-solid fa-sliders text-uph-gold"></i>
                Konfigurasi Payload &amp; HMAC Signature
            </h2>

            <!-- Quick Select Invoice -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Pilih Sampel Tagihan Mahasiswa (Ledger)</label>
                <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                    @foreach($sampleInvoices as $inv)
                        <button type="button" 
                                @click="selectInvoice({ invoice_number: '{{ $inv->invoice_number }}', va_number: '{{ $inv->va_number }}', amount: '{{ (int)$inv->amount }}' })"
                                class="w-full text-left p-2 rounded-lg bg-black/30 hover:bg-uph-navy/50 border border-white/5 flex items-center justify-between text-xs transition-colors">
                            <div>
                                <span class="font-mono font-bold text-uph-gold">{{ $inv->invoice_number }}</span>
                                <span class="text-slate-400 ml-2">{{ $inv->student->user->full_name ?? 'Mahasiswa' }}</span>
                            </div>
                            <span class="font-mono font-semibold text-slate-300">Rp {{ number_format($inv->amount, 0, ',', '.') }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Invoice Number</label>
                    <input type="text" x-model="invoiceNumber" placeholder="INV-2026-..." class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs font-mono focus:border-uph-gold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">VA Number</label>
                    <input type="text" x-model="vaNumber" placeholder="8808..." class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs font-mono focus:border-uph-gold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Nominal Pelunasan (Amount Paid)</label>
                <input type="number" x-model="amountPaid" placeholder="18500000" class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs font-mono focus:border-uph-gold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Secret Key (HMAC Shared Secret)</label>
                <input type="text" x-model="secretKey" class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs font-mono focus:border-uph-gold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">
                    Signature Header Kustom (Opsional: Kosongkan untuk Auto-HMAC valid)
                </label>
                <input type="text" x-model="providedSig" placeholder="Kosongkan untuk menguji signature valid, atau isi string acak untuk menguji 401 Unauthorized" class="w-full bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs font-mono focus:border-uph-gold">
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 text-xs text-emerald-300 cursor-pointer">
                    <input type="checkbox" x-model="executeSettlement" class="rounded bg-slate-800 border-white/20 text-emerald-500">
                    <span class="font-semibold">Terapkan Pelunasan &amp; Kliring Finansial Realtime (DB Transaction)</span>
                </label>
            </div>

            <div class="pt-2 border-t border-white/10">
                <button type="button" @click="testWebhook()" :disabled="webhookLoading"
                        class="w-full py-2.5 rounded-xl bg-gradient-to-r from-uph-gold to-amber-500 text-uph-navy font-bold text-xs hover:brightness-110 shadow-lg flex items-center justify-center gap-2 transition-all">
                    <i class="fa-solid fa-paper-plane" x-show="!webhookLoading"></i>
                    <i class="fa-solid fa-spinner animate-spin" x-show="webhookLoading"></i>
                    <span x-text="webhookLoading ? 'Mengirim Simulasi Webhook...' : 'Kirim Simulasi Webhook (HTTP POST)'"></span>
                </button>
            </div>
        </div>

        <!-- Right: Response Inspector -->
        <div class="bg-slate-900/80 p-6 rounded-2xl border border-white/10 space-y-4 shadow-xl flex flex-col">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h2 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-terminal text-uph-gold"></i>
                    Inspektor Respons &amp; Status Verifikasi
                </h2>
                <template x-if="webhookResult">
                    <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold"
                          :class="webhookResult.status === 200 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30'"
                          x-text="webhookResult.status === 200 ? '200 OK (VERIFIED)' : webhookResult.status + ' (FAILED)'">
                    </span>
                </template>
            </div>

            <div class="flex-1 bg-black/50 p-4 rounded-xl border border-white/5 font-mono text-xs overflow-auto text-slate-200 space-y-2">
                <template x-if="!webhookResult">
                    <div class="h-full flex flex-col items-center justify-center text-slate-500 py-16">
                        <i class="fa-solid fa-network-wired text-3xl mb-2 text-slate-600"></i>
                        <p>Belum ada simulasi yang ditembakkan.</p>
                        <span class="text-[10px] text-slate-600">Klik tombol di sebelah kiri untuk menguji callback webhook.</span>
                    </div>
                </template>

                <template x-if="webhookResult">
                    <pre class="text-[11px] leading-relaxed text-emerald-400 whitespace-pre-wrap" x-text="JSON.stringify(webhookResult.data, null, 2)"></pre>
                </template>
            </div>
        </div>
    </div>

    <!-- Tab 2: PDDIKTI Neo Feeder JSON Inspector -->
    <div x-show="activeTab === 'pddikti'" style="display: none;" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($pddiktiSchemas as $action => $info)
                <div class="bg-slate-900/80 p-5 rounded-2xl border border-white/10 space-y-3 shadow-xl flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-uph-gold">{{ $action }}</span>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30 uppercase">
                                Neo Feeder
                            </span>
                        </div>
                        <p class="text-xs text-slate-300">{{ $info['description'] }}</p>
                        <p class="text-[11px] font-mono text-slate-500 break-all">{{ $info['endpoint'] }}</p>

                        <div class="bg-black/40 p-3 rounded-xl border border-white/5 font-mono text-[11px] text-slate-300 overflow-x-auto max-h-48">
                            <pre>{{ json_encode($info['schema'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-white/5 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Schema Standard Kemdiktisaintek</span>
                        <span class="text-emerald-400 font-bold"><i class="fa-solid fa-check mr-1"></i>Compliant</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
