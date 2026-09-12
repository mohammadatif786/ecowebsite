<script setup lang="ts">
import { ref, computed } from "vue";
import { formatUpdatedAt } from "../../composables/dateformater";
import { ShoppingBag, Heart, Users } from 'lucide-vue-next';

const props = defineProps<{
    TabData: any
}>()

const activeTab = ref('matches')

// reactive computed properties
const friendData = computed(() =>
    props.TabData.mutual_Users || []
)

const likesReceived = computed(() =>
    props.TabData.like_revived || 0
)

const likesSent = computed(() =>
    props.TabData.like_send || 0
)

const tabs = [
    { id: 'matches', name: 'Matches', icon: Users },
    { id: 'likes', name: 'Likes', icon: Heart }
]

</script>
<template>
    <div class="bg-white border border-slate-300/35 rounded-[22px] shadow-[0_12px_26px_rgba(2,6,23,.08)] p-5">
        <!-- Tab Navigation -->
        <div class="flex space-x-1 border-b border-slate-200 mb-6">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                    'flex items-center gap-2 px-4 py-3 text-sm font-medium transition-all border-b-2',
                    activeTab === tab.id
                        ? 'text-slate-900 border-slate-900'
                        : 'text-slate-500 border-transparent hover:text-slate-700 hover:border-slate-300'
                ]"
            >
                <component :is="tab.icon" class="w-4 h-4" />
                {{ tab.name }}
            </button>
        </div>

        <!-- Matches Tab -->
        <div v-if="activeTab === 'matches'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight">Recent Matches</div>
                    <div class="text-sm text-slate-500 mt-1">People you've matched with</div>
                </div>
                <span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-purple-50 border-purple-200 text-purple-700">
                    {{ friendData.length }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <div v-if="friendData.length > 0" v-for="item in friendData"
                    class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-0 overflow-hidden">
                    <div class="h-28 bg-slate-200">
                        <img :src="item.avatar ?? 'https://www.gravatar.com/avatar/?d=mp&f=y'"
                            class="w-full h-full object-cover" :alt="item.name" />
                    </div>
                    <div class="p-3">
                        <div class="font-black text-slate-900">{{ item.name ?? '--' }}</div>
                        <div class="text-xs text-slate-500 font-bold">{{ item.city ?? '--' }}</div>
                    </div>
                </div>
                <div v-else class="col-span-full p-8 text-center">
                    <div class="text-slate-500 font-semibold">No matches found</div>
                    <div class="text-sm text-slate-400 mt-1">Start connecting with people to see your matches here</div>
                </div>
            </div>
        </div>

        <!-- Likes Tab -->
        <div v-if="activeTab === 'likes'">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-lg font-black tracking-tight">Likes Engagement</div>
                    <div class="text-sm text-slate-500 mt-1">Track your likes sent and received</div>
                </div>
                <span class="inline-flex items-center gap-[0.35rem] whitespace-nowrap border rounded-full px-[0.65rem] py-[0.35rem] text-xs font-extrabold bg-rose-50 border-rose-200 text-rose-700">
                    {{ likesReceived + likesSent }} Total
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Likes received</div>
                    <div class="mt-1 text-4xl font-black text-emerald-700">{{ likesReceived }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">People who liked you</div>
                </div>

                <div class="relative overflow-hidden border border-slate-300/35 rounded-[18px] bg-white p-[14px]">
                    <div class="text-xs text-slate-500 font-black">Likes sent</div>
                    <div class="mt-1 text-4xl font-black text-rose-700">{{ likesSent }}</div>
                    <div class="text-xs text-slate-500 font-bold mt-1">People you've liked</div>
                </div>
            </div>
        </div>
    </div>
</template>