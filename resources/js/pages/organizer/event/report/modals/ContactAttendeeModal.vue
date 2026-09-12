<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { toast } from 'vue-sonner';
import {
    X, Ticket, Hash, PhoneCall, MessagesSquare,
    TriangleAlert, MessageSquare, Mail, Link,
    Megaphone, MessageCircle
} from 'lucide-vue-next';

const props = defineProps<{
    show: boolean;
    attendee: any | null;
}>();

const emit = defineEmits(['close', 'openBroadcast', 'openLinkUpMessage']);

const attendee = computed(() => props.attendee);

const initials = computed(() => {
    const name = attendee.value?.user?.name || attendee.value?.user_name || 'Attendee';
    return name.split(' ').map((n: string) => n.charAt(0)).join('').substring(0, 2).toUpperCase();
});

const ticketId = computed(() => attendee.value?.ticket_qrcode_id || attendee.value?.id || '—');
const orderId = computed(() => attendee.value?.stripe_id?.substring(0, 10) || attendee.value?.payment_id || '—');
const statusText = computed(() => {
    const status = attendee.value?.ticket_status;
    switch (status) {
        case 'confirmed': return 'Confirmed';
        case 'pending': return 'Pending';
        case 'canceled': return 'Canceled';
        case 'failed': return 'Failed';
        case 'checked_in': return 'Checked-in';
        default: return status || '—';
    }
});

const statusColorClass = computed(() => {
    const status = attendee.value?.ticket_status;
    switch (status) {
        case 'confirmed':
        case 'checked_in': return 'bg-green-500';
        case 'pending': return 'bg-yellow-500';
        case 'canceled':
        case 'failed': return 'bg-red-500';
        default: return 'bg-slate-300';
    }
});

const email = computed(() => attendee.value?.user?.email || attendee.value?.user_email || '—');
const phone = computed(() => attendee.value?.user?.phone_number || attendee.value?.user_phone || '—');

const hasLinkUp = computed(() => !!attendee.value?.user?.id);
const userStatus = computed(() => attendee.value?.user?.status == '1' ? 'Active' : 'Inactive');
const isCheckedIn = computed(() => attendee.value?.checkins?.length > 0);
const checkedInDate = computed(() => {
    if (!isCheckedIn.value) return '—';
    const date = attendee.value.checkins[0].checked_in_at;
    return new Date(date).toLocaleString([], {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
});

const gmailHref = computed(() => {
    const emailAddr = email.value;
    const eventTitle = attendee.value?.event?.title || 'Your ticket';
    if (!emailAddr || emailAddr === '—') return '#';
    const subject = encodeURIComponent(String(eventTitle) + ' – Your ticket');
    return `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(emailAddr)}&su=${subject}`;
});

function copyText(text: string, label: string) {
    if (!text || text === '—') {
        toast.error(`No ${label} to copy`);
        return;
    }
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text)
            .then(() => toast.success(`${label} copied`))
            .catch(() => toast.error(`Failed to copy ${label}`));
    }
}

function handleBroadcast() {
    emit('openBroadcast');
}
</script>

<template>
    <div v-show="show" class="fixed inset-0 z-50 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="emit('close')"></div>

        <div class="absolute inset-0 flex items-center justify-center p-3 pointer-events-none">
            <div
                class="w-full max-w-4xl rounded-3xl bg-white shadow-2xl border overflow-hidden pointer-events-auto transform transition-all animate-in fade-in zoom-in duration-200">
                <div class="p-4 border-b flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-lg font-semibold text-slate-900">Contact Attendee</div>
                        <div class="text-sm text-slate-500 truncate">
                            {{ attendee?.event?.title || 'Event Details' }}
                        </div>
                    </div>
                    <button @click="emit('close')"
                        class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-colors"
                        aria-label="Close">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-4 grid grid-cols-1 md:grid-cols-12 gap-4 max-h-[80vh] overflow-y-auto">
                    <!-- Left: Attendee details -->
                    <div class="md:col-span-5 rounded-2xl border bg-slate-50/30 p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl grid place-items-center text-white font-semibold shadow-soft shrink-0"
                                style="background: linear-gradient(135deg, #0ea5e9, #22c55e);">
                                {{ initials }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-lg leading-tight text-slate-900 truncate">
                                    {{ attendee?.user?.name || attendee?.user_name || 'Attendee' }}
                                </div>
                                <div class="text-sm text-slate-500 flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1">
                                        <Ticket class="w-3 h-3" />
                                        <span class="font-mono text-[11px]">{{ ticketId }}</span>
                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span class="inline-flex items-center gap-1">
                                        <Hash class="w-3 h-3" />
                                        <span class="font-mono text-[11px]">{{ orderId }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4 text-sm">
                            <div>
                                <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Email
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <div class="font-medium text-slate-700 truncate">{{ email }}</div>
                                    <button @click="copyText(email, 'Email')"
                                        class="px-2 py-1 rounded-lg border bg-white hover:bg-slate-50 text-[10px] font-semibold text-slate-600 transition-colors focus:ring-2 focus:ring-blue-100">
                                        Copy
                                    </button>
                                </div>
                            </div>

                            <div>
                                <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Phone
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <div class="font-medium text-slate-700 font-mono">{{ phone }}</div>
                                    <button @click="copyText(phone, 'Phone')"
                                        class="px-2 py-1 rounded-lg border bg-white hover:bg-slate-50 text-[10px] font-semibold text-slate-600 transition-colors focus:ring-2 focus:ring-blue-100">
                                        Copy
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-xl border p-3 bg-white">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full"
                                        :class="isCheckedIn ? 'bg-green-500' : 'bg-red-500'"></span>
                                    <div>
                                        <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400">
                                            Ticket status</div>
                                        <div class="font-semibold text-slate-700">
                                            {{ isCheckedIn ? 'Checked-in' : 'Not Checked-in' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right border-l pl-3">
                                    <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400">LinkUp
                                    </div>
                                    <div class="font-semibold"
                                        :class="attendee?.user?.status == '1' ? 'text-green-600' : 'text-red-600'">
                                        {{ userStatus }}
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border p-3 bg-white">
                                <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Checked-in
                                    Date</div>
                                <div class="font-medium text-slate-600">{{ checkedInDate }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Actions + in-app message -->
                    <div class="md:col-span-7 space-y-3">
                        <div class="rounded-2xl border p-4 bg-white">
                            <div class="text-sm font-semibold text-slate-900 mb-3">Quick actions</div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <button :disabled="!hasLinkUp || attendee?.user?.status == '0'"
                                    @click="emit('openLinkUpMessage')"
                                    class="px-4 py-3 rounded-2xl bg-slate-900 text-white shadow-lg hover:bg-slate-800 disabled:opacity-40 disabled:hover:bg-slate-900 flex items-center gap-2 justify-center transition-all font-semibold text-sm">
                                    <MessagesSquare class="w-5 h-5" />
                                    Message in LinkUp
                                </button>

                                <a :href="phone != '—' ? 'tel:' + phone : '#'"
                                    class="px-4 py-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 flex items-center gap-2 justify-center transition-all font-semibold text-sm text-slate-700">
                                    <PhoneCall class="w-5 h-5" />
                                    Call attendee
                                </a>
                            </div>

                            <!-- If no LinkUp account / blocked / inactive -->
                            <div v-if="!hasLinkUp || attendee?.user?.status == '0'" id="noLinkUpBanner"
                                class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 p-3">
                                <div class="flex items-start gap-2">
                                    <TriangleAlert class="w-5 h-5 text-amber-700 shrink-0" />
                                    <div class="min-w-0">
                                        <div class="font-semibold text-amber-900 text-sm">LinkUp messaging isn’t active
                                            for this attendee.</div>
                                        <div class="text-sm text-amber-900/80 mt-0.5">
                                            Send an activation invite link (SMS/Email) or copy the link.
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button id="btnSendInviteSMS"
                                                class="px-3 py-2 rounded-xl bg-white border border-amber-200 hover:bg-amber-100 text-sm flex items-center gap-2 transition-colors">
                                                <MessageSquare class="w-4 h-4" /> Send invite (SMS)
                                            </button>
                                            <button id="btnSendInviteEmail"
                                                class="px-3 py-2 rounded-xl bg-white border border-amber-200 hover:bg-amber-100 text-sm flex items-center gap-2 transition-colors">
                                                <Mail class="w-4 h-4" /> Send invite (Email)
                                            </button>
                                            <button @click="toast.success('Invite link copied')" id="btnCopyInviteLink"
                                                class="px-3 py-2 rounded-xl bg-white border border-amber-200 hover:bg-amber-100 text-sm flex items-center gap-2 transition-colors">
                                                <Link class="w-4 h-4" /> Copy invite link
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <a :href="gmailHref" target="_blank" rel="noopener noreferrer"
                                    class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-[13px] font-semibold text-slate-600 flex items-center gap-2 justify-center transition-colors">
                                    <Mail class="w-4 h-4" /> Email attendee (Gmail)
                                </a>
                                <button
                                    class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-[13px] font-semibold text-slate-600 flex items-center gap-2 justify-center transition-colors">
                                    <MessageCircle class="w-4 h-4" /> SMS attendee
                                </button>
                            </div>
                        </div>

                        <div class="rounded-2xl border p-4 bg-white hover:border-blue-200 transition-colors group">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div
                                        class="text-sm font-semibold text-slate-900 group-hover:text-sky-600 transition-colors">
                                        Connect all attendees</div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Broadcast updates for cancellations, changes, or instructions.
                                    </div>
                                </div>
                                <button @click="handleBroadcast"
                                    class="shrink-0 px-4 py-2.5 rounded-xl bg-sky-600 text-white hover:bg-sky-700 shadow-md shadow-sky-200 transition-all text-xs font-bold flex items-center gap-2">
                                    <Megaphone class="w-4 h-4" />
                                    Broadcast
                                </button>
                            </div>
                        </div>

                        <div class="rounded-2xl border overflow-hidden bg-white">
                            <div class="p-3 border-b bg-slate-50/50 flex items-center justify-between">
                                <div class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Contact log
                                </div>
                                <button
                                    class="text-[10px] px-2 py-1 rounded-lg border bg-white hover:bg-slate-50 font-bold text-slate-500 transition-colors">
                                    Export CSV
                                </button>
                            </div>
                            <div class="max-h-40 overflow-auto divide-y">
                                <div class="p-3 text-center text-xs text-slate-400 italic">No contact history available
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t bg-slate-50/50 flex justify-end gap-2">
                    <button
                        class="px-6 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-sm font-bold text-slate-600 transition-colors"
                        @click="emit('close')">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.shadow-soft {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}
</style>