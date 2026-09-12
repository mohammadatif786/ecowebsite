<template>
    <div class="glass rounded-custom rounded-md overflow-hidden">
        <div class="p-3 flex justify-between border-b border-line bg-gradient-to-b from-black/30 to-black/20">
            <h3 class="m-0 text-white text-xl font-bold">
                Live Now
            </h3>
        </div>

        <div class="p-3">
            <div class="flex gap-3 overflow-x-auto pb-2">
                <!-- Session Card 1 -->
                <div v-for="item in all_Streams" @click="fetchLiveStreamDetail(item)"
                    class="card min-w-[18rem] rounded-2xl border border-line bg-gradient-to-b from-white/16 to-white/8 overflow-hidden cursor-pointer transition-all duration-250">
                    <!-- Thumbnail -->
                    <div class="relative aspect-[16/10] bg-gray-200 flex items-center justify-center text-gray-500 rounded-lg overflow-hidden"
                        :style="item.thumbnail ? `background-image: url('/storage/${item.thumbnail}'); background-size: cover; background-position: center;` : ''">
                        <!-- Status badge -->
                        <span class="absolute top-2 left-2 px-2 py-1 rounded-lg text-xs font-extrabold z-10"
                            :class="statusBadgeClass(item.status)">
                            {{ item.status }}
                        </span>

                        <!-- Fallback text if no thumbnail -->
                        <template v-if="!item.thumbnail">
                            <span>No Thumbnail</span>
                        </template>
                    </div>

                    <!-- Meta -->
                    <div class="p-3">
                        <div class="mb-2">
                            <strong class="text-ink">{{ item.title }}</strong><br />
                            <small class="text-muted">{{ item.broadcast_type }} • {{ item.visibility }}</small><br />
                            <small class="text-muted">{{ item.start_time }}</small>
                        </div>

                        <div class="flex space-x-2 justify-between">
                            <div>👀 0</div>
                            <div>❤️ 0</div>
                            <div>🎁 0</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";

defineProps<{
    all_Streams: any,
    fetchLiveStreamDetail: any
}>();

// Function to return Tailwind gradient class based on status
const statusBadgeClass = (status: any) => {
    switch (status?.toLowerCase()) {
        case 'live':
            return 'bg-gradient-to-r from-green-500 to-green-400 text-white'
        case 'upcoming':
            return 'bg-gradient-to-r from-blue-500 to-blue-400 text-white'
        case 'ended':
            return 'bg-gradient-to-r from-gray-500 to-gray-400 text-white'
        case 'error':
            return 'bg-gradient-to-r from-red-500 to-red-400 text-white'
        default:
            return 'bg-gradient-to-r from-yellow-500 to-yellow-400 text-black'
    }
}


</script>