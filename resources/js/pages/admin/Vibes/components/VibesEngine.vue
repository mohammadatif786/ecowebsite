<script setup lang="ts">
import { SlidersHorizontal, ShieldAlert } from 'lucide-vue-next';

defineProps<{
    feedEngineCfg: any;
}>();
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-300">
        <div class="flex items-center justify-between mb-8">
            <div><h3 class="text-3xl font-black text-slate-950">Algorithm Hub</h3><p class="text-slate-500 font-medium">Fine-tune ranking signals and safety protocols.</p></div>
            <button class="rounded-2xl bg-indigo-600 text-white px-10 py-4 font-black text-sm shadow-xl active:scale-95 transition">Apply Global Tuning</button>
        </div>
        <div class="grid grid-cols-1 2xl:grid-cols-2 gap-8">
            <div class="card rounded-[32px] p-8 bg-white border-slate-100 shadow-sm">
                <h3 class="text-xl font-black mb-10 flex items-center gap-3"><SlidersHorizontal class="w-6 h-6 text-indigo-600" /> Signal Weights</h3>
                <div class="space-y-12">
                    <div v-for="(v, k) in feedEngineCfg.rank" :key="k">
                        <div class="flex justify-between items-center mb-4 px-1 font-black text-xs text-slate-400 uppercase tracking-widest"><span>{{ k }}</span><span class="text-indigo-700 text-sm font-black">{{ v }}%</span></div>
                        <input type="range" min="0" max="100" v-model="(feedEngineCfg.rank as any)[k]" class="w-full accent-indigo-600 h-2 bg-slate-100 rounded-full appearance-none cursor-pointer transition active:scale-[1.01]">
                    </div>
                </div>
            </div>
            <div class="space-y-8">
                <div class="card rounded-[32px] p-8 bg-slate-900 text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 opacity-10"><ShieldAlert class="w-32 h-32 text-rose-500" /></div>
                    <h3 class="text-xl font-black mb-10 flex items-center gap-3 text-rose-400"><ShieldAlert class="w-6 h-6" /> AI Moderation</h3>
                    <div class="space-y-6 font-black">
                        <div><label class="text-[10px] uppercase text-slate-500 tracking-widest block mb-3 ml-1">Auto-hide threshold (Reports)</label><div class="flex items-center justify-between bg-white/5 border border-white/10 rounded-2xl p-4 shadow-inner"><input v-model="feedEngineCfg.automod.autoHideReports" type="number" class="bg-transparent border-0 text-white font-black text-3xl p-0 focus:ring-0 w-24"><span class="text-xs text-slate-500 uppercase">Alerts</span></div></div>
                        <div><label class="text-[10px] uppercase text-slate-500 tracking-widest block mb-3 ml-1">AI Confidence (%)</label><div class="flex items-center justify-between bg-white/5 border border-white/10 rounded-2xl p-4 shadow-inner"><input v-model="feedEngineCfg.automod.aiConfidence" type="number" class="bg-transparent border-0 text-white font-black text-3xl p-0 focus:ring-0 w-24"><span class="text-xs text-slate-500 uppercase">Certainty</span></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
