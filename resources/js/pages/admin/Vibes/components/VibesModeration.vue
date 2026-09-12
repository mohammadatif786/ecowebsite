<script setup lang="ts">
defineProps<{
    feedReports: any[];
}>();

const emit = defineEmits(['moderatePost']);
</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-300">
        <h3 class="text-3xl font-black text-slate-950">Safety Hub</h3>
        <div class="card rounded-[32px] bg-white border border-slate-100 shadow-sm overflow-hidden font-bold">
            <table class="w-full text-left text-sm font-bold border-collapse">
                <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-100">
                    <tr><th class="py-5 px-6">Post ID</th><th class="py-5 px-6">Creator</th><th class="py-5 px-6">Reason</th><th class="py-5 px-6 text-center">Risk Level</th><th class="py-5 px-6 text-right">Moderation</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="r in feedReports" :key="r.id">
                        <td class="py-6 px-6 font-black text-indigo-600 shadow-sm">#{{ r.post }}</td>
                        <td class="py-6 px-6 font-black text-slate-950 leading-none">{{ r.creator }}</td>
                        <td class="py-6 px-6 text-slate-500 font-bold leading-tight uppercase text-[10px]">{{ r.reason }}</td>
                        <td class="py-6 px-6 text-center"><span class="px-4 py-2 rounded-full text-[9px] font-black uppercase shadow-sm border" :class="r.risk==='High'?'bg-rose-50 text-rose-700 border-rose-100':'bg-amber-50 text-amber-700 border-amber-100'">{{ r.risk }} Risk</span></td>
                        <td class="py-6 px-6 text-right font-black"><div class="flex gap-2 justify-end"><button @click="emit('moderatePost', r.id, 'approve')" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-[10px] uppercase shadow-lg active:scale-95 transition">Allow</button><button @click="emit('moderatePost', r.id, 'remove')" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-[10px] uppercase shadow-lg active:scale-95 transition">Take Down</button></div></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
