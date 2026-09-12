<script setup lang="ts">
import { BellRing, Mail, Megaphone, MessageSquare, Send, X } from 'lucide-vue-next';
import { computed, PropType, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    event: Object,
    attendees: Array as PropType<any[]>
});

const form = useForm({
    event_id: props.event?.id,
    audience: 'all',
    preset: '',
    subject: '',
    body: '',
    chLinkUp: true,
    chEmail: false,
    chSMS: false,
    confirmText: ''
});

const filteredAttendees = computed(() => {
    if (!Array.isArray(props.attendees)) return 0

    const uniqueUsers: Record<number, boolean> = {}

    const filtered = props.attendees.filter(a => {
        const userId = a.user_id
        if (uniqueUsers[userId]) return false
        uniqueUsers[userId] = true

        switch (form.audience) {
            case 'checkedin':
                return Array.isArray(a.checkins) && a.checkins.length > 0
            case 'notchecked':
                return !a.checkins || a.checkins.length === 0
            case 'vip':
                return a.ticket_type === 'vip'
            case 'general':
                return a.ticket_type === 'general_admission'
            default:
                return true
        }
    })

    return filtered.length
})

const presets = computed((): any => {
    const ev = props.event || {}

    return {
        cancel: {
            subject: `Important: ${ev.title} has been canceled`,
            body: `Hello,

We’re sorry to share that {event} has been canceled.

We will send follow-up details regarding next steps.

Thank you for your support.`
        },
        datetime: {
            subject: `Update: New date/time for ${ev.title}`,
            body: `Hello,

Please note the time/date for {event} has changed.

New schedule: {date}
Location: {venue}

Thank you for your understanding.`
        },
        venue: {
            subject: `Update: Venue change for ${ev.title}`,
            body: `Hello,

The venue for {event} has changed.

New location: {venue}
Date/Time: {date}

Please arrive early for smooth check-in.`
        },
        checkin: {
            subject: `Check-in info for ${ev.title}`,
            body: `Hello,

Check-in for {event} starts 45 minutes before the event time.

Please have your QR ticket ready.

Date/Time: {date}
Venue: {venue}`
        }
    }
})

watch(() => form.preset, (val) => {
    if (!val || !presets.value[val]) return

    const preset = presets.value[val]

    form.subject = preset.subject
    form.body = preset.body
})

const messageAttendeesBroadcast = () => {
    form.event_id = props.event?.id;

    form.post(route('organizer.send.message.attendees.broadcast'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close')
            form.reset()
        },
        onError: (errors) => {
            console.log(errors)
        }
    })
}

const emit = defineEmits(['close']);
</script>

<template>
    <div v-if="show" id="broadcastModal" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/40" data-close="broadcast"></div>

        <div class="absolute inset-0 flex items-center justify-center p-3">
            <div class="w-full max-w-4xl rounded-3xl bg-white shadow-soft border overflow-hidden">
                <div class="p-4 border-b flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-lg font-semibold flex items-center gap-2">
                            <Megaphone class="w-5 h-5" />
                            Message attendees (Broadcast)
                        </div>
                        <div class="text-sm text-slate-500 truncate" id="broadcastSubtitle">
                            Broadcast update for {{ props.event?.title }}
                        </div>
                    </div>
                    <button class="p-2 rounded-xl hover:bg-slate-50" data-close="broadcast" aria-label="Close"
                        @click="emit('close')">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-4 grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5 space-y-3">
                        <div class="rounded-2xl border p-4">
                            <div class="text-sm font-semibold">Audience</div>
                            <div class="mt-2">
                                <select id="broadcastAudience"
                                    class="w-full px-3 py-2 rounded-xl border bg-white text-sm" v-model="form.audience">
                                    <option value="all">All attendees</option>
                                    <option value="checkedin">Checked-in only</option>
                                    <option value="notchecked">Not checked-in</option>
                                    <option value="vip">VIP only</option>
                                    <option value="general">General only</option>
                                </select>
                            </div>

                            <div class="mt-3 rounded-xl border p-3 bg-slate-50">
                                <div class="text-xs text-slate-500">Recipients</div>
                                <div class="mt-1 text-lg font-semibold mono" id="broadcastRecipientCount">
                                    {{ filteredAttendees }}
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border p-4">
                            <div class="text-sm font-semibold">Channels</div>
                            <div class="mt-3 space-y-2 text-sm">
                                <label
                                    class="flex items-center justify-between gap-3 p-3 rounded-xl border hover:bg-slate-50 cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <BellRing class="w-4 h-4" />
                                        LinkUp notification
                                    </span>
                                    <input id="chLinkUp" type="checkbox" class="w-4 h-4" checked
                                        v-model="form.chLinkUp" />
                                    <span class="text-red-500">{{ form.errors.chLinkUp }}</span>
                                </label>

                                <label
                                    class="flex items-center justify-between gap-3 p-3 rounded-xl border hover:bg-slate-50 cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <Mail class="w-4 h-4" />
                                        Email
                                    </span>
                                    <input id="chEmail" type="checkbox" class="w-4 h-4" v-model="form.chEmail" />
                                    <span class="text-red-500">{{ form.errors.chEmail }}</span>
                                </label>

                                <label
                                    class="flex items-center justify-between gap-3 p-3 rounded-xl border hover:bg-slate-50 cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <MessageSquare class="w-4 h-4" />
                                        SMS
                                    </span>
                                    <input id="chSMS" type="checkbox" class="w-4 h-4" v-model="form.chSMS" />
                                    <span class="text-red-500">{{ form.errors.chSMS }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-7 space-y-3">
                        <div class="rounded-2xl border p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold">Message</div>
                                    <div class="text-sm text-slate-500 mt-1">
                                        Tags: <span class="mono">{event}</span>, <span class="mono">{date}</span>, <span
                                            class="mono">{venue}</span> (auto-filled at send time)
                                    </div>
                                </div>

                                <select v-model="form.preset" class="px-3 py-2 rounded-xl border bg-white text-sm">
                                    <option value="">Presets</option>
                                    <option value="cancel">Event canceled</option>
                                    <option value="datetime">Date/time change</option>
                                    <option value="venue">Venue change</option>
                                    <option value="checkin">Check-in instructions</option>
                                </select>

                            </div>

                            <div class="mt-3">
                                <input v-model="form.subject"
                                    class="w-full px-3 py-2 rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-200 text-sm"
                                    placeholder="Subject (optional)" />
                                <span class="text-red-500">{{ form.errors.subject }}</span>
                            </div>

                            <div class="mt-2">
                                <textarea v-model="form.body"
                                    class="w-full px-3 py-2 rounded-xl border min-h-[150px] focus:outline-none focus:ring-2 focus:ring-sky-200 text-start text-sm"
                                    placeholder="Write your message…">
                                </textarea>
                                <span class="text-red-500">{{ form.errors.body }}</span>
                            </div>
                        </div>

                        <div class="rounded-2xl border p-4">
                            <div class="text-sm font-semibold">Confirm send</div>
                            <div class="text-sm text-slate-500 mt-1">Type <span class="mono font-semibold">SEND</span>
                                to confirm.</div>

                            <div class="mt-3 flex flex-col sm:flex-row gap-2">
                                <input id="broadcastConfirm" v-model="form.confirmText"
                                    class="flex-1 px-3 py-2 rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-200 mono"
                                    placeholder="Type SEND to confirm" />
                                <button id="btnSendBroadcast" @click="messageAttendeesBroadcast"
                                    class="px-4 py-2 rounded-xl bg-slate-900 text-white hover:opacity-95 flex items-center gap-2 justify-center">
                                    <Send class="w-4 h-4" />
                                    Send
                                </button>
                            </div>
                            <span class="text-red-500">{{ form.errors.confirmText }}</span>

                            <div id="broadcastResult" class="hidden mt-3 rounded-2xl border p-3 bg-slate-50 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t flex justify-end gap-2">
                    <button class="px-3 py-2 rounded-xl border hover:bg-slate-50 text-sm" data-close="broadcast"
                        @click="emit('close')">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
html,
body {
    background: #f6f8fb;
    color: #0f172a;
}

.shadow-soft {
    box-shadow: 0 18px 40px rgba(15, 23, 42, .10);
}

.mono {
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum" 1;
}
</style>