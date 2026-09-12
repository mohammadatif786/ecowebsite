<template>
    <li :class="[
        'ntf card cursor-pointer transition-all duration-200',
        notification.unread ? 'unread' : ''
    ]" :data-type="notification.type" :data-id="notification.id" @click="$emit('click')">
        <article tabindex="0" :class="[
            'relative overflow-hidden rounded-2xl border bg-white backdrop-blur transition-all hover:shadow-lg',
            compact ? 'p-2.5' : 'p-3',
            notification.unread ? 'border-blue-200 bg-blue-50/30' : 'border-gray-200'
        ]">
            <!-- Unread indicator bar -->
            <span v-if="notification.unread" :class="['absolute left-0 top-0 h-full w-1', accentBarColor]"></span>

            <div class="flex gap-3">
                <!-- Avatar / Icon -->
                <div class="flex-shrink-0">
                    <img v-if="computedAvatar" :src="computedAvatar" :alt="notification.send_by || 'User'"
                        class="h-9 w-9 rounded-full object-cover ring-2 ring-white/70" loading="lazy" />
                    <span v-else :class="[
                        'h-9 w-9 rounded-full grid place-items-center ring-2 ring-white/70 text-lg',
                        leadBgColor
                    ]">
                        {{ displayIcon }}
                    </span>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-medium truncate text-gray-900">{{ notification.title }}</p>
                        <time class="text-xs text-gray-500 whitespace-nowrap">
                            {{ formatTimeAgo(notification.created_at) }}
                        </time>
                    </div>

                    <!-- Amount line for payments -->
                    <div v-if="notification.type === 'payment' && notification.metadata?.amount"
                        :class="['mt-1 text-sm font-medium', amountColor]">
                        ${{ formatAmount(notification.metadata.amount) }}
                        <span class="font-normal text-gray-500">
                            {{ (notification.metadata.currency || 'USD').toUpperCase() }}
                        </span>
                    </div>

                    <!-- Live Invite Actions -->
                    <div v-if="notification.type === 'live_invite'" class="mt-2">
                        <button
                            class="text-xs px-3 py-1.5 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 shadow-sm transition-colors"
                            @click.stop="$emit('accept-invite', notification)">
                            ✅ Accept & Join
                        </button>
                    </div>

                    <p :class="[
                        'text-sm text-gray-700 mt-1',
                        compact ? '' : 'line-clamp-2'
                    ]">
                        {{ notification.message || '' }}
                    </p>

                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                        <!-- Category chip -->
                        <span :class="['text-xs px-2 py-0.5 rounded-full', chipClasses]">
                            {{ chipLabel }}
                        </span>

                        <span v-if="notification.type === 'message' && notification.unread"
                            class="text-[10px] px-2 py-0.5 rounded-full bg-blue-600 text-white font-semibold">
                            New
                        </span>

                        <!-- CTAs -->
                        <button v-if="notification.metadata?.cta"
                            class="text-sm underline text-blue-600 hover:text-blue-700" @click.stop>
                            {{ notification.metadata.cta.label }}
                        </button>

                        <template v-if="notification.metadata?.ctaGroup">
                            <button v-for="(cta, idx) in notification.metadata.ctaGroup" :key="idx" :class="[
                                'text-xs px-2 py-1 rounded',
                                cta.kind === 'approve'
                                    ? 'bg-green-600 text-white hover:bg-green-700'
                                    : 'border hover:bg-gray-50'
                            ]" @click.stop>
                                {{ cta.label }}
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col items-end gap-2">
                    <label class="text-xs text-gray-500 inline-flex items-center gap-2">
                        <input type="checkbox" :checked="isSelected" @change="$emit('toggle-select')" @click.stop
                            class="rounded" />
                        Select
                    </label>
                    <div class="flex items-center gap-1">
                        <button v-if="notification.unread" @click.stop="$emit('mark-read')"
                            class="opacity-70 hover:opacity-100 text-xs px-2 py-1 rounded border hover:bg-gray-50"
                            title="Mark as read">
                            Read
                        </button>
                        <button @click.stop="$emit('delete')"
                            class="opacity-70 hover:opacity-100 text-xs px-2 py-1 rounded border hover:bg-red-50 hover:text-red-600"
                            title="Delete">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </article>
    </li>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Notification {
    id: number;
    title: string;
    message: string;
    send_by: string;
    type: 'message' | 'match' | 'payment' | 'gift' | 'system' | 'live_invite';
    context?: string;
    unread: boolean;
    priority: boolean;
    icon?: string;
    avatar?: string;
    user?: any;
    metadata?: any;
    created_at: string;
}

const props = defineProps<{
    notification: Notification;
    compact: boolean;
    isSelected: boolean;
    appURL: string;
}>();

defineEmits<{
    (e: 'toggle-select'): void;
    (e: 'mark-read'): void;
    (e: 'delete'): void;
    (e: 'delete'): void;
    (e: 'click'): void;
    (e: 'accept-invite', notification: Notification): void;
}>();

// Icon mapping
const iconByType: Record<string, string> = {
    payment: '💵',
    gift: '🎁',
    message: '💬',
    match: '💘',
    system: '🔔',
    live_invite: '🎥',
    marketplace_order: '🛍️',
};

const displayIcon = computed(() => {
    return props.notification.icon || iconByType[props.notification.type] || '🔔';
});
const computedAvatar = computed(() => {
    const sender = props.notification?.sender;

    const organizerPhoto = sender?.organizer_profile?.media?.profile_picture;

    if (props.notification?.type === 'message' && organizerPhoto) {
        return props.appURL + organizerPhoto;
    }

    return (
        props.notification?.avatar ??
        props.notification?.user?.avatar ??
        sender?.avatar ??
        ''
    );
});
// Background colors
const leadBgColor = computed(() => {
    const { type, metadata } = props.notification;
    if (type === 'payment') {
        const status = metadata?.status;
        if (status === 'received') return 'bg-green-50';
        if (status === 'failed') return 'bg-red-50';
        if (status === 'request') return 'bg-amber-50';
        return 'bg-green-50';
    }
    if (type === 'gift') return 'bg-pink-50';
    if (type === 'message') return 'bg-blue-50';
    if (type === 'match') return 'bg-rose-50';
    if (type === 'system') return 'bg-gray-100';
    if (type === 'live_invite') return 'bg-purple-50';
    if (type === 'marketplace_order') return 'bg-emerald-50';
    return 'bg-gray-100';
});

const accentBarColor = computed(() => {
    const { type, metadata } = props.notification;
    if (type === 'payment') {
        const status = metadata?.status;
        if (status === 'received') return 'bg-green-600';
        if (status === 'failed') return 'bg-red-600';
        if (status === 'request') return 'bg-amber-500';
        return 'bg-green-600';
    }
    if (type === 'gift') return 'bg-pink-500';
    if (type === 'message') return 'bg-blue-600';
    if (type === 'match') return 'bg-rose-500';
    if (type === 'system') return 'bg-amber-500';
    if (type === 'live_invite') return 'bg-purple-600';
    if (type === 'marketplace_order') return 'bg-emerald-600';
    return 'bg-blue-600';
});

const amountColor = computed(() => {
    const status = props.notification.metadata?.status;
    if (status === 'received') return 'text-green-700';
    if (status === 'failed') return 'text-red-700';
    if (status === 'request') return 'text-amber-700';
    return '';
});

// Chip styling
const chipClasses = computed(() => {
    const { type, metadata } = props.notification;
    if (type === 'payment') {
        const status = metadata?.status;
        if (status === 'received') return 'bg-green-100 text-green-700';
        if (status === 'failed') return 'bg-red-100 text-red-700';
        if (status === 'request') return 'bg-amber-100 text-amber-700';
        return 'bg-green-100 text-green-700';
    }
    if (type === 'gift') return 'bg-pink-100 text-pink-700';
    if (type === 'match') return 'bg-rose-100 text-rose-700';
    if (type === 'message') return 'bg-gray-100 text-gray-700';
    if (type === 'system') return 'bg-gray-100 text-gray-700';
    if (type === 'live_invite') return 'bg-purple-100 text-purple-700';
    if (type === 'marketplace_order') return 'bg-emerald-100 text-emerald-700';
    return 'bg-gray-100 text-gray-700';
});

const chipLabel = computed(() => {
    const { type, context, metadata } = props.notification;
    if (type === 'payment') {
        const status = metadata?.status;
        if (status === 'received') return 'Received';
        if (status === 'failed') return 'Failed';
        if (status === 'request') return 'Request';
        return 'Payment';
    }
    if (type === 'gift') return 'Gift';
    if (type === 'match') return 'Match';
    if (type === 'message') return 'Message';
    if (type === 'system') return 'System';
    if (type === 'live_invite') return 'Live Invite';
    if (type === 'marketplace_order') return 'Marketplace Orders';
    return context || type;
});

// Formatting helpers
function formatTimeAgo(dateTime: string): string {
    const diff = Math.max(1, Math.round((Date.now() - new Date(dateTime).getTime()) / 1000));
    if (diff < 60) return `${diff}s ago`;
    const m = Math.round(diff / 60);
    if (m < 60) return `${m}m ago`;
    const h = Math.round(m / 60);
    if (h < 24) return `${h}h ago`;
    const d = Math.round(h / 24);
    return d === 1 ? 'Yesterday' : `${d}d ago`;
}

function formatAmount(n: number): string {
    return Number(n).toFixed(2);
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
