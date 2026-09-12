<script setup lang="ts">
import { Newspaper } from 'lucide-vue-next';

const props = defineProps<{
    TabData: any[];
}>();

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString();
};

const FLAG = (code: string) => {
    const map: Record<string, string> = {
        JM: "🇯🇲", BS: "🇧🇸", TT: "🇹🇹", BB: "🇧🇧", HT: "🇭🇹", DO: "🇩🇴", PR: "🇵🇷",
        AG: "🇦🇬", GD: "🇬🇩", LC: "🇱🇨", VC: "🇻🇨", KN: "🇰🇳", GY: "🇬🇾", SR: "🇸🇷",
        BR: "🇧🇷", CO: "🇨🇴", MX: "🇲🇽", AR: "🇦🇷", CL: "🇨🇱", PE: "🇵🇪", VE: "🇻🇪",
        PA: "🇵🇦", CR: "🇨🇷", GLB: "🌍"
    };
    return map[code] || "🏳️";
};
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div
            class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Your Published News</div>
                    <div class="text-sm text-slate-500 mt-1">Updates you have shared with the community</div>
                </div>
                <span
                    class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-3 py-1 text-xs font-black bg-sky-50 border-sky-100 text-sky-700">
                    {{ props.TabData?.length || 0 }} posts
                </span>
            </div>

            <div class="mt-6 overflow-hidden rounded-[18px] border border-slate-300/35 bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-100">
                        <tr>
                            <th class="text-left px-5 py-3.5 font-black uppercase tracking-wider text-[11px]">Headline
                            </th>
                            <th class="text-left px-5 py-3.5 font-black uppercase tracking-wider text-[11px]">Country
                            </th>
                            <th class="text-left px-5 py-3.5 font-black uppercase tracking-wider text-[11px]">Category
                            </th>
                            <th class="text-left px-5 py-3.5 font-black uppercase tracking-wider text-[11px]">Trending
                            </th>
                            <th class="text-left px-5 py-3.5 font-black uppercase tracking-wider text-[11px]">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="item in props.TabData" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-4 align-top">
                                <div class="font-black text-slate-900 line-clamp-1 truncate max-w-[200px]"
                                    :title="item.title">{{ item.title }}</div>
                                <div class="text-xs text-slate-400 font-bold mt-1">{{ item.source_name || 'Internal' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top whitespace-nowrap">
                                <span
                                    class="pill inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs font-black">
                                    {{ FLAG(item.country_code) }} {{ item.country }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-top whitespace-nowrap">
                                <span class="text-slate-600 font-black text-xs uppercase">{{ item.category }}</span>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-center gap-1 font-black text-orange-600 text-xs">
                                    <span class="text-orange-500">🔥</span> {{ item.trending || 0 }}
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top whitespace-nowrap text-slate-500 font-extrabold text-xs">
                                {{ formatDate(item.published_at || item.created_at) }}
                            </td>
                        </tr>
                        <tr v-if="!props.TabData || props.TabData.length === 0">
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <Newspaper class="w-8 h-8 text-slate-200" />
                                    <div class="text-slate-400 font-black">No news posts yet</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            class="relative overflow-hidden bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-black tracking-tight">Global Impact</div>
                    <div class="text-sm text-slate-500 mt-1">Trending across LinkUp</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-sky-50 border border-sky-100 grid place-items-center">
                    <Newspaper class="w-5 h-5 text-sky-700" />
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-4">
                <div class="p-4 rounded-[18px] bg-slate-50 border border-slate-200 flex flex-col gap-1">
                    <div class="text-[11px] font-black text-sky-600 uppercase tracking-wider">Most Active Region</div>
                    <div class="text-base font-black text-slate-900">Caribbean Islands</div>
                </div>

                <div class="p-4 rounded-[18px] bg-slate-50 border border-slate-200 flex flex-col gap-1">
                    <div class="text-[11px] font-black text-orange-600 uppercase tracking-wider">Total Engagement</div>
                    <div class="text-base font-black text-slate-900">{{(props.TabData || []).reduce((acc, curr) => acc
                        + (curr.trending || 0), 0) }} Points</div>
                </div>

                <div
                    class="p-4 rounded-[18px] bg-sky-600 border border-sky-500 flex flex-col gap-1 text-white shadow-lg shadow-sky-600/10">
                    <div class="text-[11px] font-black text-sky-100 uppercase tracking-wider">Top Category</div>
                    <div class="text-base font-black">Breaking News</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pill {
    border-radius: 999px;
    padding: .25rem .6rem;
    font-size: .75rem;
    font-weight: 900;
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}
</style>