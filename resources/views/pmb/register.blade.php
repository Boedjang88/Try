@extends('layouts.public')

@section('content')
<div class="bg-gradient-to-b from-uph-navy-dark to-uph-navy text-white py-12 border-b-2 border-uph-gold">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-2">
        <span class="inline-block px-3 py-1 rounded-full bg-uph-gold/20 text-uph-gold text-xs font-bold border border-uph-gold/30">
            Penerimaan Mahasiswa Baru T.A. 2026/2027
        </span>
        <h1 class="text-3xl sm:text-4xl font-display font-black text-white">Formulir Pendaftaran Online</h1>
        <p class="text-xs sm:text-sm text-slate-300">
            Lengkapi data pendaftaran Anda untuk mendapatkan Nomor Virtual Account dan akses Ujian CBT Beasiswa UPH.
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
        <div class="p-6 sm:p-8 bg-surface-slate border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-display font-bold text-lg text-uph-navy">Data Calon Mahasiswa</h2>
                <p class="text-xs text-slate-500">Pastikan informasi identitas sesuai dengan KTP / Kartu Keluarga / Akta Kelahiran.</p>
            </div>
            <div class="text-right shrink-0">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Biaya Formulir</span>
                <span class="font-display font-bold text-uph-crimson text-lg">Rp 500.000</span>
            </div>
        </div>

        @if($errors->any())
        <div class="m-6 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
            <strong class="font-bold block mb-1">Terdapat kesalahan dalam pengisian formulir:</strong>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="/admissions/pmb/register" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Program Studi Selection -->
            <div>
                <label for="target_study_program_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Pilihan Program Studi <span class="text-uph-crimson">*</span>
                </label>
                <select name="target_study_program_id" id="target_study_program_id" required class="w-full h-11 px-3 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-uph-navy">
                    <option value="">-- Pilih Program Studi Tujuan --</option>
                    @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" {{ (old('target_study_program_id', $selectedProdiId) == $prodi->id) ? 'selected' : '' }}>
                            {{ $prodi->faculty->name_id }} - {{ $prodi->name_id }} ({{ $prodi->degree_level }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Full Name -->
                <div>
                    <label for="full_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nama Lengkap (sesuai KTP/Ijazah) <span class="text-uph-crimson">*</span>
                    </label>
                    <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required placeholder="Contoh: Michael Timothy Santoso" class="w-full h-11 px-3 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-uph-navy">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Alamat Email Aktif <span class="text-uph-crimson">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="w-full h-11 px-3 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-uph-navy">
                </div>

                <!-- Phone / WhatsApp -->
                <div>
                    <label for="phone_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nomor WhatsApp / Ponsel <span class="text-uph-crimson">*</span>
                    </label>
                    <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" required placeholder="081234567890" class="w-full h-11 px-3 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-uph-navy">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Buat Kata Sandi Akun <span class="text-uph-crimson">*</span>
                    </label>
                    <input type="password" name="password" id="password" required placeholder="Minimal 8 karakter" class="w-full h-11 px-3 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-uph-navy">
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Jenis Kelamin <span class="text-uph-crimson">*</span>
                    </label>
                    <div class="flex items-center gap-6 h-11 px-3 border border-slate-300 rounded-lg">
                        <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer">
                            <input type="radio" name="gender" value="L" {{ old('gender') === 'L' ? 'checked' : '' }} required class="text-uph-navy focus:ring-uph-navy">
                            <span>Laki-Laki</span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer">
                            <input type="radio" name="gender" value="P" {{ old('gender') === 'P' ? 'checked' : '' }} class="text-uph-navy focus:ring-uph-navy">
                            <span>Perempuan</span>
                        </label>
                    </div>
                </div>

                <!-- Birth Date -->
                <div>
                    <label for="birth_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tanggal Lahir
                    </label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" class="w-full h-11 px-3 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-uph-navy">
                </div>
            </div>

            <!-- Agreement Checkbox -->
            <div class="pt-4 border-t border-slate-100">
                <label class="flex items-start gap-3 cursor-pointer text-xs text-slate-600">
                    <input type="checkbox" required class="mt-0.5 rounded text-uph-navy focus:ring-uph-navy">
                    <span>
                        Saya menyatakan bahwa seluruh data yang diisikan adalah benar dan bersedia mengikuti seluruh tahapan seleksi PMB UPH termasuk Ujian CBT Online dan kepatuhan kode etik universitas.
                    </span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                <span class="text-xs text-slate-400">
                    Setelah mendaftar, nomor Virtual Account pembayaran akan langsung diterbitkan.
                </span>
                <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-uph-crimson hover:bg-uph-crimson-hover text-white font-display font-bold text-xs uppercase tracking-wider shadow-uph-crimson transition-all transform hover:-translate-y-0.5">
                    Kirim & Terbitkan Virtual Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
