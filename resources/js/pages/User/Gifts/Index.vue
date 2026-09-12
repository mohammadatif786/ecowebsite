<template>
    <AuthenticatedLayout>

        <Head title="Gifts" />

        <main class="max-w-7xl mx-auto px-4 py-6 space-y-6">
            <!-- Mode Switch -->
            <section class="border border-slate-200 rounded-2xl p-4 md:p-5 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6">
                    <!-- Mode Buttons -->
                    <div class="seg space-x-2">
                        <button :class="[
                            'px-4 py-2 rounded-xl font-medium transition-all duration-200',
                            mode === 'gifts'
                                ? 'bg-blue-600 text-white shadow-md'
                                : 'bg-slate-200 text-slate-700 hover:bg-slate-300'
                        ]" @click="setMode('gifts')">
                            Buy Gifts
                        </button>

                        <button :class="[
                            'px-4 py-2 rounded-xl font-medium transition-all duration-200',
                            mode === 'purchase'
                                ? 'bg-blue-600 text-white shadow-md'
                                : 'bg-slate-200 text-slate-700 hover:bg-slate-300'
                        ]" @click="setMode('purchase')">
                            Purchases
                        </button>
                    </div>

                    <p class="text-sm text-white">
                        You’re in Buy Coins mode. Selecting a pack adds coins.
                    </p>

                    <div class="ml-auto flex items-center gap-2">
                        <button class="px-4 py-2 rounded-xl border border-slate-400 text-slate-700 font-medium
                                hover:bg-blue-600 hover:text-white 
                                focus:outline-none focus:ring-2 focus:ring-blue-300
                                transition-all duration-200" @click="BuyCoins">
                            Buy Coins
                        </button>
                    </div>
                </div>
            </section>

            <!-- Filters (only show in gifts mode) -->
            <section v-if="mode === 'gifts'" ref="gifts" class="border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-white mb-1">Search by name</label>
                        <input type="text" placeholder="Rose, Mojito, Crown…" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm
                            focus:ring-2 focus:ring-linkup-blue focus:border-linkup-blue
                            transition-all text-white" v-model="filters.search" @input="applyFilters" />
                    </div>

                    <!-- Category -->
                    <div class="w-full md:w-48">
                        <label class="block text-xs font-semibold text-white mb-1">Category</label>
                        <select class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm
                                focus:ring-2 focus:ring-linkup-blue focus:border-linkup-blue
                                transition-all" v-model="filters.category" @change="applyFilters">
                            <option value="">All</option>
                            <option value="3d">3D</option>
                            <option value="vip">VIP</option>
                            <option value="love">Love</option>
                            <option value="moods">Moods</option>
                            <option value="artists">Artists</option>
                            <option value="collectibles">Collectibles</option>
                            <option value="games">Games</option>
                            <option value="family">Family</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="w-full md:w-48">
                        <label class="block text-xs font-semibold text-white mb-1">Sort</label>
                        <select class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm
                                focus:ring-2 focus:ring-linkup-blue focus:border-linkup-blue
                                transition-all" v-model="filters.sort" @change="applyFilters">
                            <option value="">Featured</option>
                            <option value="name_asc">Name A→Z</option>
                            <option value="name_desc">Name Z→A</option>
                            <option value="coins_asc">Coins: Low→High</option>
                            <option value="coins_desc">Coins: High→Low</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Grid -->
            <section v-if="mode === 'gifts'" ref="gifts">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Dummy Card 1 -->
                    <article v-for="item in props.gifts"
                        class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-md hover:shadow-xl hover:border-slate-300 transition-all duration-300">
                        <div class="p-5 flex flex-col h-full">
                            <div class="flex items-center justify-center">
                                <img :src="item.file_object" alt="Mojito Pack"
                                    class="w-24 h-24 rounded-xl object-cover border shadow-sm group-hover:scale-105 transition-transform" />
                            </div>
                            <h3
                                class="mt-4 font-semibold text-lg text-slate-800 group-hover:text-linkup-blue transition-colors">
                                {{ item.name }}
                            </h3>
                            <p class="text-sm text-slate-500 capitalize">{{ item.category }}</p>
                            <div class="mt-3 text-xl font-bold text-slate-900">
                                {{ item.coins }} coins
                            </div>
                            <button class="mt-4 w-full px-4 py-2 rounded-xl bg-blue-400
                                    text-white font-medium shadow-md hover:shadow-lg hover:bg-blue-600
                                    transition-all duration-200" @click="buyGift(item.id)">
                                Buy Gift
                            </button>
                        </div>
                    </article>
                    <div v-if="props.gifts.length === 0" class="col-span-full text-center py-10 text-slate-500">
                        No gifts found.
                    </div>
                </div>
            </section>


            <!-- Purchases (only show in purchase mode) -->
            <section v-if="mode === 'purchase'" ref="purchase"
                class="border border-slate-200 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-white">Recent Purchases</h3>
                </div>

                <div v-if="props.gift_purchases.length" class="grid md:grid-cols-2 gap-3">
                    <div v-for="purchase in props.gift_purchases" :key="purchase.id"
                        class="border border-slate-200 rounded-xl p-3 bg-white">
                        <div class="flex items-center justify-between">
                            <div class="font-medium">{{ purchase.gift.name }}</div>
                            <div class="text-xs text-slate-500">{{ new Date(purchase.created_at).toLocaleString() }}</div>
                        </div>
                        <div class="text-sm">Qty: {{ purchase.quantity }}</div>
                        <div class="text-sm">Total: <span class="font-semibold">{{ purchase.total_coins }} coins</span>
                        </div>
                    </div>
                </div>

                <p v-else class="text-slate-600 text-sm">No purchases yet.</p>
            </section>
        </main>
    </AuthenticatedLayout>
</template>


<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue';
const mode = ref('gifts');

const props = defineProps<{
    gifts: Array<any>,
    gift_purchases: Array<any>,
    filters: Record<string, string>
}>()

const filters = ref({
    search: props.filters.search || '',
    category: props.filters.category || '',
    sort: props.filters.sort || 'featured',
})

const applyFilters = () => {
    router.get(route('frontend.gift.index'), filters.value, {
        preserveScroll: true
    })
}

const history = ref([
    { id: 'VG1', at: new Date().toISOString(), name: 'Island Rose Bouquet', qty: 1, totalCoins: 33, pay: 'coins' },
    { id: 'CP1', at: new Date().toISOString(), name: 'Coin Pack', qty: 1, totalCoins: 100, pay: 'wallet' }
]);
const setMode = (newMode: any) => {
    mode.value = newMode;
};

const BuyCoins = () => {
    router.visit(route('frontend.user.wallet.coin'))
};

const buyGift = (giftId: number) => {
    router.post(route('frontend.gifts.buy'),
        { gift_id: giftId},
        {
            preserveScroll: true,
        }
    )
}

</script>



<style>
.tag {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.glass {
    backdrop-filter: blur(12px);
    background: rgba(255, 255, 255, 0.72);
}

.delta {
    position: absolute;
    transform: translate(-50%, -50%);
    left: 50%;
    top: -6px;
    padding: 0.1rem 0.4rem;
    border-radius: 0.5rem;
    font-weight: 700;
    font-size: 0.75rem;
}

.delta.up {
    background: #10b981;
    color: white;
}

.delta.down {
    background: #ef4444;
    color: white;
}

.seg {
    display: inline-flex;
    border-radius: 12px;
    background: #eef2ff;
    padding: 4px;
}

.seg button {
    padding: 0.4rem 0.8rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
}

.seg .active {
    background: white;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
}
</style>
