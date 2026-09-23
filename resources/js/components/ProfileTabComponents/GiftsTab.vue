<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { Gift, Wallet, Activity, TrendingUp } from 'lucide-vue-next';

interface GiftData {
    id: number;
    sender_id: number;
    recieved_id: number;
    vibe_id?: number;
    source?: string;
    name: string;
    coins: number;
    status: string;
    created_at: string;
    sender?: {
        id: number;
        name: string;
    };
    receiver?: {
        id: number;
        name: string;
    };
}

interface ActivityItem {
    id: string;
    kind: string;
    gift: string;
    qty: number;
    coins: number;
    gross: number;
    source: string;
    to?: {
        id: string;
        name: string;
    };
    from?: {
        id: string;
        name: string;
    };
    context: string;
    date: string;
    status: string;
    sortDate: Date;
}

// COIN TO USD RATE
const COIN_RATE = 0.01;
const CASHOUT_PAYOUT_RATIO = 0.5;

const props = defineProps<{
    gifts_sent: GiftData[];
    gifts_received: GiftData[];
    all_gifts: GiftData[];
    user_wallet_balance?: number;
}>();

// Tab state
const activeTab = ref('wallet');

// Convert to cash state
const selectedCoins = ref(0);
const minCashout = ref(25);
const isConverting = ref(false);
const conversionMessage = ref('');
const showConversionModal = ref(false);

// Transform real gift data into activity format
const activity = computed((): ActivityItem[] => {
    const transformed: ActivityItem[] = [];

    // Add sent gifts
    props.gifts_sent?.forEach((gift: GiftData) => {
        const coinVal = Number(gift.coins) || 0;
        const giftSource = gift.source || (gift.vibe_id ? 'Vibes' : (gift.name?.toLowerCase().includes('u vibe') ? 'U Vibes' : 'LinkUp'));
        transformed.push({
            id: `G-SENT-${gift.id}`,
            kind: 'GIFT_SENT',
            gift: gift.name || 'Gift coins',
            qty: 1,
            coins: coinVal,
            gross: coinVal * COIN_RATE,
            source: giftSource,
            to: {
                id: gift.receiver?.id?.toString() || 'Unknown',
                name: gift.receiver?.name || 'Unknown User'
            },
            context: `Sent via ${giftSource}`,
            date: gift.created_at ? new Date(gift.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : 'Unknown',
            status: gift.status || 'sent',
            sortDate: new Date(gift.created_at || 0)
        });
    });

    // Add received gifts
    props.gifts_received?.forEach((gift: GiftData) => {
        const coinVal = Number(gift.coins) || 0;
        const giftSource = gift.source || (gift.vibe_id ? 'Vibes' : (gift.name?.toLowerCase().includes('u vibe') ? 'U Vibes' : 'LinkUp'));
        transformed.push({
            id: `G-RECV-${gift.id}`,
            kind: 'GIFT_RECEIVED',
            gift: gift.name || 'Gift coins',
            qty: 1,
            coins: coinVal,
            gross: coinVal * COIN_RATE,
            source: giftSource,
            from: {
                id: gift.sender?.id?.toString() || 'Unknown',
                name: gift.sender?.name || 'Unknown User'
            },
            context: `Received via ${giftSource}`,
            date: gift.created_at ? new Date(gift.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : 'Unknown',
            status: gift.status || 'received',
            sortDate: new Date(gift.created_at || 0)
        });
    });

    // Sort by date (newest first)
    return transformed.sort((a, b) => b.sortDate.getTime() - a.sortDate.getTime());
});

// Computed properties for statistics
const totalSentCoins = computed(() =>
    activity.value.filter(item => item.kind === 'GIFT_SENT').reduce((sum, item) => sum + item.coins, 0)
);

const totalReceivedCoins = computed(() =>
    activity.value.filter(item => item.kind === 'GIFT_RECEIVED').reduce((sum, item) => sum + item.coins, 0)
);

const totalSent = computed(() => totalSentCoins.value * COIN_RATE);
const totalReceived = computed(() => totalReceivedCoins.value * COIN_RATE);

const sentGifts = computed(() => activity.value.filter(item => item.kind === 'GIFT_SENT'));
const receivedGifts = computed(() => activity.value.filter(item => item.kind === 'GIFT_RECEIVED'));

// Calculate top received gifts
const top_gift = computed(() => {
    const giftCounts: { [key: string]: number } = {};
    receivedGifts.value.forEach(gift => {
        if (giftCounts[gift.gift]) {
            giftCounts[gift.gift] += gift.qty;
        } else {
            giftCounts[gift.gift] = gift.qty;
        }
    });

    return Object.entries(giftCounts)
        .map(([name, qty]) => ({ name, qty }))
        .sort((a, b) => (b as any).qty - (a as any).qty)
        .slice(0, 5);
});

// Available coins for conversion
const availableCoins = computed(() => {
    const received = props.gifts_received || [];
    return received
        .filter(g => g && !(g as any).status)
        .reduce((sum, g) => sum + (Number((g as any).coins) || 0), 0);
});

// Available cash value
const availableCash = computed(() => availableCoins.value * COIN_RATE);

// Cash user will actually receive after platform split
const availablePayoutCash = computed(() => availableCash.value * CASHOUT_PAYOUT_RATIO);

// Platform share (informational)
const availablePlatformShareCash = computed(() => availableCash.value - availablePayoutCash.value);

// Meets minimum requirement
const meetsMinimum = computed(() => availablePayoutCash.value >= minCashout.value);

// Selected cash value
const selectedCash = computed(() => selectedCoins.value * COIN_RATE * CASHOUT_PAYOUT_RATIO);

// Platform share for selected amount (informational)
const selectedPlatformShare = computed(() => (selectedCoins.value * COIN_RATE) - selectedCash.value);

// Wallet balance after conversion
const walletAfterConversion = computed(() => {
    const currentBalance = Number(props.user_wallet_balance ?? 0) || 0;
    const cashToAdd = Number(selectedCash.value) || 0;
    return currentBalance + cashToAdd;
});

// Can convert
const canConvert = computed(() => {
    return meetsMinimum.value && selectedCoins.value > 0 && selectedCoins.value <= availableCoins.value;
});

const tabs = [
    { id: 'wallet', name: 'Gift Wallet', icon: Wallet },
    { id: 'activity', name: 'Activity', icon: Activity },
    { id: 'summary', name: 'Summary', icon: TrendingUp },
    { id: 'convert_to_cash', name: 'Convert To Cash', icon: '💸' }
];

function directionPill(kind: string) {
    if (kind === 'GIFT_SENT') {
        return `<span class="inline-flex items-center gap-1 border rounded-full px-2.5 py-1 text-xs font-black bg-rose-100 border-rose-200 text-rose-700 shadow-2xs">📤 Sent</span>`;
    }
    return `<span class="inline-flex items-center gap-1 border rounded-full px-2.5 py-1 text-xs font-black bg-emerald-100 border-emerald-200 text-emerald-700 shadow-2xs">📥 Received</span>`;
}

function sourceBadge(source: string) {
    const map: Record<string, { bg: string; icon: string }> = {
        'Vibes': { bg: 'bg-purple-100 border-purple-200 text-purple-800', icon: '✨' },
        'U Vibes': { bg: 'bg-indigo-100 border-indigo-200 text-indigo-800', icon: '🎓' },
        'Live': { bg: 'bg-amber-100 border-amber-200 text-amber-800', icon: '📡' },
        'LinkUp': { bg: 'bg-sky-100 border-sky-200 text-sky-800', icon: '💬' },
    };
    const s = map[source] || map['LinkUp'];
    return `<span class="inline-flex items-center gap-1 border rounded-full px-2.5 py-1 text-xs font-black ${s.bg}">${s.icon} ${source}</span>`;
}

function giftDetails(r: ActivityItem) {
    if (r.kind === "GIFT_SENT") return `To <span class="font-black">${r.to?.name || '—'}</span> <span class="text-xs text-slate-500 font-bold">(${r.to?.id || ''})</span><div class="text-xs text-slate-500 font-bold mt-1">${r.context || ''}</div>`;
    if (r.kind === "GIFT_RECEIVED") return `From <span class="font-black">${r.from?.name || '—'}</span> <span class="text-xs text-slate-500 font-bold">(${r.from?.id || ''})</span><div class="text-xs text-slate-500 font-bold mt-1">${r.context || ''}</div>`;
    return `<span class="text-slate-700 font-bold">${r.context || '—'}</span>`;
}

// Convert to cash functions
function setMaxCoins() {
    selectedCoins.value = availableCoins.value;
}

function setAllCoins() {
    selectedCoins.value = availableCoins.value;
}

function updateConversionMessage() {
    if (!meetsMinimum.value) {
        const needed = minCashout.value - availablePayoutCash.value;
        conversionMessage.value = `You need $${needed.toFixed(2)} more in received gifts to cash out (minimum $${minCashout.value}).`;
    } else if (selectedCoins.value === 0) {
        conversionMessage.value = 'Select coins to convert (Convert All, Max, or enter a custom amount).';
    } else {
        conversionMessage.value = `Ready to convert ${selectedCoins.value.toLocaleString()} coins for $${selectedCash.value.toFixed(2)}.`;
    }
}

async function convertToCash() {
    if (!canConvert.value) return;

    isConverting.value = true;
    conversionMessage.value = '';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (!csrfToken) {
            conversionMessage.value = 'CSRF token not found. Please refresh the page.';
            return;
        }

        const response = await fetch('/new_frontend/convert-gifts-to-cash', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                coins: selectedCoins.value,
                minimum_cashout: minCashout.value
            })
        });

        if (!response.ok) {
            const errorText = await response.text();
            if (response.status === 419) {
                conversionMessage.value = 'CSRF token mismatch. Please refresh the page and try again.';
            } else {
                conversionMessage.value = `Server error (${response.status}). Please try again later.`;
            }
            return;
        }

        const data = await response.json();

        if (data.success) {
            selectedCoins.value = 0;
            showConversionModal.value = false;
            conversionMessage.value = data.message;

            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            conversionMessage.value = data.message || 'Conversion failed. Please try again.';
        }
    } catch (error) {
        console.error('Conversion error:', error);
        conversionMessage.value = 'Conversion failed. Please try again later.';
    } finally {
        isConverting.value = false;
    }
}

watch([selectedCoins, minCashout], updateConversionMessage);
</script>

<template>
    <div class="bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5">
        <!-- Tab Navigation -->
        <div class="flex space-x-1 border-b border-slate-200 mb-6">
            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                'flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors border-b-2',
                activeTab === tab.id
                    ? 'text-sky-700 border-sky-700 bg-sky-50/50'
                    : 'text-slate-600 border-transparent hover:text-slate-800 hover:bg-slate-50'
            ]">
                <component :is="tab.icon" class="w-4 h-4" />
                {{ tab.name }}
            </button>
        </div>

        <!-- Gift Wallet Tab -->
        <div v-if="activeTab === 'wallet'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Gift Wallet</div>
                    <div class="text-sm text-slate-500 mt-1">Purchased vs collected revenue</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-pink-50 border border-pink-100 grid place-items-center">
                    <Gift class="w-5 h-5 text-pink-700" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Sent (Rose/Red color coded) -->
                <div class="relative overflow-hidden border border-rose-200 rounded-[18px] bg-rose-50/30 p-[14px]">
                    <div class="text-xs text-rose-700 font-black flex items-center gap-1.5">
                        <span>📤</span> Gifts Sent
                    </div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-3xl font-black text-rose-700">{{ totalSentCoins }}</div>
                        <div class="text-sm font-bold text-rose-500">Coins</div>
                    </div>
                    <div class="text-xs text-rose-600 font-bold mt-1">Value: ${{ totalSent.toFixed(2) }}</div>
                </div>

                <!-- Received (Emerald/Green color coded) -->
                <div class="relative overflow-hidden border border-emerald-200 rounded-[18px] bg-emerald-50/30 p-[14px]">
                    <div class="text-xs text-emerald-700 font-black flex items-center gap-1.5">
                        <span>📥</span> Gifts Received
                    </div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-3xl font-black text-emerald-700">{{ totalReceivedCoins }}</div>
                        <div class="text-sm font-bold text-emerald-500">Coins</div>
                    </div>
                    <div class="text-xs text-emerald-600 font-bold mt-1">Value: ${{ totalReceived.toFixed(2) }}</div>
                </div>
            </div>

            <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                <div class="text-xs text-slate-500 font-black">Top gift types (received)</div>
                <div class="mt-2 grid gap-2">
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50" v-for="item in top_gift" :key="item.name">
                        <div class="font-black text-slate-900 flex items-center gap-2">
                            <span class="text-lg">🎁</span> {{ item.name }}
                        </div>
                        <div class="text-xs text-emerald-700 font-extrabold bg-emerald-100 px-2.5 py-1 rounded-full border border-emerald-200">
                            {{ item.qty }} received
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Tab -->
        <div v-if="activeTab === 'activity'" class="space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <div class="text-lg font-black tracking-tight">Gift Activity</div>
                    <div class="text-sm text-slate-500 mt-1">Color coded sent (Rose) & received (Green) by origin</div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-700 border border-rose-200">
                        📤 Sent: {{ sentGifts.length }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-700 border border-emerald-200">
                        📥 Received: {{ receivedGifts.length }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-[18px] border border-slate-300/35 bg-white hide-scroll">
                <table class="w-full text-[0.875rem]">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">ID</th>
                            <th class="text-left px-4 py-3 font-black">Direction</th>
                            <th class="text-left px-4 py-3 font-black">Origin</th>
                            <th class="text-left px-4 py-3 font-black">Gift</th>
                            <th class="text-left px-4 py-3 font-black">Coins / Value</th>
                            <th class="text-left px-4 py-3 font-black">Details</th>
                            <th class="text-left px-4 py-3 font-black">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in activity" :key="item.id"
                            :class="[
                                'transition border-b border-slate-100 last:border-none',
                                item.kind === 'GIFT_SENT'
                                    ? 'bg-rose-50/20 hover:bg-rose-50/60 border-l-4 border-l-rose-500'
                                    : 'bg-emerald-50/20 hover:bg-emerald-50/60 border-l-4 border-l-emerald-500'
                            ]">
                            <td class="px-4 py-3.5 align-middle font-mono font-bold text-xs text-slate-500">{{ item.id }}</td>
                            <td class="px-4 py-3.5 align-middle" v-html="directionPill(item.kind)"></td>
                            <td class="px-4 py-3.5 align-middle" v-html="sourceBadge(item.source)"></td>
                            <td class="px-4 py-3.5 align-middle font-black text-slate-900">{{ item.gift }}</td>
                            <td class="px-4 py-3.5 align-middle">
                                <div :class="['font-black text-sm', item.kind === 'GIFT_SENT' ? 'text-rose-600' : 'text-emerald-600']">
                                    {{ item.kind === 'GIFT_SENT' ? '-' : '+' }}{{ item.coins }} Coins
                                </div>
                                <div class="text-[11px] text-slate-400 font-bold">${{ item.gross.toFixed(2) }}</div>
                            </td>
                            <td class="px-4 py-3.5 align-middle" v-html="giftDetails(item)"></td>
                            <td class="text-slate-500 font-bold text-xs px-4 py-3.5 align-middle whitespace-nowrap">{{ item.date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Tab -->
        <div v-if="activeTab === 'summary'" class="space-y-6">
            <div>
                <div class="text-lg font-black tracking-tight">Gift Summary</div>
                <div class="text-sm text-slate-500 mt-1">Your gifting activity overview</div>
            </div>

            <!-- Main Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative overflow-hidden border border-rose-200 rounded-[18px] bg-rose-50/30 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-rose-700 font-black">Total Spent</div>
                            <div class="mt-1 flex items-baseline gap-2">
                                <div class="text-4xl font-black text-rose-700">{{ totalSentCoins }}</div>
                                <div class="text-sm font-bold text-rose-500">Coins</div>
                            </div>
                            <div class="text-xs text-rose-600 font-bold mt-1">Value: ${{ totalSent.toFixed(2) }}</div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-rose-100 border border-rose-200 grid place-items-center">
                            <Gift class="w-5 h-5 text-rose-700" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-emerald-200 rounded-[18px] bg-emerald-50/30 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-emerald-700 font-black">Total Received</div>
                            <div class="mt-1 flex items-baseline gap-2">
                                <div class="text-4xl font-black text-emerald-700">{{ totalReceivedCoins }}</div>
                                <div class="text-sm font-bold text-emerald-500">Coins</div>
                            </div>
                            <div class="text-xs text-emerald-600 font-bold mt-1">Value: ${{ totalReceived.toFixed(2) }}</div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-100 border border-emerald-200 grid place-items-center">
                            <Gift class="w-5 h-5 text-emerald-700" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-rose-200 rounded-[18px] bg-rose-50/20 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 border border-rose-200 grid place-items-center">
                            <Gift class="w-4 h-4 text-rose-700" />
                        </div>
                        <div class="text-sm font-black text-rose-900">Sent Activity</div>
                    </div>
                    <div class="text-3xl font-black text-rose-700">{{ sentGifts.length }}</div>
                    <div class="text-xs font-bold text-rose-500 mt-1">Gifts sent to creators & friends</div>
                </div>

                <div class="border border-emerald-200 rounded-[18px] bg-emerald-50/20 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 border border-emerald-200 grid place-items-center">
                            <Gift class="w-4 h-4 text-emerald-700" />
                        </div>
                        <div class="text-sm font-black text-emerald-900">Received Activity</div>
                    </div>
                    <div class="text-3xl font-black text-emerald-700">{{ receivedGifts.length }}</div>
                    <div class="text-xs font-bold text-emerald-500 mt-1">Gifts received from supporters</div>
                </div>
            </div>
        </div>

        <!-- CONVERT -->
        <section v-if="activeTab === 'convert_to_cash'" class="space-y-6">
            <div class="flex items-start justify-between gap-6 flex-wrap">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Convert to Cash</h2>
                    <p class="mt-1 text-slate-500">Turn your received gifts into wallet cash.</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <div class="text-xs text-slate-500 font-semibold">Cash-out minimum</div>
                        <div class="mt-1 flex items-center gap-2">
                            <select v-model="minCashout"
                                class="border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold">
                                <option value="25">$25</option>
                                <option value="50">$50</option>
                            </select>
                            <span class="text-xs text-slate-400">(recommended)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Note -->
            <div class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-5">
                <p class="font-semibold text-amber-900">Note</p>
                <p class="mt-1 text-amber-900/80 text-sm leading-relaxed">
                    To convert to cash, your available received balance must meet the cash-out minimum.
                    If you don't meet the minimum yet, keep collecting gifts until you do.
                </p>
            </div>

            <div class="mt-7 grid grid-cols-1 lg:grid-cols-2 gap-7">
                <!-- Left: balances -->
                <div class="rounded-3xl border border-slate-200 p-7 bg-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-500 font-semibold">Available to convert</p>
                            <div class="mt-3 flex items-end gap-3">
                                <div class="text-6xl font-extrabold tracking-tight text-slate-900">
                                    {{ availableCoins.toLocaleString() }}
                                </div>
                                <div class="pb-2 text-slate-500 font-semibold">Coins</div>
                            </div>
                            <p class="mt-2 text-slate-500 font-semibold">
                                Gross value: ${{ availableCash.toFixed(2) }}
                            </p>
                            <p class="mt-1 text-slate-500 font-semibold">
                                You receive (50%): ${{ availablePayoutCash.toFixed(2) }}
                            </p>
                            <p class="mt-1 text-slate-500 font-semibold">
                                Platform share (50%): ${{ availablePlatformShareCash.toFixed(2) }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center border border-blue-100">
                            <span class="text-blue-700 text-xl">💰</span>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-slate-600">
                                Conversion rate
                            </div>
                            <div class="text-sm font-semibold text-slate-900">
                                1 Coin = ${{ COIN_RATE.toFixed(2) }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div>
                                <div class="text-xs text-slate-500 font-semibold">Wallet (after conversion)</div>
                                <div class="mt-1 text-lg font-extrabold text-slate-900">
                                    ${{ walletAfterConversion.toFixed(2) }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500 font-semibold">Meets minimum?</div>
                                <div class="mt-1 font-bold" :class="meetsMinimum ? 'text-emerald-700' : 'text-rose-600'">
                                    {{ meetsMinimum ? 'Yes ✅' : 'No ❌' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: convert form -->
                <div class="rounded-3xl border border-slate-200 p-7 bg-white">
                    <p class="text-slate-900 font-semibold">Convert now</p>
                    <p class="mt-1 text-slate-500 text-sm">Choose how many coins you want to convert.</p>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Amount</label>
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <button @click="setAllCoins"
                                    class="px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 font-semibold hover:bg-slate-100">
                                    Convert All
                                </button>

                                <div class="flex items-center gap-2">
                                    <input v-model.number="selectedCoins" type="number" min="0" step="1"
                                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        placeholder="Custom coins" />
                                    <button @click="setMaxCoins"
                                        class="px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold hover:bg-slate-50">
                                        Max
                                    </button>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">
                                Tip: if you're doing custom, enter a coin amount (whole number).
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">You will receive</span>
                                <span class="font-extrabold text-slate-900">${{ selectedCash.toFixed(2) }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm">
                                <span class="text-slate-600">Platform share</span>
                                <span class="font-extrabold text-slate-900">${{ selectedPlatformShare.toFixed(2) }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Coins selected</span>
                                <span class="text-slate-700 font-semibold">{{ selectedCoins.toLocaleString() }}</span>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <label class="text-sm font-semibold text-slate-700">Destination</label>
                            <div class="mt-2 flex items-center justify-between gap-3 flex-wrap">
                                <div class="text-slate-700 font-semibold">LinkUp Wallet Balance</div>
                                <div class="text-slate-500 text-sm">Instant</div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <label class="text-sm font-semibold text-slate-700">Important</label>
                            <ul class="mt-2 text-sm text-slate-600 list-disc pl-5 space-y-1">
                                <li>You can only cash out once you meet the minimum.</li>
                                <li>Converted coins move from your Gifts balance to your Wallet balance.</li>
                                <li>Conversion is processed instantly and added to your wallet.</li>
                            </ul>
                        </div>

                        <div class="pt-2">
                            <button @click="convertToCash" :disabled="!canConvert || isConverting"
                                class="w-full px-5 py-4 rounded-2xl bg-blue-600 text-white font-extrabold shadow hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed">
                                {{ isConverting ? 'Converting...' : 'Convert to Cash' }}
                            </button>
                            <p v-if="conversionMessage" class="mt-3 text-sm" :class="conversionMessage.includes('failed') || conversionMessage.includes('need') ? 'text-rose-600' : 'text-emerald-600'">
                                {{ conversionMessage }}
                            </p>

                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>
