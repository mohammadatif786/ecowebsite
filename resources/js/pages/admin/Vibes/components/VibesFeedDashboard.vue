<script setup lang="ts">
import { ref, computed } from 'vue';
import { Coins, ShoppingCart } from 'lucide-vue-next';

const props = defineProps<{
    feedPosts: any[];
    feedCreators: any[];
    feedCfg: any;
    scale: number;
    fmt: (n: number) => string;
    num: (n: number) => string;
    totals: any;
}>();

const emit = defineEmits(['openCoinGift', 'feedSaveCfg']);

const feedSearchQuery = ref('');

const feedKpis = computed(() => {
    const sc = props.scale;
    const posts = props.feedPosts;
    const creators = props.feedCreators;
    const totalCoins = posts.reduce((a, p) => a + p.coins, 0);
    const coinUSD = totalCoins * props.feedCfg.coinValue;
    const creatorCut = coinUSD * (props.feedCfg.creatorShare / 100);
    const linkupCut = coinUSD - creatorCut;
    const eng = posts.reduce((a, p) => a + p.likes + p.comments + p.shares, 0);
    const views = posts.reduce((a, p) => a + p.views, 0);
    const engRate = views ? (eng / views * 100) : 0;

    return [
        { label: 'Creators', val: creators.length, sub: null, color: 'text-slate-900' },
        { label: 'Posts (period)', val: Math.round(posts.length * sc), sub: null, color: 'text-slate-900' },
        { label: 'Engagement Rate', val: engRate.toFixed(1) + '%', sub: null, color: 'text-sky-600' },
        { label: 'Coins Tipped', val: Math.round(totalCoins * sc).toLocaleString(), sub: '$' + Math.round(coinUSD * sc).toLocaleString(), color: 'text-amber-500' },
        { label: 'LinkUp Coin Revenue', val: '$' + Math.round(linkupCut * sc).toLocaleString(), sub: (100 - props.feedCfg.creatorShare) + '% of tips', color: 'text-green-600' }
    ];
});

const tipBoxMetrics = computed(() => {
    const sc = props.scale;
    const posts = props.feedPosts;
    const totalCoins = posts.reduce((a, p) => a + p.coins, 0);
    const coinUSD = totalCoins * props.feedCfg.coinValue;
    const creatorCut = coinUSD * (props.feedCfg.creatorShare / 100);
    const linkupCut = coinUSD - creatorCut;

    // For shoppable commerce demo (shoppable posts count)
    const shoppable = posts.filter(p => p.tag).length;

    return {
        tipped: Math.round(totalCoins * sc).toLocaleString(),
        gross: '$' + Math.round(coinUSD * sc).toLocaleString(),
        creatorCut: '$' + Math.round(creatorCut * sc).toLocaleString(),
        linkupCut: '$' + Math.round(linkupCut * sc).toLocaleString(),
        shoppableCount: shoppable,
        orders: 14, // Demo value
        gmv: '$1,514' // Demo value
    };
});
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-300">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div><h3 class="text-3xl font-black text-slate-950">LinkUp Vibes</h3><p class="text-slate-500">Social content — photos, reels & stories — where fans don't just like & comment, they <b>send creators LinkUp Coins</b>. Back-office ready; public feed plugs in later. Period & region reactive.</p></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <div v-for="k in feedKpis" :key="k.label" class="card rounded-3xl p-5">
                <p class="text-slate-500 font-bold uppercase text-[10px] tracking-widest">{{ k.label }}</p>
                <h3 class="text-4xl font-black mt-1" :class="k.color">{{ k.val }}</h3>
                <p v-if="k.sub" class="text-xs text-slate-500 mt-1 font-bold">{{ k.sub }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6">
            <div class="card rounded-3xl p-6 2xl:col-span-1">
                <div class="flex items-center gap-2 mb-2"><div class="h-9 w-9 rounded-xl bg-gradient-to-br from-pink-500 to-amber-400 grid place-items-center text-white font-black"><Coins class="w-4 h-4"/></div><h3 class="text-xl font-black">Coin Tipping Economy</h3></div>
                <p class="text-sm text-slate-500 mb-3">When someone loves your post, they tip Coins. Split {{ feedCfg.creatorShare }}% creator / {{ 100 - feedCfg.creatorShare }}% LinkUp — the LinkUp Feed twist.</p>

                <div class="rounded-2xl bg-slate-50 p-4 space-y-2 text-sm border border-slate-100">
                    <div class="flex justify-between"><span class="text-slate-500 font-bold">Coins tipped (period)</span><span class="font-black text-slate-900">{{ tipBoxMetrics.tipped }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 font-bold">Gross value</span><span class="font-black text-slate-900">{{ tipBoxMetrics.gross }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 font-bold">Creators get ({{ feedCfg.creatorShare }}%)</span><span class="font-black text-pink-600">{{ tipBoxMetrics.creatorCut }}</span></div>
                    <div class="flex justify-between border-t border-slate-200 pt-2"><span class="text-slate-500 font-bold">LinkUp keeps</span><span class="font-black text-green-600">{{ tipBoxMetrics.linkupCut }}</span></div>

                    <!-- Shoppable Commerce Section from HTML -->
                    <div class="mt-2 pt-2 border-t border-slate-200">
                        <p class="font-black text-slate-700 mb-1 flex items-center gap-1"><ShoppingCart class="w-4 h-4 text-indigo-600"/>Shoppable Commerce</p>
                        <div class="flex justify-between"><span class="text-slate-500 font-bold">Shoppable posts</span><span class="font-black text-slate-900">{{ tipBoxMetrics.shoppableCount }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500 font-bold">Tap-to-buy orders</span><span class="font-black text-slate-900">{{ tipBoxMetrics.orders }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500 font-bold">Feed-driven GMV</span><span class="font-black text-indigo-600">{{ tipBoxMetrics.gmv }}</span></div>
                    </div>
                </div>

                <label class="text-sm font-bold text-slate-600 mt-3 block">Coin value ($ each)</label>
                <input v-model="feedCfg.coinValue" type="number" step="0.01" @change="emit('feedSaveCfg')" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5 outline-none focus:border-indigo-400 font-bold">

                <label class="text-sm font-bold text-slate-600 mt-2 block">Creator share (%)</label>
                <input v-model="feedCfg.creatorShare" type="number" step="1" @change="emit('feedSaveCfg')" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2.5 outline-none focus:border-indigo-400 font-bold">
            </div>

            <div class="card rounded-3xl p-6 2xl:col-span-2 overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="text-xl font-black">Posts</h3>
                    <input v-model="feedSearchQuery" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm w-full sm:w-64 font-medium outline-none focus:border-indigo-400 transition-all" placeholder="Search creator, caption, hashtag...">
                </div>
                <div class="overflow-x-auto scrollbar">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="text-xs uppercase text-slate-500 border-b border-slate-100 font-bold">
                            <tr>
                                <th class="py-2 px-2">Post</th>
                                <th class="py-2 px-2">Creator</th>
                                <th class="py-2 px-2">Type</th>
                                <th class="py-2 px-2 text-center">Likes</th>
                                <th class="py-2 px-2 text-center">Comments</th>
                                <th class="py-2 px-2 text-center">Shares</th>
                                <th class="py-2 px-2 text-center">Coins</th>
                                <th class="py-2 px-2 text-center">Status</th>
                                <th class="py-2 px-2 text-right">Tip</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="p in feedPosts.filter(x => !feedSearchQuery || x.handle.toLowerCase().includes(feedSearchQuery.toLowerCase()) || x.caption.toLowerCase().includes(feedSearchQuery.toLowerCase()))" :key="p.id" class="hover:bg-slate-50 transition group">
                                <td class="py-3 px-2">
                                    <div class="font-black text-slate-950">{{ p.id }}</div>
                                    <div class="text-xs text-slate-500 font-normal truncate max-w-[200px]">{{ p.caption }}</div>
                                    <div v-if="p.tag" class="text-xs mt-1 inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-2 py-0.5 font-black uppercase tracking-tighter">
                                        {{ p.tag.name }} · ${{ p.tag.price }}
                                    </div>
                                </td>
                                <td class="py-3 px-2">
                                    <div class="font-bold text-slate-900">{{ p.creator }}</div>
                                    <div class="text-xs text-slate-400 font-medium">{{ p.handle }}</div>
                                </td>
                                <td class="py-3 px-2 font-medium">{{ p.type }}</td>
                                <td class="py-3 px-2 text-center font-bold">{{ (p.likes/1000).toFixed(1) }}k</td>
                                <td class="py-3 px-2 text-center font-bold">{{ (p.comments/1000).toFixed(1) }}k</td>
                                <td class="py-3 px-2 text-center font-bold">{{ (p.shares/1000).toFixed(1) }}k</td>
                                <td class="py-3 px-2 text-center font-black text-amber-600">{{ p.coins.toLocaleString() }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-black border"
                                        :class="p.status==='Live' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-amber-50 text-amber-700 border-amber-100'">
                                        {{ p.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-right">
                                    <button @click="emit('openCoinGift', p.id)" class="rounded-lg bg-gradient-to-r from-pink-500 to-amber-400 text-white px-2.5 py-1 text-[10px] font-black uppercase shadow-sm active:scale-95 transition">Tip</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
