@extends('layouts.portal', ['title' => $title ?? 'Bulk Data Command Center'])

@section('content')
<div class="space-y-6" x-data="bulkDataCenter()">
    <!-- Executive Header -->
    <div class="bg-gradient-to-r from-uph-navy via-slate-900 to-uph-navy p-6 sm:p-8 rounded-2xl text-white shadow-xl border border-uph-gold/30 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-uph-gold/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-uph-gold/20 text-uph-gold border border-uph-gold/30 mb-2">
                    <i class="fa-solid fa-server"></i>
                    Enterprise Batch Processing &bull; Zero Memory Streaming
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $title ?? 'Bulk Data Command Center' }}</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Ekspor dataset universitas secara real-time melalui streaming HTTP hemat memori, dan lakukan impor massal data nilai serta tagihan keuangan dengan verifikasi pra-impor (dry-run).
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 rounded-xl bg-white/10 text-xs font-mono font-bold border border-white/20">
                    <i class="fa-solid fa-shield-halved text-emerald-400 mr-1.5"></i>
                    Atomic DB Transaction
                </span>
            </div>
        </div>
    </div>

    <!-- Section 1: 1-Click Streaming CSV Export Cards -->
    <div class="space-y-3">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
            <i class="fa-solid fa-file-export text-uph-navy dark:text-uph-gold"></i>
            1-Click Streaming CSV / Excel Data Exports
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Export Students -->
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm hover:border-uph-gold/50 transition-all flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg mb-3">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Master Status Mahasiswa</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Data lengkap NIM, IPK kumulatif, total SKS lulus, dan status bebas keuangan.</p>
                </div>
                <a href="/portal/bulk-data/export/students" 
                   class="mt-4 w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-uph-navy hover:text-white dark:hover:bg-uph-gold dark:hover:text-uph-navy text-slate-700 dark:text-slate-200 text-xs font-bold text-center transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    Download CSV
                </a>
            </div>

            <!-- Export Schedules -->
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm hover:border-uph-gold/50 transition-all flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg mb-3">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Jadwal & Kuota Kelas</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Rekap seluruh seksi perkuliahan, ruangan, dosen pengampu, dan keterisian kuota.</p>
                </div>
                <a href="/portal/bulk-data/export/schedules" 
                   class="mt-4 w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-uph-navy hover:text-white dark:hover:bg-uph-gold dark:hover:text-uph-navy text-slate-700 dark:text-slate-200 text-xs font-bold text-center transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    Download CSV
                </a>
            </div>

            <!-- Export Invoices -->
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm hover:border-uph-gold/50 transition-all flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg mb-3">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Rekapitulasi Tagihan UKT</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Rekap nomor invoice, VA Bank, nominal tagihan, dan status rekonsiliasi pelunasan.</p>
                </div>
                <a href="/portal/bulk-data/export/invoices" 
                   class="mt-4 w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-uph-navy hover:text-white dark:hover:bg-uph-gold dark:hover:text-uph-navy text-slate-700 dark:text-slate-200 text-xs font-bold text-center transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    Download CSV
                </a>
            </div>

            <!-- Export Grade Template -->
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-surface-border dark:border-slate-800 shadow-sm hover:border-uph-gold/50 transition-all flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg mb-3">
                        <i class="fa-solid fa-table-cells"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Template Nilai OBE</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Template spreadsheet resmi untuk penginputan nilai OBE dosen per krs_item_id.</p>
                </div>
                <a href="/portal/bulk-data/export/grade-template" 
                   class="mt-4 w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-uph-navy hover:text-white dark:hover:bg-uph-gold dark:hover:text-uph-navy text-slate-700 dark:text-slate-200 text-xs font-bold text-center transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    Download Template
                </a>
            </div>
        </div>
    </div>

    <!-- Section 2: Bulk CSV Importer with Dry-Run Validation -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-surface-border dark:border-slate-800 p-6 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-file-import text-uph-gold"></i>
                    Import Data Massal dengan Pra-Validasi (Dry-Run Preview)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Unggah file CSV untuk menguji keabsahan setiap baris data sebelum menerapkan perubahan secara permanen ke database.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <select x-model="importType" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                    <option value="grades">Data Nilai Perkuliahan OBE</option>
                    <option value="invoices">Rekonsiliasi Pelunasan Tagihan UKT</option>
                </select>
            </div>
        </div>

        <!-- File Upload Area -->
        <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-uph-gold rounded-2xl p-6 text-center transition-colors bg-slate-50/50 dark:bg-slate-800/40">
            <input type="file" id="csvFileInput" @change="handleFileSelected" accept=".csv,text/csv" class="hidden">
            <label for="csvFileInput" class="cursor-pointer flex flex-col items-center">
                <div class="w-12 h-12 rounded-full bg-uph-navy/10 text-uph-navy dark:bg-uph-navy dark:text-uph-gold flex items-center justify-center text-xl mb-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <span class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="selectedFileName || 'Klik untuk memilih file CSV atau drag & drop di sini'"></span>
                <span class="text-xs text-slate-400 mt-1">Maksimal ukuran file 5 MB (.csv dengan pemisah koma)</span>
            </label>
            <div class="mt-4 flex items-center justify-center gap-3" x-show="selectedFile">
                <button type="button" 
                        @click="runDryRun" 
                        :disabled="isValidating"
                        class="px-5 py-2 rounded-xl bg-uph-navy text-white hover:bg-uph-navy-dark dark:bg-uph-gold dark:text-uph-navy font-bold text-xs shadow-md transition-all flex items-center gap-2 disabled:opacity-50">
                    <i class="fa-solid" :class="isValidating ? 'fa-spinner fa-spin' : 'fa-magnifying-glass-chart'"></i>
                    <span x-text="isValidating ? 'Memvalidasi...' : 'Jalankan Pra-Validasi (Dry-Run)'"></span>
                </button>
            </div>
        </div>

        <!-- Dry Run Result Preview -->
        <template x-if="dryRunResult">
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <!-- Summary Chips -->
                <div class="flex items-center gap-3">
                    <div class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Total Baris: <span class="font-bold font-mono" x-text="dryRunResult.total_rows"></span>
                    </div>
                    <div class="px-3.5 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                        <i class="fa-solid fa-circle-check mr-1"></i> Valid: <span class="font-bold font-mono" x-text="dryRunResult.valid_count"></span>
                    </div>
                    <div class="px-3.5 py-1.5 rounded-xl bg-red-100 dark:bg-red-950 text-xs font-semibold text-red-800 dark:text-red-300">
                        <i class="fa-solid fa-circle-xmark mr-1"></i> Tidak Valid: <span class="font-bold font-mono" x-text="dryRunResult.invalid_count"></span>
                    </div>

                    <div class="ml-auto" x-show="dryRunResult.valid_count > 0">
                        <button type="button" 
                                @click="commitImport" 
                                :disabled="isCommitting"
                                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/20 transition-all flex items-center gap-2 disabled:opacity-50">
                            <i class="fa-solid" :class="isCommitting ? 'fa-spinner fa-spin' : 'fa-check-double'"></i>
                            <span x-text="isCommitting ? 'Menerapkan...' : 'Terapkan ' + dryRunResult.valid_count + ' Data Valid (Commit DB)'"></span>
                        </button>
                    </div>
                </div>

                <!-- Invalid Rows Warnings -->
                <template x-if="dryRunResult.invalid_count > 0">
                    <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-xs text-red-800 dark:text-red-300">
                        <p class="font-bold mb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Baris Ditolak / Tidak Valid:
                        </p>
                        <div class="max-h-32 overflow-y-auto space-y-1">
                            <template x-for="inv in dryRunResult.invalid_rows" :key="inv.row">
                                <p class="text-[11px]">
                                    <span class="font-mono font-bold">Baris <span x-text="inv.row"></span>:</span>
                                    <span x-text="inv.reason"></span>
                                </p>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Valid Rows Table Preview -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden max-h-60 overflow-y-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800 font-bold uppercase text-[10px] text-slate-500 tracking-wider sticky top-0">
                            <tr>
                                <th class="p-2.5">Baris</th>
                                <th class="p-2.5">Target Identifier</th>
                                <th class="p-2.5">Nama / Keterangan</th>
                                <th class="p-2.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <template x-for="v in dryRunResult.valid_rows" :key="v.row">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="p-2.5 font-mono" x-text="v.row"></td>
                                    <td class="p-2.5 font-mono text-[11px]" x-text="v.krs_item_id || v.invoice_number"></td>
                                    <td class="p-2.5" x-text="v.student_name || ('Tagihan IDR ' + v.amount)"></td>
                                    <td class="p-2.5 text-right">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                            Siap Diimpor
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function bulkDataCenter() {
    return {
        importType: 'grades',
        selectedFile: null,
        selectedFileName: '',
        isValidating: false,
        isCommitting: false,
        dryRunResult: null,

        handleFileSelected(e) {
            const file = e.target.files[0];
            if (file) {
                this.selectedFile = file;
                this.selectedFileName = file.name;
                this.dryRunResult = null;
            }
        },

        async runDryRun() {
            if (!this.selectedFile) return;
            this.isValidating = true;
            const formData = new FormData();
            formData.append('csv_file', this.selectedFile);
            formData.append('import_type', this.importType);

            try {
                const res = await fetch('/portal/bulk-data/preview', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    this.dryRunResult = data;
                } else {
                    alert(data.message || 'Gagal memproses file.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat validasi CSV.');
            } finally {
                this.isValidating = false;
            }
        },

        async commitImport() {
            if (!this.dryRunResult || this.dryRunResult.valid_count === 0) return;
            if (!confirm(`Konfirmasi impor permanen ${this.dryRunResult.valid_count} data ke database?`)) return;

            this.isCommitting = true;
            try {
                const res = await fetch('/portal/bulk-data/commit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        import_type: this.importType,
                        items: this.dryRunResult.valid_rows
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.selectedFile = null;
                    this.selectedFileName = '';
                    this.dryRunResult = null;
                } else {
                    alert(data.message || 'Gagal menyimpan bulk import.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat commit import.');
            } finally {
                this.isCommitting = false;
            }
        }
    }
}
</script>
@endsection
