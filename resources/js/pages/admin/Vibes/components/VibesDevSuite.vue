<script setup lang="ts">
import { Code, Terminal } from 'lucide-vue-next';

defineProps<{
    feedAPIContract: any[];
}>();
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-300">
        <h3 class="text-3xl font-black text-slate-950">Developer Suite</h3>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 font-bold">
            <div class="card rounded-[32px] p-8 bg-white border-slate-100 shadow-sm overflow-hidden">
                <h3 class="text-xl font-black mb-10 flex items-center gap-3 font-['Inter']"><Code class="w-8 h-8 text-indigo-600" /> REST API Contract</h3>
                <div class="space-y-4">
                    <div v-for="ep in feedAPIContract" :key="ep[1]" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-indigo-300 transition-all shadow-sm">
                        <div class="flex items-center gap-5 mb-2 font-black"><span class="text-[10px] px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white uppercase tracking-tighter">{{ ep[0] }}</span><span class="font-mono text-sm text-slate-950 tracking-tight">{{ ep[1] }}</span></div>
                        <p class="text-xs text-slate-400 font-black uppercase ml-16">{{ ep[2] }}</p>
                    </div>
                </div>
            </div>
            <div class="card rounded-[32px] p-8 bg-slate-950 text-white shadow-2xl relative overflow-hidden flex flex-col">
                <div class="absolute -right-6 -top-6 opacity-10"><Terminal class="w-48 h-48" /></div>
                <h3 class="text-2xl font-black mb-8 leading-tight uppercase tracking-tighter">Data Schema (Social)</h3>
                <div class="flex-1 p-6 rounded-3xl bg-white/5 font-mono text-[11px] leading-relaxed text-indigo-300 border border-white/10 overflow-x-auto shadow-inner"><pre>{
  "post_id": "string",
  "creator_handle": "string",
  "likes_count": "number",
  "coins_tipped": "number",
  "media_url": "string"
}</pre></div>
                <button class="mt-10 w-full py-5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-black text-sm border border-white/10 transition uppercase tracking-widest">Download Integration Bundle</button>
            </div>
        </div>
    </div>
</template>
