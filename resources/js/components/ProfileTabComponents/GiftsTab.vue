<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { Gift, Wallet, Activity, TrendingUp } from 'lucide-vue-next';

interface GiftData {
    id: number;
    sender_id: number;
    recieved_id: number;
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
    sortDate: Date; // Add this for proper sorting
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
        transformed.push({
            id: `G-SENT-${gift.id}`,
            kind: 'GIFT_SENT',
            gift: gift.name,
            qty: 1,
            coins: coinVal,
            gross: coinVal * COIN_RATE,
            to: {
                id: gift.receiver?.id?.toString() || 'Unknown',
                name: gift.receiver?.name || 'Unknown User'
            },
            context: 'Gift sent',
            date: gift.created_at ? new Date(gift.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : 'Unknown',
            status: gift.status || 'sent',
            sortDate: new Date(gift.created_at || 0) // Add original date for sorting
        });
    });

    // Add received gifts
    props.gifts_received?.forEach((gift: GiftData) => {
        const coinVal = Number(gift.coins) || 0;
        transformed.push({
            id: `G-RECV-${gift.id}`,
            kind: 'GIFT_RECEIVED',
            gift: gift.name,
            qty: 1,
            coins: coinVal,
            gross: coinVal * COIN_RATE,
            from: {
                id: gift.sender?.id?.toString() || 'Unknown',
                name: gift.sender?.name || 'Unknown User'
            },
            context: 'Gift received',
            date: gift.created_at ? new Date(gift.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : 'Unknown',
            status: gift.status || 'received',
            sortDate: new Date(gift.created_at || 0) // Add original date for sorting
        });
    });

    // Sort by date (newest first) using the original timestamp
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

function giftPill(kind: any) {
    const map = [
        { GIFT_PURCHASE: `bg-slate-50 border-slate-200 text-slate-700` },
        { GIFT_SENT: `bg-rose-50 border-rose-200 text-rose-700` },
        { GIFT_RECEIVED: `bg-emerald-50 border-emerald-200 text-emerald-700` },
        { GIFT_COLLECT: `bg-sky-50 border-sky-200 text-sky-700` },
    ];
    return `<span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold ${map[kind] || 'bg-slate-50 border-slate-200 text-slate-700'}">${kind.replace('GIFT_', '').replaceAll('_', ' ')}</span>`;
}

function giftDetails(r: any) {
    if (r.kind === "GIFT_SENT") return `To <span class="font-black">${r.to?.name || '—'}</span> <span class="text-xs text-slate-500 font-bold">(${r.to?.id || ''})</span><div class="text-xs text-slate-500 font-bold mt-1">${r.context || ''}</div>`;
    if (r.kind === "GIFT_RECEIVED") return `From <span class="font-black">${r.from?.name || '—'}</span> <span class="text-xs text-slate-500 font-bold">(${r.from?.id || ''})</span><div class="text-xs text-slate-500 font-bold mt-1">${r.context || ''}</div>`;
    return `<span class="text-slate-700 font-bold">${r.details || '—'}</span>`;
}

// Convert to cash functions
function setMaxCoins() {
    selectedCoins.value = availableCoins.value;
}

function setAllCoins() {
    selectedCoins.value = availableCoins.value;
}

function updateSelectedCoins(value: number) {
    const coins = Math.min(Math.max(0, value), availableCoins.value);
    selectedCoins.value = Math.floor(coins);
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
        // Get CSRF token - Laravel standard approach
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (!csrfToken) {
            conversionMessage.value = 'CSRF token not found. Please refresh the page.';
            return;
        }

        console.log('CSRF Token found:', csrfToken.length > 0 ? 'Yes' : 'No');

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

        // Check if response is ok
        if (!response.ok) {
            // Try to get error text
            const errorText = await response.text();
            console.error('Server response error:', errorText);

            if (response.status === 419) {
                conversionMessage.value = 'CSRF token mismatch. Please refresh the page and try again.';
            } else if (response.status === 422) {
                // Validation error
                try {
                    const errorData = JSON.parse(errorText);
                    conversionMessage.value = errorData.message || 'Validation failed. Please check your input.';
                } catch {
                    conversionMessage.value = 'Validation failed. Please check your input.';
                }
            } else {
                conversionMessage.value = `Server error (${response.status}). Please try again later.`;
            }
            return;
        }

        // Parse JSON response
        const data = await response.json();

        if (data.success) {
            // Reset form
            selectedCoins.value = 0;
            showConversionModal.value = false;

            // Show success message
            conversionMessage.value = data.message;

            // Emit event to refresh parent component data
            const event = new CustomEvent('gift-converted', {
                detail: {
                    coinsConverted: data.data.coins_converted,
                    newWalletBalance: data.data.new_wallet_balance,
                    remainingGiftCoins: data.data.remaining_received_coins
                }
            });
            window.dispatchEvent(event);

            console.log('Conversion successful:', data);

            // Reload the page to show updated gift balances and wallet
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

// Watch for changes
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
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Gifts Sent</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-3xl font-black">{{ totalSentCoins }}</div>
                        <div class="text-sm font-bold text-slate-500">Coins</div>
                    </div>
                    <div class="text-xs text-slate-500 font-bold">Value: ${{ totalSent.toFixed(2) }}</div>
                </div>
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Gifts Received</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <div class="text-3xl font-black">{{ totalReceivedCoins }}</div>
                        <div class="text-sm font-bold text-slate-500">Coins</div>
                    </div>
                    <div class="text-xs text-slate-500 font-bold">Value: ${{ totalReceived.toFixed(2) }}</div>
                </div>
            </div>

            <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                <div class="text-xs text-slate-500 font-black">Top gift types (received)</div>
                <div class="mt-2 grid gap-2">
                    <div class="flex items-center justify-between" v-for="item in top_gift" :key="item.name">
                        <div class="font-black text-slate-900">{{ item.name }}</div>
                        <div class="text-xs text-slate-500 font-bold">{{ item.qty }} received</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Tab -->
        <div v-if="activeTab === 'activity'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Gift Activity</div>
                    <div class="text-sm text-slate-500 mt-1">Sent + received + collected</div>
                </div>
                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-pink-50 border-pink-200 text-pink-700">
                    {{ activity.length }}
                </span>
            </div>

            <div class="overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-[0.9rem]">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-3 font-black">ID</th>
                            <th class="text-left px-4 py-3 font-black">Type</th>
                            <th class="text-left px-4 py-3 font-black">Gift</th>
                            <th class="text-left px-4 py-3 font-black">Value</th>
                            <th class="text-left px-4 py-3 font-black">Details</th>
                            <th class="text-left px-4 py-3 font-black">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in activity" :key="item.id">
                            <td class="px-4 py-3 align-top font-black">{{ item.id }}</td>
                            <td class="px-4 py-3 align-top" v-html="giftPill(item.kind)"></td>
                            <td class="px-4 py-3 align-top font-black">{{ item.gift }}</td>
                            <td class="px-4 py-3 align-top">
                                <div class="font-black">{{ item.coins }} Coins</div>
                                <div class="text-xs text-slate-500 font-bold">${{ item.gross.toFixed(2) }}</div>
                            </td>
                            <td class="px-4 py-3 align-top" v-html="giftDetails(item)"></td>
                            <td class="text-slate-500 font-extrabold px-4 py-3 align-top">{{ item.date }}</td>
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
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-slate-500 font-black">Total spent</div>
                            <div class="mt-1 flex items-baseline gap-2">
                                <div class="text-4xl font-black">{{ totalSentCoins }}</div>
                                <div class="text-sm font-bold text-slate-500">Coins</div>
                            </div>
                            <div class="text-xs text-slate-500 font-bold mt-1">Value: ${{ totalSent.toFixed(2) }}</div>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-100 grid place-items-center">
                            <Gift class="w-5 h-5 text-rose-700" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-slate-500 font-black">Total received</div>
                            <div class="mt-1 flex items-baseline gap-2">
                                <div class="text-4xl font-black">{{ totalReceivedCoins }}</div>
                                <div class="text-sm font-bold text-slate-500">Coins</div>
                            </div>
                            <div class="text-xs text-slate-500 font-bold mt-1">Value: ${{ totalReceived.toFixed(2) }}
                            </div>
                        </div>
                        <div
                            class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 grid place-items-center">
                            <Gift class="w-5 h-5 text-emerald-700" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-100 grid place-items-center">
                            <Gift class="w-4 h-4 text-rose-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Sent</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ sentGifts.length }}</div>
                    <div class="text-xs text-slate-500 mt-1">Gifts sent</div>
                </div>

                <div class="border border-slate-300/35 rounded-[18px] bg-white p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 grid place-items-center">
                            <Gift class="w-4 h-4 text-emerald-700" />
                        </div>
                        <div class="text-sm font-medium text-slate-600">Received</div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ receivedGifts.length }}</div>
                    <div class="text-xs text-slate-500 mt-1">Gifts received</div>
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
