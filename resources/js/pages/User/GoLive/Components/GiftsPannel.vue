<template>
    <div class="glass rounded-custom rounded-md overflow-hidden">
        <h3
            class="m-0 p-3 border-b border-line bg-gradient-to-b from-black/30 to-black/20 text-white text-xl font-bold">
            Gift Catalog
        </h3>

        <div class="panel-body p-3">
            <div class="catalog grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2 max-h-72 overflow-y-auto">
                <div v-for="gift in gifts" :key="gift.id"
                    class="gift flex justify-between items-center p-3 rounded-2xl border border-line bg-gradient-to-b from-white/14 to-white/6">
                    <div class="gift-details flex flex-col gap-1 items-center">
                        <div class="gift-emoji text-xl">{{ gift.emoji }}</div>
                        <div class="text-center">
                            <strong class="gift-name text-ink">{{ gift.name }}</strong><br />
                            <small class="gift-cost text-muted">{{ gift.coins }} coins</small>
                        </div>
                    </div>

                    <button v-if="props.liveStramData" @click="$emit('confirm-send-gift', gift.id, 1)"
                        class="btn small bg-white text-gray-900 px-2.5 py-1.5 rounded-lg text-xs font-bold cursor-pointer shadow-lg transition-transform hover:scale-105">
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

const props = defineProps<{
    gifts: Array<any>
    liveStramData: any
}>()

defineEmits<{
  (e: 'confirm-send-gift', giftId: string, qty: number): void
}>()
</script>
