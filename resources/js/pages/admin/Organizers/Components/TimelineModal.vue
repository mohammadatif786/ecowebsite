<template>
  <!-- v-show keeps the component mounted – watch will fire on every prop change -->
  <div
    v-show="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    @click.self="emit('close')"
  >
    <div class="sheet w-full max-w-2xl bg-white rounded-xl shadow-2xl border border-blue-200 overflow-hidden">
      <h3 class="p-4 border-b border-blue-100 bg-blue-50 font-semibold">
        Payout Timeline
      </h3>

      <div class="body p-4">
        <!-- Timeline -->
        <div class="timeline flex flex-col gap-3 p-4">
          <template v-if="payout?.timeline?.length">
            <div
              v-for="(item, index) in payout.timeline"
              :key="index"
              class="tl-item grid grid-cols-[auto_1fr_auto] gap-3 items-center"
            >
              <!-- Dot + icon -->
              <div
                :class="[
                  'tl-dot w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                  getTimelineDotClass(item.status)
                ]"
              >
                {{ getTimelineIcon(item.status) }}
              </div>

              <!-- Text -->
              <div class="tl-content">
                <strong>{{ item.step }}</strong>
                <div class="text-sm text-gray-600">
                  {{ formatDate(item.time) }}
                </div>
              </div>
            </div>
          </template>

          <!-- Empty state -->
          <div v-else class="text-center text-gray-500 py-8">
            No timeline events available.
          </div>
        </div>

        <div class="divider my-3.5 h-px bg-blue-100"></div>

        <!-- Footer -->
        <div class="right flex justify-end gap-2.5">
          <button
            class="btn ghost px-3.5 py-2.5 rounded-lg border border-blue-300 text-blue-600 bg-white hover:bg-blue-50 hover:border-blue-400"
            @click="emit('close')"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { watch } from 'vue';

/* -------------------------------------------------
   Props
   ------------------------------------------------- */
interface TimelineItem {
  step: string;
  status: 'ok' | 'pending' | string; // you can extend this
  time: string;
}

interface Payout {
  id: number;
  reference: string;
  amount: string;
  fee_amount: string;
  net_amount: string;
  method: string;
  status: string;
  notes: string | null;
  created_at: string;
  updated_at: string;
  author: string;
  organizer_id: number;
  event_id: number;
  event: { id: number; title: string } | null;
  organizer: { id: number; name: string } | null;
  timeline?: TimelineItem[];
}

const props = defineProps<{
  show: boolean;
  payout: Payout | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

/* -------------------------------------------------
   Helpers
   ------------------------------------------------- */
const getTimelineDotClass = (status: string) => {
  if (status === 'ok') return 'bg-green-500 text-white';
  if (status === 'pending') return 'bg-yellow-500 text-white';
  return 'bg-red-500 text-white';
};

const getTimelineIcon = (status: string) => {
  if (status === 'ok') return '✓';
  if (status === 'pending') return '⟳';
  return '✗';
};

const formatDate = (d: string) =>
  new Date(d).toLocaleString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });

/* -------------------------------------------------
   Debug – see the object every time it changes
   ------------------------------------------------- */
watch(
  () => props.payout,
  (p) => {
    console.log('TimelineModal received:', p);
  },
  { immediate: true }
);
</script>

<style scoped>
/* No extra CSS needed – Tailwind handles everything */
</style>