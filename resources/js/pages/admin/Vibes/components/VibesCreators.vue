<script setup lang="ts">
import { CheckCircle2 } from 'lucide-vue-next';

defineProps<{
    feedCreators: any[];
    num: (n: number) => string;
}>();
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-300">
        <div class="flex justify-between items-center"><h3 class="text-3xl font-black text-slate-950">Creators</h3><button class="px-8 py-3.5 rounded-2xl bg-slate-950 text-white font-black text-sm shadow-xl active:scale-95 transition">+ Add Creator</button></div>
        <div class="card rounded-[32px] bg-white border border-slate-100 shadow-sm overflow-hidden font-bold">
            <table class="w-full text-left text-sm font-bold border-collapse">
                <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100">
                    <tr><th class="py-4 px-6">Creator Profile</th><th class="py-4 px-6 text-center">Followers</th><th class="py-4 px-6 text-center">Coins Earned</th><th class="py-4 px-6 text-center">Status</th><th class="py-4 px-6 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="c in feedCreators" :key="c.id" class="hover:bg-slate-50 transition">
                        <td class="py-5 px-6"><div class="flex items-center gap-3"><div><p class="text-slate-900 font-black">{{c.name}} <CheckCircle2 v-if="c.verified" class="w-4 h-4 inline text-sky-500 ml-1"/></p><p class="text-xs text-slate-400 uppercase tracking-tight">{{c.handle}}</p></div></div></td>
                        <td class="py-5 px-6 text-center text-slate-950 leading-none">{{ num(c.followers) }}</td>
                        <td class="py-5 px-6 text-center text-amber-600 font-black leading-none">{{ num(c.coins) }} 🪙</td>
                        <td class="py-5 px-6 text-center"><span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase shadow-sm border" :class="c.status==='Active' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200'">{{c.status}}</span></td>
                        <td class="py-5 px-6 text-right"><button class="px-4 py-2 rounded-xl bg-slate-950 text-white text-[10px] uppercase font-black shadow-sm active:scale-95 transition">Pay Wallet</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
