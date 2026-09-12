<script setup lang="ts">
defineProps<{
    vibesAds: any[];
    num: (n: number) => string;
}>();
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-300">
        <div class="flex justify-between items-center"><h3 class="text-3xl font-black text-slate-950">Vibes Ads</h3><button class="px-6 py-3 rounded-2xl bg-indigo-600 text-white font-black text-sm shadow-lg hover:bg-indigo-700 active:scale-95 transition">+ New Ad Unit</button></div>
        <div class="card rounded-[32px] bg-white border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm font-bold border-collapse">
                <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100">
                    <tr><th class="py-5 px-6">Advertiser</th><th class="py-5 px-6 text-center">Likes</th><th class="py-5 px-6 text-center">CTR</th><th class="py-5 px-6 text-center">Status</th><th class="py-5 px-6 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-bold">
                    <tr v-for="a in vibesAds" :key="a.id" class="hover:bg-slate-50 transition">
                        <td class="py-5 px-6"><div class="flex items-center gap-3"><span class="text-lg">{{ a.logo }}</span><div><p class="text-slate-900">{{ a.advertiser }}</p><p class="text-xs text-slate-400 uppercase tracking-widest">{{ a.handle }}</p></div></div></td>
                        <td class="py-5 px-6 text-center text-slate-900 font-black leading-none">{{ num(a.likes) }}</td>
                        <td class="py-5 px-6 text-center text-indigo-600 font-black leading-none">1.4%</td>
                        <td class="py-5 px-6 text-center"><span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase shadow-sm border border-emerald-100" :class="a.status==='Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'">{{a.status}}</span></td>
                        <td class="py-5 px-6 text-right font-black"><button class="text-xs text-indigo-600 hover:underline">Edit Content</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
