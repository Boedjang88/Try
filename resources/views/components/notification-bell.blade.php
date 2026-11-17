<div class="relative" x-data="{
    open: false,
    unreadCount: 0,
    notifications: [],
    loading: false,
    async fetchNotifications() {
        try {
            const res = await fetch('/portal/notifications/unread');
            const data = await res.json();
            this.unreadCount = data.count || 0;
            this.notifications = data.notifications || [];
        } catch (e) {
            console.error('Failed to load notifications', e);
        }
    },
    async markAsRead(id, url) {
        const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
        try {
            await fetch(`/portal/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            });
            this.unreadCount = Math.max(0, this.unreadCount - 1);
            this.notifications = this.notifications.filter(n => n.id !== id);
            if (url) window.location.href = url;
        } catch (e) {
            if (url) window.location.href = url;
        }
    },
    async markAllRead() {
        const csrf = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
        try {
            await fetch('/portal/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            });
            this.unreadCount = 0;
            this.notifications = [];
        } catch (e) {
            console.error('Failed to mark all read', e);
        }
    },
    init() {
        this.fetchNotifications();
        setInterval(() => this.fetchNotifications(), 30000);
    }
}">
    <!-- Notification Bell Button -->
    <button type="button" 
            @click="open = !open" 
            class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors focus:outline-none"
            aria-label="Pemberitahuan">
        <i class="fa-regular fa-bell text-lg"></i>
        <template x-if="unreadCount > 0">
            <span class="absolute top-1.5 right-1.5 h-4 min-w-[16px] px-1 rounded-full bg-uph-crimson text-white font-mono text-[9px] font-black flex items-center justify-center ring-2 ring-slate-900 animate-pulse" 
                  x-text="unreadCount > 9 ? '9+' : unreadCount">
            </span>
        </template>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         @click.away="open = false" 
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-1"
         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-slate-900 border border-white/10 shadow-2xl z-50 text-slate-200 overflow-hidden">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between bg-slate-950/60">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-bell text-uph-gold text-xs"></i>
                <span class="font-display font-bold text-xs uppercase tracking-wider text-white">Notifikasi Aktivitas</span>
                <span class="px-1.5 py-0.5 rounded bg-uph-gold/20 text-uph-gold text-[10px] font-bold font-mono" x-text="unreadCount">0</span>
            </div>
            <template x-if="unreadCount > 0">
                <button type="button" @click="markAllRead()" class="text-[11px] text-slate-400 hover:text-uph-gold transition-colors font-medium">
                    Tandai Semua Dibaca
                </button>
            </template>
        </div>

        <!-- Notification List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-white/5">
            <template x-if="notifications.length === 0">
                <div class="p-8 text-center text-slate-500 text-xs">
                    <i class="fa-regular fa-bell-slash text-2xl mb-2 block text-slate-600"></i>
                    Tidak ada notifikasi baru saat ini.
                </div>
            </template>

            <template x-for="item in notifications" :key="item.id">
                <div @click="markAsRead(item.id, item.action_url)" 
                     class="p-3.5 hover:bg-white/5 cursor-pointer transition-colors flex items-start gap-3">
                    <div class="h-8 w-8 rounded-lg bg-uph-navy/80 text-uph-gold border border-uph-gold/30 flex items-center justify-center text-xs shrink-0 mt-0.5">
                        <i class="fa-solid" :class="{
                            'fa-receipt text-emerald-400': item.type === 'financial_unblock',
                            'fa-book-bookmark text-uph-gold': item.type === 'krs_submitted' || item.type === 'krs_approved',
                            'fa-pen-to-square text-amber-400': item.type === 'krs_revision_requested' || item.type === 'edom_required',
                            'fa-file-lines text-cyan-400': item.type === 'assignment_published',
                            'fa-circle-check text-emerald-400': item.type === 'leave_approved' || item.type === 'exam_submitted',
                            'fa-calendar text-uph-gold': item.type === 'semester_rollover',
                            'fa-bell text-slate-400': !['financial_unblock','krs_submitted','krs_approved','krs_revision_requested','edom_required','assignment_published','leave_approved','exam_submitted','semester_rollover'].includes(item.type)
                        }"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-xs text-white truncate" x-text="item.title"></p>
                        <p class="text-[11px] text-slate-400 line-clamp-2 mt-0.5 leading-relaxed" x-text="item.message"></p>
                        <span class="text-[9px] font-mono text-slate-500 mt-1 block" x-text="new Date(item.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
