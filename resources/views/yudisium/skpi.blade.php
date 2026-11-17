@extends('layouts.portal')

@section('title', 'Surat Keterangan Pendamping Ijazah (SKPI) - UPH')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar (Hidden on print) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="/yudisium" class="text-xs font-bold text-uph-navy hover:underline">
                    &larr; Kembali ke Dasbor Yudisium
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-medium">Biro Administrasi Akademik & Kemahasiswaan</span>
            </div>
            <h1 class="text-2xl font-display font-black text-uph-navy">Surat Keterangan Pendamping Ijazah (SKPI)</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Dokumen resmi rekam jejak capaian pembelajaran lulusan (CPL/KKNI Level 6) standar Permendikbud bilingual ID/EN.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-uph-navy hover:bg-uph-navy-dark text-white font-bold text-xs uppercase tracking-wider shadow transition-colors flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak SKPI Resmi (PDF)
            </button>
        </div>
    </div>

    <!-- Official SKPI Canvas -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 sm:p-12 print:p-0 print:border-none print:shadow-none print:rounded-none max-w-5xl mx-auto text-slate-900">
        
        <!-- Institutional Header / Kop Surat -->
        <div class="border-b-2 border-slate-900 pb-6 mb-8 text-center relative">
            <div class="flex items-center justify-center gap-4 mb-2">
                <div class="w-14 h-14 rounded-full bg-uph-navy text-uph-gold flex items-center justify-center text-2xl font-black font-display shrink-0 border-2 border-uph-gold">
                    UPH
                </div>
                <div class="text-left">
                    <h2 class="text-xl sm:text-2xl font-display font-black text-uph-navy tracking-tight uppercase">
                        UNIVERSITAS PELITA HARAPAN
                    </h2>
                    <p class="text-xs font-bold tracking-widest text-slate-600 uppercase">
                        SURAT KETERANGAN PENDAMPING IJAZAH (SKPI)
                    </p>
                    <p class="text-[11px] font-mono text-slate-500 tracking-wider">
                        DIPLOMA SUPPLEMENT &bull; PERMENDIKBUD NO. 81 TAHUN 2014
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 text-xs text-slate-500 italic max-w-3xl mx-auto leading-relaxed">
                "Surat Keterangan Pendamping Ijazah (SKPI) ini memuat pernyataan resmi universitas tentang capaian pembelajaran dan kualifikasi pemegang ijazah dalam KKNI."
            </div>
        </div>

        <!-- Section 1: Information Identifying the Qualification Holder -->
        <div class="mb-8 border border-slate-200 rounded-xl overflow-hidden text-xs">
            <div class="bg-slate-100 border-b border-slate-200 px-4 py-2 font-bold text-slate-800 uppercase tracking-wider text-[11px] flex justify-between">
                <span>1. INFORMASI TENTANG IDENTITAS DIRI PEMEGANG SKPI</span>
                <span class="italic font-normal text-slate-500">Holder of the Qualification</span>
            </div>
            <div class="p-4 grid grid-cols-2 gap-y-3 gap-x-6">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Lengkap / Full Name</span>
                    <strong class="text-slate-900 text-sm">{{ $clearance->student->user->full_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor Induk Mahasiswa / Student ID</span>
                    <strong class="text-slate-900 font-mono text-sm">{{ $clearance->student->nim }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Tanggal Kelulusan / Graduation Date</span>
                    <span class="text-slate-800 font-bold">
                        {{ $clearance->graduation_date ? $clearance->graduation_date->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Penomoran Ijazah Nasional / National Diploma PIN</span>
                    <strong class="text-uph-navy font-mono text-xs">{{ $clearance->national_pin_number ?? 'PIN-UPH-OFFICIAL-VERIFIED' }}</strong>
                </div>
            </div>
        </div>

        <!-- Section 2: Information Identifying the Awarding Institution -->
        <div class="mb-8 border border-slate-200 rounded-xl overflow-hidden text-xs">
            <div class="bg-slate-100 border-b border-slate-200 px-4 py-2 font-bold text-slate-800 uppercase tracking-wider text-[11px] flex justify-between">
                <span>2. INFORMASI TENTANG INSTITUSI PENERBIT</span>
                <span class="italic font-normal text-slate-500">Awarding Institution</span>
            </div>
            <div class="p-4 grid grid-cols-2 gap-y-3 gap-x-6">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Perguruan Tinggi / Higher Education Institution</span>
                    <span class="text-slate-900 font-bold">Universitas Pelita Harapan (UPH)</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Status Akreditasi Institusi / Institutional Accreditation</span>
                    <span class="text-emerald-700 font-bold">TERAKREDITASI UNGGUL (BAN-PT)</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Program Studi / Major Program</span>
                    <span class="text-slate-800 font-bold">{{ $clearance->student->studyProgram->name_id }}</span>
                    <span class="text-[10px] text-slate-400 italic block">{{ $clearance->student->studyProgram->name_en }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Bahasa Pengantar / Language of Instruction</span>
                    <span class="text-slate-800">Bahasa Indonesia & English (Bilingual)</span>
                </div>
            </div>
        </div>

        <!-- Section 3: Information on the Level of the Qualification -->
        <div class="mb-8 border border-slate-200 rounded-xl overflow-hidden text-xs">
            <div class="bg-slate-100 border-b border-slate-200 px-4 py-2 font-bold text-slate-800 uppercase tracking-wider text-[11px] flex justify-between">
                <span>3. INFORMASI TENTANG KUALIFIKASI DAN HASIL YANG DICAPAI</span>
                <span class="italic font-normal text-slate-500">Qualification Level & Framework</span>
            </div>
            <div class="p-4 grid grid-cols-2 gap-y-3 gap-x-6">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Jenjang Pendidikan / Level of Education</span>
                    <span class="text-slate-900 font-bold">Program Sarjana Strata Satu (S1) / Bachelor Degree</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Jenjang Kualifikasi KKNI / National Qualification Framework</span>
                    <span class="text-slate-900 font-bold font-mono">Level 6 KKNI</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Persyaratan Penerimaan / Entry Requirements</span>
                    <span class="text-slate-800">Lulus Sekolah Menengah Atas (SMA/SMK) & Seleksi PMB</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Lama Studi Standar / Standard Study Duration</span>
                    <span class="text-slate-800 font-mono">8 Semester (4 Tahun) &bull; 144 SKS</span>
                </div>
            </div>
        </div>

        <!-- Section 4: Learning Outcomes (CPL OBE Standard) -->
        <div class="mb-8 border border-slate-200 rounded-xl overflow-hidden text-xs">
            <div class="bg-slate-100 border-b border-slate-200 px-4 py-2 font-bold text-slate-800 uppercase tracking-wider text-[11px] flex justify-between">
                <span>4. CAPAIAN PEMBELAJARAN LULUSAN (LEARNING OUTCOMES - OBE CPL)</span>
                <span class="italic font-normal text-slate-500">KKNI Level 6 Competencies</span>
            </div>
            <div class="p-4 space-y-4 text-slate-800 leading-relaxed">
                <div>
                    <h5 class="font-bold text-uph-navy text-xs uppercase tracking-wide">A. Sikap & Tata Nilai (Attitudes & Values)</h5>
                    <p class="text-[11px] text-slate-600 mt-1">
                        Bertakwa kepada Tuhan Yang Maha Esa, menjunjung tinggi nilai kemanusiaan, moralitas, integritas akademik, kepemimpinan transformasional (True Knowledge, Faith in God, and Godly Character), serta kesadaran hukum dan etika profesi.
                    </p>
                </div>

                <div>
                    <h5 class="font-bold text-uph-navy text-xs uppercase tracking-wide">B. Penguasaan Pengetahuan (Knowledge Mastery)</h5>
                    <p class="text-[11px] text-slate-600 mt-1">
                        Menguasai konsep teoretis komputasi, rekayasa perangkat lunak berskala industri, arsitektur data terdistribusi, serta metodologi penelitian ilmiah mutakhir yang dapat diaplikasikan dalam penyelesaian masalah multidisiplin.
                    </p>
                </div>

                <div>
                    <h5 class="font-bold text-uph-navy text-xs uppercase tracking-wide">C. Keterampilan Khusus (Specialized Skills)</h5>
                    <p class="text-[11px] text-slate-600 mt-1">
                        Mampu merancang, mengimplementasikan, dan mengevaluasi solusi sistem informasi terintegrasi dengan menerapkan standar rekayasa modern, prinsip keamanan siber, dan jaminan kualitas perangkat lunak enterprise.
                    </p>
                </div>
            </div>
        </div>

        <!-- Legal Signatures -->
        <div class="grid grid-cols-2 gap-8 text-xs pt-4 border-t-2 border-slate-900">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400 text-2xl shrink-0">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <span class="font-bold text-slate-800 block text-xs">Validasi Digital Kementerian & UPH:</span>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-tight">
                        Dokumen SKPI ini diterbitkan secara sah dan tersinkronisasi dengan Pangkalan Data Pendidikan Tinggi (PDDIKTI).
                    </p>
                    <span class="font-mono text-[10px] text-slate-400 mt-1 block">
                        VERIFY: {{ hash('sha256', $clearance->id . '|' . $clearance->national_pin_number) }}
                    </span>
                </div>
            </div>

            <div class="text-right">
                <p class="text-slate-500">Tangerang, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-slate-800 mt-0.5">Universitas Pelita Harapan</p>
                <p class="text-slate-600 text-[11px]">Dekan Fakultas Ilmu Komputer</p>
                <div class="h-16 flex items-end justify-end">
                    <span class="text-[11px] text-slate-400 italic">[Tanda Tangan Digital Resmi]</span>
                </div>
                <p class="font-bold text-slate-900 border-t border-slate-300 pt-1 inline-block min-w-[200px]">
                    Prof. Dr. Ir. Samuel H. Tirtamulia, M.Sc.
                </p>
                <p class="text-[10px] text-slate-400 font-mono">NIP: 196903151994031001</p>
            </div>
        </div>

    </div>
</div>
@endsection
