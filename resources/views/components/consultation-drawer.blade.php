@props([
    'studentId' => null,
    'lecturerId' => null,
    'contextType' => 'krs',
    'advisorName' => 'Dosen Pembimbing Akademik',
])

<div x-data="{
    chatOpen: false,
    messages: [],
    newMessage: '',
    unreadCount: 0,
    loading: false,
    studentId: '{{ $studentId }}',
    lecturerId: '{{ $lecturerId }}',
    contextType: '{{ $contextType }}',

    async loadMessages() {
        if (!this.studentId || !this.lecturerId) return;
        try {
            const url = `/portal/consultation/messages?student_id=${this.studentId}&lecturer_id=${this.lecturerId}&context_type=${this.contextType}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.success) {
                this.messages = data.data;
                this.unreadCount = data.unread_count || 0;
                this.scrollToBottom();
            }
        } catch (e) {
            console.error('Failed to load consultation messages', e);
        }
    },

    async sendMessage(text = null) {
        const msg = text || this.newMessage;
        if (!msg || !msg.trim()) return;
        this.loading = true;
        try {
            const res = await fetch('/portal/consultation/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    student_id: this.studentId,
                    lecturer_id: this.lecturerId,
                    context_type: this.contextType,
                    message: msg.trim()
                })
            });
            const data = await res.json();
            if (data.success) {
                this.messages.push(data.data);
                this.newMessage = '';
                this.scrollToBottom();
            }
        } catch (e) {
            console.error('Failed to send consultation message', e);
        } finally {
            this.loading = false;
        }
    },

    scrollToBottom() {
        this.$nextTick(() => {
            const box = document.getElementById('consultation-messages-box');
            if (box) box.scrollTop = box.scrollHeight;
        });
    },

    init() {
        this.loadMessages();
        // Poll messages periodically
        setInterval(() => {
            if (this.chatOpen) this.loadMessages();
        }, 8000);
    }
}" class="relative z-40">

    <!-- Floating Chat Trigger Button -->
    <button @click="chatOpen = !chatOpen; if (chatOpen) loadMessages();" 
            class="fixed bottom-6 right-6 z-40 px-4 py-3 rounded-full bg-gradient-to-r from-uph-gold to-amber-500 text-uph-navy font-bold text-xs shadow-2xl hover:brightness-110 flex items-center gap-2.5 transition-all">
        <i class="fa-solid fa-comments text-base"></i>
        <span>Konsultasi KRS Dosen PA</span>
        <template x-if="unreadCount > 0">
            <span class="w-5 h-5 rounded-full bg-rose-600 text-white font-mono text-[10px] flex items-center justify-center font-bold animate-bounce" x-text="unreadCount"></span>
        </template>
    </button>

    <!-- Consultation Drawer / Modal -->
    <div x-show="chatOpen" style="display: none;" 
         class="fixed bottom-20 right-6 z-50 w-full max-w-md bg-slate-900 border border-white/20 rounded-2xl shadow-2xl overflow-hidden flex flex-col h-[520px]">
        
        <!-- Header -->
        <div class="p-3.5 bg-uph-navy border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-white/10 border border-uph-gold flex items-center justify-center text-uph-gold font-bold text-xs">
                    PA
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white">{{ $advisorName }}</h3>
                    <span class="text-[10px] text-emerald-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online Konsultasi
                    </span>
                </div>
            </div>

            <button @click="chatOpen = false" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Chat Bubble Container -->
        <div id="consultation-messages-box" class="flex-1 p-4 overflow-y-auto space-y-3 bg-black/40 text-xs">
            <template x-if="messages.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-slate-500 py-12 text-center">
                    <i class="fa-solid fa-comments text-2xl mb-1 text-slate-600"></i>
                    <p>Belum ada riwayat diskusi konsultasi.</p>
                    <span class="text-[10px] text-slate-600">Kirim pesan pertama atau gunakan chip cepat di bawah.</span>
                </div>
            </template>

            <template x-for="m in messages" :key="m.id">
                <div class="flex flex-col" :class="m.sender_user_id === '{{ auth()->id() }}' ? 'items-end' : 'items-start'">
                    <div class="max-w-[85%] rounded-2xl p-3 shadow-md space-y-1"
                         :class="m.sender_user_id === '{{ auth()->id() }}' 
                            ? 'bg-uph-navy text-white rounded-tr-none border border-uph-gold/30' 
                            : 'bg-slate-800 text-slate-200 rounded-tl-none border border-white/10'">
                        <p class="text-[10px] font-bold text-uph-gold" x-text="m.sender ? m.sender.full_name : 'User'"></p>
                        <p class="whitespace-pre-wrap text-xs leading-relaxed" x-text="m.message"></p>
                        <div class="flex items-center justify-end gap-1 text-[9px] text-slate-400">
                            <span x-text="new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Quick Action Chips -->
        <div class="p-2 bg-slate-900 border-t border-white/5 flex items-center gap-1.5 overflow-x-auto text-[10px]">
            <button type="button" @click="sendMessage('Mohon cek jadwal pengganti')" 
                    class="shrink-0 px-2.5 py-1 rounded-full bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 transition-colors">
                ⚡ Cek jadwal pengganti
            </button>
            <button type="button" @click="sendMessage('KRS sudah saya revisi sesuai arahan Dok')" 
                    class="shrink-0 px-2.5 py-1 rounded-full bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 transition-colors">
                ⚡ KRS sudah direvisi
            </button>
            <button type="button" @click="sendMessage('Mohon persetujuan penambahan SKS')" 
                    class="shrink-0 px-2.5 py-1 rounded-full bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 transition-colors">
                ⚡ Usulan tambah SKS
            </button>
        </div>

        <!-- Input Box -->
        <div class="p-3 bg-slate-900 border-t border-white/10">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <input type="text" x-model="newMessage" placeholder="Tuliskan pesan konsultasi..." 
                       class="flex-1 bg-slate-800 text-slate-200 border border-white/10 rounded-xl px-3 py-2 text-xs focus:border-uph-gold">
                <button type="submit" :disabled="loading" 
                        class="px-3.5 py-2 rounded-xl bg-uph-gold text-uph-navy font-bold text-xs hover:brightness-110 shadow flex items-center justify-center">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>
